<?php
/**
 * Fine Tune from Post
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v3.6.0
 */
class MoMo_ACGWC_Embeddings_Model {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'momo_acg_trainings_embeddings_hook', array( $this, 'momo_acg_prepare_post_embeddings' ), 10, 3 );

		add_action( 'admin_init', array( $this, 'momoacg_register_cs_settings' ) );

		$ajax_events = array(
			'momo_acg_trainings_select_titles'      => 'momo_acg_trainings_select_titles',
			'momo_acg_cb_trainings_store_ids'       => 'momo_acg_cb_trainings_store_ids',
			'momo_acg_cb_embed_add_to_queue'        => 'momo_acg_cb_embed_add_to_queue',
			'momo_acg_cb_embed_remove_from_list'    => 'momo_acg_cb_embed_remove_from_list',
			'momo_acg_cb_embed_reschedule_to_queue' => 'momo_acg_cb_embed_reschedule_to_queue',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
		}

	}
	/**
	 * Register momoacg Settings
	 */
	public function momoacg_register_cs_settings() {
		register_setting( 'momoacgwc-settings-cb-trainings-group', 'momo_acgwc_cb_trainings_settings' );
	}
	/**
	 * Generate Post selection list
	 */
	public function momo_acg_trainings_select_titles() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_trainings_select_titles' !== $_POST['action'] ) {
			return;
		}
		$type = isset( $_POST['data']['type'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['type'] ) ) : 'page';
		$data = array();
		switch ( $type ) {
			case 'page':
				$args  = array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'numberposts'    => -1,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				);
				$query = new WP_Query( $args );

				$data = $query->posts;
				break;
			case 'post':
				$args  = array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'numberposts'    => -1,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				);
				$query = new WP_Query( $args );

				$data = $query->posts;
				break;
			case 'product':
				$args = array(
					'status' => 'publish',
					'limit'  => -1,
					'return' => 'ids',
				);
				$data = wc_get_products( $args );
				break;
		}
		if ( ! empty( $data ) ) {
			$trainings_list = get_option( 'momo_acg_cb_trainings_list' );
			$variable       = 'current_training_list_of_' . $type;
			$current_list   = isset( $trainings_list[ $variable ] ) ? $trainings_list[ $variable ] : array();
			$current_list   = $momoacgwc->tblfn->momo_get_table_option( 'momo_acg_cb_trainings_list', $type );
			ob_start();
			?>
			<style>
			#add-trainings-content-model-popbox .momo-pb-content{
				overflow-y: scroll;
				max-height: 650px;
			}
			</style>
			<div class="momo-cb-training-schedule-form" data-type="<?php echo esc_attr( $type ); ?>" data-noselected="<?php esc_html_e( 'No post/page/product selected', 'momoacgwc' ); ?>">
				<div class="momo-be-fixed-table-container">
					<table class="momo-be-table">
						<thead>
							<tr>
								<th width="50px"></th>
								<th><?php esc_html_e( 'Title', 'momoacgwc' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $data ) ) :
								foreach ( $data as $id ) {
									if ( ! $this->momo_check_is_in_array( $id, $current_list ) ) :
										?>
									<tr>
									<td width="50px"><input type="checkbox" value="<?php echo esc_attr( $id ); ?>"/></td>
									<td><?php echo esc_html( get_the_title( $id ) ); ?></td>
									</tr>
										<?php
									endif;
								}
							endif;
							?>
						</tbody>
					</table>
				</div>
				<div class="momo-be-block momo-mb-20">
					<span class="button button-secondary momo-cs-cancel-new-ft"><?php esc_html_e( 'Cancel', 'momoacgwc' ); ?></span>
					<span class="button button-primary momo-be-float-right momo-add-content-for-training"><?php esc_html_e( 'Schedule', 'momoacgwc' ); ?></span>
				</div>
			</div>
			<?php
			$content = ob_get_clean();
			echo wp_json_encode(
				array(
					'status'  => 'good',
					'message' => esc_html__( 'List(s) created successfully.', 'momoacgwc' ),
					'content' => $content,
				)
			);
			exit;
		}
	}
	/**
	 * Check if post is already added
	 *
	 * @param string $id_to_check ID.
	 * @param array  $whole_array Current List.
	 */
	public function momo_check_is_in_array( $id_to_check, $whole_array ) {
		$found = false;
		if ( empty( $whole_array ) ) {
			return $found;
		}
		foreach ( $whole_array as $id => $single ) {
			if ( isset( $single['id'] ) && (int) $single['id'] === $id_to_check ) {
				$found = true;
				break;
			}
		}
		return $found;
	}
	/**
	 * Store selected IDs
	 *
	 * @return void
	 */
	public function momo_acg_cb_trainings_store_ids() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_cb_trainings_store_ids' !== $_POST['action'] ) {
			return;
		}
		$trainings_list = get_option( 'momo_acg_cb_trainings_list' );
		if ( ! is_array( $trainings_list ) ) {
			$trainings_list = array();
		}
		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'page';
		$ids  = isset( $_POST['ids'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ids'] ) ) : 'ids';

		$list = array();

		$variable     = 'current_training_list_of_' . $type;
		$current_list = isset( $trainings_list[ $variable ] ) ? $trainings_list[ $variable ] : array();
		$current_list = $momoacgwc->tblfn->momo_get_table_option( 'momo_acg_cb_trainings_list', $type );
		foreach ( $ids as $id ) {
			$current_list[ $id ] = array(
				'id'      => $id,
				'status'  => 'pending',
				'changed' => time(),
			);
		}

		$trainings_list[ $variable ] = $current_list;
		update_option( 'momo_acg_cb_trainings_list', $trainings_list );
		$content = '';
		$momoacgwc->tblfn->momo_set_table_option(
			'momo_acg_cb_trainings_list',
			$type,
			$current_list,
			function () use ( $type, &$content ) {
				$cache_key = 'momo_custom_option_momo_acg_cb_trainings_list_' . $type;
				wp_cache_delete( $cache_key, 'momo_custom_options' );
				// Code to execute after the database operation is complete.
				$content = MoMo_ACGWC_Chatbot_Admin::momo_cb_trainings_generate_current_list( $type, 'ajax' );
			}
		);

		echo wp_json_encode(
			array(
				'status'   => 'good',
				'message'  => esc_html__( 'List(s) added successfully.', 'momoacgwc' ),
				'content'  => $content,
				'variable' => $variable,
			)
		);
		exit;
	}
	/**
	 * Add to queue for embeddings
	 */
	public function momo_acg_cb_embed_add_to_queue() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_cb_embed_add_to_queue' !== $_POST['action'] ) {
			return;
		}
		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'page';

		$sentence_fragments = $this->momo_get_post_sentence_fragments( $post_id );

		$index = 0;
		foreach ( $sentence_fragments as $fragments ) {
			$time   = time() + ( $index * 60 );
			$return = wp_schedule_single_event(
				$time,
				'momo_acg_trainings_embeddings_hook',
				array(
					$post_id,
					$type,
					$index,
				)
			);
			$index++;
			if ( is_wp_error( $return ) ) {
				continue;
			}
		}
		$current_list = $this->momo_acg_update_training_status( $post_id, 'scheduled', $type );
		$content      = '';
		$momoacgwc->tblfn->momo_set_table_option(
			'momo_acg_cb_trainings_list',
			$type,
			$current_list,
			function () use ( $type, &$content ) {
				$cache_key = 'momo_custom_option_momo_acg_cb_trainings_list_' . $type;
				wp_cache_delete( $cache_key, 'momo_custom_options' );
				// Code to execute after the database operation is complete.
				$content = MoMo_ACGWC_Chatbot_Admin::momo_cb_trainings_generate_current_list( $type, 'ajax' );
			}
		);

		echo wp_json_encode(
			array(
				'status'  => 'good',
				'message' => esc_html__( 'Post scheduled.', 'momoacgwc' ),
				'content' => $content,
			)
		);
		exit;
	}
	/**
	 * Get number of senteces fragments
	 *
	 * @param integer $post_id Post ID.
	 */
	public function momo_get_post_sentence_fragments( $post_id ) {
		$content = get_post_field( 'post_content', $post_id );
		$title   = get_the_title( $post_id );
		$content = $title . ' : ' . $content;
		$plain   = wp_strip_all_tags( $content );
		str_replace( array( "\r", "\n" ), '', $plain );

		// Segment the content into sentences.
		$sentences = preg_split( '/(?<=[.?!])\s+/', $plain, -1, PREG_SPLIT_NO_EMPTY );
		// Group sentences into fragments of 7 sentences each.
		$fragment_size      = 7;
		$sentence_fragments = array_chunk( $sentences, $fragment_size );
		return $sentence_fragments;
	}
	/**
	 * Preapre finetune model with contents
	 *
	 * @return void
	 */
	public function momo_acg_prepare_post_embeddings( $post_id, $type, $index ) {
		global $momoacgwc;
		$sentence_fragments = $this->momo_get_post_sentence_fragments( $post_id );

		$language_model = 'text-embedding-ada-002';
		$body           = array(
			'model' => $language_model,
			'input' => $sentence_fragments[ $index ],
		);
		$url            = 'https://api.openai.com/v1/embeddings';
		$response       = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body );
		$embeddings     = '';

		if ( isset( $response['status'] ) && 200 === $response['status'] ) {
			$data = isset( $response['body']->data ) ? $response['body']->data : array();
			if ( ! empty( $data ) ) {
				foreach ( $data as $em ) {
					$embeddings = $em->embedding;
				}
			}
		}
		$status = count( $sentence_fragments ) === ( $index + 1 ) ? 'embedded' : 'partial';
		if ( empty( $embeddings ) ) {
			$status = 'partial';
		}
		$current_list = $this->momo_acg_update_training_content( $post_id, $status, $type, $embeddings, $index );
		$momoacgwc->tblfn->momo_set_table_option( 'momo_acg_cb_trainings_list', $type, $current_list );
	}
	/**
	 * Finetune Model with given Post IDs
	 */
	public function momo_acg_cb_finetune_with_ids() {

	}
	/**
	 * Update training content
	 *
	 * @param integer $post_id Post ID.
	 * @param string  $status Status.
	 * @param string  $type Post type.
	 * @param array   $content Embedded content.
	 */
	public function momo_acg_update_training_content( $post_id, $status, $type, $content, $index ) {
		global $momoacgwc;
		$current_list = $momoacgwc->tblfn->momo_get_table_option( 'momo_acg_cb_trainings_list', $type );

		$current_list[ $post_id ]['content'][ $index ] = $content;
		$current_list[ $post_id ]['status']            = $status;
		return $current_list;
	}
	/**
	 * Update training status
	 *
	 * @param integer $post_id Post ID.
	 * @param string  $status Status.
	 * @param string  $type Post type.
	 */
	public function momo_acg_update_training_status( $post_id, $status, $type ) {
		global $momoacgwc;
		$current_list = $momoacgwc->tblfn->momo_get_table_option( 'momo_acg_cb_trainings_list', $type );

		$current_list[ $post_id ] = array(
			'id'      => $post_id,
			'status'  => $status,
			'changed' => time(),
		);
		return $current_list;
	}
	/**
	 * Remove from training list.
	 */
	public function momo_acg_cb_embed_remove_from_list() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_cb_embed_remove_from_list' !== $_POST['action'] ) {
			return;
		}
		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'page';

		$variable     = 'current_training_list_of_' . $type;
		$current_list = $momoacgwc->tblfn->momo_get_table_option( 'momo_acg_cb_trainings_list', $type );
		if ( isset( $current_list[ $post_id ] ) ) {
			unset( $current_list[ $post_id ] );
			array_values( $current_list );
		}

		$content = '';
		$momoacgwc->tblfn->momo_set_table_option(
			'momo_acg_cb_trainings_list',
			$type,
			$current_list,
			function () use ( $type, &$content, &$variable ) {
				$cache_key = 'momo_custom_option_momo_acg_cb_trainings_list_' . $type;
				wp_cache_delete( $cache_key, 'momo_custom_options' );
				// Code to execute after the database operation is complete.
				$content = MoMo_ACGWC_Chatbot_Admin::momo_cb_trainings_generate_current_list( $type, 'ajax' );
				echo wp_json_encode(
					array(
						'status'   => 'good',
						'message'  => esc_html__( 'List removed successfully.', 'momoacgwc' ),
						'content'  => $content,
						'variable' => $variable,
					)
				);
				exit;
			}
		);
	}
	/**
	 * Reschedule to Queue
	 */
	public function momo_acg_cb_embed_reschedule_to_queue() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_cb_embed_reschedule_to_queue' !== $_POST['action'] ) {
			return;
		}
		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'page';

		$trainings_list = get_option( 'momo_acg_cb_trainings_list' );

		$variable     = 'current_training_list_of_' . $type;
		$current_list = isset( $trainings_list[ $variable ] ) ? $trainings_list[ $variable ] : array();
		$current_list = $momoacgwc->tblfn->momo_get_table_option( 'momo_acg_cb_trainings_list', $type );
		if ( isset( $current_list[ $post_id ] ) ) {
			unset( $current_list[ $post_id ] );
			array_values( $current_list );
		}

		$trainings_list[ $variable ] = $current_list;

		$current_list[ $post_id ] = array(
			'id'      => $post_id,
			'status'  => 'pending',
			'changed' => time(),
		);

		$trainings_list[ $variable ] = $current_list;
		update_option( 'momo_acg_cb_trainings_list', $trainings_list );
		$content = '';
		$momoacgwc->tblfn->momo_set_table_option(
			'momo_acg_cb_trainings_list',
			$type,
			$current_list,
			function () use ( $type, &$content ) {
				$cache_key = 'momo_custom_option_momo_acg_cb_trainings_list_' . $type;
				wp_cache_delete( $cache_key, 'momo_custom_options' );
				// Code to execute after the database operation is complete.
				$content = MoMo_ACGWC_Chatbot_Admin::momo_cb_trainings_generate_current_list( $type, 'ajax' );
			}
		);
		echo wp_json_encode(
			array(
				'status'   => 'good',
				'message'  => esc_html__( 'List rescheduled successfully.', 'momoacgwc' ),
				'content'  => $content,
				'variable' => $variable,
			)
		);
		exit;
	}
	/**
	 * Log transient for Dashboard record
	 *
	 * @param string $transient_id Transient ID.
	 * @param string $type Logging type.
	 */
	public function momo_log_transient_to_option( $transient_id, $type = 'old' ) {
		$cb_dashboard_contents = get_option( 'momo_acgwc_cb_dashboard_contents' );
		if ( 'new' === $type || ! isset( $cb_dashboard_contents[ $transient_id ] ) ) {
			$content = array(
				'last_used' => time(),
				'count'     => 0,
			);
		} else {
			$content = isset( $cb_dashboard_contents[ $transient_id ] ) ? $cb_dashboard_contents[ $transient_id ] : array();
			$count   = isset( $content['count'] ) ? (int) $content['count'] : 0;
			++$count;
			$content = array(
				'last_used' => time(),
				'count'     => $count,
			);
		}
		$cb_dashboard_contents[ $transient_id ] = $content;
		update_option( 'momo_acgwc_cb_dashboard_contents', $cb_dashboard_contents );
	}
	/**
	 * Get data within time frame
	 *
	 * @param array  $data Option saved.
	 * @param string $start_time Start time.
	 * @param string $end_time End Time.
	 */
	public function momo_log_get_within_timeframe( $data, $start_time, $end_time ) {
		return ( $data['last_used'] >= $start_time && $data['last_used'] <= $end_time );
	}
	/**
	 * Get Log count
	 *
	 * @param string $type Count type.
	 * @param string $reply_session Replies or session.
	 */
	public function momo_log_get_count( $type, $reply_session ) {
		$cb_dashboard_contents = get_option( 'momo_acgwc_cb_dashboard_contents' );
		$count                 = 0;
		switch ( $type ) {
			case 'daily':
				$start_time = strtotime( 'today' );
				$end_time   = strtotime( 'today + 1 day' ) - 1;
				break;
			case 'monthly':
				$start_time = strtotime( 'first day of this month' );
				$end_time   = strtotime( 'last day of this month' ) + 86399;
				break;
		}
		$data_in_time_frame = array_filter(
			$cb_dashboard_contents,
			function ( $data ) use ( $start_time, $end_time ) {
				return $this->momo_log_get_within_timeframe( $data, $start_time, $end_time );
			}
		);
		if ( empty( $data_in_time_frame ) ) {
			return $count;
		}
		if ( 'session' === $reply_session ) {
			$count = count( $data_in_time_frame );
		} else {
			foreach ( $data_in_time_frame as $data ) {
				$count = $count + $data['count'];
			}
		}
		return $count;
	}
}
