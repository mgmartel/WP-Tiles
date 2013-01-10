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
            "group"         => "wp-tiles",
            "page_name"     => "wp-tiles",
            "option_name"   => self::$option_name,
            "nav_title"     => "WP Tiles",

            // Page blabla
            "title"         => "WP Tiles",
            "intro_text"    => sprintf ( __( "Use this page to set the default settings used with the %s shortcode.\n"
                                . "<h3>Usage</h3>\n"
                                . "<p>To show wp-tiles on any of your pages or in your posts, simply include the shortcode %1\$s</p>\n"
                                . "<p>WP Tiles will automatically show posts based on the preferences you set below. To override the settings on a per-post basis, pass arguments to the shortcode as indicated in each section.</p>\n"
                                . "<h3>Advanced</h3>\n"
                                . "<p>You can also style WP Tiles in your (child) theme. Simply add 'wp-tile.css' in a folder called 'css', 'inc' or 'inc/css' to your theme.</p><hr>", 'wp-tiles' ),
                                "<code>[wp-tiles]</code>"
                            ),

            // The actual options
            "sections"          => self::sections(),
            "dropdown_options"  => self::dropdowns(),
            "reset"             => true,

            // And some functions
            "sanitize"          => array ( 'WP_Tiles_Settings_Config', 'sanitize' ),
            "scripts"           => array ( 'WP_Tiles_Settings_Config', 'load_scripts' ),
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
            "byline" => array (
                "nothing"       =>  __( "Nothing", 'wp-tiles' ),
                "cats"          =>  __( "Categories" ),
                "excerpt"       =>  __( "Excerpt" ),
            ),
            "text"  => array (
                "show"          => __( "Show" ),
                "hide"          => __( "Hide" )
            )
        );
    }

    protected static function sections() {
        global $wptiles_defaults;
        require ( WPTILES_DIR . '/wp-tiles-defaults.php');

        $wptiles_options = get_option( self::$option_name );
        $wptiles_defaults = shortcode_atts( $wptiles_defaults, $wptiles_options);

        $sections = array(
            'display' => array (
                'title'         => __("Display", 'wp-tiles'),
                'description'   => __("What information is displayed on the tiles.", 'wp-tiles'),
                'fields'        => array (
                    'text'          => array (
                        'label'         => __("Text",'wp-tiles'),
                        'description'   => __("Display text on Tiles with images", 'wp-tiles'),
                        'dropdown'      => 'text'
                    ),
                    'byline'        => array (
                        'label'         => __("Byline",'wp-tiles'),
                        'description'   => __("What to show under the title in the tiles' 'byline'.", 'wp-tiles'),
                        'dropdown'      => 'byline'
                    ),
                ),
            ),
            'colors' => array (
                'title'     => __( "Colors", "wp-tiles" ),
                'description'
                            => sprintf ( __( "Posts that have no image randomly get assigned a background color. Here you can define the colorscheme used by WP Tiles.%s\n"
                                . "Change this in the shortcode like this: %s", "wp-tiles" ),
                                "<br><br>",
                                "<code>[wp-tiles colors='#FF0000,#00FF00, #0000FF']</code>"
                                    ),
                'fields'    => array (
                    'colors'  => array (
                        'label'     => __( 'Colors', 'wp-tiles'),
                        'description'
                                    => __ ( 'Leave the textfield empty to remove a color.', "wp-tiles" ),
                        'length'    => '200',
                        'function'  => array ( 'WP_Tiles_Settings_Config', 'plugin_setting_colorpickers' ),
                    ),
                ),
            ),
            'templates' => array (
                'title'     => __( "Templates", 'wp-tiles' ),
                'description' => sprintf ( __("You can include multiple templates in WP-Tiles. To hide the template chooser, simply add only a single template.%s\n"
                    ."Templates are formulated as per tiles.js. Check the %s for a demonstration in making templates.%1\$s\n"
                    ."In the shortcode, you can set which template to use, like this: %s", 'wp-tiles'),
                        "<br><br>",
                        "<a href='http://www.pulse.me/app/dev/#dev-section-tilejs' target='_blank'>Pulse.me website</a>",
                        '<code>[wp-tiles template="Template_Name"]</code>'
                        ),
                'fields'    => array (
                    'templates'  => array (
                        'label'     => __ ( 'Templates', "wp-tiles" ),
                        'length'    => '200',
                        'description'
                                    => "Leave a template empty to remove it.",
                        'type'      => 'textareas_name'
                    ),
                    'small_screen_template'  => array (
                        'label'     => __ ( 'Small screen template', "wp-tiles" ),
                        'length'    => '200',
                        'type'      => 'textarea'
                    ),
                    'small_screen_width'    => array (
                        'label'     => __ ( 'Small screen width','wp-tiles'),
                        'length'    => '3',
                        'suffix'    => 'px',
                        'description'
                                    => "Maximum width of the tiles element before switching to small screen template (if you experience problems with selecting templates - try lowering this value).<br><br>Set to 0 to disable switching to small screen templates."
                    ),
                    'show_selector'  => array (
                        'label'     => __ ( 'Show template selector', "wp-tiles" ),
                        'type'      => 'checkbox',
                        'value'     => 'true',
                        'description'
                                    => sprintf ( __('To show or hide the selector on a per-post basis, add %s to the shortcode.', 'wp-tiles'),
                                        "<code>show_selector=true/false</code>"
                                        ),
                    ),
                ),
            ),
            'posts_query' => array (
                'title'     => __ ( "Posts", "wp-tiles" ),
                'description'
                            => sprintf ( __( "Default arguments to be passed when querying tiles using just %s. These can be modified by passing 'posts_query=' as an argument to the shortcode.%s\n"
                                . "For example: %s.", "wp-tiles" ),
                                    "[wp-tiles]",
                                    "<br><br>",
                                    "<code>[wp-tiles posts_query='numberposts=5&post_type=page']</code>"
                                ),
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
        if ( defined ( "WPT_SANITIZED" ) && WPT_SANITIZED )
            return $input;

        define ( "WPT_SANITIZED", true );

        if ( isset ( $_POST['Reset'] ) ) {
            return '';
        }

        // Templates ( name => field )
        $i = 0; $new_a = array();
        foreach ( $input['templates']['templates']['name'] as $v ) {
            if ( ! empty ( $v ) )
                $new_a[$v] = str_replace ( "\r", "", $input['templates']['templates']['field'][$i] );
            $i++;
        }
        $input['templates']['templates'] = $new_a;

        $input['templates']['small_screen_template'] = str_replace ( "\r", "", $input['templates']['small_screen_template'] );

        return $input;
    }

    /**
     * Colorpicker method, specific for this plugin
     *
     * @global type $wp_tiles_settings
     * @param type $value
     */
    public static function plugin_setting_colorpickers($value = NULL) {
        global $wp_tiles_settings;
        $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;

        do_action('before_plugin_setting_colorpickers', $value, $default_value );

        $i = 0;
        foreach ( $default_value as $color ) {
            printf('<input id="wptiles-color-%s" type="text" name="%s" value="%s" size="40" />
                <div id="color-picker-%1$s" class="wp-tiles-colorpickers"></div>',
                $i,
                "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}][{$i}]",
                $color
            );
            $i++;
        }

        do_action('after_plugin_setting_colorpickers', $value, $default_value );
    }

    public static function load_scripts() {
        wp_enqueue_style( 'farbtastic' );
        wp_enqueue_script( 'farbtastic' );

        wp_enqueue_script( 'wp-tiles-admin-custom', WPTILES_INC_URL . '/js/admin-custom.js', array ('jquery', 'farbtastic') );
    }
}


class WP_Tiles_Settings {

    public function __construct() {
        global $wp_tiles_settings;
        $wp_tiles_settings = WP_Tiles_Settings_Config::settings();

        add_action('admin_init', array( &$this, 'plugin_admin_init'));
        add_action('admin_menu', array( &$this, 'plugin_admin_add_page'));

        add_action('admin_enqueue_scripts', array ( &$this, 'load_scripts') );
        if ( ! empty ( $wp_tiles_settings['scripts'] ) )
            add_action('admin_enqueue_scripts', $wp_tiles_settings['scripts'] );
    }

        /**
         * PHP4
         * @param type $settings_class
         */
        function wp_tiles_settings( $settings_class ) {
            $this->__construct( $settings_class );
        }

    public function load_scripts() {
        wp_enqueue_script( 'wp-tiles-admin', WPTILES_INC_URL . '/js/admin.js' );
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
            $wp_tiles_settings['title'],$wp_tiles_settings['intro_text']
        );

        settings_fields($wp_tiles_settings['group']);
        do_settings_sections($wp_tiles_settings['page_name']);

        printf('<input type="submit" name="Submit" value="%s" />
            <pre>',
            __('Save Changes')
        );
        if ( $wp_tiles_settings['reset'] ) {
            printf('<input type="submit" name="Reset" value="%s" />
                <pre>',
                __('Reset')
            );
        }
        echo "</form></div>";
    }

    function plugin_admin_init(){
        global $wp_tiles_settings;

        register_setting(
            $wp_tiles_settings['page_name'],
            $wp_tiles_settings['option_name'],
            $wp_tiles_settings['sanitize']
        );

        foreach ($wp_tiles_settings["sections"] as $section_key=>$section_value) {

            add_settings_section($section_key, $section_value['title'], array( &$this, 'plugin_section_text'), $wp_tiles_settings['page_name'], $section_value);

            foreach ($section_value['fields'] as $field_key => $field_value ) {
                $this->add_option( $field_key, $field_value, $section_key );
            }
        }
    }

    protected function add_option ( $field, $value, $section_key = NULL ) {
        global $wp_tiles_settings;

        $function = array( &$this, 'plugin_setting_string' );
        if (!empty($value['dropdown']))
            $function = array( &$this, 'plugin_setting_dropdown' );
        elseif ( ! empty ( $value['function'] ) )
            $function = $value['function'];
        elseif ( ! empty ( $value['type'] ) ) {
            $function = array( &$this, "plugin_setting_{$value['type']}" );
        }

        $desc = (!empty ($value['description'])) ? sprintf("<br><em>%s</em>",$value['description']) : '';
        $param = sprintf( " <code>%s</code>",$field );

        add_settings_field(
            $wp_tiles_settings['group'].'_'.$field,
            $value['label'] . $param . $desc,
            $function,
            $wp_tiles_settings['page_name'],
            $section_key,
            array_merge($value,array('name' => $field, 'group' => $section_key))
        );
    }

    function plugin_section_text($value = NULL) {
        global $wp_tiles_settings;
        printf("%s", $wp_tiles_settings['sections'][$value['id']]['description']);
    }

    function plugin_setting_string($value = NULL) {
        global $wp_tiles_settings;
        $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;

        do_action('before_plugin_setting_string', $value, $default_value );

        printf('<input id="%s" type="text" name="%s" value="%s" size="%s" /> %s',
            $value['name'],
            "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}]",
            $default_value,
            (!empty ($value['length'])) ? $value['length'] : '40',
            (!empty ($value['suffix'])) ? $value['suffix'] : NULL );

        do_action('after_plugin_setting_string', $value, $default_value );
    }

    function plugin_setting_checkbox($value = NULL) {
        global $wp_tiles_settings;
        $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;

        do_action('before_plugin_setting_string', $value, $default_value );

        $checked = ( $default_value == $value['value'] ) ? 'checked' : '';
        printf('<input id="%s" type="checkbox" name="%s" value="%s" size="40" %s/>',
            $value['name'],
            "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}]",
            $value['value'],
            $checked
        );
        do_action('after_plugin_setting_string', $value, $default_value );
    }

    function plugin_setting_dropdown($value = NULL) {
        global $wp_tiles_settings;
        $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;

        $current_value = $default_value;
        $chooseFrom = "";
        $choices = $wp_tiles_settings['dropdown_options'][$value['dropdown']];

        do_action('before_plugin_setting_dropdown', $value, $default_value, $choices);

        foreach($choices AS $key=>$option) {
            $chooseFrom .= sprintf('<option value="%s" %s>%s</option>',
                $key,($current_value == $key ) ? ' selected="selected"' : NULL,$option);
        }

        printf('
            <select id="%s" name="%s">%s</select>',
            $value['name'],
            "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}]",
            $chooseFrom
            //(!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL
        );

        do_action('after_plugin_setting_dropdown', $value, $default_value, $choices);
    }

    public function plugin_setting_textareas ( $value = NULL ) {
        global $wp_tiles_settings;
        $default_value = ( ! empty ( $value['default_value'] ) ) ? $value['default_value'] : NULL;

        do_action('before_plugin_setting_textareas', $value, $default_value);

        foreach ( $default_value as $k => $current ) {
            printf ( "<textarea style='height:100px' id='%s' name='%s'>%s</textarea>",
                $current['name'],
                "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}][{$k}]",
                $current
            );
        }
        do_action('after_plugin_setting_textareas', $value, $default_value);
    }

    public function plugin_setting_textareas_name ( $value = array() ) {
        global $wp_tiles_settings;
        $default_value = ( ! empty ( $value['default_value'] ) ) ? $value['default_value'] : array();

        do_action('before_plugin_setting_textareas_name', $value, $default_value);

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
        echo "<div style='float:left;cursor:pointer' class='add-textarea'><h3>" . __("[+] Add") . "</h3></div>";

        do_action('after_plugin_setting_textareas_name', $value, $default_value);
    }

    public function plugin_setting_textarea ( $value = NULL ) {
        global $wp_tiles_settings;
        $default_value = ( ! empty ( $value['default_value'] ) ) ? $value['default_value'] : NULL;

        do_action('before_plugin_setting_textarea', $value, $default_value);

        printf ( "<textarea style='height:100px' id=%s name=%s>%s</textarea>",
            $value['name'],
            "{$wp_tiles_settings['option_name']}[{$value['group']}][{$value['name']}]",
            $default_value
        );

        do_action('after_plugin_setting_textarea', $value, $default_value);
    }

}

new wp_tiles_settings();
?>