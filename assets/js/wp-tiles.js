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
      var lastEl = $el.children().last(),
          tileOffsetTop = parseInt ( $el.offset().top ),
          newHeight = parseInt(lastEl.css("height"), 10) + parseInt(lastEl.offset().top, 10) - tileOffsetTop + parseInt(padding) + "px";

      $el.parent('.wp-tiles-container').css('height', newHeight );
    }
  };

  $.fn.extend({
    wptiles: function(tiledata){
      var
          // Locals
          $el = $(this),
          $templates = $("#" + tiledata.id + "-templates"),
          $templateButtons = $('.template', $templates),
          display_opts = tiledata.display_options,
          grid,

          currTemplate = Tiles.Template.fromJSON(tiledata.rowTemplates[0]),
          largeTemplate,

          // Private Methods
          choose_template = function(){
            if ( $el.width() < display_opts.small_screen_breakpoint && !largeTemplate ) {
                $templates.hide();

                // Save large template
                largeTemplate = ( grid.template ) ? grid.template : Tiles.Template.fromJSON(tiledata.rowTemplates[0]);
                currTemplate = Tiles.Template.fromJSON(tiledata.rowTemplates['small']);

            } else if ( largeTemplate ) {
                $templates.show();

                currTemplate = largeTemplate;
                largeTemplate = false;
            }

          },

          onresize = function(){
            $.wptiles.resizeParent($el,display_opts.padding);
            $('.wp-tiles-byline').dotdotdot();

            $el.trigger('wp-tiles:resize');
          };

      choose_template();

      // Setup the Tiles grid
      grid = $.extend(new Tiles.Grid($el),{
        cellPadding: parseInt(display_opts.padding),

        template: currTemplate,

        resizeColumns: function() {
          return this.template.numCols;
        },

        createTile: function(data) {
          var tile = new Tiles.Tile(data.id,data),
              $el  = tile.$el,
              i    = parseInt(data.id.match(/[0-9]{1,}/)),
              // @todo Custom colors using data-attributes?
              color = display_opts.colors[i % display_opts.colors.length];

          $el
            .css("background-color", color);

          // Is this an image tile?
          if ( $('.wp-tiles-tile-with-image',$el).get(0) ) {

            // Then maybe also add the color to the byline
            if ( 'random' === display_opts.byline_color ) {

              var $byline  = $('.wp-tiles-byline',$el),
                  alpha = display_opts.byline_opacity,
                  //rgb   = $byline.css('background-color'),
                  rgb   = color,
                  rgbx  = rgb.substr(0,4) === 'rgba' ? rgb : rgb.replace('rgb', 'rgba').replace(')', ',' + alpha + ')'),
                  comma = rgbx.lastIndexOf(','),
                  rgba  = rgbx.slice(0, comma + 1) + alpha + ")";
                  //rgba  = rgbx.replace(')', ',' + alpha + ')');

              $byline.css("background-color", rgba);

            }

            // Set the background image
            var $bg_img = $('.wp-tiles-tile-bg .wp-tiles-img',$el);
            $('.wp-tiles-tile-bg',$el).css('background-image', 'url("'+$bg_img.attr('src')+'")');
            $bg_img.remove();
          }

          return tile;
        },

        // Repeat the same template until tiles are exhausted
        ensureTemplate: function(numTiles) {

          // verfiy that the current template is still valid
          if (!this.template || this.template.numCols !== this.numCols) {
            this.template = this.createTemplate(this.numCols, numTiles);
            this.isDirty = true;
          } else {

            // append another template if we don't have enough rects
            var missingRects = numTiles - this.template.rects.length;
            if (missingRects > 0) {
              var copyRects = [],
                len, i;

              while (missingRects > 0) {

                len = missingRects <= this.template.rects.length ? missingRects : this.template.rects.length;

                for (i = 0; i < len; i++) {
                  copyRects.push(this.template.rects[i].copy());
                }
                missingRects -= this.template.rects.length;
              }

              this.template.append(
                new Tiles.Template(copyRects, this.numCols, this.numRows)
                );

              this.isDirty = true;
            }

          }
        }
      });

      // Pass the post tiles into Tiles.js
      var posts = $('.wp-tiles-tile',$el);
      grid.updateTiles(posts);

      // Maybe do some work with bylies
      var $image_bylines = $('.wp-tiles-tile-with-image .wp-tiles-byline');
      if ( $image_bylines.get(0) ) {

        // Set color and opacity
        if ( 'random' !== display_opts.byline_color ) {
          $image_bylines.css('background-color', display_opts.byline_color); // Byline color includes alpha
        }

        // Set the byline height
        $image_bylines.css('height',display_opts.byline_height + '%');
      }

      // Draw!
      grid.redraw(display_opts.animate_init, onresize);

      // when the window resizes, redraw the grid
      $(window).resize($.wptiles.debounce(function() {
          // @todo Only resize if template is the same?
          choose_template();

          grid.isDirty = true;
          grid.resize();

          grid.redraw(display_opts.animate_resize, onresize);
      }, 200));


      // Make the grid changable
      $templateButtons.on('click', function(e) {

        // unselect all templates
        $templateButtons.removeClass("selected");

        // select the template we clicked on
        $(e.target).addClass("selected");

        // get the JSON rows for the selection
        var index = $(e.target).index(),
            rows  = tiledata.rowTemplates[index];

        // set the new template and resize the grid
        grid.template = Tiles.Template.fromJSON(rows);
        grid.isDirty  = true;
        grid.resize();

        grid.redraw(display_opts.animate_template, onresize);

      });
    }
  });

  // Init using vars from wp_localize_script
  $(function(){
    $.each (wptilesdata, function() {
      var tiledata = this,
          $el = $('#' + tiledata.id);

      $el.wptiles(tiledata);
    });

    $(window).trigger('resize');
  });

})(jQuery);