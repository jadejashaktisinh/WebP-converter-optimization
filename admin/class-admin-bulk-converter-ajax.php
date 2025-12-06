<?php

class Admin_Bulk_Converter_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_bulk_convert_images', array( $this, 'handle_bulk_convert' ) );
	}

	public function handle_bulk_convert() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'webp_opt_nonce' ) ) {
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

			$result = $this->convert_to_webp( $file_path, $quality );

			if ( $result ) {
				$stats['converted']++;
				
				if ( $delete_original ) {
					wp_delete_attachment( $attachment->ID, true );
				}
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

	private function convert_to_webp( $file_path, $quality ) {
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
			// Add WebP to media library
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

		return $success;
	}

}
