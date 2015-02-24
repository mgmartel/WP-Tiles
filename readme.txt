=== WP Tiles ===
Contributors: Mike_Cowobo
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=U32MTZ9VGHLKS
Plugin URI: http://wp-tiles.com/
Author URI: https://trenvo.com/
Tags: tiles, grid, shortcode, gallery, display, list, page, pages, posts, query
Requires at least: 3.4.2
Tested up to: 4.1.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add beautiful, fully customizable post tiles or tiled galleries anywhere on your WordPress site easily with WP Tiles.

== Description ==

WP Tiles is a WordPress plugin that allows anyone to create beautiful tiled layouts for their website.

With WP Tiles you can:

* Display your latest blog posts in a fully customizable and responsive grid layout
* Show a tiled archive of any post type, category or other taxonomy
* Create beautiful tiled image galleries using the WP Media Uploader

> To experience for yourself how easy it is to create grids with WP Tiles, checkout the [grid designer](http://wp-tiles.com/#grid-editor) on our website.

= Usages =

* **Displaying Posts**: The simplest way of using WP Tiles is by placing the `[wp-tiles]` shortcode somewhere on any of your pages or posts. Just add [wp-tiles] to your post to view the default tiles. However, WP Tiles is extremely flexible, allowing you to specify what content you want to display on your tiles, whether to display background images or skip posts that have no featured image, and much much more.

* **Create a tiled gallery**: You can use WP Tiles to replace your default WordPress `[gallery]` shortcode. Simply create a gallery and select the grid you want to use, and you are good to go. Images can link to the attachment page, the image file, or be opened using a Thickbox, or with JetPack's Carousel (or the [Gallery Carousel without JetPack](http://wordpress.org/plugins/carousel-without-jetpack/) plugin!).

> **Update:** We recently released our *free* Twenty Fourteen child theme for WP Tiles. Check it out [here](http://wp-tiles.com/tiledfourteen/).

= Other Features =

*Pagination using the shortcode*
The shortcode can be **automatically paginated**. The plugin includes AJAX pagination (without page reload), previous/next page navigation and page number navigation.

*SEO Friendly*
The Tiles require JavaScript to be rendered, but they are readable by bots that don't use JavaScript, and even include basic Schema.org microtags and are generated using clean HTML5. This means that you can safely use WP Tiles as a primary element on your page without worrying about SEO.

*Grid Builder and Tile Designer*
The admin interface comes with a live editor for grid templates, so you can immediately see what grid templates look like. Tiles and bylines also come with a collection of hover effects and a list of adjustable properties that you can edit and preview immediately in the Tile Designer.

*Completely custom bylines*
The bylines are now generated using a custom template, the mark up and content of which you can control entirely. You could even start showing complete posts on tiles if you want to!

For complete documentation, check out the website at [wp-tiles.com](http://wp-tiles.com/).

> If you want to help develop this plugin, visit the [GitHub repo](https://github.com/mgmartel/WP-Tiles).

== Installation ==

Install the plugin the usual way, and activate it.

You will now have a menu item for WP Tiles where you can edit your Grid templates and the plugin default options. In your post and page editor, you can generate the WP Tiles shortcode by clicking the button the toolbar.

For complete documentation, check out the website at [wp-tiles.com](http://wp-tiles.com/).

== Frequently Asked Questions ==

= I get a notice saying that I need to upgrade my PHP version, what does that mean? =

Check out [this post](http://wp-tiles.com/docs/upgrading-from-php-5-2-to-php-5-3/) on our website.

= My images are blurry / pixelated / low quaility! What's up? =

WP Tiles by default uses the `large` image size provided by WordPress. This is the same size as you would get when you insert an image into a page and select 'Large' for its size. However, users (that's you!) can change this size in their admin panel (in Settings -> Media). If the `large` size is smaller than the largest tile, the images will be stretched (proportionally).

There are 3 ways out:

1. Change your Large image size to be bigger (in Settings -> Media)
2. Select a different size for WP Tiles to use in the admin panel (WP Tiles -> Image Settings)
3. Override the image size in the shortcode: `[wp-tiles image_size='full']`

= WP Tiles is loading slow! What can I do? =

See the previous question! WP Tiles uses the `large` image size in WordPress by default. If that size is gigantic, or if you have set WP Tiles to use an even larger size (like `full`), then WP Tiles will load heavy images. Adjusting the image size down should make your website load faster again.

= WP Tiles is not selecting the image I want from posts! Hm? =

By default, WP Tiles will try its hardest to select an image from your posts, in this order:
1. Featured Image
2. First attached image
3. First image in post itself

If you want to limit this, there are 3 options, either in the admin panel (WP Tiles -> Image Settings), or in the shortcode (`[wp-tiles image_source=...]`):

Attached Only `attached_only` - Don't look inside post content for image
Featured Image Only `featured_only` - Only use Featured Image
Only show image for Media Posts `attachment_only` - Don't show image, unless the post itself is a media post

= Can I change what is shown on the tiles? =

Yes! The content of the tiles is determined by the 'byline template'. As with everything in WP Tiles, you can either set the template in the options panel, or put it in the shortcode directly. In the shortcode use the `byline_template` attribute for the byline on *image tiles*. Use `byline_template_textonly` for bylines on *text-only tiles*.

In the template, wherever you put the following tags, they will be replaced by content from the post:

* `%title%, %content%, %date%, %excerpt%, %link%, %author%, %featured_image%` - All taken from the post
* `%categories%` - Comma separated list of categories
* `%category_links%` - Like above, but with links
* `%tags, %tag_links%` - Same as categories
* `%meta:META_KEY%` - Replace META_KEY by the meta key you want to display
* `%tax:TAXONOMY%, %tax_links:TAXONOMY%`

Also see [this](http://wp-tiles.com/docs/byline-templates/) page in our documentation.

= Can I add WP Tiles to my theme or plugin? Or can I show tiles in my templates, for example on in the category archives? =

To show WP Tiles in your templates, there are template tags available. See the documentation on the website [here](http://wp-tiles.com/docs/template-tags).

== Screenshots ==

1. WP Tiles showing posts with a hover effect ([Designer Homepage example](http://wp-tiles.com/example-pages/designer-homepage/))
1. WP Tiles displaying similar posts ([Architectural Blog example](http://wp-tiles.com/example-pages/architectural-blog/))
1. WP Tiles gallery ([Travel Blog example](http://wp-tiles.com/travel-blog/))
1. Grid editor in the backend
1. Tile designer
1. Shortcode editor

== Changelog ==

= 1.0.1 =

* Released [TiledFourteen](http://wp-tiles.com/tiledfourteen/), a free theme for WP Tiles
* Added: Filter for the 'Load More' text (`wp_tiles_load_more_text`) when using AJAX pagination
* Fix: WP Tiles was conflicting with Squirrly admin page. Removed fixed position on the the admin page
* Fix: Path for the textdomain was not relative, causing errors
* Fix: WordPress uses another paging parameter on the front page (page instead of paged). WP Tiles now detects this

= 1.0 =

* Complete overhaul of the plugin :) New features include:
* Grid Builder and Tile Designer
* (AJAX) Pagination using the shortcode
* Tiled Galleries
* New shortcode syntax and button in editor
* Made the output SEO Friendly
* Completely custom bylines
* Modern Styles
* Grids repeat infinitely (no fallback to 1×1 template)
* Tile animation (on load, window resize or template selection) is now optional
* Automatic elipsis for multi-line content
* Hides current post when used inside the loop (can be disabled)

= 0.6.1 =

* Last version that is compatible with PHP5.2.X. Added compatibility message for when the 1.0 update happens.

= 0.6 =

* Last update before release of WP Tiles 1.0. This update contains information and notices about the forthcoming update.

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

= 1.0 =

WP Tiles 1.0 is a complete overhaul of the plugin. One thing that has greatly improved is the shortcode syntax. We have done our best to make sure that shortcodes created for the old version still work, but we can't guarantee that they will look the same as before the update. Please read the [upgrade guide](https://trenvo.com/blog/2014/05/upgrading-to-wp-tiles-1-0/) and make sure that all your pages with WP Tiles on them are still working after the update.

= 0.6.1 =

= 0.6 =

WP Tiles will update to version 1.0 **soon**. Please read the [announcement](http://wp-tiles.com/blog/announcing-wp-tiles-1-0/) and be aware that the update after this one will not be 100% compatible.

= 0.5 =

You can now customize the padding between cells.

= 0.4.2 =

The previous update changed the behaviour of the_wp_tiles() in plugins. This is restored. If you changed your template files to suit yesterday's 0.4 or 0.4.1, you can simple change the_wp_tiles() to the_loop_wp_tiles() or the_category_wp_tiles(). See the readme for more info on the new template tags.

= 0.4 =

WP Tiles can now be used as a replacement for your category pages with pagination! See the readme on how to do this.

= 0.3 =

In this version you can set at which point you want to switch to the small screen template, or disable this altogether.
