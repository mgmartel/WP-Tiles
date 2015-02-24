<?php namespace WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class WPTiles extends Abstracts\WPSingleton
{

    const GRID_POST_TYPE = 'grid_template';
    const CACHEGROUP = 'wptiles';

    /**
     * Store the current tiles id, in case we add more to one page
     * @var int
     */
    protected $tiles_id = 1;

    /**
     * Data to put to the page at the end of the day
     * @var array
     */
    protected $data = array( );

    /**
     * @var PostQuery
     * @since 1.0
     */
    public $post_query;

    /**
     * @var Options
     * @since 1.0
     */
    public $options;

    /**
     * Only made available so other plugins can interact with the AJAX class (for
     * example to remove the action).
     *
     * @var Ajax
     * @since 1.0
     */
    public $ajax;

    /**
     * @var Gallery
     * @since 1.0
     */
    public $gallery;

    public function init() {
        load_plugin_textdomain( 'wp-tiles', false, basename( WP_TILES_DIR ) . '/languages/' );

        $this->post_query = new PostQuery();
        $this->options    = new Options();
        $this->ajax       = Ajax::get_instance();
        $this->gallery    = Gallery::get_instance();

        add_action( 'after_setup_theme', array( 'WPTiles\Admin\Admin', 'setup' ), 20 );

        $this->add_action( 'init', 'register_post_type' );
        $this->add_action( 'init', 'register_scripts' );
        $this->add_action( 'init', 'register_styles' );

        // The Shortcode
        add_shortcode( 'wp-tiles', array( '\WPTiles\Shortcode', 'do_shortcode' ) );

        $this->add_action( 'save_post_' . self::GRID_POST_TYPE, 'invalidate_grid_cache' );
    }

    public function register_post_type() {
        register_post_type( self::GRID_POST_TYPE, apply_filters( 'wp_tiles_grid_template_post_type', array(
            'labels'             => array(
                'name'               => _x( 'Grids', 'post type general name', 'wp-tiles' ),
                'singular_name'      => _x( 'Grid', 'post type singular name', 'wp-tiles' ),
                'menu_name'          => _x( 'WP Tiles Grids', 'admin menu', 'wp-tiles' ),
                'name_admin_bar'     => _x( 'Grid', 'add new on admin bar', 'wp-tiles' ),
                'add_new'            => __( 'Add New Grid', 'wp-tiles' ),
                'add_new_item'       => __( 'Add New Grid', 'wp-tiles' ),
                'new_item'           => __( 'New Grid', 'wp-tiles' ),
                'edit_item'          => __( 'Edit Grid', 'wp-tiles' ),
                'view_item'          => __( 'View Grid', 'wp-tiles' ),
                'all_items'          => __( 'Grids', 'wp-tiles' ),
                'search_items'       => __( 'Search Grids', 'wp-tiles' ),
                'parent_item_colon'  => __( 'Parent Grids:', 'wp-tiles' ),
                'not_found'          => __( 'No grids found.', 'wp-tiles' ),
                'not_found_in_trash' => __( 'No grids found in Trash.', 'wp-tiles' ),
            ),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            //'show_in_menu'       => true,
            'show_in_menu'       => 'wp-tiles',
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 100,
            'menu_icon'          => 'dashicons-screenoptions',
            'supports'           => array( 'title' )
        ) ) );
    }

    /**
     * @deprecated since version 1.0 Use display tiles instead
     */
    public function show_tiles( $atts_array ) {
        $query = Legacy::get_atts_array_query( $atts_array );
        $opts  = Legacy::convert_option_array( $atts_array );

        return $this->display_tiles( $query, $opts );
    }

    /**
     * Echos the tiles and return true if output has been generated.
     *
     * @return boolean
     */
    public function display_tiles( $posts = array(), $opts = array() ) {
        if ( !$ret = $this->get_tiles( $posts, $opts ) )
            return false;

        echo $ret;
        return true;
    }

    public function get_tiles( $posts = array(), $opts = array() ) {

        //
        // SETUP
        //

        // This is a double-up when called from the shortcode, but makes the
        // method more reliable.
        $opts = shortcode_atts( $this->options->get_options(), $opts );

        /**
         * Set the variables in the instance
         */
        $wp_tiles_id = "wp_tiles_" . $this->tiles_id;
        $this->tiles_id++;

        /**
         *  Cleanup grids and set names
         */
        $opts['grids'] = $this->get_grids( $opts['grids'] );

        $grid_pretty_names = array_keys( $opts['grids'] );
        $opts['grids'] = $this->sanitize_grid_keys( $opts['grids'] );
        $grid_names = array_combine( array_keys( $opts['grids'] ), $grid_pretty_names );

        $small_screen_grids = $this->get_grids( $opts['small_screen_grid'] );
        $opts['small_screen_grid'] = end( $small_screen_grids );


        //
        // GET POSTS
        //
        if ( !$posts ) $posts = array();

        // Is $posts a query?
        if ( is_array( $posts ) && ( empty( $posts ) || count( array_filter( array_keys( $posts ), 'is_string') ) ) ) {

            if ( isset( $posts['posts_per_page'] ) && 'auto' === $posts['posts_per_page'] ) {
                $posts_in_grid = $this->get_posts_in_grid( reset( $opts['grids'] ) );
                $posts['posts_per_page'] = ( $posts_in_grid ) ? $posts_in_grid : 10;
            }

            // Automatically set paged var if tile pagination is on
            if ( $opts['pagination'] ) {
                $paged_query_var = is_front_page() ? get_query_var( 'page' ) : get_query_var('paged');
                $posts['paged'] = $paged_query_var ? $paged_query_var : 1;
            }

            $posts = new \WP_Query( apply_filters( 'wp_tiles_get_posts_query', $posts ) );
        }

        // Is posts a WP_Query or Network_Query or similar? (enables pagination)
        $wp_query = false;
        if ( is_object( $posts ) && isset( $posts->posts ) && isset( $posts->max_num_pages ) ) {
            $wp_query = $posts;
            $posts = $wp_query->posts;
        }

        if ( empty( $posts ) )
            return false;

        //
        // OPTIONS
        //

        $opts['byline_color'] = $this->options->get_byline_color( $opts );
        $opts['colors'] = $this->options->get_colors( $opts );

        /**
         * Make sure carousel module isn't loaded in vain
         */
        if ( 'carousel' == $opts['link']
            && ( !class_exists( 'No_Jetpack_Carousel' ) && !class_exists( 'Jetpack_Carousel' ) ) ) {
            $opts['link'] = 'thickbox';
        }

        /**
         * Pagination
         */

        // Only allow pagination when we have a WP Query
        $opts['next_query'] = false;
        $next_page = false;
        if ( $wp_query ) {
            $max_page  = $wp_query->max_num_pages;
            $current_page = intval( $wp_query->get( 'paged', 1 ) );

            if ( $current_page < 1 )
                $current_page = 1;

            $next_page = $current_page + 1;

            if ( $next_page > $max_page )
                $next_page = false;

            // If AJAX pagination, sign the query and pass it to JS
            if ( $next_page && 'ajax' == $opts['pagination'] ) {
                $next_query = $wp_query->query;

                $max_page  = $wp_query->max_num_pages;

                if ( $next_page <= $max_page ) {

                    $next_query['paged'] = $next_page;

                    $opts['next_query'] = array(
                        'query' => $next_query,
                        'action' => Ajax::ACTION_GET_POSTS,
                        '_ajax_nonce' => $this->ajax->get_query_nonce( $next_query )
                    );
                    $opts['ajaxurl'] = admin_url( 'admin-ajax.php' );

                }
            }
        }

        /**
         * Pass the required info to the JS
         */
        $this->add_data_for_js( $wp_tiles_id, $opts );


        //
        // RENDER HTML
        //

        /**
         * Get the classes
         */
        $classes = array(
            ( 'top' == $opts['byline_align'] ) ? 'wp-tiles-byline-align-top' : 'wp-tiles-byline-align-bottom'
        );

        if ( !empty( $opts['byline_effect'] ) && in_array( $opts['byline_effect'], $this->options->get_allowed_byline_effects() ) )
            $classes = array_merge( $classes, array(
                'wp-tiles-byline-animated',
                'wp-tiles-byline-' . $opts['byline_effect']
            ) );

        if ( !empty( $opts['image_effect'] ) && in_array( $opts['image_effect'], $this->options->get_allowed_image_effects() )  )
            $classes = array_merge( $classes, array(
                'wp-tiles-image-animated',
                'wp-tiles-image-' . $opts['image_effect']
            ) );

        /**
         * Set extra container classes for major CSS changes
         */
        //$opts['extra_classes'] = $opts['extra_classes_grid_selector'] = array();

        //Legacy styles?
        if ( apply_filters( 'wp_tiles_use_legacy_styles', $this->options->get_option( 'legacy_styles' ) ) )
            $opts['extra_classes'][] = $opts['extra_classes_grid_selector'][] = 'wp-tiles-legacy';

        // Full width experiment
        if ( $opts['full_width'] )
            $opts['extra_classes'][] = 'wp-tiles-full-width';

        $opts['extra_classes'] = implode( ' ', apply_filters( 'wp_tiles_container_classes', $opts['extra_classes'] ) );
        $opts['extra_classes_grid_selector'] = implode( ' ', apply_filters( 'wp_tiles_grid_selector_classes', $opts['extra_classes_grid_selector'] ) );

        if ( !empty( $opts['extra_classes'] ) )
            $opts['extra_classes'] = ' ' . $opts['extra_classes'];

        if ( !empty( $opts['extra_classes_grid_selector'] ) )
            $opts['extra_classes_grid_selector'] = ' ' . $opts['extra_classes_grid_selector'];

        /**
         * Render the template
         *
         * POLICY: Though the PHP should remain readable at all times, getting clean
         * HTML output is nice. To strive to get clean HTML output, WP Tiles starts 8
         * spaces (2 tabs) from the wall, and leaves an empty line between each line
         * of HTML. Remeber that ?> strips a following newline, so always leave an
         * empty line after ?>.
         */
        ob_start();
        if ( count( $grid_names ) > 1 ) : ?>

        <div id="<?php echo $wp_tiles_id; ?>-templates" class="wp-tiles-templates<?php echo $opts['extra_classes_grid_selector'] ?>">

            <ul class="wp-tiles-template-selector">
            <?php foreach ( $grid_names as $slug => $name ) : ?>

                <li class="wp-tiles-template" data-grid="<?php echo $slug ?>"><?php echo $name; ?></li>
            <?php endforeach; ?>

            </ul>

        </div>
        <?php endif; ?>

        <div class="wp-tiles-container<?php echo $opts['extra_classes'] ?>">
        <?php if ( 'carousel' == $opts['link'] ):?>

            <?php echo apply_filters( 'gallery_style', '<div id="' . $wp_tiles_id . '" class="wp-tiles-grid gallery ' . implode( ' ', $classes ) . '">' ); ?>
        <?php else : ?>

            <div id="<?php echo $wp_tiles_id; ?>" class="wp-tiles-grid <?php echo implode( ' ', $classes ); ?>">
        <?php endif; ?>
                <?php $this->render_tile_html( $posts, $opts ) ?>

            </div>

        </div>
        <?php

        /**
         * Pagination
         **/
        if ( $next_page && 'ajax' === $opts['pagination'] && $opts['next_query'] ) : ?>

        <nav class="wp-tiles-pagination wp-tiles-pagination-ajax" id="<?php echo $wp_tiles_id; ?>-pagination">
            <a href="<?php next_posts( $max_page, true ) ?>"><?php echo apply_filters( 'wp_tiles_load_more_text', __( 'Load More', 'wp-tiles' ) ) ?></a>
        </nav>
        <?php elseif ( 'prev_next' === $opts['pagination'] ) : ?>
            <?php wp_tiles_prev_next_nav( $wp_query, $wp_tiles_id ); ?>

        <?php elseif ( 'paging' === $opts['pagination'] ) : ?>
            <?php wp_tiles_paging_nav( $wp_query, $wp_tiles_id ); ?>

        <?php endif;

        $out = ob_get_contents();
        ob_end_clean();

        return $out;
    }

    public function render_tile_html( $posts, $opts ) {

        foreach( $posts as $post ) :

            $img = false;
            if ( !$opts['text_only'] && $img = $this->get_first_image( $post, $opts['image_size'] ) ) {
                $wrapper_class = 'wp-tiles-tile-with-image';
            } elseif ( $opts['images_only'] ) {
                continue; // If text_only *and* image_only are enabled, the user should expect 0 tiles..

            } else {
                $wrapper_class = 'wp-tiles-tile-text-only';
            }

            if ( $opts['byline_template_textonly'] && ($opts['text_only'] || !$img ) ) {
                $byline = $this->render_byline( $opts['byline_template_textonly'], $post );

            } elseif ( $opts['byline_template'] ) {
                $byline = $this->render_byline( $opts['byline_template'], $post );

            } else {
                $byline = false;
            }

            // Tile Classes
            $tile_classes = array( 'wp-tiles-tile' );

            $categories = get_the_category( $post->ID );
            foreach( $categories as $category ) {
                $tile_classes[] = $category->slug;
            }

            if ( 'carousel' == $opts['link'] )
                $tile_classes[] = 'gallery-item';

            $tile_classes = array_unique( apply_filters( 'wp_tiles_tile_classes', $tile_classes, $post ) );

            // Link attributes
            $link_attributes = array();

            if ( $opts['link_new_window'] )
                $link_attributes['target'] = '_blank';

            $link_attributes = apply_filters( 'wp_tiles_link_attributes', $link_attributes );
            $link_attributes_string = "";
            foreach( $link_attributes as $att => $value ) {
                $link_attributes_string .= " $att='$value'";
            }

            ?>

                <div class='<?php echo implode( ' ', $tile_classes ) ?>' id='tile-<?php echo $post->ID ?>'>
                <?php if ( 'post' == $opts['link'] ) : ?>

                    <a href="<?php echo $this->_get_permalink( $post->ID ) ?>" title="<?php echo esc_attr( apply_filters( 'the_title', $post->post_title, $post->ID ) ) ?>"<?php echo $link_attributes_string ?>>
                <?php elseif ( 'file' == $opts['link'] ) : ?>

                    <a href="<?php echo $this->get_first_image( $post, 'full' ) ?>" title="<?php echo esc_attr( apply_filters( 'the_title', $post->post_title, $post->ID ) ) ?>"<?php echo $link_attributes_string ?>>
                <?php elseif ( 'thickbox' == $opts['link'] ) : ?>

                    <a href="<?php echo $this->get_first_image( $post, 'full' ) ?>" title="<?php echo esc_attr( strip_tags( $byline ) ) ?>" class="<?php echo esc_attr( apply_filters( 'wp_tiles_thickbox_class', 'thickbox' ) ) ?>" rel="<?php echo $this->tiles_id ?>"<?php echo $link_attributes_string ?>>
                <?php elseif ( 'carousel' == $opts['link'] ) : ?>

                    <a href="<?php echo $this->get_first_image( $post, 'full' ) ?>" title="<?php echo esc_attr( strip_tags( $byline ) ) ?>"<?php echo Gallery::get_carousel_image_attr( $post ) ?>>
                <?php endif; ?>

                        <article class='<?php echo $wrapper_class ?> wp-tiles-tile-wrapper' itemscope itemtype="http://schema.org/CreativeWork">
                        <?php if ( $img ) : ?>

                            <div class='wp-tiles-tile-bg'>

                                <img src='<?php echo $img ?>' class='wp-tiles-img' itemprop="image" />

                            </div>
                        <?php endif; ?>
                        <?php if ( $byline || !$opts['hide_title'] ) : ?>

                            <div class='wp-tiles-byline'>

                                <div class='wp-tiles-byline-wrapper'>
                                <?php if ( !$opts['hide_title'] ) : ?>

                                    <h4 itemprop="name" class="wp-tiles-byline-title"><?php echo apply_filters( 'the_title', $post->post_title, $post->ID ) ?></h4>
                                <?php endif; ?>
                                <?php if ( $byline ) : ?>

                                    <div class='wp-tiles-byline-content' itemprop="description">
                                        <?php echo $byline; ?>

                                    </div>
                                <?php endif; ?>

                                </div>

                            </div>
                        <?php endif; ?>

                        </article>
                <?php if ( $opts['link'] && 'none' != $opts['link'] ) : ?>

                    </a>
                <?php endif; ?>

                </div>
            <?php
        endforeach;
    }

        protected function _get_permalink( $post_id ) {
            $link = apply_filters( 'wp_tiles_permalink', false, $post_id );
            return ( $link ) ? esc_url( $link ) : get_permalink( $post_id );
        }

    protected function render_byline( $template, $post ) {
        // Only use below filter to change the byline on a per-post level
        $template = apply_filters( 'wp_tiles_byline_template_post', $template, $post );

        $tags = array(
            '%title%'   => apply_filters( 'the_title', $post->post_title, $post->ID ),
            '%content%' => apply_filters( 'the_content', strip_shortcodes( $post->post_content ) ),
            '%excerpt%' => $this->get_the_excerpt( $post ),
            '%date%'    => $this->get_the_date( $post ),
            '%link%'    => get_permalink( $post ),
        );

        // Only do the more expensive tags if needed
        if ( strpos( $template, '%featured_image%' ) !== false ) {
            $tags['%featured_image%'] = get_the_post_thumbnail( $post->ID );
        }

        if ( strpos( $template, '%featured_image_src%' ) !== false ) {
            $tags['%featured_image_src%'] = $this->get_first_image( $post, 'full' );
        }

        if ( strpos( $template, '%author%' ) !== false ) {
            $authordata = get_userdata( $post->post_author );
            $tags['%author%'] = apply_filters('the_author', is_object($authordata) ? $authordata->display_name : null);
        }

        // Default Taxonomies
        if ( strpos( $template, '%categories%' ) !== false ) {
            $categories = get_the_category( $post->ID );
            $tags['%categories%'] = implode( ', ', wp_list_pluck( $categories, 'name' ) );
        }

        if ( strpos( $template, '%category_links%' ) !== false ) {
            $tags['%category_links%'] = get_the_category_list( ', ', '', $post->ID );
        }

        if ( strpos( $template, '%tags%' ) !== false ) {
            $tags['%tags%'] = implode( ', ', wp_get_post_tags( $post->ID, array( "fields" => "names" ) ) );
        }

        if ( strpos( $template, '%tag_links%' ) !== false ) {
            $tags['%tag_links%'] = get_the_tag_list( '', ', ', '', $post->ID );
        }

        // Meta keys: %meta:META_KEY%
        // Tax list: %tax:TAXONOMY%
        // Tax list with links: %tax_links:TAXONOMY%
        $matches = array();
        if ( preg_match_all( '/%([a-z_-]+):([^%]+)%/', $template, $matches, PREG_SET_ORDER ) ) {

            foreach( $matches as $match ) {
                if ( 'meta' === $match[1]) {
                    $key = sanitize_key( $match[2] );
                    $tags["%meta:$match[2]%"] = implode( ', ', get_post_meta( $post->ID, $key ) );

                } elseif ( 'tax' === $match[1] || 'tax_links' === $match[1] ) {
                    $taxonomy = apply_filters( 'pre_term_name', $match[2] );
                    if ( 'tax' === $match[1] ) {
                        $terms = get_the_terms( $post->ID, $taxonomy );
                        $tags["%tax:$match[2]%"] = is_array( $terms ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '';

                    } else {
                        $tags["%tax_links:$match[2]%"] = get_the_term_list( $post->ID, $taxonomy, '', ', ', '' );

                    }

                } else {
                    $tag = apply_filters( 'wp_tiles_byline_tags_dynamic', false, $match[1], $match[2], $post, $template );
                    if ( false !== $tag )
                        $tags["%{$match[1]}:{$match[2]}%"] = $tag;

                }
            }
        }

        $tags = apply_filters( 'wp_tiles_byline_tags', $tags, $post, $template );

        $ret = str_replace( array_keys( $tags ), array_values( $tags ), $template );

        // Strip empty paragraphs and headings
        $ret = preg_replace( "/<(p|h[1-6])[^>]*>[\s|&nbsp;]*<\/(p|h[1-6])>/i", '', $ret );
        return !empty( $ret ) ? $ret : false;
    }

    /**
     * @todo Filter out vars we don't need
     */
    protected function add_data_for_js( $wp_tiles_id, $opts ) {
        static $enqueued = false;

        if ( !$enqueued ) {
            $this->enqueue_scripts();
            $this->enqueue_styles();

            $enqueued = true;
        }

        if ( 'thickbox' == $opts['link'] )
            add_thickbox();

        $opts['id'] = $wp_tiles_id;

        // Only pass these on to the JS
        $js_opts = array(
            'id', 'grids', 'breakpoint', 'small_screen_grid', 'padding',
            'byline_color', 'byline_height', 'colors', 'byline_opacity',
            'next_query', 'ajaxurl', 'animate_template', 'animate_init',
            'animate_resize', 'grid_selector_color', 'image_text_color',
            'text_color', 'link_new_window'
        );

        foreach( $js_opts as &$opt ) {
            $opt = isset( $opts[$opt] ) ? $opts[$opt] : null;
        }

        $this->data[$wp_tiles_id] = $opts;
    }

    public function add_data() {
        wp_localize_script( 'wp-tiles', 'wptilesdata', $this->data );
    }

    public function register_scripts() {

        $script_path = WP_TILES_ASSETS_URL . 'js/';
        $in_footer   = apply_filters( 'wp_tiles_js_in_footer', true );

        if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
            wp_register_script( 'tilesjs',  $script_path . 'tiles.js', array( "jquery" ), "2013-05-18", $in_footer );
            wp_register_script( 'jquery-dotdotdot',  $script_path . 'jquery.dotdotdot.js', array( "jquery" ),  "1.6.14", $in_footer );

            wp_register_script( 'wp-tiles', $script_path . 'wp-tiles.js', array( "jquery", "tilesjs", "jquery-dotdotdot" ), WP_TILES_VERSION, $in_footer );

        } else {
            wp_register_script( 'wp-tiles', $script_path . 'wp-tiles.min.js', array( "jquery" ), WP_TILES_VERSION, $in_footer );

        }

    }

    public function register_styles() {

        $stylesheet = WP_TILES_ASSETS_URL . 'css/wp-tiles.css';

        // In admin we want vanilla WP Tiles styles
        if ( !is_admin() ) {

            /**
             * Get the WP Tiles stylesheet
             *
             * @since 1.0
             * @param string Stylesheet location or false to disable separate css
             */
            $stylesheet = apply_filters( 'wp_tiles_stylesheet', $stylesheet );

            if ( false === $stylesheet )
                return;
        }

        wp_register_style( 'wp-tiles', $stylesheet, false, WP_TILES_VERSION );

        /**
         * Always enqueue stylesheet or defer loading until an instance of
         * WP Tiles has been detected
         *
         * @since 1.0
         * @param bool Always enqueue
         */
        if ( !is_admin() && apply_filters( 'wp_tiles_always_enqueue_stylesheet', false ) ) {
            $this->enqueue_styles();
        }
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'wp-tiles' );
        $this->add_action( 'wp_footer', 'add_data', 1 );
    }

    public function enqueue_styles() {
        wp_enqueue_style( 'wp-tiles' );
        do_action( 'wp_tiles_enqueue_styles' );
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

    /**
     * Returns the first image
     *
     * Plugins can hijack this method by hooking into 'pre_wp_tiles_image'.
     * @param WP_Post $post
     * @return string Image url
     *
     * @todo Statically caches found images in object to prevent double lookups,
     *        Maybe we should store this in object cache? The problem is that we
     *        can't invalidate the cache reliably..
     */
    public function get_first_image( $post, $size = false ) {
        static $found = array();

        $allowed_sizes = get_intermediate_image_sizes();
        $allowed_sizes[] = 'full';

        if ( !in_array( $size, $allowed_sizes ) || !$size )
            $size = $this->options->get_option( 'image_size' );

        // Also the option *could* in theory be wrong
        if ( !in_array( $size, $allowed_sizes ) )
            $size = $this->options->get_defaults( 'image_size' );

        // @todo legacy filter: wp-tiles-image-size
        $size = apply_filters( 'wp_tiles_image_size', $size, $post );

        // Allow plugins to hijack image loading
        $src = apply_filters( 'pre_wp_tiles_image', false, $post, $size );
        if ( false !== $src )
            return $src;

        if ( !isset( $found[$post->ID] ) )
            $found[$post->ID] = array();

        if ( !isset( $found[$post->ID][$size] ) )
            $found[$post->ID][$size] = $this->_find_the_image( $post, $size );

        return $found[$post->ID][$size];
    }

        /**
         * Finds the first relevant image to a post
         *
         * Searches for a featured image, then the first attached image, then the
         * first image in the source.
         *
         * @param WP_Post $post
         * @return string Source
         * @sice 0.5.2
         */
        private function _find_the_image( $post, $size ) {
            if ( isset( $post->image_url ) && $post->image_url )
                return $post->image_url;

            $image_source = $this->options->get_option( 'image_source' );

            if ( 'attachment' === get_post_type( $post->ID ) ) {
                $image = wp_get_attachment_image_src( $post->ID, $size, false );
                return $image[0];
            }

            if ( 'attachment_only' == $image_source )
                return '';

            if ( $post_thumbnail_id = get_post_thumbnail_id( $post->ID ) ) {
                $image = wp_get_attachment_image_src( $post_thumbnail_id, $size, false );
                return $image[0];
            }

            if ( 'featured_only' == $image_source )
                return '';

            $images = get_children( array(
                'post_parent'    => $post->ID,
                'numberposts'    => 1,
                'post_mime_type' => 'image'
            ) );

            if ( !empty( $images ) ) {
                $images = current( $images );
                $src    = wp_get_attachment_image_src( $images->ID, $size );
                return $src[0];
            }

            if ( 'attached_only' == $image_source )
                return '';

            if ( !empty( $post->post_content ) ) {
                if ( class_exists( 'DOMXPath' ) && class_exists( 'DOMDocument' ) ) {
                    $xpath = new \DOMXPath( @\DOMDocument::loadHTML( $post->post_content ) );
                    $src   = $xpath->evaluate( "string(//img/@src)" );
                    return $src;
                }
            }
            return '';
        }


    public function get_grids( $query = false ) {
        // Check if this is already a grid
        // Happens when default is passed through the shortcode
        if ( is_array( $query ) && is_array( reset( $query ) ) )
                return $query;

        $posts = $this->_get_grid_posts( $query );

        // If no posts are found, use fallback grid
        if ( empty( $posts ) )
            return array(
                'Default' => Admin\GridTemplates::get_default_template()
            );

        $grids = array();
        foreach( $posts as $post ) {
            $grids[$post->post_title] = $this->format_grid( $post->post_content );
        }

        return $grids;
    }

        protected function _get_grid_posts( $query = false ) {
            if ( $query && 'all' !== $query ) {
                if ( !is_array( $query ) ) {
                    $query = strpos( $query, ',' ) !== false ? explode( ',', $query ) : array( $query );
                }

                // Are we dealing with titles?
                if ( !is_numeric( reset( $query ) ) ) {
                    $query = $this->_get_grid_ids_by_titles( $query );
                }

                if ( $query ) {
                    $query = array(
                        'post_type' => self::GRID_POST_TYPE,
                        'posts_per_page' => -1,
                        'post__in' => $query,
                        'orderby' => 'post__in'
                    );
                    $posts = get_posts( $query );

                    if ( $posts )
                        return $posts;
                }
            }

            // If no posts are found, get all of them
            return get_posts( array(
                'post_type' => self::GRID_POST_TYPE,
                'posts_per_page' => -1,
                'orderby' => 'menu_order'
            ) );
        }

        /**
         * @todo DB Query. Cache! Can be invalidated on post type save.
         */
        private function _get_grid_ids_by_titles( $titles ) {
            global $wpdb;

            if ( empty( $titles) )
                return false;

            $titles = array_map( 'trim', $titles );

            $cache_key = md5( json_encode( array_values( $titles ) ) );
            $cache = wp_cache_get( 'grids_by_titles', self::CACHEGROUP );

            if ( !$cache )
                $cache = array();

            if ( !isset( $cache[$cache_key] ) ) {

                $titles = esc_sql( $titles );
                $post_title_in_string = "'" . implode( "','", $titles ) . "'";

                $sql = $wpdb->prepare( "
                    SELECT ID
                    FROM $wpdb->posts
                    WHERE post_title IN ($post_title_in_string)
                    AND post_type = %s
                    ORDER BY FIELD( {$wpdb->posts}.post_title, $post_title_in_string )
                ", self::GRID_POST_TYPE );

                $ids = $wpdb->get_col( $sql );

                $cache[$cache_key] = $ids;
                wp_cache_set( 'grids_by_titles', $cache, self::CACHEGROUP );
            }

            return $cache[$cache_key];
       }

       public function invalidate_grid_cache() {
           wp_cache_delete( 'grids_by_titles', self::CACHEGROUP );
       }

    /**
     * Takes an array of grids and returns a sanitized version that can be passed
     * to the JS
     *
     * @param array $grids
     * @return array
     */
    public function sanitize_grid_keys( $grids ) {
        $ret = array();
        foreach( $grids as $name => $grid ) {
            $ret[sanitize_title($name)] = $grid;
        }

        return $ret;
    }

    /**
     * Takes a grid and formats it for insertion in the JS
     *
     * Explodes the grid on newlines if it's not an array and trims every line
     *
     * @param string|array $grid
     * @return array
     */
    public function format_grid( $grid ) {
        if ( !is_array( $grid ) )
            $grid = explode( "\n", str_replace( "|", "\n", $grid ) );

        $grid = array_map( 'trim', $grid );

        return $grid;
    }

    public function get_default_grid_title() {
        if ( $default_grid = $this->options->get_option( 'default_grid' ) ) {
            $title = get_the_title( $this->options->get_option( 'default_grid' ) );
            if ( $title )
                return $title;
        }

        $grids = $this->get_grids();
        if ( empty( $grids ) ) {
            return '';
        }

        $names = array_keys( $grids );
        return reset( $names );
    }

    public function get_posts_in_grid( $grid ) {
        $last_row = false;
        $letters = array();

        foreach( $grid as $line ) {
            $matches = array();
            preg_match_all("/[^ ]/", $line, $matches );
            $line = reset( $matches );

            foreach( $line as $index => $letter ) {
                // Letter has occurred
                if ( '.' !== $letter && in_array( $letter, $letters ) ) {

                    $is_adjacent = $index !== 0 && $line[$index-1] === $letter;
                    $is_beneath  = $last_row && $last_row[$index] === $letter;

                    if ( !$is_adjacent && !$is_beneath )
                        $letters[] = $letter;

                } else {
                    $letters[] = $letter;
                }
            }

            $last_row = $line;
        }

        return count( $letters );
    }

    public static function on_plugin_activation() {
        wp_tiles()->register_post_type();
        Admin\GridTemplates::install_default_templates();
    }

}