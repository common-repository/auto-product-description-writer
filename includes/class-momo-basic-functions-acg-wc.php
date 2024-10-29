<?php

/**
 * MoMo Themes Basic functions
 *
 * @package momoacgwc
 * @author MoMo Themes
 * @since v1.0.0
 */
class MoMo_Basic_Functions_ACG_WC {
    /**
     * Retrieves a list of all available models.
     *
     * @return array A list of models where each model is an array containing 'name' and 'key'.
     */
    public function momo_list_all_models() {
        $models = array(
            'davinci-002'       => array(
                'name' => 'Davinci 002',
                'key'  => 'davinci-002',
                'type' => 'text',
            ),
            'babbage-002'       => array(
                'name' => 'Babbage 001',
                'key'  => 'babbage-001',
                'type' => 'text',
            ),
            'gpt-3.5-turbo'     => array(
                'name' => 'GPT-3.5 Turbo',
                'key'  => 'gpt-3.5-turbo',
                'type' => 'chat',
            ),
            'gpt-4o'            => array(
                'name' => 'GPT-4o',
                'key'  => 'gpt-4o',
                'type' => 'chat',
            ),
            'chatgpt-4o-latest' => array(
                'name' => 'ChatGPT 4o Latest',
                'key'  => 'chatgpt-4o-latest',
                'type' => 'chat',
            ),
        );
        return $models;
    }

    /**
     * Retrieves the type of a model based on its key.
     *
     * @param string $key The key of the model.
     * @return string The type of the model.
     */
    public function momo_get_model_type( $key ) {
        $models = $this->momo_list_all_models();
        return $models[$key]['type'];
    }

    /**
     * Retrieves a list of model names.
     *
     * @return array A list of model names where each key is the model key.
     */
    public function momo_get_model_select_list() {
        $models = $this->momo_list_all_models();
        $list = array();
        foreach ( $models as $key => $value ) {
            $list[$key] = $value['name'];
        }
        return $list;
    }

    /**
     * Check if string is json
     *
     * @param string $string Provided string.
     * @return boolean
     */
    public function momo_is_json( $string ) {
        json_decode( $string );
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Recursively sanitize post fields
     *
     * @param array $array Fields to sanitize.
     */
    public function momo_recursive_sanitize_post_fields( $array ) {
        if ( is_array( $array ) ) {
            $new_array = array();
            foreach ( $array as $key => $value ) {
                if ( is_array( $value ) ) {
                    $key = sanitize_title( $key );
                    $new_array[$key] = $this->momo_recursive_sanitize_array_fields( $value );
                } else {
                    $new_array[$key] = sanitize_text_field( $value );
                }
            }
            return $new_array;
        } else {
            return sanitize_text_field( $array );
        }
    }

    /**
     * Run plugin rest API function
     *
     * @param string $method Method.
     * @param string $url Remaining url.
     * @param array  $body Body arguments.
     * @param string $transient Transient Key.
     * @param string $cache_disabled Transied disabled on / off.
     */
    public function momo_acg_wc_run_rest_api(
        $method,
        $url,
        $body,
        $transient = '',
        $cache_disabled = 'off'
    ) {
        global $momoacgwc;
        $cached_disabled = 'on';
        $openai_settings = get_option( 'momo_acg_wc_openai_settings' );
        $api_key = ( isset( $openai_settings['api_key'] ) ? $openai_settings['api_key'] : '' );
        if ( empty( $api_key ) ) {
            $response = array(
                'status'  => 'bad',
                'message' => esc_html__( 'Empty API key, please store OpenAI API key in MoMo ACG settings first.', 'momoacgwc' ),
            );
            return $response;
        }
        $timeout = 45;
        $args = array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ),
            'method'  => $method,
            'timeout' => $timeout,
        );
        if ( !empty( $body ) ) {
            $args['body'] = wp_json_encode( $body );
        }
        if ( 'on' === $cache_disabled ) {
            $response = ( 'POST' === $method ? wp_remote_post( $url, $args ) : wp_remote_request( $url, $args ) );
            if ( !empty( $transient ) ) {
                set_transient( $transient, $response, HOUR_IN_SECONDS );
            }
        } else {
            $cached_transient = false;
            if ( !empty( $transient ) ) {
                $cached_transient = get_transient( $transient );
            }
            if ( false === $cached_transient ) {
                $response = ( 'POST' === $method ? wp_remote_post( $url, $args ) : wp_remote_request( $url, $args ) );
                set_transient( $transient, $response, HOUR_IN_SECONDS );
            } else {
                $response = $cached_transient;
            }
        }
        $json = wp_remote_retrieve_body( $response );
        $details = json_decode( $json );
        if ( !is_wp_error( $response ) && isset( $response['response'] ) ) {
            $response = array(
                'status'  => $response['response']['code'],
                'message' => $response['response']['message'],
                'code'    => $response['response']['code'],
                'body'    => json_decode( $response['body'] ),
            );
            return $response;
        } else {
            $response = array(
                'status'  => 405,
                'message' => esc_html__( 'WP_Error', 'momoacgwc' ),
                'code'    => 405,
                'body'    => '',
            );
        }
        return $response;
    }

    /**
     * Returns check option check or unchecked
     *
     * @param array  $settings Settings array.
     * @param string $key Option key.
     */
    public function momo_return_check_option( $settings, $key ) {
        $option = ( isset( $settings[$key] ) ? $settings[$key] : 'off' );
        if ( 'on' === $option ) {
            $check = 'checked="checked"';
        } else {
            $check = '';
        }
        return $check;
    }

    /**
     * Returns check option check or unchecked
     *
     * @param array  $settings Settings array.
     * @param string $key Option key.
     * @param string $value Main value to check.
     */
    public function momo_return_checkbox_option( $settings, $key, $value ) {
        $option_arr = ( isset( $settings[$key] ) ? $settings[$key] : array() );
        $check = '';
        if ( in_array( $value, $option_arr, true ) ) {
            $check = 'checked="checked"';
        } else {
            $check = '';
        }
        return $check;
    }

    /**
     * Returns check option check or unchecked
     *
     * @param array  $settings Settings array.
     * @param string $key Option key.
     */
    public function momo_return_option_yesno( $settings, $key ) {
        $option = ( isset( $settings[$key] ) ? $settings[$key] : 'off' );
        return $option;
    }

    /**
     * Check API Cache enabled or disabled.
     *
     * @return boolean
     */
    public function momoacg_disable_cache_is_enabled() {
        $cache_settings = get_option( 'momo_wsw_api_cache_settings' );
        $disable_api_cache = ( isset( $cache_settings['disable_api_cache'] ) ? $cache_settings['disable_api_cache'] : 'off' );
        if ( 'on' === $disable_api_cache ) {
            return true;
        }
        return false;
    }

    /**
     * Create metabox content.
     *
     * @param array  $fields Fileds.
     * @param string $context Area.
     */
    public function momo_generate_metabox( $fields, $context = 'momo-mb-normal' ) {
        if ( !is_array( $fields ) ) {
            return;
        }
        ob_start();
        ?>
		<div class="momo-be-mb-form <?php 
        echo esc_attr( $context );
        ?>">
			<div class="momo-be-section-block">
		<?php 
        foreach ( $fields as $field ) {
            $this->momo_switch_and_generate_field( $field );
        }
        ?>
			</div><!-- ends momo-be-mb-form -->
		</div><!-- ends momo-be-section-block -->
		<?php 
        return ob_get_contents();
    }

    /**
     * Check and generate field.
     *
     * @param array $field Field.
     * @return void
     */
    public function momo_switch_and_generate_field( $field ) {
        if ( isset( $field['plan'] ) && !empty( $field['plan'] ) ) {
            $plan = $field['plan'];
            if ( !momoacgwc_fs()->is_plan( $plan ) ) {
                return;
            }
        }
        switch ( $field['type'] ) {
            case 'select':
                $this->momo_generate_select( $field );
                break;
            case 'switch':
                $this->momo_generate_switch( $field );
                break;
            case 'text':
                $this->momo_generate_text( $field );
                break;
            case 'textarea':
                $this->momo_generate_textarea( $field );
                break;
            case 'button_block':
                $this->momo_generate_button_block( $field );
                break;
            case 'button':
                $this->momo_generate_button( $field );
                break;
            case 'messagebox':
                $this->momo_generate_messagebox( $field );
                break;
            case 'working':
                $this->momo_generate_working( $field );
                break;
            case 'custom':
                $this->momo_generate_custom_field( $field );
                break;
            case 'section':
                $this->momo_generate_section_block( $field );
                break;
            case 'three-columns':
                $this->momo_generate_three_column_section( $field );
                break;
            case 'inline-after':
                $this->momo_generate_inline_after( $field );
                break;
            case 'afteryes':
                $this->momo_generate_seperate_afteryes( $field );
                break;
            case 'side-buttons-bottom':
                $this->momo_generate_side_buttons_bottom( $field );
                break;
            case 'spinner':
                $this->momo_generate_spinner( $field );
                break;
            default:
                break;
        }
    }

    /**
     * Generate Spinner
     *
     * @param array $field Fields.
     */
    public function momo_generate_spinner( $field ) {
        $id = ( isset( $field['id'] ) ? $field['id'] : '' );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        ?>
		<span class="momo-be-side-spinner spinner <?php 
        echo esc_attr( $class );
        ?>" id="<?php 
        echo esc_attr( $id );
        ?>">
		</span>
		<?php 
    }

    /**
     * Generate Seperate afteryes
     *
     * @param array $field Fields.
     */
    public function momo_generate_seperate_afteryes( $field ) {
        $fields = ( isset( $field['fields'] ) ? $field['fields'] : array() );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        $afteryes = $field['id'] . '_afteryes';
        if ( !empty( $afteryes ) ) {
            ?>
			<div class="momo-be-tc-yes-container" id="<?php 
            echo esc_attr( $afteryes );
            ?>">
				<?php 
            $this->momo_generate_metabox( $fields );
            ?>
			</div>
			<?php 
        }
    }

    /**
     * Create 3 column section
     *
     * @param array $field Fields.
     */
    public function momo_generate_three_column_section( $field ) {
        $fields = ( isset( $field['fields'] ) ? $field['fields'] : array() );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        ?>
		<div class="momo-flex-columns <?php 
        echo esc_attr( $class );
        ?>">
			<?php 
        foreach ( $fields as $field ) {
            ?>
				<div class="momo-three-column">
				<?php 
            $this->momo_switch_and_generate_field( $field );
            ?>
				</div>
				<?php 
        }
        ?>
		</div>
		<?php 
    }

    /**
     * Generate before stuff
     *
     * @param array $fields Fields.
     */
    public function momo_generate_before_inline( $fields ) {
        foreach ( $fields as $field ) {
            ?>
			<div class="momo-be-inline-before">
			<?php 
            $this->momo_switch_and_generate_field( $field );
            ?>
			</div>
			<?php 
        }
    }

    /**
     * Generate after stuff
     *
     * @param array $fields Fields.
     */
    public function momo_generate_after_inline( $fields ) {
        foreach ( $fields as $field ) {
            ?>
			<div class="momo-be-inline-after">
			<?php 
            $this->momo_switch_and_generate_field( $field );
            ?>
			</div>
			<?php 
        }
    }

    /**
     * Generate Select
     *
     * @param array $field Filed.
     */
    public function momo_generate_select( $field ) {
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        ?>
		<div class="momo-be-block <?php 
        echo esc_attr( $class );
        ?>">
			<label class="regular inline"><?php 
        echo esc_html( $field['label'] );
        ?></label>
			<select class="inline" name="<?php 
        echo esc_attr( $field['id'] );
        ?>">
			<?php 
        foreach ( $field['options'] as $value => $option ) {
            ?>
				<option value="<?php 
            echo esc_attr( $value );
            ?>" 
				<?php 
            echo esc_attr( ( $field['default'] === $value ? 'selected="selected"' : '' ) );
            ?>
				><?php 
            echo esc_html( $option );
            ?></option>
			<?php 
        }
        ?>
			</select>
			<?php 
        if ( isset( $field['after'] ) && is_array( $field['after'] ) ) {
            $this->momo_generate_after_inline( $field['after'] );
        }
        ?>
		</div>
		<?php 
    }

    /**
     * Generate Switch
     *
     * @param array $field Filed.
     */
    public function momo_generate_switch( $field ) {
        $afteryes = '';
        $value = ( isset( $field['value'] ) ? $field['value'] : 'off' );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        $pro = ( isset( $field['pro'] ) ? $field['pro'] : false );
        if ( 'on' === $value ) {
            $check = 'checked="checked"';
        } else {
            $check = '';
        }
        $afteryes_fields = array();
        if ( isset( $field['afteryes'] ) ) {
            $afteryes = $field['id'] . '_afteryes';
            $afteryes_fields = $field['afteryes'];
            if ( empty( $afteryes_fields ) ) {
                $afteryes_fields = array();
            }
        }
        $is_premium = momoacgwc_fs()->is_premium();
        $id = $field['id'];
        $disabled = '';
        if ( !$is_premium && $pro ) {
            $disabled = 'disabled="disabled"';
        }
        ?>
		<div class="momo-be-switch-block <?php 
        echo esc_attr( $class );
        ?>">
			<div class="momo-be-block">
				<span class="momo-be-toggle-container" <?php 
        echo esc_attr( ( !empty( $afteryes ) ? 'momo-be-tc-yes-container=' . $afteryes : '' ) );
        ?>>
					<label class="switch">
						<input type="checkbox" class="switch-input" name="<?php 
        echo esc_attr( $id );
        ?>" autocomplete="off" <?php 
        echo esc_attr( $check );
        ?> <?php 
        echo esc_attr( $disabled );
        ?> >
						<span class="switch-label" data-on="Yes" data-off="No"></span>
						<span class="switch-handle"></span>
					</label>
				</span>
				<span class="momo-be-toggle-container-label">
					<?php 
        echo esc_html( $field['label'] );
        ?>
					<?php 
        if ( !$is_premium && $pro ) {
            ?>
						<span class="momo-pro-label"><?php 
            esc_html_e( 'PRO', 'momoacg' );
            ?></span>
					<?php 
        }
        ?>
				</span>
				<?php 
        if ( !empty( $afteryes_fields ) ) {
            ?>
				<div class="momo-be-tc-yes-container" id="<?php 
            echo esc_attr( $afteryes );
            ?>">
					<?php 
            $this->momo_generate_metabox( $afteryes_fields );
            ?>
				</div>
				<?php 
        }
        ?>
			</div>
		</div>
		<?php 
    }

    /**
     * Generate Text
     *
     * @param array $field Filed.
     */
    public function momo_generate_text( $field ) {
        $woohelper = ( isset( $field['woohelper'] ) && !empty( $field['woohelper'] ) ? $field['woohelper'] : false );
        ?>
		<div class="momo-be-block">
			<label class="regular inline <?php 
        echo esc_attr( ( $woohelper ? 'momo-no-min-width' : '' ) );
        ?>"><?php 
        echo esc_html( $field['label'] );
        ?></label>
			<?php 
        if ( $woohelper ) {
            echo wp_kses_post( wc_help_tip( $woohelper ) );
        }
        ?>
			<input type="text" class="inline wide" name="<?php 
        echo esc_attr( $field['id'] );
        ?>" placeholder="<?php 
        echo esc_attr( ( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ) );
        ?>"
			value="<?php 
        echo esc_attr( ( isset( $field['value'] ) ? $field['value'] : '' ) );
        ?>"
			/>
		</div>
		<?php 
    }

    /**
     * Generate Text
     *
     * @param array $field Filed.
     */
    public function momo_generate_text_output( $field ) {
        ob_start();
        $this->momo_generate_text( $field );
        return ob_get_clean();
    }

    /**
     * Generate Select
     *
     * @param array $field Filed.
     */
    public function momo_generate_select_output( $field ) {
        ob_start();
        $this->momo_generate_select( $field );
        return ob_get_clean();
    }

    /**
     * Generate Textarea
     *
     * @param array $field Filed.
     */
    public function momo_generate_textarea( $field ) {
        $content = ( isset( $field['value'] ) ? $field['value'] : '' );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        $attr = ( isset( $field['attr'] ) ? $field['attr'] : array() );
        ?>
		<div class="momo-be-section <?php 
        echo esc_attr( $class );
        ?>">
			<h2><?php 
        echo esc_html( $field['label'] );
        ?></h2>
			<div class="momo-be-section">
				<div class="momo-be-block">
					<textarea class="full-width" rows="<?php 
        echo esc_attr( $field['rows'] );
        ?>" autocomplete="off" id="<?php 
        echo esc_attr( $field['id'] );
        ?>"
					<?php 
        echo esc_attr( $this->momo_generate_attr( $attr ) );
        ?>
					><?php 
        echo wp_kses_post( $content );
        ?></textarea>
				</div>
			</div>
		</div>
		<?php 
    }

    /**
     * Generate Custom Field
     *
     * @param array $field Filed.
     */
    public function momo_generate_custom_field( $field ) {
        $content = ( isset( $field['value'] ) ? $field['value'] : '' );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        $label = ( isset( $field['label'] ) ? $field['label'] : '' );
        ?>
		<div class="momo-be-section <?php 
        echo esc_attr( $class );
        ?>">
			<?php 
        if ( !empty( $label ) ) {
            ?>
			<h2><?php 
            echo esc_html( $label );
            ?></h2>
			<?php 
        }
        ?>
			<div class="momo-be-section momo-be-section-container">
				<?php 
        echo wp_kses_post( $content );
        ?>
			</div>
		</div>
		<?php 
    }

    /**
     * Generate Button Block
     *
     * @param array $field Filed.
     */
    public function momo_generate_button_block( $field ) {
        $buttons = ( isset( $field['buttons'] ) ? $field['buttons'] : array() );
        ?>
		<div class="momo-be-buttons-block">
			<?php 
        foreach ( $buttons as $button ) {
            $this->momo_generate_button( $button );
        }
        ?>
		</div>
		<?php 
    }

    /**
     * Generate Side Bottoms Block
     *
     * @param array $field Filed.
     */
    public function momo_generate_side_buttons_bottom( $field ) {
        $fields = ( isset( $field['fields'] ) ? $field['fields'] : array() );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        $id = ( isset( $field['id'] ) ? $field['id'] : '' );
        ?>
		<div class="momo-be-side-bottom <?php 
        echo esc_attr( $class );
        ?>" id=<?php 
        echo esc_attr( $id );
        ?>>
			<div class="momo-right-button">
			<?php 
        foreach ( $fields as $field ) {
            $this->momo_switch_and_generate_field( $field );
        }
        ?>
			</div>
			<div class="clear"></div>
		</div>
		<?php 
    }

    /**
     * Generate Section Block
     *
     * @param array $field Filed.
     */
    public function momo_generate_section_block( $field ) {
        $fields = ( isset( $field['fields'] ) ? $field['fields'] : array() );
        $label = ( isset( $field['label'] ) ? $field['label'] : array() );
        $sublabel = ( isset( $field['sublabel'] ) ? $field['sublabel'] : array() );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        $id = ( isset( $field['id'] ) ? $field['id'] : array() );
        ?>
		<div class="momo-be-buttons-block <?php 
        echo esc_attr( $class );
        ?>" id=<?php 
        echo esc_attr( $id );
        ?>>
			<?php 
        if ( !empty( $label ) ) {
            ?>
			<h2 class="momo-section-block"><?php 
            echo esc_html( $label );
            ?></h2>
			<?php 
        }
        ?>
			<?php 
        if ( !empty( $sublabel ) ) {
            ?>
			<h3 class="momo-section-block"><?php 
            echo esc_html( $sublabel );
            ?></h3>
			<?php 
        }
        ?>
			<?php 
        foreach ( $fields as $field ) {
            $this->momo_switch_and_generate_field( $field );
        }
        ?>
		</div>
		<?php 
    }

    /**
     * Generate Button
     *
     * @param array $field Filed.
     */
    public function momo_generate_button( $field ) {
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        if ( isset( $field['before'] ) && is_array( $field['before'] ) ) {
            $this->momo_generate_before_inline( $field['before'] );
        }
        ?>
		<span class="momo-be-btn <?php 
        echo esc_attr( $class );
        ?>" id="<?php 
        echo esc_attr( $field['id'] );
        ?>">
		<?php 
        echo esc_html( $field['label'] );
        ?>
		</span>
		<?php 
    }

    /**
     * Generate Messagebox
     *
     * @param array $field Filed.
     */
    public function momo_generate_messagebox( $field ) {
        ?>
		<div class="momo-be-msg-block <?php 
        echo esc_attr( ( isset( $field['class'] ) ? $field['class'] : '' ) );
        ?>" id="<?php 
        echo esc_attr( $field['id'] );
        ?>"
		></div>
		<?php 
    }

    /**
     * Generate Working
     *
     * @param array $field Filed.
     */
    public function momo_generate_working( $field ) {
        if ( isset( $field['class'] ) && 'notontop' === $field['class'] ) {
            ?>
			<div class="momo-be-working-parent-holder">
			<?php 
        }
        ?>
		<div class="momo-be-working" id="<?php 
        echo esc_attr( $field['id'] );
        ?>"
		<?php 
        echo esc_attr( ( isset( $field['class'] ) ? 'class="' . $field['class'] . '"' : '' ) );
        ?>
		></div>
		<?php 
        if ( isset( $field['class'] ) && 'notontop' === $field['class'] ) {
            ?>
			</div>
			<?php 
        }
    }

    /**
     * Generate attribute data
     *
     * @param array $attr Attributes.
     */
    public function momo_generate_attr( $attr ) {
        $output = '';
        if ( !empty( $attr ) ) {
            foreach ( $attr as $id => $value ) {
                $output .= $id . '=' . $value . ' ';
            }
        }
        return $output;
    }

    /**
     * Custom string replace for first occurance
     *
     * @param string $search_str Search String.
     * @param string $replacement_str Replacement String.
     * @param string $src_str Source String.
     */
    public function momo_replace_first_str( $search_str, $replacement_str, $src_str ) {
        $pos = strpos( $src_str, $search_str );
        return ( false !== $pos ? substr_replace(
            $src_str,
            $replacement_str,
            $pos,
            strlen( $search_str )
        ) : $src_str );
    }

    /**
     * Generate Section Block
     *
     * @param array $field Filed.
     */
    public function momo_generate_inline_after( $field ) {
        $fields = ( isset( $field['fields'] ) ? $field['fields'] : array() );
        $label = ( isset( $field['label'] ) ? $field['label'] : array() );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        $id = ( isset( $field['id'] ) ? $field['id'] : array() );
        $rows = ( isset( $field['rows'] ) ? $field['rows'] : 1 );
        ?>
		<div class="momo-be-inline-table <?php 
        echo esc_attr( $class );
        ?>" id=<?php 
        echo esc_attr( $id );
        ?>>
			<?php 
        if ( !empty( $label ) ) {
            ?>
			<h2><?php 
            echo esc_html( $label );
            ?></h2>
			<?php 
        }
        ?>
			<div class="momo-be-inline-row">
			<?php 
        foreach ( $fields as $field ) {
            $this->momo_switch_and_generate_field( $field );
        }
        ?>
			</div>
		</div>
		<?php 
    }

    /**
     * Generate Popbox
     *
     * @param array $field Fields.
     */
    public function momo_generate_popbox( $field ) {
        $id = ( isset( $field['id'] ) ? $field['id'] : '' );
        $class = ( isset( $field['class'] ) ? $field['class'] : '' );
        ?>
		<div class="momo-popbox <?php 
        echo esc_attr( $class );
        ?>" id="<?php 
        echo esc_attr( $id );
        ?>">
			<div class="momo-pb-container">
				<div class="momo-pb-header">
					<span class="header-text"></span>
					<i class='momo-pb-close bx bxs-x-circle'></i>
				</div>
				<div class="momo-pb-content">
					<div class="momo-be-working"></div>
					<div class="content-html"></div>
				</div>
				<div class="momo-pb-footer">
					<div class="momo-pb-message"></div>
				</div>
			</div>
		</div>
		<div class="momo-pb-overlay" data-target="<?php 
        echo esc_attr( $id );
        ?>"></div>
		<?php 
    }

    /**
     * Generate multiple images
     *
     * @param integer $post_id Post ID.
     * @param string  $message Post Content.
     */
    public function momoacgwc_generate_multiple_images( $post_id, $message ) {
        $edit_settings = get_option( 'momo_acg_wc_edit_product_settings' );
        $enable_auto_multiple_images = $this->momo_return_option_yesno( $edit_settings, 'enable_auto_multiple_images' );
        $image_ids = array();
        return $image_ids;
    }

    /**
     * Generate image by content
     *
     * @param string $content Content.
     * @param int    $post_id Post ID.
     */
    public function momoacgwc_generate_image_by_content( $content, $post_id ) {
        $no_of_images = 4;
        $image_ids = array();
        $formatted_promt = esc_html__( 'generate an image from given content as a product of woocommerce : ', 'momoacgwc' ) . $content;
        $body = array(
            'prompt' => $formatted_promt,
            'model'  => 'dall-e-2',
            'n'      => $no_of_images,
        );
        $url = 'https://api.openai.com/v1/images/generations';
        $response = $this->momo_acg_wc_run_rest_api( 'POST', $url, $body );
        if ( isset( $response['status'] ) && 200 === $response['status'] ) {
            if ( isset( $response['body']->data ) && is_array( $response['body']->data ) ) {
                foreach ( $response['body']->data as $image ) {
                    $image_url = $image->url;
                    if ( !empty( $image_url ) ) {
                        $image_ids[] = media_sideload_image(
                            $image_url,
                            $post_id,
                            'Product Image',
                            'id'
                        );
                    }
                }
            }
        }
        return $image_ids;
    }

    /**
     * Check is premium version
     */
    public function momoacgwc_is_premium() {
        return momoacgwc_fs()->is_premium();
    }

    /**
     * Recursive sanitation for an array
     *
     * @param array $array Post array data.
     */
    public function momo_recursive_sanitize_text_field( $array ) {
        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = $this->momo_recursive_sanitize_text_field( $value );
            } else {
                $value = sanitize_text_field( $value );
            }
        }
        return $array;
    }

    /**
     * Remote Post file
     *
     * @param string $url URL.
     * @param array  $body Body.
     * @param string $file_path Filepath.
     */
    public function momo_acg_remote_post_file( $url, $body, $file_path ) {
        global $momowsw;
        $openai_settings = get_option( 'momo_acg_wc_openai_settings' );
        $api_key = ( isset( $openai_settings['api_key'] ) ? $openai_settings['api_key'] : '' );
        if ( empty( $api_key ) ) {
            $response = array(
                'status'  => 'bad',
                'message' => esc_html__( 'Empty API key, please store OpenAI API key in MoMo ACG settings first.', 'momowsw' ),
            );
            return $response;
        }
        // $logger = new MoMo_ACG_Logger( 'sync_rest' );
        $timeout = 45;
        $timeout = ( isset( $openai_settings['timeout'] ) ? $openai_settings['timeout'] : 45 );
        $boundary = md5( time() );
        $file_content = file_get_contents( $file_path );
        $file_name = basename( $file_path );
        $file_type = mime_content_type( $file_path );
        $request_body = "--{$boundary}\r\n" . "Content-Disposition: form-data; name=\"purpose\"\r\n\r\n" . "fine-tune\r\n" . "--{$boundary}\r\n" . "Content-Disposition: form-data; name=\"file\"; filename=\"{$file_name}\"\r\n" . "Content-Type: {$file_type}\r\n\r\n" . $file_content . "\r\n" . "--{$boundary}--\r\n";
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
            ),
            'timeout' => $timeout,
            'body'    => $request_body,
        );
        $response = wp_remote_post( $url, $args );
        if ( !is_wp_error( $response ) && isset( $response['response'] ) ) {
            if ( isset( $response['body']->error->message ) ) {
                $details = esc_html__( 'File exporting', 'momoacg' );
                $log = array(
                    'status'  => 'bad',
                    'message' => $response['body']->error->message,
                    'detail'  => $details,
                    'code'    => $response['response']['code'],
                );
            } else {
                $details = esc_html__( 'File exporting', 'momoacg' );
                $log = array(
                    'status'  => 'good',
                    'message' => esc_html__( 'File exported successfully.', 'momoacg' ),
                    'detail'  => $details,
                    'code'    => $response['response']['code'],
                );
            }
            // $logger->set_event( $log );
            $response = array(
                'status'  => $response['response']['code'],
                'message' => $response['response']['message'],
                'code'    => $response['response']['code'],
                'body'    => json_decode( $response['body'] ),
            );
            return $response;
        } else {
            $log = array(
                'status'  => 'bad',
                'message' => $response->get_error_message(),
                'detail'  => implode( ' ', array_slice( str_word_count( $body['prompt'], 2 ), 0, 5 ) ) . ' ....',
                'code'    => $response->get_error_code(),
            );
            // $logger->set_event( $log );
            return $response;
        }
    }

}
