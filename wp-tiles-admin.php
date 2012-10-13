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
            "intro_text" => __( "Default settings for WP Tiles", 'wp-tiles' ),
            "nav_title" => "WP Tiles",
            "sections" => self::sections(),
            "dropdown_options" => self::dropdowns(),
        );
    }

    protected function dropdowns() {
        $cats = get_categories();
        $cats_a = array();
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

        //$wptiles_defaults = get_option( self::$option_name );
        $wptiles_defaults = get_option( self::$option_name, $wptiles_defaults );

        $sections = array(
            'posts_query' => array (
                'title'     => __ ( "Posts", "wp-tiles" ),
                'description'
                            => __( "Which posts to display.", "wp-tiles" ),
                'fields'    => array (
                    'numberposts'  => array (
                        'label'     => __ ( 'Number of posts', "wp-tiles" ),
                        'length'    => '3',
                        'suffix'    => '',
                    ),
                    'offset'  => array (
                        'label'     => __ ( 'Offset', "wp-tiles" ),
                        'length'    => '3',
                        'suffix'    => '',
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
                        'suffix'    => '',
                    ),
                    'exclude'  => array (
                        'label'     => __ ( 'Exclude', "wp-tiles" ),
                        'length'    => '100',
                        'suffix'    => '',
                    ),
                    'meta_key'  => array (
                        'label'     => __ ( 'Meta key', "wp-tiles" ),
                        'length'    => '100',
                        'suffix'    => '',
                    ),
                    'meta_value'  => array (
                        'label'     => __ ( 'Meta value', "wp-tiles" ),
                        'length'    => '100',
                        'suffix'    => '',
                    ),
                    'post_type'  => array (
                        'label'     => __ ( 'Post Type', "wp-tiles" ),
                        'dropdown' => "post_types",
                    ),
                )
            ),
            'colors' => array (
                'title'     => __ ( "Colors", "wp-tiles" ),
                'description'
                            => __( "Which colors to give tiles that have no image.", "wp-tiles" ),
                'fields'    => array (
                    'colors'  => array (
                        'label'     => __ ( 'Comma separated list of colors', "wp-tiles" ),
                        'length'    => '200',
                        'suffix'    => '',
                    ),
                ),
            ),
            'templates' => array (
                'title'     => __ ("Templates", 'wp-tiles'),
                'description' => __("The templates.", 'wp-tiles'),
                'fields'    => array (
                    'templates'  => array (
                        'label'     => __ ( 'Templates', "wp-tiles" ),
                        'length'    => '200',
                        'suffix'    => '',
                    ),
                    'smallscreen_template'  => array (
                        'label'     => __ ( 'Small screen template', "wp-tiles" ),
                        'length'    => '200',
                        'suffix'    => '',
                    ),
                ),
            )
        );
        foreach ( $sections as $section => &$contents ) {
            foreach ( $contents['fields'] as $name => &$values ) {
                $values['default_value'] = $wptiles_defaults[$section][$name];
            }
        }
        return $sections;
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
        register_setting($wp_tiles_settings['page_name'], $wp_tiles_settings['option_name'] );

        foreach ($wp_tiles_settings["sections"] AS $section_key=>$section_value) {

            add_settings_section($section_key, $section_value['title'], array( &$this, 'plugin_section_text'), $wp_tiles_settings['page_name'], $section_value);

            foreach ($section_value['fields'] AS $field_key=>$field_value) {

                $function = (!empty($field_value['dropdown'])) ? array( &$this, 'plugin_setting_dropdown' ) : array( &$this, 'plugin_setting_string' );
                $function = (!empty($field_value['function'])) ? $field_value['function'] : $function;
                $callback = (!empty($field_value['callback'])) ? $field_value['callback'] : NULL;
                add_settings_field(
                    $wp_tiles_settings['group'].'_'.$field_key,
                    $field_value['label'],
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
            (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
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

}

new wp_tiles_settings('wp_tiles_settings_config');
?>