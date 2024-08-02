<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://alexdenche.dev
 * @since             1.0.0
 * @package           Enhanced_Export
 *
 * @wordpress-plugin
 * Plugin Name:       Enhanced Export
 * Plugin URI:        https://alexdenche.dev
 * Description:       With this plugin you can export content from your WordPress site to a CSV or XLSX file. Compatible with custom tables.
 * Version:           1.0.0
 * Author:            Alex Denche
 * Author URI:        https://alexdenche.dev/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       enhanced-export
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
define( 'ENHANCED_EXPORT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-enhanced-export-activator.php
 */
function activate_enhanced_export() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-enhanced-export-activator.php';
	Enhanced_Export_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-enhanced-export-deactivator.php
 */
function deactivate_enhanced_export() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-enhanced-export-deactivator.php';
	Enhanced_Export_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_enhanced_export' );
register_deactivation_hook( __FILE__, 'deactivate_enhanced_export' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-enhanced-export.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_enhanced_export() {


	$plugin = new Enhanced_Export();
	$plugin->run();

}

function ee_load_dependencies() {
	// require_once plugin_dir_path( __FILE__ ) . 'libraries/action-scheduler/action-scheduler.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/deltas/class-enhanced-export-delta-exports.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/deltas/class-enhanced-export-delta-fields.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/deltas/class-enhanced-export-delta-templates.php';



	global $ee_exports_delta;
	global $ee_custom_fields_delta;
	global $ee_templates_delta;

	$ee_exports_delta = new Enhanced_Export_Delta_Exports();
	$ee_custom_fields_delta = new Enhanced_Export_Delta_Custom_Fields();
	$ee_templates_delta = new Enhanced_Export_Delta_Templates();
	
}

ee_load_dependencies();

run_enhanced_export();
