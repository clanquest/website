<?php
$start_date = "2020-05-21";
$end_date = "2020-05-28";
$line_regexp = "/(?P<user>\w*) - (?P<level>\d+m?) (?P<skill>[^,]*)/";
$url = "https://update.rsbandb.com/highscores_achievements.php?start=$start_date&end=$end_date&clan=4";
$raw_data = file_get_contents($url);

if ($raw_data === false) {
	die("Could not fetch data from RSB&B. Please try again later.\n");
}

# Set up my data structures
$max_level = array();
$vmax_level = array();
$max_xp = array();

$result = preg_match_all($line_regexp, $raw_data, $lines, PREG_SET_ORDER);

foreach ($lines as $line) {
	switch($line["level"]) {
		case "99":
			if (!array_key_exists($line["skill"], $max_level)) {
				$max_level[$line["skill"]] = array();
			}
			array_push($max_level[$line["skill"]], $line["user"]);
			break;
		case "120":
			if (!array_key_exists($line["skill"], $vmax_level)) {
				$vmax_level[$line["skill"]] = array();
			}
			array_push($vmax_level[$line["skill"]], $line["user"]);
			break;
		case "200m":
			if (!array_key_exists($line["skill"], $max_xp)) {
				$max_xp[$line["skill"]] = array();
			}
			array_push($max_xp[$line["skill"]], $line["user"]);

	}
}