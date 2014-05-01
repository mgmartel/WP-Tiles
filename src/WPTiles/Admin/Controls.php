<?php namespace WPTiles\Admin;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Controls
{

    public static function grids() {
        $grid_callback = ( Admin::is_shortcode() ) ? array( 'WPTiles\Admin\DataSources', 'get_grids_names' ) : array( 'WPTiles\Admin\DataSources', 'get_grids' );

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

        if ( !Admin::is_shortcode() ) {
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
            'dependency'  => ( Admin::is_shortcode() ) ? false : array(
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
            'dependency'  => ( Admin::is_shortcode() ) ? false : array(
                'field'    => 'small_screen_enabled',
                'function' => 'vp_dep_boolean',
            ),
        );

        $controls[] = array(
            'type' => 'slider',
            'name' => 'padding',
            'label' => __('Tile Padding', 'vp_textdomain'),
            'description' => __( 'Padding between the tiles in px', 'vp_textdomain'),
            'min' => '0',
            'max' => '100',
            'step' => '1',
            'default' => wp_tiles()->get_option_defaults( 'padding' ),
        );

        return $controls;
    }

    public static function animation() {
        return array(
            array(
                'type' => 'toggle',
                'name' => 'animated',
                'label' => __('Enable Animations', 'vp_textdomain'),
                'description' => __( 'Controls animations when tiles are loaded, modified or resized', 'vp_textdomain'),
                'default' => true,
            ),
            array(
                'type' => 'toggle',
                'name' => 'animate_init',
                'label' => __('Animation on load', 'vp_textdomain'),
                'description' => __( 'Show animation when tiles are first loaded', 'vp_textdomain'),
                'default' => wp_tiles()->get_option_defaults( 'animate_init' ),
                'dependency'  => ( Admin::is_shortcode() ) ? false : array(
                    'field'    => 'animated',
                    'function' => 'vp_dep_boolean',
                )
            ),
            array(
                'type' => 'toggle',
                'name' => 'animate_resize',
                'label' => __('Animation on window resize', 'vp_textdomain'),
                'description' => __( 'Animate the tiles when the window is resized?', 'vp_textdomain'),
                'default' => wp_tiles()->get_option_defaults( 'animate_resize' ),
                'dependency'  => ( Admin::is_shortcode() ) ? false : array(
                    'field'    => 'animated',
                    'function' => 'vp_dep_boolean',
                )
            ),
            array(
                'type' => 'toggle',
                'name' => 'animate_template',
                'label' => __('Animation on grid change', 'vp_textdomain'),
                'description' => __( 'Show animation when grid template changes', 'vp_textdomain'),
                'default' => wp_tiles()->get_option_defaults( 'animate_template' ),
                'dependency'  => ( Admin::is_shortcode() ) ? false : array(
                    'field'    => 'animated',
                    'function' => 'vp_dep_boolean',
                )
            ),
        );
    }

    public static function colors() {
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

    public static function byline_layout() {
        return array(
            array(
                'type' => 'toggle',
                'name' => 'text_only',
                'label' => __('Text-only tiles', 'vp_textdomain'),
                'description' => __( "Don't add the background image to Tiles", 'wp-tiles' ),
                'default' => wp_tiles()->get_option_defaults( 'text_only' )
            ),
            array(
                'type' => 'toggle',
                'name' => 'images_only',
                'label' => __('Hide tiles with no images', 'vp_textdomain'),
                'description' => __( "Hide tiles that don't have an image.", 'wp-tiles' ),
                'default' => wp_tiles()->get_option_defaults( 'images_only' )
            ),
            array(
                'type' => 'toggle',
                'name' => 'link_to_post',
                'label' => __( 'Link to Post', 'vp_textdomain' ),
                'description' => __( "Make the whole tile a link to the tile post.", 'wp-tiles' ),
                'default' => wp_tiles()->get_option_defaults( 'link_to_post' )
            )
        );
    }

    public static function byline_template() {
        return array(

            array(
                'type'        => 'codeeditor',
                'name'        => 'byline_template',
                'mode'        => 'html',
                'label'       => __( 'Byline Template (HTML)', 'vp_textdomain' ),
                'description' => __( '@todo: Explain tags.', 'vp_textdomain' ),
                'default'     => wp_tiles()->get_option_defaults( 'byline_template' ),
            ),

            array(
                'type'        => 'toggle',
                'name'        => 'byline_for_text_only',
                'label'       => __('Different template for text-only tiles?', 'vp_textdomain'),
                'description' => __( "Check this toggle to set up a different template for text-only tiles.", 'wp-tiles' ),
                'default'     => false
            ),

            // @todo (maybe) Trigger window resize when codeeditor is loaded via AJAX
            array(
                'type'        => 'codeeditor',
                'name'        => 'byline_template_textonly',
                'mode'        => 'html',
                'label'       => __( 'Text-Only Byline Template', 'vp_textdomain' ),
                'description' => __( '', 'vp_textdomain' ),
                'default'     => wp_tiles()->get_option_defaults( 'byline_template' ),
                'dependency' => array(
                    'field'    => 'byline_for_text_only',
                    'function' => 'vp_dep_boolean',
                ),
            ),

            array(
                'type' => 'toggle',
                'name' => 'hide_title',
                'label' => __('Hide title on byline', 'vp_textdomain'),
                'description' => __( "By default, WP Tiles add the title of the post to the byline as a H4 tag. To add the title in the template above manually, select this option.", 'wp-tiles' ),
                'default' => wp_tiles()->get_option_defaults( 'hide_title' )
            ),

        );
    }

    public static function query() {

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
                            'value'  => array( 'WPTiles\Admin\DataSources', 'get_posts_any' ),
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
                            'value'  => array( 'WPTiles\Admin\DataSources', 'get_post_types' ),
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
                            'value'  => array( 'WPTiles\Admin\DataSources', 'get_taxonomies' ),
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
                            'value'  => array( 'WPTiles\Admin\DataSources', 'get_posts_any' ),
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

    public static function images() {
        return array(
            array(
                'type' => 'select',
                'name' => 'image_size',
                'label' => __( 'Use image size', 'wp-tiles' ),
                'description' => __( 'Define the image size WP Tiles should use for tile background. Set to a larger size if Tile backgrounds come out too pixelated.', 'wp-tiles' ),
                'default' => wp_tiles()->get_option_defaults( 'image_size' ),
                'items'       => array(
                    'data' => array(
                        array(
                            'source' => 'function',
                            'value'  => array( 'WPTiles\Admin\DataSources', 'get_image_sizes' ),
                        ),
                    ),
                ),
            ),
            array(
                'type'  => 'select',
                'name'  => 'image_source',
                'label' => __( 'Image Source', 'wp-tiles' ),
                'description' => __( 'Where should WP Tiles look for the images for the background of tiles?', 'wp-tiles' ),
                'default' => wp_tiles()->get_option_defaults( 'image_source' ),
                'items'   => array(
                    array(
                        'label' => __( 'Any', 'wp-tiles' ),
                        'value' => 'all'
                    ),
                    array(
                        'label' => __( "Attached Only (don't look in post)", 'wp-tiles' ),
                        'value' => 'attached_only'
                    ),
                    array(
                        'label' => __( "Featured Image Only", 'wp-tiles' ),
                        'value' => 'featured_only'
                    ),
                    array(
                        'label' => __( "Only show image for Media Posts", 'wp-tiles' ),
                        'value' => 'attachment_only'
                    ),
                ),
            )
        );
    }

    public static function tile_preview() {
        return array(
            array(
                'type' => 'html',
                'name' => 'byline_effect_preview',
                 'description' => 'Preview Description',
                 'label' => 'Preview',
                 'binding' => array(
                     'function' => 'wp_tiles_preview_tile',
                     'field' => 'byline_height,byline_color,byline_opacity,byline_align,byline_effect,image_effect',
                 ),
             )
        );
    }

    public static function tile_designer() {
        return array(
            array(
                'type' => 'slider',
                'name' => 'byline_opacity',
                'label' => __('Byline Opacity (0 to 1)', 'vp_textdomain'),
                'description' => __('Set the byline opacity.', 'vp_textdomain'),
                'default' => wp_tiles()->get_option_defaults( 'byline_opacity' ),
                'min' => '0',
                'max' => '1',
                'step' => '0.01',
                //'validation' => 'numeric'
            ),
            array(
                'type' => 'slider',
                'name' => 'byline_height',
                'label' => __('Byline Height (px)', 'vp_textdomain'),
                'description' => __('Set the height of the byline on image tiles. 100% means fully covered, 0% means invisible.', 'vp_textdomain'),
                'default' => wp_tiles()->get_option_defaults( 'byline_height' ),
                'min' => '0',
                'max' => '100',
                'step' => '1',
            ),
            array(
                'type' => 'color',
                'name' => 'byline_color',
                'label' => __( 'Byline Color', 'vp_textdomain' ),
                'description' => __('Color for the byline. Leave empty to use the tile colors', 'vp_textdomain'),
                'default' => wp_tiles()->get_option_defaults( 'byline_color' ),
                'format' => 'hex',
            ),
            array(
                'type' => 'radiobutton',
                'name' => 'byline_effect',
                'label' => __( 'Byline Effect', 'wp-tiles' ),
                'description' => __( '', 'wp-tiles' ),
                'default' => wp_tiles()->get_option_defaults( 'byline_effect' ),
                'items' => array(
                    array(
                        'label' => __( 'None', 'wp-tiles' ),
                        'value' => 'none'
                    ),
                    array(
                        'label' => __( 'Slide Up', 'wp-tiles' ),
                        'value' => 'slide-up'
                    ),
                    array(
                        'label' => __( 'Slide Down', 'wp-tiles' ),
                        'value' => 'slide-down'
                    ),
                    array(
                        'label' => __( 'Slide Left', 'wp-tiles' ),
                        'value' => 'slide-left'
                    ),
                    array(
                        'label' => __( 'Slide Right', 'wp-tiles' ),
                        'value' => 'slide-right'
                    ),
                    array(
                        'label' => __( 'Fade In', 'wp-tiles' ),
                        'value' => 'fade-in'
                    )
                )
            ),
            array(
                'type' => 'radiobutton',
                'name' => 'byline_align',
                'label' => __( 'Byline Vertical Alignment', 'wp-tiles' ),
                'description' => __( 'Align the byline to the top or bottom of the tile. Has no effect if slide effect is up or down, or if tile is 100% high.', 'wp-tiles' ),
                'default' => wp_tiles()->get_option_defaults( 'byline_align' ),
                'items' => array(
                    array(
                        'label' => __( 'Top', 'wp-tiles' ),
                        'value' => 'top'
                    ),
                    array(
                        'label' => __( 'Bottom', 'wp-tiles' ),
                        'value' => 'bottom'
                    )
                )
            ),
            array(
                'type' => 'radiobutton',
                'name' => 'image_effect',
                'label' => __( 'Image Effect', 'wp-tiles' ),
                'description' => __( '', 'wp-tiles' ),
                'default' => wp_tiles()->get_option_defaults( 'byline_effect' ),
                'items' => array(
                    array(
                        'label' => __( 'None', 'wp-tiles' ),
                        'value' => 'none'
                    ),
                    array(
                        'label' => __( 'Scale Up', 'wp-tiles' ),
                        'value' => 'scale-up'
                    ),
                    array(
                        'label' => __( 'Scale Down', 'wp-tiles' ),
                        'value' => 'scale-down'
                    ),
                    array(
                        'label' => __( 'Saturate', 'wp-tiles' ),
                        'value' => 'saturate'
                    ),
                    array(
                        'label' => __( 'Desaturate', 'wp-tiles' ),
                        'value' => 'desaturate'
                    ),
                )
            ),
        );
    }
}