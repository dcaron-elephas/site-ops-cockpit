<?php
/**
 * Settings view template.
 *
 * @var array $option
 *
 * @package SiteOps\Cockpit
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Site Ops Cockpit Settings', 'site-ops-cockpit' ); ?></h1>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'site-ops-cockpit' );
        do_settings_sections( 'site-ops-cockpit' );
        submit_button();
        ?>
    </form>
</div>
