<?php

// Clan Quest index loader - to be placed in website home directory
// Loader will set up a phpbb session then load a page providing
// the phpbb session info to be used for customization.

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

// phpbb session setup, include common.php for access
$phpEx = "php";
$phpbb_root_path = (defined('PHPBB_ROOT_PATH'))
	? PHPBB_ROOT_PATH
	: $cfg['phpbb_root_path'] . '/';
require_once($phpbb_root_path . 'common.' . $phpEx);

// setup the user session
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// set the homepage based on whether the user is logged in
if ($user->data['user_id'] == ANONYMOUS)
	$home_include = 'static/join_home.php';
else {
	// include the phpbb text parser for members homepage
	require $phpbb_root_path . 'includes/functions_posting.php';
	require $phpbb_root_path . 'includes/functions_display.php';
	$home_include = 'dynamic/members_home.php';
}

// template variables for main site
$site_title = '';
$display_title = true;
$display_search = true;
$large_header = false;
$credits = '&copy; ' . date('Y') . ' Clan Quest - The Questing Clan.<br>
	Clan Quest&trade; and any affiliated logos or trademarks are the property
	of The Questing Clan. All Rights Reserved.';
// page variable for determining which include file to load
switch (request_var('page', 'home')) {
	case 'about':
		$site_include = 'static/about.php';
		$site_title = 'About Clan Quest';
	break;
	case 'privacy':
		$site_include = 'static/privacy.php';
		$site_title = 'Privacy Policy';
	break;
	case 'legal':
		$site_include = 'static/legal.php';
		$site_title = 'Legal Disclaimer';
	break;
	case 'hub':
		$site_include = 'dynamic/hub.php';
		$site_title = 'RuneFest Hub';
		$display_search = false;
	break;
	case 'home':
	default:
		$site_include = $home_include;
		$display_title = false;
		$display_search = false;
		$large_header = true;
}

if (empty($site_title))
	$site_title = 'The Questing Clan';

page_header($site_title, false);

$template->assign_vars(array(
	'HIDE_TITLE'	=>	!$display_title,
	'S_DISPLAY_SEARCH' => $display_search,
	'LARGE_HEADER' => $large_header,
	'IS_MAIN_SITE'	=>	MAIN_SITE,
	'CREDIT_LINE'	=>	$credits,
));

$template->set_custom_style(
		'cq_template', $phpbb_root_path . 'styles/clanquest/template');
$template->set_filenames(array(
	'header' => 'overall_header.html',
	'footer' => 'overall_footer.html'
));

$template->display('header');
require $site_include;
$template->display('footer');
