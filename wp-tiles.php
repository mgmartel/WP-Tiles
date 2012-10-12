<?php
/*
  Plugin Name: WP Tiles
  Plugin URI: http://trenvo.com
  Description: WP Tiles
  Version: 0.1
  Author: Mike Martel
  Author URI: http://trenvo.com
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Version number
 *
 * @since 0.1
 */
define('WPTILES_VERSIE', '0.1');

/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define('WPTILES_DIR', plugin_dir_path(__FILE__));
define('WPTILES_URL', plugin_dir_url(__FILE__));
define('WPTILES_TEMPLATES_DIR', WPTILES_DIR . 'templates/');
define('WPTILES_TEMPLATES_URL', WPTILES_URL . 'templates/');
define('WPTILES_INC_URL', WPTILES_URL . '_inc/');

if (!class_exists('WP_Tiles')) :

    class WP_Tiles    {

        protected $tiles_id = 1;

        /**
         * Creates an instance of the WP_Tiles class
         *
         * @return WP_Tiles object
         * @since 0.1
         * @static
        */
        public static function &init() {
            static $instance = false;

            if (!$instance) {
                //load_pugin_textdomain('wp-tiles', false, WPTILES_DIR . '/languages/');
                $instance = new WP_Tiles;
            }

            return $instance;
        }

        /**
         * Constructor
         *
         * @since 0.1
         */
        public function __construct() {
            add_shortcode( 'wp-tiles', array ( &$this, 'shortcode' ) );
            //$this->register_shortcode();
        }

            /**
             * PHP4
             *
             * @since 0.1
             */
            public function WP_Tiles() {
                $this->__construct();
            }

        public function shortcode( $atts ) {
            $default_colors = "#009999,#1D7373,#006363,#33CCCC,#5CCCCC";

            $defaults = array(
                'numberposts' => 15, 'offset' => 0,
                'category' => 0, 'orderby' => 'rand',
                'order' => 'DESC', 'include' => array(),
                'exclude' => array(), 'meta_key' => '',
                'meta_value' =>'', 'post_type' => 'post',
                'suppress_filters' => true,
                'colors' => $default_colors
            );

            $atts = shortcode_atts( $defaults, $atts );
            $posts = get_posts( $atts );

            $data = $this->extract_data( $posts, $atts['colors'] );
            if ( empty ( $data ) ) return;

            $this->enqueue_scripts ( );

            $wptiles_id = "wp-tiles-" . $this->tiles_id;
            $this->tiles_id++;

            $templates = array (
                array (
                    " . A A B B ",
                    " C C . B B ",
                    " D D E E . ",
                    " D D . C C "
                ), array (
                    " . A A D B ",
                    " C A A D B ",
                    " G F E E . ",
                    " G F . C C "
                ), array (
                    " A B C C D ",
                    " A B C C D ",
                    " G F E E . ",
                    " G F . H H "
                ), array (
                    " . . . . . ",
                ), array (
                    " . . . . . . . . ",
                )
            );
            $small_screen_template = array (
                " A A ",
                " . . ",
                " A A ",
                " . . ",
                " A A ",
                " . . ",
                " A A ",
            );

            $this->set_data ( $wptiles_id, $templates, $small_screen_template, $data );

            $this->enqueue_styles();

            ?>

            <div id="<?php echo $wptiles_id; ?>-templates">
                <ul>

                    <?php foreach ( $templates as $k => $v ) : ?>

                        <li class="template"><?php echo $k; ?></li>

                    <?php endforeach; ?>

                </ul>

            </div>

            <div class="wp-tile-container">

                <div id="<?php echo $wptiles_id; ?>" class="grid"></div>

            </div>

            <?php

        }

        protected function enqueue_scripts () {
            if (! is_admin()) {
                wp_enqueue_script("jquery");
                wp_enqueue_script( 'tilesjs', WPTILES_INC_URL . '/js/tiles.js', array ( "jquery" ), false, true );
                wp_enqueue_script( 'wp-tiles', WPTILES_INC_URL . '/js/wp-tiles.js',  array ( "tilesjs" ), false, true );
            }
        }

        protected function set_data ( $wptiles_id, $templates, $small_screen_template, $data ) {
            wp_localize_script('wp-tiles', 'wptilesdata', array ( "id" => $wptiles_id, "rowTemplates" => $templates, "smallTemplates" => $small_screen_template, "posts" => $data ) );
        }

        protected function enqueue_styles() {
            wp_enqueue_style( 'wp-tiles', WPTILES_INC_URL . '/css/wp-tiles.css' );
        }

        protected function extract_data( $posts, $colors ) {
            $data = array();
            $colors = apply_filters ( "wp-tiles-colors", explode( ",", $colors ) );

            foreach ( $posts as $post ) {
                $data[] = array (
                    "id"        => $post->ID,
                    "title"     => $post->post_title,
                    "url"       => get_permalink( $post->ID ),
                    "category"  => wp_get_post_categories( $post->ID, array ( "fields" => "names" ) ),
                    "img"       => $this->get_first_image ( $post ),
                    "color"     => $colors[ array_rand( $colors ) ],
                );
            }

            return $data;
        }

        protected function get_the_excerpt ( $post ) {
            if ( $this->has_excerpt( $post ) )
                return $post->excerpt;

            $excerpt_length = apply_filters('excerpt_length', 55);
            $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
            $excerpt = wp_trim_words( strip_tags ( $post->post_content ), $excerpt_length, $excerpt_more );

            return apply_filters('wp_trim_excerpt', $excerpt, '' );
        }

        protected function has_excerpt ( $post ) {
            return ! empty( $post->post_excerpt );
        }

        protected function get_first_image ( $post ) {
            $images = get_children ( array (
                'post_parent'    => $post->ID,
                'numberposts'    => 1,
                'post_mime_type' =>'image'
            ) );

            if( ! empty ( $images ) ) {
                $images = current ( $images );
                $src = wp_get_attachment_image_src ( $images->ID, $size = 'thumbnail' );
                return $src[0];
            }

            $xpath = new DOMXPath( @DOMDocument::loadHTML( $post->post_content ) );
            $src = $xpath->evaluate( "string(//img/@src)" );
            return $src;
        }

    }

    add_action('init', array('WP_Tiles', 'init'));
endif;