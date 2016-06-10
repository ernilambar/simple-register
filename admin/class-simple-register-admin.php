<?php
/**
 * Simple Register.
 *
 * @package   Simple_Register_Admin
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      http://www.nilambar.net
 * @copyright 2014 Nilambar Sharma
 */

/**
 * Plugin class.
 *
 * @package Simple_Register_Admin
 * @author  Nilambar Sharma <nilambar@outlook.com>
 */
class Simple_Register_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Plugin options.
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected $options = array();

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = Simple_Register::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		$this->options = $plugin->get_options_array();

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * Define custom functionality.
		 */

		add_action('admin_init', array($this, 'plugin_register_settings'));

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Simple Register', $this->plugin_slug ),
			__( 'Simple Register', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

  /**
   * Register plugin settings
   */
  public function plugin_register_settings()
  {

    register_setting('sr-plugin-options-group', 'sr_plugin_options', array( $this, 'plugin_options_validate') );

    ////

		add_settings_section('general_settings', __( 'Simple Register Settings', 'simple-register' ) , array($this, 'plugin_section_general_text_callback'), 'simple-register-general');

		add_settings_field('sr_field_enable_simple_register', __( 'Enable Simple Register', 'simple-register' ), array($this, 'sr_field_enable_simple_register_callback'), 'simple-register-general', 'general_settings');

    ////

		add_settings_section('fields_settings', __( 'Fields Settings', 'simple-register' ) , array($this, 'plugin_section_fields_text_callback'), 'simple-register-fields');

		add_settings_field('sr_field_enable_password', __( 'Password', 'simple-register' ), array($this, 'sr_field_enable_password_callback'), 'simple-register-fields', 'fields_settings');

		add_settings_field('sr_field_enable_full_name', __( 'Full Name', 'simple-register' ), array($this, 'sr_field_enable_full_name_callback'), 'simple-register-fields', 'fields_settings');

		add_settings_field('sr_field_enable_website', __( 'Website', 'simple-register' ), array($this, 'sr_field_enable_website_callback'), 'simple-register-fields', 'fields_settings');

		add_settings_field('sr_field_enable_bio', __( 'Bio', 'simple-register' ), array($this, 'sr_field_enable_bio_callback'), 'simple-register-fields', 'fields_settings');

    ////

  }

  // validate our options
  function plugin_options_validate($input) {

		$input['sr_field_enable_simple_register']    = ( isset( $input['sr_field_enable_simple_register'] ) ) ? 1 : 0 ;
		$input['sr_field_enable_password']           = ( isset( $input['sr_field_enable_password'] ) ) ? 1 : 0 ;
		$input['sr_field_enable_full_name']          = ( isset( $input['sr_field_enable_full_name'] ) ) ? 1 : 0 ;
		$input['sr_field_enable_full_name_required'] = ( isset( $input['sr_field_enable_full_name_required'] ) ) ? 1 : 0 ;
		$input['sr_field_enable_website']            = ( isset( $input['sr_field_enable_website'] ) ) ? 1 : 0 ;
		$input['sr_field_enable_website_required']   = ( isset( $input['sr_field_enable_website_required'] ) ) ? 1 : 0 ;
		$input['sr_field_enable_bio']                = ( isset( $input['sr_field_enable_bio'] ) ) ? 1 : 0 ;

  	return $input;
  }

  function plugin_section_general_text_callback() {
  	return;
	}
	function plugin_section_fields_text_callback() {
  	echo '<p>'.__('Change your Fields settings.', 'simple-register' ).'</p>';
	}

	function sr_field_enable_simple_register_callback() {
		?>
		<input type="checkbox" name="sr_plugin_options[sr_field_enable_simple_register]" value="1"
		<?php checked(isset($this->options['sr_field_enable_simple_register']) && 1 == $this->options['sr_field_enable_simple_register']); ?> />&nbsp;<?php _e("Enable",  'simple-register' ); ?>
		<?php
	}
	//
	//
	function sr_field_enable_password_callback() {
		?>
		<input type="checkbox" name="sr_plugin_options[sr_field_enable_password]" value="1"
		<?php checked(isset($this->options['sr_field_enable_password']) && 1 == $this->options['sr_field_enable_password']); ?> />&nbsp;<?php _e("Enable",  'simple-register' ); ?>
		<?php
	}
	//
	function sr_field_enable_full_name_callback() {
		?>
		<input type="checkbox" name="sr_plugin_options[sr_field_enable_full_name]" value="1"
		<?php checked(isset($this->options['sr_field_enable_full_name']) && 1 == $this->options['sr_field_enable_full_name']); ?> />&nbsp;<?php _e("Enable",  'simple-register' ); ?>&nbsp;
		<input type="checkbox" name="sr_plugin_options[sr_field_enable_full_name_required]" value="1"
		<?php checked(isset($this->options['sr_field_enable_full_name_required']) && 1 == $this->options['sr_field_enable_full_name_required']); ?> />&nbsp;<?php _e("Make Required",  'simple-register' ); ?>

		<?php
	}
	//
	function sr_field_enable_website_callback() {
		?>
		<input type="checkbox" name="sr_plugin_options[sr_field_enable_website]" value="1"
		<?php checked(isset($this->options['sr_field_enable_website']) && 1 == $this->options['sr_field_enable_website']); ?> />&nbsp;<?php _e("Enable",  'simple-register' ); ?>&nbsp;
		<input type="checkbox" name="sr_plugin_options[sr_field_enable_website_required]" value="1"
		<?php checked(isset($this->options['sr_field_enable_website_required']) && 1 == $this->options['sr_field_enable_website_required']); ?> />&nbsp;<?php _e("Make Required",  'simple-register' ); ?>

		<?php
	}
	//
	function sr_field_enable_bio_callback() {
		?>
		<input type="checkbox" name="sr_plugin_options[sr_field_enable_bio]" value="1"
		<?php checked(isset($this->options['sr_field_enable_bio']) && 1 == $this->options['sr_field_enable_bio']); ?> />&nbsp;<?php _e("Enable",  'simple-register' ); ?>&nbsp;
		<input type="checkbox" name="sr_plugin_options[sr_field_enable_bio_required]" value="1"
		<?php checked(isset($this->options['sr_field_enable_bio_required']) && 1 == $this->options['sr_field_enable_bio_required']); ?> />&nbsp;<?php _e("Make Required",  'simple-register' ); ?>

		<?php
	}

}
