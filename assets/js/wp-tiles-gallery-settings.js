( function( $ ) {
    var media = wp.media;

    // Wrap the render() function to append controls
    media.view.Settings.Gallery = media.view.Settings.Gallery.extend({
        render: function() {
            media.view.Settings.prototype.render.apply( this, arguments );

            // Append the custom template
            this.$el.append( media.template( 'wp-tiles-gallery-settings' ) );

            var $settings = this.$el.find('.wp-tiles-settings'),
                //$to_hide = $('.columns', this.$el).parent('.setting'),
                enable_tiles = function(){
                  //$to_hide.hide();
                  $settings.show();
                },
                disable_tiles = function() {
                  //$to_hide.show();
                  $settings.hide();
                },
                toggle_tiles = function(display) {
                  if ( display )
                    enable_tiles();
                  else
                    disable_tiles();
                };

            toggle_tiles(this.model.attributes.tiles);

            this.$el.find('.wp-tiles-enabled').on('change',function(){
              toggle_tiles(this.checked);
            });

            // Save the setting
            media.gallery.defaults['tiles']      = false;
            media.gallery.defaults['grids']       = false;
            media.gallery.defaults['image_size'] = false;

            this.update.apply( this, ['tiles'] );
            this.update.apply( this, ['grids'] );
            this.update.apply( this, ['image_size'] );
            return this;
        }
    } );
} )( jQuery );