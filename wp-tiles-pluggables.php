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

if ( ! function_exists( 'wp_tiles_paging_nav' ) ) :
    /**
     * Display navigation to next/previous set of posts with page numbers when applicable.
     *
     * Based on TwentyFourteen 1.0
     * @since 1.0
     */
    function wp_tiles_paging_nav( $wp_query = false, $anchor = false ) {

        if ( !$wp_query )
            $wp_query = $GLOBALS['wp_query'];

        // Don't print empty markup if there's only one page.
        if ( $wp_query->max_num_pages < 2 ) {
            return;
        }

        $paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
        $pagenum_link = html_entity_decode( get_pagenum_link() );
        $query_args   = array();
        $url_parts    = explode( '?', $pagenum_link );

        if ( isset( $url_parts[1] ) ) {
            wp_parse_str( $url_parts[1], $query_args );
        }

        $pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
        $pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

        if ( $anchor )
            $pagenum_link .= '#' . $anchor;

        $format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
        $format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

        // Set up paginated links.
        $links = paginate_links( array(
            'base'     => $pagenum_link,
            'format'   => $format,
            'total'    => $wp_query->max_num_pages,
            'current'  => $paged,
            'mid_size' => 1,
            'add_args' => array_map( 'urlencode', $query_args ),
            'prev_text' => __( '&larr; Previous', 'wp-tiles' ),
            'next_text' => __( 'Next &rarr;', 'wp-tiles' ),
        ) );

        if ( $links ) :

        ?>
        <nav class="navigation wp-tiles-pagination wp-tiles-pagination-paging paging-navigation" role="navigation">
            <h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'wp-tiles' ); ?></h1>
            <div class="pagination loop-pagination">
                <?php echo $links; ?>
            </div><!-- .pagination -->
        </nav><!-- .navigation -->
        <?php
        endif;
    }
endif;

if ( ! function_exists( 'wp_tiles_prev_next_nav' ) ) :
    /**
     * Display navigation to next/previous post when applicable.
     *
     * Based on TwentyFourteen 1.0
     * @since 1.0
     */
    function wp_tiles_prev_next_nav( $wp_query = false, $anchor = false ) {

        if ( !$wp_query )
            $wp_query = $GLOBALS['wp_query'];

        $next = $previous = false;
        $paged = $wp_query->get( 'paged', 1 );

        // Previous
        if ( $paged > 1 )
            $previous = true;

        // Next
        $max_page = $wp_query->max_num_pages;

        $nextpage = intval($paged) + 1;

        if ( $nextpage <= $max_page )
            $next = true;

        // Don't print empty markup if there's nowhere to navigate.
        if ( ! $next && ! $previous )
            return;

        ?>
        <nav class="navigation wp-tiles-pagination wp-tiles-pagination-prev-next" role="navigation">
            <h1 class="screen-reader-text"><?php _e( 'Post navigation', 'twentyfourteen' ); ?></h1>
            <div class="pagination loop-pagination">
                <?php if ( $previous ) : ?><a href='<?php echo previous_posts(false); ?><?php if ( $anchor ) echo '#' . $anchor; ?>' class='prev prev-next'><?php _e( '&larr; Previous', 'wp-tiles' ) ?></a><?php endif; ?>
                <?php if ( $next ) : ?><a href='<?php echo next_posts( $max_page, false ); ?><?php if ( $anchor ) echo '#' . $anchor; ?>' class='next prev-next'><?php _e( 'Next &rarr;', 'wp-tiles' ) ?><?php endif; ?>
            </div><!-- .pagination -->
        </nav><!-- .navigation -->
        <?php
    }
endif;