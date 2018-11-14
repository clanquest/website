/* global $, mw */
( function ( $, mw ) {

function apiErr( code, result, message ) {
	var $errdiv = $( '<div/>' ).append( message );
	var errtxt = code;
	if ( result && 'error' in result && result.error.info ) {
		errtxt = 'Error: ' + result.error.info;
	} else if ( result && result.exception ) {
		errtxt = 'Error: ' + result.exception;
	} else if ( code === 'http' ) {
		errtxt = mw.message( 'multiupload-http-error' ).parse();
	}
	if ( errtxt ) {
		$errdiv.append( '<br/>' + errtxt );
	}
	if ( result && result.error && result.error.messages ) {
		$errdiv.append( '<br/>' + result.error.messages );
	}
	mw.loader.using( [ 'mediawiki.notification', 'mediawiki.notify' ], function() {
		mw.notify( $errdiv );
	} );
}

function unpackOnServer( input, sessionkey, spinnerName ) {
	mw.loader.using( 'mediawiki.api', function () {
		( new mw.Api() ).get( {
			action: 'multiupload-unpack',
			key: sessionkey,
			filename: input.files[0].name
		} ).done( function ( data ) {
			mw.libs.ext.multiupload.removeTinySpinner( spinnerName );
			if ( 'multiupload-unpack' in data && 'contents' in data['multiupload-unpack'] ) {
				reloadForm( input, data['multiupload-unpack'].contents );
			} else {
				// TODO: if invalid token, get a new one and redo
				mw.loader.using( [ 'mediawiki.notification', 'mediawiki.notify' ], function() {
					mw.notify( 'Error unpacking ' + input.files[0].name );
				} );
			}
		} ).fail( function ( code, result ) {
			mw.libs.ext.multiupload.removeTinySpinner( spinnerName );
			apiErr( code, result, mw.message( 'multiupload-unpack-error' ).parse() );
		} );
	} );
}

function reloadForm( input, filedata ) {
	var packagename = input.files[0].name;
	var $row = $( input ).parents( 'fieldset.row' );
	var projName = $row.find( ':input.wpProjectName' ).val();
	if ( !projName ) {
		projName = '';
	}
	var opts = {
		'wpSourceType': 'Stash',
		'wpSessionKey': '',
		'wpUploadFile': null,
		'wpUploadUrl': null,
		'wpDestFile': '',
		'wpProjFilename': '',
		'wpProjectName': projName,
		'wpDestTypeTouched': 0,
		'wpDestPageTouched': 0
	};
	mw.libs.ext.multiupload.removeRow( $row );
	/* jshint forin:false */
	for ( var i in filedata ) {
		opts.wpSessionKey = filedata[i][0];
		opts.wpDestFile = filedata[i][1];
		opts.wpProjFilename = filedata[i][1];
		$row = mw.libs.ext.multiupload.addRow( opts, $row );
		// this is hacky
		// if that stuff procedure left a hanging help message at
		// the top, remove it
		$row.find( 'tr:first-child > td.htmlform-tip' )
			.parents( 'tr' )
			.remove();
		$row.find( 'tbody' )
			.prepend( '<tr><td colspan="2"><h2>' +
				mw.message( 'multiupload-file-unpacked-from', filedata[i][1], packagename ).parse() +
				'</h2></td></tr>'
			);
	}
}


// FormDataTransport looks for these
if ( !mw.UploadWizard ) {
	mw.UploadWizard = {};
}

if ( !mw.UploadWizard.config ) {
	mw.UploadWizard.config = {};
}

var enableChunked = ( mw.config.get( 'wgVersion' ).match( /^1\.2[0-9]\./ ) ? true : false );
$.extend( mw.UploadWizard.config, {
	chunkSize: 5 * 1024 * 1024,
	enableChunked: enableChunked,
	maxPhpUploadSize: mw.config.get( 'wgMultiUploadMaxPhpUploadSize' )
} );

$.extend( mw.libs.ext.multiupload, {

	unpackPackageFile: function ( input, spinnerName ) {
		var upload = {
			file: input.files[0],
			ui: { setStatus : function () { } },
			state: undefined
		};
		var progressCb = function () {};
		var doneCb = function ( response ) {
			if ( response && response.upload && response.upload.sessionkey ) {
				unpackOnServer( input, response.upload.sessionkey, spinnerName );
			} else {
				apiErr( null, response, mw.message( 'multiupload-upload-package-error' ).parse() );
				mw.libs.ext.multiupload.removeTinySpinner( spinnerName );
			}
		};
		var transport = new mw.FormDataTransport(
			mw.util.wikiScript( 'api' ),
			{
				action: 'upload',
				stash: 1,
				token: $( ':input#wpEditToken' ).val(),
				format: 'json'
			},
			upload,
			progressCb,
			doneCb
		);
		transport.upload();
	}

} );

} )( $, mw );
