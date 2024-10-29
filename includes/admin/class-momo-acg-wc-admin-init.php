<?php

/**
 * Admin Init
 *
 * @package momoacgwc
 * @author MoMo Themes
 * @since v1.0.0
 */
class MoMo_ACG_WC_Admin_Init {
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array($this, 'momo_acgwc_set_admin_menu') );
        add_action( 'admin_enqueue_scripts', array($this, 'momo_acgwc_print_admin_ss') );
        add_action( 'admin_init', array($this, 'momo_acgwc_register_settings') );
        add_action( 'momo_acgwc_api_shout', array($this, 'momo_acgwc_check_api_and_inform') );
    }

    /**
     * Check OpenAI API saved or inform
     *
     * @return void
     */
    public function momo_acgwc_check_api_and_inform() {
        $openai_settings = get_option( 'momo_acg_wc_openai_settings' );
        $api_key = ( isset( $openai_settings['api_key'] ) ? $openai_settings['api_key'] : '' );
        if ( empty( $api_key ) ) {
            ?>
			<div class="notice notice-error">
				<p><strong><?php 
            esc_html_e( 'Woo AI Notice:', 'momoacgwc' );
            ?></strong>
				<?php 
            printf( esc_html__( "It looks like you haven't saved your OpenAI API key yet. Please enter your API key in the settings page to enable AI-powered product descriptions. %1\$s ( Click here ) %2\$s", 'momoacgwc' ), '<a href="' . esc_url( admin_url( 'admin.php?page=momoacgwc-settings' ) ) . '">', '</a>' );
            ?>
				</p>
			</div>
			<?php 
        }
    }

    /**
     * Check and write product description
     *
     * @param integer    $post_id Post ID.
     * @param WC_Product $post Product.
     * @param bool       $update Update.
     */
    public function momo_check_and_write_product_description( $post_id, $post, $update ) {
        global $momoacgwc;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( !$update ) {
            return;
        }
        $description = get_post_field( 'post_content', $post_id );
        if ( empty( $description ) ) {
            $product_title = get_post_field( 'post_title', $post_id );
            if ( !empty( $product_title ) ) {
                $wc_product = wc_get_product( $post_id );
                $message[] = $new_msg;
                $openai_settings = get_option( 'momo_acg_wc_openai_settings' );
                $default_model = ( isset( $openai_settings['default_model'] ) ? $openai_settings['default_model'] : 'gpt-3.5-turbo' );
                $default_lang = ( isset( $openai_settings['default_lang'] ) ? $openai_settings['default_lang'] : 'english' );
                $new_msg = array(
                    'role'    => 'user',
                    'content' => esc_html__( 'Write a product description on ', 'momoacgwc' ) . $product_title . ' in ' . $default_lang . ' language',
                );
                $model = $default_model;
                $modeltype = $momoacgwc->fn->momo_get_model_type( $model );
                $temperature = ( isset( $chatbot_settings['temperature'] ) && !empty( $chatbot_settings['temperature'] ) ? $chatbot_settings['temperature'] : '0.7' );
                $max_tokens = ( isset( $chatbot_settings['max_tokens'] ) && !empty( $chatbot_settings['max_tokens'] ) ? $chatbot_settings['max_tokens'] : '660' );
                $body = array(
                    'model'    => $model,
                    'messages' => $message,
                );
                $url = 'https://api.openai.com/v1/chat/completions';
                $response = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body );
                $message = '';
                if ( isset( $response['status'] ) && 200 === $response['status'] ) {
                    $choices = ( isset( $response['body']->choices ) ? $response['body']->choices : array() );
                    foreach ( $choices as $choice ) {
                        if ( 'chat' === $modeltype ) {
                            $message .= $choice->message->content;
                        } else {
                            $message .= $choice->text;
                        }
                        $status = 'good';
                    }
                }
                if ( !empty( $message ) ) {
                    $updated_post = array(
                        'ID'           => $post_id,
                        'post_content' => $message,
                    );
                    wp_update_post( $updated_post );
                    $sentences = preg_split( '/(?<=[.?!])\\s+(?=[A-Z])/', $message );
                    $num_sentences = 3;
                    $extracted_sentences = array_slice( $sentences, 0, $num_sentences );
                    $extracted_message = implode( ' ', $extracted_sentences );
                    $formatted_promt = esc_html__( 'generate an image of a ', 'momoacgwc' ) . $product_title . esc_html__( ' as a product', 'momoacgwc' );
                    $body = array(
                        'prompt' => $formatted_promt,
                        'model'  => 'dall-e-2',
                    );
                    $url = 'https://api.openai.com/v1/images/generations';
                    $response = $momoacgwc->fn->momo_acg_wc_run_rest_api( 'POST', $url, $body );
                    if ( isset( $response['status'] ) && 200 === $response['status'] ) {
                        $image_url = ( isset( $response['body']->data[0]->url ) ? $response['body']->data[0]->url : '' );
                        if ( !empty( $image_url ) ) {
                            $image_id = media_sideload_image(
                                $image_url,
                                $post_id,
                                'Product Image',
                                'id'
                            );
                            if ( !is_wp_error( $image_id ) ) {
                                set_post_thumbnail( $post_id, $image_id );
                            }
                        }
                    }
                }
                $momoacgwc->fn->momoacgwc_generate_multiple_images( $post_id, $message );
            }
        }
    }

    /**
     * Register momoacg Settings
     */
    public function momo_acgwc_register_settings() {
        register_setting( 'momoacgwc-settings-openai-group', 'momo_acg_wc_openai_settings' );
        register_setting( 'momoacgwc-settings-edit-product-group', 'momo_acg_wc_edit_product_settings' );
        do_action( 'momo_acgwc_register_settings' );
    }

    /**
     * Set Admin Menu
     */
    public function momo_acgwc_set_admin_menu() {
        global $momoacgwc;
        add_menu_page(
            esc_html__( 'Woo AI', 'momoacgwc' ),
            esc_html__( 'Woo AI', 'momoacgwc' ),
            'manage_options',
            'momoacgwc',
            null,
            '',
            6
        );
        global $submenu;
        // First register the page.
        add_submenu_page(
            'momoacgwc',
            esc_html__( 'Woo AI - Settings', 'momoacgwc' ),
            esc_html__( 'Settings', 'momoacgwc' ),
            'manage_options',
            'momoacgwc-settings',
            array($this, 'momoacgwc_add_admin_settings_page')
        );
        // Now fix the path, since register_page() gets it wrong.
        if ( !isset( $submenu['momoacgwc'] ) ) {
            return;
        }
        foreach ( $submenu['momoacgwc'] as $index => &$item ) {
            // The "slug" (aka the path) is the third item in the array.
            if ( 'momoacgwc' === $item[2] ) {
                $item[2] = 'admin.php?page=' . $item[2] . '-settings';
                unset($submenu['momoacgwc'][$index]);
            }
        }
        do_action( 'momo_add_submenu_to_momoacgwc' );
    }

    /**
     * Settings Page
     */
    public function momoacgwc_add_admin_settings_page() {
        global $momoacgwc;
        include_once $momoacgwc->plugin_path . 'includes/admin/pages/momo-acg-wc-settings.php';
    }

    /**
     * Enqueue script and styles
     */
    public function momo_acgwc_print_admin_ss() {
        global $momoacgwc;
        wp_enqueue_style(
            'momoacgwc_admin_style',
            $momoacgwc->plugin_url . 'assets/css/momo_acgwc_admin.css',
            array(),
            $momoacgwc->version
        );
        wp_enqueue_style(
            'momoacgwc_boxicons',
            $momoacgwc->plugin_url . 'assets/boxicons/css/boxicons.min.css',
            array(),
            '2.1.2'
        );
        wp_enqueue_style(
            'momoacgwc_oepnai',
            $momoacgwc->plugin_url . 'assets/boxicons/css/openai.css',
            array(),
            $momoacgwc->version
        );
        wp_register_script(
            'momoacgwc_admin_script',
            $momoacgwc->plugin_url . 'assets/js/momo_acgwc_admin.js',
            array('jquery'),
            $momoacgwc->version,
            true
        );
        wp_register_script(
            'woo_product_admin_script',
            $momoacgwc->plugin_url . 'assets/js/woo_product_admin.js',
            array('jquery', 'postbox', 'wc-admin-product-meta-boxes'),
            $momoacgwc->version,
            true
        );
        wp_enqueue_script( 'momoacgwc_admin_script' );
        wp_enqueue_script( 'woo_product_admin_script' );
        $ajaxurl = array(
            'ajaxurl'              => admin_url( 'admin-ajax.php' ),
            'momoacgwc_ajax_nonce' => wp_create_nonce( 'momoacgwc_security_key' ),
            'generating_product'   => esc_html__( 'Generating Product..', 'momoacgwc' ),
        );
        $ajaxurl = apply_filters( 'momo_acgwc_add_data_to_admin_locale', $ajaxurl );
        wp_localize_script( 'momoacgwc_admin_script', 'momoacgwc_admin', $ajaxurl );
    }

}

new MoMo_ACG_WC_Admin_Init();