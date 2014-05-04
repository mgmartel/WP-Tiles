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
define( 'WP_TILES_VERSION', '0.5.9' );

/**
 * PATHs and URLs
 *
 * @since 0.1
 */

define( 'WP_TILES_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_TILES_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_TILES_TEMPLATES_DIR', WP_TILES_DIR . 'templates/' );
define( 'WP_TILES_TEMPLATES_URL', WP_TILES_URL . 'templates/' );
define( 'WP_TILES_ASSETS_URL', WP_TILES_URL . 'assets/' );

// Compat
define( 'WPTILES_DIR', WP_TILES_DIR );
define( 'WPTILES_URL', WP_TILES_URL );
define( 'WPTILES_TEMPLATES_DIR', WP_TILES_TEMPLATES_DIR );
define( 'WPTILES_TEMPLATES_URL', WP_TILES_TEMPLATES_URL );
define( 'WPTILES_INC_URL', WP_TILES_URL . '_inc/' );

defined( 'WP_TILES_DEBUG' ) or define( 'WP_TILES_DEBUG', false );

/**
 * Requires and includes
 *
 * @since 0.1
 */
if ( !defined( 'VP_VERSION' ) )
    require plugin_dir_path( __FILE__ ) .'../vafpress-framework/bootstrap.php';

require WP_TILES_DIR . 'vendor/autoload.php';

/**
 * Get the one and only true instance of WP Tiles
 *
 * @return WPTiles\WPTiles
 * @since 0.4.2
 */
function wp_tiles() {
    return \WPTiles\WPTiles::get_instance();
}
wp_tiles();

/*
require_once ( WPTILES_DIR . '/wp-tiles-admin.php' );
require_once ( WPTILES_DIR . '/wp-tiles-admin-legacy.php' );

require_once ( WPTILES_DIR . '/lib/Shortcode.php' );
require_once ( WPTILES_DIR . '/lib/GridTemplates.php' );

require_once ( WPTILES_DIR . '/wp-tiles.class.php' );
WP_Tiles_GridTemplates::get_instance();*/

add_action( 'plugins_loaded', 'wptiles_load_pluggables' );
function wptiles_load_pluggables() {
    require_once( WP_TILES_DIR . '/wp-tiles-pluggables.php' );
}

// @todo include this somewhere else
function wp_tiles_preview_tile() {
    return WPTiles\Admin\Admin::preview_tile();
}
