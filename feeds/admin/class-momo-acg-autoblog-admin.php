<?php
/**
 * Admin Init
 *
 * @package momoacg
 */
class MoMo_AutoBlog_Admin_Init {
	/**
	 * Constructor
	 */
	public function __construct() {
	}
	/**
	 * Add tab li
	 */
	public function momo_acg_add_ab_tab_li() {
		?>
		<li><a class="momo-be-tablinks" href="#momo-be-auto-blog"><i class='bx bx-timer'></i></i><span><?php esc_html_e( 'Auto Blog', 'momoacg' ); ?></span></a></li>
		<?php
	}
	/**
	 * Add tab content
	 */
	public function momo_acg_add_ab_tab_content() {
		global $momoacg;
		?>
		<div id="momo-be-auto-blog" class="momo-be-admin-content">
			<?php require_once $momoacg->plugin_path . 'autoblog/admin/pages/momo-acg-auto-blog-settings.php'; ?>
		</div>
		<?php
	}
}
new MoMo_AutoBlog_Admin_Init();
