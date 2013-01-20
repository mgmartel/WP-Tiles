=== WP Tiles ===
Contributors: Mike_Cowobo
Donate link: http://trenvo.com/wp-tiles/
Tags: tiles, shortcode
Requires at least: 3.4.2
Tested up to: 3.5
Stable tag: 0.3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Tiles shortcode adds fully customizable dynamic tiles to your WordPress posts and pages.

== Description ==

With WP Tiles you can add tiles to your WP install by simply putting `[wp-tiles]` in your posts and pages, using [Tiles.js](https://github.com/thinkpixellab/tilesjs), as seen on [Pulse.me](http://pulse.me).

See the plugin in action on the frontpage of [Trenvo.com](http://trenvo.com/) and [CreatedByDanielle.com](http://createdbydanielle.com).

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

To show WP Tiles in your templates, simply use the provided the_wp_tiles() function. If you do this on a single page or a category archive, it will render all the tiles from the current category/categories. You can also pass all the attributes you can pass to the shortcode.

Example template:
`<?php get_header(); ?>

	<section id="primary" class="site-content">
		<div id="content" role="main">

            <?php if ( function_exists ( 'the_wp_tiles' ) ) the_wp_tiles(); ?>

		</div><!-- #content -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>`

== Screenshots ==

1. WP Tiles in action ([Created by Danielle.com](http://createdbydanielle.com))
1. WP Tiles in action ([trenvo.com](http://trenvo.com))
1. Example of tile templates (posts and photos courtesy of [Motomonkey Adventures](http://motomonkeyadventures.com)
1. Example of tile templates
1. Example of tile templates (featured)
1. Example of tile templates (plain)

== Changelog ==

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

= 0.3 =
In this version you can set at which point you want to switch to the small screen template, or disable this altogether.
