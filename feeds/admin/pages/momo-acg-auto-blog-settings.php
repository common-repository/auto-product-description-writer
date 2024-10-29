<?php
/**
 * MoMo ACG - Autoblog Settings Page
 *
 * @author MoMo Themes
 * @package momoacg
 * @since v3.5.0
 */

global $momoacgwc;
?>
<div id="momo-be-form">
	<div class="momo-be-wrapper">
		<h2 class="nav-tab-wrapper"> 
			<?php do_action( 'momo_acgwc_api_shout' ); ?>
			<div class="nav-tab nav-tab-active">
				<?php esc_html_e( 'WooAI - Auto Blogging', 'momoacgwc' ); ?>
			</div>
		</h2>
		<table class="momo-be-tab-table" width="100%">
			<tbody>
				<tr>
					<td valign="top" class="momo-be-tab-menu">
						<ul class="momo-be-main-tab momo-be-block-section">
							<li><a class="momo-be-tablinks active" href="#momo-be-auto-blog-settings"><i class='bx bx-timer'></i><span><?php esc_html_e( 'Auto Blogging', 'momoacgwc' ); ?></span></a></li>
							<li><a class="momo-be-tablinks" href="#momo-be-rssfeed-editor"><i class='bx bx-rss' ></i><span><?php esc_html_e( 'RSS Feed Writer', 'momoacgwc' ); ?></span></a></li>
							<li><a class="momo-be-tablinks" href="#momo-be-rssfeed-queue-list"><i class='bx bxs-add-to-queue'></i><span><?php esc_html_e( 'Queue List', 'momoacgwc' ); ?></span></a></li>
						</ul>
					</td>
					<td class="momo-be-main-tabcontent" width="100%" valign="top">
						<div class="momo-be-working"></div>	
						<div id="momo-be-auto-blog-settings" class="momo-be-admin-content active">
							<?php require_once 'page-momo-acg-auto-blog.php'; ?>
						</div>
						<div id="momo-be-rssfeed-editor" class="momo-be-admin-content">
							<?php require_once 'page-momo-rssfeed-editor.php'; ?>
						</div>
						<div id="momo-be-rssfeed-queue-list" class="momo-be-admin-content">
							<?php require_once 'page-momo-rssfeed-queue-list.php'; ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
