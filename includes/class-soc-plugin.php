<?php
/**
 * Main plugin bootstrap class.
 *
 * @package SiteOps\Cockpit
 */

namespace SiteOps\Cockpit;

use SiteOps\Cockpit\Dashboard;
use SiteOps\Cockpit\Metrics;
use SiteOps\Cockpit\Settings;

/**
 * Class Plugin
 */
class Plugin {
    /**
     * Plugin instance.
     *
     * @var Plugin|null
     */
    protected static ?Plugin $instance = null;

    /**
     * Plugin slug.
     */
    public const SLUG = 'site-ops-cockpit';

    /**
     * Plugin version.
     */
    public const VERSION = '1.0.0';

    /**
     * Plugin constructor.
     */
    protected function __construct() {
        $this->define_constants();
        $this->init_hooks();
    }

    /**
     * Get the singleton instance.
     */
    public static function instance(): Plugin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Define plugin constants.
     */
    protected function define_constants(): void {
        $plugin_file = dirname( __DIR__ ) . '/site-ops-cockpit.php';

        if ( ! defined( 'SOC_PATH' ) ) {
            define( 'SOC_PATH', plugin_dir_path( $plugin_file ) );
        }

        if ( ! defined( 'SOC_URL' ) ) {
            define( 'SOC_URL', plugin_dir_url( $plugin_file ) );
        }
    }

    /**
     * Initialise core hooks.
     */
    protected function init_hooks(): void {
        add_action( 'plugins_loaded', [ $this, 'load_plugin_components' ] );
    }

    /**
     * Load plugin components.
     */
    public function load_plugin_components(): void {
        Dashboard::instance();
        Metrics::instance();
        Settings::instance();
    }

}
