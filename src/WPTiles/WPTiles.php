<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class WPTiles
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
    public static function get_instance() {
        static $instance = false;

        if ( !$instance ) {
            load_plugin_textdomain( 'wp-tiles', false, WPTILES_DIR . '/languages/' );

            $class = get_called_class();
            $instance = new $class;
            $instance->init();
        }

        return $instance;
    }

    protected function __construct() {}

    public function init() {
        GridTemplates::get_instance();
        Admin::setup();

        add_shortcode( 'wp-tiles', array( '\WPTiles\Shortcode', 'do_shortcode' ) );
    }

    /**
     * Return the plugin default settings
     *
     * @return array
     */
    public function get_option_defaults( $key = false ) {
        static $option_defaults = array(
            'grids' => false,
            'small_screen_grid' => false,
            'small_screen_breakpoint' => 800,

            'colors' => array(
                "#009999",
                "#1D7373",
                "#006363",
                "#33CCCC",
                "#5CCCCC",
            ),
            'padding' => 10,

            'byline_template' => "%categories%",
            'byline_template_textonly' => false,
            'byline_opacity'  => '0.8',
            'byline_color'    => '#000',
            'byline_height'   => 40,

            'text_only'    => false,
            'link_to_post' => true,
            'images_only'  => false,
            'hide_title'   => false,

            'animate_init'     => true,
            'animate_resize'   => true,
            'animate_template' => true
        );

        if ( $key )
            return isset( $option_defaults[$key] ) ? $option_defaults[$key] : null;

        return $option_defaults;
    }

    public function get_defaults() {
        static $defaults = false;

        if ( !$defaults ) {

            $defaults = array();
            $options = $this->get_option_defaults();

            foreach( $options as $option => $default ) {
                $value = $this->get_option( $option );
                $defaults[$option] = is_null( $value ) ? $default : $value;
            }

            // @todo Cache results?
            $defaults['grids']             = $this->get_grids( $defaults['grids'] );

            $small_screen_grids = $this->get_grids( $defaults['small_screen_grid'] );
            $defaults['small_screen_grid'] = end( $small_screen_grids );

            $colors = array();
            for ( $i = 1; $i <= 5; $i++ ) {
                $color = $this->get_option( 'color_' . $i );
                if ( $color )
                    $colors[] = $color;
            }

            $defaults['colors'] = Helper::colors_to_rgba( $colors );

            if ( empty( $defaults['byline_color'] ) )
                $defaults['byline_color'] = 'random';

            if ( !$this->get_option( 'byline_for_text_only' ) )
                $defaults['byline_template_textonly'] = false;

            // Disable individual animations when disabled globally
            if ( !$this->get_option( 'animated' ) ) {
                foreach( array( 'animate_init', 'animate_resize', 'animate_template' ) as $a ) {
                    $defaults[$a] = false;
                }
            }

            if ( 'random' !== $defaults['byline_color'] )
                $defaults['byline_color'] = Helper::hex_to_rgba( $defaults['byline_color'], $defaults['byline_opacity'], true );

        }

        return $defaults;

    }

    public function get_option( $name ) {
        return vp_option( "wp_tiles." . $name );
    }

    /**
     * @deprecated since version 1.0
     */
    public function show_tiles( $atts_arg ) {
        echo $this->shortcode( $atts_arg );
    }

    public function render_tiles( $posts, $options ) {

        if ( empty( $posts ) )
            return;

        /**
         * Set the variables in the instance
         */
        $wptiles_id = "wp-tiles-" . $this->tiles_id;
        $this->tiles_id++;

        /**
         * Setup the grid(s)
         */
        $grids = $options['grids'];

        // @todo Is this needed?
        array_walk( $grids, function( &$grid ){
            if ( !is_array( $grid ) )
                $grid = explode( "\n", $grid );
        });

        $small_screen_grid = $options['small_screen_grid'];

        /**
         * Pass the required info to the JS
         */
        $this->add_data_for_js( $wptiles_id, $grids, $small_screen_grid, $options );

        /**
         * Time to start rendering our template
         */
        ?>

        <?php if ( count( $grids ) > 1 ) : ?>

            <div id="<?php echo $wptiles_id; ?>-templates" class="tile-templates">

                <ul class="template-selector">

            <?php foreach ( array_keys( $grids ) as $k ) : ?>

                        <li class="template"><?php echo $k; ?></li>

            <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

        <div class="wp-tiles-container">

            <div id="<?php echo $wptiles_id; ?>" class="wp-tiles-grid">
                <?php $this->_render_tile_html( $posts, $options ) ?>
            </div>

        </div>

        <?php
    }

    private function _render_tile_html( $posts, $display_options ) {

        foreach( $posts as $post ) :

            if ( !$display_options['text_only'] && $img = $this->get_first_image( $post ) ) {
                $tile_class = 'wp-tiles-tile-with-image';
            } elseif ( $display_options['images_only'] ) {
                continue; // If text_only *and* image_only are enabled, the user should expect 0 tiles..

            } else {
                $tile_class = 'wp-tiles-tile-text-only';
            }

            ?>
            <div class='wp-tiles-tile' id='tile-<?php echo $post->ID ?>'>

                <?php if ( $display_options['link_to_post'] ) : ?>
                    <a href="<?php echo get_permalink( $post->ID ) ?>" title="<?php echo apply_filters( 'the_title', $post->post_title ) ?>">
                <?php endif; ?>

                    <?php //@todo Should this be article (both the tag & the schema)? ?>
                    <article class='<?php echo $tile_class ?> wp-tiles-tile-wrapper' itemscope itemtype="http://schema.org/Thing">

                        <?php if ( $img ) : ?>
                            <div class='wp-tiles-tile-bg'>
                                <img src='<?php echo $img ?>' class='wp-tiles-img' itemprop="image" />
                            </div>
                        <?php endif; ?>

                        <div class='wp-tiles-byline'>

                            <?php if ( !$display_options['hide_title'] ) : ?>
                                <h4 itemprop="name" class="wp-tiles-byline-title"><?php echo apply_filters( 'the_title', $post->post_title ) ?></h4>
                            <?php endif; ?>

                            <div class='wp-tiles-byline-content' itemprop="description">
                                <?php if ( $display_options['byline_template_textonly'] && ($display_options['text_only'] || !$img ) ) : ?>

                                    <?php echo $this->render_byline( $display_options['byline_template_textonly'], $post ); ?>

                                <?php elseif ( $display_options['byline_template'] ) : ?>

                                    <?php echo $this->render_byline( $display_options['byline_template'], $post ); ?>

                                <?php endif; ?>
                            </div>

                        </div>

                    </article>

<?php /*
                    <div class='<?php echo $tile_class ?>' style='<?php echo $tile_style ?>'>
                        <div class='tile-byline<?php echo $byline_class ?>'>
                            <div class='title'><?php echo $post['title']; ?></div>
                            <?php if ( !$post['hideByline'] ) :?>
                                <div class='extra'<?php echo $byline_style ?>><?php echo $post['byline']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
*/ ?>
                <?php if ( $display_options['link_to_post'] ) : ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php
        endforeach;

    }

    protected function render_byline( $template, $post ) {
        // Only use below filter to change the byline on a per-post level
        $template = apply_filters( 'wp_tiles_byline_template_post', $template, $post );

        $tags = array(
            '%title%'   => $post->post_title,
            '%content%' => $post->post_content,
            '%excerpt%' => $this->get_the_excerpt( $post ),
            '%date%'    => $this->get_the_date( $post ),
            '%link%'    => get_permalink( $post )
        );

        // Only do the more expensive tags if needed
        if ( strpos( $template, '%categories%' ) !== false ) {
            $tags['%categories%'] = implode( ', ', wp_get_post_categories( $post->ID, array( "fields" => "names" ) ) );
        }
        if ( strpos( $template, '%tags%' ) !== false ) {
            $tags['%tags%'] = implode( ', ', wp_get_post_tags( $post->ID, array( "fields" => "names" ) ) );
        }

        $tags = apply_filters( 'wp_tiles_byline_tags', $tags, $post, $template );

        return str_replace( array_keys( $tags ), array_values( $tags ), $template );
    }

    protected function add_data_for_js( $wptiles_id, $templates, $small_screen_grids, $display_options ) {
        static $enqueued = false;

        if ( !$enqueued ) {
            $this->enqueue_scripts();
            $this->enqueue_styles();

            $enqueued = true;
        }

        $grids          = array_values( $templates );
        $grids['small'] = $small_screen_grids;

        $this->data[$wptiles_id] = array(
            "id"              => $wptiles_id,
            "rowTemplates"    => $grids,
            "display_options" => $display_options,
        );
    }

    public function add_data() {
        wp_localize_script( 'wp-tiles', 'wptilesdata', $this->data );

    }

    protected function enqueue_scripts() {
        if ( !is_admin() ) {
            wp_enqueue_script( "jquery" );

            $script_path = WP_TILES_ASSETS_URL . '/js/';
            $ext = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.js' : '.min.js';

            wp_enqueue_script( 'tilesjs',  $script_path . 'tiles' . $ext, array( "jquery" ), "2013-05-18", true );
            wp_enqueue_script( 'jquery-dotdotdot',  $script_path . 'jquery.dotdotdot' . $ext, array( "jquery" ),  "1.6.14", true );

            wp_enqueue_script( 'wp-tiles', $script_path . 'wp-tiles' . $ext, array( "tilesjs", "jquery-dotdotdot" ), WP_TILES_VERSION, true );

            add_action( 'wp_footer', array( &$this, "add_data" ), 1 );
        }
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
            $located = WP_TILES_ASSETS_URL . '/css/wp-tiles.css';
        }
        wp_enqueue_style( 'wp-tiles', $located, false, WP_TILES_VERSION );
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
                "bylineColor" => Helper::hex_to_rgba( $color, $display_options['bylineOpacity'], true ),
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

    function get_the_excerpt( $text, $excerpt = '' ) {
        if ( is_a( $text, 'WP_Post' ) ) {
            $excerpt = $text->post_excerpt;
            $text = $text->post_content;
        }

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
            $tile_image_size = apply_filters( 'wp-tiles-image-size', 'medium', $post );

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
                $xpath = new \DOMXPath( @\DOMDocument::loadHTML( $post->post_content ) );
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


    public function get_grids( $q ) {
        // Is this already a grid?
        // Happens when default is passed through the shortcode
        if ( is_array( $q ) && is_array( reset( $q ) ) )
            return $q;

        $posts = $this->_get_grid_posts( $q );

        $grids = array();
        foreach( $posts as $post ) {
            $grids[$post->post_title] = array_map( 'trim', explode( "\n", $post->post_content ) );
        }

        return $grids;
    }

        protected function _get_grid_posts( $q ) {
            if ( !is_array( $q ) ) {
                $q = strpos( $q, ',' ) !== false ? explode( ',', $q ) : array( $q );
            }

            // Are we dealing with titles?
            if ( !is_numeric( reset( $q ) ) ) {
                $q = $this->_get_grid_ids_by_titles( $q );
            }

            if ( $q ) {
                $query = array(
                    'post_type' => GridTemplates::POST_TYPE,
                    'posts_per_page' => -1,
                    'post__in' => $q
                );
                $posts = get_posts( $query );

                if ( $posts )
                    return $posts;
            }

            // If no posts are found, return all of them
            return get_posts( array(
                'post_type' => GridTemplates::POST_TYPE,
                'posts_per_page' => -1
            ) );
        }

        /**
         * @todo DB Query. Cache! Can be invalidated on post type save.
         */
        private function _get_grid_ids_by_titles( $titles ) {
            global $wpdb;

            if ( empty( $titles) )
                return false;

            $titles = esc_sql( $titles );
            $post_title_in_string = "'" . implode( "','", $titles ) . "'";

            $sql = $wpdb->prepare( "
                SELECT ID
                FROM $wpdb->posts
                WHERE post_title IN ($post_title_in_string)
                AND post_type = %s
            ", GridTemplates::POST_TYPE );

            $ids = $wpdb->get_col( $sql );
            return $ids;

       }
}