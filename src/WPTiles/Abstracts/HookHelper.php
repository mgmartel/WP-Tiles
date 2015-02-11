<?php namespace WPTiles\Abstracts;

 // Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * This should be a trait for Singleton. But because we're targetting PHP5.3,
 * this is the best we can do, without killing performance with mixin helpers...
 */
abstract class HookHelper
{

    public function add_filter( $tag, $method_to_add, $priority = 10, $accepted_args = 1 ) {
        if ( is_array( $tag ) ) {
            foreach( $tag as $t ) {
                $this->add_filter( $t, $method_to_add, $priority, $accepted_args );
            }
            return true;
        }

        return \add_filter( $tag, array( &$this, $method_to_add ), $priority, $accepted_args );
    }

        public function add_action( $tag, $method_to_add, $priority = 10, $accepted_args = 1 ) {
            return $this->add_filter( $tag, $method_to_add, $priority, $accepted_args );
        }

    public function remove_filter( $tag, $method_to_remove, $priority = 10 ) {
        \remove_filter( $tag, array( &$this, $method_to_remove ), $priority );
    }

        public function remove_action( $tag, $method_to_remove, $priority = 10 ) {
            $this->remove_filter( $tag, array( &$this, $method_to_remove ), $priority );
        }

    public function hook( $tag_and_method, $priority = 10, $accepted_args = 1 ) {
        if ( is_array( $tag_and_method ) ) {
            foreach( $tag_and_method as $t ) {
                $this->hook( $t, $priority, $accepted_args );
            }
            return;
        }

        return $this->add_filter( $tag_and_method, $tag_and_method, $priority, $accepted_args );
    }

        public function unhook( $tag_and_method, $priority = 10 ) {
            return $this->remove_filter( $tag_and_method, $tag_and_method, $priority );
        }

}