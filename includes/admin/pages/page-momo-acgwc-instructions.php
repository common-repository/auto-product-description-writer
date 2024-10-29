<?php
/**
 * MoMO ACG - WC Instructions Page.
 *
 * @author MoMo Themes
 * @package momoacgwc
 * @since v1.0.0
 */

?>
<div class="momo-admin-content-box">
	<div class="momo-be-table-header momo-show">
		<h3><?php esc_html_e( 'Woo Product Writer Instructions', 'momoacgwc' ); ?></h3>
	</div>
	<div class="momo-ms-admin-content-main momoacgw-instructions-main" id="momoacg-momo-acgw-instructions-form">
		<div class="momo-be-msg-block"></div>
		<div class="momo-be-block">
			<ol>
				<li><?php esc_html_e( 'Add the API key', 'momoacgwc' ); ?></li>
				<li><?php esc_html_e( 'Create a new product page. WooCommerce > Products > Add Product.', 'momoacgwc' ); ?></li>
				<li><?php esc_html_e( 'Add the title, product tags. Add price, shipping, weight and product attribute, and finally click the blue Generate button in the Generator metabox.', 'momoacgwc' ); ?></li>
				<li>
					<?php esc_html_e('For full documentation, please check the link','momoacg');
					?>
					<a href="http://momothemes.com/documentationWooContentFree/" class="momo-pl-5" target="_blank"><?php esc_html_e( 'Documentation', 'momoacgwc' ); ?></a>
				</li>
			</ol> 
		</div>
	</div>
</div>
