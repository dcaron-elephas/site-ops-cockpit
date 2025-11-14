<?php
/**
 * Dashboard view template.
 *
 * @var array $data
 *
 * @package SiteOps\Cockpit
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$updates        = $data['updates'];
$site_health    = $data['site_health'];
$environment    = $data['environment'];
$uptime         = $data['uptime'];
$uptime_time    = isset( $uptime['timestamp'] ) ? (int) $uptime['timestamp'] : time();
$uptime_speed   = isset( $uptime['response'] ) ? (int) $uptime['response'] : 0;
$uptime_status  = isset( $uptime['status'] ) ? (int) $uptime['status'] : 0;
$uptime_error   = isset( $uptime['error'] ) ? $uptime['error'] : '';
$uptime_url     = isset( $uptime['url'] ) ? $uptime['url'] : site_url();
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Site Ops Cockpit', 'site-ops-cockpit' ); ?></h1>
    <p class="description"><?php esc_html_e( 'A quick overview of this site\'s operational readiness.', 'site-ops-cockpit' ); ?></p>

    <div class="soc-wrap">
        <div class="soc-card">
            <h2>
                <?php esc_html_e( 'Updates', 'site-ops-cockpit' ); ?>
                <span class="<?php echo $updates['core_update'] ? 'soc-status-warning' : 'soc-status-ok'; ?>">
                    <?php echo $updates['core_update'] ? esc_html__( 'Update Available', 'site-ops-cockpit' ) : esc_html__( 'Up to Date', 'site-ops-cockpit' ); ?>
                </span>
            </h2>
            <ul class="soc-metrics-list">
                <li>
                    <span><?php esc_html_e( 'WordPress Version', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $updates['current_version'] ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Plugin Updates', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $updates['plugins'] ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Theme Updates', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $updates['themes'] ); ?></strong>
                </li>
            </ul>
        </div>

        <div class="soc-card">
            <h2><?php esc_html_e( 'Site Health & Server', 'site-ops-cockpit' ); ?></h2>
            <ul class="soc-metrics-list">
                <li>
                    <span><?php esc_html_e( 'WordPress Version', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $site_health['wp_version'] ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'PHP Version', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $site_health['php_version'] ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Database Version', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $site_health['db_version'] ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'WP Memory Limit', 'site-ops-cockpit' ); ?></span>
                    <strong>
                        <?php echo esc_html( $site_health['memory_limit'] ); ?>
                        <span class="<?php echo $site_health['memory_status'] ? 'soc-status-ok' : 'soc-status-warning'; ?>">
                            <?php echo $site_health['memory_status'] ? esc_html__( 'OK', 'site-ops-cockpit' ) : esc_html__( 'Warning', 'site-ops-cockpit' ); ?>
                        </span>
                    </strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Max Execution Time', 'site-ops-cockpit' ); ?></span>
                    <strong>
                        <?php echo esc_html( $site_health['execution_time'] ); ?>s
                        <span class="<?php echo $site_health['execution_status'] ? 'soc-status-ok' : 'soc-status-warning'; ?>">
                            <?php echo $site_health['execution_status'] ? esc_html__( 'OK', 'site-ops-cockpit' ) : esc_html__( 'Warning', 'site-ops-cockpit' ); ?>
                        </span>
                    </strong>
                </li>
            </ul>
        </div>

        <div class="soc-card">
            <h2><?php esc_html_e( 'Environment & SSL', 'site-ops-cockpit' ); ?></h2>
            <ul class="soc-metrics-list">
                <li>
                    <span><?php esc_html_e( 'Site URL', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $environment['site_url'] ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Home URL', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $environment['home_url'] ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Environment', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( ucfirst( $environment['environment'] ) ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'SSL Status', 'site-ops-cockpit' ); ?></span>
                    <strong class="<?php echo $environment['is_ssl'] ? 'soc-status-ok' : 'soc-status-warning'; ?>">
                        <?php echo $environment['is_ssl'] ? esc_html__( 'Secure (HTTPS enabled)', 'site-ops-cockpit' ) : esc_html__( 'Not secure (no HTTPS)', 'site-ops-cockpit' ); ?>
                    </strong>
                </li>
            </ul>
        </div>

        <div class="soc-card">
            <h2><?php esc_html_e( 'Uptime Check', 'site-ops-cockpit' ); ?></h2>
            <ul class="soc-metrics-list">
                <li>
                    <span><?php esc_html_e( 'Check URL', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $uptime_url ); ?></strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Status', 'site-ops-cockpit' ); ?></span>
                    <strong class="<?php echo ( 200 === $uptime_status && empty( $uptime_error ) ) ? 'soc-status-ok' : 'soc-status-warning'; ?>">
                        <?php
                        if ( ! empty( $uptime_error ) ) {
                            echo esc_html( $uptime_error );
                        } elseif ( $uptime_status ) {
                            echo esc_html( $uptime_status );
                        } else {
                            esc_html_e( 'Unknown', 'site-ops-cockpit' );
                        }
                        ?>
                    </strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Response Time', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( $uptime_speed ); ?> ms</strong>
                </li>
                <li>
                    <span><?php esc_html_e( 'Last Checked', 'site-ops-cockpit' ); ?></span>
                    <strong><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $uptime_time ) ); ?></strong>
                </li>
            </ul>
            <p class="soc-meta">
                <?php esc_html_e( 'Uptime results are cached for five minutes to avoid excessive requests.', 'site-ops-cockpit' ); ?>
            </p>
        </div>
    </div>
</div>
