<?php
/**
 * MoMo Themes Custom Post Type ACG
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v1.1.0
 */
class MoMo_CPT_Momoacg {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'momo_custom_post_type_momoacg' ), 15 );
	}
	/**
	 * Register Custom Post Type momoacg
	 */
	public function momo_custom_post_type_momoacg() {
		register_post_type(
			'momoacgwc',
			array(
				'labels'       => array(
					'name'          => esc_html__( 'ACG Draft Contents', 'momoacgwc' ),
					'singular_name' => esc_html__( 'ACG Draft Content', 'momoacgwc' ),
					'menu_name'     => esc_html__( 'Draft Content', 'momoacgwc' ),
				),
				'description'  => esc_html__( 'This is where you can view your saved draft contents.', 'momoacgwc' ),
				'public'       => true,
				'has_archive'  => true,
				'show_ui'      => true,
				'show_in_menu' => false,
				'capabilities' => array(
				),
			)
		);
	}
}
new MoMo_CPT_Momoacg();
