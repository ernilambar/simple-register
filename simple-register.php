<?php
/**
 * Simple Register.
 *
 * @package   Simple_Register
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      http://www.nilambar.net
 * @copyright 2014 Nilambar Sharma
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Register
 * Plugin URI:        http://wordpress.org/plugins/simple-register/
 * Description:       Extend your registration form easily. Show Password, Full Name, etc in registration form!
 * Version:           1.0.2
 * Author:            Nilambar Sharma
 * Author URI:        http://www.nilambar.net
 * Text Domain:       simple-register
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SIMPLE_REGISTER_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'SIMPLE_REGISTER_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'SIMPLE_REGISTER_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * Include main plugin class
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-simple-register.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Simple_Register', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Simple_Register', 'deactivate' ) );

/*
 * Create instance of the plugin
 */
add_action( 'plugins_loaded', array( 'Simple_Register', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * Include plugin admin class
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-simple-register-admin.php' );
	add_action( 'plugins_loaded', array( 'Simple_Register_Admin', 'get_instance' ) );

}
