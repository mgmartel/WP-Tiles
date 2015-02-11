<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Helper
{
    public static function colors_to_rgba( $colors, $alpha = 1 ) {
        $rgba = array();
        foreach( $colors as $c ) {
            $rgba[] = self::color_to_rgba( $c, $alpha, true );
        }

        return $rgba;
    }

    public static function color_to_rgba( $color, $alpha = 1, $css = false ) {
        if ( strpos( $color, 'rgba' ) === 0 )
            $rgba = self::rgba_opacity( $color, $alpha, $css );
        if ( strpos( $color, 'rgb' ) === 0 )
            $rgba = self::rgb_to_rgba( $color, $alpha, $css );
        elseif( strpos( $color, '#' ) === 0 )
            $rgba = self::hex_to_rgba( $color, $alpha, $css );
        else
            $rgba = 'rgba(0,0,0,0)';

        return $rgba;
    }

    public static function hex_to_rgb( $hex ) {
        $hex   = str_replace( "#", "", $hex );
        $color = array( 'r' => 0, 'g' => 0, 'b' => 0 );

        if ( strlen( $hex ) == 3 ) {
            $color['r'] = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
            $color['g'] = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
            $color['b'] = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
        } else if ( strlen( $hex ) == 6 ) {
            $color['r'] = hexdec( substr( $hex, 0, 2 ) );
            $color['g'] = hexdec( substr( $hex, 2, 2 ) );
            $color['b'] = hexdec( substr( $hex, 4, 2 ) );
        }

        return $color;
    }

    public static function rgb_to_rgba( $rgb, $alpha = 1, $css = false ) {
        $matches = array();
        preg_match_all( '/[0-9]{1,3}/', $rgb, $matches );
        $rgba = array_combine( array( 'r', 'g', 'b' ), array_slice( reset( $matches ), 0, 3 ) );

        $rgba['a'] = $alpha;
        if ( !$css )
            return $rgba;

        return "rgba( {$rgba['r']},{$rgba['g']},{$rgba['b']},{$rgba['a']} )";
    }

    public static function rgba_opacity( $rgba, $alpha = 1, $css = false ) {
        return self::rgb_to_rgba( $rgba, $alpha, $css );
    }

    public static function hex_to_rgba( $hex, $alpha, $css = false ) {
        $rgba      = self::hex_to_rgb( $hex );
        $rgba['a'] = $alpha;
        if ( !$css )
            return $rgba;

        return "rgba( {$rgba['r']},{$rgba['g']},{$rgba['b']},{$rgba['a']} )";
    }


    /**
    * Allow $atts to be just the post_query as a string or object
    *
    * @param string|array $qs
    * @return array Properly formatted $atts
    * @since 0.4.2
    */
    public static function parse_query( $qs ) {
        if ( is_string( $qs ) ) {
            $query = array();
            wp_parse_str( $qs, $query );
        } else {
            $query = $qs;
        }

        return $query;
    }

}