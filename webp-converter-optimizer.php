<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://test
 * @since             1.0.0
 * @package           Webp_Converter_Optimizer
 *
 * @wordpress-plugin
 * Plugin Name:       WebP Converter & Optimizer
 * Plugin URI:        https://test
 * Description:       Automatically convert and optimize all your images to modern WebP and AVIF formats. Boost page load speeds and improve your Google PageSpeed scores with smart, automated serving and browser fallbacks.
 * Version:           1.0.0
 * Author:            Shaktisinh Jadeja
 * Author URI:        https://test/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       webp-converter-optimizer
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WEBP_CONVERTER_OPTIMIZER_VERSION', '1.0.0' );


/**
 *  dist folder constant 
 */

if ( ! defined( 'WEBPOPT_BUILD_URL' ) ) {
    // Defines the full web URL path to the 'admin/build' directory.
    // 'plugin_dir_url( __FILE__ )' gets the base URL of the plugin root.
    define( 'WEBPOPT_BUILD_URL', plugin_dir_url( __FILE__ ) . 'build/' );
}

if ( ! defined( 'WEBPOPT_BUILD_PATH' ) ) {
    // Defines the absolute server file path to the 'admin/build' directory (useful for include/require)
    define( 'WEBPOPT_BUILD_PATH', plugin_dir_path( __FILE__ ) . 'build/' );
}



/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-webp-converter-optimizer-activator.php
 */
function activate_webp_converter_optimizer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-webp-converter-optimizer-activator.php';
	Webp_Converter_Optimizer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-webp-converter-optimizer-deactivator.php
 */
function deactivate_webp_converter_optimizer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-webp-converter-optimizer-deactivator.php';
	Webp_Converter_Optimizer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_webp_converter_optimizer' );
register_deactivation_hook( __FILE__, 'deactivate_webp_converter_optimizer' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-webp-converter-optimizer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_webp_converter_optimizer() {

	$plugin = new Webp_Converter_Optimizer();
	$plugin->run();

}
run_webp_converter_optimizer();
