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


if ($user->data['user_id'] == ANONYMOUS) { // check if logged in
	http_response_code(404);
	die();
} elseif (!group_memberships(23, $user->data['user_id'], true)) { // check if member of editors
	http_response_code(403);
	die();
}

$skill_icons = array(
	"XP" => "https://clanquest.org/images/skill_icons/overall.png",
	"Attack" => "https://clanquest.org/images/skill_icons/attack.png",
	"Defence" => "https://clanquest.org/images/skill_icons/defence.png",
	"Strength" => "https://clanquest.org/images/skill_icons/strength.png",
	"Ranged" => "https://clanquest.org/images/skill_icons/ranged.png",
	"Prayer" => "https://clanquest.org/images/skill_icons/prayer.png",
	"Magic" => "https://clanquest.org/images/skill_icons/magic.png",
	"Constitution" => "https://clanquest.org/images/skill_icons/constitution.png",
	"Crafting" => "https://clanquest.org/images/skill_icons/crafting.png",
	"Mining" => "https://clanquest.org/images/skill_icons/mining.png",
	"Smithing" => "https://clanquest.org/images/skill_icons/smithing.png",
	"Fishing" => "https://clanquest.org/images/skill_icons/fishing.png",
	"Cooking" => "https://clanquest.org/images/skill_icons/cooking.png",
	"Firemaking" => "https://clanquest.org/images/skill_icons/firemaking.png",
	"Runecrafting" => "https://clanquest.org/images/skill_icons/runecrafting.png",
	"Dungeoneering" => "https://clanquest.org/images/skill_icons/dungeoneering.png",
	"Woodcutting" => "https://clanquest.org/images/skill_icons/woodcutting.png",
	"Agility" => "https://clanquest.org/images/skill_icons/agility.png",
	"Herblore" => "https://clanquest.org/images/skill_icons/herblore.png",
	"Thieving" => "https://clanquest.org/images/skill_icons/thieving.png",
	"Fletching" => "https://clanquest.org/images/skill_icons/fletching.png",
	"Slayer" => "https://clanquest.org/images/skill_icons/slayer.png",
	"Farming" => "https://clanquest.org/images/skill_icons/farming.png",
	"Construction" => "https://clanquest.org/images/skill_icons/construction.png",
	"Hunter" => "https://clanquest.org/images/skill_icons/hunter.png",
	"Summoning" => "https://clanquest.org/images/skill_icons/summoning.png",
	"Divination" => "https://clanquest.org/images/skill_icons/divination.png",
	"Invention" => "https://clanquest.org/images/skill_icons/invention.png",
	"Archaeology" => "https://clanquest.org/images/skill_icons/archaeology.png",
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

$start_date = request_var("start", "2018-01-01");
$end_date = request_var("end", "2018-12-31");

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
