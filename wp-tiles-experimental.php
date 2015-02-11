<?php
// Experimental Features - include into your theme manually

/**
 * Use WP Tiles instead of header image - tested on TwentyTwelve
 */
add_filter( 'theme_mod_header_image', function(){
    wp_tiles()->display_tiles( array(
        'posts_per_page' => 9,
        'post_type' => 'page'
    ), array(
        'padding' => 0,
        'grids' => array( 'Mixed' ),
        'pagination' => false
    ) );
    return 'remove-header';
});