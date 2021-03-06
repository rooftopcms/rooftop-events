<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.rooftopcms.com
 * @since             1.0.0
 * @package           Rooftop_Events
 *
 * @wordpress-plugin
 * Plugin Name:       Rooftop Events
 * Plugin URI:        https://github.com/rooftopcms/rooftop-events
 * Description:       The Events plugin allows you to create and manage events, availability and prices.
 * Version:           1.2.1
 * Author:            RooftopCMS
 * Author URI:        https://www.rooftopcms.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       rooftop-events
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rooftop-events-activator.php
 */
function activate_Rooftop_Events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rooftop-events-activator.php';
	Rooftop_Events_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rooftop-events-deactivator.php
 */
function deactivate_Rooftop_Events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rooftop-events-deactivator.php';
	Rooftop_Events_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Rooftop_Events' );
register_deactivation_hook( __FILE__, 'deactivate_Rooftop_Events' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rooftop-events.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_Rooftop_Events() {

	$plugin = new Rooftop_Events();
	$plugin->run();

}
run_Rooftop_Events();
