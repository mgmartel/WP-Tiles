<?php namespace WPTiles\Admin;

use WPTiles\Helper;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Admin
{
    const CONTEXT_SHORTCODE = 'shortcode';
    const CONTEXT_OPTIONS   = 'options';

    public static $context = self::CONTEXT_OPTIONS;

    public static function is_shortcode() {
        return self::CONTEXT_SHORTCODE === self::$context;
    }

    public static function is_options() {
        return self::CONTEXT_OPTIONS === self::context;
    }

    public static function setup_shortcode_generator() {
        self::$context = self::CONTEXT_SHORTCODE;

        $tmpl_sg = array(
            'name'           => 'wp_tiles_shortcode',
            'template'       => self::get_shortcode_options(),
            'modal_title'    => __( 'WP Tiles Shortcodes', 'vp_textdomain' ),
            'button_title'   => __( 'WP Tiles', 'vp_textdomain' ),
            'types'          => array( '*' ),
            'main_image'     => VP_IMAGE_URL . '/vp_shortcode_icon.png',
            'sprite_image'   => VP_IMAGE_URL . '/vp_shortcode_icon_sprite.png',
        );
        return new \VP_ShortcodeGenerator( $tmpl_sg );
    }

        private static function get_shortcode_options() {
            return array(
                'Custom Tiles' => array(
                    'elements' => array(
                        'custom_layout'    => array(
                            'title' => __( 'Custom Layout', 'wp-tiles' ),
                            'code'  => '[wp-tiles]',
                            'attributes' => Controls::grids()
                        ),
                        'custom_query'    => array(
                            'title' => __( 'Custom Query', 'wp-tiles' ),
                            'code'  => '[wp-tiles]',
                            'attributes' => Controls::query()
                        ),
                    ),
                ),
                'Basic Tiles' => array(
                    'elements' => array(
                        'wp_tiles'    => array(
                            'title' => __( 'WP Tiles - Default options', 'wp-tiles' ),
                            'code'  => '[wp-tiles]',
                        ),
                        'wp_tiles_last_20_posts'    => array(
                            'title' => __( 'WP Tiles - Last 20 Blog Posts', 'wp-tiles' ),
                            'code'  => "[wp-tiles post_type=post posts_per_page=20 orderby=date order=DESC]",
                        ),
                    ),
                ),
            );
        }

    public static function setup() {
        \VP_Security::instance()->whitelist_function( 'wp_tiles_preview_tile');

        self::setup_options();
        self::setup_shortcode_generator();
        GridTemplates::get_instance();
    }

    private static function setup_options() {
        self::$context = self::CONTEXT_OPTIONS;

        add_action( 'admin_enqueue_scripts', function( $hook_suffix ){
            if ( $hook_suffix === 'grid_template_page_wp-tiles' ) {
                wp_tiles()->enqueue_styles();
            }
        });

        return new \VP_Option( array(
            'is_dev_mode'           => false, // dev mode, default to false
            'option_key'            => 'wp_tiles', // options key in db, required
            'page_slug'             => 'wp-tiles', // options page slug, required
            'template'              => array(
                'title' => __( 'WP Tiles', 'wp-tiles' ),
                'logo'  => false,
                'menus' => self::_get_menus()
            ), // template file path or array, required
            //'menu_page'             => array(), // parent menu slug or supply `array` (can contains 'icon_url' & 'position') for top level menu
            'menu_page'             => 'edit.php?post_type=grid_template', // parent menu slug or supply `array` (can contains 'icon_url' & 'position') for top level menu
            'use_auto_group_naming' => true, // default to true
            'use_util_menu'         => true, // default to true, shows utility menu
            'minimum_role'          => 'manage_options', // default to 'edit_theme_options'
            'layout'                => 'fixed', // fluid or fixed, default to fixed
            'page_title'            => __( 'WP Tiles', 'wp-tiles' ), // page title
            'menu_label'            => __( 'Settings', 'wp-tiles' ), // menu label
        ) );
    }

    private static function _get_menus() {
        return array(
            array(
                'title' => __( 'Tile Designer', 'wp-tiles' ),
                'name'  => __( 'Tiles', 'wp-tiles' ),
                'icon'     => 'font-awesome:fa-pencil-square-o',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Tile Preview', 'vp_textdomain' ),
                        'name'        => 'tile_preview_section',
                        'description' => __( "Hover over the image to preview the hover effects you set below.", 'vp_textdomain' ),
                        'fields'      => Controls::tile_preview()
                    ),
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Options', 'vp_textdomain' ),
                        'name'        => 'tile_designer_section',
                        'description' => __( "", 'vp_textdomain' ),
                        'fields'      => Controls::tile_designer()
                    ),
                )
            ),
            array(
                'title' => __( 'Grids, Animations and Colors', 'wp-tiles' ),
                'name'  => __( 'Defaults', 'wp-tiles' ),
                'icon'     => 'font-awesome:fa-tint',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Grid Templates', 'vp_textdomain' ),
                        'name'        => 'grid_templates_section',
                        'description' => __( 'Select your default layout options', 'vp_textdomain' ),
                        'fields'      => Controls::grids()
                    ),
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Animations', 'vp_textdomain' ),
                        'name'        => 'animations_section',
                        //'description' => __( '', 'vp_textdomain' ),
                        'fields'      => Controls::animation()
                    ),
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Colors', 'vp_textdomain' ),
                        'name'        => 'colors_section',
                        'description' => __( 'Select the default colors to use for tiles without images.', 'vp_textdomain' ),
                        'fields'      => Controls::colors()
                    )
                )
            ),
            array(
                'title' => __( 'Byline Editor', 'wp-tiles' ),
                'name'  => __( 'Byline', 'wp-tiles' ),
                'icon'     => 'font-awesome:fa-list-alt',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Tile Byline', 'vp_textdomain' ),
                        'name'        => 'byline_layout_section',
                        'description' => __( "The byline is all text that is displayed on the tile.", 'vp_textdomain' ),
                        'fields'      => Controls::byline_layout()
                    ),
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Byline Template', 'vp_textdomain' ),
                        'name'        => 'byline_template_section',
                        'description' => __( "", 'vp_textdomain' ),
                        'fields'      => Controls::byline_template()
                    ),
                )
            ),
            array(
                'title' => __( 'Image Settings', 'wp-tiles' ),
                'name'  => __( 'Images', 'wp-tiles' ),
                'icon'     => 'font-awesome:fa-camera-retro',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Tile Images', 'vp_textdomain' ),
                        'name'        => 'images_section',
                        'description' => __( "If an image is found, it will be displayed as the tile background.", 'vp_textdomain' ),
                        'fields'      => Controls::images()
                    ),
                )
            ),
            array(
                'title' => 'Default Query',
                'name'  => 'Query',
                'icon'     => 'font-awesome:fa-cog',
                'controls' => array(
                    array(
                        'type'       => 'section',
                        'title'       => __( 'Query', 'vp_textdomain' ),
                        'name'        => 'query_section',
                        'description' => __( '', 'vp_textdomain' ),
                        'fields'      => Controls::query()
                    ),
                )
            )
        );
    }

    public static function preview_tile() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            list( $byline_height, $byline_color, $byline_opacity, $byline_align, $byline_effect, $image_effect ) = $_POST['params'];

            // Sanitize!
            $byline_height = (int) $byline_height;

            if ( 'random' == $byline_color || empty( $byline_color ) ) {
                $byline_color = wp_tiles()->options->get_option( 'color_1' );
            }
            $byline_color = Helper::hex_to_rgba( $byline_color, $byline_opacity, true );

            $byline_opacity = (float) $byline_opacity;
            $byline_align = 'top' == $byline_align ? 'top' : 'bottom';

        } else {
            $byline_height  = wp_tiles()->options->get_option( 'byline_height' );
            $byline_color   = wp_tiles()->options->get_option( 'byline_color' );
            $byline_opacity = wp_tiles()->options->get_option( 'byline_opacity' );
            $byline_align   = wp_tiles()->options->get_option( 'byline_align' );
            $byline_effect  = wp_tiles()->options->get_option( 'byline_effect' );
            $image_effect   = wp_tiles()->options->get_option( 'image_effect' );

        }

        /**
         * ANIMATION CLASSES
         */
        $classes = array( 'wp-tiles-byline-align-' . $byline_align );

        if ( !empty( $byline_effect ) && in_array( $byline_effect, wp_tiles()->get_allowed_byline_effects() )  )
            $classes = array_merge( $classes, array(
                'wp-tiles-byline-animated',
                'wp-tiles-byline-' . $byline_effect
            ) );

        if ( !empty( $image_effect ) && in_array( $image_effect, wp_tiles()->get_allowed_image_effects() )  )
            $classes = array_merge( $classes, array(
                'wp-tiles-image-animated',
                'wp-tiles-image-' . $image_effect
            ) );


        ob_start();
        ?>
        <div class="wp-tiles-container wp-tiles-tile-demo">

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
            .wp-tiles-container.wp-tiles-tile-demo .wp-tiles-byline {
                background: <?php echo $byline_color ?>;
                height: <?php echo $byline_height; ?>%;
            }
        </style>
        <?php
        $ret = ob_get_contents();
        ob_end_clean();

        return $ret;
    }
}