<?php
/**
 * Admin Init
 *
 * @package momoacg
 */
class MoMo_RssFeed_Admin_Init {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'momo_add_submenu_to_momoacgwc', array( $this, 'momo_add_submenu_of_autoblog' ), 13 );

		add_action( 'admin_init', array( $this, 'momorssfeed_register_settings' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'momoacg_rssfeed_print_admin_ss' ) );
		add_filter( 'momo_acg_add_extra_screens_for_body_class', array( $this, 'momo_acg_add_screen_id' ), 10, 1 );
	}
	/**
	 * Add screen id for styles
	 *
	 * @param array $screen_ids Current Ids.
	 */
	public function momo_acg_add_screen_id( $screen_ids ) {
		$screen_id    = 'woo-ai_page_momorssfeed';
		$screen_ids[] = $screen_id;
		return $screen_ids;
	}
	/**
	 * Register momoacg Settings
	 */
	public function momorssfeed_register_settings() {
		register_setting( 'momoacgwc-settings-rssfeed-group', 'momowc_rssfeed_openai_settings' );
		register_setting( 'momoacgwc-settings-autoblog-group', 'momowc_autoblog_openai_settings' );
	}
	/**
	 * Set Admin Menu
	 */
	public function momo_add_submenu_of_autoblog() {
		global $momoacgwc;
		add_submenu_page(
			'momoacgwc',
			esc_html__( 'MoMo Auto Blog', 'momoacgwc' ),
			'Auto Blog',
			'manage_options',
			'momoacgwc-feeds',
			array( $this, 'momorssfeed_add_admin_settings_page' )
		);
	}
	/**
	 * Settings Page
	 */
	public function momorssfeed_add_admin_settings_page() {
		global $momoacgwc;
		include_once $momoacgwc->plugin_path . 'feeds/admin/pages/momo-acg-auto-blog-settings.php';
	}
	/**
	 * Enqueue script and styles
	 */
	public function momoacg_rssfeed_print_admin_ss() {
		global $momoacgwc;
		wp_enqueue_style( 'momoacg_rssfeed_admin', $momoacgwc->plugin_url . 'feeds/assets/css/momo_rssfeed.css', array(), $momoacgwc->version );
		wp_register_script( 'momoacg_rssfeed_admin', $momoacgwc->plugin_url . 'feeds/assets/js/momo_rssfeed.js', array( 'jquery' ), $momoacgwc->version, true );
		wp_enqueue_script( 'momoacg_rssfeed_admin' );
		$ajaxurl = array(
			'ajaxurl'            => admin_url( 'admin-ajax.php' ),
			'momoacg_ajax_nonce' => wp_create_nonce( 'momoacg_security_key' ),
			'empty_feed_url'     => esc_html__( 'RSS feed url field is empty. Please enter URL before processing.', 'momoacgwc' ),
			'empty_tag_field'    => esc_html__( 'Tag field is empty. Please add some tag(s) before processing.', 'momoacgwc' ),
			'generating_titles'  => esc_html__( 'Generating title(s)', 'momoacgwc' ),
			'generating_cron'    => esc_html__( 'Generating cron job(s)', 'momoacgwc' ),
		);
		wp_localize_script( 'momoacg_rssfeed_admin', 'momoacg_rssfeed_admin', $ajaxurl );
	}
}
new MoMo_RssFeed_Admin_Init();
