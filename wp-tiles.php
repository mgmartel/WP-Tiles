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