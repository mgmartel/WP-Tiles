<?php namespace WPTiles\Abstracts;

 // Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

abstract class WPSingleton extends HookHelper
{

    private static $instance = array( );

    protected function __construct() {

    }

    public static function get_instance() {
        $c = get_called_class();
        if ( !isset( self::$instance[$c] ) ) {
            self::$instance[$c] = new $c();
            self::$instance[$c]->init_parent();
        }

        return self::$instance[$c];
    }

    public function init_parent() {
        $this->init();
    }
    protected function init() {}

    public static function init_on( $hook ) {
        if ( did_action( $hook  ) || $hook === current_filter() )
            self::get_instance();
        else
            \add_action( $hook, array( get_called_class(), 'get_instance' ) );
    }

}
