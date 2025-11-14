<?php
/**
 * Metrics collection.
 *
 * @package SiteOps\Cockpit
 */

namespace SiteOps\Cockpit;

use WP_Error;

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
    protected const TRANSIENT_KEY = 'site_ops_cockpit_uptime_status';

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
    public function get_update_stats(): array {
        require_once ABSPATH . 'wp-admin/includes/update.php';

        // Determine the current WordPress version.
        global $wp_version;
        $current_version = $wp_version ?? (string) get_bloginfo( 'version' );

        $core_update_available = false;
        $preferred_update      = function_exists( 'get_preferred_from_update_core' ) ? get_preferred_from_update_core() : null;

        if ( is_object( $preferred_update ) && isset( $preferred_update->response ) ) {
            $target_version = $preferred_update->current ?? $preferred_update->version ?? '';
            if ( 'upgrade' === $preferred_update->response && $target_version && version_compare( $target_version, $current_version, '>' ) ) {
                $core_update_available = true;
            }
        } else {
            $core_updates = function_exists( 'get_core_updates' ) ? get_core_updates() : [];
            if ( is_array( $core_updates ) ) {
                foreach ( $core_updates as $update ) {
                    if ( isset( $update->response ) && 'upgrade' === $update->response ) {
                        $core_update_available = true;
                        break;
                    }
                }
            }
        }

        $plugin_transient = get_site_transient( 'update_plugins' );
        $plugin_count     = 0;
        if ( is_object( $plugin_transient ) && ! empty( $plugin_transient->response ) && is_array( $plugin_transient->response ) ) {
            $plugin_count = count( $plugin_transient->response );
        }

        $theme_transient = get_site_transient( 'update_themes' );
        $theme_count     = 0;
        if ( is_object( $theme_transient ) && ! empty( $theme_transient->response ) && is_array( $theme_transient->response ) ) {
            $theme_count = count( $theme_transient->response );
        }

        return [
            'current_wp_version'     => (string) $current_version,
            'core_update_available'  => $core_update_available,
            'plugin_updates_count'   => (int) $plugin_count,
            'theme_updates_count'    => (int) $theme_count,
        ];
    }

    /**
     * Fetch site health related information.
     */
    public function get_site_info(): array {
        global $wpdb;

        $wp_version  = (string) get_bloginfo( 'version' );
        $php_version = PHP_VERSION;

        // Capture database server version when available.
        $db_version = '';
        if ( isset( $wpdb ) ) {
            if ( method_exists( $wpdb, 'db_version' ) ) {
                $db_version = (string) $wpdb->db_version();
            } elseif ( isset( $wpdb->db_server_info ) ) {
                $db_version = (string) $wpdb->db_server_info;
            }
        }

        $memory_setting = defined( 'WP_MEMORY_LIMIT' ) && WP_MEMORY_LIMIT ? WP_MEMORY_LIMIT : ini_get( 'memory_limit' );
        $memory_bytes   = function_exists( 'wp_convert_hr_to_bytes' ) ? wp_convert_hr_to_bytes( $memory_setting ) : (int) $memory_setting;

        $max_execution_time = (int) ini_get( 'max_execution_time' );

        $warnings = [];
        if ( $memory_bytes > 0 && $memory_bytes < 268435456 ) { // 256M in bytes.
            $warnings[] = __( 'Memory limit below 256M', 'site-ops-cockpit' );
        }

        if ( $max_execution_time > 0 && $max_execution_time < 60 ) {
            $warnings[] = __( 'Max execution time below 60 seconds', 'site-ops-cockpit' );
        }

        return [
            'wp_version'         => $wp_version,
            'php_version'        => $php_version,
            'db_version'         => $db_version,
            'wp_memory_limit'    => (string) $memory_setting,
            'max_execution_time' => $max_execution_time,
            'warnings'           => $warnings,
        ];
    }

    /**
     * Fetch environment information.
     */
    public function get_environment_info(): array {
        $site_url = site_url();
        $home_url = home_url();

        $environment_label = $this->determine_environment_label( $site_url );

        return [
            'site_url'          => $site_url,
            'home_url'          => $home_url,
            'environment_label' => $environment_label,
            'ssl_enabled'       => is_ssl(),
        ];
    }

    /**
     * Fetch uptime status.
     */
    public function get_uptime_status(): array {
        $settings = (array) get_option( 'site_ops_cockpit_settings', [] );

        $uptime_url = isset( $settings['uptime_check_url'] ) && $settings['uptime_check_url']
            ? esc_url_raw( $settings['uptime_check_url'] )
            : site_url();

        if ( ! $uptime_url ) {
            $uptime_url = site_url();
        }

        $timeout = isset( $settings['uptime_timeout'] ) ? (int) $settings['uptime_timeout'] : 5;
        if ( $timeout <= 0 ) {
            $timeout = 5;
        }

        $cache = get_transient( self::TRANSIENT_KEY );
        if ( is_array( $cache ) && isset( $cache['uptime_check_url'], $cache['uptime_timeout'], $cache['last_checked'] ) ) {
            // Only reuse the cached value when the settings match and cache is fresh (<= 60 seconds old).
            if ( $cache['uptime_check_url'] === $uptime_url && (int) $cache['uptime_timeout'] === $timeout && ( time() - (int) $cache['last_checked'] ) < MINUTE_IN_SECONDS ) {
                return [
                    'status_code'      => $cache['status_code'] ?? null,
                    'response_time_ms' => $cache['response_time_ms'] ?? null,
                    'last_checked'     => (int) $cache['last_checked'],
                    'error_message'    => $cache['error_message'] ?? null,
                ];
            }
        }

        $start_time = microtime( true );
        $response   = wp_remote_get(
            $uptime_url,
            [
                'timeout' => max( 1, $timeout ),
            ]
        );
        $duration_ms = ( microtime( true ) - $start_time ) * 1000;

        $status_code      = null;
        $response_time_ms = round( $duration_ms, 2 );
        $error_message    = null;

        if ( is_wp_error( $response ) ) {
            /** @var WP_Error $response */
            $error_message    = $response->get_error_message();
            $response_time_ms = null;
        } else {
            $status_code = (int) wp_remote_retrieve_response_code( $response );
        }

        $result = [
            'status_code'      => $status_code,
            'response_time_ms' => $response_time_ms,
            'last_checked'     => time(),
            'error_message'    => $error_message,
            'uptime_check_url' => $uptime_url,
            'uptime_timeout'   => $timeout,
        ];

        set_transient( self::TRANSIENT_KEY, $result, MINUTE_IN_SECONDS );

        return [
            'status_code'      => $result['status_code'],
            'response_time_ms' => $result['response_time_ms'],
            'last_checked'     => $result['last_checked'],
            'error_message'    => $result['error_message'],
        ];
    }

    /**
     * Backwards compatible alias for legacy consumers.
     */
    public function get_updates_data(): array {
        return $this->get_update_stats();
    }

    /**
     * Backwards compatible alias for legacy consumers.
     */
    public function get_site_health_data(): array {
        return $this->get_site_info();
    }

    /**
     * Backwards compatible alias for legacy consumers.
     */
    public function get_environment_data(): array {
        return $this->get_environment_info();
    }

    /**
     * Backwards compatible alias for legacy consumers.
     */
    public function get_uptime_data(): array {
        return $this->get_uptime_status();
    }

    /**
     * Determine the environment label for the provided URL.
     */
    protected function determine_environment_label( string $url ): string {
        $constant_value = null;

        if ( defined( 'WP_ENV' ) && WP_ENV ) {
            $constant_value = WP_ENV;
        } elseif ( defined( 'SITE_ENV' ) && SITE_ENV ) {
            $constant_value = SITE_ENV;
        }

        if ( null !== $constant_value ) {
            $normalized = strtolower( (string) $constant_value );
            if ( in_array( $normalized, [ 'production', 'prod', 'live' ], true ) ) {
                return 'production';
            }

            if ( in_array( $normalized, [ 'staging', 'stage', 'test' ], true ) ) {
                return 'staging';
            }

            if ( in_array( $normalized, [ 'development', 'develop', 'dev', 'local' ], true ) ) {
                return 'development';
            }

            return 'unknown';
        }

        $host = wp_parse_url( $url ?: home_url(), PHP_URL_HOST );
        if ( ! $host ) {
            return 'production';
        }

        $host = strtolower( (string) $host );

        if ( false !== strpos( $host, 'dev' ) || false !== strpos( $host, 'local' ) ) {
            return 'development';
        }

        if ( false !== strpos( $host, 'stage' ) || false !== strpos( $host, 'staging' ) ) {
            return 'staging';
        }

        return 'production';
    }
}
