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

/**
 * Requires and includes
 *
 * @since 0.1
 */
require_once ( WPTILES_DIR . '/wp-tiles-admin.php' );

if (!class_exists('WP_Tiles')) :

    class WP_Tiles    {

        /**
         * Store the current tiles id, in case we add more to one page
         *
         * @var int
         */
        protected $tiles_id = 1;

        /**
         * Options and default values
         * @var array
         */
        protected $options;

        /**
         * Data to put to the page at the end of the day
         * @var array
         */
        protected $data = array();

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
                load_plugin_textdomain('wp-tiles', false, WPTILES_DIR . '/languages/');
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
            global $wptiles_defaults;
            require_once ( WPTILES_DIR . '/wp-tiles-defaults.php');

            $wptiles_options = get_option( 'wp-tiles-options' );
            $this->options = shortcode_atts( $wptiles_defaults, $wptiles_options);

            add_shortcode( 'wp-tiles', array ( &$this, 'shortcode' ) );
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
            $this->show_tiles ( $atts );
        }

        public function show_tiles ( $atts ) {

            /**
             * Options
             */
            $defaults = $this->options;

            $atts = shortcode_atts( $defaults, $atts );

            $posts = get_posts( $atts['posts_query'] );
            if ( empty ( $posts ) ) return;

            $data = $this->extract_data( $posts, $atts['colors'] );

            $templates = ( ! empty ( $atts['template'] ) ) ? array ( $atts['template'] ) : $atts['templates']['templates'];
            foreach ( $templates as &$template ) {
                $template = explode ( "\n", $template );
            }

            $small_screen_template = explode ( "\n", $atts['templates']['small_screen_template'] );

            /**
             * Now set the variables in the instance
             */
            $wptiles_id = "wp-tiles-" . $this->tiles_id;
            $this->tiles_id++;

            // Keep array of data in class instance, so we can have multiple instances of WP Tiles
            $this->set_data ( $wptiles_id, $templates, $small_screen_template, $data );
            // ... and then process that array in the footer
            add_action ( 'wp_footer', array ( &$this, "add_data" ), 1 );

            /**
             * We are a go, so enqueue styles and scripts
             */
            $this->enqueue_scripts ();
            $this->enqueue_styles();

            $show_selector = ( ! empty ( $atts['show_selector'] ) ) ? $atts['show_selector'] : $atts['templates']['show_selector'];

            /**
             * Time to start rendering our template
             */
            ?>

            <?php if ( $show_selector == 'true' && count ( $templates ) > 1 ) : ?>

            <div id="<?php echo $wptiles_id; ?>-templates">

                <ul class="template-selector">

                    <?php foreach ( $templates as $k => $v ) : ?>

                        <li class="template"><?php echo $k; ?></li>

                    <?php endforeach; ?>

                </ul>

            </div>

            <?php endif; ?>

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
            $this->data[$wptiles_id] = array (
                "id" => $wptiles_id,
                "rowTemplates" => array_values ( $templates ),
                "smallTemplates" => $small_screen_template,
                "posts" => $data
            );
        }

        public function add_data () {
            wp_localize_script('wp-tiles', 'wptilesdata', $this->data );
        }

        /**
         * Look for the stylesheet in a million places
         */
        protected function enqueue_styles() {
            $stylesheet_name = "wp-tiles.css";

            if ( file_exists(STYLESHEETPATH . '/' . $stylesheet_name) ) {
                $located = STYLESHEETPATH . '/' . $stylesheet_name;
            } else if ( file_exists(STYLESHEETPATH . '/inc/css/' . $stylesheet_name) ) {
                $located = STYLESHEETPATH . '/inc/css/' . $stylesheet_name;
            } else if ( file_exists(STYLESHEETPATH . '/inc/' . $stylesheet_name) ) {
                $located = STYLESHEETPATH . '/inc/' . $stylesheet_name;
            } else if ( file_exists(STYLESHEETPATH . '/css/' . $stylesheet_name) ) {
                $located = STYLESHEETPATH . '/css/' . $stylesheet_name;
            } else if ( file_exists(TEMPLATEPATH . '/' . $stylesheet_name) ) {
                $located = TEMPLATEPATH . '/' . $stylesheet_name;
            } else if ( file_exists(TEMPLATEPATH . '/inc/css/' . $stylesheet_name) ) {
                $located = TEMPLATEPATH . '/inc/css/' . $stylesheet_name;
            } else if ( file_exists(TEMPLATEPATH . '/inc/' . $stylesheet_name) ) {
                $located = TEMPLATEPATH . '/inc/' . $stylesheet_name;
            } else if ( file_exists(TEMPLATEPATH . '/css/' . $stylesheet_name) ) {
                $located = TEMPLATEPATH . '/css/' . $stylesheet_name;
            } else {
                $located = WPTILES_INC_URL . '/css/wp-tiles.css';
            }

            wp_enqueue_style( 'wp-tiles', $located );
        }

        protected function extract_data( $posts, $colors ) {
            $data = array();

            if ( is_array ( $colors ) ) $colors = $colors['colors'];
            else {
                $delimiter = ( strpos ( $colors, "," ) ) ? ',' : "\n";
                $colors = explode ( $delimiter, str_replace(" ", "", $colors ) );
            }
            $colors = apply_filters ( "wp-tiles-colors", array_filter ( $colors ) );

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

            return apply_filters ( 'wp-tiles-data', $data );
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

            if ( ! empty ( $post->post_content ) ) {
                $xpath = new DOMXPath( @DOMDocument::loadHTML( $post->post_content ) );
                $src = $xpath->evaluate( "string(//img/@src)" );
                return $src;
            }

            return '';
        }

    }

    add_action('init', array('WP_Tiles', 'init'));
endif;