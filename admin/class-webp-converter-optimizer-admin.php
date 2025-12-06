<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://test
 * @since      1.0.0
 *
 * @package    Webp_Converter_Optimizer
 * @subpackage Webp_Converter_Optimizer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Webp_Converter_Optimizer
 * @subpackage Webp_Converter_Optimizer/admin
 * @author     Shaktisinh Jadeja <jadejashakti5483@gmail.com>
 */
class Webp_Converter_Optimizer_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		// Only load on our plugin page
		if ( 'toplevel_page_webp-optimizer-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/webp-converter-optimizer-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		// Only load on our plugin page
		if ( 'toplevel_page_webp-optimizer-settings' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'react', 'https://unpkg.com/react@18/umd/react.production.min.js', array(), '18', true );
		wp_enqueue_script( 'react-dom', 'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js', array( 'react' ), '18', true );
		
		wp_enqueue_script(
			'admin-menu-bundle',
			WEBPOPT_BUILD_URL . 'bundle.js',
			array( 'react', 'react-dom' ),
			$this->version,
			true
		);

		wp_localize_script(
			'admin-menu-bundle',
			'webpOptData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'webp_opt_nonce' ),
			)
		);

	}

}
