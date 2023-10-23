<?php
/**
 * Plugin Name: Pixi Contact Form for Elementor 
 * Description: Advanced Contact Form for elementor pagebuilder
 * Plugin URI:  https://wordpress.org/plugins/pixi-contact-form-for-elementor/
 * Version:     1.0.1
 * Author:      pxelcode
 * Author URI:  https://pxelcode.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:      7.2
 * Elementor tested up to:     3.5.0
 * Elementor Pro tested up to: 3.5.0
 * Text Domain: pixi-contactform
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/pxelcode01/pixi-contact-form-for-elementor
 * GitHub Branch: main
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PIXI_CONTACTFORM_VERSION', '1.0.0' );

//  INitiate plugins widgets

function pixi_contactform() {

	// Load plugin file
	require_once( __DIR__ . '/includes/plugin.php' );

	// Run the plugin
	\Pixi_contactform\Plugin::instance();

}
add_action( 'plugins_loaded', 'pixi_contactform' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pixi-contactform-activator.php
 */
function activate_pixi_contactform() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-pixi-contactform-activator.php';
	Pixi_contactform_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pixi-contactform-deactivator.php
 */
function deactivate_pixi_contactform() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-pixi-contactform-deactivator.php';
	Pixi_contactform_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pixi_contactform' );
register_deactivation_hook( __FILE__, 'deactivate_pixi_contactform' );



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/admin/class-pixi-contactform.php';



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pixi_contactform() {

	$plugin = new Pixi_contactform();
	$plugin->run();

}
run_pixi_contactform();



