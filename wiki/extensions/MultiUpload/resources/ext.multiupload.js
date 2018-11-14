/* global mediaWiki */
( function ( mw, $ ) {

$( document ).ready( function() {
	var wpFirstRowIndex = mw.config.get( 'wpFirstRowIndex' );
	var wpLastRowIndex = mw.config.get( 'wpLastRowIndex' );

	mw.libs.ext.multiupload.captureTemplate();
	for ( var i = wpFirstRowIndex; i <= wpLastRowIndex; ++i ) {
		mw.libs.ext.multiupload.setupRow( i );
	}
	mw.libs.ext.multiupload.maybeAddBlankRow( $( '#mw-upload-form' ) );
	mw.libs.ext.multiupload.revealForm();
} );

} )( mediaWiki, jQuery );
