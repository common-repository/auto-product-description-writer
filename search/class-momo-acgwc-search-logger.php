<?php
/**
 * Search Logger
 *
 * @package momoacgwc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger for Search
 */
class Momo_ACGWC_Search_Logger {
	/**
	 * Table Name
	 *
	 * @var string
	 */
	private $search_table;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb, $momoacgwc;
		$this->search_table = $wpdb->prefix . 'momo_search_logs';

		$search_settings   = get_option( 'momo_acg_wc_searchlog_settings' );
		$enable_search_log = isset( $search_settings['enable_search_log'] ) ? $search_settings['enable_search_log'] : 'off';
		if ( 'on' === $enable_search_log ) {
			add_action( 'woocommerce_product_query', array( $this, 'log_product_search' ) );
		}
		$ajax_events = array(
			'momo_acgwc_generate_template_edit_form' => 'momo_acgwc_generate_template_edit_form',
			'momo_acgwc_searchlog_update'            => 'momo_acgwc_searchlog_update',
			'momo_acgwc_searchlog_delete'            => 'momo_acgwc_searchlog_delete',
			'momo_acgwc_searchlog_email'             => 'momo_acgwc_searchlog_email',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
		}
	}
	/**
	 * Generate Template edit form.
	 */
	public function momo_acgwc_generate_template_edit_form() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acgwc_generate_template_edit_form' !== $_POST['action'] ) {
			return;
		}
		$log_id  = isset( $_POST['data'] ) ? sanitize_text_field( wp_unslash( $_POST['data'] ) ) : null;
		$content = $this->momo_generate_mail_template_form( $log_id );
		echo wp_json_encode(
			array(
				'status'  => 'good',
				'message' => esc_html__( 'Email template for search mail is ready.', 'momoacgwc' ),
				'content' => $content,
			)
		);
		exit;
	}
	/**
	 * Update Template.
	 */
	public function momo_acgwc_searchlog_update() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acgwc_searchlog_update' !== $_POST['action'] ) {
			return;
		}
		$log_id   = isset( $_POST['log_id'] ) ? sanitize_text_field( wp_unslash( $_POST['log_id'] ) ) : null;
		$template = isset( $_POST['template'] ) ? wc_sanitize_textarea( wp_unslash( $_POST['template'] ) ) : null;
		$momoacgwc->searchlogtb->momo_set_template_by_log_id( $log_id, $template );
		echo wp_json_encode(
			array(
				'status'  => 'good',
				'message' => esc_html__( 'Template updated successfully.', 'momoacgwc' ),
			)
		);
		exit;
	}
	/**
	 * Delete Template.
	 */
	public function momo_acgwc_searchlog_delete() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acgwc_searchlog_delete' !== $_POST['action'] ) {
			return;
		}
		$log_id = isset( $_POST['log_id'] ) ? sanitize_text_field( wp_unslash( $_POST['log_id'] ) ) : null;
		$result = $momoacgwc->searchlogtb->momo_delete_log_by_id( $log_id );
		if ( $result ) {
			echo wp_json_encode(
				array(
					'status'  => 'good',
					'message' => esc_html__( 'Search log deleted successfully.', 'momoacgwc' ),
				)
			);
			exit;
		} else {
			echo wp_json_encode(
				array(
					'status'  => 'bad',
					'message' => esc_html__( 'Something went wrong while deleting search log.', 'momoacgwc' ),
				)
			);
			exit;
		}
	}
	/**
	 * Email Template.
	 */
	public function momo_acgwc_searchlog_email() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acgwc_searchlog_email' !== $_POST['action'] ) {
			return;
		}
		$log_id  = isset( $_POST['log_id'] ) ? sanitize_text_field( wp_unslash( $_POST['log_id'] ) ) : null;
		$log     = $momoacgwc->searchlogtb->momo_get_all_by_id( $log_id );
		$status  = '';
		$message = '';
		if ( ! $log ) {
			$status  = 'bad';
			$message = esc_html__( 'Search log not found in database', 'momoacgwc' );
			echo wp_json_encode(
				array(
					'status'  => $status,
					'message' => $message,
				)
			);
			exit;
		}
		$user_info = get_userdata( $log->user_id );
		if ( ! $user_info ) {
			$status  = 'bad';
			$message = esc_html__( 'No user found with the given user ID.', 'momoacgwc' );
			echo wp_json_encode(
				array(
					'status'  => $status,
					'message' => $message,
				)
			);
			exit;
		}

		$user_email = $user_info->user_email;
		$user_name  = $user_info->display_name;

		$template = $momoacgwc->searchlogtb->momo_get_template_by_log_id( $log_id );

		if ( ! $template ) {
			$status  = 'bad';
			$message = esc_html__( 'No email template found for this log.', 'momoacgwc' );
			echo wp_json_encode(
				array(
					'status'  => $status,
					'message' => $message,
				)
			);
			exit;
		}
		$search_settings = get_option( 'momo_acg_wc_searchlog_settings' );
		$email_subject   = isset( $search_settings['email_subject'] ) ? $search_settings['email_subject'] : esc_html__( 'Your search result for {search_term}', 'momoacgwc' );

		$message = str_replace( array( '{username}', '{search_term}' ), array( $user_name, $log->search_term ), $template );
		$subject = str_replace( array( '{username}', '{search_term}' ), array( $user_name, $log->search_term ), $email_subject );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$sent = wp_mail( $user_email, $subject, $message, $headers );

		if ( $sent ) {
			$momoacgwc->searchlogtb->log_email_sent( $log_id );
			$status  = 'good';
			$message = esc_html__( 'Email sent successfully.', 'momoacgwc' );
			echo wp_json_encode(
				array(
					'status'  => $status,
					'message' => $message,
				)
			);
			exit;
		} else {
			$status  = 'bad';
			$message = esc_html__( 'Email sending failed.', 'momoacgwc' );
			echo wp_json_encode(
				array(
					'status'  => $status,
					'message' => $message,
				)
			);
			exit;
		}
	}
	/**
	 * Generate Form
	 *
	 * @param integer $log_id Log ID.
	 */
	public function momo_generate_mail_template_form( $log_id ) {
		global $momoacgwc;
		// Get the existing template for the log ID
		$template = $momoacgwc->searchlogtb->momo_get_template_by_log_id( $log_id );

		ob_start();
		?>

		<div id="momo_mail_template_form">
			<!-- Hidden field to store Log ID -->
			<input type="hidden" name="log_id" value="<?php echo esc_attr( $log_id ); ?>" />

			<!-- Textarea for the email template -->
			<div class="momo-form-field">
				<label for="momo_mail_template">Edit Email Template</label>
				<textarea id="momo_mail_template" class="momo_tinymce_trigger_textarea" name="momo_mail_template" rows="10"><?php echo esc_textarea( $template ); ?></textarea>
			</div>

			<!-- Buttons -->
			<div class="momo-form-actions">
				<span name="momo_update_template" class="button button-primary momo_search_log_update"><?php esc_html_e( 'Update Template', 'momoacgwc' ); ?></span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Log WooCommerce product search.
	 *
	 * @param WC_Query $query WooCommerce query object.
	 */
	public function log_product_search( $query ) {
		// Check if it's a search query.
		if ( ! is_search() ) {
			return;
		}

		// Get the search term.
		$search_term = get_search_query();
		// Ensure the search term is not empty.
		if ( empty( $search_term ) ) {
			return;
		}

		// Log the search term along with the user ID.
		$user_id = get_current_user_id();
		if ( 0 !== $user_id ) {
			$this->store_log( $user_id, $search_term );
		}
	}

	/**
	 * Store search log in the database
	 *
	 * @param int    $user_id The ID of the user who performed the search.
	 * @param string $search_term The search term entered by the user.
	 */
	private function store_log( $user_id, $search_term ) {
		global $wpdb, $momoacgwc;
		$table_name = $wpdb->prefix . 'momo_acgwc_search_logs';
		// Check if the table exists before trying to log.
		$table_exists = $momoacgwc->searchlogtb->check_table_exist( $table_name );
		if ( ! $table_exists ) {
			return;
		}
		$momoacgwc->searchlogbg->schedule_email_template_save( $user_id, $search_term );
		/* $template = $this->generate_email_template( $user_id, $search_term );
		$momoacgwc->searchlogtb->insert_log( $user_id, $search_term, current_time( 'mysql' ), $template ); */
	}
	/**
	 * Generates an email template for a given user and search term.
	 *
	 * @param int    $user_id       The ID of the user.
	 * @param string $search_term   The search term entered by the user.
	 * @return string The generated email template.
	 */
	public function generate_email_template( $user_id, $search_term ) {
		$template   = '';
		$user_info  = get_userdata( $user_id );
		$first_name = $user_info->first_name;
		$last_name  = $user_info->last_name;
		$full_name  = $first_name . ' ' . $last_name;

		$matching_products = $this->get_matching_products( $search_term );
		$ai_query          = $this->create_openai_query( $matching_products );
		$template          = $this->create_ai_email_content( $full_name, $search_term, $ai_query );
		return $template;
	}
	/**
	 * Retrieves a list of products that match the given search term.
	 *
	 * @param string $search_term The term to search for in products.
	 * @return array A list of products that match the search term.
	 */
	private function get_matching_products( $search_term ) {
		$args  = array(
			'post_type'      => 'product',
			's'              => $search_term,
			'posts_per_page' => 10,
		);
		$query = new WP_Query( $args );
		return $query;
	}
	/**
	 * Generates a query string for OpenAI based on the provided products.
	 *
	 * @param array $products A list of products to include in the query string.
	 * @return string The generated query string.
	 */
	private function create_openai_query( $products ) {
		$content = "Producs from search:\n\n";
		if ( $products->have_posts() ) {
			while ( $products->have_posts() ) {
				$products->the_post();
				$product_id = get_the_ID();
				$content   .= $this->get_product_gist( $product_id );
			}
			wp_reset_postdata();
		}
		return $content;
	}
	/**
	 * Get a short summary of the WooCommerce product.
	 *
	 * @param int $product_id Product ID.
	 * @return string Product gist (description, price, variations).
	 */
	private function get_product_gist( $product_id ) {
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return '';
		}

		// Get product details.
		$title       = $product->get_title();
		$price       = $product->get_price_html();
		$description = wp_trim_words( $product->get_short_description(), 20, '...' );

		// Check for variations if it's a variable product.
		$variations = '';
		if ( $product->is_type( 'variable' ) ) {
			$available_variations = $product->get_available_variations();
			foreach ( $available_variations as $variation ) {
				$variation_obj = wc_get_product( $variation['variation_id'] );
				$variations   .= $variation_obj->get_price_html() . ', ';
			}
			$variations = rtrim( $variations, ', ' );
		}

		// Create the gist.
		$gist = "{$title}: {$description} - Price: {$price}";
		$link = $product->get_permalink();
		if ( $variations ) {
			$gist .= " | Variations: {$variations}";
		}
		$gist .= " | URL: {$link}\n";
		return $gist;
	}

	/**
	 * Generates a personalized email content for a customer based on their search term and recommended products using OpenAI API.
	 *
	 * @param string $user_name The name of the customer.
	 * @param string $search_term The search term entered by the customer.
	 * @param array  $products A list of products to recommend to the customer.
	 * @return string The generated email content.
	 */
	private function create_ai_email_content( $user_name, $search_term, $products ) {
		global $momoacgwc;
		$openai_settings = get_option( 'momo_acg_wc_openai_settings' );
		$default_model   = isset( $openai_settings['default_model'] ) ? $openai_settings['default_model'] : 'gpt-3.5-turbo';
		$default_lang    = isset( $openai_settings['default_lang'] ) ? $openai_settings['default_lang'] : 'english';

		$model     = $default_model;
		$modeltype = $momoacgwc->fn->momo_get_model_type( $model );

		$prompt    = "Create a personalized email in '$default_lang' language for a customer named '$user_name', based on their search term '$search_term', recommending the following products: $products (just email body please)";
		$message[] = array(
			'role'    => 'user',
			'content' => $prompt,
		);
		$message[] = $this->momo_searchmail_helper_get_initial_message();

		$temperature = '0.7';
		$max_tokens  = '660';
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
		$response = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body );
		$message  = '';
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

		return $message;
	}
	/**
	 * Get some predefined cotext
	 */
	public function momo_searchmail_helper_get_initial_message() {
		$message = array(
			'role'    => 'system',
			'content' => esc_html__( "You are an AI assistant, your task is to generate and modify content based on user requests. This functionality is integrated into the AI Tools developed by MoMo Themes. Users interact with you through a search result, and want an email ready to be sent. Strictly follow these rules: Format your responses in HTML syntax (email ready, just info inside of <body></body>, no other letters or words outside <body> please), ready to be send through email.\\n\\n- If you cannot generate a meaningful response to a user’s request, reply with '__MOMO_AI_HELPER_ERROR__'. This term should only be used in this context, it is used to generate user facing errors.\\n\\n", 'momoacgwc' ),
		);
		return $message;
	}
}
new Momo_ACGWC_Search_Logger();
