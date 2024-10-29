<?php
/**
 * Table functions for Search Log
 *
 * @package momoacgwc
 * @author MoMo Themes
 * @since v1.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Main Class
 */
class Momo_ACGWC_Search_Table extends WP_List_Table {
	/**
	 * Table Name
	 *
	 * @var string
	 */
	private $table_name;
	/**
	 * Table Name
	 *
	 * @var string
	 */
	private $sent_tbl;
	/**
	 * Sent email count record
	 *
	 * @var integer
	 */
	private $rendered_send_email_count = 0;

	/**
	 * Initializes the Momo_ACGWC_Search_Table class.
	 *
	 * Sets the table name and hooks into the activation and deactivation lifecycle.
	 *
	 * @global wpdb $wpdb WordPress database object.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'momo_wcgwc_search_log', // Singular name for the table.
				'plural'   => 'momo_wcgwc_search_logs', // Plural name for the table.
				'ajax'     => false, // Does this table support AJAX?
			)
		);
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'momo_acgwc_search_logs';
		$this->sent_tbl   = $wpdb->prefix . 'momo_acgwc_email_sent_logs';

		// Hook into the activation and deactivation lifecycle.
		add_action( 'momo_acgwc_activate', array( $this, 'create_table' ) );
		add_action( 'momo_acgwc_deactivate', array( $this, 'drop_table' ) );

		add_action( 'admin_notices', array( $this, 'check_table_exists_message' ) );

		add_action( 'admin_init', array( $this, 'momo_acgwc_handle_searchlog_upgrade_db' ) );
	}
	/**
	 * Upgrade data base button
	 *
	 * @return void
	 */
	public function momo_acgwc_handle_searchlog_upgrade_db() {
		if ( isset( $_POST['momo_searchlog_upgrade_db'] ) ) {

			if ( ! isset( $_POST['momo_upgrade_searchlog_db_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['momo_upgrade_searchlog_db_nonce'] ) ), 'momo_upgrade_searchlog_db_action' ) ) {
				wp_die( esc_html__( 'Nonce verification failed. Please try again.', 'momoacgwc' ) );
			}

			$this->momo_acgwc_upgrade_database_searchlog();
			add_action( 'admin_notices', array( $this, 'momo_acgwc_searchlog_upgrade_success_notice' ) );
		}
	}
	/**
	 * Table added message
	 *
	 * @return void
	 */
	public function momo_acgwc_searchlog_upgrade_success_notice() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?php esc_html_e( 'WooAI', 'momoacgwc' ); ?> : </strong><?php esc_html_e( 'Database upgraded successfully!', 'momoacgwc' ); ?></p>
		</div>
		<?php
	}
	/**
	 * Upgrade database
	 *
	 * @return void
	 */
	public function momo_acgwc_upgrade_database_searchlog() {
		$this->create_table();
	}
	/**
	 * Check if the search log table exists and show a notice if it doesn't
	 */
	public function check_table_exists_message() {
		// Check if the table exists.
		$table_name   = $this->table_name;
		$table_exists = $this->check_table_exist( $table_name );

		$sent_tbl        = $this->sent_tbl;
		$sent_tbl_exists = $this->check_table_exist( $sent_tbl );
		if ( ! $table_exists || ! $sent_tbl_exists ) {
			ob_start();
			?>
			<div class="notice notice-error" style="padding: 12px;line-height:2.2;">
			<strong><?php esc_html_e( 'WooAI', 'momoacgwc' ); ?> : </strong>
			<?php echo esc_html__( 'The search log table is missing. Please reactivate the plugin to resolve this issue or ', 'momoacgwc' ); ?>
			<form method="post" action="" style="display:inline-block;margin-left: 12px">
				<?php wp_nonce_field( 'momo_upgrade_searchlog_db_action', 'momo_upgrade_searchlog_db_nonce' ); ?>
				<input type="submit" name="momo_searchlog_upgrade_db" id="momo_searchlog_upgrade_db" class="button button-primary" value="<?php esc_html_e( 'Upgrade Database', 'momoacgwc' ); ?>" style="display: inline-block;" />
			</form>
			</div>
			<?php
			return ob_get_contents();
		}
	}
	/**
	 * Create the search log table on plugin activation
	 */
	public function create_table() {
		global $wpdb;
		$table_name   = $this->table_name;
		$table_exists = $this->check_table_exist( $table_name );
		if ( ! $table_exists ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				user_id bigint(20) NOT NULL,
				search_term text NOT NULL,
				search_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				email_template longtext NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
		$table_name   = $this->sent_tbl;
		$table_exists = $this->check_table_exist( $table_name );
		if ( ! $table_exists ) {
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				log_id bigint(20) NOT NULL,
				email_sent_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	/**
	 * Drop the search log table on plugin deactivation (optional)
	 */
	public function drop_table() {
		global $wpdb;

		$sql = "DROP TABLE IF EXISTS {$this->table_name};";
		$wpdb->query( $sql );

		$sql = "DROP TABLE IF EXISTS {$this->sent_tbl};";
		$wpdb->query( $sql );
	}

	/**
	 * Insert a new search log into the table
	 *
	 * @param int    $user_id User ID.
	 * @param string $search_term Search term.
	 * @param string $time Time.
	 * @param string $email_template Email Template.
	 */
	public function insert_log( $user_id, $search_term, $time, $email_template ) {
		global $wpdb;

		wp_cache_delete( 'momo_acgwc_search_logs_all' );
		$wpdb->insert(
			$this->table_name,
			array(
				'user_id'        => $user_id,
				'search_term'    => $search_term,
				'search_date'    => $time,
				'email_template' => $email_template,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
			)
		);
	}

	/**
	 * Retrieve search logs with optional limit
	 *
	 * @param int $limit Search limit.
	 * @return array
	 */
	public function get_logs( $limit = 20 ) {
		// Try to get cached results.
		$cached_logs = wp_cache_get( 'momo_acgwc_search_logs_all' );

		if ( false === $cached_logs ) {
			global $wpdb;

			// No cached data, fetch from the database.
			$sql = $wpdb->prepare(
				"SELECT * FROM {$this->table_name} ORDER BY search_date DESC LIMIT %d",
				$limit
			);

			$logs = $wpdb->get_results( $sql );

			// Cache the results for future use (for 5 minutes).
			wp_cache_set( 'momo_acgwc_search_logs_all', $logs, '', 300 );
		} else {
			$logs = $cached_logs;
		}

		return $logs;
	}
	/**
	 * Check table exists
	 */
	public function check_table_exist( $table_name ) {
		global $wpdb;
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SHOW TABLES LIKE %s",
				$table_name
			)
		);
		return $table_exists;
	}
	/**
	 * Fetch search logs from the database with pagination
	 *
	 * @param int $per_page Number of records per page.
	 * @param int $current_page Current page number.
	 * @return array
	 */
	public static function get_search_logs( $per_page = 10, $current_page = 1 ) {
		global $wpdb;

		$offset     = ( $current_page - 1 ) * $per_page;
		$table_name = $wpdb->prefix . 'momo_acgwc_search_logs';

		$sql = $wpdb->prepare(
			"SELECT * FROM $table_name ORDER BY search_date DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}
	/**
	 * Get total number of logs in the database
	 *
	 * @return int
	 */
	public static function record_count() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'momo_acgwc_search_logs';

		$sql = "SELECT COUNT(*) FROM $table_name";

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Prepare the items for the table to display
	 */
	public function prepare_items() {
		$per_page     = $this->get_items_per_page( 'search_logs_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = self::get_search_logs( $per_page, $current_page );
	}

	/**
	 * Define the columns for the table
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'search_term'    => esc_html__( 'Search', 'momoacgwc' ),
			'search_date'    => esc_html__( 'Date', 'momoacgwc' ),
			'user_id'        => esc_html__( 'User', 'momoacgwc' ),
			'email_template' => esc_html__( 'Mail Template', 'momoacgwc' ),
			'actions'        => esc_html__( 'Actions', 'momoacgwc' ),
			'email_actions'  => '',
		);

		return $columns;
	}

	/**
	 * Render each column's content
	 *
	 * @param array  $item        Data for the row.
	 * @param string $column_name Column name.
	 * @return mixed
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'search_term':
				return esc_html( $item['search_term'] );
			case 'search_date':
				return esc_html( gmdate( 'Y-m-d', strtotime( $item['search_date'] ) ) );
			case 'user_id':
				$user_info  = get_userdata( $item['user_id'] );
				$first_name = $user_info->first_name;
				$last_name  = $user_info->last_name;
				$full_name  = $first_name . ' ' . $last_name;
				return ( ! empty( trim( $full_name ) ) ) ? $full_name : $user_info->username;
			case 'email_template':
				return wp_kses_post( $this->display_few_words( $item['email_template'], 10 ) );
			case 'actions':
				$action_span = $this->get_action_span( $item['id'] );
				return $action_span;
			case 'email_actions':
				$action_span = $this->get_email_action_span( $item['id'] );
				return $action_span;
			default:
				return print_r( $item, true );
		}
	}
	/**
	 * Truncates a given text to a specified limit of words.
	 *
	 * @param string $text The original text to be truncated.
	 * @param int    $limit The maximum number of words to display. Defaults to 10.
	 * @return string The truncated text, or the original text if it's within the limit.
	 */
	private function display_few_words( $text, $limit = 10 ) {
		$words = explode( ' ', $text );

		if ( count( $words ) > $limit ) {
			return implode( ' ', array_slice( $words, 0, $limit ) ) . '...';
		} else {
			return $text;
		}
	}
	/**
	 * Get Action Html
	 *
	 * @param int $item_id Log ID.
	 */
	public function get_action_span( $item_id ) {
		?>
		<span class="momo-sl-action-container">
			<span class="momo-sl-action copy" data-id="<?php echo esc_attr( $item_id ); ?>"><i class='bx bx-copy' ></i></span>
			<span class="momo-sl-action edit momo-pb-triggerer" data-id="<?php echo esc_attr( $item_id ); ?>" data-target="search-email-templates-content-model-popbox" data-ajax="momo_acgwc_generate_template_edit_form" data-header="edit_email_template" data-trigger="momo_acgwc_search_tinymce" data-djson="<?php echo esc_attr( $item_id ); ?>"><i class='bx bx-edit-alt'></i></span>
			<span class="momo-sl-action delete" data-id="<?php echo esc_attr( $item_id ); ?>"><i class='bx bxs-trash'></i></span>
		</span>
		<?php
	}
	/**
	 * Get Email Action Html
	 *
	 * @param int $item_id Log ID.
	 */
	public function get_email_action_span( $item_id ) {
		$is_premium = momoacgwc_fs()->is_premium();
		if ( ! $is_premium ) {
			$monthly_limit   = 5;
			$sent_count      = $this->calculate_sent_email_count();
			$remaining_quota = max( 0, $monthly_limit - $sent_count );
		} else {
			$remaining_quota = PHP_INT_MAX;
		}
		ob_start();
		?>
		<span class="momo-sl-action-email">
			<?php
			if ( $remaining_quota > 0 && ( $is_premium || $this->rendered_send_email_count < $remaining_quota ) ) {
				++$this->rendered_send_email_count;
				?>
				<span class="momo-sl-action email" data-id="<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Send Email', 'momoacgwc' ); ?></span>
				<?php
			} else {
				?>
				<span class="momo-sl-action pro" data-id="<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Pro', 'momoacgwc' ); ?></span>
				<?php
			}
			?>
		</span>
		<?php
		return ob_get_clean();
	}
	/**
	 * Display no items message
	 */
	public function no_items() {
		esc_html_e( 'No search logs found.', 'momoacgwc' );
	}
	/**
	 * Modify table classes to exclude footer
	 *
	 * @return array
	 */
	protected function get_table_classes() {
		$classes = parent::get_table_classes();

		$key = array_search( 'has-footer', $classes, true );
		if ( false !== $key ) {
			unset( $classes[ $key ] );
		}

		return $classes;
	}

	/**
	 * Render the table without the footer
	 */
	public function display() {
		?>
		<table class="wp-list-table <?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>">
			<thead>
				<?php $this->print_column_headers(); ?>
			</thead>

			<tbody id="the-list">
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
	}
	/**
	 * Retrieves a template by log ID from the database.
	 *
	 * @param int $log_id The ID of the log to retrieve the template for.
	 * @return string The retrieved template, or an empty string if not found.
	 */
	public function momo_get_template_by_log_id( $log_id ) {
		global $wpdb;
		$table_name = $this->table_name;

		$template = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT email_template FROM $table_name WHERE id = %d",
				$log_id
			)
		);

		return $template ? $template : '';
	}
	/**
	 * Updates a template in the database for a given log ID.
	 *
	 * @param int    $log_id The ID of the log to update the template for.
	 * @param string $new_template The new template to update.
	 * @return bool True if the update was successful, false otherwise.
	 */
	public function momo_set_template_by_log_id( $log_id, $new_template ) {
		global $wpdb;
		$table_name = $this->table_name;

		$updated = $wpdb->update(
			$table_name,
			array( 'email_template' => $new_template ),
			array( 'id' => $log_id ),
			array( '%s' ),
			array( '%d' )
		);
		return $updated !== false;
	}
	/**
	 * Delete log entry by ID
	 *
	 * @param int $log_id The ID of the log to delete.
	 * @return bool|int False on failure, number of rows affected on success.
	 */
	public function momo_delete_log_by_id( $log_id ) {
		global $wpdb;

		$table_name = $this->table_name;

		$log_id = intval( $log_id );

		$result = $wpdb->delete(
			$table_name,
			array( 'id' => $log_id ),
			array( '%d' )
		);

		return $result;
	}
	/**
	 * Retrieves all log data associated with a specific log ID.
	 *
	 * @param int $log_id The ID of the log to retrieve.
	 * @return object The log data as an object.
	 */
	public function momo_get_all_by_id( $log_id ) {
		global $wpdb;
		$table_name = $this->table_name;
		$log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $log_id ) );
		return $log;
	}
	/**
	 * Log email sending event
	 *
	 * @param int $log_id
	 */
	public function log_email_sent( $log_id ) {
		global $wpdb;
		$email_log_table = $this->sent_tbl;
		$wpdb->insert(
			$email_log_table,
			array(
				'log_id' => $log_id,
				'email_sent_at' => current_time( 'mysql' ),
			)
		);
	}
	/**
	 * Check email quota for the current month and return the number of remaining sends
	 */
	public function get_remaining_email_quota( $monthly_limit = 5 ) {
		global $wpdb;
		$table_name = $this->sent_tbl;

		$sent_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM $table_name WHERE MONTH(sent_date) = MONTH(CURRENT_DATE())"
		);

		$remaining_quota = $monthly_limit - $sent_count;

		return max( 0, $remaining_quota );
	}
}
