jQuery(document).on('submit', '#papucho_import', function( e ) {
    e.preventDefault();
    jQuery.ajax( {
        url: jQuery(this).attr('action'),
        type: 'POST',
        data: new FormData( this ),
        processData: false,
        contentType: false
    }).done(function( data ) {
        jQuery('#papucho_review').html(data);
    });

});

jQuery(document).on('submit', '#papucho_review', function( e ) {
    e.preventDefault();
    jQuery.ajax( {
        url: jQuery(this).attr('action'),
        type: 'POST',
        data: new FormData( this ),
        processData: false,
        contentType: false
    }).done(function( data ) {
        jQuery('#papucho_done').html(data);
    });

});
