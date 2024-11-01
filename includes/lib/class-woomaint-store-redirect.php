<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Maintenance_Mode_Store_Redirect {

	/**
	 * Site wide or woocommerce only
	 * @var 	string
	 * @access  public
	 * @since 	2.0.0
	 */
	public $scope;

	/**
	 * Redirect Url
	 * @var 	string
	 * @access  public
	 * @since 	2.0.0
	 */
	public $redirect_url;

	/**
	 * Maintenance Mode Conditions
	 * @var 	boolean
	 * @access  public
	 * @since 	2.0.0
	 */
	public $conditions;


	public function __construct () {

		add_action( 'wp', array( $this, 'maintmode_redirect' ) );
	}

	/**
	 * Set up admin messages for post type
	 * @return null
	 */
	public function maintmode_redirect() {

		$this->scope = get_option( 'woo_maint_display_scope' );
		$this->redirect_url = get_option( 'woo_maint_redirect_url' );
		$this->conditions = new WooCommerce_Maintenance_Mode_Conditions();

		if ( current_user_can( 'manage_woocommerce' ) && ! $this->conditions->is_preview ){
			return;
		} 

		if( $this->scope === 'woo' && $this->conditions->woocommerce_pages() ) {
			
			wp_redirect( $this->redirect_url, 302 );
			exit();
		}

		if( $this->scope === 'all' && ! $this->conditions->woocommerce_pages() ) {
			
			wp_redirect( $this->redirect_url, 302 );
			exit();
		}
	}

}