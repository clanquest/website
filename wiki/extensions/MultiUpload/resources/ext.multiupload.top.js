/* global $, mw */
( function ( $, mw ) {

if ( !mw.libs ) {
	mw.libs = {};
}
if ( !mw.libs.ext ) {
	mw.libs.ext = {};
}

// a small portion of JS code to be available during page load
mw.libs.ext.multiupload = {
	// I want spinners to appear quick, so no waiting for this code to load
	createTinySpinner: function( id ) {
		return $( '<div>' ).attr( {
			id: 'multiupload-tiny-spinner-' + id,
			'class': 'multiupload-tiny-spinner',
			title: '...'
		} );
	},

	injectTinySpinner: function( elt, id ) {
		this.removeTinySpinner( id );
		return elt.after( this.createTinySpinner( id ) );
	},

	removeTinySpinner: function( id ) {
		return $( '#multiupload-tiny-spinner-' + id ).remove();
	}
};

} )( $, mw );
