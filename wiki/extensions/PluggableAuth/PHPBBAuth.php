<?php

class PHPBBAuth extends PluggableAuth {

	public function authenticate( &$id, &$username, &$realname, &$email, &$errorMsg ) {
		global $db, $cache, $config, $auth, $user, $template, $request, $symfony_request, $phpbb_filesystem, $phpbb_container, $phpbb_dispatcher, $table_prefix, $phpEx, $phpbb_root_path;
		$username = $user->data['username_clean'];
		$id = null;
		$realname = $user->data['username'];
		$email = $user->data['user_email'];

		if ($username == 'anonymous') {
			$errorMsg = 'Can not authenticate anonymous user.';
			return false;
		}

		unset($db); unset($cache); unset($config); unset($user); unset($auth); unset($template); unset($phpbb_root_path); unset($phpEx); unset($request); unset($symfony_request); unset($phpbb_filesystem); unset($phpbb_container); unset($phpbb_dispatcher);

		wfDebug('phpbb data: username: ' . $username . ' real name: ' . $realname . ' email: ' . $email);

		return true;
	}

	public function deauthenticate( User &$user ) {}

	public function saveExtraAttributes( $id ) {}

	public static function autoLoginInit( &$out, &$skin ) {
		global $user;
		if ( !$GLOBALS['wgPluggableAuth_EnableAutoLogin'] ) {
			return true;
		}

		// if the phpbb user is 'anonymous' or not logged in, don't redirect
		if ( $user->data['username_clean'] == 'anonymous' ) {
			return true;
		}
		
		if ( !$out->getUser()->isAnon() ) {
			return true;
		}
		$loginSpecialPages = ExtensionRegistry::getInstance()->getAttribute(
			'PluggableAuthLoginSpecialPages' );
		$title = $out->getTitle();
		foreach ( $loginSpecialPages as $page ) {
			if ( $title->isSpecial( $page ) ) {
				return true;
			}
		}
		if ( !User::isEveryoneAllowed( 'read' ) && $title->userCan( 'read' ) ) {
			return true;
		}
		$out->addModules( 'ext.PluggableAuthAutoLogin' );
		return true;
	}
}
