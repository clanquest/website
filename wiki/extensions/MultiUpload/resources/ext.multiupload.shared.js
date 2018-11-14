/* ext.multiupload.shared.js:
 * JS resources that are useful to outside extensions based on MultiUpload
 */
/* global fillDestFilename, mediaWiki */
( function ( mw, $ ) {

var collapse = function( $fieldset ) {
	$fieldset.find( '.multiupload-collapsible' ).hide( 400 );
	$fieldset.data( 'collapsed', true );
};

var expand = function( $fieldset ) {
	$fieldset.find( '.multiupload-collapsible' ).show( 400 ); //slideDown( );
	$fieldset.data( 'collapsed', false );
};

var toggle = function( $fieldset ) {
	var collapsed = $fieldset.data( 'collapsed' );
	if ( collapsed ) {
		expand( $fieldset );
	} else {
		collapse( $fieldset );
	}
};

var setupAnimation = function( $fieldset ) {
	// mark all rows that will be hidden
	var marking = false;
	$fieldset.find( 'tbody' ).children().map( function() {
		var tr = $( this );
		if ( !marking && tr.is( '.multiupload-first-to-collapse' ) ) {
			marking = true;
		}
		if ( marking ) {
			tr.addClass( 'multiupload-collapsible' );
		}
	} );
	// now set up the table to collapse upwards
	$fieldset.find( 'input.wpUploadFileUrl,input.wpUploadFile' )
		.change( function ( event ) {
			if ( event.target.type === 'file' ) {
				mw.libs.ext.multiupload.checkForPackageFile( event.target );
			} // else unpack from URL?
			// this test is because I trigger 'change' in document.ready()
			if ( !mw.libs.ext.multiupload.isBlank( $fieldset ) ) {
				expand( $fieldset );
			}
			mw.libs.ext.multiupload.maybeAddBlankRow( $fieldset.parent() );
		} );
	// make the legend act to toggle the row's collapsed status
	// TODO add a little >/v or [-]/[+] to the legend
	var legend = $fieldset.find( 'legend' );
	legend.off( 'click' ).click( function() { toggle( $fieldset ); } );

	// before collapsing, capture the widths so they don't change
	// TODO doesn't work unless the other fields are visible at the time
	//alert($fieldset.find( '.multiupload-width-exemplar .mw-input' ).width() );
	//$fieldset.find( '.mw-input' ).width( $fieldset.find( '.multiupload-width-exemplar .mw-input' ).width() );
	//$fieldset.find( '> table' ).width( '100%' );

	// collapse the row if fields are not filled in.
	if ( mw.libs.ext.multiupload.isBlank( $fieldset ) ) {
		// don't call collapse(), do directly to bypass animation
		$fieldset.find( '.multiupload-collapsible' ).hide();
		$fieldset.data( 'collapsed', true );
	}
};

var fixRowNumbers = function () {
	var $rows = $( 'form > fieldset' ).not( '.ww-messages' );
	var lastRowIndex = 0;
	$rows.each( function ( index ) {
		mw.libs.ext.multiupload.renumberRow( $( this ), index + 1 );
		lastRowIndex = index + 1;
	} );
	mw.config.set( 'wpLastRowIndex', lastRowIndex );
	$( '#wpLastRowIndex' ).val( lastRowIndex );
	//alert( 'debug: ' + lastRowIndex + ' row(s)' );
	if ( lastRowIndex === 0 ) {
		mw.libs.ext.multiupload.addRow( {} );
	}
};

if ( !mw.libs ) {
	mw.libs = {};
}
if ( !mw.libs.ext ) {
	mw.libs.ext = {};
}
if ( !mw.libs.ext.multiupload ) {
	mw.libs.ext.multiupload = {};
}

$.extend( mw.libs.ext.multiupload, {
	checkForPackageFile: function ( input ) {
		// only if HTML5 File API is available
		if ( !( 'files' in input ) || input.files.length === 0 ) {
			$( input ).parent().find( '.unpackButton' ).remove();
			return;
		}
		var packageRegexp = /\.zip$|\.tar$|\.tar\.gz$|\.tgz$/;
		var upfilename = input.files[0].name;
		if ( upfilename.match( packageRegexp ) ) {
			if ( $( input ).parent().find( '.unpackButton' ).length === 0 ) {
				$( input ).after(
					$( '<div/>' )
					.css( { 'float' : 'right' } )
					.append(
						$( '<input type="submit"/>' )
						.attr( 'name', 'unpackButton' )
						.addClass( 'unpackButton' )
						.val( mw.message( 'multiupload-unpack-button' ).plain() )
						.click( function ( event ) {
							event.preventDefault();
							var rowindex = $( input ).parents( 'fieldset.row' ).data( 'row-index' );
							var spinnerName = 'unpack-' + rowindex;
							mw.libs.ext.multiupload.injectTinySpinner( $( event.target ), spinnerName );
							mw.loader.using( 'ext.multiupload.unpack', function () {
								mw.libs.ext.multiupload.unpackPackageFile( input, spinnerName );
							} );
						} )
					)
				);
				$( input ).parent().css( { 'width' : '100%' } );
				mw.loader.using( 'ext.multiupload.unpack', function () {} ); // preload
			}
		} else {
			$( input ).parent().find( '.unpackButton' ).remove();
		}
	},

	// row indexes start at 1, sadly
	findRow: function ( i ) {
		return $( $( 'form > fieldset' ).not( '.ww-messages' ).get( i - 1 ) );
	},

	setupRow: function( i, $fieldset ) {
		if ( !$fieldset ) {
			$fieldset = mw.libs.ext.multiupload.findRow( i );
		}
		$fieldset.addClass( 'row' );
		$fieldset.data( 'row-index', i );
		if (
			$fieldset.children( 'div.row' ) === '' ||
			$fieldset.children( 'div.row' ) === 0 ||
			$fieldset.children( 'div.row' ) === "0" ||
			$fieldset.children( 'div.row' ) === null ||
			$fieldset.children( 'div.row' ) === false ||
			$fieldset.children( 'div.row' ) === undefined
		)
		{
			$( '<div>' ).addClass( 'row' )
				.append( $fieldset.children().not( 'legend' ) )
				.appendTo( $fieldset );
		}

		$fieldset.find( 'input.wpUploadFile,input.wpUploadFileURL' )
			.off( 'change' );
		window.uploadSetupByIds(
			'wpSourceType' + i + 'url',
			'wpUploadFileURL' + i,
			'wpLicense' + i,
			'wpDestFile-warning' + i,
			'wpDestFileWarningAck' + i,
			'wpDestFile' + i,
			'mw-htmlform-row-' + i,
			'mw-license-preview' + i
		);

		var upUrl = document.getElementById( 'wpUploadFileURL' + i );
		var destFile = document.getElementById( 'wpDestFile' + i );
		var upperm = document.getElementById( 'mw-upload-permitted' + i );
		var uppro = document.getElementById( 'mw-upload-prohibited' + i );
		var warningId = 'wpDestFile-warning' + i;
		var ackElt = document.getElementsByName( 'wpDestFileWarningAck' + i );
		var configvar = 'wgMultiUploadAutoFill' + i;
		if ( destFile ) {
			$fieldset.find( 'input.wpUploadFile,input.wpUploadFileURL' )
				.change( function() {
					fillDestFilename( this, upUrl, destFile,
						upperm, uppro, warningId, ackElt, configvar );
				} );
		}
		window.setupThumbnail(
			'wpUploadFile' + i,
			'mw-upload-thumbnail' + i,
			'wpSourceTypeFile-error' + i,
			'mw-htmlform-row-' + i
		);
		window.setupSourceFields(
			$( '#mw-htmlform-row-' + i + ' .mw-htmlform-field-UploadSourceField' ),
			'wpSourceTypeFile-error' + i,
			'wpSourceType' + i
		);

		setupAnimation( $fieldset );

		// give it a close button if it doesn't have one
		var $cb = $fieldset.find( '.multiupload-close-button-container' );
		if (
			$cb === '' || $cb === 0 || $cb === "0" || $cb === null ||
			$cb === false || $cb === undefined
		)
		{
			$fieldset.children( 'div.row' ).prepend(
				$( '<div/>' ).addClass( 'multiupload-close-button-container' ).append(
					$( '<span/>' )
						.addClass( 'multiupload-close-button' )
						.click( function () {
							mw.libs.ext.multiupload.removeRow( $fieldset );
						} )
				)
			);
		}
	},

	// a fieldset is 'blank' if it has at least one source field, and no source
	// field is filled.
	isBlank: function( $fieldset ) {
		var inputs = $fieldset.find( 'input.wpUploadFileUrl,input.wpUploadFile' );
		var nfilled = inputs
			.filter( function() { return this.value ? true: false; } )
			.length;
		var destFile = $fieldset.find( 'input.wpDestFile' );
		return ( inputs.length > 0 && nfilled === 0 && !destFile.val() );
	},

	// function to add a blank row at bottom of form when appropriate
	maybeAddBlankRow: function ( form ) {
		var lastfs = mw.libs.ext.multiupload.findLastRow( form );
		if ( lastfs.length === 1 && mw.libs.ext.multiupload.isBlank( lastfs ) ) {
			return;
		}
		mw.libs.ext.multiupload.addRow( {}, lastfs );
	},

	renumberRow: function( $fieldset, i ) {
		$fieldset.data( 'row-index', i );
		$fieldset.find( '*' ).map( function() {
			var dnb = $( this ).attr( 'data-name-base' );
			if ( dnb ) {
				var id = dnb + i;
				this.id = id;
				if ( this.name ) {
					this.name = id;
				}
			}
			if ( this.htmlFor ) {
				var hfb = $( this ).attr( 'data-htmlFor-base' );
				if ( hfb ) {
					this.htmlFor = hfb + i;
				}
			}
		} );
		mw.libs.ext.multiupload.setupRow( i, $fieldset );
		mw.libs.ext.multiupload.stuffRow( $fieldset );
		$fieldset.find( 'legend' ).text(
			mw.message( 'multiupload-row', i ).plain()
		);

		return $fieldset;
	},

	findLastRow: function ( form ) {
		var fieldsets;
		if ( form ) {
			fieldsets = form.find( '> fieldset' ).not( '.ww-messages' );
		} else {
			fieldsets = $( 'form > fieldset' ).not( '.ww-messages' );
		}
		return fieldsets.filter( ':last' );
	},

	templateUploadRow: null,

	addRow: function ( opts, $lastfs ) {
		// what is first unused row number?
		var i = +mw.config.get( 'wpLastRowIndex' ) + 1;
		// create row and change 'template' to number in attributes
		var fieldset = mw.libs.ext.multiupload.templateUploadRow.clone();
		var $fieldset = $( fieldset );
		// the new row is the new last row
		// append the row after the existing rows.
		$fieldset.hide();
		// TODO: make the animation happen sometime after this returns
		if (
			!$lastfs ||
			$lastfs === '' || $lastfs === 0 || $lastfs === "0" || $lastfs === null ||
			$lastfs === false || $lastfs === undefined
		)
		{
			$lastfs = mw.libs.ext.multiupload.findLastRow();
		}
		if (
			$lastfs === '' || $lastfs === 0 || $lastfs === "0" || $lastfs === null ||
			$lastfs === false || $lastfs === undefined
		)
		{
			$( 'form#mw-upload-form' ).prepend( $fieldset );
		} else {
			$fieldset.insertAfter( $lastfs );
		}
		fixRowNumbers();
		mw.libs.ext.multiupload.setupRow( $fieldset.data( 'row-index' ), $fieldset );
		mw.libs.ext.multiupload.stuffRow( $fieldset, opts );
		mw.config.set( 'wgMultiUploadAutoFill' + i, true );
		// do collapse the fast way
		if ( mw.libs.ext.multiupload.isBlank( $fieldset ) ) {
			$fieldset.find( '.multiupload-collapsible' ).hide();
			$fieldset.data( 'collapsed', true );
		} else {
			$fieldset.find( '.multiupload-collapsible' ).show();
			$fieldset.data( 'collapsed', false );
		}
		// once we abandon 1.21, do this with mw.hook
		if ( 'addRow_hook' in mw.libs.ext.multiupload ) {
			mw.libs.ext.multiupload.addRow_hook( $fieldset );
		}
		$fieldset.fadeIn( 600 );
		return $fieldset;
	},

	stuffRow: function ( $fieldset, opts ) {
		/* jshint forin:false */
		for ( var name in opts ) {
			var $input = $fieldset.find( ':input.' + name );
			var id = name + $fieldset.data( 'row-index' );
			if ( $input.length ) {
				if ( opts[name] !== null ) {
					$input.val( opts[name] ).change();
				} else {
					// note this may remove a whole <tr>, not
					// only an input
					$fieldset.find( '.' + name ).remove();
				}
			} else if ( opts[name] !== null ) {
				$fieldset.append(
					'<input type="hidden" name="' + id + '" ' +
					'id ="' + id + '" class="' + name + '" ' +
					'data-name-base="' + name + '" ' +
					'value="' + opts[name] + '">'
				);
			}
		}
	},

	removeRow: function ( $fieldset ) {
		$fieldset.animate(
			{ height: 'toggle', opacity: 'toggle' },
			500,
			function () {
				$fieldset.remove();
				fixRowNumbers();
			}
		);
	},

	// grab the template row and be able to copy it to bottom of the
	// form on demand
	captureTemplate: function() {
		var $template = $( 'form > fieldset:first-child' ).not( '.ww-messages' );
		$template.detach();
		mw.libs.ext.multiupload.templateUploadRow = $template;
		// move template row's hidden fields into its DOM tree
		$( 'input[type="hidden"][id*="template"]' ).appendTo( $template );
		$template.find( '*' ).each( function () {
			if ( this.id.match( /template$/ ) ) {
				$( this ).attr( 'data-name-base', this.id.replace( /template$/, '' ) );
			}
			if ( this.htmlFor && this.htmlFor.match( /template$/ ) ) {
				$( this ).attr( 'data-htmlFor-base', this.htmlFor.replace( /template$/, '' ) );
			}
		} );
		// same for row 1, for convenience
		var row1 = $( 'form > fieldset:first-child' ).not( '.ww-messages' );
		$( 'input[type="hidden"][id*="1"]' ).appendTo( row1 );
		// and, to get the close button and stuff right
		fixRowNumbers();
	},

	revealForm: function() {
		// make the form visible.
		$( '#mw-upload-form > *' ).show( 'clip' );
		// shut off the spinner.
		$( '#mw-upload-form' ).css( 'background-image', 'none' );
	}

} );

}( mediaWiki, jQuery ) );
