<?php
/**
 * WC Product Block Enable
 *
 * @package momoacgwc
 * @author MoMo Themes
 * @since v1.0.0
 */
class MoMo_ACG_WC_Block {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_loaded', array( $this, 'momo_enable_gutenberg_woo_product' ), 9999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_on_product_edit_page' ) );
	}
	/**
	 * A function to enqueue scripts on the product edit page.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts_on_product_edit_page( $hook ) {
		global $momoacgwc;
		global $post;

		// Check if we're on the product edit page.
		if ( 'post-new.php' === $hook || 'post.php' === $hook ) {
			if ( 'product' === $post->post_type ) {
				// Enqueue your scripts or styles here.
				wp_enqueue_style( 'momoacgwc_oepnai', $momoacgwc->plugin_url . 'assets/css/momo_wc_product.css', array(), $momoacgwc->version );
			}
		}
	}

	/**
	 * Enable Gutenberg for Woocommerce
	 */
	public function momo_enable_gutenberg_woo_product() {
		remove_filter( 'gutenberg_can_edit_post_type', 'WC_Post_Types::gutenberg_can_edit_post_type', 10 );
		remove_filter( 'use_block_editor_for_post_type', 'WC_Post_Types::gutenberg_can_edit_post_type', 10 );
	}
}
new MoMo_ACG_WC_Block();
