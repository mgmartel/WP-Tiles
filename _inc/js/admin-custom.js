jQuery(document).ready( function () {
    var colorpickers = jQuery('.wp-tiles-colorpickers');
    jQuery.each(colorpickers, function ( i, value ) {
        jQuery(this).farbtastic("#wptiles-color-" + i );
    });
});