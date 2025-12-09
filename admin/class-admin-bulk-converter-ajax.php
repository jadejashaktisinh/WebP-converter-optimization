<?php

/**
 * Bulk Converter AJAX Handler
 *
 * @package    Webp_Converter_Optimizer
 * @subpackage Webp_Converter_Optimizer/admin
 */
class Admin_Bulk_Converter_Ajax {

	/**
	 * Initialize the class and set up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_bulk_convert_images', array( $this, 'handle_bulk_convert' ) );
	}

	/**
	 * Handle bulk image conversion AJAX request.
	 *
	 * @since 1.0.0
	 */
	public function handle_bulk_convert() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'webp_opt_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ), 403 );
		}

		// Check user permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
		}

		$quality = isset( $_POST['quality'] ) ? intval( $_POST['quality'] ) : 80;
		$delete_original = isset( $_POST['delete_original'] ) && $_POST['delete_original'] === '1';
		
		// Get batch size from settings
		$settings = get_option( 'webp_optimizer_settings', array( 'batch_size' => 10 ) );
		$batch_size = ! empty( $settings['batch_size'] ) ? intval( $settings['batch_size'] ) : 10;

		// Get all images from media library
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => array( 'image/jpeg', 'image/png', 'image/gif' ),
			'post_status'    => 'inherit',
			'posts_per_page' => $batch_size,
			'paged'          => isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1,
		);

		$attachments = get_posts( $args );

		// Get total count
		$total_args = $args;
		$total_args['posts_per_page'] = -1;
		$total_args['fields'] = 'ids';
		$total_count = count( get_posts( $total_args ) );

		$stats = array(
			'total'     => $total_count,
			'processed' => 0,
			'converted' => 0,
			'failed'    => 0,
			'skipped'   => 0,
		);

		foreach ( $attachments as $attachment ) {
			$file_path = get_attached_file( $attachment->ID );

			if ( ! file_exists( $file_path ) ) {
				$stats['skipped']++;
				continue;
			}

			$result = $this->convert_to_webp( $file_path, $quality, $attachment->ID, $delete_original );

			if ( $result ) {
				$stats['converted']++;
			} else {
				$stats['failed']++;
			}

			$stats['processed']++;
		}

		$has_more = ( $stats['processed'] + ( ( $args['paged'] - 1 ) * $batch_size ) ) < $total_count;

		wp_send_json_success( array(
			'message'  => 'Batch completed',
			'stats'    => $stats,
			'has_more' => $has_more,
			'next_page' => $args['paged'] + 1,
		) );
	}

	/**
	 * Convert an image to WebP format.
	 *
	 * @since 1.0.0
	 * @param string $file_path Path to the original image file.
	 * @param int    $quality WebP quality (1-100).
	 * @param int    $attachment_id Optional. Attachment ID if replacing original.
	 * @param bool   $delete_original Optional. Whether to delete the original file.
	 * @return bool True on success, false on failure.
	 */
	private function convert_to_webp( $file_path, $quality, $attachment_id = null, $delete_original = false ) {
		$image_type = exif_imagetype( $file_path );

		switch ( $image_type ) {
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg( $file_path );
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng( $file_path );
				break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif( $file_path );
				break;
			default:
				return false;
		}

		if ( ! $image ) {
			return false;
		}

		$webp_path = preg_replace( '/\.(jpe?g|png|gif)$/i', '.webp', $file_path );

		$success = imagewebp( $image, $webp_path, $quality );
		imagedestroy( $image );

		if ( $success ) {
			if ( $attachment_id && $delete_original ) {
				// Get old and new URLs
				$old_url = wp_get_attachment_url( $attachment_id );
				$new_url = preg_replace( '/\.(jpe?g|png|gif)$/i', '.webp', $old_url );
				
				// Replace original file with WebP and update attachment metadata
				wp_delete_file( $file_path );
				update_attached_file( $attachment_id, $webp_path );
				
				wp_update_post( array(
					'ID' => $attachment_id,
					'post_mime_type' => 'image/webp',
					'guid' => $new_url
				) );
				
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				$attach_data = wp_generate_attachment_metadata( $attachment_id, $webp_path );
				wp_update_attachment_metadata( $attachment_id, $attach_data );
				
				// Update post content references
				$this->update_image_references( $old_url, $new_url );
			} else {
				// Create new attachment for WebP
				$attachment = array(
					'post_mime_type' => 'image/webp',
					'post_title'     => basename( $webp_path, '.webp' ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);

				$attach_id = wp_insert_attachment( $attachment, $webp_path );
				
				if ( ! is_wp_error( $attach_id ) ) {
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $webp_path );
					wp_update_attachment_metadata( $attach_id, $attach_data );
				}
			}
		}

		return $success;
	}

	/**
	 * Update image URL references in post content.
	 *
	 * @since 1.0.0
	 * @param string $old_url Old image URL to replace.
	 * @param string $new_url New image URL.
	 */
	private function update_image_references( $old_url, $new_url ) {
		$posts = get_posts( array(
			'post_type' => 'any',
			'posts_per_page' => -1,
			's' => basename( $old_url ),
			'fields' => 'ids'
		) );

		foreach ( $posts as $post_id ) {
			$post = get_post( $post_id );
			if ( strpos( $post->post_content, $old_url ) !== false ) {
				wp_update_post( array(
					'ID' => $post_id,
					'post_content' => str_replace( $old_url, $new_url, $post->post_content )
				) );
			}
		}
	}

}
