<?php
/**
 * Search Logger Cron job
 *
 * @package momoacgwc
 */
class Momo_ACGWC_Search_Log_Cron {

	/**
	 * The table name where the logs are stored.
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * Constructor to initialize table name and hooks.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'momo_acgwc_search_logs';

		add_action( 'update_option_momo_acg_wc_searchlog_settings', array( $this, 'on_save_log_storage_duration' ), 10, 2 );

		add_action( 'momo_delete_old_search_logs_cron', array( $this, 'delete_old_logs_scheduled' ) );

		register_deactivation_hook( __FILE__, array( $this, 'clear_cron_on_deactivation' ) );
	}

	/**
	 * Function to delete logs older than the specified duration.
	 *
	 * @param int $days Number of days to keep the logs.
	 */
	public function delete_old_logs( $days ) {
		global $wpdb;
		$date_limit = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->table_name} WHERE search_date < %s",
				$date_limit
			)
		);
	}

	/**
	 * Hook into saving the log storage duration option.
	 *
	 * @param string $old_value The old value of the option.
	 * @param string $value The new value of the option.
	 */
	public function on_save_log_storage_duration( $old_value, $value ) {
		// Clear any existing cron jobs
		if ( wp_next_scheduled( 'momo_delete_old_search_logs_cron' ) ) {
			wp_clear_scheduled_hook( 'momo_delete_old_search_logs_cron' );
		}

		// Schedule a new cron job
		wp_schedule_event( time(), 'daily', 'momo_delete_old_search_logs_cron' );

		// Immediately delete logs older than the newly set duration
		$this->delete_old_logs( $value );
	}

	/**
	 * Function called by the cron job to delete old logs.
	 */
	public function delete_old_logs_scheduled() {
		$search_settings      = get_option( 'momo_acg_wc_searchlog_settings' );
		$log_retention_period = isset( $search_settings['log_retention_period'] ) ? $search_settings['log_retention_period'] : '';

		$days = 30;
		switch ( $log_retention_period ) {
			case '1m':
				$days = 30;
				break;
			case '3m':
				$days = 90;
				break;
			case '6m':
				$days = 180;
				break;
			case '1y':
				$days = 365;
				break;
			case 'forever':
				return;
			default:
				$days = 30;
				break;
		}

		$this->delete_old_logs( $days );
	}

	/**
	 * Clear the scheduled cron job on plugin deactivation.
	 */
	public function clear_cron_on_deactivation() {
		$timestamp = wp_next_scheduled( 'momo_delete_old_search_logs_cron' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'momo_delete_old_search_logs_cron' );
		}
	}
}
new Momo_ACGWC_Search_Log_Cron();
