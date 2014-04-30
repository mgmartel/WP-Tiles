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
          newHeight = parseInt(lastEl.css("height"), 10) + parseInt(lastEl.offset().top, 10) - tileOffsetTop + padding + "px";

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
              i    = parseInt(data.id.match(/[0-9]./));

          // @todo Custom colors using data-attributes?
          $el
            .css("background-color", display_opts.colors[i % display_opts.colors.length]);

          return tile;
        }
      });

      // Pass the post tiles into Tiles.js
      var posts = $('.wp-tiles-tile',$el);
      grid.updateTiles(posts);

      // @todo Make animated an option
      var animated = true;
      grid.redraw(animated, onresize);

      // when the window resizes, redraw the grid
      $(window).resize($.wptiles.debounce(function() {
          // @todo Only resize if template is the same?
          choose_template();

          grid.isDirty = true;

          grid.resize();

          // @todo window resize animation
          grid.redraw(animated, onresize);
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

        // @todo 'template change animation' option
        grid.redraw(animated, onresize);

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