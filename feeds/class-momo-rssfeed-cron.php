<?php
/**
 * RSS Feed Cron
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v3.2.0
 */
class MoMo_RssFeed_Cron {
	/**
	 * Cron Hook(s)
	 *
	 * @var array
	 */
	private $cron_hook;
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->cron_hook = array(
			'single_content'   => 'momo_acg_rssfeed_hook',
			'autoblog_content' => 'momo_acg_autoblog_hook',
		);
		add_action( 'momo_acg_rssfeed_hook', array( $this, 'momo_acg_single_content_writer' ), 10, 4 );
		add_action( 'momo_acg_autoblog_hook', array( $this, 'momo_acg_autoblog_content_writer' ), 10, 9 );

		$this->momo_add_actions_to_existing_hooks( 'momo_acg_autoblog_hook' );

	}
	/**
	 * Add action to existing hook.
	 *
	 * @param string $search_hook Search term.
	 */
	public function momo_add_actions_to_existing_hooks( $search_hook ) {
		$existing_hooks = $this->momo_get_existing_hooks( $search_hook );

		foreach ( $existing_hooks as $hook_name ) {
			add_action( $hook_name, array( $this, 'momo_acg_autoblog_content_writer' ), 10, 9 );
		}
	}
	/**
	 * Get existing hook from search
	 *
	 * @param string $search_hook Search term.
	 */
	public function momo_get_existing_hooks( $search_hook ) {
		$existing_hooks = array();

		$scheduled_events = _get_cron_array();

		foreach ( $scheduled_events as $timestamp => $events ) {
			foreach ( $events as $hook => $data ) {
				if ( preg_match( '/^' . $search_hook . '_\d+$/', $hook ) ) {
					$existing_hooks[] = $hook;
				}
			}
		}
		return $existing_hooks;
	}
	/**
	 * Add each line to cron queue
	 *
	 * @param array $line Arguments.
	 */
	public function momo_add_item_to_queue( $line ) {
		$title    = $line['title'];
		$date     = $line['date'];
		$noofpara = $line['noofpara'];
		$index    = $line['index'];
		$ptype    = $line['ptype'];
		$addimage = isset( $line['addimage'] ) ? $line['addimage'] : 'off';
		$category = isset( $line['category'] ) ? $line['category'] : '';
		if ( empty( $noofpara ) ) {
			$noofpara = 4;
		}
		if ( empty( $date ) ) {
			$time = time() + ( 60 * 5 * (int) $index );
		} else {
			$time = strtotime( $date );
		}
		$args = array(
			'title'    => $title,
			'noofpara' => $noofpara,
			'other'    => array(),
			'time'     => $time,
			'ptype'    => $ptype,
			'addimage' => $addimage,
			'category' => $category,
		);
		return $this->momo_acg_schedule_single_content_writer( $args );
	}
	/**
	 * Add to Autoblog
	 *
	 * @param array $args Arguments.
	 */
	public function momo_add_autoblog_to_queue( $args ) {
		$time_basis = $args['time_basis'];

		$unique_identifier = time();
		$hook              = 'momo_acg_autoblog_hook_' . $unique_identifier;
		if ( ! wp_next_scheduled( $hook ) ) {
			$array_values = array_values( $args );
			add_action( $hook, array( $this, 'momo_acg_autoblog_content_writer' ), 10, 9 );

			$return = wp_schedule_event( time(), $time_basis, $hook, $args );

			if ( is_wp_error( $return ) ) {
				return false;
			} else {
				return true;
			}
		}
		return false;
	}
	/**
	 * Schedule single content writer.
	 *
	 * @param array $args Arguments.
	 */
	public function momo_acg_schedule_single_content_writer( $args ) {
		$key      = md5( wp_json_encode( $args ) );
		$title    = isset( $args['title'] ) ? $args['title'] : '';
		$noofpara = isset( $args['noofpara'] ) ? $args['noofpara'] : '';
		$other    = isset( $args['other'] ) ? $args['other'] : array();
		$time     = isset( $args['time'] ) ? $args['time'] : time();
		$ptype    = isset( $args['ptype'] ) && ! empty( $args['ptype'] ) ? $args['ptype'] : 'momoacg_post_draft';

		$other['addimage'] = $args['addimage'];
		$other['category'] = $args['category'];

		$return = wp_schedule_single_event( $time, $this->cron_hook['single_content'], array( $title, $noofpara, $ptype, $other ) );
		if ( is_wp_error( $return ) ) {
			return false;
		}
		$this->momo_acg_update_single_event_list( $key, $args, 'add' );
		return true;
	}
	/**
	 * Generate Autoblog
	 *
	 * @param string $tags Tags.
	 * @param string $category Category.
	 * @param string $status Saving status.
	 * @param int    $no_of_posts Number of posts.
	 * @param string $time_basis Time basis.
	 * @param string $writing_style Writing style.
	 * @param string $add_image Add image.
	 */
	public function momo_acg_autoblog_content_writer( $tags, $category, $status, $no_of_posts, $time_basis, $writing_style, $add_image, $gen_title = 'off', $no_of_para = 5 ) {
		$other['addimage']   = $add_image;
		$other['category']   = $category;
		$other['gen_title']  = $gen_title;
		$other['no_of_para'] = $no_of_para;
		$noofpara            = $no_of_para;
		$ptype               = $status;
		/* translators: %s: tags */
		$title = sprintf( esc_html__( 'a blog title with following tags ( %s ) ', 'momoacgwc' ), $tags, $writing_style );
		for ( $i = 1; $i <= (int) $no_of_posts; $i++ ) {
			$this->momo_acg_single_content_writer( $title, $noofpara, $ptype, $other, $tags, $writing_style );
		}
	}
	/**
	 * Fires single content writer
	 *
	 * @param string  $title Title.
	 * @param integer $noofpara No of Paragraphs.
	 * @param string  $ptype Post Type.
	 * @param array   $other Other.
	 */
	public function momo_acg_single_content_writer( $title, $noofpara, $ptype, $other, $tags = '', $wstyle = '' ) {
		//https://feeds.a.dj.com/rss/RSSLifestyle.xml
		global $momoacgwc;
		$args = array(
			'title'    => $title,
			'nopara'   => $noofpara,
			'language' => 'english',
		);
		if ( ! empty( $wstyle ) ) {
			$args['writing_style'] = $wstyle;
		}
		/* $headings = $momoacgwc->api->momoacg_openai_generate_headings_array( $args );
		$output   = $momoacgwc->api->momo_acg_generate_content_from_headings( $headings, $args ); */

		$addimage = isset( $other['addimage'] ) ? $other['addimage'] : 'off';
		$category = isset( $other['category'] ) ? $other['category'] : '';

		$openai_settings = get_option( 'momo_acg_wc_openai_settings' );

		$default_model = isset( $openai_settings['default_model'] ) ? $openai_settings['default_model'] : 'gpt-3.5-turbo';
		$default_lang  = isset( $openai_settings['default_lang'] ) ? $openai_settings['default_lang'] : 'english';

		$model     = $default_model;
		$modeltype = $momoacgwc->fn->momo_get_model_type( $model );

		$temperature = '0.7';
		$max_tokens  = '660';

		$initial   = $this->momo_autoblog_get_initial_message();
		$message   = array();
		$message[] = $initial;
		/* translators: %1$s: langugage, %2$s: tags, %3$s: writing style, %4$s: number of paragraph */
		$title = sprintf( esc_html__( 'Write a blog post in %1$s language with following tags ( %2$s ) in a %3$s writing style with %4$s number of paragraph(s).', 'momoacgwc' ), $default_lang, $tags, $wstyle, $noofpara );
		if ( isset( $other['gen_title'] ) && 'on' === $other['gen_title'] ) {
			$title .= esc_html__( ' Please provide a Title (# Title: ) for that entire blog post at beginning of this post.', 'momoacgwc' );
		}
		$prompt    = $title;
		$new_msg   = array(
			'role'    => 'user',
			'content' => $prompt,
		);
		$message[] = $new_msg;
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
		$content  = '';
		$message  = '';
		$status   = 'bad';
		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
			$status  = 'bad';
		} else {
			if ( isset( $response['status'] ) && 404 === $response['status'] ) {
				$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Provided url not found.', 'momoacgwc' );
				$status   = 'bad';
			}
			if ( isset( $response['status'] ) && 400 === $response['status'] ) {
				$message .= isset( $response['body']->error->message ) ? $response['body']->error->message : esc_html__( 'Provided url not found.', 'momoacgwc' );
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
		}

		$content_title = $this->momo_acg_parsedown_markdown( $message );

		if ( 'on' === $addimage ) {
			$image = $momoacgwc->api->momo_acg_generate_image_from_title( $title );
			if ( ! empty( $image ) ) {
				$content_title['content'] .= $image;
			}
		}
		if ( isset( $content_title['content'] ) && ! empty( $content_title['content'] ) ) {
			$posttype = 'momoacgwc';
			$pstatus  = 'draft';
			switch ( $ptype ) {
				case 'momoacg_post_draft':
					$posttype = 'momoacgwc';
					$pstatus  = 'draft';
					break;
				case 'wp_post_draft':
					$posttype = 'post';
					$pstatus  = 'draft';
					break;
				case 'wp_post_publish':
					$posttype = 'post';
					$pstatus  = 'publish';
					break;
			}
			$data = array(
				'post_title'   => ! empty( $content_title['title'] ) && ( isset( $other['gen_title'] ) && 'on' === $other['gen_title'] ) ? $content_title['title'] : $tags,
				'post_content' => ! empty( $content_title['content'] ) ? $content_title['content'] : $message,
				'post_status'  => $pstatus,
				'post_type'    => $posttype,
			);
			if ( 'wp_post_draft' === $ptype || 'wp_post_publish' ) {
				if ( ! empty( $category ) ) {
					$data['post_category'] = array( (int) $category );
				}
			}
			$return = wp_insert_post( wp_slash( $data ) );
		}
	}
	/**
	 * Parse markdown
	 *
	 * @param string $message Message.
	 */
	public function momo_acg_parsedown_markdown_old( $message ) {
		require_once 'Parsedown.php';
		$parser = new Parsedown();

		// Parse the Markdown text into HTML.
		$html = $parser->text( $message );

		// Extract the title based on the presence of an h1 heading.
		$dom = new DOMDocument();
		$dom->loadHTML( $html );

		// Remove the <!DOCTYPE> declaration.
		$doctype = $dom->doctype;
		if ( $doctype ) {
			$doctype->parentNode->removeChild( $doctype ); //phpcs:ignore
		}
		// Define elements to remove.

		$title   = '';
		$content = '';

		// Check for an h1 heading and remove it.
		$h1 = $dom->getElementsByTagName( 'h1' );
		if ( $h1->length > 0 ) {
			$title = $h1->item( 0 )->textContent;
			$h1->item( 0 )->parentNode->removeChild( $h1->item( 0 ) );
			$title = preg_replace( '/^Title:\s*/', '', $title );
		}
		$body_element = $dom->getElementsByTagName( 'body' )->item( 0 );

		$content = $dom->saveHTML( $body_element ); //phpcs:ignore

		return array(
			'title'   => $title,
			'content' => $content,
		);
	}
	/**
	 * Parse markdown
	 *
	 * @param string $message Message.
	 */
	public function momo_acg_parsedown_markdown( $message ) {
		require_once 'Parsedown.php';
		$parser = new Parsedown();

		// Parse the Markdown text into HTML.
		$html = $parser->text( $message );

		// Load the HTML into DOMDocument, specifying UTF-8 encoding.
		$dom = new DOMDocument();
		@$dom->loadHTML( '<?xml encoding="UTF-8">' . $html); // Use UTF-8 explicitly.

		// Remove the <!DOCTYPE> declaration if present.
		$doctype = $dom->doctype;
		if ( $doctype ) {
			$doctype->parentNode->removeChild( $doctype ); //phpcs:ignore
		}

		// Extract the title based on the presence of an h1 heading.
		$title = '';
		$h1 = $dom->getElementsByTagName( 'h1' );
		if ( $h1->length > 0 ) {
			$title = $h1->item( 0 )->textContent;
			$h1->item( 0 )->parentNode->removeChild( $h1->item( 0 ) );
			$title = preg_replace( '/^Title:\s*/', '', $title );
		}

		// Extract the body content without added <html> and <body> tags.
		$body_element = $dom->getElementsByTagName( 'body' )->item( 0 );
		$content = '';
		if ( $body_element ) {
			foreach ( $body_element->childNodes as $child ) {
				$content .= $dom->saveHTML( $child ); // Save only child nodes inside <body>.
			}
		}

		return array(
			'title'   => $title,
			'content' => $content,
		);
	}

	/**
	 * Update Single event list options
	 *
	 * @param string $key Key.
	 * @param array  $args Arguments.
	 * @param string $type Add or Delete.
	 */
	public function momo_acg_update_single_event_list( $key, $args, $type = 'add' ) {
		$single_event_list = get_option( 'momo_acg_rssfeed_event_list' );
		if ( 'add' === $type ) {
			$single_event_list[ $key ] = $args;
			update_option( 'momo_acg_rssfeed_event_list', $single_event_list );
		}
	}
	/**
	 * Get some predefined cotext
	 */
	public function momo_autoblog_get_initial_message() {
		$message = array(
			'role'    => 'system',
			'content' => esc_html__( "You are an AI assistant, your task is to generate and modify content based on user requests. This functionality is integrated into the AI Tools developed by MoMo Themes. Users interact with you through a Gutenberg block, you are inside the Wordpress editor. Strictly follow these rules: Format your responses in Markdown syntax, ready to be published with some context about user message.\\n\\n- Execute the request without any acknowledgement to the user.\\n\\n- Avoid sensitive or controversial topics and ensure your responses are grammatically correct and coherent.\\n\\n- If you cannot generate a meaningful response to a user’s request, reply with '__MOMO_AI_HELPER_ERROR__'. This term should only be used in this context, it is used to generate user facing errors.\\n\\n", 'momoacgwc' ),
		);
		return $message;
	}
}
