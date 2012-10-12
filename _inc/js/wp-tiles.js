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

    if ( $("#" + wptilesdata.id ).width() < 800 ) {
        grid.template = Tiles.Template.fromJSON(wptilesdata.smallTemplates);
        grid.isDirty = true;
    } else {
        grid.template = Tiles.Template.fromJSON(wptilesdata.rowTemplates[0]);
    }

    grid.isDirty = true;
    grid.resize();

    var posts = wptilesdata.posts;
    grid.updateTiles(posts);
    grid.redraw(true, resizeWpTiles);

    function resizeWpTiles() { // @todo is there a way to make this less hacky?
        var lastEl = $('#' + wptilesdata.id).children().last();
        var newHeight = parseInt(lastEl.css("height"), 10) + parseInt(lastEl.css("top"), 10) + 10 + "px";
        $('.wp-tile-container').css('height', newHeight );
    }

    var oldTemplate = false;
    // wait until users finishes resizing the browser
    var debouncedResize = debounce(function() {
        if ( $("#" + wptilesdata.id ).width() < 800 ) {
            oldTemplate = grid.template;
            console.log (grid.template);
            grid.template = Tiles.Template.fromJSON(wptilesdata.smallTemplates);
            grid.isDirty = true;
        } else if ( oldTemplate ) {
            grid.template = oldTemplate;
            oldTemplate = false;
            grid.isDirty = true;
        }

        grid.resize();
        grid.redraw(true, resizeWpTiles);
    }, 200);

    // when the window resizes, redraw the grid
    $(window).resize(debouncedResize);

    // Make the grid changable
    var $templateButtons = $('#' + wptilesdata.id + '-templates li.template').on('click', function(e) {

        // unselect all templates
        $templateButtons.removeClass("selected");

        // select the template we clicked on
        $(e.target).addClass("selected");

        // get the JSON rows for the selection
        var index = $(e.target).index(),
            rows = wptilesdata.rowTemplates[index];

        // set the new template and resize the grid
        grid.template = Tiles.Template.fromJSON(rows);
        grid.isDirty = true;
        grid.resize();

        grid.redraw(true, resizeWpTiles);
    });
})(jQuery);