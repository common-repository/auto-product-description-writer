<?php
/**
 * MoMO ACG WC - Related Product
 *
 * @author MoMo Themes
 * @package momoacgwc
 * @since v1.2.1
 */
class Momo_Acg_Related_Actions {
	/**
	 * Days
	 *
	 * @var int
	 */
	public $days;
	/**
	 * Top
	 *
	 * @var int
	 */
	public $top;
	/**
	 * Constructor
	 */
	public function __construct() {
		$related_settings             = get_option( 'momo_acg_wc_related_settings' );
		$enable_product_recomendation = isset( $related_settings['enable_product_recomendation'] ) ? $related_settings['enable_product_recomendation'] : 'off';
		if ( 'on' === $enable_product_recomendation ) {
			add_filter( 'woocommerce_related_products', array( $this, 'momo_acg_remove_default_related_products' ), 100, 2 );
			add_filter( 'woocommerce_output_related_products_args', array( $this, 'momo_acg_change_related_args' ), 100, 1 );

			// For User Viewed Products.
			$value = 'user_viewed_products';
			if ( $this->recomendation_source_enabled( $value ) ) {
				add_action( 'woocommerce_before_single_product', array( $this, 'track_viewed_product_on_single_product_page' ) );
				add_action( 'wp', array( $this, 'schedule_delete_old_viewed_products' ) );
				add_action( 'momoacgwc_delete_old_viewed_products_event', 'momoacgwc_delete_old_viewed_products' );
			}
			// Removed Cart Items.
			$value = 'removed_cart_items';
			if ( $this->recomendation_source_enabled( $value ) ) {
				add_action( 'woocommerce_cart_item_removed', array( $this, 'track_removed_cart_item' ), 10, 2 );

				add_action( 'wp', array( $this, 'schedule_delete_old_removed_cart_products' ) );
				add_action( 'momoacgwc_delete_old_removed_cart_products_event', 'momoacgwc_delete_old_removed_cart_products' );
			}
		}
		$this->days = 30;
		$this->top  = 5;
	}
	/**
	 * Check recomendation source enabled
	 *
	 * @param string $key Key.
	 */
	public function recomendation_source_enabled( $key ) {
		$related_settings       = get_option( 'momo_acg_wc_related_settings' );
		$recommendation_sources = isset( $related_settings['recommendation_sources'] ) ? $related_settings['recommendation_sources'] : array();
		if ( in_array( $key, $recommendation_sources, true ) ) {
			return true;
		}
		return false;
	}
	/**
	 * Delete Old Viewed Products
	 */
	public function momoacgwc_delete_old_viewed_products() {
		if ( isset( $_COOKIE['momo_recomendation_viewed_products'] ) ) {
			$viewed_products = json_decode( wp_unslash( $_COOKIE['momo_recomendation_viewed_products'] ), true ); //phpcs:ignore

			if ( $viewed_products ) {
				$current_time    = time();
				$thirty_days_ago = $current_time - ( DAY_IN_SECONDS * $this->days );

				foreach ( $viewed_products as $product_id => $timestamp ) {
					if ( $timestamp < $thirty_days_ago ) {
						unset( $viewed_products[ $product_id ] );
					}
				}

				// Update the cookie.
				setcookie( 'momo_recomendation_viewed_products', wp_json_encode( $viewed_products ), $current_time + ( DAY_IN_SECONDS * $this->days ), '/' );
			}
		}
	}
	/**
	 * Delete Old Removed Cart Products
	 */
	public function momoacgwc_delete_old_removed_cart_products() {
		if ( isset( $_COOKIE['momo_recomendation_removed_cart_products'] ) ) {
			$removed_cart_products = json_decode( wp_unslash( $_COOKIE['momo_recomendation_removed_cart_products'] ), true ); //phpcs:ignore

			if ( $removed_cart_products ) {
				$current_time    = time();
				$thirty_days_ago = $current_time - ( DAY_IN_SECONDS * $this->days );

				foreach ( $removed_cart_products as $product_id => $timestamp ) {
					if ( $timestamp < $thirty_days_ago ) {
						unset( $removed_cart_products[ $product_id ] );
					}
				}

				// Update the cookie.
				setcookie( 'momo_recomendation_removed_cart_products', wp_json_encode( $removed_cart_products ), $current_time + ( DAY_IN_SECONDS * $this->days ), '/' );
			}
		}
	}
	/**
	 * Schedule Delete Old Viewed Products
	 */
	public function schedule_delete_old_viewed_products() {
		if ( ! wp_next_scheduled( 'momoacgwc_delete_old_viewed_products_event' ) ) {
			wp_schedule_event( time(), 'daily', 'momoacgwc_delete_old_viewed_products_event' );
		}
	}
	/**
	 * Schedule Delete Old Removed Cart Products
	 */
	public function schedule_delete_old_removed_cart_products() {
		if ( ! wp_next_scheduled( 'momoacgwc_delete_old_removed_cart_products_event' ) ) {
			wp_schedule_event( time(), 'daily', 'momoacgwc_delete_old_removed_cart_products_event' );
		}
	}
	/**
	 * Track Viewed Product on Single Product
	 */
	public function track_viewed_product_on_single_product_page() {
		if ( is_product() ) {
			$product_id = get_the_ID();
			$this->momoacgwc_record_viewed_product( $product_id );
		}
	}
	/**
	 * Track Removed Cart Item
	 *
	 * @param string  $cart_item_key Cart item key.
	 * @param WC_Cart $wc_cart WC Cart.
	 * @return void
	 */
	public function track_removed_cart_item( $cart_item_key, $wc_cart ) {
		$removed_item = $wc_cart->removed_cart_contents[ $cart_item_key ];
		$product_id   = $removed_item['product_id'];
		$this->momoacgwc_record_removed_cart_product( $product_id );

	}
	/**
	 * Record Viewed Product
	 *
	 * @param int $product_id Product ID.
	 */
	public function momoacgwc_record_viewed_product( $product_id ) {
		$product_id      = sanitize_key( $product_id );
		$viewed_products = isset( $_COOKIE['momo_recomendation_viewed_products'] ) ? json_decode( wp_unslash( $_COOKIE['momo_recomendation_viewed_products'] ), true ) : array(); //phpcs:ignore

		$viewed_products[ $product_id ] = time();

		// Set the cookie.
		setcookie( 'momo_recomendation_viewed_products', wp_json_encode( $viewed_products ), time() + ( DAY_IN_SECONDS * $this->days ), '/' );
	}
	/**
	 * Record Removed Cart Product
	 *
	 * @param int $product_id Product ID.
	 */
	public function momoacgwc_record_removed_cart_product( $product_id ) {
		$product_id            = sanitize_key( $product_id );
		$removed_cart_products = isset( $_COOKIE['momo_recomendation_removed_cart_products'] ) ? json_decode( wp_unslash( $_COOKIE['momo_recomendation_removed_cart_products'] ), true ) : array(); //phpcs:ignore

		$removed_cart_products[ $product_id ] = time();

		// Set the cookie.
		setcookie( 'momo_recomendation_removed_cart_products', wp_json_encode( $removed_cart_products ), time() + ( DAY_IN_SECONDS * $this->days ), '/' );
	}
	/**
	 * Track previous orders within the last 30 days.
	 */
	public function track_previous_orders() {
		$ordered_product_ids = array();
		if ( ! is_user_logged_in() ) {
			return $ordered_product_ids;
		}
		$user_id = get_current_user_id();

		$args = array(
			'customer_id' => $user_id,
			'date_query'  => array(
				array(
					'after'     => gmdate( 'Y-m-d', strtotime( "-{$this->days} days" ) ),
					'inclusive' => true,
				),
			),
			'status'      => array_keys( wc_get_order_statuses() ),
			'limit'       => -1,
		);

		$orders = wc_get_orders( $args );

		foreach ( $orders as $order ) {
			$items = $order->get_items();
			foreach ( $items as $item ) {
				$product_id            = $item->get_product_id();
				$ordered_product_ids[] = $product_id;
			}
		}
		return $ordered_product_ids;
	}
	/**
	 * Track products frequently purchased together.
	 */
	public function track_frequently_purchased_together( $product_id ) {
		$days_ago = gmdate( 'Y-m-d H:i:s', strtotime( "-{$this->days} days" ) );
		// Get past orders.
		$orders = wc_get_orders(
			array(
				'limit'        => -1,
				'date_created' => '>=' . $days_ago,
			)
		);

		$product_combinations = array();

		foreach ( $orders as $order ) {
			$product_in_order = false;

			foreach ( $order->get_items() as $item ) {
				if ( $item->get_product_id() === $product_id ) {
					$product_in_order = true;
					break;
				}
			}

			if ( $product_in_order ) {
				foreach ( $order->get_items() as $item ) {
					$other_product_id = $item->get_product_id();

					if ( $other_product_id != $product_id ) {
						if ( ! isset( $product_combinations[ $other_product_id ] ) ) {
							$product_combinations[ $other_product_id ] = 1;
						} else {
							$product_combinations[ $other_product_id ]++;
						}
					}
				}
			}
		}

		arsort( $product_combinations );
		return $product_combinations;

	}
	/**
	 * Track Best Selling Products
	 *
	 * @param int $exclude_product_id Exclude product ID.
	 */
	public function track_best_selling_products( $exclude_product_id ) {
		$args = array(
			'date_query' => array(
				array(
					'after' => strtotime( "-{$this->days} days" ),
				),
			),
			'status'     => 'completed',
			'limit'      => -1,
		);
		$orders = wc_get_orders( $args );

		$product_sales = array();

		foreach ( $orders as $order ) {
			foreach ( $order->get_items() as $item ) {
				$product_id = $item->get_product_id();

				if ( $product_id === $exclude_product_id || 0 === $product_id ) {
					continue;
				}

				if ( ! isset( $product_sales[ $product_id ] ) ) {
					$product_sales[ $product_id ] = 1;
				} else {
					$product_sales[ $product_id ]++;
				}
			}
		}

		arsort( $product_sales );

		$best_selling_products = array_slice( $product_sales, 0, $this->top, true );

		return $best_selling_products;
	}
	/**
	 * Track Top Rated Products
	 *
	 * @param int $product_id Product ID.
	 */
	public function track_top_rated_products( $product_id ) {
		global $wpdb;

		// Define a unique cache key for the query results.
		$cache_key = 'momo_recomendation_top_rated_products_' . $product_id . '_' . $this->top;

		// Try to retrieve the query results from the cache.
		$cached_results = wp_cache_get( $cache_key, 'top_rated_products' );

		// If the results are found in the cache, return them.
		if ( false !== $cached_results ) {
			return $cached_results;
		}

		$query = $wpdb->prepare(
			"
			SELECT 
				p.ID AS product_id,
				AVG(pm.meta_value) AS average_rating
			FROM 
				{$wpdb->posts} p
			INNER JOIN 
				{$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE 
				p.post_type = 'product'
				AND p.post_status = 'publish'
				AND pm.meta_key = '_wc_average_rating'
				AND p.ID != %d
			GROUP BY 
				p.ID
			ORDER BY 
				average_rating DESC
			LIMIT 
				%d
			",
			$product_id,
			$this->top
		);

		$top_rated_products = $wpdb->get_results( $query ); // phpcs:ignore

		// Store the query results in the cache for future use.
		wp_cache_set( $cache_key, $top_rated_products );

		return $top_rated_products;
	}
	/**
	 * Track Featured Products
	 */
	public function track_featured_products() {
		return wc_get_featured_product_ids();
	}
	/**
	 * Track On Sale Products
	 */
	public function track_on_sale_products() {
		return wc_get_product_ids_on_sale();
	}
	/**
	 * Track New Arrival Products
	 *
	 * @param int $product_id Product ID.
	 */
	public function track_new_arrival_products( $product_id ) {
		$cache_key               = 'momoacgwc_new_arrival_products';
		$new_arrival_product_ids = get_transient( $cache_key );
		if ( true === $new_arrival_product_ids || empty( $new_arrival_product_ids ) ) {
			$args = array(
				'status'  => 'publish',
				'limit'   => $this->top,
				'orderby' => 'date',
				'order'   => 'DESC',
				'exclude' => array( $product_id ),
			);

			$new_arrival_products    = wc_get_products( $args );
			$new_arrival_product_ids = wp_list_pluck( $new_arrival_products, 'id' );
			if ( ! empty( $new_arrival_product_ids ) ) {
				set_transient( $cache_key, $new_arrival_product_ids, DAY_IN_SECONDS );
			}
		}
		return $new_arrival_product_ids;
	}
	/**
	 * Track Same Category products
	 *
	 * @param int $product_id Product ID.
	 */
	public function track_same_category_products( $product_id ) {
		$product_ids        = array();
		$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

		if ( ! empty( $product_categories ) ) {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => $this->top,
				'post_status'    => 'publish',
				'post__not_in'   => array( $product_id ),
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => $product_categories,
						'operator' => 'IN',
					),
				),
			);

			$products_query = new WP_Query( $args );
			// Extract product IDs from the query results.
			$product_ids = wp_list_pluck( $products_query->posts, 'ID' );
			// Reset the query to avoid conflicts with other loops.
			wp_reset_postdata();
		}
		return $product_ids;
	}
	/**
	 * Track Product with similar title
	 *
	 * @param int $product_id Product ID.
	 */
	public function track_product_with_similar_title( $product_id ) {
		$product     = wc_get_product( $product_id );
		$product_ids = array();
		if ( $product ) {
			$product_title = $product->get_title();

			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => $this->top,
				'post_status'    => 'publish',
				'post__not_in'   => array( $product_id ),
				's'              => $product_title,
			);

			$products_query = new WP_Query( $args );

			$product_ids = wp_list_pluck( $products_query->posts, 'ID' );
			wp_reset_postdata();
		}
		return $product_ids;
	}
	/**
	 * Track Random Products
	 *
	 * @param int $product_id Product ID.
	 */
	public function track_random_products( $product_id ) {
		$random_product_ids = array();

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $this->top,
			'orderby'        => 'rand',
			'fields'         => 'ids',
		);

		$random_products_query = new WP_Query( $args );

		if ( $random_products_query->have_posts() ) {
			$random_product_ids = $random_products_query->posts;
			wp_reset_postdata();
		}
		return $random_product_ids;
	}
	/**
	 * Remove default related products
	 *
	 * @param array $args Arguments.
	 */
	public function momo_acg_change_related_args( $args ) {
		return $args;
	}
	/**
	 * Change Default Values
	 *
	 * @param array $args Arguments.
	 * @param int   $product_id Product ID.
	 */
	public function momo_acg_remove_default_related_products( $args, $product_id ) {
		$related_settings     = get_option( 'momo_acg_wc_related_settings' );
		$no_of_products_count = isset( $related_settings['no_of_products_count'] ) ? $related_settings['no_of_products_count'] : 5;
		$products_per_row     = isset( $related_settings['products_per_row'] ) ? $related_settings['products_per_row'] : 3;

		$default_list = array(
			'user_viewed_products',
			'removed_cart_items',
			'previous_orders',
			'frequently_purchased_together',
			'best_selling',
			'top_rated',
			'featured_products',
			'on_sale',
			'new_arrivals',
			'same_category',
			'similar_title',
			'random',
		);
		$enabled_list = array();
		foreach ( $default_list as $value ) {
			if ( $this->recomendation_source_enabled( $value ) ) {
				$enabled_list[] = $value;
			}
		}
		$args = $this->momo_generate_recomendated_products( $product_id, $no_of_products_count, $products_per_row, $enabled_list );

		$related_products = new WP_Query( $args );
		return $related_products->posts;
	}
	/**
	 * Generate recomendated products
	 *
	 * @param int   $product_id Product ID.
	 * @param int   $no_of_products_count Number of products.
	 * @param int   $products_per_row Products per row.
	 * @param array $enabled_list Enabled list.
	 */
	public function momo_generate_recomendated_products( $product_id = 0, $no_of_products_count = 5, $products_per_row = 3, $enabled_list = array() ) {
		$args = array(
			'post_type'      => 'product',
			'post__not_in'   => array( (int) $product_id ),
			'posts_per_page' => (int) $no_of_products_count,
			'column'         => $products_per_row,
			'orderby'        => 'rand',
			'post__in'       => array(),
		);

		$value = 'user_viewed_products';
		if ( in_array( $value, $enabled_list, true ) ) {
			$viewed_products = isset( $_COOKIE['momo_recomendation_viewed_products'] ) ? json_decode( wp_unslash( $_COOKIE['momo_recomendation_viewed_products'] ), true ) : array(); //phpcs:ignore
			$viewed_ids      = array();
			if ( ! empty( $viewed_products ) ) {
				foreach ( $viewed_products as $cproduct_id => $timestamp ) {
					if ( $product_id === $cproduct_id ) {
						continue;
					}
					$viewed_ids[] = $cproduct_id;
				}
			}
			if ( ! empty( $viewed_ids ) ) {
				$args['post__in'] = $viewed_ids;
			}
		}
		$value = 'removed_cart_items';
		if ( in_array( $value, $enabled_list, true ) ) {
			$removed_cart_products = isset( $_COOKIE['momo_recomendation_removed_cart_products'] ) ? json_decode( wp_unslash( $_COOKIE['momo_recomendation_removed_cart_products'] ), true ) : array(); //phpcs:ignore
			$removed_cart_ids      = array();
			if ( ! empty( $removed_cart_products ) ) {
				foreach ( $removed_cart_products as $cproduct_id => $timestamp ) {
					if ( $product_id === $cproduct_id ) {
						continue;
					}
					$removed_cart_ids[] = $cproduct_id;
				}
			}
			if ( ! empty( $removed_cart_ids ) ) {
				$existing         = $args['post__in'];
				$merged           = array_merge( $existing, $removed_cart_ids );
				$args['post__in'] = $merged;
			}
		}
		$value = 'previous_orders';
		if ( in_array( $value, $enabled_list, true ) ) {
			$previous_order_ids = $this->track_previous_orders();
			if ( ! empty( $previous_order_ids ) ) {
				$existing         = $args['post__in'];
				$merged           = array_merge( $existing, $previous_order_ids );
				$args['post__in'] = $merged;
			}
		}
		$value = 'frequently_purchased_together';
		if ( in_array( $value, $enabled_list, true ) ) {
			$frequently_purchased_together = $this->track_frequently_purchased_together( $product_id );
			if ( ! empty( $frequently_purchased_together ) ) {
				$fpt_ids = array();
				foreach ( $frequently_purchased_together as $product_id => $frequency ) {
					$fpt_ids[] = $product_id;
				}
				$existing         = $args['post__in'];
				$merged           = array_merge( $existing, $fpt_ids );
				$args['post__in'] = $merged;
			}
		}
		$value = 'best_selling';
		if ( in_array( $value, $enabled_list, true ) ) {
			$best_selling_products = $this->track_best_selling_products( $product_id );
			if ( ! empty( $best_selling_products ) ) {
				$bsp_ids = array();
				foreach ( $best_selling_products as $product_id => $frequency ) {
					$bsp_ids[] = $product_id;
				}
				$existing         = $args['post__in'];
				$merged           = array_merge( $existing, $bsp_ids );
				$args['post__in'] = $merged;
			}
		}
		$value = 'top_rated';
		if ( in_array( $value, $enabled_list, true ) ) {
			$top_rated_products = $this->track_top_rated_products( $product_id );
			if ( ! empty( $top_rated_products ) ) {
				$trp_ids = array();
				foreach ( $top_rated_products as $product ) {
					if ( isset( $product->average_rating ) && $product->average_rating > 3 ) {
						$trp_ids[] = $product->product_id;
					}
				}
				if ( ! empty( $trp_ids ) ) {
					$existing         = $args['post__in'];
					$merged           = array_merge( $existing, $trp_ids );
					$args['post__in'] = $merged;
				}
			}
		}
		$value = 'featured_products';
		if ( in_array( $value, $enabled_list, true ) ) {
			$featured_products = $this->track_featured_products();
			if ( ! empty( $featured_products ) ) {
				$fp_ids = array();
				foreach ( $featured_products as $fproduct_id ) {
					if ( $fproduct_id !== $product_id ) {
						$fp_ids[] = $fproduct_id;
					}
				}
				if ( ! empty( $fp_ids ) ) {
					$existing         = $args['post__in'];
					$merged           = array_merge( $existing, $fp_ids );
					$args['post__in'] = $merged;
				}
			}
		}
		$value = 'on_sale';
		if ( in_array( $value, $enabled_list, true ) ) {
			$onsale_products = $this->track_on_sale_products();
			if ( ! empty( $onsale_products ) ) {
				$os_ids = array();
				foreach ( $onsale_products as $sproduct_id ) {
					if ( $sproduct_id !== $product_id ) {
						$os_ids[] = $sproduct_id;
					}
				}
				if ( ! empty( $os_ids ) ) {
					$existing         = $args['post__in'];
					$merged           = array_merge( $existing, $os_ids );
					$args['post__in'] = $merged;
				}
			}
		}
		$value = 'new_arrivals';
		if ( in_array( $value, $enabled_list, true ) ) {
			$new_arrivals_products = $this->track_new_arrival_products( $product_id );

			if ( ! empty( $new_arrivals_products ) ) {
				$existing         = $args['post__in'];
				$merged           = array_merge( $existing, $new_arrivals_products );
				$args['post__in'] = $merged;
			}
		}
		$value = 'same_category';
		if ( in_array( $value, $enabled_list, true ) ) {
			$same_category_products = $this->track_same_category_products( $product_id );
			if ( ! empty( $same_category_products ) ) {
				$existing         = $args['post__in'];
				$merged           = array_merge( $existing, $same_category_products );
				$args['post__in'] = $merged;
			}
		}
		$value = 'similar_title';
		if ( in_array( $value, $enabled_list, true ) ) {
			$similar_title_products = $this->track_product_with_similar_title( $product_id );
			if ( ! empty( $similar_title_products ) ) {
				$existing         = $args['post__in'];
				$merged           = array_merge( $existing, $similar_title_products );
				$args['post__in'] = $merged;
			}
		}
		$value = 'random';
		if ( in_array( $value, $enabled_list, true ) ) {
			$random_products = $this->track_random_products( $product_id );
			if ( ! empty( $random_products ) ) {
				$existing         = $args['post__in'];
				$merged           = array_merge( $existing, $random_products );
				$args['post__in'] = $merged;
			}
		}
		return $args;
	}
}
