<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Maintenance_Mode_Store_Notice {

	/**
	 * Maintenance Mode Conditions
	 * @var 	object
	 * @access  public
	 * @since 	2.0.0
	 */
	public $conditions;

	public function __construct () {

		add_action( 'wp_footer', array( $this, 'maintmode_display_notice' ) );
	}

	/**
	 * Set up admin messages for post type
	 * @param  string $content Default content
	 * @return string content Modified content
	 */
	public function maintmode_display_notice () {

		$this->conditions = new WooCommerce_Maintenance_Mode_Conditions();

		if ( current_user_can( 'manage_woocommerce' ) && ! $this->conditions->is_preview ){
			return;
		} 

		$scope = get_option( 'woo_maint_display_scope' );
		$bg = get_option('woo_maint_notification_bg');
		$txt = get_option('woo_maint_notification_txt');

		$message = get_option('woo_maint_message');

		if( has_filter('woomaint_change_notice_message') ) {
			$message = apply_filters('woomaint_change_notice_message', $message);
		}

		$style = "style='background:{$bg};color:{$txt}'";
		$output = "<div class='woomaint_notice' {$style}>{$message}</div>";

		if ( $scope === 'woo' ){
			
			if( $this->conditions->woocommerce_pages() ) {

				echo $output;
			}
		}
		else {

			echo $output;
		}

	}

}
