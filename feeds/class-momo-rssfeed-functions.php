<?php
/**
 * MoMo RSS Feed Functions
 *
 * @package momoacg
 * @author MoMo Themes
 * @since v1.3.0
 */
class MoMo_RssFeed_Functions {
	/**
	 * Constructor
	 */
	public function __construct() {
	}
	/**
	 * Generate Cron queue Lists.
	 */
	public function momo_rssfeed_generate_queue_cron_list() {
		$lists = $this->momo_get_cron_events_list();
		ob_start();
		if ( empty( $lists ) ) {
			?>
			<div class="momo-rssfeed-empty-queue">
				<?php esc_html_e( 'Empty queue list.', 'momoacgwc' ); ?>
			</div>
			<?php
		} else {
			?>
			<table class="momo-rssfeed-queue-list-table">
				<thead>
					<tr>
						<th class="action"></th>
						<th><?php esc_html_e( 'ID', 'momoacgwc' ); ?></th>
						<th><?php esc_html_e( 'Title', 'momoacgwc' ); ?></th>
						<th><?php esc_html_e( 'Scheduled Datetime', 'momoacgwc' ); ?></th>
					</tr>
				</thead>
				<tbody>
			<?php
			foreach ( $lists as $list ) {
				$cron_id = '';
				?>
				<tr>
				<?php
				$timestamp = $list['timestamp'];
				$datetime  = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
				foreach ( $list['content'] as $id => $content ) {
					$cron_id = $id;
					$title   = isset( $content['args'][0]['title'] ) ? $content['args'][0]['title'] : $content['args'][0];
				}
				?>
					<td class="action" title="<?php esc_html_e( 'Remove', 'momoacgwc' ); ?>" data-id="<?php echo esc_html( $cron_id ); ?>">
						<span class="momo-remove-holder">
							<i class="bx bxs-trash momo-rssfeed-remove-cron"></i>
						</span>
					</td>
					<td><?php echo esc_html( $cron_id ); ?></td>
					<td><?php echo esc_html( $title ); ?></td>
					<td><?php echo esc_html( $datetime ); ?></td>
				</tr>
				<?php
			}
			?>
				</tbody>
			</table>
			<?php
		}
		return ob_get_clean();
	}
	/**
	 * Generate Cron queue Lists.
	 */
	public function momo_autoblog_generate_queue_cron_list() {
		$lists = $this->momo_get_cron_autoblog_list();
		ob_start();
		if ( empty( $lists ) ) {
			?>
			<div class="momo-rssfeed-empty-queue">
				<?php esc_html_e( 'Empty queue list.', 'momoacgwc' ); ?>
			</div>
			<?php
		} else {
			?>
			<table class="momo-rssfeed-queue-list-table">
				<thead>
					<tr>
						<th class="action"></th>
						<th><?php esc_html_e( 'ID', 'momoacgwc' ); ?></th>
						<th><?php esc_html_e( 'Tag(s)', 'momoacgwc' ); ?></th>
						<th><?php esc_html_e( 'Scheduled', 'momoacgwc' ); ?></th>
					</tr>
				</thead>
				<tbody>
			<?php
			foreach ( $lists as $list ) {
				$cron_id = '';
				?>
				<tr>
				<?php
				$timestamp = $list['timestamp'];
				$datetime  = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
				foreach ( $list['content'] as $id => $content ) {
					$cron_id  = $id;
					$title    = isset( $content['args']['tags'] ) ? $content['args']['tags'] : '';
					$schedule = isset( $content['args']['time_basis'] ) ? $content['args']['time_basis'] : '';
				}
				?>
					<td class="action" title="<?php esc_html_e( 'Remove', 'momoacgwc' ); ?>" data-id="<?php echo esc_html( $cron_id ); ?>">
						<span class="momo-remove-holder">
							<i class="bx bxs-trash momo-rssfeed-remove-cron"></i>
						</span>
					</td>
					<td><?php echo esc_html( $cron_id ); ?></td>
					<td><?php echo esc_html( $title ); ?></td>
					<td><?php echo esc_html( ucfirst( $schedule ) ); ?></td>
				</tr>
				<?php
			}
			?>
				</tbody>
			</table>
			<?php
		}
		return ob_get_clean();
	}
	/**
	 * Get cron events list
	 */
	public function momo_get_cron_events_list() {
		$crons = _get_cron_array();
		$lists = array();
		foreach ( $crons as $timestamp => $cron ) {
			if ( isset( $cron['momo_acg_rssfeed_hook'] ) || isset( $cron['momo_acg_create_page_hook'] ) ) {
				if ( isset( $cron['momo_acg_rssfeed_hook'] ) ) {
					$lists[] = array(
						'timestamp' => $timestamp,
						'content'   => $cron['momo_acg_rssfeed_hook'],
					);
				} elseif ( isset( $cron['momo_acg_create_page_hook'] ) ) {
					$lists[] = array(
						'timestamp' => $timestamp,
						'content'   => $cron['momo_acg_create_page_hook'],
					);
				}
			}
		}
		return $lists;
	}
	/**
	 * Get cron events list
	 */
	public function momo_get_cron_autoblog_list() {
		$crons       = _get_cron_array();
		$lists       = array();
		$search_hook = 'momo_acg_autoblog_hook';
		foreach ( $crons as $timestamp => $events ) {
			foreach ( $events as $hook => $data ) {
				if ( preg_match( '/^' . $search_hook . '_\d+$/', $hook ) ) {
					$lists[] = array(
						'timestamp' => $timestamp,
						'content'   => $data,
					);
				}
			}
		}
		return $lists;
	}
	/**
	 * Get cron events list normal
	 */
	public function momo_get_cron_events_list_normal() {
		$crons = _get_cron_array();
		$lists = array();

		$search_hook = 'momo_acg_autoblog_hook';
		foreach ( $crons as $timestamp => $cron ) {
			foreach ( $cron as $event_hook => $event_args ) {
				foreach ( $event_args as $cron_id => $args ) {
					if ( 'momo_acg_rssfeed_hook' === $event_hook || 'momo_acg_create_page_hook' === $event_hook || preg_match( '/^' . $search_hook . '_\d+$/', $event_hook ) ) {
						if ( 'momo_acg_rssfeed_hook' === $event_hook ) {
							$args['hook']      = $event_hook;
							$args['timestamp'] = $timestamp;
							$lists[ $cron_id ] = $args;
						} elseif ( 'momo_acg_create_page_hook' === $event_hook ) {
							$args['hook']      = $event_hook;
							$args['timestamp'] = $timestamp;
							$lists[ $cron_id ] = $args;
						} elseif ( preg_match( '/^' . $search_hook . '_\d+$/', $event_hook ) ) {
							$args['hook']      = $event_hook;
							$args['timestamp'] = $timestamp;
							$lists[ $cron_id ] = $args;
						}
					}
				}
			}
		}
		return $lists;
	}
	/**
	 * Get single event by cron id.
	 *
	 * @param string $cron_id Cron ID.
	 */
	public function momo_get_rssfeed_single_event( $cron_id ) {
		$events = $this->momo_get_cron_events_list_normal();
		return isset( $events[ $cron_id ] ) ? $events[ $cron_id ] : false;
	}
}
