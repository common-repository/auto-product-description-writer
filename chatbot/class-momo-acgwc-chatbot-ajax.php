<?php
/**
 * MoMo ACG - Chatbot AJAX functions
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v3.10.0
 */
class MoMo_ACGWC_Chatbot_Ajax {
	/**
	 * Constructor
	 */
	public function __construct() {
		$ajax_events = array(
			'momo_acg_openai_generate_content_fe' => 'momo_acg_openai_generate_content_fe', // One.

		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
		}
	}
	/**
	 * Generate OpenAI Content ( One )
	 */
	public function momo_acg_openai_generate_content_fe() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgcs_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_openai_generate_content_fe' !== $_POST['action'] ) {
			return;
		}
		$language        = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';
		$title           = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$addimage        = isset( $_POST['addimage'] ) ? sanitize_text_field( wp_unslash( $_POST['addimage'] ) ) : 'off';
		$addintroduction = isset( $_POST['addintroduction'] ) ? sanitize_text_field( wp_unslash( $_POST['addintroduction'] ) ) : 'off';
		$addconclusion   = isset( $_POST['addconclusion'] ) ? sanitize_text_field( wp_unslash( $_POST['addconclusion'] ) ) : 'off';
		$addheadings     = isset( $_POST['addheadings'] ) ? sanitize_text_field( wp_unslash( $_POST['addheadings'] ) ) : 'off';
		$nopara          = isset( $_POST['nopara'] ) ? sanitize_text_field( wp_unslash( $_POST['nopara'] ) ) : 4;
		$writing_style   = isset( $_POST['writing_style'] ) ? sanitize_text_field( wp_unslash( $_POST['writing_style'] ) ) : 'informative';
		$headingwrapper  = isset( $_POST['headingwrapper'] ) ? sanitize_text_field( wp_unslash( $_POST['headingwrapper'] ) ) : 'h1';
		$addhyperlink    = isset( $_POST['addhyperlink'] ) ? sanitize_text_field( wp_unslash( $_POST['addhyperlink'] ) ) : 'off';
		$hyperlink_text  = isset( $_POST['hyperlink_text'] ) ? sanitize_text_field( wp_unslash( $_POST['hyperlink_text'] ) ) : '';
		$anchor_link     = isset( $_POST['anchor_link'] ) ? sanitize_text_field( wp_unslash( $_POST['anchor_link'] ) ) : '';
		$modifyheadings  = isset( $_POST['modifyheadings'] ) ? sanitize_text_field( wp_unslash( $_POST['modifyheadings'] ) ) : 'off';
		$headings        = isset( $_POST['headings'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['headings'] ) ) : array();

		$args = array(
			'language'        => $language,
			'title'           => $title,
			'addimage'        => $addimage,
			'addintroduction' => $addintroduction,
			'addconclusion'   => $addconclusion,
			'addheadings'     => $addheadings,
			'nopara'          => $nopara,
			'writing_style'   => $writing_style,
			'headingwrapper'  => $headingwrapper,
			'addhyperlink'    => $addhyperlink,
			'hyperlink_text'  => $hyperlink_text,
			'anchor_link'     => $anchor_link,
			'modifyheadings'  => $modifyheadings,
			'headings'        => $headings,
		);
		$momoacgwc->api->momoacg_openai_generate_content_output_json( $args );
	}
	/**
	 * Generate Exit Signal.
	 *
	 * @param string $message Message string.
	 */
	public function momo_generate_exit_signal( $message ) {
		echo wp_json_encode(
			array(
				'status' => 'bad',
				'msg'    => $message,
				'stage'  => 'stop',
			)
		);
		exit;
	}
}
new MoMo_ACGWC_Chatbot_Ajax();
