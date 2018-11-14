<?php
/**
 * Implements Special:MultiUpload
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup SpecialPage
 * @ingroup Upload
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

/**
 * Special page for uploading multiple files in one submission.
 */
class SpecialMultiUpload extends SpecialUpload {

	function __construct( $request = null ) {
		SpecialPage::__construct( 'MultiUpload', 'upload' );
	}

	public $mFrom;   // which numbered rows to display, process
	public $mTo;

	public $mRows;

	/**
	 * Under which header this special page is listed in Special:SpecialPages
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'media';
	}

	/**
	 * Special page entry point
	 */
	public function execute( $par ) {
		$this->setHeaders();
		$this->outputHeader();

		# Check uploading enabled
		if ( !UploadBase::isEnabled() ) {
			throw new ErrorPageError( 'uploaddisabled', 'uploaddisabledtext' );
		}

		# Check permissions
		$user = $this->getUser();
		$permissionRequired = UploadBase::isAllowed( $user );
		if ( $permissionRequired !== true ) {
			throw new PermissionsError( $permissionRequired );
		}

		# Check blocks
		if ( $user->isBlocked() ) {
			throw new UserBlockedError( $user->getBlock() );
		}

		# Check whether we actually want to allow changing stuff
		$this->checkReadOnly();

		$this->handleRequestData();
	}

	public function handleRequestData() {
		$request = $this->getRequest();

		$token = $request->getVal( 'wpEditToken' );
		$this->mTokenOk = $this->getUser()->matchEditToken( $token );

		$this->mFrom = 1; # $request->getVal( 'from', 1 );
		global $wgMultiUploadInitialNumberOfImportRows;
		$this->mTo = $request->getVal( 'wpLastRowIndex',
			$wgMultiUploadInitialNumberOfImportRows );
		# stick an invisible template row in front
		$this->mRows = array(
			$this->createRow( 'template' ),
		);
		$i = $this->mFrom;
		while ( $i <= $this->mTo ) {
			$this->mRows[] = $row = $this->createRow( $i );
			$row->handleRequestData();
			$i++;
		}
		$this->showUploadForm( $this->getUploadForm() );
	}

	protected function createRow( $i ) {
		$row = new UploadRow( $this, $i );
		$row->setContext( $this->getContext() );
		return $row;
	}

	/**
	 * Get an UploadForm instance with title and text properly set.
	 *
	 * @param $message String: HTML string to add to the form
	 * @param $sessionKey String: session key in case this is a stashed upload
	 * @param $hideIgnoreWarning true if warning's already been dealt with
	 * @return UploadForm
	 */
	protected function getUploadForm( $message = '', $sessionKey = '', $hideIgnoreWarning = false ) {
		# Initialize form
		$form = new MultiUploadForm( $this, $this->mRows, $this->mTo, $this->getContext() );
		$form->setTitle( $this->getTitle() );

		# Check the edit token.
		# Unlike Special:Upload, no fine distinctions about
		# whether they're uploading vs. cancelling, etc.
		if ( !$this->mTokenOk && $this->getRequest()->wasPosted() ) {
			$form->addPreText( $this->msg( 'session_fail_preview' )->parse() );
		}
		# Add the page-top text.
		$form->addPreText( $this->msg( 'multiupload-text' )->parse() );
		// @todo FIXME: add footer

		return $form;
	}

	public function getGlobalFormDescriptors() {
		return array();
	}
}

/**
 * Subclass of HTMLForm that provides the form section of Special:MultiUpload
 */
class MultiUploadForm extends UploadForm {
	protected $mPage;
	protected $mRows;
	protected $mLastIndex;

	public function __construct( $page, $rows, $lastIndex, IContextSource $context = null ) {
		$this->mPage = $page;
		$this->mRows = $rows;
		$this->mLastIndex = $lastIndex;
		parent::__construct( array(), $context );
		$this->mSourceIds = array();
		$this->mMessagePrefix = 'multiupload';
		$this->setSubmitText( wfMessage( 'multiupload-submit' )->parse() );
		// ashley 28 October 2016: I have no idea what I'm doing...
		$this->constructForm( $context );
	}

//	protected function constructData( array $options = array(), IContextSource $context = null ) {
//	}

	protected function constructForm( IContextSource $context ) {
		$descriptor = $this->getGlobalFormDescriptors()
			+ $this->mPage->getGlobalFormDescriptors();
		foreach ( $this->mRows as $row ) {
			$rowdesc = $row->getFormDescriptors();
			$descriptor = $descriptor + $rowdesc;
		}
		HTMLForm::__construct( $descriptor, $context, 'upload' );
	}

	protected function getGlobalFormDescriptors() {
		return array(
			'LastRowIndex' => array(
				'type' => 'hidden',
				'id' => 'wpLastRowIndex',
				'default' => $this->mLastIndex,
			),
		);
	}

	public function getLegend( $key ) {
		$parts = explode( '-', $key );
		$msg = array_shift( $parts );
		return wfMessage( "{$this->mMessagePrefix}-$msg", $parts )->parse();
	}

	protected function addUploadJS() {
		$out = $this->getOutput();
		parent::addUploadJS();
		$jsConfig = array(
			'wpFirstRowIndex' => $this->mPage->mFrom,
			'wpLastRowIndex' => $this->mPage->mTo,
			'wgMultiUploadMaxPhpUploadSize' => min(
				wfShorthandToInteger( ini_get( 'upload_max_filesize' ) ),
				wfShorthandToInteger( ini_get( 'post_max_size' ) )
			)
		);
		foreach ( $this->mRows as $row ) {
			$jsConfig = $jsConfig + $row->jsConfigVars();
		}
		$out->addJsConfigVars( $jsConfig );

		$out->addModules( array(
			'ext.multiupload.top',
			'ext.multiupload',
		) );
	}
}

/**
 * Hoping this gets merged into core, won't have to do it here
 */
//if ( !class_exists( 'FauxWebRequestUpload' ) ) {
	/**
	 * A WebRequestUpload that can be faked.
	 */
	class FauxWebRequestUpload extends WebRequestUpload {
		/**
		 * Constructor. Should only be called by FauxRequest.
		 *
		 * @param $request WebRequest The associated request
		 * @param array $data Data in the same format that would be found
		 *          in the $_FILES array. If provided, will be used
		 *          instead of $_FILES[$key].
		 */
		public function __construct( $request, $data ) {
			$this->request = $request;
			$this->fileInfo = $data;
			$this->doesExist = true;
		}
	}
	/**
	 * allow DerivativeRequest to include fake uploaded files
	 */
	class DerivativeRequestWithFiles extends DerivativeRequest {
		/**
		 * @param string $key
		 * @return FauxWebRequestUpload|WebRequestUpload
		 */
		public function getUpload( $key ) {
			if ( array_key_exists( $key, $this->data ) ) {
				return new FauxWebRequestUpload( $this, $this->data[$key] );
			} else {
				return new WebRequestUpload( $this, $key );
			}
		}
	}
//} else {
	/**
	 * If the feature is in MW core, just use it
	 */
//	class DerivativeRequestWithFiles extends DerivativeRequest { }
//}

class UploadRow extends SpecialUpload {
	public $mPage;
	public $mRowNumber;
	public $mRequest;
	public $mFormMessage;
	public $mSessionKey;
	public $mHideIgnoreWarning;
	public $mExtraButtons;

	/**
	 * Different constructor, let it know which row it is and
	 * the upload object it belongs to
	 */
	public function __construct( $page, $number ) {
		$this->mPage = $page;
		$this->setContext( $page->getContext() );
		$this->mRowNumber = $number;
		$this->mRequest = null;
		$this->mFormMessage = '';
		$this->mSessionKey = '';
		$this->mHideIgnoreWarning = '';
		$this->mExtraButtons = array();
	}

	/**
	 * UploadBase and various parent class methods expect certain
	 * form field names that don't have a row number appended.
	 * Here we create a fake request object that responds to those field names.
	 *
	 * @return DerivativeRequestWithFiles
	 */
	public function getRequest() {
		if ( !$this->mRequest ) {
			$webRequest = $this->mPage->getRequest();
			$i = $this->mRowNumber;
			$valuesKept = $valuesAltered = array();

			foreach ( $webRequest->getValues() + $_FILES as $key => $value ) {
				$matches = null;
				$prefixMatch = preg_match( '/^(.*?)(\d+)$/', $key, $matches );
				if ( $prefixMatch === false ) {
					// ERROR
				} elseif ( $prefixMatch == 0 ) {
					// key has no row number
					$valuesKept[$key] = $value;
				} elseif ( $matches[2] == $this->mRowNumber ) {
					// key has my row number
					$valuesAltered[$matches[1]] = $value;
				}	// else it has some other row number
			}

			$this->mRequest = new DerivativeRequestWithFiles(
				$webRequest,
				$valuesKept + $valuesAltered,
				$webRequest->wasPosted()
			);
		}

		return $this->mRequest;
	}

	/**
	 * Respond to submitted form data and/or display upload form
	 * @since 1.23
	 */
	public function handleRequestData() {
		$request = $this->getRequest();
		$this->mUploadSuccessful = $request->getCheck( 'wpUploadSuccessful' );

		$this->loadRequest();

		# Unsave the temporary file in case this was a cancelled upload
		if ( $this->mCancelUpload ) {
			if ( !$this->unsaveUploadedFile() ) {
				# Something went wrong, so unsaveUploadedFile showed a warning
				return;
			}
		}

		# Process upload or show a form
		if ( $this->shouldProcessUpload() ) {
			$this->processUpload();
		} else {
			# Backwards compatibility hook
			if ( !Hooks::run( 'UploadForm:initial', array( &$this ) ) ) {
				wfDebug( "Hook 'UploadForm:initial' broke output of the upload form" );
				return;
			}
			// MediaWiki 1.28 fix -- either MW doesn't call the empty function
			// below or then calling it has no effect, but for whatever reason
			// MW tends to act like it calls the *parent* class' function w/
			// that name, which breaks stuff. Commenting this out doesn't appear
			// to have any nasty side-effects, so...
			//$this->showUploadForm( $this->getUploadForm() );
		}

		# Cleanup
		if ( $this->mUpload ) {
			$this->mUpload->cleanupTempFile();
		}
	}

	/**
	 * Unlike the superclass, don't actually create a form object when
	 * this is called, wait and do it when the page object is ready to
	 * assemble its full output form.
	 *
	 * @param HTMLForm|string $form An HTMLForm instance or HTML string to show [unused]
	 */
	public function showUploadForm( $form ) {
	}

	/**
	 * Format an upload error message for display.
	 *
	 * @param string $message HTML string
	 * @return string HTML message
	 * @since 1.23
	 */
	protected function getUploadError( $message ) {
		return '<h2>' . $this->msg( 'uploadwarning' )->escaped() . "</h2>\n" .
			'<div class="error">' . $message . "</div>\n";
	}

	/**
	 * Construct a recoverable error message.
	 *
	 * See showRecoverableUploadError below.
	 *
	 * @param string $message HTML message to be passed to mainUploadForm
	 * @return string formatted HTML
	 * @since 1.23
	 */
	protected function getRecoverableUploadError( $message ) {
		return '<h2>' . $this->msg( 'uploaderror' )->escaped() . "</h2>\n" .
			'<div class="error">' . $message . "</div>\n";
	}

	public function showUploadError( $message ) {
		$this->mFormMessage .= $this->getUploadError( $message );
	}

	protected function showRecoverableUploadError( $message ) {
		$this->mSessionKey = $this->mUpload->stashSession();
		$this->mFormMessage .= $this->getRecoverableUploadError( $message );
	}

	/**
	 * Construct a formatted list of upload warnings.
	 *
	 * @todo FIXME: filthy copypasta to work around a fatal --ashley 29 October 2016
	 *
	 * @param array $warnings
	 * @return string|bool A string if there are warnings to display, false if there are no
	 *         warnings and it should continue processing
	 * @return string Formatted HTML
	 * @since 1.23
	 */
	protected function getUploadWarning( $warnings ) {
		# If there are no warnings, or warnings we can ignore, return early.
		# mDestWarningAck is set when some javascript has shown the warning
		# to the user. mForReUpload is set when the user clicks the "upload a
		# new version" link.
		if ( !$warnings || ( count( $warnings ) == 1
			&& isset( $warnings['exists'] )
			&& ( $this->mDestWarningAck || $this->mForReUpload ) )
		) {
			return false;
		}

		$warningHtml = '<h2>' . $this->msg( 'uploadwarning' )->escaped() . "</h2>\n"
			. '<ul class="warning">';
		foreach ( $warnings as $warning => $args ) {
			if ( $warning == 'badfilename' ) {
				$this->mDesiredDestName = Title::makeTitle( NS_FILE, $args )->getText();
			}
			if ( $warning == 'exists' ) {
				$msg = "\t<li>" . self::getExistsWarning( $args ) . "</li>\n";
			} elseif ( $warning == 'duplicate' ) {
				$msg = $this->getDupeWarning( $args );
			} elseif ( $warning == 'duplicate-archive' ) {
				$msg = "\t<li>" . $this->msg( 'file-deleted-duplicate',
						Title::makeTitle( NS_FILE, $args )->getPrefixedText() )->parse()
					. "</li>\n";
			} else {
				if ( $args === true ) {
					$args = array();
				} elseif ( !is_array( $args ) ) {
					$args = array( $args );
				}
				$msg = "\t<li>" . $this->msg( $warning, $args )->parse() . "</li>\n";
			}
			$warningHtml .= $msg;
		}
		$warningHtml .= "</ul>\n";
		$warningHtml .= $this->msg( 'uploadwarning-text' )->parseAsBlock();

		return $warningHtml;
	}

	/**
	 * Stash the upload, show the main form, but add a "continue anyway" button.
	 * Also check whether there are actually warnings to display.
	 *
	 * @param array $warnings
	 * @return bool True if warnings were displayed, false if there are no
	 *         warnings and it should continue processing
	 */
	protected function showUploadWarning( $warnings ) {
		$warningHtml = $this->getUploadWarning( $warnings );
		if ( $warningHtml === false ) {
			return false;
		}

		$sessionKey = $this->mUpload->stashSession();

		$form = $this->getUploadForm( $warningHtml, $sessionKey, /* $hideIgnoreWarning */ true );
		$form->setSubmitText( $this->msg( 'upload-tryagain' )->text() );
		$form->addButton( 'wpUploadIgnoreWarning', $this->msg( 'ignorewarning' )->text() );
		$form->addButton( 'wpCancelUpload', $this->msg( 'reuploaddesc' )->text() );

		$this->showUploadForm( $form );

		# Indicate that we showed a form
		return true;
	}

	/* OLD VERSION:
	protected function showUploadWarning( $warnings ) {
		$warningHtml = $this->getUploadWarning( $warnings );
		if ( $warningHtml === false ) {
			return false;
		}

		$this->mSessionKey = $this->mUpload->stashSession();
		$this->mFormMessage .= $warningHtml;
		$this->mHideIgnoreWarning = true;
		# Special:Upload changes the 'Upload' button to
		# 'Submit modified file description', and adds two
		# additional submit buttons. We add the additional
		# two as check boxes, and just leave the
		# 'Upload' button below all rows.
		$this->mExtraButtons = array(
			'UploadIgnoreWarning' => 'ignorewarning',
			'CancelUpload' => 'reuploaddesc',
		);

		return true;
	}
	*/

	/**
	 * This is apparently a pretty bad one.
	 * Special:Upload replaces the whole page with an error page
	 * when this happens. I'll just do it as an error message added
	 * to the form. But if it happens, you should probably start
	 * over clean.
	 */
	protected function showFileDeleteError() {
		$this->mFormMessage .= '<div class="error">'
			. $this->getOutput()->msg(
				'filenotfound', $this->mUpload->getTempPath() )
			. '</div>';
	}

	/**
	 * Suppress error message that file is empty, because this
	 * happens normally when you don't fill all the rows of the form.
	 */
	protected function processVerificationError( $details ) {
		if (
			$details['status'] === UploadBase::EMPTY_FILE &&
			$this->mDesiredDestName === ''
		)
		{
			return;
		}
		parent::processVerificationError( $details );
	}

	protected function createFormRow() {
		return new UploadFormRow(
			$this,
			array(
				'watch' => $this->getWatchCheck(),
				'forreupload' => $this->mForReUpload,
				'sessionkey' => $this->mSessionKey,
				'hideignorewarning' => $this->mHideIgnoreWarning,
				'destwarningack' => (bool)$this->mDestWarningAck,

				'description' => $this->mComment,
				'texttop' => $this->uploadFormTextTop,
				'textaftersummary' => $this->uploadFormTextAfterSummary,
				'destfile' => $this->mDesiredDestName,
				'sourcetype' => $this->mSourceType,
			),
			$this->getContext()
		);
	}

	/**
	 * Assemble the text of the "view X deleted revisions" link
	 *
	 * @todo FIXME: yet another filthy hack since this returns a string
	 * instead of using core's OutputPage directly as SpecialUpload::showViewDeletedLinks()
	 * does. --ashley 29 October 2016
	 *
	 * @return string
	 * @since 1.23
	 */
	protected function getViewDeletedLinks() {
		$title = Title::makeTitleSafe( NS_FILE, $this->mDesiredDestName );
		$user = $this->getUser();
		// Show a subtitle link to deleted revisions (to sysops et al only)
		if ( $title instanceof Title ) {
			$count = $title->isDeleted();
			if ( $count > 0 && $user->isAllowed( 'deletedhistory' ) ) {
				$restorelink = Linker::linkKnown(
					SpecialPage::getTitleFor( 'Undelete', $title->getPrefixedText() ),
					$this->msg( 'restorelink' )->numParams( $count )->escaped()
				);
				$link = $this->msg( $user->isAllowed( 'delete' ) ? 'thisisdeleted' : 'viewdeleted' )
					->rawParams( $restorelink )->parseAsBlock();
				return "<div id=\"contentSub2\">{$link}</div>";
			}
		}
		return '';
	}

	public function getFormDescriptors() {
		# Initialize form
		$form = $this->createFormRow();

		$preText = '';

		# Add links if file was previously deleted
		if ( $this->mDesiredDestName ) {
			$preText .= $this->getViewDeletedLinks();
		}

		# Give a notice if the user is uploading a file that has been deleted or moved
		# Note that this is independent from the message 'filewasdeleted' that requires JS
		$desiredTitleObj = Title::makeTitleSafe( NS_FILE, $this->mDesiredDestName );

		$delNotice = ''; // empty by default
		if ( $desiredTitleObj instanceof Title && !$desiredTitleObj->exists() ) {
			LogEventsList::showLogExtract(
				$delNotice,
				array( 'delete', 'move' ),
				$desiredTitleObj,
				'',
				array(
					'lim' => 10,
					'conds' => array( "log_action != 'revision'" ),
					'showIfEmpty' => false,
					'msgKey' => array( 'upload-recreate-warning' )
				)
			);
		}
		$preText .= $delNotice;

		$preText .= $this->mFormMessage;

		return $form->descriptor( $preText, $this->mExtraButtons,
				$this->mUploadSuccessful );
	}

	// @todo FIXME: <s>not called anymore</s>, core SpecialUpload::execute() used to
	// call this right above the 'UploadForm:initial' hook --ashley 28 October 2016
	protected function shouldProcessUpload() {
		return ( !$this->mUploadSuccessful &&
				$this->mPage->mTokenOk && !$this->mCancelUpload &&
				( $this->getRequest()->getVal( 'wpDestFile' ) &&
				$this->mUploadClicked ) );
	}

	// @todo FIXME: doesn't exist in core anymore as of 1.27. remove? --ashley 28 October 2016
	protected function uploadSucceeded() {
		$this->mDesiredDestName = $this->mLocalFile->getTitle()->getDBkey();
	}

	/**
	 * @todo FIXME: probably should be something like this:
	 * @code
	 $this->getOutput()->addJsConfigVars( array( ... ) );
	 * @endcode
	 * but where would it then be called from? Hmm... --ashley 28 October 2016
	 * @return array
	 */
	public function jsConfigVars() {
		return array(
			'wgMultiUploadAutoFill' . $this->mRowNumber =>
				( !$this->mForReUpload &&
				// if mDestFile was provided in the request,
				// don't overwrite it by autofilling
				$this->mDesiredDestName === '' ),
		);
	}

	protected function addUploadJS() {
		$out = $this->getOutput();
		$out->addJsConfigVars( array(
			'wgMultiUploadAutoFill' . $this->mRowNumber =>
				( !$this->mForReUpload &&
				// if mDestFile was provided in the request,
				// don't overwrite it by autofilling
				$this->mDesiredDestName === '' ),
		) );
	}
}

class UploadFormRow extends UploadForm {
	public $mRow;

	function __construct( $row, array $options = array(), IContextSource $context = null ) {
		$this->mRow = $row;
		# setContext is called in the constructor, but it's needed
		# before we get there
		$this->setContext( $context );

		$this->mWatch = !empty( $options['watch'] );
		$this->mForReUpload = !empty( $options['forreupload'] );
		$this->mSessionKey = isset( $options['sessionkey'] )
				? $options['sessionkey'] : '';
		$this->mHideIgnoreWarning = !empty( $options['hideignorewarning'] );
		$this->mDestWarningAck = !empty( $options['destwarningack'] );
		$this->mDestFile = isset( $options['destfile'] ) ? $options['destfile'] : '';

		$this->mComment = isset( $options['description'] ) ?
			$options['description'] : '';

		$this->mTextTop = isset( $options['texttop'] )
			? $options['texttop'] : '';

		$this->mTextAfterSummary = isset( $options['textaftersummary'] )
			? $options['textaftersummary'] : '';

		$this->mSourceType = isset( $options['sourcetype'] )
			? $options['sourcetype'] : '';
		# HTMLForm::__construct( array(), $context, 'upload' );
		# $this->mSourceIds = array();
	}

	protected function twoColumnDescriptor( $text, $section ) {
		return array(
			'type' => 'info',
			'raw' => true,
			'rawrow' => true,
			'default' => '<tr><td colspan="2">' . $text . '</td></tr>',
			'section' => $section,
		);
	}

	protected function uploadedMessage() {
		$destTitle = Title::newFromText( $this->mDestFile, NS_FILE );
		return '<div class="multiupload-success-message">'
			. wfMessage( 'multiupload-uploadedto',
				Linker::linkKnown(
					$destTitle,
					$destTitle->getText()
				) )->text()
			. '</div>';
	}

	protected function uploadSucceededDescriptor( $i, $sectionLabel ) {
		return array(
			'UploadedMessage' . $i => $this->twoColumnDescriptor(
				$this->uploadedMessage(),
				$sectionLabel
			),
			'DestFile' . $i => array(
				'type' => 'hidden',
				'default' => $this->mDestFile,
				'section' => $sectionLabel
			),
			'UploadSuccessful' . $i => array(
				'type' => 'hidden',
				'default' => true,
				'section' => $sectionLabel
			),
		);
	}

	public function descriptor( $preText = '', $extraButtons = array(),
			$uploadSuccessful = false ) {
		$descriptor = array();
		$i = $this->mRow->mRowNumber;
		$sectionLabel = 'row-' . $i;

		if ( $uploadSuccessful ) {
			return $this->uploadSucceededDescriptor( $i, $sectionLabel );
		}

		$sectionDescriptors = $this->getSourceSection()
				+ $this->getDescriptionSection()
				+ $this->getOptionsSection();
		$header = '';

		foreach ( array( $preText, $this->mHeader ) + $this->mSectionHeaders as $head ) {
			if ( $head != '' ) {
				if ( $header != '' ) {
					$header .= "<br/>\n";
				}
				$header .= $head;
			}
		}

		$preTextSection = array();
		if ( $header != '' ) {
			$preTextSection['Message'] = $this->twoColumnDescriptor(
				$header, $sectionLabel );
		}

		# a couple markers for the JavaScript animations
		if ( isset( $sectionDescriptors['DestFile'] ) ) {
			$sectionDescriptors['DestFile']['cssclass'] = 'multiupload-first-to-collapse multiupload-width-exemplar';
		}

		foreach ( $preTextSection + $sectionDescriptors as $name => $field ) {
			if ( isset( $field['id'] ) ) {
				# put the IDs that Special:Upload uses into
				# the class attributes, without numbers appended,
				# so that JavaScript routines can find them that
				# way
				if ( isset( $field['cssclass'] ) ) {
					$field['cssclass'] .= ' ' . $field['id'];
				} else {
					$field['cssclass'] = $field['id'];
				}
				# add the row number to the actual ID, for use
				# as distinct form fields.
				$field['id'] = $field['id'] . $i;
			}
			if ( isset( $field['radio-name'] ) ) {
				$field['radio-name'] = $field['radio-name'] . $i;
			}
			if ( isset( $field['section'] ) ) {
				if ( isset( $field['cssclass'] ) ) {
					$field['cssclass'] .= ' ';
				} else {
					$field['cssclass'] = '';
				}
				$field['cssclass'] .= 'mw-htmlform-section-'
					. str_replace( '/', '-', $field['section'] );
			}
			$field['section'] = $sectionLabel;
			$descriptor["$name$i"] = $field;
		}

		foreach ( $extraButtons as $key => $msg ) {
			$descriptor[$key] = array(
				'type' => 'check',
				'id' => $key,
				'label-message' => $msg,
				'section' => $sectionLabel,
			);
		}

		return $descriptor;
	}
}
