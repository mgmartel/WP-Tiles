<?php
/*
  Plugin Name: WP Tiles
  Plugin URI: http://trenvopress.com/
  Description: Add fully customizable dynamic tiles to your WordPress posts and pages.
  Version: 1.0-beta1
  Author: Mike Martel
  Author URI: http://trenvopress.com
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

if ( version_compare( phpversion(), '5.3', '<' ) ) {

    function wp_tiles_php53_dashboard_notice() {
        echo __( '<div class="error"><p>WP Tiles is <strong>not</strong> active. This version of the plugin requires PHP v5.3+.</p></div>', 'wp-tiles' );
    }

    add_action( 'all_admin_notices', 'wp_tiles_php53_dashboard_notice' );
    
} else {

    /**
     * Version number
     *
     * @since 0.1
     */
    define( 'WP_TILES_VERSION', '1.0-beta1' );

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

    /**
     * Requires and includes
     *
     * @since 1.0
     */
    if ( !defined( 'VP_VERSION' ) )
        require plugin_dir_path( __FILE__ ) .'vafpress-framework/bootstrap.php';

    require WP_TILES_DIR . 'vendor/autoload.php';

    register_activation_hook( __FILE__, array( 'WPTiles\WPTiles', 'on_plugin_activation' ) );

    if ( get_option( 'wp-tiles-options' ) ) {
        WPTiles\Legacy::convert_option();
    }

    /**
     * Get the one and only true instance of WP Tiles
     *
     * @return WPTiles\WPTiles
     * @since 0.4.2
     */
    function wp_tiles() {
        return \WPTiles\WPTiles::get_instance();
    }

    // Initialize
    wp_tiles();

    add_action( 'plugins_loaded', 'wptiles_load_pluggables' );
    function wptiles_load_pluggables() {
        require_once( WP_TILES_DIR . '/wp-tiles-pluggables.php' );
    }

    function wp_tiles_preview_tile() {
        return WPTiles\Admin\Admin::preview_tile();
    }

    // Add settings link
    $plugin = plugin_basename( __FILE__ );
    add_filter( "plugin_action_links_$plugin", function( $links ){
        $links[] = '<a href="admin.php?page=wp-tiles">' . __( 'Settings', 'wp-tiles' ) . '</a>';
        return $links;
    } );

}