<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Maintenance_Mode_Settings {

	/**
	 * The single instance of WooCommerce_Maintenance_Mode_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	2.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	2.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   2.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   2.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		$this->base = 'woo_maint_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {

		// WooCommerce Page
		$page = add_submenu_page( 'woocommerce', __( 'Woocommerce Maintenance | Message Mode', 'woocommerce-maintenance-mode' ), __( 'Maintenance & Messaging Mode', 'woocommerce-maintenance-mode' ), 'manage_options', $this->parent->_token . '_settings', array( $this, 'settings_page' ) ); 

		// Setting Page
		//$page = add_options_page( __( 'Plugin Settings', 'woocommerce-maintenance-mode' ) , __( 'Plugin Settings', 'woocommerce-maintenance-mode' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );

		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	// Duration picker
    	wp_register_script( $this->parent->_token . '-duration-picker', esc_url( $this->parent->assets_url ) . 'js/vendor/bootstrap-duration-picker.js', array( 'jquery' ), $this->parent->_version );
		wp_enqueue_script( $this->parent->_token . '-duration-picker' );

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ), '2.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="admin.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'woocommerce-maintenance-mode' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$display = get_option('woo_maint_display_type');

		$settings['general'] = array(
			'title'					=> __( '<i class="fa fa-cogs" aria-hidden="true"></i> General Settings', 'woocommerce-maintenance-mode' ),
			'description'			=> __( '', 'woocommerce-maintenance-mode' ),
			'fields'				=> array(
				array(
					'id' 			=> 'is_active',
					'label'			=> __( 'Active:', 'woocommerce-maintenance-mode' ),
					'description'	=> __( '', 'wordpress-plugin-template' ),
					'type'			=> 'switch',
					'default'		=> ''
				),
				array(
					'id' 			=> 'display_type',
					'label'			=> __( 'Mode:', 'woocommerce-maintenance-mode' ),
					'description'	=> __( 'What do you want to do with users accessing your woocommerce store?', 'woocommerce-maintenance-mode' ),
					'type'			=> 'radio',
					'options'		=> array(
						[ 'redirect', 'Redirect users', false ],
						[ 'notice', 'Show store notice', false ],
						[ 'modal', 'Show popup notifications <a href="http://mattroyal.co.za/royal/product/woocommerce-maintenance-mode-premium/" target="_blank">(available in premium version)</a>' , 'disabled' ]
					),
					'default'		=> 'notice'
				),
				array(
					'id' 			=> 'display_scope',
					'label'			=> __( 'Display:', 'woocommerce-maintenance-mode' ),
					'description'	=> __( 'What do you want to do with users accessing your woocommerce store?', 'woocommerce-maintenance-mode' ),
					'type'			=> 'radio',
					'options'		=> array(
						[ 'woo', 'WooCommerce pages only', false ],
						[ 'all', 'All pages', false ]
					),
					'default'		=> 'woo'
				),
				array(
					'id' 			=> 'auto_disable',
					'label'			=> __( 'Schedule:', 'woocommerce-maintenance-mode' ),
					'description'	=> __( 'Specify an end date when notices or redirects should be automatically turned off. <a href="http://mattroyal.co.za/royal/product/woocommerce-maintenance-mode-premium/" target="_blank">(available in premium version)</a>', 'woocommerce-maintenance-mode' ),
					'type'			=> 'date_picker',
					'default'		=> '',
					'placeholder'	=> __( 'end date', 'woocommerce-maintenance-mode' )
				),
				array(
					'id' 			=> 'disable_addcart',
					'label'			=> __( 'Disable e-commerce:', 'woocommerce-maintenance-mode' ),
					'description'	=> __( 'This will disable add to cart & checkout functionality. <br /> FYI - Your store will become a catalogue view only (please note this will clear customers carts).<br /> * This setting can also (sometimes) affect the preview mode. Ensure to double check by testing in a seperate browser or incognito window if you are unsure.', 'woocommerce-maintenance-mode' ),
					'type'			=> 'checkbox',
					'default'		=> ''
				)
			)
		);

		$settings['redirect'] = array(
			'title'					=> __( '<i class="fa fa-paper-plane-o" aria-hidden="true"></i> Redirects', 'woocommerce-maintenance-mode' ),
			'description'			=> __( '', 'woocommerce-maintenance-mode' ),
			'fields'				=> array(
				array(
					'id' 			=> 'redirect_url',
					'label'			=> __( 'Redirect URL:' , 'woocommerce-maintenance-mode' ),
					'description'	=> __( 'Where do you wish to redirect your users to?', 'woocommerce-maintenance-mode' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'http://google.co.za', 'woocommerce-maintenance-mode' )
				)
			)
		);

		$settings['notice'] = array(
			'title'					=> __( '<i class="fa fa-bullhorn" aria-hidden="true"></i> Store Notices', 'woocommerce-maintenance-mode' ),
			'description'			=> __( '', 'woocommerce-maintenance-mode' ),
			'fields'				=> array(
				array(
					'id' 			=> 'message',
					'label'			=> __( 'Store Notification' , 'woocommerce-maintenance-mode' ),
					'description'	=> __( 'Use shortcodes in your notifications with the <a href="http://mattroyal.co.za/royal/product/woocommerce-maintenance-mode-premium/" target="_blank">premium version</a>.<br />Comes bundled with some convenient shortcodes as well.', 'woocommerce-maintenance-mode' ),
					'type'			=> 'wp_editor'
				),
				array(
					'id' 			=> 'notification_bg',
					'label'			=> __( 'Background Colour', 'woocommerce-maintenance-mode' ),
					'description'	=> __( '', 'woocommerce-maintenance-mode' ),
					'type'			=> 'color',
					'default'		=> '#21759B'
				),
				array(
					'id' 			=> 'notification_txt',
					'label'			=> __( 'Text Colour', 'woocommerce-maintenance-mode' ),
					'description'	=> __( '', 'woocommerce-maintenance-mode' ),
					'type'			=> 'color',
					'default'		=> '#ffffff'
				)
			)
		);

		$settings['modal'] = array(
			'title'					=> __( '<i class="fa fa-list-alt" aria-hidden="true"></i> Lightbox/Popup', 'woocommerce-maintenance-mode' ),
			'description'			=> __( '' ),
			'fields'				=> array(
				array(
					'id' 			=> 'modal-prem',
					'label'			=> __( '', 'woocommerce-maintenance-mode' ),
					'description'	=> __( '', 'wordpress-plugin-template' ),
					'type'			=> 'html',
					'content'		=> 'Enable this feature when purchasing the <a href="http://mattroyal.co.za/royal/product/woocommerce-maintenance-mode-premium/" target="_blank">premium version</a>.'
				)
			)
		);

		// Preview content variable based on display type
		if($display == 'redirect') {
			
			$preview_content = '<h3>Sorry, Inline Preview is disabled for Redirect Mode. </h3>
			<p>This feature uses iframes and many sites do not allow you to access them through an iframe (for security reasons).</p>
			<p>&nbsp;</p>
			<p>Use the Live Preview feature instead: </p>
			<p>&nbsp;</p>
			<a href="' . home_url() . '?preview=true" class="button button-primary button-large" target="_blank"><i class="fa fa-desktop" aria-hidden="true"></i> Live Preview</a>';
		}
		else{
			$preview_content = __( '<iframe src="' . home_url() . '?preview=true" style="width:100%; height: 70vh;"></iframe>', 'woocommerce-maintenance-mode' );
		}

		$settings['preview'] = array(
			'title'					=> __( '<i class="fa fa-desktop" aria-hidden="true"></i> Inline Preview', 'woocommerce-maintenance-mode' ),
			'description'			=> __( '', 'woocommerce-maintenance-mode' ),
			'fields'				=> array(
				array(
					'id' 			=> 'preview',
					'label'			=> __( '', 'woocommerce-maintenance-mode' ),
					'description'	=> __( '' ),
					'type'			=> 'html',
					'content'		=> $preview_content
				),
			)
		);

		$settings['support'] = array(
			'title'					=> __( '<i class="fa fa-life-ring" aria-hidden="true"></i> Support', 'woocommerce-maintenance-mode' ),
			'description'			=> __( '', 'woocommerce-maintenance-mode' ),
			'fields'				=> array(
				array(
					'id' 			=> 'preview',
					'label'			=> __( '', 'woocommerce-maintenance-mode' ),
					'description'	=> __( '' ),
					'type'			=> 'html',
					'content'		=> '<p>Please use support via the plugin page on WordPress.Org</p>
						<p>&nbsp;</p>
						<a href="https://wordpress.org/support/plugin/woocommerce-maintenance-mode" class="button button-primary button-large" target="_blank"><i class="fa fa-trophy" aria-hidden="true"></i> Support</a>
						<p>&nbsp;</p>
						<p> Want to jump the queue? Options for priority support available as well.</p>
						<p>&nbsp;</p>
						<a href="http://mattroyal.co.za/my-plugins/" class="button button-primary button-large" target="_blank"><i class="fa fa-trophy" aria-hidden="true"></i> Priority Support Options</a'
				),
			)
		);

		$img_style = 'margin: 20px; border: 3px solid #0F74A8; padding: 10px; background: #fff;}';

		$settings['premium'] = array(
			'title'					=> __( 'Premium Version', 'woocommerce-maintenance-mode' ),
			'description'			=> __( 'The premium version has the following features: <br /><br /><a href="http://mattroyal.co.za/royal/product/woocommerce-maintenance-mode-premium/" target="_blank">Purchase the premium version now!</a>', 'woocommerce-maintenance-mode' ),
			'fields'				=> array(
				array(
					'id' 			=> 'premium_features',
					'label'			=> __( '', 'woocommerce-maintenance-mode' ),
					'description'	=> __( '' ),
					'type'			=> 'html',
					'content'		=> __( '<ul>
						<li><p><strong>Popup display options</p></strong><br /><img style="' .$img_style . '" src="' .  esc_url( $this->parent->assets_url ) . 'img/popup.png" /></li>
						<li><p><strong>Control the display frequency (cookie options)</p></strong><br /><img style="' .$img_style . '" src="' .  esc_url( $this->parent->assets_url ) . 'img/cookies.png" /></li>
						<li><p><strong>Use Shortcodes within notice and popup editors</p></strong></p>
						<br /><img style="' .$img_style . '" src="' .  esc_url( $this->parent->assets_url ) . 'img/use_shortcodes.png" /></li>
						<li><p><strong>Comes with bundled Shortcodes for your convenience</p></strong><br /><img style="' .$img_style . '" src="' .  esc_url( $this->parent->assets_url ) . 'img/shortcodes.png" /></li>
						<li><p><strong>Option to scheduled automatic disabling of notices/redirects</strong></p>
						<br /><img style="' .$img_style . '" src="' .  esc_url( $this->parent->assets_url ) . 'img/date_deactivation.png" /></li>
					</ul>
					<p><a href="http://mattroyal.co.za/royal/product/woocommerce-maintenance-mode-premium/" target="_blank">Purchase the premium version now!</a></p>', 'woocommerce-maintenance-mode' )
				),
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";

		$html .= '<h2>Maintenance & Messaging Mode</h2><small>Maintenance Mode, Coming Soon display options, messaging & promoitions display options.</small><hr /><a href="' . home_url() .'?preview=true" class="button button-primary button-large right" style="position: fixed; right: 1.5em; top: 3.5em;" target="_blank"><i class="fa fa-desktop" aria-hidden="true"></i> Live Preview</a>';

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . $data['title'] . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'woocommerce-maintenance-mode' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main WooCommerce_Maintenance_Mode_Settings Instance
	 *
	 * Ensures only one instance of WooCommerce_Maintenance_Mode_Settings is loaded or can be loaded.
	 *
	 * @since 2.0.0
	 * @static
	 * @see WooCommerce_Maintenance_Mode()
	 * @return Main WooCommerce_Maintenance_Mode_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}

		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
