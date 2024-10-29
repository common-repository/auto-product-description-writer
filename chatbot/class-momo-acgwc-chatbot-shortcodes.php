<?php
/**
 * MoMo Chstbot Shortcodes
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v3.10.0
 */
class MoMo_ACGWC_Chatbot_Shortcodes {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'momo_add_single_chatbot', array( $this, 'momo_add_single_chatbot' ) );
	}
	/**
	 * Add Content Chatbot UI
	 *
	 * @param array $attributes Attributes.
	 */
	public function momo_add_single_chatbot( $attributes ) {
		global $momoacgwc;
		$transient_id = $momoacgwc->chatbot->momo_chatbot_get_transient_id();
		wp_enqueue_script( 'momoacg_chatbot_script' );
		$chatbot_session = get_transient( $transient_id );
		if ( empty( $chatbot_session ) ) {
			set_transient( $transient_id, array(), time() + 3600 );
			$chatbot_session = get_transient( $transient_id );
		}
		return $momoacgwc->chatbot->momo_acg_generate_chatbot( $attributes, $chatbot_session );
	}
}
new MoMo_ACGWC_Chatbot_Shortcodes();
