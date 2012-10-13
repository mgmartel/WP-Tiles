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

    $.each ( wptilesdata, function( key, tiledata ) {

        var el = document.getElementById(tiledata.id),
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
                                + "<div class='title'>" + title + "</div>"
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

        if ( $("#" + tiledata.id ).width() < 800 ) {
            grid.template = Tiles.Template.fromJSON(tiledata.smallTemplates);
            grid.isDirty = true;
        } else {
            grid.template = Tiles.Template.fromJSON(tiledata.rowTemplates[0]);
        }

        grid.isDirty = true;
        grid.resize();

        var posts = tiledata.posts;
        grid.updateTiles(posts);
        grid.redraw(true, resizeWpTiles);

        function resizeWpTiles() { // @todo is there a way to make this less hacky?
            var lastEl = $('#' + tiledata.id).children().last();
            var newHeight = parseInt(lastEl.css("height"), 10) + parseInt(lastEl.css("top"), 10) + 10 + "px";
            $('.wp-tile-container:has("#'+ tiledata.id +'")').css('height', newHeight );
        }

        var oldTemplate = false;
        // wait until users finishes resizing the browser
        var debouncedResize = debounce(function() {
            if ( $("#" + tiledata.id ).width() < 800 ) {
                if ( ! oldTemplate )
                    oldTemplate = grid.template;
                grid.template = Tiles.Template.fromJSON(tiledata.smallTemplates);
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
})(jQuery);