<?php
/**
 * Controls admin notices.
 *
 * @package Better_Search
 */

namespace WebberZone\Better_Search\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Notices Class.
 *
 * @since 3.3.0
 */
class Admin_Notices {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'update_db_check' ) );
		add_action( 'admin_notices', array( $this, 'fulltext_index_notice' ) );
		add_action( 'admin_notices', array( $this, 'missing_table_notice' ) );
	}

	/**
	 * Updaate DB check.
	 *
	 * @since 3.3.0
	 */
	public function update_db_check() {
		$current_db_version = get_option( 'bsearch_db_version' );

		if ( $current_db_version && version_compare( $current_db_version, BETTER_SEARCH_DB_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'update_db_notice' ) );
			add_action( 'network_admin_notices', array( $this, 'update_db_notice' ) );
		}
	}

	/**
	 * Display admin notice if the the database is not updated.
	 *
	 * @since 3.3.0
	 */
	public function update_db_notice() {

		// Don't display if current admin screen is bsearch-upgrader.
		if ( isset( $_GET['page'] ) && 'bsearch-upgrader' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'Better Search database needs to be updated. Please click on the button below to update the database.', 'better-search' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bsearch-upgrader&bsearch_action=update_db' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Update Database', 'better-search' ); ?></a>
				
				<?php if ( is_multisite() && current_user_can( 'manage_network_options' ) ) { ?>
					<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=bsearch-upgrader&bsearch_action=update_db' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Update Database (Network)', 'better-search' ); ?></a>
				<?php } ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Display admin notice if the fulltext indexes are not created.
	 *
	 * @since 4.0.0
	 */
	public function fulltext_index_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! \bsearch_get_option( 'use_fulltext' ) ) {
			return;
		}

		// Check if all indexes are installed.
		if ( ! Activator::is_fulltext_index_installed() ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php esc_html_e( 'Better Search: Some fulltext indexes are missing, which will affect search results.', 'better-search' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=bsearch_tools_page' ) ); ?>">
						<?php esc_html_e( 'Click here to recreate indexes.', 'better-search' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Display admin notice if the tables are not created.
	 *
	 * @since 4.0.2
	 */
	public function missing_table_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;

		$table_name       = $wpdb->prefix . 'bsearch';
		$table_name_daily = $wpdb->prefix . 'bsearch_daily';

		if ( ! Activator::is_table_installed( $table_name ) || ! Activator::is_table_installed( $table_name_daily ) ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php esc_html_e( 'Better Search: Some tables are missing, which will affect search results.', 'better-search' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=bsearch_tools_page' ) ); ?>">
						<?php esc_html_e( 'Click here to recreate tables.', 'better-search' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}
}
