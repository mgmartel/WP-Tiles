<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class PostQuery
{
    protected static $defaults = array(
        'author'               => '',
        'category'             => '',
        'id'                   => null,
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
        'exclude_current_post' => true,
        'related_in_taxonomy'  => null
    );

    /**
     * Returns plugin defaults for the posts query
     *
     * @param string|bool $key (optional) Return specific option
     * @return array|mixed
     */
    public function get_query_defaults( $key = false ) {
        if ( $key )
            return isset( self::$defaults[$key] ) ? self::$defaults[$key] : null;

        return self::$defaults;
    }

    /**
     * Returns specific query option, optional fallback to default
     *
     * @param string $key
     * @param bool (optional) $default Return default if option does not exist
     * @return mixed Option value
     */
    public function get_query_option( $key, $default = true ) {
        $option = $this->get_option( $key, false );
        if ( $default && is_null( $option ) )
            $option = $this->get_query_defaults( $key );

        return $option;
    }
}