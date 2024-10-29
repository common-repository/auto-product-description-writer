<?php
/**
 * AI Helper Block
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v2.0.0
 */
class Momo_ACG_Woo_Product_Helper_Block {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'momo_acg_ai_helper_block_init' ), 20 );
		add_action( 'rest_api_init', array( $this, 'momo_ai_helper_rest_api_init' ) );
	}
	/**
	 * Blocks Init
	 */
	public function momo_acg_ai_helper_block_init() {
		global $momoacg;
		register_block_type( __DIR__ . '/build' );
		$settings = array(
			'rest_aihelper'           => get_rest_url() . 'momoacgwc/v1/woohelper',
			'momoacgforms_ajax_nonce' => wp_create_nonce( 'wp_rest' ),
		);
		wp_localize_script( 'wp-blocks', 'momo_ai_helper_script', $settings );
	}

	/**
	 * Initialize rest route
	 */
	public function momo_ai_helper_rest_api_init() {
		$slug     = 'momoacgwc';
		$version  = 'v1';
		$endpoint = 'woohelper';
		register_rest_route(
			"$slug/$version",
			"/$endpoint",
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'momo_acg_aihelper_post_handler' ),
				'permission_callback' => function () {
					return ( '__return_true' );
				},
			)
		);
	}
	/**
	 * Handle Form Request
	 *
	 * @param WP_Request $request request.
	 */
	public function momo_acg_aihelper_post_handler( $request ) {
		global $momoacgwc;
		$attributes = $request->get_params();
		$prompt     = isset( $attributes['prompt'] ) && ! empty( $attributes['prompt'] ) ? $attributes['prompt'] : '';
		$type       = isset( $attributes['type'] ) && ! empty( $attributes['type'] ) ? $attributes['type'] : 'initial';
		$old_data   = isset( $attributes['old_data'] ) && ! empty( $attributes['old_data'] ) ? $attributes['old_data'] : '';

		$initial   = $this->momo_ai_helper_get_initial_message();
		$message   = array();
		$message[] = $initial;
		if ( 'initial' === $type ) {
			$new_msg   = array(
				'role'    => 'user',
				'content' => esc_html__( 'Write a product description on ', 'momoacgwc' ) . $prompt,
			);
			$message[] = $new_msg;
		} elseif ( 'style' === $type ) {
			$new_msg   = array(
				'role'    => 'user',
				'content' => $old_data,
			);
			$message[] = $new_msg;
			$new_msg   = array(
				'role'    => 'user',
				'content' => sprintf( esc_html__( 'Rewrite your last answer as %s writing style.', 'momoacg' ), $prompt ),
			);
			$message[] = $new_msg;
		} elseif ( 'language' === $type ) {
			$new_msg   = array(
				'role'    => 'user',
				'content' => $old_data,
			);
			$message[] = $new_msg;
			$new_msg   = array(
				'role'    => 'user',
				'content' => sprintf( esc_html__( 'Change your last answer into %s language.', 'momoacg' ), $prompt ),
			);
			$message[] = $new_msg;
		} elseif ( 'rewrite' === $type ) {
			$new_msg   = array(
				'role'    => 'user',
				'content' => $old_data,
			);
			$message[] = $new_msg;
			$content   = '';
			if ( 'longer' === $prompt ) {
				$content = esc_html__( 'Modify above article to convey a greater length or expanded meaning.', 'momoacg' );
			} elseif ( 'shorter' === $prompt ) {
				$content = esc_html__( 'Modify above piece of writing using fewer words.', 'momoacg' );
			} elseif ( 'summarize' === $prompt ) {
				$content = esc_html__( 'Summarize above content.', 'momoacg' );
			}
			$new_msg   = array(
				'role'    => 'user',
				'content' => $content,
			);
			$message[] = $new_msg;
		}
		$model       = 'gpt-3.5-turbo';
		$modeltype   = $momoacgwc->fn->momo_get_model_type( $model );
		$temperature = isset( $chatbot_settings['temperature'] ) && ! empty( $chatbot_settings['temperature'] ) ? $chatbot_settings['temperature'] : '0.7';
		$max_tokens  = isset( $chatbot_settings['max_tokens'] ) && ! empty( $chatbot_settings['max_tokens'] ) ? $chatbot_settings['max_tokens'] : '660';

		$body     = array(
			'model'    => $model,
			'messages' => $message,
		);
		$url      = 'https://api.openai.com/v1/chat/completions';
		$response = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body );
		$content  = '';
		$message  = '';
		$status   = 'bad';
		if ( is_wp_error( $response ) ) {
			$message = esc_html__( 'Something went wrong with provided output from server.', 'momoacg' );
			$status  = 'bad';
		}
		if ( isset( $response['status'] ) && 404 === $response['status'] ) {
			$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Provided url not found.', 'momoacg' );
			$status   = 'bad';
		}
		if ( isset( $response['status'] ) && 400 === $response['status'] ) {
			$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Provided url not found.', 'momoacg' );
			$status   = 'bad';
		}
		if ( isset( $response['status'] ) && 429 === $response['status'] ) {
			$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Quota Exceded.', 'momoacg' );
			$status   = 'bad';
		}
		if ( isset( $response['status'] ) && 200 === $response['status'] ) {
			$choices = isset( $response['body']->choices ) ? $response['body']->choices : array();

			foreach ( $choices as $choice ) {
				if ( 'chat' === $modeltype ) {
					$message .= $choice->message->content;

				} else {
					$message .= $choice->text;

				}
				$status = 'good';
			}
		}
		return new WP_REST_Response(
			array(
				'status'  => $status,
				'message' => $message,
				'content' => '',
			),
			200
		);
	}
	/**
	 * Get some predefined cotext
	 */
	public function momo_ai_helper_get_initial_message() {
		$message = array(
			'role'    => 'system',
			'content' => esc_html__( "You are an AI assistant, your task is to generate and modify content based on user requests. This functionality is integrated into the AI Tools developed by MoMo Themes. Users interact with you through a Gutenberg block, you are inside the Wordpress editor. Strictly follow these rules: Format your responses in Markdown syntax, ready to be published with some context about user message.\\n\\n- Execute the request without any acknowledgement to the user.\\n\\n- Avoid sensitive or controversial topics and ensure your responses are grammatically correct and coherent.\\n\\n- If you cannot generate a meaningful response to a user’s request, reply with '__MOMO_AI_HELPER_ERROR__'. This term should only be used in this context, it is used to generate user facing errors.\\n\\n", 'momoacg' ),
		);
		return $message;
	}
}
new Momo_ACG_Woo_Product_Helper_Block();
