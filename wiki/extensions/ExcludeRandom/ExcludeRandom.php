<?php
/**
 * ExcludeRandom - this extension allows pages to be excluded from Special:Random
 *
 * To activate this extension, add the following into your LocalSettings.php file:
 * require_once "$IP/extensions/ExcludeRandom/ExcludeRandom.php";
 *
 * @ingroup Extensions
 * @author Matt Russell
 * @version 0.1
 * @link https://www.mediawiki.org/wiki/Extension:ExcludeRandom Documentation
 * @license BSD
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die();
}

// Define extensions info
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'ExcludeRandom',
	'author' => array( 'Matt Russell', '...' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:ExcludeRandom',
	'descriptionmsg' => 'excluderandom-desc',
	'version' => 1,
);

// Define internationalisation file
$wgExtensionMessagesFiles['ExcludeRandom'] = dirname( __FILE__ ) . '/ExcludeRandom.i18n.php';

$wgHooks['SpecialRandomGetRandomTitle'][] = 'wfExcludeRandomInit';
function wfExcludeRandomInit( &$rand, &$isRedir, &$namespaces, &$extra, &$title ) {
	global $wgExcludeRandomPages;
	if ( !$wgExcludeRandomPages ) {
		return true;
	}
	
	$db = wfGetDB( DB_SLAVE );
	foreach ( $wgExcludeRandomPages AS $cond ) {
		$pattern = $db->strencode( $cond );
		$pattern = str_replace(
			array( '_', '%', ' ', '*' ),
			array( '\_', '\%', '\_', '%' ),
			$pattern
		);
		$extra[] = "`page_title` NOT LIKE '$pattern'";
	}
	
	return true;
}
