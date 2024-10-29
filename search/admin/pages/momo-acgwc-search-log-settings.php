<?php
/**
 * Search Log Settings Page
 *
 * @author MoMo Themes
 * @package momoacgwc
 * @since v1.2.4
 */

global $momoacgwc;
?>
<div id="momo-be-form">
	<div class="momo-be-wrapper">
		<?php do_action( 'momo_acgwc_api_shout' ); ?>
		<h2 class="nav-tab-wrapper">  
			<div class="nav-tab nav-tab-active">
				<?php esc_html_e( 'WooAI - Search Log', 'momoacgwc' ); ?>
			</div>
		</h2>
		<table class="momo-be-tab-table" width="100%">
			<tbody>
				<tr>
					<td valign="top" class="momo-be-tab-menu">
						<ul class="momo-be-main-tab momo-be-block-section">
							<li><a class="momo-be-tablinks active" href="#momo-be-search-log"><i class='bx bx-search-alt-2' ></i><span><?php esc_html_e( 'Search Log', 'momoacgwc' ); ?></span></a></li>
							<li><a class="momo-be-tablinks" href="#momo-be-sales-mail"><i class='bx bx-envelope' ></i><span><?php esc_html_e( 'Sales Mail', 'momoacgwc' ); ?></span></a></li>
						</ul>
					</td>
					<td class="momo-be-main-tabcontent" width="100%" valign="top">
						<div class="momo-be-working"></div>	
						<div id="momo-be-search-log" class="momo-be-admin-content active">
							<form method="post" action="options.php" id="momo-momoacg-wc-admin-settings-searchlog-form">
								<?php settings_fields( 'momoacgwc-settings-searchlog-group' ); ?>
								<?php do_settings_sections( 'momoacgwc-settings-searchlog-group' ); ?>
								<?php require_once 'page-momo-acgwc-search-log.php'; ?>
								<?php submit_button(); ?>
							</form>
						</div>
						<div id="momo-be-sales-mail" class="momo-be-admin-content">
							<?php require_once 'page-momo-acgwc-sales-mail.php'; ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
