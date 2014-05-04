<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Options
{
    static $defaults = array(
        'grids' => false,
        'default_grid' => false,
        'small_screen_grid' => false,
        'breakpoint' => 800,
        'template_selector_color' => '#444444',

        'colors' => array(
            "#009999",
            "#1D7373",
            "#006363",
            "#33CCCC",
            "#5CCCCC",
        ),
        'background_opacity' => 1,
        'padding' => 10,

        'byline_template' => "%categories%",
        'byline_template_textonly' => false,
        'byline_opacity'  => '0.8',
        'byline_color'    => '#fff',
        'byline_height'   => 40,

        'text_only'    => false,
        'link'         => 'post', //post|thickbox|attachment|none
        'images_only'  => false,
        'hide_title'   => false,

        'animate_init'     => true,
        'animate_resize'   => true,
        'animate_template' => true,

        'image_size'       => 'medium',
        'image_source'     => 'all',

        'byline_effect' => 'none',
        'byline_align'  => 'bottom',
        'image_effect'  => 'none',

        'pagination' => 'ajax',

        'legacy_styles' => false,
    );

    /**
     * Return the plugin default settings
     *
     * @return array
     */
    public function get_option_defaults( $key = false ) {
        if ( $key )
            return isset( self::$defaults[$key] ) ? self::$defaults[$key] : null;

        return self::$defaults;
    }

    public function get_options() {
        static $options = false;

        if ( !$options ) {

            $options = array();
            $defaults = $this->get_option_defaults();

            foreach( $defaults as $option => $default ) {
                $value = $this->get_option( $option );
                $options[$option] = is_null( $value ) ? $default : $value;
            }

            // @todo Cache results?
            $options['grids'] = wp_tiles()->get_grids( $options['default_grid'] );

            $small_screen_grids = wp_tiles()->get_grids( $options['small_screen_grid'] );
            $options['small_screen_grid'] = end( $small_screen_grids );

            $colors = array();
            for ( $i = 1; $i <= 5; $i++ ) {
                $color = $this->get_option( 'color_' . $i );
                if ( $color )
                    $colors[] = $color;
            }

            $options['colors'] = Helper::colors_to_rgba( $colors );

            if ( empty( $options['byline_color'] ) )
                $options['byline_color'] = 'random';

            $options['byline_color'] = $this->get_byline_color( $options );

            if ( !$this->get_option( 'byline_for_text_only' ) )
                $options['byline_template_textonly'] = false;

            // Disable individual animations when disabled globally
            if ( !$this->get_option( 'animated' ) ) {
                foreach( array( 'animate_init', 'animate_resize', 'animate_template' ) as $a ) {
                    $options[$a] = false;
                }
            }

        }

        return $options;

    }

    public function get_option( $name, $get_default = true ) {
        $option = vp_option( "wp_tiles." . $name );

        if ( $get_default && is_null( $option ) )
            $option = $this->get_option_defaults( $name );

        return apply_filters( 'wp_tiles_option_' . $name, $option, $get_default );
    }


    //
    // UTILS
    //

    public function get_byline_color( $opts_or_byline_color, $byline_opacity = false ) {
        if ( is_array( $opts_or_byline_color ) ) {
            $byline_opacity = $opts_or_byline_color['byline_opacity'];
            $byline_color   = $opts_or_byline_color['byline_color'];
        } else {
            $byline_color   = $opts_or_byline_color;

        }

        if ( !$byline_color || empty( $byline_color ) || 'random' === $byline_color )
            return 'random';

        return Helper::color_to_rgba( $byline_color, $byline_opacity, true );
    }

    public function get_colors( $opts_or_colors, $background_opacity = false ) {
        if ( is_array( $opts_or_colors ) ) {
            $background_opacity = $opts_or_colors['background_opacity'];
            $colors = $opts_or_colors['colors'];
        } else {
            $colors = $opts_or_colors;

        }

        return Helper::colors_to_rgba( $colors, $background_opacity );
    }

    public function get_allowed_byline_effects() {
        return array( 'slide-up', 'slide-down', 'slide-left', 'slide-right', 'fade-in' );
    }

    public function get_allowed_image_effects() {
        return array( 'scale-up', 'scale-down', 'saturate', 'desaturate' );
    }

    //
    // UTILS
    //

    public function boolean( $value ) {
        if ( in_array( $value, array( true, 'true', 'yes', '1', 1 ), true ) )
            return true;

        return false;
    }
}