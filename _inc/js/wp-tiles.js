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

(function($) {

    var el = document.getElementById(wptilesdata.id),
        grid = new Tiles.Grid(el);

    grid.resizeColumns = function() {
            return this.template.numCols;
        };

    grid.createTile = function(data) {
        var img = data.img;
        var url = data.url;
        var category = data.category;
        var color = data.color;
        var title = data.title;
        var tile = new Tiles.Tile(data.id);

        if ( img ) {
            tile.$el.append(
                     "<div class='tile-bg' style='background: " + color + " url(" + img + ");' onclick='window.location=\"" + url + "\"'>"
                        + "<div class='tile-byline'>"
                            + "<div class='title'><a href='" +url + "?KeepThis=true&TB_iframe=true&height=400&width=600' class='thickbox'> " + title + "</a></div>"
                            + "<div class='category'>" + category + "</div>"
                        + "</div>"
                    + "</div><!-- end .tile-bg -->"
            );
        } else {
            tile.$el.append(
                     "<div class='tile-color' style='background:" + color + "' onclick='window.location=\"" + url + "\"'>"
                        + "<div class='tile-byline tile-text-only'>"
                            + "<div class='title'>" + title + "</div>"
                            + "<div class='category'>" + category + "</div>"
                        + "</div>"
                    + "</div><!-- end .tile-bg -->"
            );
        }
        return tile;
    };


    var rows =
    [
        " . A A B B ",
        " C C . B B ",
        " D D E E . ",
        " D D . C C "
    ];

    grid.template = Tiles.Template.fromJSON(rows);
    grid.isDirty = true;
    grid.resize();

    grid.updateTiles(wptilesdata.posts);
    grid.redraw(true, resizeWpTiles);

    function resizeWpTiles() { // @todo is there a way to make this less hacky?
        $('.wp-tile-container').css('height', $('#' + wptilesdata.id).children().last().css("top") ).css("height", "+="+ jQuery('#' + wptilesdata.id).children().last().css("height") );
    }

    // wait until users finishes resizing the browser
    var debouncedResize = debounce(function() {
        grid.resize();
        grid.redraw(true, resizeWpTiles);
    }, 200);

    // when the window resizes, redraw the grid
    $(window).resize(debouncedResize);
})(jQuery);