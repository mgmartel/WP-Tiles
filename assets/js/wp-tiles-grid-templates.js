(function($){

  var
      // Constants
      colors  = ["#50b2c0", "#83b437", "#7673b0", "#ec7e4a","#ff851b","#ffdc00"],
      padding = 5,

      // Locals
      $el = $("#grid-template-demo"),
      grid,

      // Private functions
      get_tile_letters = function(g){
        var letters  = '',
            g        = $("#grid_template").val(),
            r        = g.split("\n");

        var line_above;
        $.each(r,function(index){
          var template = this.match(/[^ ]/g);
          if (!template) return;

          for(var i=0; i<template.length; i++){
            // Letter has been used?
            if ( template[i] !== '.' && letters.indexOf(template[i]) !== -1 ) {

              var is_adjacent = i !== 0 && template[i-1] === template[i],
                  is_beneath  = line_above && line_above[i] === template[i];

              if ( !is_adjacent && !is_beneath )
                letters += template[i];

            } else {
              letters += template[i];

            }
          }
          line_above = template;

        });

        return letters;
      },

      draw = function(callback){
        var $g = $("#grid_template");

        if ( !$g.get(0) )
          return;

        var g = $g.val(),
            f = g.replace(/\r/g, "").split("\n"),
            template = Tiles.Template.fromJSON(f);

        grid.template = template;
        grid.isDirty = true;
        grid.resize();

        // Generate list of numbers from 1 to tiles.length
        var i = 0,
            h = Array.apply(0, Array(template.rects.length)).map(function() { return i++; });

        grid.updateTiles(h);
        grid.redraw(false,function(){
          if (typeof callback === 'function')
            callback.call();

          $.wptiles.resizeParent($el,10);
        });

        $('.wp-tiles-show-help').click(function(e){
          e.preventDefault();
          e.stopPropagation();
          $('#contextual-help-link').click();
        });
      };

  ;;

  grid = $.extend(new Tiles.Grid($el[0]),{
    cellPadding: padding,
    resizeColumns: function() {
      return this.template.numCols;
    },
    createTile: function(i) {
      var tile = new Tiles.Tile(i),
          $el  = tile.$el;

      $el
        .addClass("wp-tiles-tile")
        .css("background-color", colors[i % colors.length])
        .append('<div class="wp-tiles-tile-number">' + i + "</div>")
        .append('<div class="wp-tiles-tile-letter"></div>');

      return tile;
    }
  });

  ;;

  //
  // GRID TEMPLATE EDITOR
  //

  // Force scrollbar
  $('html').css('overflow-y','scroll');

  var tr =  ($.browser.webkit)  ? '-webkit-transition' :
            ($.browser.mozilla) ? '-moz-transition' :
            ($.browser.msie)    ? '-ms-transition' :
            ($.browser.opera)   ? '-o-transition' : 'transition';

  var o = { 'min-height': '50px' };
  o[tr] = 'height 0.2s';

  $("#grid_template")
    .css(o)
    .autosize({append:"\n"})
    .on('keyup',function(e){
      var t     = $(this).val(),
          n     = t.replace(/ {1,}/g, "").replace(/\n[ ]{1,}/g, "\n"),
          d     = n.length - t.length,
          start = this.selectionStart + d,
          end   = this.selectionEnd + d;

      $(this).val(n.toUpperCase());

      this.setSelectionRange(start, end);

      draw(function(){
        var letters = get_tile_letters();

        $el.find('.wp-tiles-tile-letter').each(function(k){
          $(this).text(letters[k]);
        });
      });
    })
    .trigger('keyup');

  ;;

  // Draw actions
  $('.meta-box-sortables').on('sortupdate',draw);
  $(window).resize($.wptiles.debounce(draw, 200));

  // (Maybe) Walkthrough
  if ( typeof wpTilesPointers !== 'undefined' ) {
    wp_tiles_next_pointer();
  }

  function wp_tiles_next_pointer() {
      var pointer = wpTilesPointers.pop();

      if ( !pointer )
        return;

      $.extend( pointer.options, {
          close: function() {
            wp_tiles_next_pointer();
              $.post( ajaxurl, {
                  pointer: pointer.pointer_id,
                  action: 'dismiss-wp-pointer'
              });
          }
      });

      $(pointer.target).pointer( pointer.options ).pointer('open');
  }

})(jQuery);