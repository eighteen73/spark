<?php
/**
 * Admin Page class
 *
 * @package Spark
 */

namespace Eighteen73\Spark;

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
		$cache                  = Cache::instance();
		$storage_status         = $cache->get_storage_status();
		$credentials_configured = defined( 'SPARK_API_USERNAME' ) && defined( 'SPARK_API_PASSWORD' ) && defined( 'SPARK_API_URI' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Spark Patterns', 'spark' ); ?></h1>

			<?php self::display_admin_notices_inline(); ?>

			<div class="tablenav top">
				<div class="alignleft actions">
					<form method="post" style="display: inline-block;">
						<?php wp_nonce_field( 'spark_admin', 'spark_nonce' ); ?>
						<input type="hidden" name="spark_action" value="update_patterns">
						<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Update Patterns', 'spark' ); ?>" <?php echo $credentials_configured ? '' : 'disabled'; ?>>
					</form>
				</div>

				<div class="alignright">
					<?php self::display_storage_status_inline( $storage_status, $credentials_configured ); ?>
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
		$missing_config = [];

		if ( ! defined( 'SPARK_API_USERNAME' ) ) {
			$missing_config[] = 'SPARK_API_USERNAME';
		}
		if ( ! defined( 'SPARK_API_PASSWORD' ) ) {
			$missing_config[] = 'SPARK_API_PASSWORD';
		}
		if ( ! defined( 'SPARK_API_URI' ) ) {
			$missing_config[] = 'SPARK_API_URI';
		}

		if ( ! empty( $missing_config ) ) {
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Configuration Required:', 'spark' ); ?></strong>
					<?php printf(
						/* translators: %s: comma-separated list of missing constants */
						esc_html__( 'The following constants are not defined in wp-config.php: %s', 'spark' ),
						implode( ', ', $missing_config )
					); ?>
					<?php esc_html_e( 'Add the following lines to your wp-config.php file:', 'spark' ); ?>
					<br><code>define( 'SPARK_API_USERNAME', 'your-username' );</code>
					<br><code>define( 'SPARK_API_PASSWORD', 'your-password' );</code>
					<br><code>define( 'SPARK_API_URI', 'https://your-api-endpoint.com/patterns' );</code>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Display storage status inline in the tablenav
	 *
	 * @param array $storage_status Storage status information
	 * @param bool  $credentials_configured Whether credentials are configured
	 * @return void
	 */
	private static function display_storage_status_inline( $storage_status, $credentials_configured ): void {
		?>
		<div class="storage-status">
			<?php if ( $storage_status['has_patterns'] ) : ?>
				<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
				<span style="color: #46b450;"><?php esc_html_e( 'Active', 'spark' ); ?></span>
				<?php if ( isset( $storage_status['last_sync_formatted'] ) ) : ?>
					<span class="dashicons dashicons-update-alt" style="color: #666; margin-left: 8px;"></span>
					<span class="description" style="margin-left: 4px;">
						<?php echo esc_html( $storage_status['last_sync_formatted'] ); ?>
					</span>
				<?php endif; ?>
			<?php else : ?>
				<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
				<span style="color: #dc3232;"><?php esc_html_e( 'Empty', 'spark' ); ?></span>
			<?php endif; ?>

			<?php if ( ! $credentials_configured ) : ?>
				<span class="description" style="margin-left: 8px;">
					| <span style="color: #dc3232;"><?php esc_html_e( 'Credentials not configured', 'spark' ); ?></span>
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
			<input type="hidden" name="page" value="spark">
			<?php $patterns_table->display(); ?>
		</form>
		<?php
	}
}
