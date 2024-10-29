<?php
/**
 * Search Logger Background Job
 *
 * @package momoacgwc
 */
class Momo_ACGWC_Search_Background {
	/**
	 * Constructor to initialize the hooks
	 */
	public function __construct() {
		add_action( 'momo_store_email_template_event', array( $this, 'store_email_template_in_background' ), 10, 2 );

		register_deactivation_hook( __FILE__, array( $this, 'clear_all_scheduled_events' ) );
	}

	/**
	 * Schedule the background event for storing email templates
	 *
	 * @param int    $user_id User ID.
	 * @param string $search_term Search term.
	 */
	public function schedule_email_template_save( $user_id, $search_term ) {
		if ( ! wp_next_scheduled( 'momo_store_email_template_event', array( $user_id, $search_term ) ) ) {
			wp_schedule_single_event( time() + 10, 'momo_store_email_template_event', array( $user_id, $search_term ) );
		}
	}

	/**
	 * Function called by the cron job to store the email template in the background
	 *
	 * @param int    $user_id User ID.
	 * @param string $search_term Search term.
	 */
	public function store_email_template_in_background( $user_id, $search_term ) {
		global $momoacgwc;
		if ( ! empty( $user_id ) && ! empty( $search_term ) ) {
			$template = $momoacgwc->searchlogger->generate_email_template( $user_id, $search_term );
			$momoacgwc->searchlogtb->insert_log( $user_id, $search_term, current_time( 'mysql' ), $template );
		}
	}

	/**
	 * Clear the scheduled event, useful if plugin deactivates or similar.
	 *
	 * @param int    $user_id User ID.
	 * @param string $search_term Search term.
	 */
	public function clear_scheduled_event( $user_id, $search_term ) {
		$timestamp = wp_next_scheduled( 'momo_store_email_template_event', array( $user_id, $search_term ) );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'momo_store_email_template_event', array( $user_id, $search_term ) );
		}
	}
	/**
	 * Clear all scheduled events when the plugin is deactivated
	 */
	public function clear_all_scheduled_events() {
		// Loop through all user IDs and search terms (assuming you can retrieve them).
		global $wpdb;
		// Assuming you have a search log table where user_id and search_term are stored.
		$logs = $wpdb->get_results( "SELECT user_id, search_term FROM {$wpdb->prefix}momo_acgwc_search_logs" );

		// Iterate over each log entry and clear the scheduled event.
		if ( ! empty( $logs ) ) {
			foreach ( $logs as $log ) {
				$this->clear_scheduled_event( $log->user_id, $log->search_term );
			}
		}
	}
}
