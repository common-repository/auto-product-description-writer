<?php
/**
 * MoMo Chatbot - Amin AJAX functions
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v3.4.0
 */
class MoMo_WC_Chatbot_Admin_Ajax {
	/**
	 * Constructor
	 */
	public function __construct() {
		$ajax_events = array(
			'momo_acg_create_new_ft_form'    => 'momo_acg_create_new_ft_form', // One.
			'momo_acg_ft_generate_qa_row'    => 'momo_acg_ft_generate_qa_row', // Two.
			'momo_acg_ft_create_new_model'   => 'momo_acg_ft_create_new_model', // Three.
			'momo_acg_remove_ft_model_by_id' => 'momo_acg_remove_ft_model_by_id', // Four.
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
		}
		add_filter( 'momo_acgwc_add_data_to_admin_locale', array( $this, 'momo_cb_add_some_locale' ) );
	}
	/**
	 * Add some locale data
	 *
	 * @param array $ajaxdata Default datas.
	 */
	public function momo_cb_add_some_locale( $ajaxdata ) {
		$ajaxdata['pb_select_page']    = esc_html__( 'Select Page(s)', 'momoacgwc' );
		$ajaxdata['pb_select_post']    = esc_html__( 'Select Post(s)', 'momoacgwc' );
		$ajaxdata['pb_select_product'] = esc_html__( 'Select Product(s)', 'momoacgwc' );
		$ajaxdata['create_ft_popbox']  = esc_html__( 'Create new Fine Tune Model', 'momoacgwc' );
		return $ajaxdata;
	}
	/**
	 * Generate Fine Tune Creation Form ( One )
	 */
	public function momo_acg_create_new_ft_form() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_create_new_ft_form' !== $_POST['action'] ) {
			return;
		}
		$content = $this->momo_generate_ft_top_header();
		echo wp_json_encode(
			array(
				'status'  => 'good',
				'message' => esc_html__( 'Fine Tune Model Form created.', 'momoacgwc' ),
				'content' => $content,
			)
		);
		exit;
	}
	/**
	 * Generate QA row
	 */
	public function momo_acg_ft_generate_qa_row() {
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_ft_generate_qa_row' !== $_POST['action'] ) {
			return;
		}
		$content = $this->momo_generate_ft_question_answer_row();
		echo wp_json_encode(
			array(
				'status'  => 'good',
				'message' => esc_html__( 'Question Answer row created.', 'momoacgwc' ),
				'content' => $content,
			)
		);
		exit;
	}
	/**
	 * Create new Model
	 */
	public function momo_acg_ft_create_new_model() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_ft_create_new_model' !== $_POST['action'] ) {
			return;
		}
		$model_id    = isset( $_POST['model_id'] ) ? sanitize_text_field( wp_unslash( $_POST['model_id'] ) ) : '';
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
		$post_data   = isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : array(); // phpcs:ignore Standard.Category.SniffName.ErrorCode Sanitization in next line
		$post_data   = $momoacgwc->fn->momo_recursive_sanitize_text_field( $post_data );
		// Remove empty titles.
		$dataset = array();
		foreach ( $post_data as $data ) {
			if ( ! empty( $data['prompt'] ) && ! empty( $data['completion'] ) ) {
				$dataset[] = $data;
			}
		}
		if ( empty( $dataset ) ) {
			echo wp_json_encode(
				array(
					'status'  => 'bad',
					'message' => esc_html__( 'All QA field(s) are empty. Please enter some question / answer in order to create finetuning model.', 'momoacgwc' ),
				)
			);
			exit;
		}
		$return = $this->momo_ft_create_temp_jsonl( $dataset );
		if ( ! $return ) {
			echo wp_json_encode(
				array(
					'status'  => 'bad',
					'message' => esc_html__( 'Unable to create .jsonl file in temp folder. There may be problem with file permission. Please ask your admin.', 'momoacgwc' ),
				)
			);
			exit;
		}
		$body      = '';
		$file_path = $momoacgwc->plugin_path . 'chatbot/temp/momo-ft-temp.jsonl';
		$url       = 'https://api.openai.com/v1/files';
		$response  = $momoacgwc->fn->momo_acg_remote_post_file( $url, $body, $file_path );

		if ( 200 === $response['code'] && isset( $response['body']->id ) ) {
			$file_id = $response['body']->id;
			$url     = 'https://api.openai.com/v1/fine-tunes';
			$url     = 'https://api.openai.com/v1/fine_tuning/jobs';
			$body    = array(
				'training_file' => $file_id,
				'model'         => 'davinci-002',
				'suffix'        => $model_id,
			);

			$response = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body );
			if ( 200 === $response['code'] && isset( $response['body']->id ) ) {
				echo wp_json_encode(
					array(
						'status'  => 'good',
						'message' => esc_html__( 'File tuning created successfully.', 'momoacgwc' ),
					)
				);
				exit;
			} else {
				echo wp_json_encode(
					array(
						'status'  => 'bad',
						'message' => isset( $response['message'] ) ? $response['message'] : esc_html__( 'Unable to assign finetuning file.', 'momoacgwc' ),
					)
				);
				exit;
			}
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'bad',
					'message' => isset( $response['message'] ) ? $response['message'] : esc_html__( 'Unable to upload finetuning file.', 'momoacgwc' ),
				)
			);
			exit;
		}
	}
	/**
	 * Remove Fintune Model by ID.
	 */
	public function momo_acg_remove_ft_model_by_id() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_remove_ft_model_by_id' !== $_POST['action'] ) {
			return;
		}
		$model_id = isset( $_POST['model_id'] ) ? sanitize_text_field( wp_unslash( $_POST['model_id'] ) ) : '';
		$ft_id    = isset( $_POST['ft_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ft_id'] ) ) : '';

		$url      = "https://api.openai.com/v1/fine-tunes/$ft_id";
		$url      = "https://api.openai.com/v1/fine_tuning/jobs/$ft_id/cancel";
		$response = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, array() );

		/* $url      = "https://api.openai.com/v1/models/$model_id";
		$body     = array();
		$response = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'DELETE', $url, $body ); */

		if ( 200 === $response['code'] && isset( $response['body']->deleted ) && true === $response['body']->deleted ) {
			echo wp_json_encode(
				array(
					'status'  => 'good',
					'message' => esc_html__( 'Fine tune model deleted successfully.', 'momoacgwc' ),
				)
			);
			exit;
		} else {
			$message = isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Something went wrong while deleting fine tune model. Please try again.', 'momoacgwc' );
			$message = isset( $response['response']['message'] ) ? $response['response']['message'] : $message;
			echo wp_json_encode(
				array(
					'status'  => 'bad',
					'message' => $message,
				)
			);
			exit;
		}
	}
	/**
	 * Create temp jsonl file for finetuning
	 *
	 * @param array $dataset Dataset array.
	 */
	public function momo_ft_create_temp_jsonl( $dataset ) {
		global $wp_filesystem, $momoacgwc;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		// Convert the data to a JSONL string.
		$jsonl = '';
		foreach ( $dataset as $item ) {
			$jsonl .= wp_json_encode( $item ) . "\n";
		}
		$file_path = $momoacgwc->plugin_path . 'chatbot/temp/momo-ft-temp.jsonl';

		$return = $wp_filesystem->put_contents( $file_path, $jsonl );
		return $return;
	}
	/**
	 * Generate Top Header Row
	 */
	public function momo_generate_ft_top_header() {
		ob_start();
		?>
		<style>
			#create-ft-model-popbox .momo-pb-content{
				overflow-y: scroll;
				max-height: 650px;
			}
		</style>
		<div class="momo-be-block momo-mb-20 momo-full-block">
			<label class="regular req"><?php esc_html_e( 'Unique ID (no space, less than 40 characters)', 'momoacgwc' ); ?></label>
			<input type="text" class="wide" name="model_id" autocomplete="off">
		</div>
		<div class="momo-ft-qa-block"></div>
		<div class="momo-be-block momo-mb-20 momo-full-block">
			<div class="momo-cs-add-new-qa-btn"><i class="bx bx-plus-circle"></i><span><?php esc_html_e( 'Add new training set', 'momoacgwc' ); ?></span></div>
		</div>
		<div class="momo-be-block momo-mb-20">
			<span class="button button-secondary momo-cs-cancel-new-ft"><?php esc_html_e( 'Cancel', 'momoacgwc' ); ?></span>
			<span class="button button-primary momo-be-float-right momo-cs-create-new-ft"><?php esc_html_e( 'Create', 'momoacgwc' ); ?></span>
		</div>
		<?php
		return ob_get_clean();
	}
	/**
	 * Generate Question Answer Row
	 */
	public function momo_generate_ft_question_answer_row() {
		ob_start();
		?>
		<div class="momo-be-hr-line"></div>
		<div class="momo-be-row momo-ft-qa-row">
			<div class="momo-be-col momo-mb-20 momo-full-block">
				<label class="regular req"><?php esc_html_e( 'Prompt', 'momoacgwc' ); ?></label>
				<input type="text" class="wide" name="momo_ft_question" autocomplete="off">
			</div>
			<div class="momo-be-col momo-mb-20 momo-full-block">
				<label class="regular req"><?php esc_html_e( 'Completion', 'momoacgwc' ); ?></label>
				<input type="text" class="wide" name="momo_ft_answer" autocomplete="off">
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
new MoMo_WC_Chatbot_Admin_Ajax();
