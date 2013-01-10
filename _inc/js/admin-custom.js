jQuery(document).ready( function ($) {
    //$('div.color-pickers').hide();
    $('div.color-pickers-show a.show-colors').click( function() {
        $('div.color-pickers-show').hide()
        $('div.color-pickers').show();
        e.preventDefault();
    });
    $('div.color-pickers a.hide-colors').click( function() {
        $('div.color-pickers-show').show()
        $('div.color-pickers').hide();
        e.preventDefault();
    });

    var colorpickers = $('.wp-tiles-colorpickers');
    $.each(colorpickers, function ( i, value ) {
        $(this).farbtastic("#wptiles-color-" + i );
    });
});