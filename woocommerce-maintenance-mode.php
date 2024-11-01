<?php
/*
 * Plugin Name: WooCommerce Maintenance Mode (Free Version)
 * Version: 2.0.1
 * Plugin URI: http://www.mattroyal.co.za/plugins/woocommerce-maintenance-mode/
 * Description: Show notifications or redirect users with the option to disable e-commerce functionality but still allow product visibility. You can set this site wide or on WooCommerce pages only thus not affecting any other parts of your website. Logged in admins will not see anything so you can continue working, testing and managing your store seemllessly without your users even being aware and you can specify a date to automatically disable the redirect/notification so you never forget.
 * Author: Matt Royal
 * Author URI: http://www.mattroyal.co.za/
 * Requires at least: 3.8
 * Tested up to: 4.9.1
 *
 * Text Domain: woocommerce-maintenance-mode
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Matt Royal
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'WooCommerce_Maintenance_Mode' ) ) {
			
	exit( __( '<p>I am sad to see you reverting to the free version, please think about sending me some feedback so I can help or make improvements <a href="http://mattroyal.co.za/contact-me/" target="_blank">(click here to mail me)</a>. If you really still want to, please first deactivate and delete the premium version of WooCommerce Maintenance Mode plugin first.</p>', 'woocommerce-maintenance-mode') );
}

require_once( 'includes/class-woomaint.php' );
require_once( 'includes/class-woomaint-settings.php' );
require_once( 'includes/lib/class-woomaint-admin-api.php' );
require_once( 'includes/lib/class-maintmode-conditions.php' );
require_once( 'includes/lib/class-woomaint-store-notice.php' );
require_once( 'includes/lib/class-woomaint-store-redirect.php' );

/**
 * Returns the main instance of WooCommerce_Maintenance_Mode to prevent the need to use globals.
 *
 * @since  2.0.0
 * @return object WooCommerce_Maintenance_Mode
 */
function WooCommerce_Maintenance_Mode () {
	$instance = WooCommerce_Maintenance_Mode::instance( __FILE__, '2.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = WooCommerce_Maintenance_Mode_Settings::instance( $instance );
	}

	return $instance;
}

WooCommerce_Maintenance_Mode();
