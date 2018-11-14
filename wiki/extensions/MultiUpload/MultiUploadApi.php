<?php
/**
 * @file
 * @ingroup API
 */
class MultiUploadApiUnpack extends ApiBase {
	static public $pkgExtensions = array( 'tar', 'tgz', 'tar.gz', 'zip' );
	static public $unpackDirBase = 'MultiUpload_unpack_';

	public function suffixMatches( $string, $extension ) {
		$len = strlen( $extension );
		return substr( $string, -$len ) == $extension;
	}

	/**
	 * Get the temporary directory and the prefix in a platform-independent way,
	 * no matter what.
	 *
	 * @return array( temp dir (such as /tmp/MultiUpload_unpack_5pzFrr on *NIX), prefix (such as /tmp/MultiUpload_unpack_ on *NIX) )
	 */
	public function getTempDirAndPrefix() {
		global $wgMultiUploadTempDir;
		if ( wfIsWindows() ) {
			// tempnam(), or at least how this code uses it, is bugged on Windows
			// @see http://php.net/manual/en/function.tempnam.php
			// Quoth that page: "Note: Windows uses only the first three characters of prefix."
			$ourTempDir = is_dir( $wgMultiUploadTempDir ) ? $wgMultiUploadTempDir : wfTempDir(); // C:\Users\<user name>\AppData\Local\Temp
			$unpackLocation = realpath( tempnam( $ourTempDir, MultiUploadApiUnpack::$unpackDirBase ) ); // C:\Users\<user name>\AppData\Local\Temp\MulD6D8.tmp
			$removeMe = end( explode( DIRECTORY_SEPARATOR, $unpackLocation ) ); // such as MulD6D8.tmp
			$prefix = str_replace( str_replace( 'Mul', '', $removeMe ), '', $unpackLocation ); // should be something like C:\Users\<user name>\AppData\Local\Temp\Mul
		} else {
			// the end result is something like "/tmp/MultiUpload_unpack_Whcdu9"
			// here (for the $unpackLocation var) and "/tmp/MultiUpload_unpack_"
			// for the $prefix var
			$unpackLocation = realpath( tempnam(
				$wgMultiUploadTempDir,
				MultiUploadApiUnpack::$unpackDirBase
			) );
			$prefix = realpath( $wgMultiUploadTempDir ) . '/' .
				MultiUploadApiUnpack::$unpackDirBase;
		}
		return array( $unpackLocation, $prefix );
	}

	/**
	 * Given a package (e.g. tar.gz or .zip file), unpack it into a temporary
	 * location.
	 *
	 * @param string $pkgFile Path to the file to be unpacked, such as '/tmp/php192ujhK'
	 * @param string $srcName The name the file's supposed to have (i.e 'package.tar.gz')
	 * @return array In case of success, return value is array(true, location_code),
	 *   where location_code is the unique part of the path such that
	 *   tempDir().'/'.$unpackDirBase.location_code is the name of the directory
	 *   where the unpacked files are.
	 *   In case of error, returns array(false, error_message).
	 */
	public function unpack( $pkgFile, $srcName ) {
		list( $unpackLocation, $prefix ) = $this->getTempDirAndPrefix();

		wfDebugLog( 'MultiUploadApi', "unpackLocation is $unpackLocation, prefix is $prefix\n" );

		if ( strncmp( $unpackLocation, $prefix, strlen( $prefix ) ) != 0 ) {
			wfDebugLog( 'MultiUploadApi', "Temp directory $unpackLocation doesn't start with $prefix!\n" );
			return array( false, 'Temp directory ' . htmlentities( $unpackLocation ) .
				" doesn't start with " . htmlentities( $prefix ) . '!' );
		}

		$locationCode = substr( $unpackLocation, strlen( $prefix ) );
		wfDebugLog( 'MultiUploadApi', 'location code is ' . $locationCode . "\n" );
		if ( file_exists( $unpackLocation ) && !unlink( $unpackLocation ) ) {
			return array( false, "Couldn't unlink $unpackLocation" );
		}

		if ( !mkdir( $unpackLocation ) ) {
			return array( false, "Couldn't make temp dir $unpackLocation" );
		}

		if ( !chmod( $unpackLocation, 0700 ) ) {
			return array( false,
				"Couldn't set restricted permissions on temp dir $unpackLocation" );
		}

		# tar seems trustworthy not to extract files into other locations
		# TODO (found on Talk:WorkingWiki page): When unpacking uploaded .tar.gz files:
		# [http://en.wikipedia.org/wiki/Tar_(file_format) Wikipedia says]: GNU tar by default
		# refuses to create or extract absolute paths, but is still vulnerable to parent-directory
		# references. So I would need to check tar contents for ../ before extracting. In practice
		# it seems to be catching this case — but I should trap it explicitly anyway?
		if ( $this->suffixMatches( $srcName, '.tgz' ) || $this->suffixMatches( $srcName, '.tar.gz' ) ) {
			$unpackCommand = 'tar -xz -C ' . wfEscapeShellArg( $unpackLocation )
				. ' -f ' . wfEscapeShellArg( $pkgFile );
			system( $unpackCommand, $unpackSuccess );
		} elseif ( $this->suffixMatches( $srcName, '.tar' ) ) {
			$unpackCommand = 'tar -x -C ' . wfEscapeShellArg( $unpackLocation )
				. ' -f ' . wfEscapeShellArg( $pkgFile );
			system( $unpackCommand, $unpackSuccess );
		} elseif ( $this->suffixMatches( $srcName, '.zip' ) ) {
			# unzip also rejects files outside of the extraction directory
			if ( wfIsWindows() ) {
				// Windows is different
				// @see https://phabricator.wikimedia.org/T124908
				$E_STRICT_IS_STUPID = explode( '/', $pkgFile );
				$removeMeToGetTheRealTempDirPathOnWindows = end( $E_STRICT_IS_STUPID ); // [sic]
				$realUnpackLocation = str_replace( $removeMeToGetTheRealTempDirPathOnWindows, '', $pkgFile );

				$zip = new ZipArchive;
				$res = $zip->open( $pkgFile );
				if ( $res ) {
					wfDebugLog( 'MultiUploadApi', 'On Windows; ZipArchive extraction OK, extracting to ' . print_r( $realUnpackLocation, true ) );
					$zip->extractTo( $realUnpackLocation );
					$zip->close();
					$unpackSuccess = 0;
				} else {
					wfDebugLog( 'MultiUploadApi', 'On Windows; ZipArchive extraction FAILED, status code = ' . print_r( $res, true ) );
					// something went wrong and extraction failed
					$unpackSuccess = $res;
				}
			} else {
				$unpackCommand = 'unzip -q ' . wfEscapeShellArg( $pkgFile )
					. ' -d ' . wfEscapeShellArg( $unpackLocation );
				system( $unpackCommand, $unpackSuccess );
			}
		} else {
			return array( false, "Unknown filetype $srcName" );
		}

		if ( $unpackSuccess != 0 ) {
			return array( false,
				"command “{$unpackCommand}” failed with return code $unpackSuccess"
			);
		}

		return array( true, $locationCode );
	}

	/**
	 * Search a directory (recursively) for files
	 *
	 * @param string $dir Path to directory to search
	 * @return array|bool Array of relative filenames on success, or false on failure
	 */
	public function recursiveFindFiles( $dir ) {
		if ( is_dir( $dir ) ) {
			$dhl = opendir( $dir );
			if ( $dhl ) {
				$files = array();
				while ( ( $file = readdir( $dhl ) ) !== false ) {
					if ( $file == '.' || $file == '..' ) {
						continue;
					}
					$path = $dir . '/' . $file;
					if ( is_dir( $path ) ) {
						$filesWithin = $this->recursiveFindFiles( $path );
						if ( is_array( $filesWithin ) && count( $filesWithin ) > 0 ) {
							foreach ( $filesWithin as $fileWithin ) {
								$files[] = $file . '/' . $fileWithin;
							}
						}
					} elseif ( is_file( $path ) ) {
						$files[] = $file;
					}
				}
				return $files;
			} // else
			return false;
		} elseif ( is_file( $dir ) ) {
			return array( $dir );
		} // else

		return false;
	}

	public function recursiveUnlink( $filename, $delSelf ) {
		if ( !is_link( $filename ) && is_dir( $filename ) && ( $handle = opendir( $filename ) ) ) {
			while ( ( $entry = readdir( $handle ) ) !== false ) {
				if ( $entry !== '.' && $entry !== '..' ) {
					$this->recursiveUnlink( $filename . '/' . $entry, true );
				}
			}
		}
		if ( $delSelf ) {
			if ( is_dir( $filename ) && !is_link( $filename ) ) {
				rmdir( $filename );
			} elseif ( file_exists( $filename ) ) {
				unlink( $filename );
			}
		}
	}

	public function execute() {
		//global $wgMultiUploadTempDir;

		$params = $this->extractRequestParams();
		wfDebugLog( 'MultiUploadApi', 'MultiUploadApiUnpack, params is ' . json_encode( $params ) . "\n" );

		// We are called with a session key representing a file that's been
		// uploaded and stashed. First is to find the physical file.
		// NOTE: could possibly do this easier using UploadFromBase - when I
		// wrote this I thought that wasn't working but it actually is.
		// See https://sourceforge.net/p/workingwiki/bugs/472.
		$sessionkey = $params['key'];
		$repo = RepoGroup::singleton()->getLocalRepo();
		$stash = new UploadStash( $repo, $this->getUser() );
		$metadata = $stash->getMetadata( $sessionkey );
		$file = $repo->getLocalReference( $metadata['us_path'] );
		$path = $file->getPath();

		// Second is to unpack it. Code for that is in ImportQueue.
		$packageFileName = $params['filename'];
		wfDebugLog( 'MultiUploadApi', __METHOD__ . ': $packageFileName = ' . print_r( $packageFileName, true ) . ' and $path = ' . print_r( $path, true ) );
		list( $success, $unpackCode ) = $this->unpack( $path, $packageFileName );
		if ( !$success ) {
			$this->dieUsage(
				'Could not unpack uploaded package: ' . $unpackCode,
				'unknownerror'
			);
		}

		// $prefix is unused, but meh
		list( $unpackDir, $prefix ) = $this->getTempDirAndPrefix();
		wfDebugLog( 'MultiUploadApi', 'in ' . __METHOD__ . ', raw $unpackDir is: ' . print_r( $unpackDir, true ) . ' and $prefix is : ' . print_r( $prefix, true ) );
		// $unpackDir nowadays contains $unpackCode already
		/*
		$unpackDir = realpath( $wgMultiUploadTempDir ) . '/'
			. MultiUploadApiUnpack::$unpackDirBase
			. $unpackCode;
		*/

		// done with the package - delete it
		$stash->removeFile( $sessionkey );
		// hmm, that didn't work when I tested it. Make sure it gets deleted.
		unlink( $path );

		// now to traverse that directory, stash all the files and remember their
		// names
		if ( wfIsWindows() ) {
			// $path is something like D:\xamppnew\htdocs\shoutwiki\trunk/images/temp/5/56/20161107221803!php8B81.tmp
			// so we need to remove the last part
			$dir = str_replace( end( explode( '/', $path ) ), '', $path );
		} else {
			$dir = $unpackDir;
		}
		$filenames = $this->recursiveFindFiles( $dir );
		wfDebugLog( 'MultiUploadApi', 'filenames after finding them recursively: ' . print_r( $filenames, true ) );
		natsort( $filenames );
		wfDebugLog( 'MultiUploadApi', 'filenames after natsort()ing: ' . print_r( $filenames, true ) );
		$filedata = array();

		foreach ( $filenames as $filename ) {
			if ( $filename == 'index.html' ) {
				// I don't even...
				continue;
			}
			if ( wfIsWindows() ) {
				$fileToStash = $dir . $filename;
			} else {
				$fileToStash = $unpackDir . '/' . $filename;
			}
			wfDebugLog( 'MultiUploadApi', 'fileToStash: ' . print_r( $fileToStash, true ) . ', filename: ' . print_r( $filename, true ) );
			$stashedFile = $stash->stashFile( $fileToStash, 'file' );
			$filedata[] = array( $stashedFile->getFileKey(), $filename );
		}
		$this->recursiveUnlink( $dir, true );

		$res = array(
			'contents' => $filedata,
		);
		$this->getResult()->addValue( null, 'multiupload-unpack', $res );
	}

	public function getAllowedParams() {
		return array(
			'key' => array(
				ApiBase::PARAM_TYPE => 'string',
				# ApiBase::PARAM_REQUIRED => false
			),
			'filename' => array(
				ApiBase::PARAM_TYPE => 'string',
				# ApiBase::PARAM_REQUIRED => false
			),
		);
	}

	public function isWriteMode() {
		return true;
	}

	public function getParamDescription() {
		return array(
			'key' => '"filekey" obtained when uploading the package file to stash',
			'filename' => 'filename of the package',
		);
	}

	public function getDescription() {
		return 'Unpack a zip or tar file before importing its contents.';
	}
}