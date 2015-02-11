<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

/**
 * Parses the shortcode for WP Tiles
 *
 * Parses the Tiles arguments in a tile array and sets a post query that is
 * largely based on Display Posts Shortcode by Bill Erickson
 * @since 1.0
 */
class Shortcode
{
    public static function do_shortcode( $atts ) {

        // Legacy
        if ( Legacy::maybe_process_shortcode( $atts ) ) {
            $posts   = Legacy::get_posts( $atts );
            $options = Legacy::get_options( $atts );
        } else {
            $posts   = self::get_posts( $atts );
            $options = self::get_options( $atts );
        }

        return wp_tiles()->get_tiles( $posts, $options );
    }

    public static function get_options( $original_atts ) {
        $defaults = wp_tiles()->options->get_options();

        $atts = shortcode_atts( array(
            'grids'  => $defaults['grids'],
            'grid'   => false, // Pass grid manually
            'colors' => $defaults['colors'],

            'background_opacity' => $defaults['background_opacity'],
            'grid_selector_color' => $defaults['grid_selector_color'],

            'breakpoint'        => $defaults['breakpoint'],
            'small_screen_grid' => $defaults['small_screen_grid'],

            'byline_template' => $defaults['byline_template'],
            'byline_template_textonly' => $defaults['byline_template_textonly'],

            'byline_opacity'  => $defaults['byline_opacity'],
            'byline_color'    => $defaults['byline_color'],
            'byline_height'   => $defaults['byline_height'],
            'byline_height_auto' => $defaults['byline_height_auto'],
            'byline_align'    => $defaults['byline_align'],
            'byline_effect'   => $defaults['byline_effect'],
            'image_effect'    => $defaults['image_effect'],

            'text_color'       => $defaults['text_color'],
            'image_text_color' => $defaults['image_text_color'],

            'link'            => $defaults['link'],
            'link_new_window' => $defaults['link_new_window'],

            'text_only'   => $defaults['text_only'],
            'images_only' => $defaults['images_only'],
            'hide_title'  => $defaults['hide_title'],

            'image_size'   => $defaults['image_size'],
            'image_source' => $defaults['image_source'],

            'padding' => $defaults['padding'],

            'animated'         => true,
            'animate_init'     => $defaults['animate_init'],
            'animate_resize'   => $defaults['animate_resize'],
            'animate_template' => $defaults['animate_template'],

            'pagination'       => $defaults['pagination'],

            'extra_classes'    => $defaults['extra_classes'],
            'extra_classes_grid_selector'
                               => $defaults['extra_classes_grid_selector'],

            'full_width'       => $defaults['full_width']

        ), $original_atts );

        if ( $atts['grid'] ) {
            $atts['grids'] = array(
                'Custom' => wp_tiles()->format_grid( $atts['grid'] )
            );
        }

        // Maybe convert full grid strings into grids so they are not interpreted as names
        if ( $atts['small_screen_grid'] && self::_is_grid_string( $atts['small_screen_grid'] ) ) {
            $atts['small_screen_grid'] = array(
                'Custom' => wp_tiles()->format_grid( $atts['small_screen_grid'] )
            );
        }

        $grid_names = self::_get_options_array( $atts['grids'] );

        $options = array(
            'grids' => $grid_names, // Will be converted into grid templates in get_tiles

            'small_screen_grid' => $atts['small_screen_grid'],
            'breakpoint' => (int) $atts['breakpoint'],

            'colors' => self::_get_colors( $atts['colors'] ),
            'background_opacity' => (float) $atts['background_opacity'],
            'grid_selector_color' => $atts['grid_selector_color'],

            'byline_template'          => $atts['byline_template'],
            'byline_template_textonly' => $atts['byline_template_textonly'],

            'byline_opacity'  => (float) $atts['byline_opacity'],
            'byline_color'    => $atts['byline_color'],
            'byline_height'   => (int) $atts['byline_height'],
            'byline_height_auto' => wp_tiles()->options->boolean( $atts['byline_height_auto'] ),
            'byline_align'    => $atts['byline_align'],
            'byline_effect'   => $atts['byline_effect'],
            'image_effect'    => $atts['image_effect'],

            'text_color'       => $atts['text_color'],
            'image_text_color' => $atts['image_text_color'],

            'link'            => $atts['link'],
            'link_new_window' => $atts['link_new_window'],

            'text_only'    => wp_tiles()->options->boolean( $atts['text_only'] ),
            'images_only'  => wp_tiles()->options->boolean( $atts['images_only'] ),
            'hide_title'   => wp_tiles()->options->boolean( $atts['hide_title'] ),

            'image_source' => $atts['image_source'],
            'image_size'   => $atts['image_size'],

            'padding' => (int) $atts['padding'],

            'extra_classes'    => self::_get_options_array( $atts['extra_classes'] ),

            'extra_classes_grid_selector'
                               => self::_get_options_array( $atts['extra_classes_grid_selector'] ),

            'animate_init'     => ( $atts['animated'] && $atts['animate_init'] ),
            'animate_resize'   => ( $atts['animated'] && $atts['animate_resize'] ),
            'animate_template' => ( $atts['animated'] && $atts['animate_template'] ),

            'pagination'       => $atts['pagination'],

            'full_width'       => $atts['full_width']
        );

        if ( $atts['breakpoint'] ) {
            $options['small_screen_grid'] = $atts['small_screen_grid'];
            $options['breakpoint'] = (int) $atts['breakpoint'];
        }

        return $options;

    }

        private static function _is_grid_string( $string ) {
            return is_string( $string ) && ( strpos( $string, '|' ) !== false || strpos( $string, "\n" ) !== false );
        }

        private static function _get_colors( $colors ) {
            $colors = self::_get_options_array( $colors );

            $rgba = Helper::colors_to_rgba( $colors );

            return $rgba;
        }

        private static function _get_options_array( $array ) {
            $options = ( !is_array( $array ) ) ? explode( ',', $array ) : $array;
            return ( is_string( $options ) ) ? array_map( 'trim', $options ) : $options;
        }

    public static function get_posts( $original_atts ) {

        // Pull in shortcode attributes and set defaults
        $atts = shortcode_atts( array(
            'author'               => '',
            'category'             => '',
            'id'                   => false,
            'exclude'              => false,
            'ignore_sticky_posts'  => false,
            'meta_key'             => '',
            'offset'               => 0,
            'order'                => 'DESC',
            'orderby'              => 'date',
            'post_parent'          => false,
            'post_status'          => 'publish',
            'post_type'            => 'post',

            'posts_per_page'       => 'auto',
            'paged'                => 1,
            'tag'                  => '',
            'tax_operator'         => 'IN',
            'tax_term'             => false,
            'taxonomy'             => false,

            // Contextual args, only supported in the loop:
            'exclude_current_post' => true,
            'related_in_taxonomy'  => false
        ), $original_atts );

        $author = sanitize_text_field( $atts['author'] );
        $category = sanitize_text_field( $atts['category'] );
        $id      = $atts['id']; // Sanitized later as an array of integers
        $exclude = $atts['exclude']; // Sanitized later as an array of integers
        $ignore_sticky_posts = (bool) $atts['ignore_sticky_posts'];
        $meta_key = sanitize_text_field( $atts['meta_key'] );
        $offset = intval( $atts['offset'] );
        $order = sanitize_key( $atts['order'] );
        $orderby = sanitize_key( $atts['orderby'] );
        $post_parent = $atts['post_parent']; // Validated later, after check for 'current'
        $post_status = $atts['post_status']; // Validated later as one of a few values
        $post_type =  sanitize_text_field( $atts['post_type'] );
        $posts_per_page = 'auto' == $atts['posts_per_page'] ? 'auto' : intval( $atts['posts_per_page'] );
        $tag = sanitize_text_field( $atts['tag'] );
        $tax_operator = $atts['tax_operator']; // Validated later as one of a few values
        $tax_term = sanitize_text_field( $atts['tax_term'] );
        $taxonomy = sanitize_key( $atts['taxonomy'] );

        if ( 'current' === $post_type )
            $post_type = in_the_loop() ? get_post_type() : 'any';

        // Set up initial query for post
        $args = array(
            'category_name'       => $category,
            'order'               => $order,
            'orderby'             => $orderby,
            'post_type'           => explode( ',', $post_type ),
            'posts_per_page'      => $posts_per_page,
            'tag'                 => $tag,
        );

        // Set paged to 'paged' to use pagination parameters
        if ( 'paged' === $atts['paged'] )
            $args['paged'] = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
        else
            $args['paged'] = intval( $atts['paged'] );

        // Ignore Sticky Posts
        if( $ignore_sticky_posts )
            $args['ignore_sticky_posts'] = true;

        // Meta key (for ordering)
        if( !empty( $meta_key ) )
            $args['meta_key'] = $meta_key;

        // If Post IDs
        if( $id ) {
            $post_in = array_map( 'intval', explode( ',', $id ) );

            // Exclude wins from include
            if ( $exclude ) {
                $post_not_in = array_map( 'intval', explode( ',', $exclude ) );
                $post_in = array_diff( $post_in, $post_not_in );
            }

            $args['post__in'] = $post_in;

            if ( !isset( $original_atts['post_type'] ) || !$original_atts['post_type'] ) {
                $args['post_type'] = 'any';
            }

            if ( !isset( $original_atts['orderby'] ) || !$original_atts['orderby'] ) {
                $args['orderby'] = 'post__in';
            }

        // Only process exclude if there is no include
        } elseif ( $exclude ) {
            $args['post__not_in'] = array_map( 'intval', explode( ',', $exclude ) );

        }


        // Post Author
        if( !empty( $author ) )
            $args['author_name'] = $author;

        // Offset
        if( !empty( $offset ) )
            $args['offset'] = $offset;

        // Post Status
        $post_status = explode( ', ', $post_status );
        $validated = array();
        $available = array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash', 'any' );
        foreach ( $post_status as $unvalidated ) {
            if ( in_array( $unvalidated, $available ) )
                $validated[] = $unvalidated;
        }
        if( !empty( $validated ) )
            $args['post_status'] = $validated;


        // If taxonomy attributes, create a taxonomy query
        if ( !empty( $taxonomy ) && !empty( $tax_term ) ) {

            // Term string to array
            $tax_term = explode( ', ', $tax_term );

            // Validate operator
            if( !in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) )
                $tax_operator = 'IN';

            $tax_args = array(
                'tax_query' => array(
                    array(
                        'taxonomy' => $taxonomy,
                        'field'    => 'slug',
                        'terms'    => $tax_term,
                        'operator' => $tax_operator
                    )
                )
            );

            // Check for multiple taxonomy queries
            $count = 2;
            $more_tax_queries = false;
            while(
                isset( $original_atts['taxonomy_' . $count] ) && !empty( $original_atts['taxonomy_' . $count] ) &&
                isset( $original_atts['tax_' . $count . '_term'] ) && !empty( $original_atts['tax_' . $count . '_term'] )
            ):

                // Sanitize values
                $more_tax_queries = true;
                $taxonomy = sanitize_key( $original_atts['taxonomy_' . $count] );
                $terms = explode( ', ', sanitize_text_field( $original_atts['tax_' . $count . '_term'] ) );
                $tax_operator = isset( $original_atts['tax_' . $count . '_operator'] ) ? $original_atts['tax_' . $count . '_operator'] : 'IN';
                $tax_operator = in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) ? $tax_operator : 'IN';

                $tax_args['tax_query'][] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $terms,
                    'operator' => $tax_operator
                );

                $count++;

            endwhile;

            if( $more_tax_queries ):
                $tax_relation = 'AND';
                if( isset( $original_atts['tax_relation'] ) && in_array( $original_atts['tax_relation'], array( 'AND', 'OR' ) ) )
                    $tax_relation = $original_atts['tax_relation'];
                $args['tax_query']['relation'] = $tax_relation;
            endif;

            $args = array_merge( $args, $tax_args );
        }

        // Contextual queries
        if ( in_the_loop() ) {

            // Only exclude if post IDs are not explicitly given
            // (post__not_in and post__in at the same time is not supported by WP_Query)
            if ( $atts['exclude_current_post'] && !$id ) {

                if ( !isset( $args['post__not_in'] ) || !is_array( $args['post__not_in'] ) )
                    $args['post__not_in'] = array();

                $args['post__not_in'][] = get_the_ID();
            }

            if ( !empty( $atts['related_in_taxonomy'] ) ) {

                $taxonomy = sanitize_key( $atts['related_in_taxonomy'] );
                $terms = get_the_terms( get_post(), $taxonomy );

                if ( is_array( $terms ) ) {

                    // Maybe set up the tax query still
                    if ( !isset( $args['tax_query'] ) ) {
                        $args['tax_query'] = array();

                    // Or the tax relation
                    } elseif ( !isset( $args['tax_query']['tax_relation'] ) ) {
                        $args['tax_query']['tax_relation'] = 'AND';

                    }

                    $args['tax_query'][] = array(
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => wp_list_pluck( $terms, 'term_id' ),
                        'operator' => 'IN'
                    );

                }

                // Also set the post type if not explicitly given
                if ( !isset( $original_atts['post_type'] ) || !$original_atts['post_type'] ) {
                    $args['post_type'] = in_the_loop() ? get_post_type() : 'any';
                }

            }

        }


        // If post parent attribute, set up parent
        if( $post_parent ) {
            if( 'current' == $post_parent ) {
                global $post;
                $post_parent = $post->ID;
            }
            $args['post_parent'] = intval( $post_parent );
        }

        return apply_filters( 'wp_tiles_shortcode_post_query', $args, $original_atts );
    }
}