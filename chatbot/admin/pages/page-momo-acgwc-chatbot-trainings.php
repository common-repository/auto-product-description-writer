<?php
/**
 * MoMO ACG - Chatbot Trainings
 *
 * @author MoMo Themes
 * @package momoacg
 * @since v3.6.0
 */
$chatbot_settings         = get_option( 'momo_acgwc_cb_trainings_settings' );
$enable_page_trainings    = $momoacgwc->fn->momo_return_check_option( $chatbot_settings, 'enable_page_trainings' );
$enable_post_trainings    = $momoacgwc->fn->momo_return_check_option( $chatbot_settings, 'enable_post_trainings' );
$enable_product_trainings = $momoacgwc->fn->momo_return_check_option( $chatbot_settings, 'enable_product_trainings' );
$wp_pages                 = get_pages();
$pages_for_trainings      = isset( $chatbot_settings['pages_for_trainings'] ) ? $chatbot_settings['pages_for_trainings'] : array();
$posts_for_trainings      = isset( $chatbot_settings['posts_for_trainings'] ) ? $chatbot_settings['posts_for_trainings'] : array();
$products_for_trainings   = isset( $chatbot_settings['products_for_trainings'] ) ? $chatbot_settings['products_for_trainings'] : array();
$trainings_for            = array(
	'page'    => array(
		'title'    => esc_html__( 'Page(s)', 'momoacgwc' ),
		'enable'   => $enable_page_trainings,
		'embedded' => $pages_for_trainings,
		'afteryes' => 'enable_page_trainings_afteryes',
	),
	'post'    => array(
		'title'    => esc_html__( 'Post(s)', 'momoacgwc' ),
		'enable'   => $enable_post_trainings,
		'embedded' => $posts_for_trainings,
		'afteryes' => 'enable_post_trainings_afteryes',
	),
	'product' => array(
		'title'    => esc_html__( 'Product(s)', 'momoacgwc' ),
		'enable'   => $enable_product_trainings,
		'embedded' => $products_for_trainings,
		'afteryes' => 'enable_product_trainings_afteryes',
	),
);
$is_premium               = $momoacgwc->fn->momoacgwc_is_premium();
$disabled                 = '';
if ( ! $is_premium ) {
	$enable_woo_helper_block  = 'off';
	$enable_auto_desc_on_save = 'off';
	$disabled                 = "disabled='disabled'";
}
$field = array(
	'type'  => 'popbox',
	'id'    => 'add-trainings-content-model-popbox',
	'class' => 'momo-effect-1',
);
$momoacgwc->fn->momo_generate_popbox( $field );
?>
<div class="momo-admin-content-box">
	<div class="momo-ms-admin-content-main momoacg-chatbot-trainings-main" id="momoacg-momo-acg-chatbot-trainings-form">
		<div class="momo-be-msg-block"></div>
		<?php foreach ( $trainings_for as $index => $trainings ) : ?>
			<?php
			$table_id = 'current_training_list_of_' . $index;
			if ( 'product' === $index && ! class_exists( 'WooCommerce' ) ) {
				continue;
			}
			?>
			<div class="momo-row momo-responsive">
				<div class="momo-col">
					<div class="momo-be-block-section momo-min-h-110">
						<h2 class="momo-be-block-section-header"><?php echo esc_html( $trainings['title'] ); ?></h2>
						<div class="momo-be-block">
							<span class="momo-be-toggle-container" momo-be-tc-yes-container="<?php echo esc_attr( $trainings['afteryes'] ); ?>">
								<label class="switch">
									<input type="checkbox" class="switch-input" name="momo_acgwc_cb_trainings_settings[enable_<?php echo esc_attr( $index ); ?>_trainings]" autocomplete="off" <?php echo esc_attr( $trainings['enable'] ); ?> <?php echo esc_attr( $disabled ); ?> >
									<span class="switch-label" data-on="Yes" data-off="No"></span>
									<span class="switch-handle"></span>
								</label>
							</span>
							<span class="momo-be-toggle-container-label">
								<?php
									/* translators: %s: index */
									printf( esc_html__( 'Enable %s trainings', 'momoacgwc' ), esc_html( $index ) );
								?>
								<?php
								if ( ! $is_premium ) {
									?>
									<span class="momo-pro-label"><?php esc_html_e( 'PRO', 'momoacgwc' ); ?></span>
									<?php
								}
								?>
							</span>
						</div>
						<div id="<?php echo esc_attr( $trainings['afteryes'] ); ?>" class="momo-be-tc-yes-container momo-no-background">
							<div class="momo-be-fixed-table-container" id=<?php echo esc_attr( $table_id ); ?>>
								<table class="momo-be-table">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Title', 'momoacgwc' ); ?></th>
											<th><?php esc_html_e( 'Status', 'momoacgwc' ); ?></th>
											<th><?php esc_html_e( 'Status Date', 'momoacgwc' ); ?></th>
											<th></th>
										</tr>
									</thead>
									<tbody>
									<?php MoMo_ACGWC_Chatbot_Admin::momo_cb_trainings_generate_current_list( $index ); ?>
									</tbody>
								</table>
							</div>
							<div class="momo-be-block momo-be-right">
								<?php $btn_name = 'momo-embedding-add-content-' . $index; ?>
								<span class="momo-be-btn-extra momo-be-btn <?php echo esc_attr( $btn_name ); ?> momo-pb-triggerer" data-target="add-trainings-content-model-popbox" data-ajax="momo_acg_trainings_select_titles" data-header="pb_select_<?php echo esc_attr( $index ); ?>" data-djson=<?php echo esc_attr( wp_json_encode( array( 'type' => $index ) ) ); ?>><?php esc_html_e( 'Add Content', 'momoacgwc' ); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php if ( 'product' !== $index ) : ?>
			<div class="momo-be-hr-line"></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>
