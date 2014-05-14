<?php
/*
  Plugin Name: WP Tiles
  Plugin URI: http://trenvopress.com/
  Description: Add fully customizable dynamic tiles to your WordPress posts and pages.
  Version: 0.5.9
  Author: Mike Martel
  Author URI: http://trenvopress.com
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * Version number
 *
 * @since 0.1
 */
define( 'WPTILES_VERSION', '0.5.9' );

/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define( 'WPTILES_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPTILES_URL', plugin_dir_url( __FILE__ ) );
define( 'WPTILES_TEMPLATES_DIR', WPTILES_DIR . 'templates/' );
define( 'WPTILES_TEMPLATES_URL', WPTILES_URL . 'templates/' );
define( 'WPTILES_INC_URL', WPTILES_URL . '_inc/' );

/**
 * Requires and includes
 *
 * @since 0.1
 */
require_once ( WPTILES_DIR . '/wp-tiles.class.php' );
if ( is_admin() )
    require_once ( WPTILES_DIR . '/wp-tiles-admin.php' );

add_action( 'plugins_loaded', 'wptiles_load_pluggables' );
function wptiles_load_pluggables() {
    require_once( WPTILES_DIR . '/wp-tiles-pluggables.php' );
}

function wptiles_upgrade_notice() {

    $url = esc_attr( get_bloginfo( 'url' ) );
    $email = esc_attr( wp_get_current_user()->user_email );

    return <<<HTML
    <div id="message" class="updated">
        <h3>Announcing WP Tiles 1.0</h3>
<p>We have <strong>completely reworked</strong> WP Tiles using the feedback we have received since the first version.<br />
    This will be the <strong>last</strong> version of WP Tiles before major version update.</p>

<p><strong><a href='#' target='_blank'>Find more information and the public beta here &rarr;</a></strong></p>

<p><strong>Staying up to date</strong><br />
To stay up to date with the development of WP Tiles, please sign up to our newsletter here:</p>

        <!-- Begin MailChimp Signup Form -->
        <div id="mc_embed_signup">
            <form action="http://trenvo.us2.list-manage.com/subscribe/post?u=999667a0c77440ad37655daa6&amp;id=7b9dc606b6" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                <div class="mc-field-group">
                    <input type="email" value="$email" name="EMAIL" class="required email" id="mce-EMAIL" placeholder="Email Address">
                    <input type="hidden" value="$url" name="SITE" class=" url" id="mce-SITE">
                    <input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button-primary">
                </div>

                <div id="mce-responses" class="clear">
                    <div class="response" id="mce-error-response" style="display:none"></div>
                    <div class="response" id="mce-success-response" style="display:none"></div>
                </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                <div style="position: absolute; left: -5000px;"><input type="text" name="b_999667a0c77440ad37655daa6_7b9dc606b6" tabindex="-1" value=""></div>
            </form>
        </div>
        <!--End mc_embed_signup-->

        <p>Thank you for using WP Tiles!</p>

        <ul class='subsubsub'>
            <li><strong>Also see:</strong> </li>
            <li><a href='#' target='_blank'>wp-tiles.com</a> | </li>
            <li><a href='#' target='_blank'>WP Tiles on WordPress.org</a> | </li>
            <li><a href='#' target='_blank'>Download WP Tiles 1.0-Beta</a></li>
        </ul>

        <div class='clear'></div>

   </div>
HTML;
}