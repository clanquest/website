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
require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);


if ($user->data['user_id'] == ANONYMOUS) {
	http_response_code(404);
	die();
} elseif (!group_memberships(23, $user->data['user_id'], true)) {
	http_response_code(403);
	die();
}

$skill_icons = array(
	"XP" => "http://i.imgur.com/e9Scoyr.png?1",
	"Attack" => "http://i.imgur.com/aIlGWj7.png?1",
	"Defence" => "http://i.imgur.com/BSHMZ9g.png?1",
	"Strength" => "http://i.imgur.com/TN11iB0.png?1",
	"Ranged" => "http://i.imgur.com/aolcwkI.png?1",
	"Prayer" => "http://i.imgur.com/5RAnYE2.png?1",
	"Magic" => "http://i.imgur.com/GKezxqd.png?1",
	"Constitution" => "http://i.imgur.com/yINgxxN.png?1",
	"Crafting" => "http://i.imgur.com/9k68VwV.png?1",
	"Mining" => "http://i.imgur.com/OrDE0F3.png?1",
	"Smithing" => "http://i.imgur.com/NxQRKjB.png?1",
	"Fishing" => "http://i.imgur.com/vnZN6Ko.png?1",
	"Cooking" => "http://i.imgur.com/1ZcM1R0.png?1",
	"Firemaking" => "http://i.imgur.com/1VRk0f9.png?1",
	"Runecrafting" => "http://i.imgur.com/g9C9BPh.png?1",
	"Dungeoneering" => "http://i.imgur.com/jzCOwwo.png?1",
	"Woodcutting" => "http://i.imgur.com/J5NPmLq.png?1",
	"Agility" => "http://i.imgur.com/XZokISx.png?1",
	"Herblore" => "http://i.imgur.com/txuk5P8.png?1",
	"Thieving" => "http://i.imgur.com/qM4olc0.png?1",
	"Fletching" => "http://i.imgur.com/wi6HiAQ.png?1",
	"Slayer" => "http://i.imgur.com/qbpB9iZ.png?1",
	"Farming" => "http://i.imgur.com/prlVhyF.png?1",
	"Construction" => "http://i.imgur.com/bhbOQBu.png?1",
	"Hunter" => "http://i.imgur.com/ZHMDypr.png?1",
	"Summoning" => "http://i.imgur.com/3DWpUNG.png?1",
	"Divination" => "http://i.imgur.com/aTRaKhA.png?1",
	"Invention" => "http://i.imgur.com/1FA9141.png?1",
	"Archaeology" => "https://clanquest.org/wiki/images/1/12/Archicon.jpg",
);

$colors = array(
	"808000",
	"0080FF",
	"00BF00",
	"00FF00",
	"00FFFF",
	"40FF00",
	"8080BF",
	"80FFFF",
	"BF0000",
	"FF0000",
	"FF4080",
	"FF40FF",
	"FF8000",
	"FF8040",
	"FFBF00",
	"FFBF00",
	"FFBFFF",
	"FFFF00",
);


function update_array_key($key, $value, &$arr) {
	if(!array_key_exists($key, $arr)) {
		$arr[$key] = array();
	}
	return array_push($arr[$key], $value);
}

function get_icons_for_skill($skill, $icons) {
	$res = array();
	$tokens = explode(" ", $skill);
	foreach ($tokens as $token) {
		array_push($res, $icons[$token]);
	}
	return $res;
}

$start_date = NULL;
$end_date = NULL;

if (isset($_GET["start"])) {
	$start_date = htmlspecialchars($_GET["start"]);
}
if (isset($_GET["end"])) {
	$end_date = htmlspecialchars($_GET["end"]);
}

if (is_null($start_date) || is_null($end_date)) {
	die("Must supply both start and end as query parameters.");
}

$date_regexp = "/\d{4}-\d{2}-\d{2}/";
if (preg_match($date_regexp, $start_date) === 0 || preg_match($date_regexp, $end_date) === 0) {
	die("Dates must be of the form YYYY-MM-DD");
}

$line_regexp = "/(?P<user>\w*) - (?P<level>\d+m?) (?P<skill>[^,]*)/";
$url = "https://update.rsbandb.com/highscores_achievements.php?start=$start_date&end=$end_date&clan=4";
$raw_data = file_get_contents($url);

if ($raw_data === false) {
	die("Could not fetch data from RSB&B. Please try again later.\n");
}

$cheevs = array(
	"99" => array(),
	"120" => array(),
	"200m" => array()
);

$result = preg_match_all($line_regexp, $raw_data, $lines, PREG_SET_ORDER);

foreach ($lines as $line) {
	update_array_key($line["skill"], $line["user"], $cheevs[$line["level"]]);
}


foreach($cheevs as $level => $skills) {
	shuffle($colors);
	echo "[table][row][column]";
	$idx = 0;

	foreach($skills as $skill => $users) {
		$color = $colors[$idx%count($colors)];
		$idx++;
		echo "$level [img]". join("[/img][img]", get_icons_for_skill($skill, $skill_icons)) ."[/img]:[color=#$color]" . join(", ", $users) . "[/color]<br>";

		if($idx === (int)ceil(count($skills)/2)) {
			echo "[/column][column]";
		}
	}
	echo "[/column][/row][/table]";
}
