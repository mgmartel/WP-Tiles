=== WP Tiles ===
Contributors: Mike_Cowobo
Donate link: http://trenvo.com/
Tags: tiles, shortcode
Requires at least: 3.4.2
Tested up to: 3.4.2
Stable tag: 0.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Tiles shortcode adds fully customizable dynamic tiles to your WordPress posts and pages.

== Description ==

**This plugin is in its very early stages. Use with caution.**

*If you want to help develop this plugin, visit the [GitHub repo](https://github.com/mgmartel/WP-Tiles).*

With WP Tiles you can add tiles to your WP install by simply putting `[wp-tiles]` in your posts and pages, using [Tiles.js](https://github.com/thinkpixellab/tilesjs), as seen on [Pulse.me](http://pulse.me).

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

== Screenshots ==

1. Example of tile templates (posts and photos courtesy of [Motomonkey Adventures](http://motomonkeyadventures.com)
2. Example of tile templates
3. Example of tile templates (featured)
4. Example of tile templates (plain)
5. Excerpt of the options section

== Changelog ==

= 0.1.2 =
* Fixed shortcode attribute handling

= 0.1.1 =
* Fixed "T_PAAMAYIM_NEKUDOTAYIM" error with PHP < 5.3.

= 0.1 =
* First upload.