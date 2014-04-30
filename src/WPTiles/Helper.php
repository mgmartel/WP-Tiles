<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Helper
{
    public static function hex_to_rgb( $hex ) {
        $hex   = str_replace( "#", "", $hex );
        $color = array( );

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

    public static function rgba_to_rgba( $rgb, $alpha = 1, $css = false ) {
        $rgba = array( 'r' => '', 'g' => '', 'b' => '' );
        preg_match_all( '[0-9]{3}', $rgb, $rgba );

        $rgba['a'] = $alpha;
        if ( !$css )
            return $rgba;

        return "rgba( {$rgba['r']},{$rgba['g']},{$rgba['b']},{$rgba['a']} )";
    }

    public static function hex_to_rgba( $hex, $alpha, $css = false ) {
        $rgba      = self::hex_to_rgb( $hex );
        $rgba['a'] = $alpha;
        if ( !$css )
            return $rgba;

        return "rgba( {$rgba['r']},{$rgba['g']},{$rgba['b']},{$rgba['a']} )";
    }
}