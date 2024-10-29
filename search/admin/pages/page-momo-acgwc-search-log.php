<?php
/**
 * Search Log Settings Page
 *
 * @author MoMo Themes
 * @package momoacgwc
 * @since v1.2.4
 */

global $momoacgwc;
$search_settings      = get_option( 'momo_acg_wc_searchlog_settings' );
$enable_search_log    = $momoacgwc->fn->momo_return_check_option( $search_settings, 'enable_search_log' );
$log_retention_period = isset( $search_settings['log_retention_period'] ) ? $search_settings['log_retention_period'] : '';
$email_subject        = isset( $search_settings['email_subject'] ) ? $search_settings['email_subject'] : '';
$is_premium           = momoacgwc_fs()->is_premium();
$disabled             = '';


?>
<div class="momo-admin-content-box">
	<div class="momo-ms-admin-content-main momosearchlog-editor-main" id="momosearchlog-editor-main-form">
		<div class="momo-be-block-section" id="momo-auto-blog-section">
			<div class="momo-be-table-header">
				<h3 class="momo-be-block-section-header"><?php esc_html_e( 'Search Log Settings', 'momoacgwc' ); ?>
				</h3>
			</div>
			<div class="momo-be-msg-block"></div>
			<div class="momo-be-block momo-mt-30">
				<div class="momo-be-messagebox"></div>
				<div class="momo-be-block-section">
					<div class="momo-be-block momo-mb-10">
						<span class="momo-be-toggle-container" momo-be-tc-yes-container="enable_searchlog_afteryes">
							<label class="switch">
								<input type="checkbox" class="switch-input" name="momo_acg_wc_searchlog_settings[enable_search_log]" autocomplete="off" <?php echo esc_attr( $enable_search_log ); ?> <?php echo esc_attr( $disabled ); ?> >
								<span class="switch-label" data-on="Yes" data-off="No"></span>
								<span class="switch-handle"></span>
							</label>
						</span>
						<span class="momo-be-toggle-container-label">
							<?php esc_html_e( 'Enable search log', 'momoacgwc' ); ?>
							<span class="momo-be-note"><?php esc_html_e( 'Enabling this function will also enables sales mail option', 'momoacgwc' ); ?></span>
						</span>
						<div id="enable_searchlog_afteryes" class="momo-be-tc-yes-container momo-no-background">
							<label for="momo_acg_wc_searchlog_settings[log_retention_period]" class="regular">
								<?php esc_html_e( 'Search Log Storage Duration', 'momoacgwc' ); ?>
							</label>
							<select class="regular" name="momo_acg_wc_searchlog_settings[log_retention_period]">
								<option value="1m" <?php selected( $log_retention_period, '1m' ); ?>><?php esc_html_e( '1 Month', 'momoacgwc' ); ?></option>
								<option value="3m" <?php selected( $log_retention_period, '3m' ); ?>><?php esc_html_e( '3 Months', 'momoacgwc' ); ?></option>
								<option value="6m" <?php selected( $log_retention_period, '6m' ); ?>><?php esc_html_e( '6 Months', 'momoacgwc' ); ?></option>
								<option value="1y" <?php selected( $log_retention_period, '1y' ); ?>><?php esc_html_e( '1 Year', 'momoacgwc' ); ?></option>
								<option value="forever" <?php selected( $log_retention_period, 'forever' ); ?>><?php esc_html_e( 'Forever', 'momoacgwc' ); ?></option>
							</select>
							<div class="momo-be-block">
								<label for="momo_acg_wc_searchlog_settings[email_subject]" class="regular block">
									<?php esc_html_e( 'Email subject', 'momoacgwc' ); ?>
								</label>
								<input name="momo_acg_wc_searchlog_settings[email_subject]" type="text" class="regular block" value="<?php echo esc_attr( $email_subject ); ?>" placeholder="<?php esc_html_e( 'Your search result for {search_term}', 'momoacgwc' ); ?>"/>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
