<?php
/**
 * MoMO ACG WC - Edit Settings
 *
 * @author MoMo Themes
 * @package momoacgwc
 * @since v1.0.0
 */

global $momoacgwc;
$edit_settings               = get_option( 'momo_acg_wc_edit_product_settings' );
$enable_woo_helper_block     = $momoacgwc->fn->momo_return_check_option( $edit_settings, 'enable_woo_helper_block' );
$enable_auto_desc_on_save    = $momoacgwc->fn->momo_return_check_option( $edit_settings, 'enable_auto_desc_on_save' );
$enable_auto_multiple_images = $momoacgwc->fn->momo_return_check_option( $edit_settings, 'enable_auto_multiple_images' );
$is_premium                  = momoacgwc_fs()->is_premium();
$disabled                    = '';
if ( ! $is_premium ) {
	$enable_woo_helper_block     = 'off';
	$enable_auto_desc_on_save    = 'off';
	$enable_auto_multiple_images = 'off';
	$disabled                    = "disabled='disabled'";
}
?>
<div class="momo-admin-content-box">
	<div class="momo-be-table-header">
		<h3><?php esc_html_e( 'Woo Product Writer : Edit Product Settings', 'momoacgwc' ); ?></h3>
	</div>
	<div class="momo-ms-admin-content-main momoacg-export-settings-main" id="momoacg-momo-wsw-export-settings-form">
		<div class="momo-be-msg-block"></div>
		<div class="momo-be-block-section">
			<div class="momo-be-block momo-mb-10">
				<span class="momo-be-toggle-container">
					<label class="switch">
						<input type="checkbox" class="switch-input" name="momo_acg_wc_edit_product_settings[enable_woo_helper_block]" autocomplete="off" <?php echo esc_attr( $enable_woo_helper_block ); ?> <?php echo esc_attr( $disabled ); ?> >
						<span class="switch-label" data-on="Yes" data-off="No"></span>
						<span class="switch-handle"></span>
					</label>
				</span>
				<span class="momo-be-toggle-container-label">
					<?php esc_html_e( 'Enable Woo Helper Block', 'wooshopify' ); ?>
					<?php
					if ( ! $is_premium ) {
						?>
						<span class="momo-pro-label"><?php esc_html_e( 'PRO', 'momoacgwc' ); ?></span>
						<?php
					}
					?>
					<span class="momo-be-note"><?php esc_html_e( 'Enabling this function will also enables the block on the product edit page', 'momoacgwc' ); ?></span>
				</span>
			</div>
		</div>
		<span class="momo-be-hr-line"></span>
		<div class="momo-be-block-section">
			<div class="momo-be-block momo-mb-10">
				<span class="momo-be-toggle-container">
					<label class="switch">
						<input type="checkbox" class="switch-input" name="momo_acg_wc_edit_product_settings[enable_auto_desc_on_save]" autocomplete="off" <?php echo esc_attr( $enable_auto_desc_on_save ); ?> <?php echo esc_attr( $disabled ); ?> >
						<span class="switch-label" data-on="Yes" data-off="No"></span>
						<span class="switch-handle"></span>
					</label>
				</span>
				<span class="momo-be-toggle-container-label">
					<?php esc_html_e( 'Enable Product description generation automatically on post save', 'wooshopify' ); ?>
					<?php
					if ( ! $is_premium ) {
						?>
						<span class="momo-pro-label"><?php esc_html_e( 'PRO', 'momoacgwc' ); ?></span>
						<?php
					}
					?>
					<span class="momo-be-note"><?php esc_html_e( 'Content will only be generated if the description field is empty and a product title is provided.', 'momoacgwc' ); ?></span>
				</span>
			</div>
		</div>
		<span class="momo-be-hr-line"></span>
		<div class="momo-be-block-section">
			<div class="momo-be-block momo-mb-10">
				<span class="momo-be-toggle-container">
					<label class="switch">
						<input type="checkbox" class="switch-input" name="momo_acg_wc_edit_product_settings[enable_auto_multiple_images]" autocomplete="off" <?php echo esc_attr( $enable_auto_multiple_images ); ?> <?php echo esc_attr( $disabled ); ?> >
						<span class="switch-label" data-on="Yes" data-off="No"></span>
						<span class="switch-handle"></span>
					</label>
				</span>
				<span class="momo-be-toggle-container-label">
					<?php esc_html_e( 'Enable multiple images', 'wooshopify' ); ?>
					<?php
					if ( ! $is_premium ) {
						?>
						<span class="momo-pro-label"><?php esc_html_e( 'PRO', 'momoacgwc' ); ?></span>
						<?php
					}
					?>
					<span class="momo-be-note"><?php esc_html_e( 'Several images matching the generated content will be created automatically.', 'momoacgwc' ); ?></span>
				</span>
			</div>
		</div>
	</div>
</div>
