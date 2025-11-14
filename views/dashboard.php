<?php
/**
 * Dashboard view template.
 *
 * @package SiteOps\Cockpit
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$update_stats  = isset( $update_stats ) && is_array( $update_stats ) ? $update_stats : [];
$site_info     = isset( $site_info ) && is_array( $site_info ) ? $site_info : [];
$env_info      = isset( $env_info ) && is_array( $env_info ) ? $env_info : [];
$uptime_status = isset( $uptime_status ) && is_array( $uptime_status ) ? $uptime_status : [];

$core_update_available = ! empty( $update_stats['core_update_available'] );
$plugin_updates_count  = isset( $update_stats['plugin_updates_count'] ) ? (int) $update_stats['plugin_updates_count'] : 0;
$theme_updates_count   = isset( $update_stats['theme_updates_count'] ) ? (int) $update_stats['theme_updates_count'] : 0;

$memory_warning_string    = __( 'Memory limit below 256M', 'site-ops-cockpit' );
$execution_warning_string = __( 'Max execution time below 60 seconds', 'site-ops-cockpit' );
$warnings                 = isset( $site_info['warnings'] ) && is_array( $site_info['warnings'] ) ? $site_info['warnings'] : [];
$memory_is_ok             = ! in_array( $memory_warning_string, $warnings, true );
$execution_is_ok          = ! in_array( $execution_warning_string, $warnings, true );

$other_warnings = array_filter(
    $warnings,
    static function ( $warning ) use ( $memory_warning_string, $execution_warning_string ) {
        return ! in_array( $warning, [ $memory_warning_string, $execution_warning_string ], true );
    }
);

$uptime_settings = isset( $uptime_settings ) && is_array( $uptime_settings ) ? $uptime_settings : [];
$uptime_url      = ! empty( $uptime_settings['uptime_check_url'] ) ? $uptime_settings['uptime_check_url'] : site_url();
$status_code = isset( $uptime_status['status_code'] ) ? $uptime_status['status_code'] : null;
$response_ms = isset( $uptime_status['response_time_ms'] ) ? $uptime_status['response_time_ms'] : null;
$last_check  = isset( $uptime_status['last_checked'] ) ? (int) $uptime_status['last_checked'] : 0;
$error_text  = isset( $uptime_status['error_message'] ) ? $uptime_status['error_message'] : null;
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Site Ops Cockpit', 'site-ops-cockpit' ); ?></h1>
    <p class="description"><?php esc_html_e( 'A quick overview of this site\'s operational readiness.', 'site-ops-cockpit' ); ?></p>

    <div class="soc-wrap">
        <div class="soc-card">
            <h2>
                <?php esc_html_e( 'Updates', 'site-ops-cockpit' ); ?>
                <span class="<?php echo $core_update_available ? 'soc-status-warning' : 'soc-status-ok'; ?>">
                    <?php echo $core_update_available ? esc_html__( 'Update Available', 'site-ops-cockpit' ) : esc_html__( 'Up to Date', 'site-ops-cockpit' ); ?>
                </span>
            </h2>
            <ul class="soc-metrics-list">
                <li>
                    <span><?php esc_html_e( 'WordPress Version', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $update_stats['current_wp_version'] ?? '' ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Plugin Updates', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $plugin_updates_count ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Theme Updates', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $theme_updates_count ); ?></strong>
                </li>
            </ul>
        </div>

        <div class="soc-card">
            <h2><?php esc_html_e( 'Site / Server Info', 'site-ops-cockpit' ); ?></h2>
            <ul class="soc-metrics-list">
                <li>
                    <span><?php esc_html_e( 'WordPress Version', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $site_info['wp_version'] ?? '' ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'PHP Version', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $site_info['php_version'] ?? '' ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Database Version', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $site_info['db_version'] ?? __( 'Unknown', 'site-ops-cockpit' ) ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'WP Memory Limit', 'site-ops-cockpit' ); ?></span>
                    <strong>
                        <?php echo esc_html( $site_info['wp_memory_limit'] ?? '' ); ?>
                        <span class="<?php echo $memory_is_ok ? 'soc-status-ok' : 'soc-status-warning'; ?>">
                            <?php echo $memory_is_ok ? esc_html__( 'OK', 'site-ops-cockpit' ) : esc_html__( 'Warning', 'site-ops-cockpit' ); ?>
                        </span>
                    </strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Max Execution Time', 'site-ops-cockpit' ); ?></span>
                    <strong>
                        <?php echo esc_html( isset( $site_info['max_execution_time'] ) ? (int) $site_info['max_execution_time'] : 0 ); ?>s
                        <span class="<?php echo $execution_is_ok ? 'soc-status-ok' : 'soc-status-warning'; ?>">
                            <?php echo $execution_is_ok ? esc_html__( 'OK', 'site-ops-cockpit' ) : esc_html__( 'Warning', 'site-ops-cockpit' ); ?>
                        </span>
                    </strong>
                </li>
            </ul>
            <?php if ( ! empty( $other_warnings ) ) : ?>
                <div class="soc-meta">
                    <strong><?php esc_html_e( 'Additional warnings:', 'site-ops-cockpit' ); ?></strong>
                    <ul>
                        <?php foreach ( $other_warnings as $warning ) : ?>
                            <li><?php echo esc_html( $warning ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="soc-card">
            <h2><?php esc_html_e( 'Environment & SSL', 'site-ops-cockpit' ); ?></h2>
            <ul class="soc-metrics-list">
                <li>
                    <span><?php esc_html_e( 'Site URL', 'site-ops-cockpit' ); ?></span>
                    <strong><a href="<?php echo esc_url( $env_info['site_url'] ?? '' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $env_info['site_url'] ?? '' ); ?></a></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Home URL', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $env_info['home_url'] ?? '' ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Environment', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( ucfirst( $env_info['environment_label'] ?? 'unknown' ) ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'SSL Status', 'site-ops-cockpit' ); ?></span>
                    <strong class="<?php echo ! empty( $env_info['ssl_enabled'] ) ? 'soc-status-ok' : 'soc-status-warning'; ?>">
                        <?php echo ! empty( $env_info['ssl_enabled'] ) ? esc_html__( 'Secure (HTTPS enabled)', 'site-ops-cockpit' ) : esc_html__( 'Not secure (no HTTPS)', 'site-ops-cockpit' ); ?>
                    </strong>
                </li>
            </ul>
        </div>

        <div class="soc-card">
            <h2><?php esc_html_e( 'Uptime Check', 'site-ops-cockpit' ); ?></h2>
            <ul class="soc-metrics-list">
                <li>
                    <span><?php esc_html_e( 'Check URL', 'site-ops-cockpit' ); ?></span>
                    <strong><a href="<?php echo esc_url( $uptime_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $uptime_url ); ?></a></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Status', 'site-ops-cockpit' ); ?></span>
                    <strong class="<?php echo ( 200 === (int) $status_code && empty( $error_text ) ) ? 'soc-status-ok' : 'soc-status-warning'; ?>">
                        <?php if ( ! empty( $error_text ) ) : ?>
                            <?php echo esc_html( $error_text ); ?>
                        <?php elseif ( null !== $status_code ) : ?>
                            <?php echo esc_html( $status_code ); ?>
                        <?php else : ?>
                            <?php esc_html_e( 'Unknown', 'site-ops-cockpit' ); ?>
                        <?php endif; ?>
                    </strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Response Time', 'site-ops-cockpit' ); ?></span>
                    <strong>
                        <?php
                        if ( null === $response_ms ) {
                            esc_html_e( 'N/A', 'site-ops-cockpit' );
                        } else {
                            echo esc_html( number_format_i18n( (float) $response_ms, 2 ) );
                            echo ' '; // output separator before unit.
                            esc_html_e( 'ms', 'site-ops-cockpit' );
                        }
                        ?>
                    </strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Last Checked', 'site-ops-cockpit' ); ?></span>
                    <strong>
                        <?php
                        if ( $last_check > 0 ) {
                            echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_check ) );
                        } else {
                            esc_html_e( 'Not checked yet', 'site-ops-cockpit' );
                        }
                        ?>
                    </strong>
                </li>
            </ul>
            <p class="soc-meta">
                <?php esc_html_e( 'Uptime results are cached for roughly one minute to avoid excessive requests.', 'site-ops-cockpit' ); ?>
            </p>
        </div>
    </div>
</div>
