<?php

/**
 * Settings AJAX Handler
 *
 * @package    Webp_Converter_Optimizer
 * @subpackage Webp_Converter_Optimizer/admin
 */
class Admin_Settings_Ajax {

	/**
	 * Settings option name.
	 *
	 * @since 1.0.0
	 */
	const OPTION_NAME = 'webp_optimizer_settings';

	/**
	 * Initialize the class and set up hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_get_webp_settings', array( $this, 'get_settings' ) );
		add_action( 'wp_ajax_save_webp_settings', array( $this, 'save_settings' ) );
	}

	/**
	 * Get plugin settings via AJAX.
	 *
	 * @since 1.0.0
	 */
	public function get_settings() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'webp_opt_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ), 403 );
		}

		// Check user permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
		}

		$defaults = array(
			'default_quality'    => 80,
			'auto_convert'       => false,
			'keep_original'      => true,
			'batch_size'         => 10,
			'cdn_enabled'        => false,
			'cdn_url'            => '',
			'supported_formats'  => array(
				'jpeg' => true,
				'png'  => true,
				'gif'  => true,
			),
		);

		$settings = get_option( self::OPTION_NAME, $defaults );

		wp_send_json_success( $settings );
	}

	/**
	 * Save plugin settings via AJAX.
	 *
	 * @since 1.0.0
	 */
	public function save_settings() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'webp_opt_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ), 403 );
		}

		// Check user permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
		}

		if ( ! isset( $_POST['settings'] ) ) {
			wp_send_json_error( array( 'message' => 'No settings provided' ) );
		}

		$settings_raw = sanitize_text_field( wp_unslash( $_POST['settings'] ) );
		$settings = json_decode( stripslashes( $settings_raw ), true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( array( 'message' => 'Invalid settings format' ) );
		}

		// Sanitize settings
		$sanitized = array(
			'default_quality'   => intval( $settings['default_quality'] ),
			'auto_convert'      => (bool) $settings['auto_convert'],
			'keep_original'     => (bool) $settings['keep_original'],
			'batch_size'        => intval( $settings['batch_size'] ),
			'cdn_enabled'       => (bool) $settings['cdn_enabled'],
			'cdn_url'           => esc_url_raw( $settings['cdn_url'] ),
			'supported_formats' => array(
				'jpeg' => (bool) $settings['supported_formats']['jpeg'],
				'png'  => (bool) $settings['supported_formats']['png'],
				'gif'  => (bool) $settings['supported_formats']['gif'],
			),
		);

		update_option( self::OPTION_NAME, $sanitized );

		wp_send_json_success( array( 'message' => 'Settings saved successfully!' ) );
	}

}
