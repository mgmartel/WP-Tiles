<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Gallery extends Abstracts\WPSingleton
{
    public function init() {
        $this->add_filter( 'post_gallery', 'maybe_do_gallery', 1001, 2 );

        /**
         * Add WP Tiles Gallery to media modal
         */
        $this->add_action( 'init', 'register_scripts' );
        $this->add_action( 'wp_enqueue_media',      'enqueue_media_script' );
        $this->add_action( 'print_media_templates', 'print_media_templates' );

    }

    public function register_scripts() {
        // Add WP Tiles gallery to media modal
        wp_register_script(
            'wp-tiles-gallery-settings',
            WP_TILES_ASSETS_URL . 'js/wp-tiles-gallery-settings.js',
            array( 'media-views' )
        );
    }

    public function enqueue_media_script() {

        if ( !is_admin() || !function_exists( 'get_current_screen' ) )
            return;

        if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
            return;

        wp_enqueue_script( 'wp-tiles-gallery-settings' );

    }

    public function print_media_templates() {

        if ( !is_admin() || !function_exists( 'get_current_screen' ) )
            return;

        if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
            return;

        $grids = wp_tiles()->get_grids();
        $default = wp_tiles()->get_default_grid_title();

        ?>
        <script type="text/html" id="tmpl-wp-tiles-gallery-settings">
            <label class="setting">
                <span><?php _e( 'Tiled Gallery', 'wp-tiles' ); ?></span>
                <input type="checkbox" class="wp-tiles-enabled" data-setting="tiles" value="yes" />
            </label>

            <div class="wp-tiles-settings">

                <label class="setting">
                    <span><?php _e( 'Grid', 'wp-tiles' ); ?></span>
                    <select name="wp-tiles-grids" data-setting="grids">
                        <?php foreach ( array_keys( $grids ) as $grid ) : ?>
                            <option value="<?php echo esc_attr( $grid ); ?>" <?php selected( $grid, $default ); ?>>
                                <?php echo esc_html( $grid ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="setting">
                    <span><?php _e('Image Size', 'wp-tiles' ) ?></span>
                    <select class="type" name="image_size" data-setting="image_size">
                        <?php

                        $sizes = Admin\DataSources::get_image_sizes();

                        foreach ( $sizes as $size ) { ?>
                            <option value="<?php echo esc_attr( $size['value'] ); ?>" <?php selected( $size['value'], 'large' ); ?>>
                                <?php echo esc_html( $size['label'] ); ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>


            </div>
        </script>
        <?php
    }

    public function maybe_do_gallery( $ret, $atts ) {
        if ( isset( $atts['tiles'] ) )
            return $this->do_gallery( $atts );

        return $ret;
    }

    public function do_gallery( $atts ) {
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
        $link = wp_tiles()->options->get_option( 'link' );

        if ( class_exists( 'No_Jetpack_Carousel' ) || class_exists( 'Jetpack_Carousel' )
            && !apply_filters( 'jp_carousel_maybe_disable', false ) ) {
            $link = 'carousel';
        }

        $atts = wp_parse_args( $atts, array(
            'byline_template' => '<h4 class="wp-tiles-byline-title">%excerpt%</h4>',
            'hide_title' => 'true',
            'link' => $link
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