<?php
/**
 * MoMo ACG RSS Feed - Amin AJAX functions
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v3.0.0
 */
class MoMo_RssFeed_Admin_Ajax {
	/**
	 * Constructor
	 */
	public function __construct() {
		$ajax_events = array(
			'momo_rssfeed_generate_title_add_to_queue' => 'momo_rssfeed_generate_title_add_to_queue', // One.
			'momo_acg_rssfeed_delete_cron_by_id'       => 'momo_acg_rssfeed_delete_cron_by_id', // Two.
			'momo_autoblog_add_to_queue'               => 'momo_autoblog_add_to_queue', // Three.
			'momo_acg_autoblog_delete_cron_by_id'      => 'momo_acg_autoblog_delete_cron_by_id', // Four.
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
		}
	}
	/**
	 * Generate New Title Row ( One )
	 */
	public function momo_rssfeed_generate_title_add_to_queue() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacg_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_rssfeed_generate_title_add_to_queue' !== $_POST['action'] ) {
			return;
		}
		$url      = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		$status   = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';

		$rss    = simplexml_load_file( $url );
		$cnt    = 0;
		$feeder = array();
		if ( ! $rss ) {
			echo wp_json_encode(
				array(
					'status' => 'bad',
					'msg'    => esc_html__( 'Unable to generate title from given RSS feed url. Please check URL and try again..', 'momoacgwc' ),
				)
			);
			exit;
		}
		foreach ( $rss->channel->item as $feeds ) {
			$cnt++;
			if ( isset( $feeds->title ) ) {
				$feeder[] = array(
					'title'     => $feeds->title->__toString(),
					'ocategory' => isset( $feeds->category ) ? (array) $feeds->category : array(),
					'link'      => isset( $feeds->link ) ? $feeds->link->__toString() : '',
				);
			}
		}
		$index   = 1;
		$success = 0;
		$failure = 0;
		foreach ( $feeder as $line ) {
			$line['index']    = $index;
			$line['ptype']    = $status;
			$line['category'] = $category;
			$line['date']     = '';
			$line['noofpara'] = 4;
			$return           = $momoacgwc->rssfeedcron->momo_add_item_to_queue( $line );
			if ( $return ) {
				$success++;
			} else {
				$failure++;
			}
			$index++;
		}
		if ( 0 === $failure ) {
			echo wp_json_encode(
				array(
					'status' => 'good',
					/* translators: %s: success number */
					'msg'    => sprintf( esc_html__( '%s title(s) are added to schedule.', 'momoacgwc' ), $success ),
				)
			);
			exit;
		} else {
			echo wp_json_encode(
				array(
					'status' => 'good',
					/* translators: %1$1s: success number, %2$2s: failure */
					'msg'    => sprintf( esc_html__( '%1$1s title(s) are added to schedule with %2$2s failed to add.', 'momoacgwc' ), $success, $failure ),
					'queue'  => $momoacgwc->rssfeedfn->momo_rssfeed_generate_queue_cron_list(),
				)
			);
			exit;
		}
	}
	/**
	 * Delete Cron by Cron ID.
	 */
	public function momo_acg_rssfeed_delete_cron_by_id() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacg_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_rssfeed_delete_cron_by_id' !== $_POST['action'] ) {
			return;
		}
		$cron_id = isset( $_POST['cron_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cron_id'] ) ) : '';

		$single = $momoacgwc->rssfeedfn->momo_get_rssfeed_single_event( $cron_id );
		if ( $single ) {
			wp_unschedule_event( $single['timestamp'], $single['hook'], $single['args'] );
			echo wp_json_encode(
				array(
					'status' => 'good',
					'msg'    => esc_html__( 'Cron event unscheduled successfully.', 'momoacgwc' ),
				)
			);
			exit;
		} else {
			echo wp_json_encode(
				array(
					'status' => 'bad',
					'msg'    => esc_html__( 'Cron event not found for given cron ID.', 'momoacgwc' ),
				)
			);
			exit;
		}
	}
	/**
	 * Generate New Title Row ( One )
	 */
	public function momo_autoblog_add_to_queue() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacg_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_autoblog_add_to_queue' !== $_POST['action'] ) {
			return;
		}
		$tags     = isset( $_POST['tags'] ) ? sanitize_text_field( wp_unslash( $_POST['tags'] ) ) : '';
		$status   = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
		$nop      = isset( $_POST['nop'] ) ? sanitize_text_field( wp_unslash( $_POST['nop'] ) ) : '';
		$nofpara  = isset( $_POST['nofpara'] ) ? sanitize_text_field( wp_unslash( $_POST['nofpara'] ) ) : '';
		$per      = isset( $_POST['per'] ) ? sanitize_text_field( wp_unslash( $_POST['per'] ) ) : '';
		$wstyle   = isset( $_POST['wstyle'] ) ? sanitize_text_field( wp_unslash( $_POST['wstyle'] ) ) : 'normal';
		$addimage = isset( $_POST['addimage'] ) ? sanitize_text_field( wp_unslash( $_POST['addimage'] ) ) : 'off';
		$title    = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : 'off';

		$args = array(
			'tags'          => $tags,
			'category'      => $category,
			'status'        => $status,
			'no_of_posts'   => $nop,
			'time_basis'    => $per,
			'writing_style' => $wstyle,
			'add_image'     => $addimage,
			'gen_title'     => $title,
			'no_of_para'    => $nofpara,
		);

		$return = $momoacgwc->rssfeedcron->momo_add_autoblog_to_queue( $args );
		if ( ! $return  ) {
			echo wp_json_encode(
				array(
					'status' => 'bad',
					'msg'    => esc_html__( 'Not able to auto blogging. Please try it later.', 'momoacgwc' ),
				)
			);
			exit;
		} else {
			echo wp_json_encode(
				array(
					'status' => 'good',
					'msg'    => esc_html__( 'Auto blogging added successfully.', 'momoacgwc' ),
					'queue'  => $momoacgwc->rssfeedfn->momo_autoblog_generate_queue_cron_list(),
				)
			);
			exit;
		}
	}
}
new MoMo_RssFeed_Admin_Ajax();
