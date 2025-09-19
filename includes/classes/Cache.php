<?php
/**
 * Cache class
 *
 * @package Spectrum
 */

namespace Eighteen73\Spectrum;

use WP_Error;

/**
 * Handles storage of pattern data using WordPress Options API.
 */
class Cache {
	use Singleton;

	/**
	 * Option key for storing patterns
	 *
	 * @var string
	 */
	private $option_key = 'spectrum_patterns';

	/**
	 * Option key for storing last sync timestamp
	 *
	 * @var string
	 */
	private $sync_timestamp_key = 'spectrum_patterns_sync_timestamp';

	/**
	 * Get stored patterns
	 *
	 * @return array|false Array of patterns or false if not stored
	 */
	public function get_patterns(): array|false {
		$patterns = get_option( $this->option_key, false );
		return $patterns !== false && is_array( $patterns ) ? $patterns : false;
	}

	/**
	 * Set patterns in options table
	 *
	 * @param array $patterns Array of pattern data to store
	 * @return bool True on success, false on failure
	 */
	public function set_patterns( $patterns ): bool {
		if ( ! is_array( $patterns ) ) {
			return false;
		}

		$result = update_option( $this->option_key, $patterns );

		// Update sync timestamp
		if ( $result ) {
			update_option( $this->sync_timestamp_key, current_time( 'timestamp' ) );
		}

		return $result;
	}

	/**
	 * Clear the stored patterns
	 *
	 * @return bool True on success, false on failure
	 */
	public function clear_patterns(): bool {
		$result = delete_option( $this->option_key );
		delete_option( $this->sync_timestamp_key );
		return $result;
	}

	/**
	 * Check if patterns are stored
	 *
	 * @return bool True if patterns exist, false otherwise
	 */
	public function has_patterns(): bool {
		$patterns = $this->get_patterns();
		return $patterns !== false && is_array( $patterns );
	}

	/**
	 * Get storage status information
	 *
	 * @return array Storage status with timestamp info
	 */
	public function get_storage_status(): array {
		$has_patterns = $this->has_patterns();
		$last_sync = get_option( $this->sync_timestamp_key, false );

		$status = [
			'has_patterns' => $has_patterns,
			'option_key'   => $this->option_key,
		];

		if ( $last_sync ) {
			$status['last_sync'] = $last_sync;
			$status['last_sync_formatted'] = sprintf(
				'%s (%s ago)',
				date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_sync ),
				human_time_diff( $last_sync, current_time( 'timestamp' ) )
			);
		}

		return $status;
	}

	/**
	 * Update patterns by fetching new data from API
	 *
	 * @return array|WP_Error Array of patterns on success, WP_Error on failure
	 */
	public function update_patterns(): array|WP_Error {
		$api_client = ApiClient::instance();
		$patterns = $api_client->fetch_patterns();

		if ( ! is_wp_error( $patterns ) ) {
			$this->set_patterns( $patterns );
		}

		return $patterns;
	}

	/**
	 * Schedule weekly sync
	 */
	public function schedule_weekly_sync(): void {
		if ( ! wp_next_scheduled( 'spectrum_weekly_sync' ) ) {
			wp_schedule_event( time(), 'weekly', 'spectrum_weekly_sync' );
		}
	}

	/**
	 * Unschedule weekly sync
	 */
	public function unschedule_weekly_sync(): void {
		$timestamp = wp_next_scheduled( 'spectrum_weekly_sync' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'spectrum_weekly_sync' );
		}
	}
}
