<?php namespace WPTiles\Admin;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class Controls
{

    public static function grids() {
        $grid_callback = ( Admin::is_shortcode() ) ? array( 'WPTiles\Admin\DataSources', 'get_grids_names' ) : array( 'WPTiles\Admin\DataSources', 'get_grids' );

        $controls   = array();

        if ( Admin::is_shortcode() ) {
            $controls[] =  array(
                'type' => 'notebox',
                'name' => 'notice_shortcode_grid',
                'label' => __('Custom Layout Options', 'wp-tiles'),
                'description' => __('Use the options below to change the layout settings of this instance. Once inserted as a shortcode, you can still edit them manually.', 'wp-tiles' ),
                'status' => 'normal',
            );

            $controls[] = array(
                'type'        => 'sorter',
                'name'        => 'grids',
                'label'       => __( 'Grids', 'wp-tiles' ),
                'description' => __( 'Select which Grids to use', 'wp-tiles' ),
                'default'     => get_the_title( wp_tiles()->options->get_option( 'default_grid' ) ),
                'items'       => array(
                    'data' => array(
                        array(
                            'source' => 'function',
                            'value'  => $grid_callback,
                        ),
                    ),
                ),
            );
        } else {
            $controls[] = array(
                'type' => 'notebox',
                'name' => 'notice_girds',
                'label' => __('Creating Grids', 'wp-tiles'),
                'description' => sprintf(
                    __('In this panel you can set the default options for your tile grids. Go to the <a href="%s">Grids</a> page to create and edit grids.', 'wp-tiles' ),
                    admin_url( 'edit.php?post_type=' . \WPTiles\WPTiles::GRID_POST_TYPE )
                ),
                'status' => 'info',
            );

            $controls[] = array(
                'type'        => 'select',
                'name'        => 'default_grid',
                'label'       => __( 'Default Grid', 'wp-tiles' ),
                'description' => __( 'Select which Grid to use by default', 'wp-tiles' ),
                'default'     => '{{last}}',
                'validation'  => 'required',
                'items'       => array(
                    array(
                        'label' => __( 'All', 'wp-tiles' ),
                        'value' => 'all',
                    ),
                    'data' => array(
                        array(
                            'source' => 'function',
                            'value'  => $grid_callback,
                        ),
                    ),
                ),
            );

            $controls[] = array(
                'type'        => 'toggle',
                'name'        => 'small_screen_enabled',
                'label'       => __( 'Different template on small screens?', 'wp-tiles' ),
                'description' => __( "Yay or nay? What's it going to be?", 'wp-tiles' ),
                'default'     => '1',
            );

        }

        $controls[] = array(
            'type'        => 'select',
            'name'        => 'small_screen_grid',
            'label'       => __( 'Small Screen Grid', 'wp-tiles' ),
            'description' => __( 'Select the grid to use on small screens.', 'wp-tiles' ),
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
            'name'        => 'breakpoint',
            'label'       => __( 'Small Screen Breakpoint', 'wp-tiles' ),
            'description' => __( 'Select the breakpoint (in px) after which the template should switch to small screen.', 'wp-tiles' ),
            'default'     => wp_tiles()->options->get_defaults( 'breakpoint' ),
            'validation'  => 'numeric',
            'dependency'  => ( Admin::is_shortcode() ) ? false : array(
                'field'    => 'small_screen_enabled',
                'function' => 'vp_dep_boolean',
            ),
        );

        $controls[] = array(
            'type' => 'slider',
            'name' => 'padding',
            'label' => __('Tile Padding', 'wp-tiles'),
            'description' => __( 'Padding between the tiles in px', 'wp-tiles'),
            'min' => '0',
            'max' => '100',
            'step' => '1',
            'default' => wp_tiles()->options->get_defaults( 'padding' ),
        );

        $controls[] = array(
            'type'        => 'radiobutton',
            'name'        => 'pagination',
            'label'       => __( 'Pagination', 'wp-tiles' ),
            'description' => __( "Should pagination be shown under the tiles?", 'wp-tiles' ),
            'items'       => array(
                array(
                    'value' => 'none',
                    'label' => __( 'No Pagation', 'wp-tiles' )
                ),
                array(
                    'value' => 'ajax',
                    'label' => __( 'Load More (without page refresh)', 'wp-tiles' )
                ),
                array(
                    'value' => 'prev_next',
                    'label' => __( 'Next / Previous (new page)', 'wp-tiles' )
                ),
                array(
                    'value' => 'paging',
                    'label' => __( 'With Page Numbers', 'wp-tiles' )
                )
            ),
            'default' => wp_tiles()->options->get_defaults( 'pagination' ),
        );

        $controls[] = array(
            'type' => 'color',
            'name' => 'template_selector_color',
            'label' => __( 'Template Selector Color', 'wp-tiles' ),
            'description' => __( 'Select the color for the template selector.', 'wp-tiles' ),
            'default' => wp_tiles()->options->get_defaults( 'template_selector_color' ),
            'format' => 'hex',
        );

        return $controls;
    }

    public static function animations() {
        return array(
            array(
                'type' => 'toggle',
                'name' => 'animated',
                'label' => __('Enable Animations', 'wp-tiles'),
                'description' => __( 'Controls animations when tiles are loaded, modified or resized', 'wp-tiles'),
                'default' => true,
            ),
            array(
                'type' => 'toggle',
                'name' => 'animate_init',
                'label' => __('Animation on load', 'wp-tiles'),
                'description' => __( 'Show animation when tiles are first loaded', 'wp-tiles'),
                'default' => wp_tiles()->options->get_defaults( 'animate_init' ),
                'dependency'  => ( Admin::is_shortcode() ) ? false : array(
                    'field'    => 'animated',
                    'function' => 'vp_dep_boolean',
                )
            ),
            array(
                'type' => 'toggle',
                'name' => 'animate_resize',
                'label' => __('Animation on window resize', 'wp-tiles'),
                'description' => __( 'Animate the tiles when the window is resized?', 'wp-tiles'),
                'default' => wp_tiles()->options->get_defaults( 'animate_resize' ),
                'dependency'  => ( Admin::is_shortcode() ) ? false : array(
                    'field'    => 'animated',
                    'function' => 'vp_dep_boolean',
                )
            ),
            array(
                'type' => 'toggle',
                'name' => 'animate_template',
                'label' => __('Animation on grid change', 'wp-tiles'),
                'description' => __( 'Show animation when grid template changes', 'wp-tiles'),
                'default' => wp_tiles()->options->get_defaults( 'animate_template' ),
                'dependency'  => ( Admin::is_shortcode() ) ? false : array(
                    'field'    => 'animated',
                    'function' => 'vp_dep_boolean',
                )
            ),
        );
    }

    public static function colors() {
        $default_colors = wp_tiles()->options->get_defaults( 'colors' );
        $controls = array();

        $controls[] = array(
            'type' => 'notebox',
            'name' => 'notice_girds',
            'label' => __('Tile background colors', 'wp-tiles'),
            'description' => __( 'Tiles without images automatically get a background '
                . 'color from the set of colors below. Select the default colors '
                . 'to use for tiles without images.', 'wp-tiles' ),
            'status' => 'normal',
        );

        // @todo Make color field repeatable
        $i = 1;
        foreach( $default_colors as $color ) {
            $controls[] = array(
                'type' => 'color',
                'name' => 'color_' . $i,
                'label' => sprintf( __( 'Color %d', 'wp-tiles' ), $i ),
                'default' => $color,
                'format' => 'hex',
            );

            $i++;
        }

        $controls[] = array(
            'type' => 'slider',
            'name' => 'background_opacity',
            'label' => __( 'Background Opacity (0 to 1)', 'wp-tiles' ),
            'description' => __( 'If you have your own background behind your posts, '
                . 'you can set the background opacity for tiles without background '
                . 'image here. Set to 0 to make tile completely transparent.', 'wp-tiles'),
            'default' => wp_tiles()->options->get_defaults( 'background_opacity' ),
            'min'  => '0',
            'max'  => '1',
            'step' => '0.01',
        );

        return $controls;
    }

    public static function byline_layout() {
        return array(
            array(
                'type' => 'toggle',
                'name' => 'text_only',
                'label' => __('Text-only tiles', 'wp-tiles'),
                'description' => __( "Don't add the background image to Tiles", 'wp-tiles' ),
                'default' => wp_tiles()->options->get_defaults( 'text_only' )
            ),
            array(
                'type' => 'toggle',
                'name' => 'images_only',
                'label' => __('Hide tiles with no images', 'wp-tiles'),
                'description' => __( "Hide tiles that don't have an image. Please note that setting this option is incompatible with pagination!", 'wp-tiles' ),
                'default' => wp_tiles()->options->get_defaults( 'images_only' )
            ),
            array(
                'type' => 'radiobutton',
                'name' => 'link',
                'label' => __( 'Link To', 'wp-tiles' ),
                'description' => __( "Where should people go when they click on a tile (if anywhere?)", 'wp-tiles' ),
                'default' => wp_tiles()->options->get_defaults( 'link' ),
                'items' => array(
                    array(
                        'value' => 'post',
                        'label' => __( 'The Post (default)', 'wp-tiles' )
                    ),
                    /*array(
                        'value' => 'attachment',
                        'label' => __( 'Attachment page', 'wp-tiles' )
                    ),*/
                    array(
                        'value' => 'file',
                        'label' => __( 'Image File', 'wp-tiles' )
                    ),
                    array(
                        'value' => 'thickbox',
                        'label' => __( 'Open image in default Thickbox', 'wp-tiles' )
                    ),
                    array(
                        'value' => 'none',
                        'label' => __( 'No Link', 'wp-tiles' )
                    ),
                )
            ),
        );
    }

    public static function byline_template() {
        return array(

            array(
                'type'        => 'codeeditor',
                'name'        => 'byline_template',
                'mode'        => 'html',
                'label'       => __( 'Byline Template (HTML)', 'wp-tiles' ),
                'description' => __( "Edit the content that appears on tile bylines "
                    . "here. This field takes HTML with special merge tags to display "
                    . "information from the posts. See the info box below for all "
                    . "available tags.", 'wp-tiles' ),
                'default'     => wp_tiles()->options->get_defaults( 'byline_template' ),
            ),

            array(
                'type' => 'notebox',
                'name' => 'notice_byline_template',
                'label' => __('Byline Tags', 'wp-tiles'),
                'description' => __( "You can use the following tags anywhere in your byline templates:\n"
                    . "* `%title%`, `%content%`, `%date%`, `%excerpt%`, `%link%`, "
                    . "`%author%`, `%featured_image%` - All taken from the post\n"
                    . "* `%categories%` - Comma separated list of categories\n"
                    . "* `%category_links%` - Like above, but with links\n"
                    . "* `%tags`, `%tag_links%` - Same as categories\n"
                    . "* `%meta:META_KEY%` - Replace META_KEY by the meta key you want to display\n"
                    . "* `%tax:TAXONOMY%`, `%tax_links:TAXONOMY%`"
                    . "" ),
                'status' => 'info',
            ),

            array(
                'type'        => 'toggle',
                'name'        => 'byline_for_text_only',
                'label'       => __('Different template for text-only tiles?', 'wp-tiles'),
                'description' => __( "Check this toggle to set up a different template for text-only tiles.", 'wp-tiles' ),
                'default'     => false
            ),

            // @todo (maybe) Trigger window resize when codeeditor is loaded via AJAX
            array(
                'type'        => 'codeeditor',
                'name'        => 'byline_template_textonly',
                'mode'        => 'html',
                'label'       => __( 'Text-Only Byline Template', 'wp-tiles' ),
                'description' => __( '', 'wp-tiles' ),
                'default'     => wp_tiles()->options->get_defaults( 'byline_template_textonly' ),
                'dependency' => array(
                    'field'    => 'byline_for_text_only',
                    'function' => 'vp_dep_boolean',
                ),
            ),

            array(
                'type' => 'toggle',
                'name' => 'hide_title',
                'label' => __('Hide title on byline', 'wp-tiles'),
                'description' => __( "By default, WP Tiles add the title of the post to the byline as a H4 tag. To add the title in the template above manually, select this option.", 'wp-tiles' ),
                'default' => wp_tiles()->options->get_defaults( 'hide_title' )
            ),

        );
    }

        private static function get_query_option( $key ) {
            if ( false && Admin::is_shortcode() )
                return wp_tiles()->post_query->get_query_option( 'id', true );

            return wp_tiles()->post_query->get_query_defaults( $key );
        }

    public static function query() {

        return array(
            array(
                'type'        => 'sorter',
                'name'        => 'id',
                'label'       => __( 'Manual Selection', 'wp-tiles' ),
                'description' => __( 'Select posts manually', 'wp-tiles' ),
                'default'     => self::get_query_option( 'id' ),
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
                'default' => self::get_query_option( 'post_type' ),
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
                'label' => __('Posts Per Page', 'wp-tiles'),
                'validation' => 'numeric',
                'default' => self::get_query_option( 'posts_per_page' ),
            ),

            array(
                'type' => 'multiselect',
                'name' => 'category',
                'label' => __( 'Category', 'wp-tiles' ),
                'default'     => self::get_query_option( 'category' ),
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
                'default'     => self::get_query_option( 'tag' ),
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
                'default'     => self::get_query_option( 'taxonomy' ),
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
                'default'     => self::get_query_option( 'tax_operator' ),
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
                'default'     => self::get_query_option( 'tax_term' ),
                'label' => __('Taxonomy Term', 'wp-tiles'),
            ),

            array(
                'type' => 'select',
                'name' => 'order',
                'default'     => self::get_query_option( 'order' ),
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
                'default'     => self::get_query_option( 'orderby' ),
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
                'default'     => self::get_query_option( 'author' ),
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
                'default'     => self::get_query_option( 'meta_key' ),
                'label' => __('Meta Key', 'wp-tiles'),
            ),

            array(
                'type' => 'textbox',
                'name' => 'offset',
                'default'     => self::get_query_option( 'offset' ),
                'label' => __('Offset', 'wp-tiles'),
                'validation' => 'numeric'
            ),

            array(
                'type'        => 'select',
                'name'        => 'post_parent',
                'default'     => self::get_query_option( 'post_parent' ),
                'label'       => __( 'Post Parent', 'wp-tiles' ),
                'description' => __( 'Only show children of selected post', 'wp-tiles' ),
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
                'default'     => self::get_query_option( 'post_status' ),
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
                'default'     => self::get_query_option( 'ignore_sticky_posts' ),
                'label' => __('Ignore Sticky Posts', 'wp-tiles'),
            ),

            array(
                'type' => 'toggle',
                'name' => 'exclude_current_post',
                'default'     => self::get_query_option( 'exclude_current_post' ),
                'label' => __('Exclude Current Post from Tiles?', 'wp-tiles')
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
                'default' => wp_tiles()->options->get_defaults( 'image_size' ),
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
                'default' => wp_tiles()->options->get_defaults( 'image_source' ),
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
                'default' => wp_tiles_preview_tile()
             )
        );
    }

    public static function tile_designer() {
        return array(
            array(
                'type' => 'color',
                'name' => 'byline_color',
                'label' => __( 'Byline Background Color', 'wp-tiles' ),
                'description' => __('To use the same set of colors as the tiles without text, leave this option empty.', 'wp-tiles'),
                'default' => wp_tiles()->options->get_defaults( 'byline_color' ),
                'format' => 'hex',
            ),
            array(
                'type' => 'slider',
                'name' => 'byline_opacity',
                'label' => __('Byline Opacity (0 to 1)', 'wp-tiles'),
                'description' => __( 'Set the opacity for the background of the byline on top of the image. 0 is completely transparent, 1 fully opaque.', 'wp-tiles' ),
                'default' => wp_tiles()->options->get_defaults( 'byline_opacity' ),
                'min'  => '0',
                'max'  => '1',
                'step' => '0.01',
            ),
            array(
                'type' => 'slider',
                'name' => 'byline_height',
                'label' => __('Byline Height (%)', 'wp-tiles'),
                'description' => __( 'Set the height of the byline on image tiles. 100% means fully covered, 0% means invisible.', 'wp-tiles' ),
                'default' => wp_tiles()->options->get_defaults( 'byline_height' ),
                'min' => '0',
                'max' => '100',
                'step' => '1',
            ),
            array(
                'type' => 'radiobutton',
                'name' => 'byline_effect',
                'label' => __( 'Byline Effect', 'wp-tiles' ),
                'description' => __( 'Select the effect you want to use for the byline to appear when you hover over the tile.', 'wp-tiles' ),
                'default' => wp_tiles()->options->get_defaults( 'byline_effect' ),
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
                'description' => __( 'Align the byline to the top or bottom of the tile. Nb. This option has no effect if slide effect is up or down, or if tile is 100% high.', 'wp-tiles' ),
                'default' => wp_tiles()->options->get_defaults( 'byline_align' ),
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
                'description' => __( 'Select the effect you want to use for the image when you hover over the tile.', 'wp-tiles' ),
                'default' => wp_tiles()->options->get_defaults( 'byline_effect' ),
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
            array(
                'type'        => 'toggle',
                'name'        => 'legacy_styles',
                'label'       => __( 'Use legacy (pre-1.0) styles', 'wp-tiles' ),
                'description' => __( 'Check this box to enable the old styles for WP Tiles. Not recommended, unless you need to ensure compatibility with your own custom styles from the pre-1.0 era.', 'wp-tiles' ),
                'default'     => wp_tiles()->options->get_defaults( 'legacy_styles' )
            )
        );
    }
}