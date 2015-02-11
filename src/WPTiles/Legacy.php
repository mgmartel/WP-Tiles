<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Legacy
{

    public static function maybe_process_shortcode( $atts ) {
        $old_keys = array(
            'posts_query',
            'display',
            'colors',
            'template',
            'templates',
            'show_selector'
        );

        foreach( $old_keys as $old_key ) {
            if ( isset( $atts[$old_key] ) )
                return true;
        }

        return false;
    }

    public static function get_posts( $atts_array ) {
        return self::get_atts_array_query( $atts_array );
    }

    public static function get_options( $atts_array ) {
        return shortcode_atts( wp_tiles()->options->get_options(), self::convert_option_array( $atts_array ) );
    }

    public static function get_atts_array_query( $atts_array ) {
        $atts_array = self::parse_atts( $atts_array );
        $query = array();

        if ( isset( $atts_array['posts_query'] ) ) {
            $default_query = array (
                'offset'        => 0,
                'category'      => 0,
                'orderby'       => 'post_date',
                'order'         => 'DESC',
                'include'       => array(),
                'exclude'       => array(),
                'meta_key'      => '',
                'meta_value'    =>'',
                'post_type'     => 'post',
                'suppress_filters'
                                => true,
                'posts_per_page'
                                => 20,
                'page'          => 1,
                'tax_query'     => array(),
                'post_parent'   => 0,
                'post_mime_type' => ''
            );

            $query = shortcode_atts( $default_query, self::parse_atts( $atts_array['posts_query'] ) );
        }

        return $query;
    }

    public static function convert_option_array( $legacy, $save_templates = false ) {
        $legacy = self::parse_atts( $legacy );

        /**
         * DISPLAY
         */
        if ( isset( $legacy['display'] ) ) {

            $display =& $display;
            if ( isset( $display['text'] ) && 'hide' == $display['text'] )
                $options['byline_height'] = 0;

            $byline_templates = array(
                'nothing' => '',
                'cats' => '%categories%',
                'excerpt' => '%excerpt%',
                'date1' => '%date%',
                'date2' => '%date%', // Sorry, only 1 date method left
                'date3' => '%date%',
            );
            if ( isset( $display['byline'] ) && isset( $byline_templates[$display['byline']] ) )
                $options['byline_template'] = $byline_templates[$display['byline']];

            isset( $display['bylineBg'] ) && $options['byline_color'] = 'default' == $display['bylineBg'] ? '#000000' : '';
            isset( $display['cellPadding'] ) && $options['padding'] = $display['cellPadding'];

        }

        /**
         * COLORS
         */
        if ( isset( $legacy['colors'] ) && isset( $legacy['colors']['colors'] ) ) {
            for( $i = 0; $i < 5; $i++ ) {
                if ( isset( $legacy['colors']['colors'][$i] ) )
                    $options['color_' . $i] = $legacy['colors']['colors'][$i];
                else
                    $options['color_' . $i] = '';
            }
        }

        /**
         * TEMPLATES
         */
        if ( isset( $legacy['templates'] ) ) {
            $templates =& $legacy['templates'];

            if ( isset( $templates['show_selector'] ) && $templates['show_selector'] )
                $options['default_grid'] = 'all';

            isset( $templates['small_screen_width'] ) && $options['breakpoint'] = $templates['small_screen_width'];

            if ( $save_templates ) {

                if ( isset( $templates['templates'] ) ) {
                    foreach( $templates['templates']  as $title => $template ) {
                        if ( 'option_is_array' === $title )
                            continue;

                        wp_insert_post( array(
                            'post_type'    => WPTiles::GRID_POST_TYPE,
                            'post_status'  => 'publish',
                            'post_title'   => $title,
                            'post_content' => wp_kses_post( $template )
                        ) );
                    }
                }

                if ( isset( $templates['small_screen_template'] ) && !empty( $templates['small_screen_template'] ) ) {
                    $small_id = wp_insert_post( array(
                        'post_type' => WPTiles::GRID_POST_TYPE,
                        'post_status' => 'publish',
                        'post_tite' => 'Small Screen Grid',
                        'post_content' => wp_kses_post( $templates['small_screen_template'] )
                    ) );
                    $options['small_screen_grid'] = $small_id;
                }
            }
        }

        if ( isset( $legacy['template'] ) && $legacy['template'] ) {
            $options['grids'] = array( $legacy['template'] );
        }

        $options['pagination'] = false;
        return $options;
    }

    public static function convert_options() {
        $legacy = get_option( 'wp-tiles-options' );

        if ( $legacy ) {
            $options = wp_parse_args( self::convert_option_array( $legacy, true ), get_option( 'wp_tiles' ) );
            update_option( 'wp_tiles', $options );
        }

        // Just to make sure we are getting autoloaded, we'll delete and re-add the option
        delete_option( 'wp-tiles-options' );
        add_option( 'wp-tiles-options', 'legacy', '', 'yes' );

        // And also make sure our default grids are installed
        Admin\GridTemplates::install_default_templates();
    }


    private static function parse_atts( $atts ) {
        $atts_parsed = $atts;
        if ( ! is_array( $atts ) && strpos( $atts, '=' ) ) {
            $atts_parsed = array( );
            $atts = str_replace( array( '{', '}' ), array( '[', ']' ), html_entity_decode( $atts ) );
            wp_parse_str( $atts, $atts_parsed );
        }

        return $atts_parsed;
    }
}