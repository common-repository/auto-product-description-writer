<?php
/**
 * MoMo Chatbot Frontend
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v3.10.0
 */
class MoMo_ACGWC_Chatbot_Frontned {
	/**
	 * Unique session
	 *
	 * @var string
	 */
	private $session_id;
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'momo_acg_cb_load_scripts_styles' ), 10 );

		add_action( 'rest_api_init', array( $this, 'momo_chatbot_rest_api_init' ) );

		$this->momo_generate_session_id();
	}
	/**
	 * Generate session id if not started
	 */
	public function momo_generate_session_id() {
		if ( session_status() === PHP_SESSION_NONE ) {
			session_start();
		}
		$session_id       = session_id();
		$this->session_id = $session_id;
	}
	/**
	 * Get unique session ID.
	 */
	public function momo_chatbot_get_transient_id() {
		$transient_id = 'momo-acg-chatbot-single_' . $this->session_id;
		return $transient_id;
	}
	/**
	 * Initialize rest route
	 *
	 * @return void
	 */
	public function momo_chatbot_rest_api_init() {
		$slug     = 'momoacgwc';
		$version  = 'v1';
		$endpoint = 'chatbot';
		register_rest_route(
			"$slug/$version",
			"/$endpoint",
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'momo_acg_chatbot_post_handler' ),
				'permission_callback' => function () {
					return ( '__return_true' );
				},
			)
		);
	}
	/**
	 * Handle Chatbot Request
	 *
	 * @param WP_Request $request request.
	 */
	public function momo_acg_chatbot_post_handler( $request ) {
		global $momoacgwc;
		$status           = '';
		$chatbot_settings = get_option( 'momo_acgwc_chatbot_settings' );
		$default_icon     = $momoacgwc->plugin_url . 'chatbot/assets/images/chatbot.png';
		$context          = isset( $chatbot_settings['context'] ) ? $chatbot_settings['context'] : '';
		$model            = isset( $chatbot_settings['model'] ) ? $chatbot_settings['model'] : 'gpt-3.5-turbo';
		$temperature      = isset( $chatbot_settings['temperature'] ) && ! empty( $chatbot_settings['temperature'] ) ? $chatbot_settings['temperature'] : '0.2';
		$max_tokens       = isset( $chatbot_settings['max_tokens'] ) && ! empty( $chatbot_settings['max_tokens'] ) ? $chatbot_settings['max_tokens'] : '660';
		$sentence_buffer  = isset( $chatbot_settings['sentence_buffer'] ) ? $chatbot_settings['sentence_buffer'] : '';
		$max_length       = isset( $chatbot_settings['max_length'] ) ? $chatbot_settings['max_length'] : '';
		$embeddings_index = isset( $chatbot_settings['embeddings_index'] ) ? $chatbot_settings['embeddings_index'] : '';
		$content_aware    = isset( $chatbot_settings['content_aware'] ) ? $chatbot_settings['content_aware'] : '';
		$ai_name          = isset( $chatbot_settings['ai_name'] ) && ! empty( $chatbot_settings['ai_name'] ) ? $chatbot_settings['ai_name'] : esc_html__( 'AI', 'momoacgwc' );
		$welcome_message  = isset( $chatbot_settings['welcome_message'] ) && ! empty( $chatbot_settings['welcome_message'] ) ? $chatbot_settings['welcome_message'] : esc_html__( 'How can I help you?', 'momoacgwc' );
		$username         = isset( $chatbot_settings['username'] ) && ! empty( $chatbot_settings['username'] ) ? $chatbot_settings['username'] : esc_html__( 'User', 'momoacgwc' );

		$casually_fine_tuned = $momoacgwc->fn->momo_return_option_yesno( $chatbot_settings, 'casually_fine_tuned' );

		$enable_ft_chatbot_modal   = $momoacgwc->fn->momo_return_option_yesno( $chatbot_settings, 'enable_ft_chatbot_modal' );
		$selected_ft_chatbot_modal = isset( $chatbot_settings['selected_ft_chatbot_modal'] ) ? $chatbot_settings['selected_ft_chatbot_modal'] : '';

		$attributes = $request->get_params();
		$question   = isset( $attributes['question'] ) && ! empty( $attributes['question'] ) ? $attributes['question'] : '';
		if ( empty( $question ) ) {
			ob_start();
			$empty_question = esc_html__( 'Empty question provided. Please try again.', 'momoacgwc' );
			?>
				<div class="acg-cb-message acg-ai">
					<span class="acg-message"><?php echo esc_html( $empty_question ); ?></span>
					<span class="acg-name"><?php echo esc_html( $ai_name ); ?></span>
				</div>
			<?php
			$content = ob_get_clean();
			return new WP_REST_Response(
				array(
					'status'  => 'good',
					'message' => '',
					'content' => $content,
				),
				200
			);
		}
		$message = '';
		if ( 'on' === $enable_ft_chatbot_modal && ! empty( $selected_ft_chatbot_modal ) ) {
			$context = isset( $chatbot_settings['context'] ) ? $chatbot_settings['context'] : '';

			$prompt = $question . '?' . "\n";

			$body = array(
				'model'             => $selected_ft_chatbot_modal,
				'temperature'       => (float) $temperature,
				'max_tokens'        => (int) $max_tokens,
				'top_p'             => 1,
				'frequency_penalty' => 0,
				'presence_penalty'  => 0,
				'prompt'            => $prompt,
				'n'                 => 1,
			);
			$url  = 'https://api.openai.com/v1/completions';
		} else {
			$message  = array();
			$new_ques = array(
				'role'    => 'user',
				'content' => $question,
			);
			if ( 'on' === $sentence_buffer ) {
				$message = $this->momo_chatbot_add_previous_questions( $new_ques );
			}
			if ( empty( $message ) ) {
				$message[] = $new_ques;
			}
			/**** For new chatbot ******** */
			$training_settings = get_option( 'momo_acgwc_cb_trainings_settings' );
			if (
				'on' === $training_settings['enable_page_trainings']
				||
				'on' === $training_settings['enable_post_trainings']
				||
				'on' === $training_settings['enable_product_trainings']
			) {
				$trainings = 'on';
			} else {
				$trainings = 'off';
			}
			$embedded = false;
			if ( 'on' === $trainings ) {
				$embeddings_list = $this->momo_acg_get_embeddings_list();
				if ( ! empty( $embeddings_list ) ) {
					$ques_embeddings = $this->momo_generate_embeddings_for_user_input( $question );
					$similarity      = $this->momo_acg_cb_check_similarity( $embeddings_list, $ques_embeddings );
					if ( ! empty( $similarity ) ) {
						$context    = ( is_array( $similarity ) ? implode( '.', $similarity ) : $similarity );
						$cb_context = isset( $chatbot_settings['context'] ) ? $chatbot_settings['context'] : '';
						$context   .= "\n" . $cb_context;
						$new_ques   = array(
							'role'    => 'assistant',
							'content' => 'Context: __MOMO_CHATBOT_EMBEDDINGS__ : ' . $context,
						);
						array_unshift( $message, $new_ques );
						$message[] = $new_ques;
						$embedded = true;
					}
				}
			}
			$content = array(
				'role'    => 'assistant',
				'content' => esc_html__( 'Please donot provide any answer as an AI, meaning pretending as an AI ( Chatbot, Digital Assistant ), act as a Human ( answer it as if user is talking to a human ), please pretend as Human also check context before answering.\\n\\n If answering from context please do not say as youve read it from above, answer it as if you\'ve got knowledge about it. If there\'s a context with __MOMO_CHATBOT_EMBEDDINGS__, then prepare your answer only based on that context.', 'momoacgwc' ),
			);
			array_unshift( $message, $content );
			if ( 'on' === $content_aware ) {
				if ( ! $embedded ) {
					$message = $this->momo_chatbot_add_context_to_start( $message );
				}
			}
			/**** For new chatbot ******** */
			$modeltype = $momoacgwc->fn->momo_get_model_type( $model );
			if ( 'chat' === $modeltype ) {
				$body = array(
					'model'       => $model,
					'temperature' => (float) $temperature,
					'max_tokens'  => (int) $max_tokens,
					'messages'    => $message,
				);
				$url  = 'https://api.openai.com/v1/chat/completions';
			} else {
				$context = isset( $chatbot_settings['context'] ) ? $chatbot_settings['context'] : '';

				$prompt = $question . '?' . "\n";
				$body   = array(
					'model'       => $model,
					'temperature' => (float) $temperature,
					'max_tokens'  => (int) $max_tokens,
					'prompt'      => $prompt,
				);
				$url    = 'https://api.openai.com/v1/completions';
			}
		}
		if ( 'on' === $casually_fine_tuned ) {
			$body['stop'] = "\\n\\n";
		}
		$response  = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body );
		$content   = '';
		$message   = '';
		$transient = array();
		if ( is_wp_error( $response ) ) {
			$message = esc_html__( 'Something went wrong with provided output from server.', 'momoacgwc' );
			$status  = 'bad';
		} else {
			if ( isset( $response['status'] ) && 401 === $response['status'] ) {
				$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Invalid API Keys.', 'momoacgwc' );
				$status   = 'bad';
			}
			if ( isset( $response['status'] ) && 404 === $response['status'] ) {
				$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Provided url not found.', 'momoacgwc' );
				$status   = 'bad';
			}
			if ( isset( $response['status'] ) && 429 === $response['status'] ) {
				$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Too many request.', 'momoacgwc' );
				$status   = 'bad';
			}
			if ( isset( $response['status'] ) && 400 === $response['status'] ) {
				$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Provided url not found.', 'momoacgwc' );
				$status   = 'bad';
			}
			if ( isset( $response['status'] ) && 200 === $response['status'] ) {
				$choices = isset( $response['body']->choices ) ? $response['body']->choices : array();

				$transient_ques = array(
					'role'    => 'user',
					'content' => $question,
				);
				$this->momo_chatbot_log_transient( $transient_ques );
				foreach ( $choices as $choice ) {
					if ( 'on' === $enable_ft_chatbot_modal && ! empty( $selected_ft_chatbot_modal ) ) {
						$message .= $choice->text;

						$transient = array(
							'role'    => 'system',
							'content' => $choice->text,
						);
					} else {
						$modeltype = $momoacgwc->fn->momo_get_model_type( $model );
						if ( 'chat' === $modeltype ) {
							$message .= $choice->message->content;

							$transient = array(
								'role'    => 'system',
								'content' => $choice->message->content,
							);
						} else {
							$message .= $choice->text;

							$transient = array(
								'role'    => 'system',
								'content' => $choice->text,
							);
						}
					}
					$status = 'good';
				}
				if ( ! empty( $transient ) && isset( $transient['content'] ) && ! empty( $transient['content'] ) ) {
					$this->momo_chatbot_log_transient( $transient );
				}
			}
		}
		$class = '';
		if ( 'bad' === $status ) {
			$class = 'acg-message-bad';
		}
		ob_start();
		?>
			<div class="acg-cb-message acg-ai <?php echo esc_attr( $class ); ?>">
				<span class="acg-message"><?php echo esc_html( $message ); ?></span>
				<span class="acg-name"><?php echo esc_html( $ai_name ); ?></span>
			</div>
		<?php
		$content = ob_get_clean();
		return new WP_REST_Response(
			array(
				'status'  => $status,
				'message' => '',
				'content' => $content,
			),
			200
		);
	}
	/**
	 * Load script and styles
	 *
	 * @return void
	 */
	public function momo_acg_cb_load_scripts_styles() {
		global $momoacgwc;
		wp_enqueue_style( 'momoacgcs_boxicons', $momoacgwc->plugin_url . 'assets/boxicons/css/boxicons.min.css', array(), '2.2.0' );
		wp_enqueue_style( 'momoacg_chatbot_style', $momoacgwc->plugin_url . 'chatbot/assets/momo_acg_chatbot.css', array(), $momoacgwc->version );
		wp_register_script( 'momoacg_chatbot_script', $momoacgwc->plugin_url . 'chatbot/assets/momo_acg_chatbot.js', array( 'jquery' ), $momoacgwc->version, true );

		$chatbot_settings = get_option( 'momo_acgwc_chatbot_settings' );
		$username         = isset( $chatbot_settings['username'] ) && ! empty( $chatbot_settings['username'] ) ? $chatbot_settings['username'] : esc_html__( 'User', 'momoacgwc' );
		$max_length       = isset( $chatbot_settings['max_length'] ) ? $chatbot_settings['max_length'] : 0;
		$typing           = isset( $chatbot_settings['typing'] ) ? $chatbot_settings['typing'] : '';

		$ajaxurl = array(
			'ajaxurl'              => admin_url( 'admin-ajax.php' ),
			'momoacgcb_ajax_nonce' => wp_create_nonce( 'wp_rest' ),
			'username'             => $username,
			'rest_endpoint'        => get_rest_url() . 'momoacgwc/v1/chatbot',
			'max_length'           => (int) $max_length,
			'typing'               => ( '' === $typing ) ? esc_html__( 'Collecting data', 'momoacgwc' ) : $typing,
		);
		wp_localize_script( 'momoacg_chatbot_script', 'momoacg_chatbot', $ajaxurl );
	}
	/**
	 * Generate Content Generator
	 *
	 * @param array $attributes Attributes.
	 * @param array $chatbot_cookie Cookie.
	 */
	public function momo_acg_generate_chatbot( $attributes, $chatbot_cookie ) {
		global $momoacgwc;
		$chatbot_settings = get_option( 'momo_acgwc_chatbot_settings' );
		$default_icon     = $momoacgwc->plugin_url . 'chatbot/assets/images/chatbot.svg';
		$context          = isset( $chatbot_settings['context'] ) ? $chatbot_settings['context'] : '';
		$model            = isset( $chatbot_settings['model'] ) ? $chatbot_settings['model'] : 'davinci';
		$temperature      = isset( $chatbot_settings['temperature'] ) ? $chatbot_settings['temperature'] : '0.2';
		$max_tokens       = isset( $chatbot_settings['max_tokens'] ) ? $chatbot_settings['max_tokens'] : '660';
		$sentence_buffer  = isset( $chatbot_settings['sentence_buffer'] ) ? $chatbot_settings['sentence_buffer'] : '';
		$max_length       = isset( $chatbot_settings['max_length'] ) ? $chatbot_settings['max_length'] : '';
		$embeddings_index = isset( $chatbot_settings['embeddings_index'] ) ? $chatbot_settings['embeddings_index'] : '';
		$ai_name          = isset( $chatbot_settings['ai_name'] ) && ! empty( $chatbot_settings['ai_name'] ) ? $chatbot_settings['ai_name'] : esc_html__( 'AI', 'momoacgwc' );
		$welcome_message  = isset( $chatbot_settings['welcome_message'] ) && ! empty( $chatbot_settings['welcome_message'] ) ? $chatbot_settings['welcome_message'] : esc_html__( 'How can I help you?', 'momoacgwc' );
		$username         = isset( $chatbot_settings['username'] ) && ! empty( $chatbot_settings['username'] ) ? $chatbot_settings['username'] : esc_html__( 'User', 'momoacgwc' );
		$placeholder      = isset( $chatbot_settings['placeholder'] ) && ! empty( $chatbot_settings['placeholder'] ) ? $chatbot_settings['placeholder'] : esc_html__( 'Type your message here ....', 'momoacgwc' );
		$position         = isset( $chatbot_settings['position'] ) ? $chatbot_settings['position'] : 'bright';
		$popup            = isset( $chatbot_settings['popup'] ) && ! empty( $chatbot_settings['popup'] ) ? $chatbot_settings['popup'] : esc_html__( 'Chatbot', 'momoacgwc' );
		$icon_url         = isset( $chatbot_settings['icon_url'] ) && ! empty( $chatbot_settings['icon_url'] ) ? $chatbot_settings['icon_url'] : $default_icon;
		$width            = isset( $chatbot_settings['width'] ) && ! empty( $chatbot_settings['width'] ) ? $chatbot_settings['width'] : '400px';
		$height           = isset( $chatbot_settings['height'] ) && ! empty( $chatbot_settings['height'] ) ? $chatbot_settings['height'] : '600px';
		switch ( $position ) {
			case 'bright':
				$positionc = 'bottom-right';
				break;
			case 'bleft':
				$positionc = 'bottom-left';
				break;
			case 'tright':
				$positionc = 'top-right';
				break;
			case 'tleft':
				$positionc = 'top-left';
				break;
			default:
				$positionc = 'bottom-right';
				break;
		}
		ob_start();
		?>
		<style>
			:root {
				--momo-cb-theme-color: #FF6978;
				--momo-cb-body-height: <?php echo esc_html( $height ); ?>;
				--momo-cb-top-height: 55px;
				--momo-cb-bottom-height: 55px;
				--momo-cb-body-width: <?php echo esc_html( $width ); ?>;
				--momo-cb-ai-message: #FF6978;
				--momo-cb-user-message: #E4E6EB;
			}
			.momo-chatbot-icon::before{
				content: url('<?php echo esc_url( $icon_url ); ?>');
				display: block;
				width: 35px;
				height: 35px;
			}
		</style>
		<div class="momo-acg-chatbot-circle <?php echo esc_attr( $positionc ); ?>">
			<!-- <i class="bx bxl-messenger"></i> -->
			<i class="momo-chatbot-icon"></i>
		</div>
		<div class="momo-acg-chatbox <?php echo esc_attr( $positionc ); ?>">
			<div class="momo-acg-cb-header">
				<?php echo esc_html( $popup ); ?>
				<span class="momo-acg-cb-toggle"><i class="bx bx-x"></i></span>
			</div>
			<div class="momo-acg-cb-body">
				<div class="momo-acg-cb-overlay">   
				</div>
				<div class="momo-acg-cb-logs">
					<?php
					if ( ! empty( $chatbot_cookie ) ) :
						foreach ( $chatbot_cookie as $message ) {
							$class = 'acg-ai';
							if ( 'user' === $message['role'] ) {
								$class = 'acg-user';
								$name  = $username;
								?>
								<div class="acg-cb-message <?php echo esc_attr( $class ); ?>">
									<span class="acg-name"><?php echo esc_html( $name ); ?></span>
									<span class="acg-message"><?php echo esc_html( $message['content'] ); ?></span>
								</div>
								<?php
							} else {
								$class = 'acg-ai';
								$name  = $ai_name;
								?>
								<div class="acg-cb-message <?php echo esc_attr( $class ); ?>">
									<span class="acg-message"><?php echo esc_html( $message['content'] ); ?></span>
									<span class="acg-name"><?php echo esc_html( $name ); ?></span>
								</div>
								<?php
							}
						}
						?>
					<?php else : ?>
					<div class="acg-cb-message acg-ai">
						<span class="acg-message"><?php echo esc_html( $welcome_message ); ?></span>
						<span class="acg-name"><?php echo esc_html( $ai_name ); ?></span>
					</div>
					<?php endif; ?>
				</div><!--chat-log -->
			</div>
			<div class="momo-acg-cb-footer">
				<div class="momo-chatbot-working"></div> 
				<div class="momo-acg-cb-input-form">
					<input type="text" id="momo-acg-cb-input" placeholder="<?php echo esc_attr( $placeholder ); ?>">
					<i class="bx bxs-send sender-button"></i>
				</div>      
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	/**
	 * Log Transient Data
	 *
	 * @param array $content Content.
	 */
	public function momo_chatbot_log_transient( $content ) {
		global $momoacgwc;
		$transient_id    = $this->momo_chatbot_get_transient_id();
		$chatbot_session = get_transient( $transient_id );
		if ( empty( $chatbot_session ) ) {
			$transient        = array();
			$chatbot_settings = get_option( 'momo_acgwc_chatbot_settings' );
			$welcome_message  = isset( $chatbot_settings['welcome_message'] ) && ! empty( $chatbot_settings['welcome_message'] ) ? $chatbot_settings['welcome_message'] : esc_html__( 'How can I help you?', 'momoacgwc' );
			$first_entry[0]   = array(
				'role'    => 'system',
				'content' => $welcome_message,
			);
			set_transient( $transient_id, $first_entry, 3600 );
			$momoacgwc->embeddings->momo_log_transient_to_option( $transient_id, 'new' );
		}
		if ( isset( $content['role'] ) && 'system' === $content['role'] ) {
			$momoacgwc->embeddings->momo_log_transient_to_option( $transient_id, 'old' );
		}
		$old_session = get_transient( $transient_id );
		array_push( $old_session, $content );
		set_transient( $transient_id, $old_session, 3600 );
	}
	/**
	 * Add previous conversation
	 *
	 * @param array $new_ques New question.
	 */
	public function momo_chatbot_add_previous_questions( $new_ques ) {
		$transient_id = $this->momo_chatbot_get_transient_id();
		$new_session  = ! empty( get_transient( $transient_id ) ) ? get_transient( $transient_id ) : array();
		array_push( $new_session, $new_ques );
		return $new_session;
	}
	/**
	 * Add context if any given
	 *
	 * @param array $message Message.
	 */
	public function momo_chatbot_add_context_to_start( $message ) {
		$chatbot_settings = get_option( 'momo_acgwc_chatbot_settings' );
		$context          = isset( $chatbot_settings['context'] ) ? $chatbot_settings['context'] : '';
		if ( empty( $context ) ) {
			return $message;
		} else {
			$content = array(
				'role'    => 'assistant',
				'content' => 'Context:' . $context,
			);
			array_unshift( $message, $content );
		}

		return $message;
	}
	/**
	 * Generate user input embeddings
	 *
	 * @param string $input User Input.
	 */
	public function momo_generate_embeddings_for_user_input( $input ) {
		global $momoacgwc;
		$plain = wp_strip_all_tags( $input );
		str_replace( array( "\r", "\n" ), '', $plain );

		$language_model = 'text-embedding-ada-002';
		$body           = array(
			'model' => $language_model,
			'input' => $plain,
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
		return $embeddings;
	}
	/**
	 * Check similarity function
	 *
	 * @param array  $embeddings Embeddings.
	 * @param string $context Context.
	 */
	public function momo_acg_cb_check_similarity( $embeddings, $context ) {
		$similarities = array();
		foreach ( $embeddings as $post_id => $embedding_array ) {
			foreach ( $embedding_array as $index => $embedding ) {
				$similarity                              = $this->momo_acg_cosine_similarity( $embedding, $context );
				$similarities[ $post_id . '_' . $index ] = $similarity;
			}
		}
		arsort( $similarities );
		$most_similar_post_id = key( $similarities );

		$most_similar_post_content = $this->momo_get_post_content_by_id_index( $most_similar_post_id );
		return $most_similar_post_content;

	}
	/**
	 * Get Post content by index.
	 *
	 * @param integer $post_id_with_index Post Index.
	 */
	public function momo_get_post_content_by_id_index( $post_id_with_index ) {
		global $momoacgwc;
		$parts     = explode( '_', $post_id_with_index );
		$post_id   = (int) $parts[0];
		$index     = (int) $parts[1];
		$fragments = $momoacgwc->embeddings->momo_get_post_sentence_fragments( $post_id );
		return $fragments[ $index ];
	}

	/**
	 * Checl cosine similarity
	 *
	 * @param array  $embedding Embeddings.
	 * @param string $context Context.
	 */
	public function momo_acg_cosine_similarity( $embedding, $context ) {
		$dot_product = 0;
		foreach ( $embedding as $i => $value ) {
			$dot_product += $value * $context[ $i ];
		}
		// Calculate magnitudes of embedding and context vectors.
		$embedding_magnitude = sqrt(
			array_sum(
				array_map(
					function ( $value ) {
						return $value * $value;
					},
					$embedding
				)
			)
		);

		$context_magnitude = sqrt(
			array_sum(
				array_map(
					function ( $value ) {
						return $value * $value;
					},
					$context
				)
			)
		);

		// Calculate cosine similarity.
		$cosine_similarity = $dot_product / ( $embedding_magnitude * $context_magnitude );

		return $cosine_similarity;
	}
	/**
	 * Get embeddings list
	 */
	public function momo_acg_get_embeddings_list() {
		global $momoacgwc;
		$types      = array(
			'post',
			'page',
			'product',
		);
		$embeddings = array();
		foreach ( $types as $type ) :
			$current_list = $momoacgwc->tblfn->momo_get_table_option( 'momo_acg_cb_trainings_list', $type );
			if ( ! empty( $current_list ) && is_array( $current_list ) ) {
				foreach ( $current_list as $id => $item ) {
					if ( isset( $item['content'] ) && ! empty( $item['content'] ) ) {
						$embeddings[ $id ] = $item['content'];
					}
				}
			}
		endforeach;
		return $embeddings;
	}
}
