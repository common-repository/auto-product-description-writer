<?php
/**
 * MoMO AutoBlog - Settings Page
 *
 * @author MoMo Themes
 * @package momoacg
 * @since v3.5.0
 */

global $momoacgwc;
$all_styles     = $momoacgwc->lang->momo_get_all_writing_style();
$all_styles_pro = $momoacgwc->lang->momo_get_all_writing_style_pro();
$is_premium     = momoacgwc_fs()->is_premium();
$disabled       = '';
if ( ! $is_premium ) {
	$disabled = 'disabled="disabled"';
}
?>
<div class="momo-admin-content-box">
	<div class="momo-ms-admin-content-main momoautoblog-editor-main" id="momoautoblog-editor-main-form">
		<div class="momo-be-block-section" id="momo-auto-blog-section">
			<div class="momo-be-table-header">
				<h3 class="momo-be-block-section-header"><?php esc_html_e( 'Auto Blog', 'momoacgwc' ); ?>
				</h3>
			</div>
			<div class="momo-be-msg-block"></div>
			<div class="momo-be-block momo-mt-30">
				<div class="momo-be-messagebox"></div>
				<div class="momo-be-block momo-mb-20 momo-full-block">
					<label class="regular">
						<?php esc_html_e( 'Select Category', 'momoacgwc' ); ?>
					</label>
					<select name="momo_autoblog_category" class="full-width momo-green">
					<?php
					$categories = get_categories(
						array(
							'orderby' => 'name',
							'order'   => 'ASC',
						)
					);

					foreach ( $categories as $category ) {
						?>
						<option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?></option>
						<?php
					}
					?>
					</select>
				</div>
				<div class="momo-row momo-mt-20">
					<div class="momo-col">
						<label  class="regular"><?php esc_html_e( 'Add Keywords', 'momoacgwc' ); ?>:</label>
						<input type="text" class="full-width momo-green" name="momo_autoblog_keywords" placeholder="<?php echo esc_html( 'nepali food, chinese food, indian food' ); ?>">
					</div>
				</div>
				<div class="momo-row momo-mt-20">
					<div class="momo-col">
						<label  class="regular"><?php esc_html_e( 'Number of posts', 'momoacgwc' ); ?>:</label>
						<select name="momo_autoblog_nop" class="full-width momo-green">
							<option value="1"><?php esc_html_e( '1', 'momoacgwc' ); ?></option>
							<option value="2"><?php esc_html_e( '2', 'momoacgwc' ); ?></option>
							<option value="3"><?php esc_html_e( '3', 'momoacgwc' ); ?></option>
							<option value="4"><?php esc_html_e( '4', 'momoacgwc' ); ?></option>
							<option value="5"><?php esc_html_e( '5', 'momoacgwc' ); ?></option>
						</select>
					</div>
					<div class="momo-col">
						<label  class="regular"><?php esc_html_e( 'Per', 'momoacgwc' ); ?>:</label>
						<select name="momo_autoblog_per" class="full-width momo-green">
							<option value="daily"><?php esc_html_e( 'Day', 'momoacgwc' ); ?></option>
							<option value="weekly"><?php esc_html_e( 'Week', 'momoacgwc' ); ?></option>
							<option value="monthly"><?php esc_html_e( 'Month', 'momoacgwc' ); ?></option>
							<option value="yearly"><?php esc_html_e( 'Year', 'momoacgwc' ); ?></option>
						</select>
					</div>
				</div>
				<div class="momo-row momo-mt-20">
					<div class="momo-col">
						<label class="regular">
							<?php esc_html_e( 'Writing Style', 'momoacgwc' ); ?>
						</label>
						<select name="momo_autoblog_writing_style" class="full-width momo-green">
							<?php foreach ( $all_styles as $value => $name ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
							<?php if ( momoacgwc_fs()->is_premium() ) { ?>
								<?php foreach ( $all_styles_pro as $value => $name ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $name ); ?></option>
								<?php endforeach; ?>
							<?php } else { ?>
								<?php foreach ( $all_styles_pro as $value => $name ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>_pro" disabled><?php echo esc_html( $name ); ?><?php esc_html_e( ' ( PRO )', 'momoacgwc' ); ?></option>
								<?php endforeach; ?>
							<?php }	?>
						</select>
					</div>
					<div class="momo-col">
						<label  class="regular"><?php esc_html_e( 'Number of paragraphs', 'momoacgwc' ); ?>:</label>
						<select name="momo_autoblog_nof_para" class="full-width momo-green">
							<option value="1"><?php esc_html_e( '1', 'momoacgwc' ); ?></option>
							<option value="2"><?php esc_html_e( '2', 'momoacgwc' ); ?></option>
							<?php if ( momoacgwc_fs()->is_premium() ) { ?>
								<option value="3"><?php esc_html_e( '3', 'momoacgwc' ); ?></option>
								<option value="4"><?php esc_html_e( '4', 'momoacgwc' ); ?></option>
								<option value="5"><?php esc_html_e( '5', 'momoacgwc' ); ?></option>
								<option value="6"><?php esc_html_e( '6', 'momoacgwc' ); ?></option>
							<?php } else { ?>
								<option value="3" disabled><?php esc_html_e( '3 ( PRO )', 'momoacgwc' ); ?></option>
								<option value="4" disabled><?php esc_html_e( '4 ( PRO )', 'momoacgwc' ); ?></option>
								<option value="5" disabled><?php esc_html_e( '5 ( PRO )', 'momoacgwc' ); ?></option>
								<option value="6" disabled><?php esc_html_e( '6 ( PRO )', 'momoacgwc' ); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="momo-row momo-mt-20">
					<div class="momo-col">
						<label class="regular">
							<?php esc_html_e( 'Generate Title', 'momoacgwc' ); ?>
						</label>
						<span class="momo-be-toggle-container">
							<label class="switch">
								<input type="checkbox" class="switch-input" name="momo_autoblog_generate_title" autocomplete="off" <?php echo esc_attr( $disabled ); ?>/>
								<span class="switch-label" data-on="Yes" data-off="No"></span>
								<span class="switch-handle"></span>
							</label>
						</span>
						<?php if ( ! $is_premium ) { ?>
							<span class="momo-pro-label"><?php esc_html_e( 'PRO', 'momoacgwc' ); ?></span>
						<?php } ?>
					</div>
					<div class="momo-col">
						<label class="regular">
							<?php esc_html_e( 'Add Image', 'momoacgwc' ); ?>
						</label>
						<span class="momo-be-toggle-container">
							<label class="switch">
								<input type="checkbox" class="switch-input" name="momo_autoblog_add_image" autocomplete="off" <?php echo esc_attr( $disabled ); ?>/>
								<span class="switch-label" data-on="Yes" data-off="No"></span>
								<span class="switch-handle"></span>
							</label>
						</span>
						<?php if ( ! $is_premium ) { ?>
							<span class="momo-pro-label"><?php esc_html_e( 'PRO', 'momoacgwc' ); ?></span>
						<?php } ?>
					</div>
				</div>
				<div class="momo-be-block momo-mt-20 momo-full-block">
					<label class="regular">
						<?php esc_html_e( 'Save generated post as', 'momoacgwc' ); ?>
					</label>
					<select name="momo_autoblog_status"  class="full-width momo-green">
						<!-- <option value="momoacg_post_draft"><?php esc_html_e( 'Plugin Draft', 'momoacgwc' ); ?></option> -->
						<option value="wp_post_draft"><?php esc_html_e( 'Post Draft', 'momoacgwc' ); ?></option>
						<option value="wp_post_publish"><?php esc_html_e( 'Post Publish', 'momoacgwc' ); ?></option>
					</select>
				</div>
				<div class="momo-be-block momo-mb-20 momo-w-80">
					<p class="regular">
						<?php esc_html_e( 'Add to queue', 'momoacgwc' ); ?>
					</p>
					<!-- <?php //if ( momoacgwc_fs()->is__premium_only() ) { ?> -->
						<span class="momo-autoblog-generate-queue momo-be-btn momo-be-btn-extra"><?php esc_html_e( 'Process', 'momoacgwc' ); ?></span>
					<!-- <?php //} else { ?>
						<span class="momo-fake-btn momo-be-btn momo-be-btn-extra"><?php esc_html_e( 'Process', 'momoacgwc' ); ?></span>
						<span class="momo-pro-label"><?php esc_html_e( 'PRO', 'momoacgwc' ); ?></span>
					<?php //} ?> -->
				</div>
			</div>
		</div>
	</div>
</div>
