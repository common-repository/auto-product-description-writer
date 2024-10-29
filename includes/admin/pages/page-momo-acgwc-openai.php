<?php
/**
 * MoMO ACG WC - OpenAI Settings
 *
 * @author MoMo Themes
 * @package momoacgwc
 * @since v1.0.0
 */

global $momoacgwc;
$openai_settings           = get_option( 'momo_acg_wc_openai_settings' );
$enable_openai_post_option = $momoacgwc->fn->momo_return_check_option( $openai_settings, 'enable_openai_post_option' );
$api_key                   = isset( $openai_settings['api_key'] ) ? $openai_settings['api_key'] : '';
$disable_api_cache         = $momoacgwc->fn->momo_return_check_option( $openai_settings, 'disable_api_cache' );
$ak_class                  = '';
if ( ! empty( $api_key ) ) {
	$ak_class = 'momo-hidden';
}
$nonce      = wp_create_nonce( 'momo_acg_wc_export_settings' );
$export_url = add_query_arg(
	array(
		'action' => 'momo_acg_wc_export_settings',
		'nonce'  => $nonce,
	),
	admin_url( 'admin-ajax.php' )
);

$models        = $momoacgwc->fn->momo_get_model_select_list();
$languages     = $momoacgwc->lang->momo_get_all_langs();
$default_model = isset( $openai_settings['default_model'] ) ? $openai_settings['default_model'] : 'text-davinci-003';
$default_lang  = isset( $openai_settings['default_lang'] ) ? $openai_settings['default_lang'] : 'english';
?>
<div class="momo-admin-content-box">
	<div class="momo-be-table-header">
		<h3><?php esc_html_e( 'Woo Product Writer : OpenAI API Settings', 'momoacgwc' ); ?></h3>
	</div>
	<div class="momo-ms-admin-content-main momoacg-export-settings-main" id="momoacg-momo-wsw-export-settings-form">
		<div class="momo-be-msg-block"></div>
		<div class="momo-be-buttons-block momo-mt-0 momo-no-tborder">
			<div class="momo-be-block momo-mb-10">
				<div class="momo-be-block-section" id="openai_api_settings">
					<div class="momo-be-block">
						<label class="regular inline"><?php esc_html_e( 'API Key', 'momoacgwc' ); ?></label>
							<input type="text" class="inline wide <?php echo esc_attr( $ak_class ); ?>" name="momo_acg_wc_openai_settings[api_key]" value="<?php echo esc_attr( $api_key ); ?>"/>
							<?php
							if ( ! empty( $api_key ) ) :
								$after_three      = substr( $api_key, 3 );
								$masked_substring = str_repeat( '*', strlen( $after_three ) - 3 );
								$masked           = substr( $api_key, 0, 3 ) . $masked_substring . substr( $api_key, -3, 3 );
								?>
							<span class="momo-block-with-asterix">
									<?php echo esc_html( $masked ); ?>
								<span class="momo-clear-api"><?php esc_html_e( 'Clear API Key', 'momoacgwc' ); ?></span>
							</span>
							<?php endif; ?>
					</div>
					<div class="momo-be-block">
						<p>	
							<label class="regular inline"></label>
							<a href="https://beta.openai.com/account/api-keys" class="momo-pl-5" target="_blank"><?php esc_html_e( 'Get API Key', 'momoacgwc' ); ?></a>
						</p>
					</div>
					<div class="momo-be-block momo-mt-10">
						<label class="regular inline"><?php esc_html_e( 'Default Model', 'momoacgwc' ); ?></label>
						<select class="inline" name="momo_acg_wc_openai_settings[default_model]">
							<?php
							foreach ( $models as $model => $name ) :
								?>
								<option value="<?php echo esc_attr( $model ); ?>" <?php echo esc_attr( ( $default_model === $model ) ? 'selected="selected"' : '' ); ?>>
									<?php echo esc_html( $name ); ?>
								</option>
								<?php
							endforeach;
							?>
						</select>
						<div class="momo-be-block">
							<p>	
								<label class="regular inline"></label>
								<?php esc_html_e( 'Learn more about models ', 'momoacgwc' ); ?><a href="https://openai.com/api/pricing/" class="momo-pl-5" target="_blank"><?php esc_html_e( 'here', 'momoacgwc' ); ?></a>
							</p>
						</div>
					</div>
					<div class="momo-be-block momo-mt-10">
						<label class="regular inline"><?php esc_html_e( 'Default Language', 'momoacgwc' ); ?></label>
						<select class="inline" name="momo_acg_wc_openai_settings[default_lang]">
							<?php
							foreach ( $languages as $language => $name ) :
								?>
								<option value="<?php echo esc_attr( $language ); ?>" <?php echo esc_attr( ( $default_lang === $language ) ? 'selected="selected"' : '' ); ?>>
									<?php echo esc_html( $name ); ?>
								</option>
								<?php
							endforeach;
							?>
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="momo-be-block-section">
			<div class="momo-be-block momo-mb-10">
				<span class="momo-be-toggle-container">
					<label class="switch">
						<input type="checkbox" class="switch-input" name="momo_acg_wc_openai_settings[disable_api_cache]" autocomplete="off" <?php echo esc_attr( $disable_api_cache ); ?>>
						<span class="switch-label" data-on="Yes" data-off="No"></span>
						<span class="switch-handle"></span>
					</label>
				</span>
				<span class="momo-be-toggle-container-label">
					<?php esc_html_e( 'Disable API cache', 'wooshopify' ); ?>
				</span>
			</div>
		</div>
		<div class="momo-be-section-block">
			<h2 class="momo-section-block"><?php esc_html_e( 'Import / Export Settings', 'momoacgwc' ); ?></h2>
			<div class="momo-be-buttons-block">
				<div class="momo-flex-columns">
					<div class="momo-two-column">
						<a href="<?php echo esc_url( $export_url ); ?>" class="momo-be-btn momo-be-btn-extra" style="margin-top: 45px;"><?php esc_html_e( 'Export', 'momoacgwc' ); ?></a>
					</div>
					<div class="momo-two-column">
						<input style="padding: 10px 0" type="file" id="file-select" name="settings[]" multiple="" accept=".json" data-file_type=".json">
						</br>
						<span id="momo_acg_wc_import_settings" class="momo-be-btn momo-be-btn-extra"><?php esc_html_e( 'Import', 'momoacgwc' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
