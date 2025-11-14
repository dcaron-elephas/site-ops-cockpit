<?php
/**
 * Settings management.
 *
 * @package SiteOps\Cockpit
 */

namespace SiteOps\Cockpit;

/**
 * Class Settings
 */
class Settings {
    /**
     * Instance.
     *
     * @var Settings|null
     */
    protected static ?Settings $instance = null;

    /**
     * Option name.
     */
    public const OPTION_NAME = 'site_ops_cockpit_settings';

    /**
     * Default settings.
     */
    protected array $defaults = [
        'uptime_check_url' => '',
        'uptime_timeout'   => 5,
    ];

    /**
     * Get instance.
     */
    public static function instance(): Settings {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    protected function __construct() {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings(): void {
        register_setting(
            'site-ops-cockpit',
            self::OPTION_NAME,
            [ $this, 'sanitize_settings' ]
        );

        add_settings_section(
            'soc_general_section',
            __( 'General Settings', 'site-ops-cockpit' ),
            '__return_false',
            'site-ops-cockpit'
        );

        add_settings_field(
            'uptime_check_url',
            __( 'Uptime Check URL', 'site-ops-cockpit' ),
            [ $this, 'render_uptime_url_field' ],
            'site-ops-cockpit',
            'soc_general_section'
        );

        add_settings_field(
            'uptime_timeout',
            __( 'Uptime Timeout (seconds)', 'site-ops-cockpit' ),
            [ $this, 'render_uptime_timeout_field' ],
            'site-ops-cockpit',
            'soc_general_section'
        );
    }

    /**
     * Sanitize settings values.
     */
    public function sanitize_settings( $values ): array {
        if ( ! is_array( $values ) ) {
            $values = [];
        }

        $defaults = $this->get_default_settings();
        $values   = wp_parse_args( $values, $defaults );

        $values['uptime_check_url'] = esc_url_raw( $values['uptime_check_url'] );
        $values['uptime_timeout']   = max( 1, (int) $values['uptime_timeout'] );

        return $values;
    }

    /**
     * Render uptime URL field.
     */
    public function render_uptime_url_field(): void {
        $options = $this->get_settings();
        $value   = ! empty( $options['uptime_check_url'] ) ? $options['uptime_check_url'] : site_url();

        printf(
            '<input type="url" class="regular-text" name="%1$s[uptime_check_url]" value="%2$s" />',
            esc_attr( self::OPTION_NAME ),
            esc_attr( $value )
        );

        echo '<p class="description">' . esc_html__( 'URL to request for the uptime check.', 'site-ops-cockpit' ) . '</p>';
    }

    /**
     * Render uptime timeout field.
     */
    public function render_uptime_timeout_field(): void {
        $options = $this->get_settings();
        $value   = isset( $options['uptime_timeout'] ) ? (int) $options['uptime_timeout'] : 5;

        printf(
            '<input type="number" min="1" class="small-text" name="%1$s[uptime_timeout]" value="%2$d" />',
            esc_attr( self::OPTION_NAME ),
            $value
        );

        echo '<p class="description">' . esc_html__( 'Timeout in seconds for the uptime request.', 'site-ops-cockpit' ) . '</p>';
    }

    /**
     * Retrieve merged settings.
     */
    public function get_settings(): array {
        $stored   = (array) get_option( self::OPTION_NAME, [] );
        $defaults = $this->get_default_settings();

        if ( empty( $stored['uptime_check_url'] ) ) {
            $stored['uptime_check_url'] = site_url();
        }

        return wp_parse_args( $stored, $defaults );
    }

    /**
     * Get default settings.
     */
    protected function get_default_settings(): array {
        $defaults = $this->defaults;
        if ( empty( $defaults['uptime_check_url'] ) ) {
            $defaults['uptime_check_url'] = site_url();
        }

        return $defaults;
    }
}
