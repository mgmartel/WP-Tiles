<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Ajax
{
    const ACTION_GET_POSTS = 'wp-tiles-get-posts';

    public function __construct() {
        add_action( 'wp_ajax_nopriv_' . self::ACTION_GET_POSTS, array( &$this, 'get_posts' ) );
        add_action( 'wp_ajax_' . self::ACTION_GET_POSTS, array( &$this, 'get_posts' ) );
    }

    public function get_posts() {
        $query = $_POST['query'];

        $hash = md5( build_query( $query ) );
        check_ajax_referer( $hash );

        // $query is signed by nonce
        $posts = get_posts( $query );

        if ( !$posts ) {
            exit('-1');
        }

        $posted_opts = $_POST['opts'];

        $opts = array(
            'hide_title'               => $this->_bool( $posted_opts['hide_title'] ),
            'link'                     => in_array( $posted_opts['link'], array( 'post', 'file', 'thickbox', 'none' ) )
                                            ? $posted_opts['link'] : wp_tiles()->get_option( 'link', true ),
            'byline_template'          => wp_kses_post( $posted_opts['byline_template'] ),
            'byline_template_textonly' => $this->_bool( $posted_opts['byline_template_textonly'] ),
            'images_only'              => $this->_bool( $posted_opts['images_only'] ),
            'image_size'               => $posted_opts['image_size'], // Will be sanitized in WPTiles::get_first_image
            'text_only'                => $this->_bool( $posted_opts['text_only'] )
        );

        ob_start();
        wp_tiles()->render_tile_html( $posts, $opts );
        $html = ob_get_contents();
        ob_end_clean();

        $query['paged']++;

        $this->_return( array(
            'tiles' => $html,
            '_ajax_nonce' => wp_tiles()->get_query_nonce( $query )
        ) );
    }

    private function _bool( $value ) {
        if ( 'false' === $value || !$value )
            return false;

        return true;
    }

    private function _return( $data ) {
        if ( !headers_sent() )
            header('Content-Type: application/json');

        echo json_encode( $data );

        exit();
    }
}