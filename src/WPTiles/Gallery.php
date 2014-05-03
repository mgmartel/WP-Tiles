<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Gallery
{
    public static function maybe_do_gallery( $ret, $atts ) {
        if ( isset( $atts['tiles'] ) )
            return self::do_gallery( $atts );

        return $ret;
    }

    public static function do_gallery( $atts ) {
        $post = get_post();

        $gallery_atts = shortcode_atts(array(
            'order'      => 'ASC',
            'orderby'    => 'menu_order ID',
            'id'         => $post ? $post->ID : 0,
            'include'    => '',
            'exclude'    => '',
        ), $atts, 'gallery');

        $id = intval($gallery_atts['id']);

        $order = $gallery_atts['order'];
        $orderby = $gallery_atts['orderby'];
        $include = $gallery_atts['include'];
        $exclude = $gallery_atts['exclude'];

        if ( 'RAND' == $order )
            $orderby = 'none';

        if ( !empty($include) ) {
            $_attachments = get_posts( array(
                'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

            $attachments = array();
            foreach ( $_attachments as $key => $val ) {
                $attachments[$val->ID] = $_attachments[$key];
            }
        } elseif ( !empty($exclude) ) {
            $attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
        } else {
            $attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
        }

        // Set Gallery specific defaults
        $atts = wp_parse_args( $atts, array(
            'byline_template' => '<h4 class="wp-tiles-byline-title">%excerpt%</h4>',
            'hide_title' => 'true',
            'link' => wp_tiles()->options->get_option( 'link' ) == 'thickbox' ? 'thickbox' : 'file'
        ) );

        if ( 'attachment' == $atts['link'] )
            $atts['link'] = 'post';

        // Get rest of shortcode options
        $options = Shortcode::get_options( $atts );

        $ret = wp_tiles()->get_tiles( $attachments, $options );

        return $ret;
    }

    public static function get_carousel_image_attr( $attachment ) {
        $attr = apply_filters( 'wp_get_attachment_image_attributes', array( 'src' => '', 'class' => '', 'alt' => '' ), $attachment );
        $attr = array_map( 'esc_attr', $attr );
        unset( $attr['src'], $attr['class'], $attr['alt'] );

        /**
         *  The caption is gotten in a roundabout way, by flying to .parents('dl').find('dd.gallery-caption')
         *  Right now, won't fix.
         */

        $ret = '';
        foreach ( $attr as $name => $value ) {
            $ret .= " $name=" . '"' . $value . '"';
        }

        return $ret;
    }
}