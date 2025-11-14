<?php
/**
 * Plugin Name:       Site Ops Cockpit
 * Plugin URI:        https://example.com/
 * Description:       Provides a high-level operational dashboard for WordPress sites.
 * Version:           1.0.0
 * Author:            Site Ops Cockpit
 * Author URI:        https://example.com/
 * Text Domain:       site-ops-cockpit
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 */

// Abort if this file is called directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Autoload plugin classes.
spl_autoload_register(
    function ( $class ) {
        $prefix   = 'SiteOps\\Cockpit\\';
        $base_dir = __DIR__ . '/includes/';

        $len = strlen( $prefix );
        if ( 0 !== strncmp( $prefix, $class, $len ) ) {
            return;
        }

        $relative_class = substr( $class, $len );
        $relative_class = strtolower( str_replace( '\\', '-', $relative_class ) );

        $file = $base_dir . 'class-soc-' . $relative_class . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
);

// Bootstrap the plugin.
SiteOps\Cockpit\Plugin::instance();
