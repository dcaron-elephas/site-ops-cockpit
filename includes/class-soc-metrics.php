<?php
/**
 * Metrics collection.
 *
 * @package SiteOps\Cockpit
 */

namespace SiteOps\Cockpit;

/**
 * Class Metrics
 */
class Metrics {
    /**
     * Instance.
     *
     * @var Metrics|null
     */
    protected static ?Metrics $instance = null;

    /**
     * Uptime transient key.
     */
    protected const TRANSIENT_KEY = 'soc_uptime_status';

    /**
     * Get instance.
     */
    public static function instance(): Metrics {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Fetch update metrics.
     */
    public function get_updates_data(): array {
        require_once ABSPATH . 'wp-admin/includes/update.php';

        $core_updates   = get_core_updates();
        $plugins_updates = get_plugin_updates();
        $themes_updates  = get_theme_updates();

        global $wp_version;
        $core_available = false;

        if ( is_array( $core_updates ) ) {
            foreach ( $core_updates as $update ) {
                if ( isset( $update->response ) && 'upgrade' === $update->response ) {
                    $core_available = true;
                    break;
                }
            }
        }

        return [
            'current_version' => $wp_version,
            'core_update'     => $core_available,
            'plugins'         => is_array( $plugins_updates ) ? count( $plugins_updates ) : 0,
            'themes'          => is_array( $themes_updates ) ? count( $themes_updates ) : 0,
        ];
    }

    /**
     * Fetch site health related data.
     */
    public function get_site_health_data(): array {
        global $wpdb;

        $php_version = PHP_VERSION;
        $db_version  = $wpdb->db_version();
        $memory_limit = defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : ini_get( 'memory_limit' );
        $memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
        $execution_time = (int) ini_get( 'max_execution_time' );

        return [
            'wp_version'     => get_bloginfo( 'version' ),
            'php_version'    => $php_version,
            'db_version'     => $db_version,
            'memory_limit'   => $memory_limit,
            'memory_status'  => ( $memory_bytes >= 268435456 ), // 256M
            'execution_time' => $execution_time,
            'execution_status' => ( 0 === $execution_time || $execution_time >= 120 ),
        ];
    }

    /**
     * Fetch environment data.
     */
    public function get_environment_data(): array {
        $site_url = site_url();
        $home_url = home_url();
        $environment = $this->detect_environment( $site_url );

        return [
            'site_url'   => $site_url,
            'home_url'   => $home_url,
            'environment'=> $environment,
            'is_ssl'     => is_ssl(),
        ];
    }

    /**
     * Fetch uptime data.
     */
    public function get_uptime_data(): array {
        $settings   = Settings::instance()->get_settings();
        $url        = ! empty( $settings['uptime_check_url'] ) ? esc_url_raw( $settings['uptime_check_url'] ) : site_url();
        $timeout    = isset( $settings['uptime_timeout'] ) ? (int) $settings['uptime_timeout'] : 5;
        $transient  = get_transient( self::TRANSIENT_KEY );

        $defaults = [
            'url'       => $url,
            'status'    => null,
            'error'     => null,
            'response'  => 0,
            'timestamp' => time(),
        ];

        if ( is_array( $transient ) && isset( $transient['timestamp'] ) && ( time() - $transient['timestamp'] ) < 300 ) {
            $transient['url'] = $url;

            return wp_parse_args( $transient, $defaults );
        }

        $start_time = microtime( true );
        $response   = wp_remote_get(
            $url,
            [
                'timeout' => max( 1, $timeout ),
            ]
        );
        $duration   = ( microtime( true ) - $start_time ) * 1000;

        $status_code = null;
        $error       = null;

        if ( is_wp_error( $response ) ) {
            $error = $response->get_error_message();
        } else {
            $status_code = wp_remote_retrieve_response_code( $response );
        }

        $result = [
            'url'        => $url,
            'status'     => $status_code,
            'error'      => $error,
            'response'   => max( 0, (int) round( $duration ) ),
            'timestamp'  => time(),
        ];

        set_transient( self::TRANSIENT_KEY, wp_parse_args( $result, $defaults ), 5 * MINUTE_IN_SECONDS );

        return $result;
    }

    /**
     * Detect environment value.
     */
    protected function detect_environment( string $url ): string {
        if ( defined( 'WP_ENV' ) && WP_ENV ) {
            return WP_ENV;
        }

        if ( defined( 'SITE_ENV' ) && SITE_ENV ) {
            return SITE_ENV;
        }

        $host = wp_parse_url( $url, PHP_URL_HOST );
        if ( ! $host ) {
            return 'production';
        }

        $host_lower = strtolower( $host );
        $patterns   = [ 'dev', 'stage', 'staging', 'local' ];

        foreach ( $patterns as $pattern ) {
            if ( false !== strpos( $host_lower, $pattern ) ) {
                return $pattern;
            }
        }

        return 'production';
    }
}
