<?php
/**
 * Rest API Class for MoMo Auto Content Generator for WooCommerce
 *
 * @author   MoMo Themes
 * @version  1.0.0
 * @package  momoacgwc
 */
class MoMo_ACG_WC_Rest_API {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'momo_acg_initiate_rest_api' ) );
	}
	/**
	 * Register Rest Route
	 */
	public function momo_acg_initiate_rest_api() {
		$version   = '1';
		$namespace = 'momoacgwc/v' . $version;
		$base      = 'openai-products';
		register_rest_route(
			$namespace,
			$base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'momo_acg_generate_openai_contents' ),
				'permission_callback' => function () {
					return ( '__return_true' );
				},
			)
		);
	}
	/**
	 * Generate OpenAI Contents
	 *
	 * @param WP_Rest_Request $request Request.
	 */
	public function momo_acg_generate_openai_contents( $request ) {
		global $momoacg;
		$attributes        = $request->get_params();
		$language          = isset( $attributes['language'] ) ? $attributes['language'] : '';
		$title             = isset( $attributes['title'] ) ? $attributes['title'] : '';
		$product_id        = isset( $attributes['product_id'] ) ? sanitize_text_field( wp_unslash( $attributes['product_id'] ) ) : '';
		$temperature       = isset( $attributes['temperature'] ) ? sanitize_text_field( wp_unslash( $attributes['temperature'] ) ) : 0.7;
		$max_tokens        = isset( $attributes['max_tokens'] ) ? sanitize_text_field( wp_unslash( $attributes['max_tokens'] ) ) : 2000;
		$top_p             = isset( $attributes['top_p'] ) ? sanitize_text_field( wp_unslash( $attributes['top_p'] ) ) : 1.0;
		$frequency_penalty = isset( $attributes['frequency_penalty'] ) ? sanitize_text_field( wp_unslash( $attributes['frequency_penalty'] ) ) : 0.0;
		$presence_penalty  = isset( $attributes['presence_penalty'] ) ? sanitize_text_field( wp_unslash( $attributes['presence_penalty'] ) ) : 0.0;
		$addimage          = isset( $attributes['addimage'] ) ? sanitize_text_field( wp_unslash( $attributes['addimage'] ) ) : 'off';
		$args              = array(
			'language'          => $language,
			'title'             => $title,
			'product_id'        => $product_id,
			'temperature'       => $temperature,
			'max_tokens'        => $max_tokens,
			'top_p'             => $top_p,
			'frequency_penalty' => $frequency_penalty,
			'presence_penalty'  => $presence_penalty,
			'addimage'          => $addimage,
		);

		$return = $this->momoacgwc_openai_generate_content_output_json( $args );
	}
	/**
	 * Generate json output
	 *
	 * @param array  $args Arguments.
	 * @param string $type Normal or Headings content.
	 */
	public function momoacgwc_openai_generate_content_output_json( $args, $type = 'normal' ) {
		global $momoacgwc;
		$language          = isset( $args['language'] ) ? $args['language'] : '';
		$model             = isset( $args['model'] ) ? $args['model'] : 'text-davinci-003';
		$title             = isset( $args['title'] ) ? $args['title'] : '';
		$product_id        = isset( $args['product_id'] ) ? sanitize_text_field( wp_unslash( $args['product_id'] ) ) : '';
		$temperature       = isset( $args['temperature'] ) ? sanitize_text_field( wp_unslash( $args['temperature'] ) ) : 0.7;
		$max_tokens        = isset( $args['max_tokens'] ) ? sanitize_text_field( wp_unslash( $args['max_tokens'] ) ) : 2000;
		$top_p             = isset( $args['top_p'] ) ? sanitize_text_field( wp_unslash( $args['top_p'] ) ) : 1.0;
		$frequency_penalty = isset( $args['frequency_penalty'] ) ? sanitize_text_field( wp_unslash( $args['frequency_penalty'] ) ) : 0.0;
		$presence_penalty  = isset( $args['presence_penalty'] ) ? sanitize_text_field( wp_unslash( $args['presence_penalty'] ) ) : 0.0;
		$addimage          = isset( $args['addimage'] ) ? sanitize_text_field( wp_unslash( $args['addimage'] ) ) : 'off';
		$addgallery        = isset( $args['addgallery'] ) ? sanitize_text_field( wp_unslash( $args['addgallery'] ) ) : 'off';

		$response = $this->momo_acg_wc_check_api_return_json();
		if ( isset( $response['code'] ) && 404 === $response['code'] ) {
			echo wp_json_encode(
				array(
					'status' => 'bad',
					'msg'    => $response['message'],
				)
			);
			exit;
		}
		if ( empty( $title ) ) {
			echo wp_json_encode(
				array(
					'status' => 'bad',
					'msg'    => esc_html__( 'Product title field is empty.', 'momoacgwc' ),
				)
			);
			exit;
		}
		$variations_result   = '';
		$product_tags_result = '';
		$shipping_prompt     = '';
		$tags                = array();
		if ( ! empty( $product_id ) ) {
			$wc_product = new WC_Product( $product_id );
		}
		$content = '';
		$message = '';
		$short   = '';

		$formatted_prompt = 'write long detailed product description about ' . $title . ' in ' . $language . ' language.';

		$modeltype = $momoacgwc->fn->momo_get_model_type( $model );
		if ( 'chat' === $modeltype ) {
			$messages   = array();
			$messages[] = array(
				'role'    => 'user',
				'content' => $formatted_prompt,
			);

			$body = array(
				'model'             => $model,
				'messages'          => $messages,
				'temperature'       => (float) $temperature,
				'max_tokens'        => (int) $max_tokens,
				'top_p'             => (float) $top_p,
				'frequency_penalty' => (float) $frequency_penalty,
				'presence_penalty'  => (float) $presence_penalty,
			);
			$url  = 'https://api.openai.com/v1/chat/completions';
		} else {
			$body = array(
				'model'             => $model,
				'prompt'            => $formatted_prompt,
				'temperature'       => (float) $temperature,
				'max_tokens'        => (int) $max_tokens,
				'top_p'             => (float) $top_p,
				'frequency_penalty' => (float) $frequency_penalty,
				'presence_penalty'  => (float) $presence_penalty,
			);
			$url  = 'https://api.openai.com/v1/completions';
		}
		$openai_settings = get_option( 'momo_acg_wc_openai_settings' );
		$cache_disabled  = $momoacgwc->fn->momo_return_option_yesno( $openai_settings, 'disable_api_cache' );
		$ftransient      = $body;
		if ( 'chat' === $modeltype ) {
			$ftransient['messages'] = $formatted_prompt;
		}
		$ftransient['url']    = $url;
		$ftransient['pid']    = $product_id;
		$ftransient['prompt'] = 'description';
		$transient            = implode( ':', array_map( 'urlencode', $ftransient ) );
		$response             = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body, $transient, $cache_disabled );
		if ( is_wp_error( $response ) ) {
			$message .= esc_html__( 'Something went wrong (WP_Error).', 'momoacgwc' );
			$status   = 'bad';
		}
		if ( isset( $response['status'] ) && 404 === $response['status'] ) {
			$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Provided url not found.', 'momoacgwc' );
		}
		if ( isset( $response['status'] ) && 429 === $response['status'] ) {
			$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'You may have exceeded your current quota, please check your plan and billing details.', 'momoacgwc' );
		}
		if ( isset( $response['body']->error->message ) ) {
			$message .= $response['body']->error->message;
		}
		if ( isset( $response['status'] ) && 401 === $response['status'] ) {
			$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Invalid API Key.', 'momoacgwc' );
		}
		if ( isset( $response['status'] ) && 200 === $response['status'] ) {
			$choices = isset( $response['body']->choices ) ? $response['body']->choices : array();
			if ( ! empty( $choices ) ) {
				foreach ( $choices as $choice ) {
					if ( 'chat' === $modeltype ) {
						$content .= $choice->message->content;
					} else {
						$content .= $choice->text;
					}
				}
			} else {
				$message .= esc_html__( 'Not enough choices generated.', 'momoacgwc' );
			}
		}

		/** Short Description */
		$formatted_prompt = 'write 3 sentences short product summary about ' . $title . ' in ' . $language . ' language.';
		$body['prompt']   = $formatted_prompt;

		$openai_settings = get_option( 'momo_acg_wc_openai_settings' );
		$cache_disabled  = $momoacgwc->fn->momo_return_option_yesno( $openai_settings, 'disable_api_cache' );
		$ftransient      = $body;
		if ( 'chat' === $modeltype ) {
			$ftransient['messages'] = $formatted_prompt;

			$messages         = array();
			$messages[]       = array(
				'role'    => 'user',
				'content' => $formatted_prompt,
			);
			$body['messages'] = $messages;
			unset( $body['prompt'] );
		}
		$ftransient['url']    = $url;
		$ftransient['pid']    = $product_id;
		$ftransient['prompt'] = 'summary';
		$transient            = implode( ':', array_map( 'urlencode', $ftransient ) );
		$response             = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body, $transient, $cache_disabled );

		if ( is_wp_error( $response ) ) {
			$message .= esc_html__( 'Something went wrong (WP_Error).', 'momoacgwc' );
			$status   = 'bad';
		}
		if ( isset( $response['status'] ) && 404 === $response['status'] ) {
			$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Provided url not found.', 'momoacgwc' );
		}
		if ( isset( $response['status'] ) && 200 === $response['status'] ) {
			$choices = isset( $response['body']->choices ) ? $response['body']->choices : array();
			if ( ! empty( $choices ) ) {
				foreach ( $choices as $choice ) {
					if ( 'chat' === $modeltype ) {
						$short .= $choice->message->content;
					} else {
						$short .= $choice->text;
					}
				}
			} else {
				$message .= esc_html__( 'Not enough choices generated.', 'momoacgwc' );
			}
		}
		$image_id = -1;
		$gallery  = false;
		if ( 'on' === $addimage ) {
			$formatted_promt = esc_html__( 'generate an image of a ', 'momoacgwc' ) . $title . esc_html__( ' as a product', 'momoacgwc' );
			$body            = array(
				'prompt' => $title,
			);

			$url      = 'https://api.openai.com/v1/images/generations';
			$response = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body );
			/* if ( isset( $response['status'] ) && 200 === $response['status'] ) {
				$image = isset( $response['body']->data[0]->url ) ? $response['body']->data[0]->url : '';
				if ( ! empty( $image ) ) {
					$imgresult = "<img src='" . $image . "' alt='" . $title . "' />";
					$content  .= "\n\n\n" . $imgresult;
				}
			} */
			$image_id = -1;
			if ( isset( $response['status'] ) && 200 === $response['status'] ) {
				$image_url = isset( $response['body']->data[0]->url ) ? $response['body']->data[0]->url : '';
				if ( ! empty( $image_url ) ) {
					$image_id = media_sideload_image( $image_url, $product_id, 'Product Image', 'id' );
					if ( ! is_wp_error( $image_id ) ) {
						set_post_thumbnail( $product_id, $image_id );
					}
				}
			}
		}
		if ( 'on' === $addgallery && ! empty( $content ) ) {
			$gallery = $momoacgwc->fn->momoacgwc_generate_multiple_images( $product_id, $content );
		}
		if ( ! empty( $content ) ) {
			echo wp_json_encode(
				array(
					'status'    => 'good',
					'msg'       => esc_html__( 'Content generated successfully.', 'momoacgwc' ),
					'content'   => $content,
					'short'     => $short,
					'thumbnail' => $image_id,
					'gallery'   => $gallery,
				)
			);
			exit;
		}
		echo wp_json_encode(
			array(
				'status' => 'bad',
				'msg'    => empty( $message ) ? esc_html__( 'Something went wrong while generating content. Please try again.', 'momoacgwc' ) : $message,
			)
		);
		exit;
	}

	/**
	 * Check API is set if not return json.
	 */
	public function momo_acg_wc_check_api_return_json() {
		$openai_settings = get_option( 'momo_acg_wc_openai_settings' );
		$api_key         = isset( $openai_settings['api_key'] ) ? $openai_settings['api_key'] : '';
		if ( empty( $api_key ) ) {
			$url      = '<a href="' . admin_url( 'admin.php?page=momoacgwc' ) . '">' . esc_html__( 'settings page', 'momoacgwc' ) . '</a>';
			$response = array(
				'status'  => 'bad',
				/* translators: %s: settings url */
				'message' => sprintf( esc_html__( 'Empty API key, please store OpenAI API key in MoMo ACG %s first.', 'momoacgwc' ), $url ),
				'code'    => 404,
			);
			return $response;
		}
		return true;
	}
}
