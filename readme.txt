=== WP Tiles ===
Contributors: Mike_Cowobo
Plugin URI: http://wordpress.org/extend/plugins/wp-tiles/
Author URI: http://trenvopress.com/
Tags: tiles, shortcode
Requires at least: 3.4.2
Tested up to: 3.5.2
Stable tag: 0.5.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Tiles shortcode adds fully customizable dynamic tiles to your WordPress posts and pages.

== Description ==

With WP Tiles you can add tiles to your WP install by simply putting `[wp-tiles]` in your posts and pages, using [Tiles.js](https://github.com/thinkpixellab/tilesjs), as seen on [Pulse.me](http://pulse.me).

See the plugin in action on the frontpage of [CreatedByDanielle.com](http://createdbydanielle.com).

*If you want to help develop this plugin, visit the [GitHub repo](https://github.com/mgmartel/WP-Tiles).*

= Usage =

WP Tiles are automatically generated based on your 20 last posts, but can be modified to show whichever query you want it to, by adjusting the defaults in the options section, or passing arguments to the shortcode.

The options section of the plugin details how to use the shortcode.

= Images =

Posts are automatically shown with either their featured image, the first attached image, or the first image found in the post itself. Posts that don't have an image automatically get a background color, randomly chosen from a selection of colors set in the options panel of WP Tiles.

= Templates =

WP Tiles comes with a couple default tile-templates. You can modify these in the options section.

== Installation ==

Install the plugin the usual way and activate it.

Under "Settings"->"WP Tiles" you can update your settings.

== Frequently Asked Questions ==

= How can I style the tiles? =

You can style WP Tiles by adding a file called 'wp-tiles.css' in your (child) themes 'inc', 'css' or 'inc/css' folder. It will automatically be loaded.

= Can I use WP Tiles as the menu for my website? =

You certainly can! For example, you filter the Tiles data to add certain pages on fixed places. For an example on how to code this, see [this gist](https://gist.github.com/4454318).

= Can I change the size of the image used for the tiles? =

Yes, use the wp-tiles-image-size filter to return the desired image size. For example:

`add_filter('wp-tiles-image-size', 'change_tile_image_size');
function change_tile_image_size( $image_size ) {
    return 'large';
}`

= Can I show tiles in my templates, for example on in the category archives? =

To show WP Tiles in your templates, three template tags are available:

`the_wp_tiles( $atts )`
This works the same as the shortcode. Pass the same arguments to the function as you would to the shortcode as an array. (The settings page shows you hints on how to do this)

`the_category_wp_tiles( $atts )`
Works the same as the_wp_tiles(), but shows posts from the current categories (for use on single posts and category pages).

`the_loop_wp_tiles()`
Can be used instead of the loop. Shows all posts that would be shown when normally using the loop.

N.B.: you can't pass any post_query attributes to `the_loop_wp_tiles()`. Set up the number of posts in your WP settings ( WP-admin -> Settings -> Reading -> "Blog pages show at most" ).

With `the_loop_wp_tiles()`, pagination works the same as it would in your other theme files. For help see [Pagination in the WordPress Codex](https://codex.wordpress.org/Pagination). (Tip: use your current category.php and just replace `while ( have_posts() ) : the_post(); [...] endwhile;` by `the_loop_wp_tiles()`)

Example basic template:

`<?php get_header(); ?>

	<section id="primary" class="site-content">
		<div id="content" role="main">

            <?php if ( function_exists ( 'the_loop_wp_tiles' ) ) the_loop_wp_tiles(); ?>

		</div><!-- #content -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>`

Example category.php for Twenty Twelve:
`<?php
/**
 * The template for displaying Category pages.
 *
 * Used to display archive-type pages for posts in a category.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<section id="primary" class="site-content">
		<div id="content" role="main">

		<?php if ( have_posts() ) : ?>
			<header class="archive-header">
				<h1 class="archive-title"><?php printf( __( 'Category Archives: %s', 'twentytwelve' ), '<span>' . single_cat_title( '', false ) . '</span>' ); ?></h1>

			<?php if ( category_description() ) : // Show an optional category description ?>
				<div class="archive-meta"><?php echo category_description(); ?></div>
			<?php endif; ?>
			</header><!-- .archive-header -->

			<?php
            if ( function_exists ( 'the_loop_wp_tiles' ) ) :
                the_loop_wp_tiles();
            else :
                while ( have_posts() ) : the_post();

                    /* Include the post format-specific template for the content. If you want to
                     * this in a child theme then include a file called called content-___.php
                     * (where ___ is the post format) and that will be used instead.
                     */
                    get_template_part( 'content', get_post_format() );

                endwhile;
            endif;

			twentytwelve_content_nav( 'nav-below' );
			?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

		</div><!-- #content -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>`

= How can I change what image is used for the tiles? =

There's a filter for that! Changing how the first image is loaded works like this:

`add_filter( 'pre_wp_tiles_image', 'my_tiles_first_image_function', 10, 2 );
function my_tiles_first_image_function( $src, $post ) {
    // Your own code that handles which image is returned
    return $src;
}`

For example, to enforce that only the featured image is loaded (and never an image from inside a post):

`add_filter( 'pre_wp_tiles_image', 'my_tiles_first_image_function', 10, 2 );
function my_tiles_first_image_function( $src, $post ) {
    $tile_image_size = apply_filters( 'wp-tiles-image-size', 'post-thumbnail', $post );

    $images = get_children( array(
        'post_parent'    => $post->ID,
        'numberposts'    => 1,
        'post_mime_type' =>'image'
    ) );

    if( !empty( $images ) ) {
        $images = current( $images );
        $src = wp_get_attachment_image_src( $images->ID, $size = $tile_image_size );
        return $src[0];
    }

    return '';
}`

= How can I use custom taxonomy queries in the shortcode? =

Custom tax queries are supported in the shortcode (but they aren't pretty) using curly braces to create arrays. Use it like this:

`
[wp-tiles posts_query="tax_query{0}{taxonomy}=my_custom_tax&tax_query{0}{field}=slug&tax_query{0}{terms}=taxonomy-cat-1"]
`

= The plugin does not work in Internet Explorer, help! =

The plugin should work in IE, but if it does not, try adding the following to your theme's header:

`<meta http-equiv="x-ua-compatible" content="IE=edge">`

This will force IE to display the site as valid as possible and similiar to the competitors like Firefox and Chrome.

(thanks to [48fps](http://wordpress.org/support/profile/48fps) for this tip!)

= Can I show images attached to the current post using WP Tiles? =

Yes! Since version 0.5.6 this is possible by using the appropriate query, which will look like this:

`[wp-tiles posts_query='post_parent={POST_ID}&post_type=attachment&posts_per_page=-1&post_mime_type=image']`

= How can I style my tiles per category? =

WP Tiles automatically adds the class slug to your tiles, so you can add your own CSS rules for each category independently.

== Screenshots ==

1. WP Tiles in action ([Created by Danielle.com](http://createdbydanielle.com))
1. WP Tiles in action ([trenvo.com](http://trenvo.com))
1. Example of tile templates (posts and photos courtesy of [Motomonkey Adventures](http://motomonkeyadventures.com)
1. Example of tile templates
1. Example of tile templates (featured)
1. Example of tile templates (plain)

== Changelog ==

= 0.5.9 =

* Added wp-tiles-byline filter

= 0.5.8 =

* Made plugin compatible with qTranslate

= 0.5.7 =

* Added categories to tile classes

= 0.5.6 =

* Make sure images are still grabbed correctly when showing post attachments

= 0.5.5 =

* Fix warning static declaration of WP_Tiles_Settings_Config::dropdowns. (Thanks @chrishas35)
* Allow for more complicated queries in the shortcode, using curly braces for arrays.

= 0.5.4 =

* Load admin class at a later stage, so Custom Post Types have the time to register

= 0.5.3 =

* Trigger window resize event for browser compatibility
* Use minified scripts unless SCRIPT_DEBUG is true

= 0.5.2 =

* Use cache when finding first image
* Made first image pluggable
* Allow multiple post types to be queried by using post_type{}=xxx&post_type{}=xxx in the shortcode

= 0.5.1 =

* Made all template functions pluggable
* Fixed dates showing date of parent post

= 0.5 =

* Updated to new version of Tiles.js
* Added customizable cellpadding option
* Improved admin documentation

= 0.4.4 =

* Fix loading of templates from settings instead of from defaults

= 0.4.3 =

* Fix bug where numberposts argument doesn't work from the settings and the shortcode

= 0.4.2 =

* Restored the_wp_tiles() template tag behaviour to pre-0.4 for backwards compatibility
* Added the_category_wp_tiles() and the_loop_wp_tiles() for more fine-grained control over WP Tiles in themes
* Fixed: since 0.4 show_selector wasn't properly saved in the settings page

= 0.4.1 =

* Updated to new version of Tiles.js

= 0.4 =

* Use existing query when used on category page (allows for pagination!)

= 0.3.6 =

* Fixed various code bugs (many thanks to [maciejkurowski](http://wordpress.org/support/profile/maciejkurowski)!)

= 0.3.5 =

* Added options to show dates in byline
* Some updates to the FAQ section

= 0.3.4 =

* Fix: tiles weren't outputted on the place of the shortcode

= 0.3.3 =

* Added template function the_wp_tiles(), which renders the tiles (if on posts or archive page for current category)

= 0.3.2 =

* Make sure that tile text background does show up when default is selected

= 0.3.1 =

* Added option to use random colors for tile texts (colors and opacity configurable)

= 0.3 =

* Added option for cut-off point small-screen / big-screen templates
* Hide template selector if small screen template is used
* Added all possible 'order by' parameters to the settings screen
* Improved settings screen
* Various small fixes (thanks raubvogel)

= 0.2.2 =

* Added extra display options: show text and byline contents (choose whether to show categories, excerpts or nothing at all)

= 0.2.1 =

* Centered cut off tile images
* Tiles can now be inserted with a greater offset from the top

= 0.2 =

* Fix resize bug - now rest of content is displaced properly
* Background now always stretches to cover
* Also checks for featured image (oops)
* Fixed that templates would sometimes get lost when saving the first time
* Extended wp-tiles-data filter
* Added 'wp-tiles-hide-byline' filter, to hide the byline box on a per-post basis (only programmatically..)
* Various other fixes
* Fix loading of stylesheet from (child)theme
* Fix small screen (mobile) template would add an extra row of single posts
* Added wp-tiles-image-size filter to set the image size used for the tiles

= 0.1.3 =

* Change the way templates are handled in the shortcode - now you can choose a predefined template by its name in the shortcode (eg. `[wp-tiles template="Banner"]`).

= 0.1.2 =

* Fixed shortcode attribute handling

= 0.1.1 =

* Fixed "T_PAAMAYIM_NEKUDOTAYIM" error with PHP < 5.3.

= 0.1 =

* First upload.

== Upgrade Notice ==

= 0.5 =

* You can now customize the padding between cells.

= 0.4.2 =

* The previous update changed the behaviour of the_wp_tiles() in plugins. This is restored. If you changed your template files to suit yesterday's 0.4 or 0.4.1, you can simple change the_wp_tiles() to the_loop_wp_tiles() or the_category_wp_tiles(). See the readme for more info on the new template tags.

= 0.4 =

* WP Tiles can now be used as a replacement for your category pages with pagination! See the readme on how to do this.

= 0.3 =

* In this version you can set at which point you want to switch to the small screen template, or disable this altogether.
