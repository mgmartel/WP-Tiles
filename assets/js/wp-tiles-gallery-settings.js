( function( $ ) {
    var media = wp.media;

    // Wrap the render() function to append controls
    media.view.Settings.Gallery = media.view.Settings.Gallery.extend({
        render: function() {
            media.view.Settings.prototype.render.apply( this, arguments );

            // Append the custom template
            this.$el.append( media.template( 'wp-tiles-gallery-settings' ) );

            var $settings = this.$el.find('.wp-tiles-settings');

            if ( this.model.attributes.tiles )
              $settings.show();
            else
              $settings.hide();

            this.$el.find('.wp-tiles-enabled').on('change',function(){
              if ( this.checked )
                $settings.show();
              else
                $settings.hide();
            });

            // Save the setting
            media.gallery.defaults['tiles']      = false;
            media.gallery.defaults['grid']       = false;
            media.gallery.defaults['image_size'] = false;

            this.update.apply( this, ['tiles'] );
            this.update.apply( this, ['grid'] );
            this.update.apply( this, ['image_size'] );
            return this;
        }
    } );
} )( jQuery );