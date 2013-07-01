<?php

/**
 * Contains pluggable template functions
 *
 * @since 0.5.1
 */
if ( !function_exists( 'the_wp_tiles' ) ) :

    /**
     * Show the WP Tiles. Use as template tag.
     *
     * @param string|array $atts
     * @since 0.3.3
     */
    function the_wp_tiles( $atts = array( ) ) {

        // Allow $atts to be just the post_query as a string or object
        $atts = wp_tiles()->parse_post_query_string( $atts );

        // Backward compatibility - this is going out! Use the_category_wp_tiles instead
        if ( ( is_category() || is_single() ) && !isset( $atts['posts_query']['category'] ) ) {
            $categories = get_the_category();
            $cats       = array( );
            foreach ( $categories as $category ) {
                $cats[] = $category->term_id;
            }

            $atts['posts_query']['category'] = implode( ', ', $cats );
        }

        wp_tiles()->show_tiles( $atts );
    }

endif;

if ( !function_exists( 'the_category_wp_tiles' ) ) :

    /**
     * Show the WP Tiles for the current category
     *
     * @since 0.4.2
     */
    function the_category_wp_tiles( $atts ) {
        $atts = wp_tiles()->parse_post_query_string( $atts );

        // If is single and no cat is given, use posts from current categories
        if ( !is_category() && !is_single() )
            _doing_it_wrong( 'the_wp_tiles', "Only use the_category_wp_tiles on category pages or single posts/pages", '0.4.2' );
        else if ( isset( $atts['posts_query']['category'] ) && !empty( $atts['posts_query']['category'] ) ) {
            _doing_it_wrong( 'the_wp_tiles', "Don't pass a category into the_category_wp_tiles(), use the_wp_tiles() instead.", '0.4.2' );
        } else {
            $categories = get_the_category();
            $cats       = array( );
            foreach ( $categories as $category ) {
                $cats[] = $category->term_id;
            }

            $atts['posts_query']['category'] = implode( ', ', $cats );
        }

        wp_tiles()->show_tiles( $atts );
    }

endif;

if ( !function_exists( 'the_loop_wp_tiles' ) ) :

    /**
     * Show the posts in the current query.
     *
     * Can be used to replace the loop.
     *
     * @since 0.4.2
     */
    function the_loop_wp_tiles() {

        global $wp_query;
        $posts = $wp_query->get_posts();

        wp_tiles()->show_tiles( $posts );
    }


endif;