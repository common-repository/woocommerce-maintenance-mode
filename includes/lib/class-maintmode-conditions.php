<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Maintenance_Mode_Conditions {

	/**
	 * Status active/inactive
	 * @var 	boolean
	 * @access  public
	 * @since 	2.0.0
	 */
	public $is_active;

	/**
	 * Preview plugin settings
	 * @var 	boolean
	 * @access  public
	 * @since 	2.0.0
	 */
	public $is_preview;

	/**
	 * Disable add-to-cart
	 * @var 	boolean
	 * @access  public
	 * @since 	2.0.0
	 */
	public $disable_addcart;

	/**
	 * Disable Date
	 * @var 	string
	 * @access  public
	 * @since 	2.0.0
	 */
	public $auto_disable;

	/**
	 * Current page Woocommerce
	 * @var 	boolean
	 * @access  public
	 * @since 	2.0.0
	 */
	public $woocommerce;



	public $cookie_name = 'maintmode_dismiss';

	public $display_type;

	public function __construct () {

		// Check if we are running in preview mode
		$this->preview_mode_check();

		// Check if status is active
		$this->is_active = get_option( 'woo_maint_is_active' );

		// Check the display or logic type
		$this->display_type = get_option( 'woo_maint_display_type' );

		// Check if we should disable woocommer e-commerce functionality or not
		$this->maintmode_catalog_only();
	}

	/**
	 * Test for woocommerce pages.
	 * @return boolean
	 */
	public function woocommerce_pages () {

		$this->woocommerce = is_woocommerce() || is_shop() || is_product_category() || is_product() ||
			is_product_tag() || is_cart() || is_checkout() || is_account_page();

		return $this->woocommerce;
	}

	public function preview_mode_check(){

		if( isset( $_GET['preview'] ) && $_GET['preview'] == "true" ) {
			
			$this->is_preview = true;
			add_action( 'wp_footer', array( $this, 'footer_js' ) );
		}
	}

	public function footer_js(){
		echo "<script>var querystring = 'preview=true';
			jQuery('a').each(function() {
			    var href = jQuery(this).attr('href');

			    if (href) {
			        href += (href.match(/\?/) ? '&' : '?') + querystring;
			        jQuery(this).attr('href', href);
			    }
			});
		</script>";
	}

	public function maintmode_catalog_only () {

		$this->disable_addcart = get_option( 'woo_maint_disable_addcart' );

		if ( ( $this->disable_addcart && $this->is_active ) || $this->disable_addcart && $this->is_preview){

			add_filter( 'woocommerce_is_purchasable', array( $this, 'maintmode_disable_add_cart' ) );
			add_action( 'init',array( $this, 'remove_loop_button' ) );
		}

	}

	public function maintmode_disable_add_cart () {
		return false;
	}

	public function remove_loop_button () {

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'disabled_add_to_cart_button' ) );
	}

	public function disabled_add_to_cart_button () {

		global $product;

		echo '<button type="submit" name="add-to-cart" class="button alt" disabled>' .
			esc_html( $product->single_add_to_cart_text() ) .'</button>';
	}

}