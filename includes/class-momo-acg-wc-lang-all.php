<?php
/**
 * MoMo Themes Basic functions
 *
 * @package momoacgwc
 * @author MoMo Themes
 * @since v1.0.0
 */
class MoMo_ACG_WC_Lang_All {
	/**
	 * Language list
	 *
	 * @var array
	 */
	public $langs;
	/**
	 * Writing Style
	 *
	 * @var srray
	 */
	public $writing_style;
	/**
	 * Writing Style Pro
	 *
	 * @var srray
	 */
	public $writing_style_pro;
	/**
	 * Writing Text
	 *
	 * @var array
	 */
	public $writing_text;
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->langs = array(
			'english'          => esc_html__( 'English', 'momoacgwc' ),
			'dutch'            => esc_html__( 'Dutch', 'momoacgwc' ),
			'french'           => esc_html__( 'French', 'momoacgwc' ),
			'german'           => esc_html__( 'German', 'momoacgwc' ),
			'hindi'            => esc_html__( 'Hindi', 'momoacgwc' ),
			'indonesian'       => esc_html__( 'Indonesian', 'momoacgwc' ),
			'italian'          => esc_html__( 'Italian', 'momoacgwc' ),
			'japanese'         => esc_html__( 'Japanese', 'momoacgwc' ),
			'arabic'           => esc_html__( 'Arabic', 'momoacgwc' ),
			'chinese'          => esc_html__( 'Chinese', 'momoacgwc' ),
			'hongkong chinese' => esc_html__( 'Hongkong Chinese', 'momoacgwc' ),
			'korean'           => esc_html__( 'Korean', 'momoacgwc' ),
			'polish'           => esc_html__( 'Polish', 'momoacgwc' ),
			'portuguese'       => esc_html__( 'Portuguese', 'momoacgwc' ),
			'russian'          => esc_html__( 'Russian', 'momoacgwc' ),
			'spanish'          => esc_html__( 'Spanish', 'momoacgwc' ),
			'turkish'          => esc_html__( 'Turkish', 'momoacgwc' ),
			'ukranian'         => esc_html__( 'Ukranian', 'momoacgwc' ),
			'vietnamese'       => esc_html__( 'Vietnamese', 'momoacgwc' ),
			'bengali'          => esc_html__( 'Bengali', 'momoacgwc' ),
			'persian'          => esc_html__( 'Persian', 'momoacgwc' ),
			'malay'            => esc_html__( 'Malay', 'momoacgwc' ),
			'romanian'         => esc_html__( 'Romanian', 'momoacgwc' ),
		);

		$this->writing_style = array(
			'simple'      => esc_html__( 'Simple', 'momoacgwc' ),
			'informative' => esc_html__( 'Informative', 'momoacgwc' ),
			'descriptive' => esc_html__( 'Descriptive', 'momoacgwc' ),
		);
		$this->writing_style_pro = array(
			'concise'       => esc_html__( 'Concise', 'momoacgwc' ),
			'formal'        => esc_html__( 'Formal', 'momoacgwc' ),
			'ersuasive'     => esc_html__( 'Persuasive', 'momoacgwc' ),
			'informal'      => esc_html__( 'Informal', 'momoacgwc' ),
			'seo-optimized' => esc_html__( 'SEO-Optimized', 'momoacgwc' ),
			'helpful'       => esc_html__( 'Helpful', 'momoacgwc' ),
			'humorous'      => esc_html__( 'Humorous', 'momoacgwc' ),
		);
		$this->writing_text  = array();
		foreach ( $this->writing_style as $style => $desc ) {
			$this->writing_text[ $style ] = array(
				'introduction' => $style . ' introduction about',
				'article'      => $style . ' article about',
				'conclusion'   => $style . ' conclusion about',
				'heading'      => $style . ' heading(s) about',
			);
		}
	}
	/**
	 * Get all Langugae
	 */
	public function momo_get_all_langs() {
		return $this->langs;
	}
	/**
	 * Get all Writing styles.
	 */
	public function momo_get_all_writing_style() {
		return $this->writing_style;
	}
	/**
	 * Get all Writing styles.
	 */
	public function momo_get_all_writing_style_pro() {
		return $this->writing_style_pro;
	}
	/**
	 * Get all Writing Text
	 *
	 * @param string $style Style.
	 */
	public function momo_get_all_writing_text( $style = 'informative' ) {
		return $this->writing_text[ $style ];
	}
}
