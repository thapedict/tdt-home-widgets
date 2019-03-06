
/**
 * Image Picker
 */
jQuery(
    function( $ ) {
        $( '.tdt-hw-image-picker' ).on( 'click', function( e ){
            e.preventDefault();

            var id = $( this ).attr( 'for' );

            var clicked_button = $( this );

            image_selector = wp.media({
                title: 'Select an Image',
                button: {
                    text: 'Select'
                },
                multiple: false
            });

            image_selector.on( 'select', function(){
                var selected = image_selector.state().get( 'selection' ).first().toJSON();

                clicked_button.siblings( 'img' ).attr( 'src', getThumbnailUrl(selected.url) );
                clicked_button.siblings( 'input' ).val( selected.url );
            });

            image_selector.open();
        });

        function getThumbnailUrl( url ) {
            var name, ext, i;

            i = url.lastIndexOf( '.' );
            name = url.slice( 0, i );
            ext = url.slice( i );

            return name + '-150x150' + ext;
        }
    }
);


/**
 * Icon Picker
 */
jQuery(
    function( $ ) {
        $( 'a.tdt-hw-icon-picker' ).on( 'click', function(){
            clicked_button = $( this );

            $( '#tdt-hw-icon-picker-dialog' ).on( 'select', function( e, data ){
                clicked_button.siblings( 'input' ).val( data.icon );
                clicked_button.siblings( 'span' ).children().attr( 'class', data.icon );
            });

            showIconPickerDialog();
        });

        $( '#tdt-hw-icon-picker-lightbox' ).on( 'click', hideIconPickerDialog );

        function showIconPickerDialog() {
            $( '#tdt-hw-icon-picker-lightbox' ).show();
            $( '#tdt-hw-icon-picker-dialog' ).show();
        }

        function hideIconPickerDialog() {
            $( '#tdt-hw-icon-picker-lightbox' ).hide();
            $( '#tdt-hw-icon-picker-dialog' ).hide();
        }

        var icon_selected = $.Event( 'select' );

        $( '#tdt-hw-icon-picker-dialog i' ).on( 'click', function(){
            var css_class = $( this ).attr( 'class' );
            $( '#tdt-hw-icon-picker-dialog' ).data( 'last-picked', css_class );
            $( '#tdt-hw-icon-picker-dialog' ).trigger( icon_selected, { icon: css_class });
            hideIconPickerDialog();
        });
    }
);

