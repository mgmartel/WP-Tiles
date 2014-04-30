<?php
// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

/**
 * Parses the shortcode for WP Tiles
 *
 * Largely based on Display Posts Shortcode by ....
 * @since 1.0
 */
class WP_Tiles_Shortcode
{
    public static function get_options( $original_atts ) {

        $atts = shortcode_atts( array(
            'grids' => '',
            'grid'  => false,

            'colors' => array (
                "#009999",
                "#1D7373",
                "#006363",
                "#33CCCC",
                "#5CCCCC",
            ),
            'color' => false,

            'breakpoint'        => false,
            'small_screen_grid' => false,

            'byline_template' => '%categories%',
            'byline_opacity'  => '1',

            'text_only' => false,
            'link_to_post' => true,

            'padding' => 10,

        ), $original_atts );

        $grid_names = self::_get_options_array( $atts['grids'],  $atts['grid'] );
        $grids = wp_tiles()->get_grids( $grid_names );

        $options = array(
            'grids' => $grids,

            'small_screen_grid' => '',
            'small_screen_breakpoint' => false,

            'colors' => self::_get_options_array( $atts['colors'], $atts['color'] ),

            'byline_template' => $atts['byline_template'],
            'byline_opacity' => $atts['byline_opacity'],

            'text_only'    => self::_boolean( $atts['text_only'] ),
            'link_to_post' => self::_boolean( $atts['link_to_post'] ),

            'padding' => $atts['padding']
        );

        if ( $atts['breakpoint'] ) {
            $options['small_screen_grid'] = $atts['small_screen_grid'];
            $options['breakpoint'] = $atts['breakpoint'];
        }

        return $options;

    }

        private static function _get_options_array( $plural, $singular = false ) {
            if ( $singular )
                return array( $singular );

            $options = ( !is_array( $plural ) ) ? explode( ',', $plural ) : $plural;
            return array_map( 'trim', $options );
        }

        private static function _boolean( $value ) {
            if ( true === $value || 'yes' === $value )
                return true;

            return false;
        }


    public static function get_posts( $original_atts ) {

        // Pull in shortcode attributes and set defaults
        $atts = shortcode_atts( array(
            'author'              => '',
            'category'            => '',
            //'date_format'         => '(n/j/Y)',
            'id'                  => false,
            'ignore_sticky_posts' => false,
            //'image_size'          => false,
            //'include_content'     => false,
            //'include_date'        => false,
            //'include_excerpt'     => false,
            'meta_key'            => '',
            //'no_posts_message'    => '',
            'offset'              => 0,
            'order'               => 'DESC',
            'orderby'             => 'date',
            'post_parent'         => false,
            'post_status'         => 'publish',
            'post_type'           => 'post',
            'posts_per_page'      => '10',
            'tag'                 => '',
            'tax_operator'        => 'IN',
            'tax_term'            => false,
            'taxonomy'            => false,
            //'wrapper'             => 'ul',
        ), $original_atts );

        $author = sanitize_text_field( $atts['author'] );
        $category = sanitize_text_field( $atts['category'] );
        //$date_format = sanitize_text_field( $atts['date_format'] );
        $id = $atts['id']; // Sanitized later as an array of integers
        $ignore_sticky_posts = (bool) $atts['ignore_sticky_posts'];
        //$image_size = sanitize_key( $atts['image_size'] );
        //$include_content = (bool)$atts['include_content'];
        //$include_date = (bool)$atts['include_date'];
        //$include_excerpt = (bool)$atts['include_excerpt'];
        $meta_key = sanitize_text_field( $atts['meta_key'] );
        //$no_posts_message = sanitize_text_field( $atts['no_posts_message'] );
        $offset = intval( $atts['offset'] );
        $order = sanitize_key( $atts['order'] );
        $orderby = sanitize_key( $atts['orderby'] );
        $post_parent = $atts['post_parent']; // Validated later, after check for 'current'
        $post_status = $atts['post_status']; // Validated later as one of a few values
        $post_type = sanitize_text_field( $atts['post_type'] );
        $posts_per_page = intval( $atts['posts_per_page'] );
        $tag = sanitize_text_field( $atts['tag'] );
        $tax_operator = $atts['tax_operator']; // Validated later as one of a few values
        $tax_term = sanitize_text_field( $atts['tax_term'] );
        $taxonomy = sanitize_key( $atts['taxonomy'] );
        //$wrapper = sanitize_text_field( $atts['wrapper'] );

        // Set up initial query for post
        $args = array(
            'category_name'       => $category,
            'order'               => $order,
            'orderby'             => $orderby,
            'post_type'           => explode( ',', $post_type ),
            'posts_per_page'      => $posts_per_page,
            'tag'                 => $tag,
        );

        // Ignore Sticky Posts
        if( $ignore_sticky_posts )
            $args['ignore_sticky_posts'] = true;

        // Meta key (for ordering)
        if( !empty( $meta_key ) )
            $args['meta_key'] = $meta_key;

        // If Post IDs
        if( $id ) {
            $posts_in = array_map( 'intval', explode( ',', $id ) );
            $args['post__in'] = $posts_in;
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

        // If post parent attribute, set up parent
        if( $post_parent ) {
            if( 'current' == $post_parent ) {
                global $post;
                $post_parent = $post->ID;
            }
            $args['post_parent'] = intval( $post_parent );
        }

        return get_posts( apply_filters( 'wp_tiles_shortcode_args', $args, $original_atts ) );
        //return new WP_Query( apply_filters( 'wp_tiles_shortcode_args', $args, $original_atts ) );
    }
}