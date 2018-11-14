<?php

class ExtUserFunctions {

	/**
	 * @param $parser Parser
	 * @return bool
	 */
	public static function clearState( $parser ) {
		$parser->pf_ifexist_breakdown = array();
		return true;
	}

	/**
	 * Register ParserClearState hook.
	 * We defer this until needed to avoid the loading of the code of this file
	 * when no parser function is actually called.
	 */
	public static function registerClearHook() {
		static $done = false;
		if( !$done ) {
			global $wgHooks;
			$wgHooks['ParserClearState'][] = __CLASS__ . '::clearState';
			$done = true;
		}
	}

	/**
	 * @return User
	 * Using $wgUser Incompatibility with SMW using via $parser
	 **/
	private static function getUserObj() {
		global $wgUser;
		return $wgUser;
	}

	/**
	 * @param $parser Parser
	 * @param $frame PPFrame
	 * @param $args array
	 * @return string
	 */
	public static function ifanonObj( $parser, $frame, $args ) {
		$parser->disableCache();
		$pUser = self::getUserObj();

		if( $pUser->isAnon() ){
			return isset( $args[0] ) ? trim( $frame->expand( $args[0] ) ) : '';
		} else {
			return isset( $args[1] ) ? trim( $frame->expand( $args[1] ) ) : '';
		}
	}

	/**
	 * @param $parser Parser
	 * @param $frame PPFrame
	 * @param $args array
	 * @return string
	 */
	public static function ifblockedObj( $parser, $frame, $args ) {
		$parser->disableCache();
		$pUser = self::getUserObj();

		if( $pUser->isBlocked() ){
			return isset( $args[0] ) ? trim( $frame->expand( $args[0] ) ) : '';
		} else {
			return isset( $args[1] ) ? trim( $frame->expand( $args[1] ) ) : '';
		}
	}

	/**
	 * @param $parser Parser
	 * @param $frame PPFrame
	 * @param $args array
	 * @return string
	 */
	public static function ifsysopObj( $parser, $frame, $args ) {
		$parser->disableCache();
		$pUser = self::getUserObj();

		if( $pUser->isAllowed( 'protect' ) ){
			return isset( $args[0] ) ? trim( $frame->expand( $args[0] ) ) : '';
		} else {
			return isset( $args[1] ) ? trim( $frame->expand( $args[1] ) ) : '';
		}
	}

	/**
	 * @param $parser Parser
	 * @param $frame PPFrame
	 * @param $args array
	 * @return string
	 */
	public static function ifingroupObj ( $parser, $frame, $args ) {
		$parser->disableCache();
		$pUser = self::getUserObj();

		$grp = isset( $args[0] ) ? trim( $frame->expand( $args[0] ) ) : '';

		if( $grp!=='' ) {
			# Considering multiple groups
			$allgrp = explode(",", $grp);

			$userGroups = $pUser->getEffectiveGroups();
			foreach ( $allgrp as $elgrp ) {
				if ( in_array( trim( $elgrp ), $userGroups ) ) {
					return isset( $args[1] ) ? trim( $frame->expand( $args[1] ) ) : '';
				}
			}
		}
		return isset( $args[2] ) ? trim( $frame->expand( $args[2] ) ) : '';
	}

	/**
	 * @param $parser Parser
	 * @param $alt string
	 * @return String
	 */
	public static function realname( $parser, $alt = '' ) {
		$parser->disableCache();
		$pUser = self::getUserObj();

		if( $pUser->isAnon() && $alt !== '' ) {
			return $alt;
		}
		return $pUser->getRealName();
	}

	/**
	 * @param $parser Parser
	 * @param $alt string
	 * @return String
	 */
	public static function username( $parser, $alt = '' ) {
		$parser->disableCache();
		$pUser = self::getUserObj();

		if( $pUser->isAnon() && $alt !== '' ) {
			return $alt;
		}
		return $pUser->getName();
	}

	/**
	 * @param $parser Parser
	 * @param $alt string
	 * @return String
	 */
	public static function useremail( $parser, $alt = '' ) {
		$parser->disableCache();
		$pUser = self::getUserObj();

		if($pUser->isAnon() && $alt!=='') {
			return $alt;
		}
		return $pUser->getEmail();
	}

	/**
	 * @param $parser Parser
	 * @param $alt string
	 * @return String
	 */
	public static function nickname( $parser, $alt = '' ) {
		$parser->disableCache();
		$pUser = self::getUserObj();

		if( $pUser->isAnon() ) {
			if ( $alt !== '' ) {
				return $alt;
			}
			return $pUser->getName();
		}
		$nickname = $pUser->getOption( 'nickname' );
		$nickname = $nickname === '' ? $pUser->getName() : $nickname;
		return $nickname;
	}

	/**
	 * @param $parser Parser
	 * @return string
	 */
	public static function ip( $parser ) {
		$parser->disableCache();
		$request = self::getUserObj()->getRequest();
		return $request->getIP();
	}

}
