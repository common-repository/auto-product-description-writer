<?php
/**
 * Woo Product Write Unisntall function
 *
 * @package momoacgwc
 * @author MoMo Themes
 * @since v1.0.0
 */

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$option_name = 'momo_acg_wc_openai_settings';

delete_option( $option_name );
