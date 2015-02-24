<?php namespace WPTiles\Admin;

use WPTiles\Helper;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Admin
{
    const CONTEXT_SHORTCODE = 'shortcode';
    const CONTEXT_OPTIONS   = 'options';

    const PAGE_SLUG = 'wp-tiles';

    public static $context = self::CONTEXT_OPTIONS;

    /**
     * @var \VP_Option
     */
    public static $options;

    /**
     * @var \VP_ShortcodeGenerator
     */
    public static $shortcode_generator;

    public static function setup() {
        \VP_Security::instance()->whitelist_function( 'wp_tiles_preview_tile' );

        self::$options             = self::setup_options();
        self::$shortcode_generator = self::setup_shortcode_generator();

        GridTemplates::get_instance();

        add_action( 'wp_tiles_after_admin_page', array( __CLASS__, 'render_about_box' ) );
    }

    public static function render_about_box() {
        include WP_TILES_DIR . 'assets/views/about.php';
        echo '<script>jQuery(function($){ $("#about-wp-tiles").insertAfter("#vp-copyright"); });</script>';
    }

    public static function is_shortcode() {
        return self::CONTEXT_SHORTCODE === self::$context;
    }

    public static function is_options() {
        return self::CONTEXT_OPTIONS === self::$context;
    }

    public static function setup_shortcode_generator() {
        self::$context = self::CONTEXT_SHORTCODE;

        $tmpl_sg = array(
            'name'           => 'wp_tiles_shortcode',
            'template'       => self::get_shortcode_options(),
            'modal_title'    => __( 'WP Tiles Shortcodes', 'wp-tiles' ),
            'button_title'   => __( 'WP Tiles', 'wp-tiles' ),
            'types'          => array( '*' ),
            'main_image'     => WP_TILES_ASSETS_URL . 'images/editor-button-main.png',
            'sprite_image'   => WP_TILES_ASSETS_URL . 'images/editor-button.png',
        );
        return new \VP_ShortcodeGenerator( $tmpl_sg );
    }

        private static function get_shortcode_options() {

            $controls = array();
            $controls[__( 'Tiles', 'wp-tiles' )] = array(
                'elements' => array(
                    'wp_tiles'    => array(
                        'title' => __( '[wp-tiles] WP Tiles Shortcode', 'wp-tiles' ),
                        'code'  => '[wp-tiles]',
                        'attributes' => array_merge( Controls::query_basic(), Controls::single_grid() )
                    ),
                    'wp_tiles_basic_query' => array(
                        'title' => __( 'Tiles with custom Grid options', 'wp-tiles' ),
                        'code' => '[wp-tiles]',
                        'attributes' => array_merge( Controls::query_basic(), Controls::grids() )
                    ),
                    'wp_tiles_select_posts' => array(
                        'title' => __( 'Select Specific Posts to display', 'wp-tiles' ),
                        'code' => '[wp-tiles]',
                        'attributes' => array_merge( Controls::query_manual(), Controls::single_grid() )
                    ),
                ),
            );

            $controls[__('Presets', 'wp-tiles')] = array(
                'elements' => array(
                    'wp_tiles_last_20_posts' => array(
                        'title' => __( '<em>Preset:</em> Last 20 Blog Posts', 'wp-tiles' ),
                        'code'  => "[wp-tiles post_type='post' posts_per_page=20 orderby='date' order='DESC']",
                    ),
                    'wp_tiles_related_tag' => array(
                        'title' => __( '<em>Preset:</em> Display posts with same tags as current post', 'wp-tiles' ),
                        'code'  => "[wp-tiles related_in_taxonomy='tag']",
                    ),
                    'wp_tiles_related_category'    => array(
                        'title' => __( '<em>Preset:</em> Display posts with same categories as current post', 'wp-tiles' ),
                        'code'  => "[wp-tiles related_in_taxonomy='category']",
                    ),
                )
            );

            $controls[__( 'Galleries', 'wp-tiles')] = array(
                'elements' => array(
                    'gallery' => array(
                        'title' => __( 'Tiled Gallery - display images attached to current post', 'wp-tiles'),
                        'code'  => '[gallery tiles=yes]',
                        'attributes' => Controls::gallery_current()
                    ),
                    'gallery_other' => array(
                        'title' => __( 'Tiled Gallery other post - display images attached to another post', 'wp-tiles' ),
                        'code'  => '[gallery tiles=yes]',
                        'attributes' => Controls::gallery_select_post()
                    ),
                )
            );

            $controls[__( 'Advanced', 'wp-tiles' )] = array(
                'elements' => array(
                    'custom_query'    => array(
                        'title' => __( 'Custom Query', 'wp-tiles' ),
                        'code'  => '[wp-tiles]',
                        'attributes' => array_merge( Controls::query() )
                    ),
                    'custom_query_and_display' => array(
                        'title' => __( 'Custom Query and Grid Options', 'wp-tiles' ),
                        'code'  => '[wp-tiles]',
                        'attributes' => array_merge( Controls::query(), Controls::grids() )
                    ),
                ),
            );

            return $controls;
        }

    private static function setup_options() {
        self::$context = self::CONTEXT_OPTIONS;

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles' ) );
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_actions' ) );

        return new \VP_Option( array(
            'is_dev_mode'           => false,
            'option_key'            => 'wp_tiles',
            'page_slug'             => self::PAGE_SLUG,
            'template'              => array(
                'title' => __( 'WP Tiles', 'wp-tiles' ),
                'logo'  => WP_TILES_ASSETS_URL . '/images/wp-tiles-logo.png',
                'menus' => self::_get_menus()
            ),
            //'menu_page'             => 'edit.php?post_type=grid_template',
            'menu_page'             => array(
                'icon_url' => 'dashicons-screenoptions',
            ),
            'priority'              => 9, // Before register_post_type sets the submenu
            'use_auto_group_naming' => true,
            'use_util_menu'         => true,
            'minimum_role'          => apply_filters( 'wp_tiles_capability', 'manage_options' ),
            'layout'                => 'fixed',
            'page_title'            => __( 'WP Tiles', 'wp-tiles' ),
            'menu_label'            => __( 'WP Tiles', 'wp-tiles' ),
        ) );

    }

        public static function add_admin_actions() {

            $hookname = \get_plugin_page_hookname( self::$options->get_page_slug(), '' );
            \add_action( $hookname, array( __CLASS__, 'do_before_render_admin_page' ), 9 );
            \add_action( $hookname, array( __CLASS__, 'do_after_render_admin_page' ), 11 );

        }

        public static function do_before_render_admin_page() {
            do_action( 'wp_tiles_before_admin_page' );
        }

        public static function do_after_render_admin_page() {
            do_action( 'wp_tiles_after_admin_page' );
        }

        public static function enqueue_admin_styles( $hook_suffix ) {
            if ( $hook_suffix === self::$options->get_hook_suffix() ) {
                wp_tiles()->enqueue_styles();
            }
        }

    private static function _get_menus() {
        return array(
            array(
                'title' => __( 'Tile Designer', 'wp-tiles' ),
                'name'  => 'Tiles',
                'icon'     => 'font-awesome:fa-pencil-square-o',
                'controls' => array(
                    array(
                        'type' => 'notebox',
                        'name' => 'notice_tile_designer',
                        'label' => __('Using the Tile Designer', 'wp-tiles'),
                        'description' => __( 'In this panel you can change the look-and-feel of tiles with an image background. '
                            . 'You can directly preview your changes in the tile preview below (remember to hover your mouse over '
                            . 'the preview if you want to see the effects).', 'wp-tiles' ),
                        'status' => 'normal',
                    ),
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Tile Preview', 'wp-tiles' ),
                        'name'        => 'tile_preview_section',
                        'fields'      => Controls::tile_preview()
                    ),
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Options', 'wp-tiles' ),
                        'name'        => 'tile_designer_section',
                        'description' => __( "", 'wp-tiles' ),
                        'fields'      => Controls::tile_designer()
                    ),
                )
            ),
            array(
                'title' => __( 'Grids', 'wp-tiles' ),
                'name'  => 'Grids',
                'icon'     => 'font-awesome:fa-th-large',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Grid Templates', 'wp-tiles' ),
                        'name'        => 'grid_templates_section',
                        'description' => __( 'Select your default layout options', 'wp-tiles' ),
                        'fields'      => Controls::grids()
                    )
                )
            ),
            array(
                'title' => __( 'Animations and Colors', 'wp-tiles' ),
                'name'  => 'Animations-Colors',
                'icon'     => 'font-awesome:fa-tint',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Colors', 'wp-tiles' ),
                        'name'        => 'colors_section',
                        'fields'      => Controls::colors()
                    ),
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Tile Animations', 'wp-tiles' ),
                        'name'        => 'animations_section',
                        'fields'      => Controls::animations()
                    ),
                )

            ),
            array(
                'title' => __( 'Byline Content', 'wp-tiles' ),
                'name'  => 'Byline',
                'icon'     => 'font-awesome:fa-list-alt',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Byline Links and Content', 'wp-tiles' ),
                        'name'        => 'byline_layout_section',
                        'description' => __( "The byline the text block that is displayed on the tile.", 'wp-tiles' ),
                        'fields'      => Controls::byline_layout()
                    ),
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Byline Template', 'wp-tiles' ),
                        'name'        => 'byline_template_section',
                        'description' => __( "", 'wp-tiles' ),
                        'fields'      => Controls::byline_template()
                    ),
                )
            ),
            array(
                'title' => __( 'Image Settings', 'wp-tiles' ),
                'name'  => 'Images',
                'icon'     => 'font-awesome:fa-camera-retro',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Tile Images', 'wp-tiles' ),
                        'name'        => 'images_section',
                        'description' => __( "If an image is found, it will be displayed as the tile background.", 'wp-tiles' ),
                        'fields'      => Controls::images()
                    ),
                )
            ),
            /*array(
                'title' => 'Default Query',
                'name'  => 'Query',
                'icon'     => 'font-awesome:fa-cog',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Query', 'wp-tiles' ),
                        'name'        => 'query_section',
                        'description' => __( '', 'wp-tiles' ),
                        'fields'      => Controls::query()
                    ),
                )
            )*/
        );
    }

    public static function preview_tile( $atts = array() ) {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['params'] ) && count( $_POST['params'] ) === 8 ) {

            $atts = array_combine( array(
                'byline_height_auto',
                'byline_height',
                'byline_color',
                'byline_opacity',
                'byline_align',
                'byline_effect',
                'image_effect',
                'image_text_color'
            ), $_POST['params'] );

            // Sanitize!
            $atts['byline_height'] = (int) $atts['byline_height'];

            if ( 'random' == $atts['byline_color'] || empty( $atts['byline_color'] ) ) {
                $atts['byline_color'] = wp_tiles()->options->get_option( 'color_1' );
            }

            $atts['byline_opacity'] = (float) $atts['byline_opacity'];
            $atts['byline_align'] = 'top' == $atts['byline_align'] ? 'top' : 'bottom';

            $atts['id'] = 'tile-ajax-preview';

        } else {
            $atts = shortcode_atts( array(
                'byline_height_auto' => wp_tiles()->options->get_option( 'byline_height_auto' ),
                'byline_height'      => wp_tiles()->options->get_option( 'byline_height' ),
                'byline_color'       => wp_tiles()->options->get_option( 'byline_color' ),
                'byline_opacity'     => wp_tiles()->options->get_option( 'byline_opacity' ),
                'byline_align'       => wp_tiles()->options->get_option( 'byline_align' ),
                'byline_effect'      => wp_tiles()->options->get_option( 'byline_effect' ),
                'image_effect'       => wp_tiles()->options->get_option( 'image_effect' ),
                'image_text_color'   => wp_tiles()->options->get_option( 'image_text_color' ),
                'id'                 => 'tile-preview'
            ), $atts );

        }

        $id = $atts['id'];

        $byline_height_auto = wp_tiles()->options->boolean( $atts['byline_height_auto'] );
        $byline_height      = $atts['byline_height'];
        $byline_color       = Helper::hex_to_rgba( $atts['byline_color'], $atts['byline_opacity'], true );
        $byline_align       = $atts['byline_align'];
        $byline_effect      = $atts['byline_effect'];
        $image_effect       = $atts['image_effect'];
        $image_text_color   = $atts['image_text_color'];

        /**
         * ANIMATION CLASSES
         */
        $classes = array( 'wp-tiles-byline-align-' . $byline_align );

        if ( !empty( $byline_effect ) && in_array( $byline_effect, wp_tiles()->options->get_allowed_byline_effects() )  )
            $classes = array_merge( $classes, array(
                'wp-tiles-byline-animated',
                'wp-tiles-byline-' . $byline_effect
            ) );

        if ( !empty( $image_effect ) && in_array( $image_effect, wp_tiles()->options->get_allowed_image_effects() )  )
            $classes = array_merge( $classes, array(
                'wp-tiles-image-animated',
                'wp-tiles-image-' . $image_effect
            ) );


        ob_start();
        ?>
        <div class="wp-tiles-container wp-tiles-tile-demo wp-tiles-loaded" id="<?php echo $id ?>">

            <div id="wp_tiles_1" class="wp-tiles-grid <?php echo implode( ' ', $classes ); ?>">

                <div class="wp-tiles-tile" id="tile-1">

                    <a href="javascript:void(0)" title="Animation Demo">

                        <article class="wp-tiles-tile-with-image wp-tiles-tile-wrapper" itemscope itemtype="http://schema.org/CreateWork">

                            <div class="wp-tiles-tile-bg"></div>

                            <div class="wp-tiles-byline">

                                <h4 itemprop="name" class="wp-tiles-byline-title"><?php _e( 'Byline Preview', 'wp-tiles' ); ?></h4>

                                <div class="wp-tiles-byline-content" itemprop="description">
                                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in...
                                </div>

                            </div>

                        </article>

                    </a>
                </div>

            </div>

        </div>
        <style>
            #<?php echo $id ?> .wp-tiles-byline {
                background: <?php echo $byline_color ?>;
                <?php if ( $byline_height_auto ) : ?>max-<?php endif; ?>height: <?php echo $byline_height; ?>%;
                <?php if ( $image_text_color ) : ?>color: <?php echo $image_text_color ?>;<?php endif; ?>
            }
        </style>
        <?php
        $ret = ob_get_contents();
        ob_end_clean();

        return $ret;
    }
}