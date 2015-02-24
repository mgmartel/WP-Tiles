<?php
/*
  Plugin Name: WP Tiles
  Plugin URI: http://wp-tiles.com/
  Description: Add fully customizable dynamic tiles to your WordPress posts and pages.
  Version: 1.0.1
  Author: Mike Martel
  Author URI: http://trenvo.com/
  Requires at least: 3.6
  Tested up to: 4.1.1
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

    require plugin_dir_path( __FILE__ ) . 'wp-tiles-loader.php';

    $plugin_file = plugin_basename( __FILE__ );

    // Activation
    register_activation_hook( __FILE__, array( 'WPTiles\WPTiles', 'on_plugin_activation' ) );

    // Add settings link
    add_filter( "plugin_action_links_$plugin_file", 'wp_tiles_plugin_action_links' );
    function wp_tiles_plugin_action_links( $links ) {
        $links[] = '<a href="admin.php?page=wp-tiles">' . __( 'Settings', 'wp-tiles' ) . '</a>';
        return $links;
    }


}
