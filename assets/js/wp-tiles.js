(function($){
  // Public methods
  $.wptiles = {
    // debounce utility from underscorejs.org
    debounce: function(func, wait, immediate) {
        var timeout;
        return function() {
          var context = this, args = arguments;
          var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
          };
          if (immediate && !timeout) func.apply(context, args);
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
        };
    },

    resizeParent: function($el, padding) {
      var tiles = $el.children('.wp-tiles-tile'),
          tileOffsetTop = parseInt ( $el.offset().top ),
          max = 0, newHeight;

      // Iterates over every tile to find the bottom. Is there a faster way?
      tiles.each(function(){
        var $e = $(this), bottom = $e.height() + $e.offset().top;
        if ( bottom > max )
          max = bottom;
      });

      newHeight = max - tileOffsetTop + parseInt(padding) + "px";

      $el.parent('.wp-tiles-container').css('height', newHeight );
    }
  };

  $.fn.extend({
    wptiles: function(opts){
      var
          // Locals
          $el = $(this),
          $templates  = $("#" + opts.id + "-templates"),
          $pagination = $("#" + opts.id + "-pagination"),
          grid,
          curr_large_template = false,
          using_small = false,

          // Private Methods
          get_first_grid = function(){
            var grid;

            $.each(opts.grids, function() {
              grid = this;
              return false;
            });

            return grid;
          },

          get_template = function(){
            var is_small;

            // First run?
            if ( !curr_large_template )
              curr_large_template = get_first_grid();

            // Setup for responsiveness?
            if ( !opts.breakpoint )
              return curr_large_template;

            is_small = $el.width() < opts.breakpoint;

            if ( is_small && !using_small ) {
                $templates.hide();

                // Save large template
                using_small = true;
                return opts.small_screen_grid;

            } else if ( !is_small && using_small ) {
                $templates.show();
                using_small = false;

                return curr_large_template;
            }

            return ( is_small ) ? opts.small_screen_grid : curr_large_template;
          },

          set_template = function(template){
            curr_large_template = template;
            grid.template = template;
          },

          onresize = function(){
            $.wptiles.resizeParent($el,opts.padding);
            $('.wp-tiles-byline').dotdotdot();

            $el.trigger('wp-tiles:resize');
          },

          style_tiles = function() {
            var set_bylines_color = function( $bylines, color ) {
                  $bylines.css('color', color);                 // Sets color attribute on parent element
                  $bylines.find(':header').css('color', color); // Because headers sometime have explicit CSS colors, do the H tags explicitly
                },

                $image_bylines = $('.wp-tiles-tile-with-image .wp-tiles-byline', $el);

            if ( $image_bylines.get(0) ) {

              // Set color and opacity
              if ( 'random' !== opts.byline_color ) {
                $image_bylines.css('background-color', opts.byline_color); // Byline color includes alpha
              }

              // Set the byline (max)height
              $image_bylines.css(opts.byline_height_auto ? 'max-height' : 'height',opts.byline_height + '%');

              if ( opts.image_text_color ) {
                set_bylines_color($image_bylines, opts.image_text_color);
              }
            }

            if ( opts.text_color ) { // Make sure we have to set the option before selecting the elements

              var $text_only_bylines = $('.wp-tiles-tile-text-only .wp-tiles-byline', $el);
              if ($text_only_bylines.get(0)) {
                set_bylines_color($text_only_bylines, opts.text_color);
              }

            }

          };

      // We're doing JS
      $el.addClass('wp-tiles-loaded');

      // Init the grids
      if ( opts.breakpoint )
        opts.small_screen_grid = Tiles.Template.fromJSON(opts.small_screen_grid);

      var grids = {};
      $.each(opts.grids,function(key){
        grids[key] = Tiles.Template.fromJSON(this);
      });

      opts.grids = grids;

      // Setup the Tiles grid
      grid = $.extend(new Tiles.Grid($el),{
        cellPadding: parseInt(opts.padding),

        template: get_template(),

        templateFactory: {
            get: function(numCols, numTiles) {
              //var numRows      = Math.ceil(numTiles / numCols),
              var template     = get_template().copy(),
                  missingRects = numTiles - template.rects.length;

              while (missingRects > 0) {
                var copyRects = [],
                    i, t = get_template().copy();

                if ( missingRects <= t.rects.length ) {
                  copyRects = t.rects;
                  missingRects = 0;

                } else {
                  for (i = 0; i < t.rects.length; i++) {
                    copyRects.push(t.rects[i].copy());
                  }

                  missingRects -= t.rects.length;
                }

                template.append(
                  new Tiles.Template(copyRects, t.numCols, t.numRows)
                );
              }

              return template;
            }
        },

        resizeColumns: function() {
          return this.template.numCols;
        },

        createTile: function(data) {
          var tile = new Tiles.Tile(data.id,data),
              $el  = tile.$el,
              i    = parseInt(data.id.match(/[0-9]{1,}/)),
              // @todo Custom colors using data-attributes?
              color = opts.colors[i % opts.colors.length];

          $el
            .css("background-color", color);

          // Is this an image tile?
          if ( $('.wp-tiles-tile-with-image',$el).get(0) ) {

            // Then maybe also add the color to the byline
            if ( 'random' === opts.byline_color ) {

              var $byline  = $('.wp-tiles-byline',$el),
                  alpha = opts.byline_opacity,
                  //rgb   = $byline.css('background-color'),
                  rgb   = color,
                  rgbx  = rgb.substr(0,4) === 'rgba' ? rgb : rgb.replace('rgb', 'rgba').replace(')', ',' + alpha + ')'),
                  comma = rgbx.lastIndexOf(','),
                  rgba  = rgbx.slice(0, comma + 1) + alpha + ")";

              $byline.css("background-color", rgba);

            }

            // Set the background image
            var $bg_img = $('.wp-tiles-tile-bg .wp-tiles-img',$el);
            $('.wp-tiles-tile-bg',$el).css('background-image', 'url("'+$bg_img.attr('src')+'")');
            $bg_img.remove();
          }

          return tile;
        },

        nextPage: function(){

          if ( !opts.next_query)
            return;

          // Only allow one instance (per WP Tiles instance) at a time
          if ( typeof grid.nextPage.running !== 'undefined' &&grid.nextPage.running )
            return;

          grid.nextPage.running = true;
          $pagination.addClass('loading');

          if( !opts.next_query.opts ) {
            opts.next_query.opts = {};
            $.each([
              'hide_title',
              'link',
              'byline_template',
              'byline_template_textonly',
              'images_only',
              'image_size',
              'text_only',
              'link_new_window'
            ],function(){
                opts.next_query.opts[this] = opts[this];
            });
          }

          $.post(opts.ajaxurl,opts.next_query)
            .success(function(response){

              if ( '-1' == response ) {
                $pagination.fadeOut(function(){
                  $pagination.removeClass('loading');
                });
              }

              if ( response.has_more ) {
                opts.next_query.query.paged++;
                opts.next_query._ajax_nonce = response._ajax_nonce;
                $pagination.removeClass('loading');

              } else {
                opts.next_query = false;
                $pagination.fadeOut(function(){
                  $pagination.removeClass('loading');
                });

              }

              var tiles = $('<div />').html(response.tiles).find('.wp-tiles-tile').get();

              grid.addTiles(tiles);
              grid.redraw(opts.animate_template, onresize);
              style_tiles();

              grid.nextPage.running = false;
              //$pagination.removeClass('loading');
            });

        }
      });

      // Pass the post tiles into Tiles.js
      var $posts = $('.wp-tiles-tile',$el);
      grid.updateTiles($posts);

      // Draw!
      grid.redraw(opts.animate_init, onresize);

      // Maybe do some work with bylies
      style_tiles();

      // when the window resizes, redraw the grid
      $(window).resize($.wptiles.debounce(function() {
          // @todo Only resize if template is the same?
          grid.template = get_template();

          grid.isDirty = true;
          grid.resize();

          grid.redraw(opts.animate_resize, onresize);
      }, 200));


      /**
       * Template Buttons
       */
      if ( $templates.get(0) ) {
        var $templateButtons = $('.wp-tiles-template', $templates);

        $templateButtons.css('background-color',opts.grid_selector_color);

        $templateButtons.on('click', function(e) {
          e.preventDefault();

          // unselect all templates
          $templateButtons.removeClass("selected");

          // select the template we clicked on
          $(this).addClass("selected");

          // get the JSON rows for the selection
          var rows = opts.grids[$(this).data('grid')];

          // set the new template and resize the grid
          set_template(rows);

          grid.isDirty  = true;
          grid.resize();

          grid.redraw(opts.animate_template, onresize);
        });
      }

      /**
       * Pagination
       */
      if ( $pagination.get(0) ) {
        if ( $pagination.hasClass('wp-tiles-pagination-ajax') ) {
          $pagination.click(function(e){
            e.preventDefault();
            grid.nextPage();
          });
        }
      }

      opts.grid = grid;
    }
  });

  // Init using vars from wp_localize_script
  if ( typeof wptilesdata === 'object' ) {
    $(function(){
      $.each (wptilesdata, function() {
        var tiledata = this,
            $el = $('#' + tiledata.id);

        $el.wptiles(tiledata);
      });

      // @todo Is this really needed?
      $(window).trigger('resize');
    });
  }

})(jQuery);