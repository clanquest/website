<?php
/**
 * The UserPageEditProtection extension to MediaWiki allows to restrict the edit
 * access to user pages.
 *
 * @link https://www.mediawiki.org/wiki/Extension:UserPageEditProtection Homepage
 * @link https://phabricator.wikimedia.org/diffusion/EUPE/browse/master/README.md Documentation
 * @link https://www.mediawiki.org/wiki/Extension_talk:UserPageEditProtection Support
 * @link https://phabricator.wikimedia.org/maniphest/task/edit/form/1/ Issue tracker
 * @link https://phabricator.wikimedia.org/diffusion/EUPE/repository/master/ Source Code
 * @link https://github.com/wikimedia/mediawiki-extensions-UserPageEditProtection/releases Downloads
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @version 4.0.0 2016-10-27
 *
 * @author Lisa Ridley (lhridley/hoggwild5)
 * @author Eric Gingell (egingell)
 * @author Karsten Hoffmeyer (kghbln)
 *
 * @copyright Copyright (C) 2007, Lisa Ridley
 *
 * @license https://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

// Ensure that the script cannot be executed outside of MediaWiki
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is an extension to MediaWiki and cannot be run standalone.' );
}

// Register extension with MediaWiki
$wgExtensionCredits['other'][] = [
	'path' => __FILE__,
	'name' => 'UserPageEditProtection',
	'author' => [
		'Lisa Ridley',
		'Eric Gingell',
		'Karsten Hoffmeyer',
		'...'
	],
	'version' => '4.0.0',
	'url' => 'https://www.mediawiki.org/wiki/Extension:UserPageEditProtection',
	'descriptionmsg' => 'userpageeditprotection-desc',
	'license-name' => 'GPL-2.0+'
];

// Load extension's class
$wgAutoloadClasses['UserPageEditProtection'] = __DIR__ . '/UserPageEditProtection.class.php';

// Register extension messages
$wgMessagesDirs['UserPageEditProtection'] = __DIR__ . '/i18n';

// Add user permission
$wgAvailableRights[] = 'editalluserpages';
$wgGroupPermissions['sysop']['editalluserpages'] = true;

// Register hook
$wgHooks['userCan'][] = 'UserPageEditProtection::onUserCan';
