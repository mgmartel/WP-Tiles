(function($) {
    $(document).ready( function() {
        $('.add-textarea').on('click', function (e) {
            $(this).before(
                '<div style="float:left"><input type="text" value="" name="wp-tiles-options[templates][templates][name][]" width="140px"><br><textarea style="height:100px;width:140px" id=" " name="wp-tiles-options[templates][templates][field][]"></textarea></div>'
            );
        });
    })
})(jQuery);