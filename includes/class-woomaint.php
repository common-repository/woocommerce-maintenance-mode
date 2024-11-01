<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Maintenance_Mode {

	/**
	 * The single instance of WooCommerce_Maintenance_Mode.
	 * @var 	object
	 * @access  private
	 * @since 	2.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   2.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   2.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   2.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   2.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   2.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   2.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   2.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   2.0.0
	 */
	public $script_suffix;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   2.0.0
	 */
	public $mode_type;

	/**
	 * Conditions class object.
	 * @var     object
	 * @access  public
	 * @since   2.0.0
	 */
	public $conditions;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '2.0.0' ) {

		$this->_version = $version;
		$this->_token = 'woocommerce_maintenance_mode';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new WooCommerce_Maintenance_Mode_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Logic/display to be run
		$this->maint_mode_type();

	} // End __construct ()

	/**
	 * Check to see if WooCommerce is activated
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function woocommerce_activation_check (){

		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$active = in_array( 'woocommerce/woocommerce.php', $active_plugins );

		if( ! $active ){

			exit( __( 'This plugin requires WooCommerce to be installed & activated!', 'woocommerce-maintenance-mode' ) );
		}
	}

	/**
	 * The display or logic to be used as set by the user in the backend
	 * @access  public
	 * @since   2.0.0
	 * @return void
	 */
	public function maint_mode_type () {

		// This needs to be run earlier incase the disable add-to-cart/ catelog only option is selected.
		$this->conditions = new WooCommerce_Maintenance_Mode_Conditions();


		if ( $this->conditions->is_active || $this->conditions->is_preview ) {

			add_action( 'admin_notices', array( $this, 'woocommerce_maintmode_admin_notice') );
			add_filter('body_class', array( $this, 'wooomaint_add_active_body_class' ) );

			$this->mode_type = get_option( 'woo_maint_display_type' );

			if ( $this->mode_type === 'redirect' ) {
				
				new WooCommerce_Maintenance_Mode_Store_Redirect();
			}
			else {

				new WooCommerce_Maintenance_Mode_Store_Notice();
			}
		}		
	}

	/**
	 * Adds a html class to the body when maintenance mode is active
	 * @access  public
	 * @since   2.0.0
	 * @return updated array of body classes
	 */
	public function wooomaint_add_active_body_class ( $classes ) {
        
        $classes[] = 'woocommerce-maintenance-mode-active';
        
        return $classes;
	}

	/**
	 * Admin note when plugin is active and end date is not in the past.
	 * @access  public
	 * @since   2.0.0
	 * @return void
	 */
	public function woocommerce_maintmode_admin_notice () {

		echo '<div id="message" class="updated woocommerce-message" style="border-left-color: #cc99c2!important;"><p>' . __( 'WooCommerce Maintenance & Notification Mode is active!', 'woocommerce-maintenance-mode' ) . ' <a href="admin.php?page=' . $this->_token . '_settings" style="float:right;">' . __( ' <i class="fa fa-wrench" aria-hidden="true"></i> deactivate or update settings', 'woocommerce-maintenance-mode' ) . '</a></p></div>';
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   2.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
		
		wp_register_style( $this->_token . '-fawesome', esc_url( $this->assets_url ) . 'css/font-awesome.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-fawesome' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );

	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'woocommerce-maintenance-mode', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'woocommerce-maintenance-mode';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main WooCommerce_Maintenance_Mode Instance
	 *
	 * Ensures only one instance of WooCommerce_Maintenance_Mode is loaded or can be loaded.
	 *
	 * @since 2.0.0
	 * @static
	 * @see WooCommerce_Maintenance_Mode()
	 * @return Main WooCommerce_Maintenance_Mode instance
	 */
	public static function instance ( $file = '', $version = '2.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}

		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	public function install () {
		
		$this->woocommerce_activation_check();

		$this->_log_version_number();

	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   2.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
