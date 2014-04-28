<?php
$tmpl_opt = array(
    'title' => 'WP Tiles Title',
    'logo' => '',
    'menus' => array(
        array(
            'title' => 'General Options',
            'name'  => 'General',
            'icon'  => '', // fontawesome:
            'controls' => array(

            )
        )
    )
);


$options = new VP_Option( array(
    'is_dev_mode'           => WP_TILES_DEBUG, // dev mode, default to false
    'option_key'            => 'wp_tiles_options', // options key in db, required
    'page_slug'             => 'wp-tiles', // options page slug, required
    'template'              => $tmpl_opt, // template file path or array, required
    'menu_page'             => array(), // parent menu slug or supply `array` (can contains 'icon_url' & 'position') for top level menu
    'use_auto_group_naming' => true, // default to true
    'use_util_menu'         => true, // default to true, shows utility menu
    'minimum_role'          => 'manage_options', // default to 'edit_theme_options'
    'layout'                => 'fixed', // fluid or fixed, default to fixed
    'page_title'            => __( 'WP Tiles', 'wp-tiles' ), // page title
    'menu_label'            => __( 'WP Tiles', 'wp-tiles' ), // menu label
) );
