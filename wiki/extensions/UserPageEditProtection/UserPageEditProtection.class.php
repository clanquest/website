<?php
/**
 * UserPageEditProtection
 *
 * @file
 * @ingroup Extensions
 *
 * @license https://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
 */

class UserPageEditProtection {

	public static function onUserCan( $title, $user, $action, &$result ) {
		global $wgOnlyUserEditUserPage;
		$lTitle = explode( '/', $title->getText() );
		if ( !( $action == 'edit' || $action == 'move' ) ) {
			$result = null;
		return true;
		}
		if ( $title->mNamespace !== NS_USER ) {
			$result = null;
			return true;
		}
		if ( $wgOnlyUserEditUserPage ) {
			if ( $user->isAllowed( 'editalluserpages' ) || ( $user->getname() == $lTitle[0] ) ) {
				$result = null;
				return true;
			} else {
				$result = false;
				return false;
			}
		}
		$result = null;
		return true;
	}
}
