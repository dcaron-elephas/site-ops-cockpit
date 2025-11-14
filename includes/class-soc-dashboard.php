<?php
/**
 * Dashboard page management.
 *
 * @package SiteOps\Cockpit
 */

namespace SiteOps\Cockpit;

/**
 * Class Dashboard
 */
class Dashboard {
    /**
     * Singleton instance.
     *
     * @var Dashboard|null
     */
    protected static ?Dashboard $instance = null;

    /**
     * Metrics handler.
     */
    protected Metrics $metrics;

    /**
     * Stored admin page hooks for asset loading.
     *
     * @var array<int, string>
     */
    protected array $page_hooks = [];

    /**
     * Get the instance.
     */
    public static function instance(): Dashboard {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    protected function __construct() {
        $this->metrics = Metrics::instance();
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Register admin menu items.
     */
    public function register_menu(): void {
        $dashboard_hook = add_submenu_page(
            'index.php',
            __( 'Site Ops Cockpit', 'site-ops-cockpit' ),
            __( 'Ops Cockpit', 'site-ops-cockpit' ),
            'manage_options',
            Plugin::SLUG,
            [ $this, 'render_dashboard_page' ]
        );

        $settings_hook = add_submenu_page(
            'index.php',
            __( 'Ops Settings', 'site-ops-cockpit' ),
            __( 'Ops Settings', 'site-ops-cockpit' ),
            'manage_options',
            Plugin::SLUG . '-settings',
            [ $this, 'render_settings_page' ]
        );

        $this->page_hooks = array_filter(
            [ $dashboard_hook, $settings_hook ],
            static fn ( $hook ) => is_string( $hook ) && ! empty( $hook )
        );
    }

    /**
     * Enqueue admin assets for cockpit screens.
     */
    public function enqueue_assets( string $hook ): void {
        if ( ! in_array( $hook, $this->page_hooks, true ) ) {
            return;
        }

        wp_enqueue_style(
            'soc-admin',
            SOC_URL . 'assets/css/admin.css',
            [],
            Plugin::VERSION
        );

        wp_enqueue_script(
            'soc-admin',
            SOC_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            Plugin::VERSION,
            true
        );
    }

    /**
     * Render the dashboard view.
     */
    public function render_dashboard_page(): void {
        $update_stats  = $this->metrics->get_update_stats();
        $site_info     = $this->metrics->get_site_info();
        $env_info      = $this->metrics->get_environment_info();
        $uptime_status = $this->metrics->get_uptime_status();

        $view_file = SOC_PATH . 'views/dashboard.php';

        if ( file_exists( $view_file ) ) {
            /** @var array $update_stats */
            /** @var array $site_info */
            /** @var array $env_info */
            /** @var array $uptime_status */
            include $view_file;
        }
    }

    /**
     * Render the settings view.
     */
    public function render_settings_page(): void {
        $settings = Settings::instance();
        $option   = $settings->get_settings();

        $view_file = SOC_PATH . 'views/settings.php';

        if ( file_exists( $view_file ) ) {
            /** @var array $option */
            include $view_file;
        }
    }
}
