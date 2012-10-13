<?php
//
//  SETTINGS CONFIGURATION CLASS
//  Inspired by original:
//  By Olly Benson / v 1.2 / 13 July 2011 / http://code.olib.co.uk
//  Modified / Bugfix by Karl Cohrs / 17 July 2011 / http://karlcohrs.com
//  Modified again by Mike Martel / 13 October 2012 / http://trenvo.com/

class WP_Tiles_Settings_Config
{

    static $option_name = "wp-tiles-options";

    public static function settings() {
        return array (
            "group" => "wp-tiles",
            "page_name" => "wp-tiles",

            "option_name" => self::$option_name,
            "title" => "WP Tiles",
            "intro_text" => __( "Use this page to set the default settings used with the <code>[wp-tiles]</code> shortcode.
                <h3>Usage</h3>
                <p>To show wp-tiles on any of your pages or in your posts, simply include the shortcode <code>[wp-tiles]</code></p>
                <p>WP Tiles will automatically show posts based on the preferences you set below. To override the settings on a per-post basis, pass arguments to the shortcode as indicated in each section.</p>
                <h3>Advanced</h3>
                <p>You can also style WP Tiles in your (child) theme. Simply add 'wp-tile.css' in a folder called 'inc' to your theme.</p><hr>", 'wp-tiles' ),
            "nav_title" => "WP Tiles",
            "sections" => self::sections(),
            "dropdown_options" => self::dropdowns(),
            "sanitize" => array ( 'WP_Tiles_Settings_Config', 'sanitize' )
        );
    }

    protected function dropdowns() {
        $cats = get_categories();
        $cats_a = array( "" => "All");
        foreach ( $cats as $cat ) {
            $cats_a[$cat->term_id] = $cat->name;
        }

        return array (
            "categories" => $cats_a,
            "orderby" => array (
                "rand"  => __( "Random" ),
                "post_date"
                        => __( "Date" )
            ),
            "order" => array (
                "DESC"  => __( "Descending" ),
                "ASC"  => __( "Ascending" ),
            ),
            "post_types" => get_post_types(),
        );
    }

    protected static function sections() {
        global $wptiles_defaults;
        require ( WPTILES_DIR . '/wp-tiles-defaults.php');

        $wptiles_options = get_option( self::$option_name );
        $wptiles_defaults = shortcode_atts( $wptiles_defaults, $wptiles_options);

        $sections = array(
            'colors' => array (
                'title'     => __ ( "Colors", "wp-tiles" ),
                'description'
                            => __( "Posts that have no image randomly get assigned a background color. Here you can define the colorscheme used by WP Tiles.<br><br>
                                Change this in the shortcode like this: <code>[wp-tiles colors='#FF0000,#00FF00, #0000FF']</code>", "wp-tiles" ),
                'fields'    => array (
                    'colors'  => array (
                        'label'     => __ ( 'Enter the RGB code of each color on a seperate line.', "wp-tiles" ),
                        'length'    => '200',
                        'type'      => 'textarea'
                    ),
                ),
            ),
            'templates' => array (
                'title'     => __ ("Templates", 'wp-tiles'),
                'description' => __("You can include multiple templates in WP-Tiles. To hide the template chooser, simply add only a single template.<br><br>
                    Templates are formulated as per tiles.js. Check the <a href='http://www.pulse.me/app/dev/#dev-section-tilejs'>Pulse.me website</a> for a demonstration in making templates.<br><br>
                    Change this in the shortcode like this: <code>[wp-tiles template=\"A . B . C C\\nA . B . C C \\nA . . . .\"]</code>", 'wp-tiles'),
                'fields'    => array (
                    'templates'  => array (
                        'label'     => __ ( 'Templates', "wp-tiles" ),
                        'length'    => '200',
                        'type'      => 'textareas_name'
                    ),
                    'small_screen_template'  => array (
                        'label'     => __ ( 'Small screen template', "wp-tiles" ),
                        'length'    => '200',
                        'type'      => 'textarea'
                    ),
                ),
            ),
            'posts_query' => array (
                'title'     => __ ( "Posts", "wp-tiles" ),
                'description'
                            => __( "Default arguments to be passed when querying tiles using just [wp-tiles]. These can be modified by passing 'posts_query=' as an argument to the shortcode.<br><br>
                                For example: <code>[wp-tiles posts_query='numberposts=5&post_type=page']</code>.", "wp-tiles" ),
                'fields'    => array (
                    'numberposts'  => array (
                        'label'     => __ ( 'Number of posts', "wp-tiles" ),
                        'length'    => '3',
                    ),
                    'offset'  => array (
                        'label'     => __ ( 'Offset', "wp-tiles" ),
                        'length'    => '3',
                    ),
                    'category'  => array (
                        'label'     => __ ( 'Category', "wp-tiles" ),
                        'dropdown' => "categories",
                    ),
                    'orderby'  => array (
                        'label'     => __ ( 'Order by', "wp-tiles" ),
                        'dropdown' => "orderby",
                    ),
                    'order'  => array (
                        'label'     => __ ( 'Order', "wp-tiles" ),
                        'dropdown' => "order",
                    ),
                    'include'  => array (
                        'label'     => __ ( 'Include', "wp-tiles" ),
                        'length'    => '100',
                    ),
                    'exclude'  => array (
                        'label'     => __ ( 'Exclude', "wp-tiles" ),
                        'length'    => '100',
                    ),
                    'meta_key'  => array (
                        'label'     => __ ( 'Meta key', "wp-tiles" ),
                        'length'    => '100',
                    ),
                    'meta_value'  => array (
                        'label'     => __ ( 'Meta value', "wp-tiles" ),
                        'length'    => '100',
                    ),
                    'post_type'  => array (
                        'label'     => __ ( 'Post Type', "wp-tiles" ),
                        'dropdown' => "post_types",
                    ),
                )
            ),
        );
        foreach ( $sections as $section => &$contents ) {
            foreach ( $contents['fields'] as $name => &$values ) {
                $values['default_value'] = $wptiles_defaults[$section][$name];
            }
        }
        return $sections;
    }

    public static function sanitize ( $input ) {
        global $wp_tiles_settings;

        $i = 0; $new_a = array();
        foreach ( $input['templates']['templates']['name'] as $v ) {
            $new_a[$v] = $input['templates']['templates']['field'][$i];
            $i++;
        }
        $input['templates']['templates'] = $new_a;

        return $input;
    }
}


class WP_Tiles_Settings {

    public function __construct( $settings_class ) {
        global $wp_tiles_settings;
        $wp_tiles_settings = $settings_class::settings();

        add_action('admin_init', array( &$this, 'plugin_admin_init'));
        add_action('admin_menu', array( &$this, 'plugin_admin_add_page'));
    }

        /**
         * PHP4
         * @param type $settings_class
         */
        function wp_tiles_settings( $settings_class ) {
            $this->__construct( $settings_class );
        }

    /**
     * Add the options page
     *
     * @global array $wp_tiles_settings
     */
    function plugin_admin_add_page() {
        global $wp_tiles_settings;

        add_options_page(
            $wp_tiles_settings['title'],
            $wp_tiles_settings['nav_title'],
            'manage_options',
            $wp_tiles_settings['page_name'],
            array( &$this,'plugin_options_page')
        );
    }

    function plugin_options_page() {
        global $wp_tiles_settings;
        printf('</pre>
            <div>
            <h2>%s</h2>
            %s
            <form action="options.php" method="post">',

            $wp_tiles_settings['title'],$wp_tiles_settings['intro_text']);
             settings_fields($wp_tiles_settings['group']);
             do_settings_sections($wp_tiles_settings['page_name']);
             printf('<input type="submit" name="Submit" value="%s" /></form></div>
            <pre>',
            __('Save Changes')
        );
    }

    function plugin_admin_init(){
        global $wp_tiles_settings;

        register_setting(
            $wp_tiles_settings['page_name'],
            $wp_tiles_settings['option_name'],
            $wp_tiles_settings['sanitize']
        );

        foreach ($wp_tiles_settings["sections"] AS $section_key=>$section_value) {

            add_settings_section($section_key, $section_value['title'], array( &$this, 'plugin_section_text'), $wp_tiles_settings['page_name'], $section_value);

            foreach ($section_value['fields'] AS $field_key => $field_value ) {

                $function = array( &$this, 'plugin_setting_string' );
                if (!empty($field_value['dropdown']))
                    $function = array( &$this, 'plugin_setting_dropdown' );
                elseif ( ! empty ( $field_value['function'] ) )
                    $function = $field_value['function'];
                elseif ( ! empty ( $field_value['type'] ) ) {
                    $function = array( &$this, "plugin_setting_{$field_value['type']}" );
                }

                add_settings_field(
                    $wp_tiles_settings['group'].'_'.$field_key,
                    $field_value['label'] . sprintf( " <code>%s</code>",$field_key ),
                    $function,
                    $wp_tiles_settings['page_name'],
                    $section_key,
                    array_merge($field_value,array('name' => $field_key, 'group' => $section_key))
                );

            }
        }
    }

    function plugin_section_text($value = NULL) {
        global $wp_tiles_settings;
        printf("%s", $wp_tiles_settings['sections'][$value['id']]['description']);
    }

    function plugin_setting_string($value = NULL) {
        global $wp_tiles_settings;

        $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
        printf('<input id="%s" type="text" name="%s" value="%s" size="40" /> %s%s',
            $value['name'],
            "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}]",
            $default_value,
            (!empty ($value['suffix'])) ? $value['suffix'] : NULL,
            (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL );
    }

    function plugin_setting_dropdown($value = NULL) {
        global $wp_tiles_settings;

        $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
        $current_value = $default_value;
        $chooseFrom = "";
        $choices = $wp_tiles_settings['dropdown_options'][$value['dropdown']];

        foreach($choices AS $key=>$option) {
            $chooseFrom .= sprintf('<option value="%s" %s>%s</option>',
                $key,($current_value == $key ) ? ' selected="selected"' : NULL,$option);
        }

        printf('
            <select id="%s" name="%s">%s</select>%s',
            $value['name'],
            "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}]",
            $chooseFrom,
            (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL
        );
    }

    public function plugin_setting_textareas ( $value = NULL ) {
        global $wp_tiles_settings;
        $default_value = ( ! empty ( $value['default_value'] ) ) ? $value['default_value'] : NULL;

        foreach ( $default_value as $k => $current ) {
            printf ( "<textarea style='height:100px' id='%s' name='%s'>%s</textarea>",
                $current['name'],
                "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}][{$k}]",
                $current
            );
        }
    }

    public function plugin_setting_textareas_name ( $value = NULL ) {
        global $wp_tiles_settings;
        $default_value = ( ! empty ( $value['default_value'] ) ) ? $value['default_value'] : NULL;

        foreach ( $default_value as $k => $current ) {
            printf ( "<div style='float:left'><input type='text' value='%s' name='%s' width=140px><br>
                <textarea style='height:100px;width:140px' id='%s' name='%s'>%s</textarea></div>",
                $k,
                "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}][name][]",
                $current['name'],
                "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}][field][]",
                $current
            );
        }
    }

    public function plugin_setting_textarea ( $value = NULL ) {
        global $wp_tiles_settings;
        $default_value = ( ! empty ( $value['default_value'] ) ) ? $value['default_value'] : NULL;
        printf ( "<textarea style='height:100px' id=%s name=%s>%s</textarea>",
                $value['name'],
                "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}]",
                $default_value
            );
    }

}

new wp_tiles_settings('wp_tiles_settings_config');
?>