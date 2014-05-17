<?php
/*
  Plugin Name: WP Tiles
  Plugin URI: http://wp-tiles.com/
  Description: Add fully customizable dynamic tiles to your WordPress posts and pages.
  Version: 0.6.1
  Author: Mike Martel
  Author URI: http://trenvo.com
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * Version number
 *
 * @since 0.1
 */
define( 'WPTILES_VERSION', '0.6.1' );

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

//
// PREPRARE FOR WP TILES 1.0
//

/**
 * @since 0.6
 */
function wptiles_upgrade_notice() {

    $url = esc_attr( get_bloginfo( 'url' ) );
    $email = esc_attr( wp_get_current_user()->user_email );

    return <<<HTML
    <div id="message" class="updated">
        <h3>WP Tiles 1.0 is coming!</h3>

        <p>We have <strong>completely reworked</strong> WP Tiles using the feedback we have received since the first version.<br />
        You are now using the <strong>last</strong> version of WP Tiles before the major version update.</p>

        <p><strong><a href='http://wp-tiles.com/blog/announcing-wp-tiles-1-0/' target='_blank'>Find more information and try out WP Tiles 1.0 Beta &rarr;</a></strong></p>

        <p><strong>Staying up to date</strong><br />
        To stay up to date with the development of WP Tiles, please sign up to our newsletter below:</p>

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
            <li><a href='http://wp-tiles.com/' target='_blank'>wp-tiles.com</a> | </li>
            <li><a href='http://wordpress.org/support/plugin/wp-tiles' target='_blank'>Plugin support on WordPress.org</a> | </li>
            <li><a href='http://wp-tiles.com/blog/announcing-wp-tiles-1-0/' target='_blank'>Download WP Tiles 1.0-Beta</a></li>
        </ul>

        <div class='clear'></div>

   </div>
HTML;
}

/**
 * Display upgrade notice in plugin list. Adapted from WooCommerce
 *
 * @since 0.6
 */
$plugin = plugin_basename( __FILE__ );
add_action( "in_plugin_update_message-$plugin", "wp_tiles_plugin_update_message" );

function wp_tiles_plugin_update_message( $args ) {
    $transient_name = 'wp-tiles_upgrade_notice_' . $args['Version'];

    if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {

        $response = wp_remote_get( 'https://plugins.svn.wordpress.org/wp-tiles/trunk/readme.txt' );

        if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {

            // Output Upgrade Notice
            $matches        = null;
            $regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( WPTILES_VERSION ) . '\s*=|$)~Uis';
            $upgrade_notice = '';
            $version_matches = null;

            if ( preg_match( $regexp, $response['body'], $matches ) ) {
                $version        = trim( $matches[1] );
                $notices        = (array) preg_split('~[\r\n]+~', trim( $matches[2] ) );

                if ( version_compare( WPTILES_VERSION, $version, '<' ) ) {

                    $upgrade_notice .= '<div class="wp-tiles_plugin_upgrade_notice">';

                    foreach ( $notices as $index => $line ) {
                        if ( preg_match( '~=\s*([0-9.]+)\s*=~Uis', $line, $version_matches ) )
                            break;

                        $upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) ) . '<br />';
                    }

                    if ( version_compare( 1.0, $version, '>=' ) && version_compare(phpversion(), '5.3', '<') ) {
                        $upgrade_notice .= "<strong>Important!</strong> It seems that you are using PHP 5.2. WP Tiles 1.0+ will no longer be compatible with versions of PHP lower than 5.3.";
                    }

                    $upgrade_notice .= '</div> ';
                }
            }

            set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
        }
    }

    echo wp_kses_post( $upgrade_notice );
    ?>
    <style>
    .wp-tiles_plugin_upgrade_notice {
        font-weight: normal;
        color: #fff;
        background: #d54d21;
        padding: 1em;
        margin: 9px 0;
    }

    .wp-tiles_plugin_upgrade_notice a {
        color: #fff;
        text-decoration: underline;
    }

    .wp-tiles_plugin_upgrade_notice:before {
        content: "\f348";
        display: inline-block;
        font: 400 18px/1 dashicons;
        speak: none;
        margin: 0 8px 0 -2px;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        vertical-align: top;
    }
    </style>
    <?php
}