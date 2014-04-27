// debounce utility from underscorejs.org
var debounce = function(func, wait, immediate) {
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
};

jQuery(function($){
  $.each ( wptilesdata, function() {

    var tiledata = this,
        el = document.getElementById(tiledata.id),
        grid = new Tiles.Grid(el),
        display_opts = tiledata.display_options;

    // Fix the number of columns to the number in the current template
    grid.resizeColumns = function() {
        return this.template.numCols;
    };

    // Use the existing element as the new Tile data
    grid.createTile = function(data) {
      return new Tiles.Tile(data.id,data);
    };

    var largeTemplate = tiledata.rowTemplates[0],
        smallTemplate = tiledata.rowTemplates['small'];

    if ( $(el).width() < tiledata.display_options.small_screen_width ) {
        $("div#" + tiledata.id + "-templates").hide();
        grid.template = Tiles.Template.fromJSON(smallTemplate);
        largeTemplate = Tiles.Template.fromJSON(largeTemplate);
    } else {
        $("div#" + tiledata.id + "-templates").show();
        grid.template = Tiles.Template.fromJSON(largeTemplate);
    }

    grid.isDirty = true;
    grid.resize();
    grid.cellPadding = parseInt(display_opts.cellPadding);

    var posts = $('.wp-tiles-tile',el);
    grid.updateTiles(posts);
    grid.redraw(true, resizeWpTiles);

    function resizeWpTiles() { // @todo is there a way to make this less hacky?
        var lastEl = $('#' + tiledata.id).children().last();
        var tileOffsetTop = parseInt ( $('#' + tiledata.id).offset().top );
        //var newHeight = parseInt(lastEl.css("height"), 10) + parseInt(lastEl.css("top"), 10) + 10 + "px";
        var newHeight = parseInt(lastEl.css("height"), 10) + parseInt(lastEl.offset().top, 10) - tileOffsetTop + 10 + "px";
        $('.wp-tile-container:has("#'+ tiledata.id +'")').css('height', newHeight );
    }

    // wait until users finishes resizing the browser
    var debouncedResize = debounce(function() {
        if ( $("#" + tiledata.id ).width() < tiledata.display_options.small_screen_width ) {
            $("div#" + tiledata.id + "-templates").hide();
            if ( ! largeTemplate )
                largeTemplate = grid.template;
            grid.template = Tiles.Template.fromJSON(smallTemplate);
            grid.isDirty = true;
        } else if ( largeTemplate ) {
            $("div#" + tiledata.id + "-templates").show();
            grid.template = Tiles.Template.fromJSON(largeTemplate);
            grid.isDirty = true;
        }

        grid.resize();
        grid.redraw(true, resizeWpTiles);
    }, 200);

    // when the window resizes, redraw the grid
    $(window).resize(debouncedResize);

    // Make the grid changable
    var $templateButtons = $('#' + tiledata.id + '-templates li.template').on('click', function(e) {

        // unselect all templates
        $templateButtons.removeClass("selected");

        // select the template we clicked on
        $(e.target).addClass("selected");

        // get the JSON rows for the selection
        var index = $(e.target).index(),
            rows = tiledata.rowTemplates[index];

        // set the new template and resize the grid
        grid.template = Tiles.Template.fromJSON(rows);
        grid.isDirty = true;
        grid.resize();

        grid.redraw(true, resizeWpTiles);
    });
  });
  $(window).trigger('resize');

});