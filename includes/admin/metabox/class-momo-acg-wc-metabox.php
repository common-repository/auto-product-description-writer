<?php
/**
 * Woo Product Writer Metabox
 *
 * @package momoacgwc
 * @author MoMo Themes
 * @since v1.0.0
 */
class MoMo_ACG_WC_Metabox {
	/**
	 * The title for your meta box.
	 *
	 * @var string
	 */
	public $title;
	/**
	 * The context for your meta box.
	 *
	 * @var string
	 */
	public $context;
	/**
	 * The priority for your meta box.
	 *
	 * @var string
	 */
	public $priority;
	/**
	 * Allowed post type
	 *
	 * @var array
	 */
	private $allowed_post_type = array(
		'product',
	);
	/**
	 * Constructor
	 */
	public function __construct() {
		global $momoacgwc;
		$this->title    = esc_html__( 'Product Description', 'momoacgwc' );
		$this->context  = 'side';
		$this->priority = 'default';
		add_action( 'add_meta_boxes', array( $this, 'momo_acg_wc_add_meta_boxes' ) );
	}
	/**
	 * Add OpenAI Content Generator
	 */
	public function momo_acg_wc_add_meta_boxes() {
		foreach ( $this->allowed_post_type as $screen ) {
			add_meta_box(
				'woocommerce-content-generator',
				$this->title,
				array( $this, 'momo_acg_wc_amb_callback' ),
				$screen,
				$this->context,
				$this->priority
			);
		}
	}
	/**
	 * Metaxbox Creator
	 */
	public function momo_acg_wc_amb_callback() {
		global $momoacgwc;
		$openai_settings   = get_option( 'momo_acg_wc_openai_settings' );
		$temperature       = 0.7;
		$max_tokens        = 2000;
		$top_p             = 1.0;
		$frequency_penalty = 0.0;
		$presence_penalty  = 0.0;
		$openai_settings   = get_option( 'momo_acg_wc_openai_settings' );

		$default_model = isset( $openai_settings['default_model'] ) ? $openai_settings['default_model'] : 'text-davinci-003';
		$default_lang  = isset( $openai_settings['default_lang'] ) ? $openai_settings['default_lang'] : 'english';

		$fields  = array(
			array(
				'type'    => 'messagebox',
				'id'      => 'momo_be_mb_generate_product_messagebox',
				'default' => 'none',
				'class'   => 'momo-mb-10',
			),
			array(
				'type'    => 'select',
				'label'   => esc_html__( 'Language', 'momoacgwc' ),
				'default' => $default_lang,
				'options' => $momoacgwc->lang->momo_get_all_langs(),
				'id'      => 'momo_be_mb_language',
			),
			array(
				'type'    => 'select',
				'label'   => esc_html__( 'Model', 'momoacgwc' ),
				'default' => $default_model,
				'options' => $momoacgwc->fn->momo_get_model_select_list(),
				'id'      => 'momo_be_mb_model',
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Temperature', 'momoacgwc' ),
				'placeholder' => esc_html__( '0.7', 'momoacgwc' ),
				'id'          => 'momo_be_mb_temperature',
				'woohelper'   => esc_html__( 'Higher temperature generates less accurate but diverse and creative output. Lesser temperature will generate more accurate results.', 'momoacgwc' ),
				'value'       => $temperature,
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Max Tokens', 'momoacgwc' ),
				'placeholder' => esc_html__( '2000', 'momoacgwc' ),
				'id'          => 'momo_be_mb_max_tokens',
				'woohelper'   => esc_html__( 'Use it in combination with "Temperature" to control the randomness and creativity of the output.', 'momoacgwc' ),
				'value'       => $max_tokens,
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Top P', 'momoacgwc' ),
				'placeholder' => esc_html__( '1.0', 'momoacgwc' ),
				'id'          => 'momo_be_mb_top_p',
				'woohelper'   => esc_html__( 'To control randomness of the output.', 'momoacgwc' ),
				'value'       => $top_p,
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Frequency Penalty', 'momoacgwc' ),
				'placeholder' => esc_html__( '0.0', 'momoacgwc' ),
				'id'          => 'momo_be_mb_frequency_penalty',
				'woohelper'   => esc_html__( 'For improving the quality and coherence of the generated text.', 'momoacgwc' ),
				'value'       => $frequency_penalty,
			),
			array(
				'type'        => 'text',
				'label'       => esc_html__( 'Presence Penalty', 'momoacgwc' ),
				'placeholder' => esc_html__( '0.0', 'momoacgwc' ),
				'id'          => 'momo_be_mb_presence_penalty',
				'woohelper'   => esc_html__( 'To produce more concise text.', 'momoacgwc' ),
				'value'       => $presence_penalty,
			),
			array(
				'type'    => 'switch',
				'label'   => esc_html__( 'Generate Image', 'momoacgwc' ),
				'default' => 'off',
				'id'      => 'momo_be_generate_image',
			),
			array(
				'type'    => 'switch',
				'label'   => esc_html__( 'Generate Gallery', 'momoacgwc' ),
				'default' => 'off',
				'id'      => 'momo_be_generate_gallery',
				'pro'     => true,
			),
			array(
				'type'   => 'side-buttons-bottom',
				'fields' => array(
					array(
						'type' => 'spinner',
					),
					array(
						'type'  => 'button',
						'id'    => 'momo-acg-wc-generate-product',
						'label' => esc_html__( 'Generate', 'momoacgwc' ),
						'class' => 'button button-primary button-large',
					),
				),
			),
		);
		$content = $momoacgwc->fn->momo_generate_metabox( $fields, 'momo-mb-side' );
		return $content;
	}
}
new MoMo_ACG_WC_Metabox();
