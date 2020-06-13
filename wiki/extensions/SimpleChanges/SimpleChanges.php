<?php
/**
 * SimpleChanges - Special page that displays a barebones Recent Changes list
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * wfLoadExtension( 'SimpleChanges' );
 *
 * @ingroup Extensions
 * @author Ike Hecht
 * @version 0.2
 * @link https://www.mediawiki.org/wiki/Extension:SimpleChanges Documentation
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SimpleChanges' );
	wfWarn(
		'Deprecated PHP entry point used for SimpleChanges extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
} else {
	die( 'This version of the SimpleChanges extension requires MediaWiki 1.29+' );
}