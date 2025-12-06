<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://test
 * @since      1.0.0
 *
 * @package    Webp_Converter_Optimizer
 * @subpackage Webp_Converter_Optimizer/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Webp_Converter_Optimizer
 * @subpackage Webp_Converter_Optimizer/includes
 * @author     Shaktisinh Jadeja <jadejashakti5483@gmail.com>
 */
class Webp_Converter_Optimizer_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'webp-converter-optimizer',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
