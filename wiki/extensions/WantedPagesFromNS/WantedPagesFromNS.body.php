<?php
/**
 WantedPagesFromNS v1.0.0 beta -- Shows list of wanted page from specified namespace

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

 * @file
 * @ingroup Extensions
 */

if ( !defined( 'MEDIAWIKI' ) ) {
 echo( "This file is an extension to the MediaWiki software and is not a valid access point" );
 die( 1 );
}

class WantedPagesFromNS {

  //gets value from the parameter list
  function get( $name, $value = null, $parser = null ) {
    if ( preg_match( "/^\s*$name\s*=\s*(.*)/mi", $this->sInput, $matches ) ) {
      $arg = trim( $matches[1] );
      if ( is_int( $value ) )
        return intval( $arg );
      elseif ( is_null( $parser ) )
        return htmlspecialchars( $arg );
      else
        return $parser->replaceVariables( $arg );
    }
    return $value;
  }

  function msg( $type, $error = null ) {
    if ( $error && ( $this->get( 'suppresserrors' ) == 'true' ) )
      return '';

    return wfMessage( $type )->escaped();
  }

  function parse( &$input, &$parser ) {
    global $wgContLang;

    $this->sInput =& $input;

    $arg = $this->get( 'namespace', '', $parser );
    $iNamespace = $wgContLang->getNsIndex( $arg );
    if ( !$iNamespace ) {
      if ( ( $arg ) || ( $arg === '0' ) )
        $iNamespace = intval( $arg );
      else
        $iNamespace = - 1;
    }
    if ( $iNamespace < 0 )
      return $this->msg('wpfromns-nons', 1);

    $output = '';

    $count = 1;
    $start = 0;
    if ( !( $this->get( 'cache' ) == 'true' ) ) {
      $parser->disableCache();
    }
    if ( $start < 0 )
      $start = 0;

    // build the SQL query
    $dbr = wfGetDB( DB_SLAVE );
    $pagelinks = $dbr->tableName( 'pagelinks' );
    $page      = $dbr->tableName( 'page' );
    //The SQL below is derived from includes/specials/SpecialWantedpages.php
    $sql = "SELECT
              pl_namespace AS namespace,
              pl_title AS title,
              COUNT(*) AS value
            FROM $pagelinks
            LEFT JOIN $page AS pg1
            ON pl_namespace = pg1.page_namespace AND pl_title = pg1.page_title
            LEFT JOIN $page AS pg2
            ON pl_from = pg2.page_id
            WHERE pg1.page_namespace IS NULL
            AND pl_namespace = $iNamespace"
            //AND pg2.page_namespace != " . NS_MEDIAWIKI . "
            . " GROUP BY pl_namespace, pl_title";

    // process the query
    $res = $dbr->query($sql, __METHOD__ );

    while ( $row = $dbr->fetchObject( $res ) ) {
      $title = Title::makeTitle( $row->namespace, $row->title );

      $wlh = SpecialPage::getTitleFor( 'Whatlinkshere' );
      $label = wfMessage('wpfromns-links', $row->value)->text();

      $output .= '<li>' . Linker::link($title, $title->getText(), array(), array(), array('broken'))
                        . ' ('
                             . Linker::link($wlh , $label, array(), array('target' => $title->getPrefixedText()))
                        . ')'
                        . "</li>\n";
    }

    if ($output)
      return '<ul>' . $output . "</ul>\n";

    else //no pages found
      return wfMessage('wpfromns-nores')->text();
  }
}
