<?php
/**
 * MoMO ACG WC - Related Product
 *
 * @author MoMo Themes
 * @package momoacgwc
 * @since v1.2.1
 */
class Momo_Acg_Related_Products {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'momo_acgwc_register_settings', array( $this, 'momo_acgwc_related_register_settings' ) );
		if ( is_admin() ) {
			add_action( 'momo_acg_wc_settings_tab', array( $this, 'momo_acg_wc_related_settings_tab' ) );
			add_action( 'momo_acg_wc_settings_tab_content', array( $this, 'momo_acg_wc_related_settings_tab_content' ) );
		}
	}
	/**
	 * Register Settings
	 */
	public function momo_acgwc_related_register_settings() {
		register_setting( 'momoacgwc-settings-related-group', 'momo_acg_wc_related_settings' );
	}
	/**
	 * Settings Tab
	 */
	public function momo_acg_wc_related_settings_tab() {
		?>
		<li><a class="momo-be-tablinks" href="#momo-be-related-momoacgwc"><i class='bx bx-grid' ></i><span><?php esc_html_e( 'Recomendation', 'momoacgwc' ); ?></span></a></li>
		<?php
	}
	/**
	 * Settings Tab Content
	 */
	public function momo_acg_wc_related_settings_tab_content() {
		?>
		<div id="momo-be-related-momoacgwc" class="momo-be-admin-content">
			<form method="post" action="options.php" id="momo-momoacg-wc-admin-settings-related-form">
				<?php settings_fields( 'momoacgwc-settings-related-group' ); ?>
				<?php do_settings_sections( 'momoacgwc-settings-relate-group' ); ?>
				<?php require_once 'pages/page-momo-acgwc-related.php'; ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
new Momo_Acg_Related_Products();
