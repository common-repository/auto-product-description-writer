<?php
/**
 * Admin Init for Chatbot
 *
 * @package momoacgwc
 * @since v1.2.2
 */
class MoMo_ACGWC_Chatbot_Admin {
	/**
	 * Construct
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'momoacg_register_cs_settings' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'momoacg_chatbot_print_admin_ss' ) );

		add_action( 'admin_menu', array( $this, 'momo_add_submenu_of_chatbot' ) );
	}
	/**
	 * Register momoacg Settings
	 */
	public function momoacg_register_cs_settings() {
		register_setting( 'momoacgwc-settings-chatbot-group', 'momo_acgwc_chatbot_settings' );
	}
	/**
	 * Set Admin Menu
	 */
	public function momo_add_submenu_of_chatbot() {
		global $momoacgwc;
		add_submenu_page(
			'momoacgwc',
			esc_html__( 'MoMo Chatbot', 'momoacgwc' ),
			'Chatbot',
			'manage_options',
			'momoacgwc-chatbot',
			array( $this, 'momo_chatbot_add_admin_settings_page' )
		);
	}
	/**
	 * Settings Page
	 */
	public function momo_chatbot_add_admin_settings_page() {
		global $momoacgwc;
		include_once $momoacgwc->plugin_path . 'chatbot/admin/pages/momo-chatbot-settings.php';
	}
	/**
	 * Add tab li
	 */
	public function momo_acg_add_cb_tab_li() {
		?>
		<li><a class="momo-be-tablinks" href="#momo-be-chatbot"><i class='bx bx-credit-card-front'></i><span><?php esc_html_e( 'Chatbot', 'momoacgwc' ); ?></span></a></li>
		<?php
	}
	/**
	 * Add tab content
	 */
	public function momo_acg_add_cb_tab_content() {
		global $momoacgwc;
		?>
		<div id="momo-be-chatbot" class="momo-be-admin-content">
			<form method="post" action="options.php" id="momo-momoacg-chatbot-settings-form">
				<?php settings_fields( 'momoacgwc-settings-chatbot-group' ); ?>
				<?php do_settings_sections( 'momoacgwc-settings-chatbot-group' ); ?>
				<?php require_once $momoacgwc->plugin_path . 'chatbot/admin/pages/page-momo-acg-chatbot.php'; ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	/**
	 * Enqueue script and styles
	 */
	public function momoacg_chatbot_print_admin_ss() {
		global $momoacgwc;
		wp_enqueue_style( 'momoacg_chatbot_admin', $momoacgwc->plugin_url . 'chatbot/assets/momo_acg_chatbot.css', array(), $momoacgwc->version );
		wp_register_script( 'momoacg_chatbot_admin', $momoacgwc->plugin_url . 'chatbot/assets/momo_acg_chatbot.js', array( 'jquery' ), $momoacgwc->version, true );
		wp_enqueue_script( 'momoacg_chatbot_admin' );
		$ajaxurl = array(
			'ajaxurl'            => admin_url( 'admin-ajax.php' ),
			'momoacg_ajax_nonce' => wp_create_nonce( 'momoacg_security_key' ),
			'empty_input_fields' => esc_html__( 'Empty input fields or non-numeric value in numeric fields. All field(s) required. Price and Tokens field can only be numeric.', 'momoacgwc' ),
			'numeric_field'      => esc_html__( 'Price and Tokens field can only be numeric.', 'momoacgwc' ),
		);
		wp_localize_script( 'momoacg_chatbot_admin', 'momoacg_chatbot_admin', $ajaxurl );
	}
	/**
	 * Generate current list HTML
	 *
	 * @param string $type Post/Page/Products.
	 * @param string $contype Page or Ajax.
	 */
	public static function momo_cb_trainings_generate_current_list( $type, $contype = 'page' ) {
		global $momoacgwc;
		$trainings_list = get_option( 'momo_acg_cb_trainings_list' );

		$variable     = 'current_training_list_of_' . $type;
		$current_list = isset( $trainings_list[ $variable ] ) ? $trainings_list[ $variable ] : array();
		$current_list = $momoacgwc->tblfn->momo_get_table_option( 'momo_acg_cb_trainings_list', $type );

		ob_start();
		if ( ! empty( $current_list ) && is_array( $current_list ) ) {
			foreach ( $current_list as $id => $item ) {
				$date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
				$this_date_time   = date_i18n( $date_time_format, $item['changed'] );
				?>
				<tr>
					<td><?php echo esc_html( get_the_title( $item['id'] ) ); ?></td>
					<td><?php echo esc_html( $item['status'] ); ?></td>
					<td><?php echo esc_html( $this_date_time ); ?></td>
					<td class="momo-table-action" data-id="<?php echo esc_attr( $item['id'] ); ?>" data-type="<?php echo esc_attr( $type ); ?>">
						<span class="embed-remove" title="<?php esc_html_e( 'Remove', 'momoacgwc' ); ?>"><i class="bx bx-trash"></i></span>
						<span class="embed-reschedule" title="<?php esc_html_e( 'Repost', 'momoacgwc' ); ?>"><i class="bx bx-repost"></i></span>
						<?php if ( 'pending' === $item['status'] ) : ?>
						<span class="embed-process" title="<?php esc_html_e( 'Process', 'momoacgwc' ); ?>"><i class='bx bxs-right-arrow-square'></i></span>
						<?php endif; ?>
					</td>
				</tr>
				<?php
			}
		} else {
			?>
				<tr>
					<td colspan="4">
						<?php
						/* translators: %s: type index */
						printf( esc_html__( 'There are no %s added for trainings.', 'momoacgwc' ), esc_html( $type ) );
						?>
					</td>
				</tr>
			<?php
		}
		if ( 'page' === $contype ) {
			return ob_get_contents();
		} else {
			return ob_get_clean();
		}
	}
	/**
	 * Generate current list input HTML
	 *
	 * @param string $type Post/Page/Products.
	 * @param string $contype Page or Ajax.
	 */
	public static function momo_cb_trainings_generate_current_list_input( $type, $contype = 'page' ) {
		global $momoacgwc;
		$trainings_settings = get_option( 'momo_acgwc_cb_trainings_settings' );

		$variable     = 'current_training_list_of_' . $type;
		$current_list = isset( $trainings_settings[ $variable ] ) ? $trainings_settings[ $variable ] : array();
		$current_list = $momoacgwc->tblfn->momo_get_table_option( 'momo_acg_cb_trainings_list', $type );
		ob_start();
		?>
		<input type="hidden" name="momo_acg_cb_trainings_settings[<?php echo esc_attr( $variable ); ?>]" value="<?php echo ! empty( $current_list ) ? esc_attr( $current_list ) : ''; ?>"/>
		<?php
		if ( 'page' === $contype ) {
			return ob_get_contents();
		} else {
			return ob_get_clean();
		}
	}
}
new MoMo_ACGWC_Chatbot_Admin();
