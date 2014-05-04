( function( $ ) {
    var media = wp.media;

    // Wrap the render() function to append controls
    media.view.Settings.Gallery = media.view.Settings.Gallery.extend({
        render: function() {
            media.view.Settings.prototype.render.apply( this, arguments );

            // Append the custom template
            this.$el.append( media.template( 'wp-tiles-gallery-settings' ) );

            // Save the setting
            media.gallery.defaults['tiles'] = false;
            this.update.apply( this, ['tiles'] );
            return this;
        }
    } );
} )( jQuery );