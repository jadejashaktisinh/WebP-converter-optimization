<?php

class Admin_Image_Converter_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_convert_images', array( $this, 'handle_convert_images' ) );
		add_action( 'add_attachment', array( $this, 'auto_convert_on_upload' ) );
	}

	public function handle_convert_images() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'webp_opt_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ), 403 );
		}

		// Check user permissions
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
		}

		// Check if files were uploaded
		if ( empty( $_FILES['images'] ) ) {
			wp_send_json_error( array( 'message' => 'No images uploaded' ) );
		}

		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$settings = get_option( 'webp_optimizer_settings', array( 'default_quality' => 80 ) );
		$quality = $settings['default_quality'];

		$files = $_FILES['images'];
		$converted = array();
		$errors = array();

		// Handle multiple files
		foreach ( $files['name'] as $key => $value ) {
			if ( $files['error'][$key] !== UPLOAD_ERR_OK ) {
				$errors[] = $files['name'][$key] . ': Upload error';
				continue;
			}

			$file = array(
				'name'     => $files['name'][$key],
				'type'     => $files['type'][$key],
				'tmp_name' => $files['tmp_name'][$key],
				'error'    => $files['error'][$key],
				'size'     => $files['size'][$key]
			);

			$result = $this->convert_and_save( $file, $quality );
			
			if ( is_wp_error( $result ) ) {
				$errors[] = $file['name'] . ': ' . $result->get_error_message();
			} else {
				$converted[] = $result;
			}
		}

		wp_send_json_success( array(
			'converted' => $converted,
			'errors'    => $errors,
			'message'   => count( $converted ) . ' image(s) converted successfully'
		) );
	}

	public function auto_convert_on_upload( $attachment_id ) {
		$settings = get_option( 'webp_optimizer_settings', array() );

		// Check if auto convert is enabled
		if ( empty( $settings['auto_convert'] ) ) {
			return;
		}

		$file_path = get_attached_file( $attachment_id );
		$mime_type = get_post_mime_type( $attachment_id );

		// Check if format is supported
		$supported = array();
		if ( ! empty( $settings['supported_formats']['jpeg'] ) ) {
			$supported[] = 'image/jpeg';
		}
		if ( ! empty( $settings['supported_formats']['png'] ) ) {
			$supported[] = 'image/png';
		}
		if ( ! empty( $settings['supported_formats']['gif'] ) ) {
			$supported[] = 'image/gif';
		}

		if ( ! in_array( $mime_type, $supported ) ) {
			return;
		}

		$quality = ! empty( $settings['default_quality'] ) ? $settings['default_quality'] : 80;
		$keep_original = ! empty( $settings['keep_original'] );

		$this->convert_to_webp( $file_path, $quality, $keep_original ? 0 : $attachment_id );
	}

	private function convert_and_save( $file, $quality ) {
		// Create image resource from uploaded file
		$image_type = exif_imagetype( $file['tmp_name'] );
		
		switch ( $image_type ) {
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg( $file['tmp_name'] );
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng( $file['tmp_name'] );
				break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif( $file['tmp_name'] );
				break;
			default:
				return new WP_Error( 'invalid_type', 'Unsupported image type' );
		}

		if ( ! $image ) {
			return new WP_Error( 'conversion_failed', 'Failed to create image resource' );
		}

		// Create WebP filename
		$upload_dir = wp_upload_dir();
		$filename = pathinfo( $file['name'], PATHINFO_FILENAME ) . '.webp';
		$webp_path = $upload_dir['path'] . '/' . $filename;

		// Convert to WebP
		if ( ! imagewebp( $image, $webp_path, $quality ) ) {
			imagedestroy( $image );
			return new WP_Error( 'webp_failed', 'Failed to convert to WebP' );
		}

		imagedestroy( $image );

		// Add to media library
		$attachment = array(
			'post_mime_type' => 'image/webp',
			'post_title'     => sanitize_file_name( pathinfo( $file['name'], PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $webp_path );
		
		if ( is_wp_error( $attach_id ) ) {
			return $attach_id;
		}

		// Generate metadata
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $webp_path );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return array(
			'id'  => $attach_id,
			'url' => wp_get_attachment_url( $attach_id )
		);
	}

	private function convert_to_webp( $file_path, $quality, $delete_original_id = 0 ) {
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

			// Delete original if requested
			if ( $delete_original_id > 0 ) {
				wp_delete_attachment( $delete_original_id, true );
			}
		}

		return $success;
	}

}
