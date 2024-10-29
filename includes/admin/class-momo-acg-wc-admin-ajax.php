<?php
/**
 * MoMo ACGWC - Amin AJAX functions
 *
 * @package momoacgwc
 * @author MoMo Themes
 * @since v1.0.0
 */
class MoMo_ACG_WC_Admin_Ajax {
	/**
	 * Constructor
	 */
	public function __construct() {
		$ajax_events = array(
			'momo_acg_wc_openai_generate_product' => 'momo_acg_wc_openai_generate_product', // One.
			'momo_acg_wc_export_settings'         => 'momo_acg_wc_export_settings', // Two.
			'momo_acg_wc_import_settings'         => 'momo_acg_wc_import_settings', // Three.
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
		}
	}
	/**
	 * Generate OpenAI Content ( One )
	 */
	public function momo_acg_wc_openai_generate_product() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_wc_openai_generate_product' !== $_POST['action'] ) {
			return;
		}
		$language          = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';
		$model             = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : 'text-davinci-003';
		$title             = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$product_id        = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
		$temperature       = isset( $_POST['temperature'] ) ? sanitize_text_field( wp_unslash( $_POST['temperature'] ) ) : 0.7;
		$max_tokens        = isset( $_POST['max_tokens'] ) ? sanitize_text_field( wp_unslash( $_POST['max_tokens'] ) ) : 2000;
		$top_p             = isset( $_POST['top_p'] ) ? sanitize_text_field( wp_unslash( $_POST['top_p'] ) ) : 1.0;
		$frequency_penalty = isset( $_POST['frequency_penalty'] ) ? sanitize_text_field( wp_unslash( $_POST['frequency_penalty'] ) ) : 0.0;
		$presence_penalty  = isset( $_POST['presence_penalty'] ) ? sanitize_text_field( wp_unslash( $_POST['presence_penalty'] ) ) : 0.0;
		$addimage          = isset( $_POST['addimage'] ) ? sanitize_text_field( wp_unslash( $_POST['addimage'] ) ) : 'off';
		$addgallery        = isset( $_POST['addgallery'] ) ? sanitize_text_field( wp_unslash( $_POST['addgallery'] ) ) : 'off';
		$args              = array(
			'language'          => $language,
			'model'             => $model,
			'title'             => $title,
			'product_id'        => $product_id,
			'temperature'       => $temperature,
			'max_tokens'        => $max_tokens,
			'top_p'             => $top_p,
			'frequency_penalty' => $frequency_penalty,
			'presence_penalty'  => $presence_penalty,
			'addimage'          => $addimage,
			'addgallery'        => $addgallery,
		);
		$momoacgwc->api->momoacgwc_openai_generate_content_output_json( $args );
	}
	/**
	 * Export Settings ( Two )
	 */
	public function momo_acg_wc_export_settings() {
		$res = check_ajax_referer( 'momo_acg_wc_export_settings', 'nonce' );
		if ( isset( $_POST['action'] ) && 'momo_acg_wc_export_settings' !== $_POST['action'] ) {
			return;
		}

		header( 'Content-type: text/plain' );
		header( 'Content-Disposition: attachment; filename=Woo_Product_Writer_Settings__' . gmdate( 'd-m-y' ) . '.json' );

		$json           = array();
		$acg_wc_options = get_option( 'momo_acg_wc_openai_settings' );
		foreach ( $acg_wc_options as $field => $option ) {
			$skip_fields = array(
				'option_page',
				'action',
				'_wpnonce',
				'_wp_http_referer',
			);
			if ( in_array( $field, $skip_fields, true ) ) {
				continue;
			}
			$json[ $field ] = $option;
		}

		echo wp_json_encode( $json );
		exit;
	}
	/**
	 * Import Settings ( Three )
	 */
	public function momo_acg_wc_import_settings() {
		global $momoacgwc;
		$res = check_ajax_referer( 'momoacgwc_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'momo_acg_wc_import_settings' !== $_POST['action'] ) {
			return;
		}
		// Sanitization done with own function.
		$jsondata = isset( $_POST['jsondata'] ) ? $momoacgwc->fn->momo_recursive_sanitize_post_fields( wp_unslash( $_POST['jsondata'] ) ) : array(); // phpcs:ignore
		$message  = '';
		if ( ! is_array( $jsondata ) ) {
			$message = esc_html__( 'Not correct json format!', 'momoacgwc' );
			$status  = 'bad';
		} else {
			update_option( 'momo_acg_wc_openai_settings', $jsondata );
			$message = esc_html__( 'Successfully updated settings!', 'momoacgwc' );
			$status  = 'good';
		}
		echo wp_json_encode(
			array(
				'status'  => $status,
				'message' => $message,
			)
		);
		exit;
	}
}
new MoMo_ACG_WC_Admin_Ajax();
