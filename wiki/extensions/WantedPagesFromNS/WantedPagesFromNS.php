<?php
/**
 WantedPagesFromNS -- Shows list of wanted page from specified namespace

 Author: Kazimierz Król

 Code based largely on DPL Forum extension by Ross McClure
 http://www.mediawiki.org/wiki/User:Algorithm

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 http://www.gnu.org/copyleft/gpl.html

 To install, add following to LocalSettings.php
   include_once("$IP/extensions/WantedPagesFromNS/WantedPagesFromNS.php");

*/

if ( !defined( 'MEDIAWIKI' ) ) {
  echo( "This file is an extension to the MediaWiki software and is not a valid access point" );
  die( 1 );
}

$wgExtensionFunctions[] = 'efWantedPagesFromNSInit';
$wgExtensionCredits['parserhook'][] = array(
  'path' => __FILE__,
  'name' => 'WantedPagesFromNS',
  'author' => 'Kazimierz Król',
  'version' => '1.1.0 beta',
  'descriptionmsg' => 'wpfromns-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgMessagesDirs['WantedPagesFromNS'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['WantedPagesFromNS'] = $dir . 'WantedPagesFromNS.i18n.php';
$wgAutoloadClasses['WantedPagesFromNS'] = $dir . 'WantedPagesFromNS.body.php';

function efWantedPagesFromNSInit() {
  global $wgParser;

	// Only call wfLoadExtensionMessages if it is defined, it was removed in 1.21
	if( function_exists( 'wfLoadExtensionMessages' ) ) {
		wfLoadExtensionMessages( 'WantedPagesFromNS' );
	}

  $wgParser->setHook( 'wantedpagens', 'PageListRender' );
  //$wgParser->setFunctionHook( 'forumlink', array( new DPLForum(), 'link' ) );
  return TRUE;
}

function PageListRender( $input, array $args, Parser $parser, PPFrame $frame ) {

  $f = new WantedPagesFromNS();
  return $f->parse( $input, $parser );
}
