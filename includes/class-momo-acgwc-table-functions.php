<?php
/**
 * Table functions for MoMo ACG
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v3.5.0
 */
class MoMo_ACGWC_Table_Functions {
	/**
	 * Create an Option like table
	 *
	 * @param string $option_table Option or Table Name.
	 */
	public function momo_create_option_table( $option_table ) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . $option_table;

		$sql = "CREATE TABLE $table_name (
			id INT NOT NULL AUTO_INCREMENT,
			option_key VARCHAR(255) NOT NULL,
			option_value LONGTEXT,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
	/**
	 * Delete an Option like table
	 *
	 * @param string $option_table Option or Table Name.
	 */
	public function momo_delete_option_table( $option_table ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $option_table;

		$sql = "DROP TABLE IF EXISTS {$table_name}";
		// Execute the query.
		$wpdb->query( $sql ); // phpcs:ignore
	}
	/**
	 * Get a value from the custom options table.
	 *
	 * @param string $option_table The name of the option table to retrieve.
	 * @param string $option_name The name of the option to retrieve.
	 * @param mixed  $default Optional. The default value to return if the option doesn't exist.
	 * @return mixed The value of the option, or the default value if not found.
	 */
	public function momo_get_table_option( $option_table, $option_name, $default = false ) {
		// Define a unique cache key based on the option name.
		$cache_key = 'momo_custom_option_' . $option_table . '_' . $option_name;

		// Attempt to retrieve the value from the cache.
		$cached_value = wp_cache_get( $cache_key, 'momo_custom_options' );

		if ( false !== $cached_value ) {
			// If the value is found in the cache, return it.
			return $cached_value;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . $option_table;

		$value = $wpdb->get_var( // phpcs:ignore
			$wpdb->prepare( // phpcs:ignore
				/* translators: %i: table name, %s : option_name */
				"SELECT option_value FROM %i WHERE option_key = %s", // phpcs:ignore
				$table_name,
				$option_name
			)
		);

		$unserialized_value = null === $value ? $default : maybe_unserialize( $value );

		// Store the value in the cache for future use.
		wp_cache_set( $cache_key, $unserialized_value, 'momo_custom_options' );

		return $unserialized_value;
	}

	/**
	 * Update a value in the custom options table with caching.
	 *
	 * @param string $option_table The name of the option table to retrieve.
	 * @param string $option_name The name of the option to update.
	 * @param mixed  $new_value The new value for the option.
	 * @param string $callback Callback function.
	 * @return bool True if the option was updated successfully, false otherwise.
	 */
	public function momo_set_table_option( $option_table, $option_name, $new_value, $callback = null ) {
		global $wpdb;
		$table_name       = $wpdb->prefix . $option_table;
		$serialized_value = maybe_serialize( $new_value );

		// Define a unique cache key for this option.
		$cache_key = 'momo_custom_option_' . $table_name . '_' . $option_name;

		// Check if the option already exists in the database table.
		$existing_option = $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare( // phpcs:ignore
				/* translators: $i: table name, %s: option name */
				'SELECT option_key FROM %i WHERE option_key = %s', // phpcs:ignore
				$table_name,
				$option_name
			)
		);

		if ( $existing_option ) {
			// Option exists, perform an update.
			$updated = $wpdb->update( // phpcs:ignore
				$table_name,
				array( 'option_value' => $serialized_value ),
				array( 'option_key' => $option_name )
			);

			if ( false !== $updated ) {
				// Update the cached value if it exists.
				wp_cache_set( $cache_key, $new_value, 'momo_custom_options' );
			}
			if ( is_callable( $callback ) ) {
				// Execute the callback function if provided.
				call_user_func( $callback );
			}
			return false !== $updated;
		} else {
			// Option does not exist, perform an insert.
			$inserted = $wpdb->insert( // phpcs:ignore
				$table_name,
				array(
					'option_key'   => $option_name,
					'option_value' => $serialized_value,
				)
			);

			if ( false !== $inserted ) {
				// Store the new value in the cache.
				wp_cache_set( $cache_key, $new_value, 'momo_custom_options' );
			}
			if ( is_callable( $callback ) ) {
				// Execute the callback function if provided.
				call_user_func( $callback );
			}
			return false !== $inserted;
		}
	}

}
