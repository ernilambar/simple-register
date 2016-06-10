<?php
/**
 * Simple Register.
 *
 * @package   Simple_Register
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      http://www.nilambar.net
 * @copyright 2014 Nilambar Sharma
 */

/**
 * Plugin class
 *
 * @package Simple_Register
 * @author  Nilambar Sharma <nilambar@outlook.com>
 */
class Simple_Register {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.2';

	/**
	 * Unique identifier of the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'simple-register';

	/**
	 * Unique option name of the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_option_name = 'sr_plugin_options';

	/**
	 * Default options of the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected static $default_options = null ;

	/**
	 * Plugin options.
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected $options = array();

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		self :: $default_options = array(
		    'sr_field_enable_simple_register' => 1,
		    'sr_field_enable_password' => 1,
		    'sr_field_enable_full_name' => 0,
		    'sr_field_enable_full_name_required' => 0,
		    'sr_field_enable_website' => 0,
		    'sr_field_enable_website_required' => 0,
		    'sr_field_enable_bio' => 0,
		    'sr_field_enable_bio_required' => 0,
		);

		// Set Default options of the plugin
		$this -> _setDefaultOptions();

		// Populate current options
    $this->_getCurrentOptions();

		/*
		 * Define custom functionality.
		 */
		if ( $this->options['sr_field_enable_simple_register'] ) {
			// Only if Enable Simple Register option is true
			add_action( 'register_form', array( $this, 'simple_register_show_extra_register_fields' ) );
			add_action( 'register_post', array( $this, 'simple_register_check_extra_register_fields' ), 10, 3 );
			add_action( 'user_register', array( $this, 'simple_register_save_extra_register_fields' ), 100  );

			add_filter( 'gettext', array( $this, 'simple_register_edit_password_email_text' ) );
			add_filter( 'shake_error_codes', array( $this, 'simple_register_shake_error_codes' ) );
    }

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
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
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {

		update_option(self :: $plugin_option_name, self :: $default_options);
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// Deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Display Extra fields in Registration form.
	 *
	 * @since    1.0.0
	 */

	public function simple_register_show_extra_register_fields() {
		$flag_http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
		?>

		<?php if ( $this->options['sr_field_enable_password'] ): ?>
		<p>
		  <label for="password"><?php _e( 'Password', 'simple-register' )?><br/>
		  <input id="password" class="input" type="password" value="" name="password" />
		  </label>
		  <span class="description"><?php echo sprintf(__( 'Password should be at least %d characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like %s.', 'simple-register' ), 7, '! " ? $ % ^ & )' ) ?>
		  </span>
		</p>
		<p>
		  <label for="confirm_password"><?php _e( 'Confirm Password', 'simple-register' )?><br/>
		  <input id="confirm_password" class="input" type="password" value="" name="confirm_password" />
		  </label>
		</p>
		<?php endif;  ?>

		<?php if ( $this->options['sr_field_enable_full_name'] ): ?>
		<p>
			<?php $first_name = ($flag_http_post && isset($_POST['first_name']) && '' !== $_POST['first_name'] ) ? $_POST['first_name'] : '' ; ?>
		  <label for="first_name"><?php _e( 'First Name', 'simple-register' )?><br/>
		  <input id="first_name" class="input" type="text" value="<?php echo esc_attr(wp_unslash($first_name)); ?>" name="first_name" />
		  </label>
		</p>
		<p>
			<?php $last_name = ($flag_http_post && isset($_POST['last_name']) && '' !== $_POST['last_name'] ) ? $_POST['last_name'] : '' ; ?>
		  <label for="last_name"><?php _e( 'Last Name', 'simple-register' )?><br/>
		  <input id="last_name" class="input" type="text" value="<?php echo esc_attr(wp_unslash($last_name)); ?>" name="last_name" />
		  </label>
		</p>
		<?php endif;  ?>

		<?php if ( $this->options['sr_field_enable_website'] ): ?>
		<p>
			<?php $url = ($flag_http_post && isset($_POST['url']) && '' !== $_POST['url'] ) ? $_POST['url'] : '' ; ?>
		  <label for="url"><?php _e( 'Website', 'simple-register' )?><br/>
		  <input id="url" class="input" type="text" value="<?php echo esc_attr(wp_unslash($url)); ?>" name="url" />
		  </label>
		</p>
		<?php endif;  ?>

		<?php if ( $this->options['sr_field_enable_bio'] ): ?>
		<p>
			<?php $bio = ($flag_http_post && isset($_POST['bio']) && '' !== $_POST['bio'] ) ? $_POST['bio'] : '' ; ?>
		  <label for="bio"><?php _e( 'Bio', 'simple-register' )?><br/>
		  <textarea name="bio" id="bio" cols="30" rows="4"><?php echo esc_textarea(wp_unslash($bio)); ?></textarea>
		  </label>
		</p>
		<?php endif;  ?>

		<?php
	}

	/**
	 * Validation of extra fields.
	 *
	 * @since    1.0.0
	 */

	public function simple_register_check_extra_register_fields( $login, $email, $errors ) {
		// Password
		if ( $this->options['sr_field_enable_password'] ){
			if ( strlen( $_POST['password'] ) < 7 ) {
			  $errors->add( 'password_too_short', sprintf(__('<strong>ERROR</strong>: Password must be at least %d characters long.', 'simple-register' ), 7)  );
			}
			if ( $_POST['password'] !== $_POST['confirm_password'] ) {
			  $errors->add( 'passwords_not_matched', __('<strong>ERROR</strong>: Passwords must be matched.', 'simple-register' ) );
			}
		}

		// Full Name
		if ( $this->options['sr_field_enable_full_name'] ){
			if ( $this->options['sr_field_enable_full_name_required'] ){
				if ( '' == $_POST['first_name'] ) {
				  $errors->add( 'first_name_required', __( '<strong>ERROR</strong>: Please enter first name.', 'simple-register' ) );
				}
				if ( '' == $_POST['last_name'] ) {
				  $errors->add( 'last_name_required', __( '<strong>ERROR</strong>: Please enter last name.', 'simple-register' ) );
				}
			}
		}

		// Website
		if ( $this->options['sr_field_enable_website'] ){
			if ( $this->options['sr_field_enable_website_required'] ){
				if ( '' == $_POST['url'] ) {
				  $errors->add( 'url_required', __( '<strong>ERROR</strong>: Please enter website.', 'simple-register' ) );
				}
			}
		}

		// Bio
		if ( $this->options['sr_field_enable_bio'] ){
			if ( $this->options['sr_field_enable_bio_required'] ){
				if ( '' == $_POST['bio'] ) {
				  $errors->add( 'bio_required', __( '<strong>ERROR</strong>: Please enter bio.', 'simple-register' ) );
				}
			}
		}

	}

	/**
	 * Save value of extra fields.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function simple_register_save_extra_register_fields( $user_id ){
		$userdata = array();

		$userdata['ID'] = $user_id;

		// Password
		if ( $this->options['sr_field_enable_password'] ){
			if ( $_POST['password'] !== '' ) {
			  $userdata['user_pass'] = $_POST['password'];
			}
		}
		// Website
		if ( $this->options['sr_field_enable_website'] ){
			if ( $_POST['url'] !== '' ) {
			  $userdata['user_url'] = esc_url( $_POST['url'] );
			}
		}
		$new_user_id = wp_update_user( $userdata );
		// Full Name
		if ( $this->options['sr_field_enable_full_name'] ){
			if ( $_POST['first_name'] !== '' ) {
				update_user_meta($user_id, 'first_name' , esc_attr( $_POST['first_name'] ) );
			}
			if ( $_POST['last_name'] !== '' ) {
				update_user_meta($user_id, 'last_name' , esc_attr( $_POST['last_name'] ) );
			}
		}
		// Bio
		if ( $this->options['sr_field_enable_bio'] ){
			if ( $_POST['bio'] !== '' ) {
				update_user_meta($user_id, 'description' , esc_textarea( $_POST['bio'] ) );
			}
		}


	}

	/**
	 * Change text displayed in the registration form.
	 *
	 * @since    1.0.0
	 *
	 * @return    string    Text
	 */
	public function simple_register_edit_password_email_text( $text ) {

		if ( $text == 'A password will be e-mailed to you.' ) {
			$text = __( 'If you leave password fields empty, one will be generated for you and sent in your email.', 'simple-register' ) ;
		}
		return $text;
	}

	/**
	 * Filter for shaking codes
	 *
	 * @since    1.0.1
	 *
	 * @return    string    Array
	 */
	public function simple_register_shake_error_codes( $shake_error_codes ) {

		$sr_codes = array(
		      'password_too_short',
		      'passwords_not_matched',
		      'first_name_required',
		      'last_name_required',
		      'url_required',
		      'bio_required',
		      );
    $shake_error_codes = array_merge( $shake_error_codes, $sr_codes );
    return $shake_error_codes;

	}

	/**
	 * Return current options.
	 *
	 * @since    1.0.0
	 *
	 * @return    array Current options
	 */
  public function get_options_array(){
		return $this->options;
	}

	// Private STARTS

	/**
	 * Populate current options.
	 *
	 * @since    1.0.0
	 */
	private function _getCurrentOptions() {
		$options = array_merge( self :: $default_options , (array) get_option( self :: $plugin_option_name, array() ) );
    $this->options = $options;
  }

  /**
   * Get default options and saves in options table.
   *
   * @since    1.0.0
   */
  private function _setDefaultOptions() {
      if( !get_option( self :: $plugin_option_name ) ) {
          update_option( self :: $plugin_option_name, self :: $default_options);
      }
  }
	// Private ENDS


}
