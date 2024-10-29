<?php
/**
 * Page - Personalized product recommendation
 *
 * @since v1.2.1
 */

global $momoacgwc;
$related_settings             = get_option( 'momo_acg_wc_related_settings' );
$enable_product_recomendation = $momoacgwc->fn->momo_return_check_option( $related_settings, 'enable_product_recomendation' );
$no_of_products_count         = isset( $related_settings['no_of_products_count'] ) ? $related_settings['no_of_products_count'] : 5;
$products_per_row             = isset( $related_settings['products_per_row'] ) ? $related_settings['products_per_row'] : 3;
$recomendation_sources        = array(
	'user_viewed_products'          => array(
		'label'   => esc_html__( 'User Viewed Products', 'momoacgwc' ),
		'value'   => 'user_viewed_products',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'user_viewed_products' ),
	),
	'removed_cart_items'            => array(
		'label'   => esc_html__( 'Removed Cart Items', 'momoacgwc' ),
		'value'   => 'removed_cart_items',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'removed_cart_items' ),
	),
	'previous_orders'               => array(
		'label'   => esc_html__( 'Previous Orders', 'momoacgwc' ),
		'value'   => 'previous_orders',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'previous_orders' ),
	),
	'frequently_purchased_together' => array(
		'label'   => esc_html__( 'Frequently Purchased Together', 'momoacgwc' ),
		'value'   => 'frequently_purchased_together',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'frequently_purchased_together' ),
	),
	'best_selling'                  => array(
		'label'   => esc_html__( 'Best Selling', 'momoacgwc' ),
		'value'   => 'best_selling',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'best_selling' ),
	),
	'top_rated'                     => array(
		'label'   => esc_html__( 'Top Rated', 'momoacgwc' ),
		'value'   => 'top_rated',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'top_rated' ),
	),
	'featured_products'             => array(
		'label'   => esc_html__( 'Featured Products', 'momoacgwc' ),
		'value'   => 'featured_products',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'featured_products' ),
	),
	'on_sale'                       => array(
		'label'   => esc_html__( 'On Sale', 'momoacgwc' ),
		'value'   => 'on_sale',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'on_sale' ),
	),
	'new_arrivals'                  => array(
		'label'   => esc_html__( 'New Arrivals', 'momoacgwc' ),
		'value'   => 'new_arrivals',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'new_arrivals' ),
	),
	'same_category'                 => array(
		'label'   => esc_html__( 'Same Category', 'momoacgwc' ),
		'value'   => 'same_category',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'same_category' ),
	),
	'similar_title'                 => array(
		'label'   => esc_html__( 'Similar Title', 'momoacgwc' ),
		'value'   => 'similar_title',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'similar_title' ),
	),
	'random'                        => array(
		'label'   => esc_html__( 'Random', 'momoacgwc' ),
		'value'   => 'random',
		'checked' => $momoacgwc->fn->momo_return_checkbox_option( $related_settings, 'recommendation_sources', 'random' ),
	),
);
?>
<div class="momo-admin-content-box">
	<div class="momo-be-table-header">
		<h3><?php esc_html_e( 'Personalized Product Recommendation', 'momoacgwc' ); ?></h3>
	</div>
	<div class="momo-ms-admin-content-main momoacg-export-settings-main" id="momoacg-momo-wsw-export-settings-form">
		<div class="momo-be-msg-block"></div>
		<div class="momo-be-block-section">
			<div class="momo-be-block momo-mb-10">
				<span class="momo-be-toggle-container"  momo-be-tc-yes-container="enable_product_recomendation_afteryes">
					<label class="switch">
						<input type="checkbox" class="switch-input" name="momo_acg_wc_related_settings[enable_product_recomendation]" autocomplete="off" <?php echo esc_attr( $enable_product_recomendation ); ?> >
						<span class="switch-label" data-on="Yes" data-off="No"></span>
						<span class="switch-handle"></span>
					</label>
				</span>
				<span class="momo-be-toggle-container-label">
					<?php esc_html_e( 'Enable Product Recommendation', 'wooshopify' ); ?>
					<span class="momo-be-note"><?php esc_html_e( 'Enabling this function will disable default WooCommerce related product and enable Personalized Product Recommendation', 'momoacgwc' ); ?></span>
				</span>
			</div>
			<div id="enable_product_recomendation_afteryes" class="momo-be-tc-yes-container">
				<div class="momo-be-block-section">
					<h2 class="momo-be-block-section-header"><?php esc_html_e( 'Product Recommendation Settings', 'momoacgwc' ); ?></h2>
					<div class="momo-be-block momo-mn-10">
						<h4><?php esc_html_e( 'Select recommendation sources', 'momoacgwc' ); ?></h4>
						<div class="momo-row">
						<?php foreach ( $recomendation_sources as $key => $value ) { ?>
							<div class="momo-cols">
								<div class="momo-be-block momo-mb-10">
								<input class="inline" type="checkbox" name="momo_acg_wc_related_settings[recommendation_sources][]" value="<?php echo esc_attr( $value['value'] ); ?>" <?php echo esc_attr( $value['checked'] ); ?> >
								<label class="inline" for="user_viewed_products"><?php echo esc_html( $value['label'] ); ?></label>
								</div>
							</div>
						<?php } ?>
						</div>
					</div>
					<span class="momo-be-hr-line"></span>
					<div class="momo-be-block momo-mb-10">
						<label class="block"><?php esc_html_e( 'Number of products', 'momoacgwc' ); ?></label>
						<input class="inline" type="number" name="momo_acg_wc_related_settings[no_of_products_count]" value="<?php echo esc_attr( $no_of_products_count ); ?>" >
					</div>
					<span class="momo-be-hr-line"></span>
					<div class="momo-be-block momo-mb-10">
						<label class="block"><?php esc_html_e( 'Products per row', 'momoacgwc' ); ?></label>
						<input class="inline" type="number" name="momo_acg_wc_related_settings[products_per_row]" value="<?php echo esc_attr( $products_per_row ); ?>" >
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
