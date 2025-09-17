<?php
/**
 * Admin Page class
 *
 * @package Spectrum
 */

namespace Eighteen73\Spectrum;

/**
 * Handles the main admin page layout with list table style
 */
class AdminPage {

	/**
	 * Display the main admin page
	 *
	 * @return void
	 */
	public static function render(): void {
		$cache              = Cache::instance();
		$cache_status       = $cache->get_cache_status();
		$api_key_configured = defined( 'SPECTRUM_API_KEY' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Spectrum Patterns', 'spectrum' ); ?></h1>

			<?php self::display_admin_notices_inline(); ?>

			<div class="tablenav top">
				<div class="alignleft actions">
					<form method="post" style="display: inline-block; margin-right: 10px;">
						<?php wp_nonce_field( 'spectrum_admin', 'spectrum_nonce' ); ?>
						<input type="hidden" name="spectrum_action" value="refresh_cache">
						<?php submit_button( __( 'Refresh Cache', 'spectrum' ), 'primary', '', false, [ 'disabled' => ! $api_key_configured ] ); ?>
					</form>

					<form method="post" style="display: inline-block;">
						<?php wp_nonce_field( 'spectrum_admin', 'spectrum_nonce' ); ?>
						<input type="hidden" name="spectrum_action" value="clear_cache">
						<?php submit_button( __( 'Clear Cache', 'spectrum' ), 'secondary', '', false ); ?>
					</form>
				</div>

				<div class="alignright">
					<?php self::display_cache_status_inline( $cache_status, $api_key_configured ); ?>
				</div>
			</div>

			<?php self::display_cached_patterns(); ?>
		</div>
		<?php
	}

	/**
	 * Display admin notices inline (for warnings, etc.)
	 *
	 * @return void
	 */
	private static function display_admin_notices_inline() {
		if ( ! defined( 'SPECTRUM_API_KEY' ) ) {
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'API Key Required:', 'spectrum' ); ?></strong>
					<?php esc_html_e( 'SPECTRUM_API_KEY is not defined in wp-config.php.', 'spectrum' ); ?>
					<?php esc_html_e( 'Add the following line to your wp-config.php file:', 'spectrum' ); ?>
					<br><code>define( 'SPECTRUM_API_KEY', 'your-api-key-here' );</code>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Display cache status inline in the tablenav
	 *
	 * @param array $cache_status Cache status information
	 * @param bool  $api_key_configured Whether API key is configured
	 * @return void
	 */
	private static function display_cache_status_inline( $cache_status, $api_key_configured ): void {
		?>
		<div class="cache-status">
			<strong><?php esc_html_e( 'Cache Status:', 'spectrum' ); ?></strong>
			<?php if ( $cache_status['has_cache'] ) : ?>
				<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
				<span style="color: #46b450;"><?php esc_html_e( 'Active', 'spectrum' ); ?></span>
				<?php if ( isset( $cache_status['expires_in'] ) && $cache_status['expires_in'] > 0 ) : ?>
					<span class="description">
						(<?php
							printf(
								/* translators: %s: human readable time difference */
								esc_html__( 'expires in %s', 'spectrum' ),
								human_time_diff( time(), $cache_status['expires_at'] )
							);
						?>)
					</span>
				<?php endif; ?>
			<?php else : ?>
				<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
				<span style="color: #dc3232;"><?php esc_html_e( 'Empty', 'spectrum' ); ?></span>
			<?php endif; ?>

			<?php if ( ! $api_key_configured ) : ?>
				<span class="description">
					| <span style="color: #dc3232;"><?php esc_html_e( 'API key not configured', 'spectrum' ); ?></span>
				</span>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Display cached patterns using WP_List_Table
	 *
	 * @return void
	 */
	private static function display_cached_patterns(): void {
		$patterns_table = new PatternListTable();
		$patterns_table->prepare_items();

		?>
		<form method="get">
			<input type="hidden" name="page" value="spectrum">
			<?php $patterns_table->display(); ?>
		</form>
		<?php
	}
}
