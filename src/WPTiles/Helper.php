<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Helper
{
    public static function colors_to_rgba( $colors ) {
        $rgba = array();
        foreach( $colors as $c ) {
            if ( strpos( $c, 'rgba' ) === 0 )
                $rgba[] = $c;
            if ( strpos( $c, 'rgb' ) === 0 )
                $rgba[] = Helper::rgb_to_rgba( $c, 1, true );
            elseif( strpos( $c, '#' ) === 0 )
                $rgba[] = Helper::hex_to_rgba( $c, 1, true );
        }

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

    public static function hex_to_rgba( $hex, $alpha, $css = false ) {
        $rgba      = self::hex_to_rgb( $hex );
        $rgba['a'] = $alpha;
        if ( !$css )
            return $rgba;

        return "rgba( {$rgba['r']},{$rgba['g']},{$rgba['b']},{$rgba['a']} )";
    }
}