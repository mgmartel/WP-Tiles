<?php
if ( !class_exists( 'WP_Tiles' ) ) :

    class WP_Tiles
    {

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
        public $options;

        /**
         * Data to put to the page at the end of the day
         * @var array
         */
        protected $data = array( );

        /**
         * Creates an instance of the WP_Tiles class
         *
         * @return WP_Tiles object
         * @since 0.1
         * @static
         */
        public static function &init() {
            static $instance = false;

            if ( !$instance ) {
                load_plugin_textdomain( 'wp-tiles', false, WPTILES_DIR . '/languages/' );
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
            $this->options   = $this->shortcode_atts_rec( $wptiles_defaults, $wptiles_options );

            add_shortcode( 'wp-tiles', array( &$this, 'shortcode' ) );
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
            ob_start();

            $this->show_tiles( $atts );
            $out = ob_get_contents();
            ob_end_clean();

            return $out;
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

        public function show_tiles( $atts_arg ) {

            /**
             * Options and attributes
             */
            if ( is_array( $atts_arg ) && isset( $atts_arg[0] ) && is_a( $atts_arg[0], 'WP_Post' ) ) {
                $posts = $atts_arg;
                $atts  = $this->options;
            } else {
                $atts = $this->shortcode_atts_rec( $this->options, $atts_arg );
                if ( isset( $atts['posts_query']['numberposts'] ) && !empty( $atts['posts_query']['numberposts'] ) )
                    $atts['posts_query']['posts_per_page'] = $atts['posts_query']['numberposts'];

                $posts = get_posts( $atts['posts_query'] );
            }

            if ( empty( $posts ) )
                return;

            $data = $this->extract_data( $posts, $atts['display'], $atts['colors'] );

            if ( !empty( $atts['template'] ) && !empty( $atts['templates']['templates'][$atts['template']] ) ) {
                $templates = array( $atts['templates']['templates'][$atts['template']] );
            } else {
                $templates = $atts['templates']['templates'];
            }
            foreach ( $templates as &$template ) {
                $template = explode( "\n", $template );
            }

            $small_screen_template = explode( "\n", $atts['templates']['small_screen_template'] );

            $display_options                       = (array) $atts['display'];
            $display_options['small_screen_width'] = intval( $atts['templates']['small_screen_width'] );

            /**
             * Now set the variables in the instance
             */
            $wptiles_id = "wp-tiles-" . $this->tiles_id;
            $this->tiles_id++;

            // Keep array of data in class instance, so we can have multiple instances of WP Tiles
            $this->set_data( $wptiles_id, $templates, $small_screen_template, $display_options, $data );
            // ... and then process that array in the footer
            add_action( 'wp_footer', array( &$this, "add_data" ), 1 );

            /**
             * We are a go, so enqueue styles and scripts
             */
            $this->enqueue_scripts();
            $this->enqueue_styles();

            $show_selector = (!empty( $atts['show_selector'] ) ) ? $atts['show_selector'] : $atts['templates']['show_selector'];

            /**
             * Time to start rendering our template
             */
            ?>

            <?php if ( $show_selector == 'true' && count( $templates ) > 1 ) : ?>

                <div id="<?php echo $wptiles_id; ?>-templates" class="tile-templates">

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

        protected function enqueue_scripts() {
            if ( !is_admin() ) {
                wp_enqueue_script( "jquery" );

                $script_path = WPTILES_INC_URL . '/js/';
                $ext = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.js' : '.min.js';

                wp_enqueue_script( 'tilesjs',  $script_path . 'tiles'    . $ext, array( "jquery" ),  "2013-05-18",    true );
                wp_enqueue_script( 'wp-tiles', $script_path . 'wp-tiles' . $ext, array( "tilesjs" ), WPTILES_VERSION, true );
            }
        }

        protected function set_data( $wptiles_id, $templates, $small_screen_template, $display_options, $data ) {
            $rowTemplates          = array_values( $templates );
            $rowTemplates['small'] = $small_screen_template;

            $this->data[$wptiles_id] = array(
                "id"              => $wptiles_id,
                "rowTemplates"    => $rowTemplates,
                "display_options" => $display_options,
                "posts"           => $data,
            );
        }

        public function add_data() {
            wp_localize_script( 'wp-tiles', 'wptilesdata', $this->data );
        }

        /**
         * Look for the stylesheet in a million places
         */
        protected function enqueue_styles() {
            $stylesheet_name = "wp-tiles.css";

            if ( file_exists( STYLESHEETPATH . '/' . $stylesheet_name ) ) {
                $located = get_stylesheet_directory_uri() . '/' . $stylesheet_name;
            } else if ( file_exists( STYLESHEETPATH . '/inc/css/' . $stylesheet_name ) ) {
                $located = get_stylesheet_directory_uri() . '/inc/css/' . $stylesheet_name;
            } else if ( file_exists( STYLESHEETPATH . '/inc/' . $stylesheet_name ) ) {
                $located = get_stylesheet_directory_uri() . '/inc/' . $stylesheet_name;
            } else if ( file_exists( STYLESHEETPATH . '/css/' . $stylesheet_name ) ) {
                $located = get_stylesheet_directory_uri() . '/css/' . $stylesheet_name;
            } else if ( file_exists( TEMPLATEPATH . '/' . $stylesheet_name ) ) {
                $located = get_template_directory_uri() . '/' . $stylesheet_name;
            } else if ( file_exists( TEMPLATEPATH . '/inc/css/' . $stylesheet_name ) ) {
                $located = get_template_directory_uri() . '/inc/css/' . $stylesheet_name;
            } else if ( file_exists( TEMPLATEPATH . '/inc/' . $stylesheet_name ) ) {
                $located = get_template_directory_uri() . '/inc/' . $stylesheet_name;
            } else if ( file_exists( TEMPLATEPATH . '/css/' . $stylesheet_name ) ) {
                $located = get_template_directory_uri() . '/css/' . $stylesheet_name;
            } else {
                $located = WPTILES_INC_URL . '/css/wp-tiles.css';
            }
            wp_enqueue_style( 'wp-tiles', $located, false, WPTILES_VERSION );
        }

        protected function extract_data( $posts, $display_options, $colors ) {
            $data = array( );

            if ( is_array( $colors ) )
                $colors = $colors['colors'];
            else {
                $delimiter = ( strpos( $colors, "," ) ) ? ',' : "\n";
                $colors    = explode( $delimiter, str_replace( " ", "", $colors ) );
            }
            $colors = apply_filters( "wp-tiles-colors", array_filter( $colors ) );

            $display_options = apply_filters( "wp-tiles-display_options", $display_options );

            $hideByline = ( 'show' == $display_options['text'] ) ? false : true;

            foreach ( $posts as $post ) {
                $hideByline = apply_filters( 'wp-tiles-hide-byline', $hideByline, $post->ID, $post );

                $categories = wp_get_post_categories( $post->ID, array( "fields" => "all" ) );

                $category_slugs = $category_names = array();
                foreach( $categories as $category ) {
                    $category_slugs[] = $category->slug;
                    $category_names[] = $category->name;
                }

                switch ( $display_options['byline'] ) {
                    case 'nothing' :
                        $byline = '';
                        break;
                    case 'excerpt' :
                        $byline = $this->get_the_excerpt( $post->post_content, $post->post_excerpt );
                        break;
                    case 'date1' :
                        $byline = $this->get_the_date( $post );
                        break;
                    case 'date2' :
                        $byline = $this->get_the_date( $post, 'd-m-Y' );
                        break;
                    case 'date3' :
                        $byline = $this->get_the_date( $post, 'm-d-Y' );
                        break;
                    case 'cats' :
                    default :
                        $byline = $category_names;
                        break;
                }

                $color  = $colors[array_rand( $colors )];
                /**
                 * Byline opacity only when using random colors
                 */
                $data[] = array(
                    "id"          => $post->ID,
                    "title"       => apply_filters( 'the_title', $post->post_title ),
                    "url"         => get_permalink( $post->ID ),
                    "byline"      => apply_filters( 'wp-tiles-byline', $byline, $post ),
                    "img"         => $this->get_first_image( $post ),
                    "color"       => $color,
                    "bylineColor" => $this->HexToRGBA( $color, $display_options['bylineOpacity'], true ),
                    "hideByline"  => $hideByline,
                    "categories"  => $category_slugs
                );
            }

            return apply_filters( 'wp-tiles-data', $data, $posts, $colors, $this );
        }

        private function get_the_date( $post, $d = '' ) {
            $the_date = '';

            if ( '' == $d )
                $the_date .= mysql2date( get_option( 'date_format' ), $post->post_date );
            else
                $the_date .= mysql2date( $d, $post->post_date );

            return apply_filters( 'get_the_date', $the_date, $d );
        }

        private function HexToRGB( $hex ) {
            $hex   = str_replace( "#", "", $hex );
            $color = array( );

            if ( strlen( $hex ) == 3 ) {
                $color['r'] = hexdec( substr( $hex, 0, 1 ) . $r );
                $color['g'] = hexdec( substr( $hex, 1, 1 ) . $g );
                $color['b'] = hexdec( substr( $hex, 2, 1 ) . $b );
            } else if ( strlen( $hex ) == 6 ) {
                $color['r'] = hexdec( substr( $hex, 0, 2 ) );
                $color['g'] = hexdec( substr( $hex, 2, 2 ) );
                $color['b'] = hexdec( substr( $hex, 4, 2 ) );
            }

            return $color;
        }

        private function HexToRGBA( $hex, $alpha, $css = false ) {
            $rgba      = $this->HexToRGB( $hex );
            $rgba['a'] = $alpha;
            if ( !$css )
                return $rgba;

            return "rgba( {$rgba['r']},{$rgba['g']},{$rgba['b']},{$rgba['a']} )";
        }

        function get_the_excerpt( $text, $excerpt ) {
            if ( $excerpt )
                return $excerpt;

            $text = strip_shortcodes( $text );

            $text           = apply_filters( 'the_content', $text );
            $text           = str_replace( ']]>', ']]&gt;', $text );
            $text           = strip_tags( $text );
            $excerpt_length = apply_filters( 'excerpt_length', 55 );
            $excerpt_more   = apply_filters( 'excerpt_more', ' ' . '[...]' );
            $words          = preg_split( "/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY );
            if ( count( $words ) > $excerpt_length ) {
                array_pop( $words );
                $text = implode( ' ', $words );
                $text = $text . $excerpt_more;
            } else {
                $text = implode( ' ', $words );
            }

            return apply_filters( 'wp_trim_excerpt', $text, $excerpt );
        }

        protected function has_excerpt( $post ) {
            return !empty( $post->post_excerpt );
        }

        /**
         * Returns the first image
         *
         * Uses cache. Plugins can hijack this method by hooking into 'pre_wp_tiles_image'.
         * @param WP_Post $post
         * @return string Image url
         */
        public function get_first_image( $post ) {
            // Allow plugins to hijack image loading
            $src = apply_filters( 'pre_wp_tiles_image', false, $post );
            if ( false !== $src )
                return $src;

            if ( !$src = wp_cache_get( 'wp_tiles_image_' . $post->ID, 'wp-tiles' ) ) {
                $src = $this->_find_the_image( $post );
                wp_cache_set( 'wp_tiles_image_' . $post->ID, $src, 'wp-tiles' );
            }

            return $src;
        }

            /**
             * Finds the first relevant image to a post
             *
             * Searches for a featured image, then the first attached image, then the first image in the source.
             *
             * @param WP_Post $post
             * @return string Source
             * @sice 0.5.2
             */
            private function _find_the_image( $post ) {
                $tile_image_size = apply_filters( 'wp-tiles-image-size', 'post-thumbnail', $post );

                if ( 'attachment' === get_post_type( $post->ID ) ) {
                    $image = wp_get_attachment_image_src( $post->ID, $tile_image_size, false );
                    return $image[0];
                }

                if ( $post_thumbnail_id = get_post_thumbnail_id( $post->ID ) ) {
                    $image = wp_get_attachment_image_src( $post_thumbnail_id, $tile_image_size, false );
                    return $image[0];
                }

                $images = get_children( array(
                    'post_parent'    => $post->ID,
                    'numberposts'    => 1,
                    'post_mime_type' => 'image'
                        ) );

                if ( !empty( $images ) ) {
                    $images = current( $images );
                    $src    = wp_get_attachment_image_src( $images->ID, $size   = $tile_image_size );
                    return $src[0];
                }

                if ( !empty( $post->post_content ) ) {
                    $xpath = new DOMXPath( @DOMDocument::loadHTML( $post->post_content ) );
                    $src   = $xpath->evaluate( "string(//img/@src)" );
                    return $src;
                }
                return '';
            }

        /**
         * Allow $atts to be just the post_query as a string or object
         *
         * @param string|array $atts
         * @return array Properly formatted $atts
         * @since 0.4.2
         */
        public function parse_post_query_string( $atts ) {
            if ( is_array( $atts ) ) {
                if ( !isset( $atts['posts_query'] ) )
                    $atts['posts_query'] = array( );
            } else {

                $posts_query = array( );
                wp_parse_str( $atts, $posts_query );
                $atts        = array( 'posts_query' => $posts_query );
            }

            /**
             * Backward compatibility
             */
            if ( isset( $atts['posts_query']['numberposts'] ) ) {
                $atts['posts_query']['posts_per_page'] = $atts['posts_query']['numberposts'];
                _doing_it_wrong( 'the_wp_tiles', "WP Tiles doesn't use numberposts anymore. Use posts_per_page instead.", '0.4.2' );
            }

            return $atts;
        }

    }

    add_action( 'init', array( 'WP_Tiles', 'init' ) );

    /**
     * Get the one and only true instance of WP Tiles
     *
     * @return WP_Tiles
     * @since 0.4.2
     */
    function wp_tiles() {
        return WP_Tiles::init();
    }



endif;