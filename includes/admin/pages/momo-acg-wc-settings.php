<?php
/**
 * MoMo ACG - Admin Settings Page
 *
 * @author MoMo Themes
 * @package momoacgwc
 * @since v1.0.0
 */

?>
<div id="momo-be-form">
	<div class="momo-be-wrapper">
		<?php do_action( 'momo_acgwc_api_shout' ); ?>
		<h2 class="nav-tab-wrapper">  
			<div class="nav-tab nav-tab-active">
				<?php esc_html_e( 'MoMo Themes - Woo Product Writer', 'momoacgwc' ); ?>
			</div>
		</h2>
		<table class="momo-be-tab-table">
			<tbody>
				<tr>
					<td valign="top">
						<ul class="momo-be-main-tab">
							<li><a class="momo-be-tablinks active" href="#momo-be-settings-openai"><i class='bx openai-icon-custom'></i><span><?php esc_html_e( 'Settings', 'momoacgwc' ); ?></span></a></li>
							<li><a class="momo-be-tablinks" href="#momo-be-edit-settings"><i class='bx bxs-credit-card-front'></i><span><?php esc_html_e( 'Product Edit', 'momoacgwc' ); ?></span></a></li>
							<li><a class="momo-be-tablinks" href="#momo-be-instructions-momoacgwc"><i class='bx bx-notepad' ></i><span><?php esc_html_e( 'Instructions', 'momoacgwc' ); ?></span></a></li>
							<?php do_action( 'momo_acg_wc_settings_tab' ); ?>
						</ul>
					</td>
					<td class="momo-be-main-tabcontent" width="100%" valign="top">
						<div class="momo-be-working"></div>	
						<div id="momo-be-settings-openai" class="momo-be-admin-content active momo-mt-m15">
							<form method="post" action="options.php" id="momo-momoacg-wc-admin-settings-form">
								<?php settings_fields( 'momoacgwc-settings-openai-group' ); ?>
								<?php do_settings_sections( 'momoacgwc-settings-openai-group' ); ?>
								<?php require_once 'page-momo-acgwc-openai.php'; ?>
								<?php submit_button(); ?>
							</form>
						</div>
						<div id="momo-be-edit-settings" class="momo-be-admin-content">
							<form method="post" action="options.php" id="momo-momoacg-wc-admin-settings-edit-form">
								<?php settings_fields( 'momoacgwc-settings-edit-product-group' ); ?>
								<?php do_settings_sections( 'momoacgwc-settings-edit-product-group' ); ?>
								<?php require_once 'page-momo-acgwc-edit-settings.php'; ?>
								<?php submit_button(); ?>
							</form>
						</div>
						<div id="momo-be-instructions-momoacgwc" class="momo-be-admin-content">
							<?php require_once 'page-momo-acgwc-instructions.php'; ?>
						</div>
						<?php do_action( 'momo_acg_wc_settings_tab_content' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php require_once 'partial-momo-settings-footer.php'; ?>
