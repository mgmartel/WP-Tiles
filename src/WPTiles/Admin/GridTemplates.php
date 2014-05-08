<?php namespace WPTiles\Admin;

use WPTiles\WPTiles;

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

class GridTemplates extends \WPTiles\Abstracts\WPSingleton
{
    const POST_TYPE = WPTiles::GRID_POST_TYPE;

    private static $_default_template = "AA.B\nAA.B\n.CC.";

    public function init() {
        $this->add_action( 'add_meta_boxes_' . self::POST_TYPE, 'setup_admin_page' );
        $this->add_action( 'save_post_' . self::POST_TYPE,      'maybe_save_post' );
    }

    public function maybe_save_post( $post_id ) {
        static $saving = false;

        if ( $saving )
            return;

        if ( !isset( $_POST['save_grid_template'] ) || !isset( $_POST['grid_template'] ) )
            return;

        if ( ! wp_verify_nonce( $_POST['save_grid_template'], 'save_grid_template' ) )
            return;

        // @todo Do we want to autosave?
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( !current_user_can( 'edit_post', $post_id ) )
            return;

        // You still here?
        $saving = true;

        $this->_save_template( $post_id );

        $saving = false;
    }

        private function _save_template( $post_id ) {
            $grid_template = wp_kses_post( $_POST['grid_template'] );
            wp_update_post( array(
                'ID'           => $post_id,
                'post_content' => $grid_template
            ) );
        }

    public function setup_admin_page() {
        //add_action( 'edit_form_after_title', array( &$this, 'render_template_editor' ) );
        add_meta_box( 'wp_tiles_template_preview', __( 'Preview', 'wp-tiles' ), array( &$this, 'render_template_preview' ), null, 'normal' );
        add_meta_box( 'wp_tiles_template_editor', __( 'Grid Editor', 'wp-tiles' ), array( &$this, 'render_template_editor' ), null, 'side' );

        $screen = get_current_screen();
        $screen->add_help_tab( array(
           'id'       => 'grid_editor',
           'title'    => "Grid Editor",
           'content'  =>  "<h4>" . __( "Grid Editor" ) . "</h4>"
                        . "<p>" . __( "Use the grid editor below to create a tiles template. Play around with it, the example will be automatically updated." ) . "</p>"
                        . "<p>" . __( "Here's how it works: A template is an array of strings where each string represents a row in the grid. We parse that array and form a 'tile' whenever the we detect repeated characters in adjacent cells.") . "</p>"
                        . "<p>" . __("So, in the example to the left the A's form a 2x2 tile, the B's for a 1x2 tile and the C's form a 2x1 tile. You can use just about any character you want but we tend to use capital letters as a convention. ") . "</p>"
                        . "<p>" . __("Periods are special. they're reserved to always mean single cells. All white space is ignored (it turns out that these are a lot easier to read if you pad the characters a little).") . "</p>",
        ) );

        wp_enqueue_style( 'wp-tiles' );

        $deps = array( 'jquery', 'wp-tiles', 'jquery-autosize' );
        if ( $pointers = $this->setup_walkthrough() )
            $deps[] = 'wp-pointer';

        wp_enqueue_script( 'jquery-autosize',  WP_TILES_ASSETS_URL . 'js/jquery.autosize.js', array( "jquery" ),  "1.16.17", true );
        wp_enqueue_script( 'wp-tiles-grid-templates', WP_TILES_ASSETS_URL . 'js/wp-tiles-grid-templates.js', $deps, WP_TILES_VERSION, true );

        if ( $pointers )
            wp_localize_script( 'wp-tiles-grid-templates', 'wpTilesPointers', $pointers );
    }

    private function setup_walkthrough() {
        // Set up for mulitple pointers. Simply add them to this array
        $pointers = array(
            'wptiles-grid-editor-1' => array(
                'target' => '#wp_tiles_template_editor',
                'options' => array(
                    'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
                        __( 'WP Tiles Grid Editor' ,'wp-tiles'),
                        __( 'Use this box to edit the grid. The grid preview on the left will automatically update when you change the template. For more help, click the "Need help?" link in this box.','wp-tiles')
                    ),
                    'position' => array( 'edge' => 'bottom', 'align' => 'top' )
                )
            )
        );

        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $valid_pointers =array();

        foreach ( $pointers as $pointer_id => $pointer ) {

            if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
                continue;

            $pointer['pointer_id'] = $pointer_id;

            $valid_pointers[] =  $pointer;
        }

        if ( empty( $valid_pointers ) )
            return;

        wp_enqueue_style( 'wp-pointer' );

        return array_reverse( $valid_pointers );
    }

    public function render_template_editor( $post ) {
        $template = $post->post_content ? $post->post_content : self::$_default_template;

        wp_nonce_field( 'save_grid_template', 'save_grid_template' );
        echo "<textarea name='grid_template' id='grid_template' class='grid-template-editor' spellcheck='false'>" . $template . "</textarea>";
        echo "<p><a href='#contextual-help-wrap' class='wp-tiles-show-help'>Need help?</a></p>";
    }

    public function render_template_preview() {
        echo "<div class='wp-tiles-container wp-tiles-loaded'><div id='grid-template-demo' class='wp-tiles-grid'></div></div>";
    }

    public static function get_default_template() {
        return wp_tiles()->format_grid( self::$_default_template );
    }

    public static function install_default_templates( $force = false ) {
        if ( !$force ) {
            $current = get_posts( array( 'post_type' => WPTiles::GRID_POST_TYPE, 'posts_per_page' => 1 ) );

            if ( !empty( $current ) )
                return;
        }

        $default_templates = array (
            "News"       =>   "AABB.\n"
                            . "AA.CC\n"
                            . ".DDEE\n"
                            . "FF.EE",

            "Simple"     =>   "AA...\n"
                            . "AABB.\n"
                            . "..BB.",

            "Fancy"      =>   "JJ..EE\n"
                            . ".AA.EE\n"
                            . "BAAFF.\n"
                            . "B.DD.H\n"
                            . "CCDDGH\n"
                            . "CC..G.",

            "Featured"   =>   ".AAA.\n"
                            . ".AAA.\n"
                            . ".AAA.",

            "Plain"      =>   ".....",
            "Mobile"     =>   "AA\n"
                            . "..",

        );

        foreach( $default_templates as $title => $template ) {
            wp_insert_post( array(
                'post_type'    => WPTiles::GRID_POST_TYPE,
                'post_title'   => $title,
                'post_content' => $template,
                'post_status'  => 'publish'
            ) );
        }

    }
}