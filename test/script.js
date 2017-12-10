/**
 * Script.
 */
( function( window, undefined ) {

	window.wp = window.wp || {};
	var wp = window.wp;
	var $ = window.jQuery;

	// Remove uploaded gallery image.
	$( 'body' ).on( 'click', '.image-remove', function(e) {
		e.preventDefault();

		$( this ).parents( '.gallery-image' ).remove();
	} );

	// Display upload preview.
	// @link https://stackoverflow.com/questions/39439760
	var imagesPreview = function( input, el ) {
		if ( input.files ) {
			var filesAmount = input.files.length;
			for (i = 0; i < filesAmount; i++) {
				var reader = new FileReader();
				reader.onload = function(event) {
					$( '<div class="gallery-image"></div>' ).css( 'background-image', "url('" + event.target.result + "')" ).appendTo( el );
				}
				reader.readAsDataURL(input.files[i]);
			}
		}
	};

	$( '#add-images' ).on( 'change', function() {
		$( '#preview' ).html( '' );
		imagesPreview( this, '#preview' );
	});


}( window ) );










