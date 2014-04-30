<?php namespace WPTiles;

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
                            'attributes' => self::_get_controls_grids()
                        ),
                        'custom_query'    => array(
                            'title' => __( 'Custom Query', 'wp-tiles' ),
                            'code'  => '[wp-tiles]',
                            'attributes' => self::_get_controls_query()
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
        self::$context = self::CONTEXT_OPTIONS;

        $tmpl_opt = array(
            'title' => 'WP Tiles Title',
            'logo'  => '',
            'menus' => array(
                array(
                    'title' => 'Tile Defaults',
                    'name'  => 'Defaults',
                    'icon'     => 'font-awesome:fa-magic',
                    'controls' => array(
                        array(
                            'type'       => 'section',
                            'title'       => __( 'Grid Templates', 'vp_textdomain' ),
                            'name'        => 'grid_templates_section',
                            'description' => __( 'Select your default layout options', 'vp_textdomain' ),
                            'fields'      => self::_get_controls_grids()
                        ),
                        array(
                            'type'       => 'section',
                            'title'       => __( 'Byline', 'vp_textdomain' ),
                            'name'        => 'byline_section',
                            'description' => __( 'The byline is the content line shown on every tile.', 'vp_textdomain' ),
                            'fields'      => self::_get_controls_byline()
                        ),
                        array(
                            'type'       => 'section',
                            'title'       => __( 'Colors', 'vp_textdomain' ),
                            'name'        => 'colors_section',
                            'description' => __( 'Select the default colors to use for tiles without images.', 'vp_textdomain' ),
                            'fields'      => self::_get_controls_colors()
                        )
                    )
                ),
                array(
                    'title' => 'Default Query',
                    'name'  => 'Query',
                    'icon'     => 'font-awesome:fa-magic',
                    'controls' => array(
                        array(
                            'type'       => 'section',
                            'title'       => __( 'Query', 'vp_textdomain' ),
                            'name'        => 'query_section',
                            'description' => __( '', 'vp_textdomain' ),
                            'fields'      => self::_get_controls_query()
                        ),
                    )
                )
            )
        );

        return new \VP_Option( array(
            'is_dev_mode'           => false, // dev mode, default to false
            'option_key'            => 'wp_tiles', // options key in db, required
            'page_slug'             => 'wp-tiles', // options page slug, required
            'template'              => $tmpl_opt, // template file path or array, required
            'menu_page'             => array(), // parent menu slug or supply `array` (can contains 'icon_url' & 'position') for top level menu
            'use_auto_group_naming' => true, // default to true
            'use_util_menu'         => true, // default to true, shows utility menu
            'minimum_role'          => 'manage_options', // default to 'edit_theme_options'
            'layout'                => 'fluid', // fluid or fixed, default to fixed
            'page_title'            => __( 'WP Tiles', 'wp-tiles' ), // page title
            'menu_label'            => __( 'WP Tiles', 'wp-tiles' ), // menu label
        ) );
    }

    //
    // CONTROLS
    //

        private static function _get_controls_grids() {
            $grid_callback = ( self::is_shortcode() ) ? array( __CLASS__, 'get_grids_names' ) : array( __CLASS__, 'get_grids' );

            $controls   = array();
            $controls[] = array(
                'type'        => 'sorter',
                'name'        => 'grids',
                'label'       => __( 'Grids', 'vp_textdomain' ),
                'description' => __( 'Select which Grids to use', 'vp_textdomain' ),
                'default'     => '{{all}}',
                'items'       => array(
                    'data' => array(
                        array(
                            'source' => 'function',
                            'value'  => $grid_callback,
                        ),
                    ),
                ),
            );

            if ( !self::is_shortcode() ) {
                $controls[] = array(
                    'type'        => 'toggle',
                    'name'        => 'small_screen_enabled',
                    'label'       => __( 'Different template on small screens?', 'vp_textdomain' ),
                    'description' => __( "Yay or nay? What's it going to be?", 'vp_textdomain' ),
                    'default'     => '1',
                );
            }

            $controls[] = array(
                'type'        => 'select',
                'name'        => 'small_screen_grid',
                'label'       => __( 'Small Screen Grid', 'vp_textdomain' ),
                'description' => __( 'Select the grid to use on small screens.', 'vp_textdomain' ),
                'default'     => '{{last}}',
                'items'       => array(
                    'data' => array(
                        array(
                            'source' => 'function',
                            'value'  => $grid_callback,
                        ),
                    ),
                ),
                'dependency'  => ( self::is_shortcode() ) ? false : array(
                    'field'    => 'small_screen_enabled',
                    'function' => 'vp_dep_boolean',
                ),
            );
            $controls[] = array(
                'type'        => 'textbox',
                'name'        => 'small_screen_breakpoint',
                'label'       => __( 'Small Screen Breakpoint', 'vp_textdomain' ),
                'description' => __( 'Select the breakpoint (in px) after which the template should switch to small screen.', 'vp_textdomain' ),
                'default'     => wp_tiles()->get_option_defaults( 'small_screen_breakpoint' ),
                'validation'  => 'numeric',
                'dependency'  => ( self::is_shortcode() ) ? false : array(
                    'field'    => 'small_screen_enabled',
                    'function' => 'vp_dep_boolean',
                ),
            );

            return $controls;
        }

        private static function _get_controls_colors() {
            $default_colors = wp_tiles()->get_option_defaults( 'colors' );

            // @todo Make color field repeatable
            $i = 1;
            foreach( $default_colors as $color ) {
                $controls[] = array(
                    'type' => 'color',
                    'name' => 'color_' . $i,
                    'label' => sprintf( __('Color %d', 'vp_textdomain'), $i ),
                    'description' => __('Another color', 'vp_textdomain'),
                    'default' => $color,
                    'format' => 'hex',
                );

                $i++;
            }

            return $controls;
        }

        private static function _get_controls_byline() {
            // @todo Add byline tags buttons to TinyMCE
            return array(
                array(
                    'type'                       => 'wpeditor',
                    'name'                       => 'byline_template',
                    'label'                      => __( 'Byline Template', 'vp_textdomain' ),
                    'description'                => __( '@todo: Explain tags.', 'vp_textdomain' ),
                    'default'                    => wp_tiles()->get_option_defaults( 'byline_template' ),
                    'use_external_plugins'       => '1',
                    'disabled_externals_plugins' => '',
                    'disabled_internals_plugins' => '',
                ),
                array(
                    'type' => 'textbox',
                    'name' => 'byline_opacity',
                    'label' => __('Byline Opacity', 'vp_textdomain'),
                    'description' => __('Set the byline opacity.', 'vp_textdomain'),
                    'default' => wp_tiles()->get_option_defaults( 'byline_opacity' ),
                    'validation' => 'numeric'
                ),
                array(
                    'type' => 'color',
                    'name' => 'byline_color',
                    'label' => __( 'Byline Color', 'vp_textdomain' ),
                    'description' => __('Color for the byline', 'vp_textdomain'),
                    'default' => wp_tiles()->get_option_defaults( 'byline_color' ),
                    'format' => 'hex',
                )
            );
        }

        private static function _get_controls_query() {

            return array(
                array(
                    'type'        => 'sorter',
                    'name'        => 'id',
                    'label'       => __( 'Manual Selection', 'vp_textdomain' ),
                    'description' => __( 'Select posts manually', 'vp_textdomain' ),
                    'items'       => array(
                        'data' => array(
                            array(
                                'source' => 'function',
                                'value'  => array( __CLASS__, 'get_posts_any' ),
                            ),
                        ),
                    ),
                ),

                array(
                    'type' => 'multiselect',
                    'name' => 'post_type',
                    'label' => __( 'Post Type', 'wp-tiles' ),
                    'items' => array(
                        'data' => array(
                            array(
                                'source' => 'function',
                                'value'  => array( __CLASS__, 'get_post_types' ),
                            ),
                        ),
                    ),
                ),

                array(
                    'type' => 'textbox',
                    'name' => 'posts_per_page',
                    'label' => __('Posts Per Page', 'vp_textdomain'),
                    'validation' => 'numeric'
                ),

                array(
                    'type' => 'multiselect',
                    'name' => 'category',
                    'label' => __( 'Category', 'wp-tiles' ),
                    'items'       => array(
                        'data' => array(
                            array(
                                'source' => 'function',
                                'value'  => 'vp_get_categories',
                            ),
                        ),
                    ),
                ),

                array(
                    'type' => 'multiselect',
                    'name' => 'tag',
                    'label' => __( 'Tags', 'wp-tiles' ),
                    'items'       => array(
                        'data' => array(
                            array(
                                'source' => 'function',
                                'value'  => 'vp_get_tags',
                            ),
                        ),
                    ),
                ),

                array(
                    'type' => 'select',
                    'name' => 'taxonomy',
                    'label' => __( 'Taxonomy', 'wp-tiles' ),
                    'items'       => array(
                        'data' => array(
                            array(
                                'source' => 'function',
                                'value'  => array( __CLASS__, 'get_taxonomies' ),
                            ),
                        ),
                    ),
                ),

                array(
                    'type' => 'select',
                    'name' => 'tax_operator',
                    'label' => __( 'Taxonomy Operator', 'wp-tiles' ),
                    'items'       => array(
                        array(
                            'value' => 'IN',
                            'label' => 'IN',
                        ),
                        array(
                            'value' => 'NOT IN',
                            'label' => 'NOT IN',
                        ),
                        array(
                            'value' => 'AND',
                            'label' => 'AND',
                        ),
                    ),
                ),

                array(
                    'type' => 'textbox',
                    'name' => 'tax_term',
                    'label' => __('Taxonomy Term', 'vp_textdomain'),
                ),

                array(
                    'type' => 'select',
                    'name' => 'order',
                    'label' => __( 'Order', 'wp-tiles' ),
                    'items'       => array(
                        array(
                            'value' => 'ASC',
                            'label' => __( 'Ascending', 'wp-tiles' ),
                        ),
                        array(
                            'value' => 'DESC',
                            'label' => __( 'Descending', 'wp-tiles' ),
                        )
                    ),
                ),

                array(
                    'type' => 'select',
                    'name' => 'orderby',
                    'label' => __( 'Order By', 'wp-tiles' ),
                    'items'       => array(
                        array(
                            'value' => 'name',
                            'label' => 'Name',
                        ),
                        array(
                            'value' => 'author',
                            'label' => 'Author',
                        ),
                        array(
                            'value' => 'date',
                            'label' => 'Date',
                        ),
                        array(
                            'value' => 'title',
                            'label' => 'Title',
                        ),
                        array(
                            'value' => 'modified',
                            'label' => 'Modified',
                        ),
                        array(
                            'value' => 'menu_order',
                            'label' => 'Menu Order',
                        ),
                        array(
                            'value' => 'parent',
                            'label' => 'Parent',
                        ),
                        array(
                            'value' => 'ID',
                            'label' => 'ID',
                        ),
                        array(
                            'value' => 'rand',
                            'label' => 'Rand',
                        ),
                        array(
                            'value' => 'comment_count',
                            'label' => 'Comment Count',
                        ),
                        array(
                            'value' => 'none',
                            'label' => 'None',
                        ),
                        array(
                            'value' => 'post__in',
                            'label' => 'Manual (post__in)',
                        ),
                        // @todo Should these be in here?
                        /*array(
                            'value' => 'post_parent__in',
                            'label' => 'Post_parent__in',
                        )*/
                    ),
                ),

                array(
                    'type' => 'select',
                    'name' => 'author',
                    'label' => __( 'Author', 'wp-tiles' ),
                    'items' => array(
                        'data' => array(
                            array(
                                'source' => 'function',
                                'value'  => 'vp_get_users',
                            ),
                        ),
                    ),
                ),

                array(
                    'type' => 'textbox',
                    'name' => 'meta_key',
                    'label' => __('Meta Key', 'vp_textdomain'),
                ),

                array(
                    'type' => 'textbox',
                    'name' => 'offset',
                    'label' => __('Offset', 'vp_textdomain'),
                    'validation' => 'numeric'
                ),

                array(
                    'type'        => 'select',
                    'name'        => 'post_parent',
                    'label'       => __( 'Post Parent', 'vp_textdomain' ),
                    'description' => __( 'Only show children of selected post', 'vp_textdomain' ),
                    'items'       => array(
                            array(
                                'label' => '[Use Current Post]',
                                'value' => 'current'
                            ),
                        'data' => array(
                            array(
                                'source' => 'function',
                                'value'  => array( __CLASS__, 'get_posts_any' ),
                            ),
                        ),
                    ),
                ),

                array(
                    'type' => 'select',
                    'name' => 'post_status',
                    'label' => __( 'Post Status', 'wp-tiles' ),
                    'items'       => array(
                        array(
                            'value' => 'publish',
                            'label' => 'Publish',
                        ),
                        array(
                            'value' => 'pending',
                            'label' => 'Pending',
                        ),
                        array(
                            'value' => 'draft',
                            'label' => 'Draft',
                        ),
                        array(
                            'value' => 'auto-draft',
                            'label' => 'Auto-draft',
                        ),
                        array(
                            'value' => 'future',
                            'label' => 'Future',
                        ),
                        array(
                            'value' => 'private',
                            'label' => 'Private',
                        ),
                        array(
                            'value' => 'inherit',
                            'label' => 'Inherit',
                        ),
                        array(
                            'value' => 'trash',
                            'label' => 'Trash',
                        ),
                        array(
                            'value' => 'any',
                            'label' => 'Any',
                        )
                    ),
                ),

                array(
                    'type' => 'toggle',
                    'name' => 'ignore_sticky_posts',
                    'label' => __('Ignore Sticky Posts', 'vp_textdomain'),
                ),
            );
        }


    //
    // DATA SOURCES
    //

    public static function get_grids() {
        $wp_posts = get_posts(array(
            'posts_per_page' => -1,
            'post_type' => GridTemplates::POST_TYPE
        ));

        $result = array();
        foreach ($wp_posts as $post)
        {
            $result[] = array('value' => $post->ID, 'label' => $post->post_title);
        }
        return $result;
    }

    public static function get_posts_any() {
        $wp_posts = get_posts(array(
            'posts_per_page' => -1,
            'post_type' => 'any'
        ));

        $result = array();
        foreach ($wp_posts as $post)
        {
            $result[] = array('value' => $post->ID, 'label' => $post->post_title);
        }
        return $result;
    }

    public static function get_grids_names() {
        $wp_posts = get_posts(array(
            'posts_per_page' => -1,
            'post_type' => GridTemplates::POST_TYPE
        ));

        $result = array();
        foreach ($wp_posts as $post)
        {
            $result[] = array('value' => $post->post_title, 'label' => $post->post_title);
        }
        return $result;
    }

    public static function get_post_types() {
        $result = array();
        foreach( get_post_types( array( 'public' => true ), 'objects' ) as $post_type => $post_type_obj ) {
            $result[] = array( 'value' => $post_type, 'label' => $post_type_obj->labels->name );
        }

        return $result;
    }

    public static function get_taxonomies() {
        $result = array();
        foreach( get_taxonomies( array( 'public' => true ), 'objects' ) as $post_type => $post_type_obj ) {
            $result[] = array( 'value' => $post_type, 'label' => $post_type_obj->labels->name );
        }

        return $result;
    }
}