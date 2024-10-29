<?php
/**
 * Admin Init
 *
 * @package momoacg
 */
class MoMo_ACGWC_Search_Admin {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'momo_acgwc_register_settings', array( $this, 'momo_acgwc_searchlog_register_settings' ) );
		add_action( 'momo_add_submenu_to_momoacgwc', array( $this, 'momo_add_submenu_of_searchlog' ), 13 );

		add_action( 'admin_enqueue_scripts', array( $this, 'momoacg_searchlog_print_admin_ss' ) );

		add_filter( 'momo_acgwc_add_data_to_admin_locale', array( $this, 'momo_cb_add_some_locale' ) );
	}
	/**
	 * Add some locale data
	 *
	 * @param array $ajaxdata Default datas.
	 */
	public function momo_cb_add_some_locale( $ajaxdata ) {
		$ajaxdata['edit_email_template'] = esc_html__( 'Edit email template', 'momoacgwc' );
		return $ajaxdata;
	}
	/**
	 * Register Settings
	 */
	public function momo_acgwc_searchlog_register_settings() {
		register_setting( 'momoacgwc-settings-searchlog-group', 'momo_acg_wc_searchlog_settings' );
	}
	/**
	 * Adds Submenu
	 */
	public function momo_add_submenu_of_searchlog() {
		global $momoacgwc;
		add_submenu_page(
			'momoacgwc',
			esc_html__( 'WooAI Sales Mail', 'momoacgwc' ),
			'Sales Mail',
			'manage_options',
			'momoacgwc-search',
			array( $this, 'wooai_searchlog_add_admin_settings_page' )
		);
	}
	/**
	 * Settings Page
	 */
	public function wooai_searchlog_add_admin_settings_page() {
		global $momoacgwc;
		include_once $momoacgwc->plugin_path . 'search/admin/pages/momo-acgwc-search-log-settings.php';
	}
	/**
	 * Enqueue script and styles
	 */
	public function momoacg_searchlog_print_admin_ss() {
		$current_screen = get_current_screen();
		if ( isset( $current_screen->base ) && 'woo-ai_page_momoacgwc-search' === $current_screen->base ) {
			global $momoacgwc;
			wp_enqueue_style( 'momoacg_searchlog_admin', $momoacgwc->plugin_url . 'search/assets/momoacgwcsearchlog.css', array(), $momoacgwc->version );
			wp_register_script( 'momoacg_searchlog_admin', $momoacgwc->plugin_url . 'search/assets/momoacgwcsearchlog.js', array( 'jquery', 'wp-tinymce' ), $momoacgwc->version, true );
			wp_enqueue_script( 'momoacg_searchlog_admin' );
			$ajaxurl = array(
				'ajaxurl'            => admin_url( 'admin-ajax.php' ),
				'momoacg_ajax_nonce' => wp_create_nonce( 'momoacg_security_key' ),
			);
			wp_localize_script( 'momoacg_searchlog_admin', 'momoacg_searchlog_admin', $ajaxurl );
		}
	}
}
new MoMo_ACGWC_Search_Admin();
