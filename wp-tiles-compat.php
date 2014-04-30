<?php
class wp_tiles_get_compat_options
{
    public $options;

    public function __get( $name ) {
        if ( isset( $this->options[$name] ) )
            return $this->options[$name];

        return null;
    }

    public function __construct() {
        global $wptiles_defaults;
        require_once ( WPTILES_DIR . '/wp-tiles-defaults.php');

        $wptiles_options = get_option( 'wp-tiles-options' );
        $this->options   = $this->shortcode_atts_rec( $wptiles_defaults, $wptiles_options );
    }

    protected function shortcode_atts_rec( $options, $atts ) {
        if ( is_array( $atts ) ) {
            // Don't continue if the option itself is an array
            if ( isset( $options['option_is_array'] ) ) {
                $option_is_array = $options['option_is_array'];
                unset( $options['option_is_array'] );

                if ( $option_is_array ) {
                    if ( 'inclusive' == $option_is_array )
                        $atts = array_merge( $atts, $options );

                    return $atts;
                }
            }

            foreach ( $atts as $k => &$att ) {
                if ( is_array( $att ) && array_keys( $atts ) !== range( 0, count( $atts ) - 1 ) // Make sure array is associative
                ) {
                    $att = $this->shortcode_atts_rec( $options[$k], $att );
                } elseif ( ! is_array( $att ) && strpos( $att, '=' ) ) {
                    $atts_parsed = array( );
                    $att = str_replace( array( '{', '}' ), array( '[', ']' ), html_entity_decode( $att ) );
                    wp_parse_str( $att, $atts_parsed );
                    if ( !empty( $atts_parsed ) )
                        $att         = $atts_parsed;
                    if ( is_array( $options[$k] ) )
                        $att         = shortcode_atts( $options[$k], $att );
                }
            }
        }
        return shortcode_atts( $options, $atts );
    }
}