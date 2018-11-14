<?php

$table_prefix = 'phpbb_';

$forumDirectory = $_SERVER["DOCUMENT_ROOT"] . '/forums/';
define('PHPBB_ROOT_PATH', $forumDirectory);

define('IN_PHPBB', true);

define('FROM_MEDIAWIKI', true);

$phpbb_root_path = PHPBB_ROOT_PATH;

$phpEx = 'php';

include($phpbb_root_path . 'common.' . $phpEx);
$request->enable_super_globals();
$user->session_begin();
$auth->acl($user->data);
$user->setup();
