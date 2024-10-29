<?php
/**
 * MoMO rssfeed - Queue List
 *
 * @author MoMo Themes
 * @package momoacg
 * @since v2.5.0
 */

global $momoacgwc;
$lists   = $momoacgwc->rssfeedfn->momo_rssfeed_generate_queue_cron_list();
$allowed = array(
	'tr'    => array(),
	'th'    => array(),
	'td'    => array(
		'class'   => array(),
		'title'   => array(),
		'data-id' => array(),
	),
	'table' => array(
		'class' => array(),
	),
	'div'   => array(
		'class' => array(),
	),
	'i'     => array(
		'class' => array(),
	),
	'span'  => array(
		'class' => array(),
	),
);
$autoblog = $momoacgwc->rssfeedfn->momo_autoblog_generate_queue_cron_list();
?>
<div class="momo-admin-content-box">
	<div class="momo-be-block-section">
		<div class="momo-be-table-header momo-show">
			<h3 class="momo-be-block-section-header"><?php esc_html_e( 'RSS Feeds Current Queue', 'momoacgwc' ); ?></h3>
		</div>
		<div class="momo-be-block">
			<div class="momo-ms-admin-content-main momorssfeed-editor-main" id="momorssfeed-editor-main-form">
				<div class="momo-be-msg-block"></div>
					<?php echo wp_kses( $lists, $allowed ); ?>
				</div>
		</div>
	</div>
	<div class="momo-be-hr-line"></div>
	<div class="momo-be-block-section">
		<div class="momo-be-table-header momo-show">
			<h3 class="momo-be-block-section-header"><?php esc_html_e( 'Auto Blogging Current Queue', 'momoacgwc' ); ?></h3>
		</div>
		<div class="momo-be-block">
			<div class="momo-ms-admin-content-main momoautoblog-editor-main" id="momoautoblog-editor-main-form">
				<div class="momo-be-msg-block"></div>
				<?php
					echo wp_kses( $autoblog, $allowed );
				?>
			</div>
		</div>
	</div>
</div>
