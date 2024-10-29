<?php
/**
 * Search Log And Sales Mail Page
 *
 * @author MoMo Themes
 * @package momoacgwc
 * @since v1.2.4
 */
global $momoacgwc, $wpdb;

$table_name    = $wpdb->prefix . 'momo_acgwc_search_logs';
$installed_log = $momoacgwc->searchlogtb->check_table_exist( $table_name );

$table_name     = $wpdb->prefix . 'momo_acgwc_email_sent_logs';
$installed_sent = $momoacgwc->searchlogtb->check_table_exist( $table_name );

$message   = '';
$class     = '';
if ( ! $installed_log || ! $installed_sent ) {
	$message = esc_html__( 'The search log table is missing. Please reactivate the plugin to resolve this issue or ', 'momoacgwc' );
	$class   = 'warning show';
}
$field = array(
	'type'  => 'popbox',
	'id'    => 'search-email-templates-content-model-popbox',
	'class' => 'momo-effect-1',
);
$momoacgwc->fn->momo_generate_popbox( $field );
?>
<div class="momo-admin-content-box">
	<div class="momo-ms-admin-content-main momosearchlog-editor-main" id="momosearchlog-editor-main-form">
		<div class="momo-be-block-section" id="momo-auto-blog-section">
			<div class="momo-be-table-header">
				<h3 class="momo-be-block-section-header"><?php esc_html_e( 'Search Log & Mail', 'momoacgwc' ); ?>
				</h3>
			</div>
			<div class="momo-be-msg-block <?php echo esc_attr( $class ); ?>" style="line-height:2.2;">
				<?php echo esc_html( $message ); ?>
				<?php if ( ! $installed_log || ! $installed_sent ) : ?>
					<form method="post" action="" style="display:inline-block;margin-left: 12px">
						<?php wp_nonce_field( 'momo_upgrade_searchlog_db_action', 'momo_upgrade_searchlog_db_nonce' ); ?>
						<input type="submit" name="momo_searchlog_upgrade_db" id="momo_searchlog_upgrade_db" class="button button-primary" value="<?php esc_html_e( 'Upgrade Database', 'momoacgwc' ); ?>"  />
					</form>
				<?php endif; ?>
			</div>
			<?php if ( $installed_log && $installed_sent ) : ?>
				<div class="momo-acgwc-search-mail-table">
					<?php $momoacgwc->searchlogtb->prepare_items(); ?>
					<div class="momo-be-block momo-mt-30">
						<?php if ( ! momoacgwc_fs()->is_premium() ) { ?>
							<span class="momo-pro-note"><?php esc_html_e( 'Only 5 emails per month are available in the free version.', 'momoacgwc' ); ?></span>
						<?php } ?>
						<form method="post">
							<?php
							$momoacgwc->searchlogtb->display();
							$momoacgwc->searchlogtb->pagination( 'bottom' );
							?>
						</form>
					</div>
				</div>
				<div class="momo-agwc-searchmail-footer">
					<?php
					esc_html_e( 'This section automatically generates highly effective sales letters based on user-provided search terms. By analyzing keywords, it tailors a letter with a captivating headline, a solution showcasing the product or service, and persuasive benefits. The letter is optimized for the target user, ensuring the tone and language align with their needs. Finally, a strong call to action is included, encouraging immediate engagement. Users can quickly review and edit the letter or use it as is, making the sales process efficient and targeted.', 'momoacgwc' );
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
