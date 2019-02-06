<?php
header('Content-type: application/json');

// Initialize the phpbb connection
try {
	$cfg = (require_once('./includes/config.php'));
} catch (Exception $e) {
	die('Could not load config file. Please ensure includes/config.php exists.');
}

define('IN_PHPBB', true);
define('MAIN_SITE', true);

if (!defined('IN_PHPBB')) {
	exit();
}

// phpbb session setup, include common.php for access to database
$phpEx = "php";
$phpbb_root_path = (defined('PHPBB_ROOT_PATH'))
	? PHPBB_ROOT_PATH
	: $cfg['phpbb_root_path'] . '/';
require_once($phpbb_root_path . 'common.' . $phpEx);

// variable retrieval
// game = rs or osrs
$game = request_var('game', 'rs');
$game = ($game == 'rs' || $game == 'osrs') ? $game : false;
// specify a user id or...
$user_id = request_var('user_id', 1);
// specify a username in full
$username = request_var('username', 'Anonymous');

if ($game === false) { // no game was entered that is acceptable
	echo json_encode(['status' => 400, 'error' => 'Incorrect game specified.']);
	die();
}

// did they give us a username or userid? if not get the entire guild listing
if ($user_id <= 1 && $username == 'Anonymous') {
	$sql_arr = [
		'SELECT'	=>	'fd.pf_' . $game . '_name, fd.user_id, u.username',
		'FROM'		=>	['phpbb_profile_fields_data' => 'fd'],
		'LEFT_JOIN'	=>	[
			[
				'FROM'	=>	['phpbb_users' => 'u'],
				'ON'	=>	'fd.user_id = u.user_id'
			]
		],
		'WHERE'		=>	'fd.pf_' . $game . '_name NOT LIKE ""'
	];
	$sql = $db->sql_build_query('SELECT', $sql_arr);
	$result = $db->sql_query($sql);
	$guild_users = [];
	// build an array of guild users including: username, user id, in-game name
	while ($r = $db->sql_fetchrow($result)) {
		$guild_users[] = [
			'forum_username' => $r['username'],
			'forum_id' => $r['user_id'],
			'game_name' => $r['pf_' . $game . '_name']
		];
	}
	echo json_encode(['status' => 200, 'guild_users' => $guild_users]);
}
else {
	if ($user_id == 1) { // Get the user id from the username supplied
		$sql_arr = [
			'SELECT'	=>	'user_id',
			'FROM'		=>	['phpbb_users' => 'u'],
			'WHERE'		=>	'username = "' . $username . '"'
		];
		$sql = $db->sql_build_query('SELECT', $sql_arr);
		$result = $db->sql_query($sql);
		$row_result = $db->sql_fetchrow($result);
		$user_id = $row_result['user_id'];
	}
	
	// fetch a specific in-game name with a given user id
	$sql_arr = [
		'SELECT'	=>	'pf_' . $game . '_name',
		'FROM'		=>	['phpbb_profile_fields_data' => 'fd'],
		'WHERE'		=>	'user_id = ' . $user_id
	];
	
	$sql = $db->sql_build_query('SELECT', $sql_arr);
	$result = $db->sql_query($sql);
	$row_result = $db->sql_fetchrow($result);
	$game_name = $row_result['pf_' . $game . '_name'];
	echo json_encode(['status' => 200, 'game_name' => $game_name]);	
}
