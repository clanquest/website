<?php
$wgExtensionCredits['validextensionclass'][] = array(
	'path'           => __FILE__,
	'name'           => 'Clan Quest Wiki Markup',
	'version'        => '1.0',
	'author'         => 'Clan Quest',
	'url'            => 'https://clanquest.org/wiki',
	'description'    => 'Provides custom wiki markup tags for the Clan Quest community.'
);
$wgHooks['ParserFirstCallInit'][] = 'ClanQuestWikiMarkup::onParserSetup';

class ClanQuestWikiMarkup {
	public static function onParserSetup(Parser $parser) {
		$parser->setHook('box', 'ClanQuestWikiMarkup::renderTagBox');
		$parser->setHook('col', 'ClanQuestWikiMarkup::renderTagCol');
		$parser->setHook('columns', 'ClanQuestWikiMarkup::renderTagColumns');
		$parser->setHook('soundcloud', 'ClanQuestWikiMarkup::renderTagSoundCloud');
		$parser->setHook('twitch', 'ClanQuestWikiMarkup::renderTagTwitch');
		$parser->setHook('vimeo', 'ClanQuestWikiMarkup::renderTagVimeo');
		$parser->setHook('youtube', 'ClanQuestWikiMarkup::renderTagYoutube');
	}

	public static function renderTagBox($input, array $args, Parser $parser, PPFRame $frame) {
		$output = $parser->recursiveTagParse($input, $frame);
		$style = array_key_exists('style', $args) ? " style=\"" . $args['style'] . "\"" : "";
		return "<div class=\"boon box\"" . $style .">" . $output . "</div>\n";
	}

	public static function renderTagCol($input, array $args, Parser $parser, PPFRame $frame) {
		$output = $parser->recursiveTagParse($input, $frame);
		$style = array_key_exists('style', $args) ? " style=\"" . $args['style'] . "\"" : "";
		return "<div class=\"column\"" . $style .">" . $output . "</div>\n";
	}

	public static function renderTagColumns($input, array $args, Parser $parser, PPFRame $frame) {
		$output = $parser->recursiveTagParse($input, $frame);
		$style = array_key_exists('style', $args) ? " style=\"" . $args['style'] . "\"" : "";
		return "<div class=\"column-container\"" . $style . ">" . $output . "</div>\n";
	}

	public static function renderTagSoundCloud($input, array $args, Parser $parser, PPFRame $frame) {
		$output = $parser->recursiveTagParse($input, $frame);
		$src = "https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/" . $output . "&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true";
		return "<iframe class=\"wiki-embed\" width=\"560\" height=\"166\" src=\"" . $src . "\" frameborder=\"0\" scrolling=\"no\" allow=\"autoplay\" ></iframe>\n";
	}

	public static function renderTagTwitch($input, array $args, Parser $parser, PPFRame $frame) {
		$output = $parser->recursiveTagParse($input, $frame);
		return "<iframe class=\"wiki-embed\" width=\"560\" height=\"315\" src=\"https://player.twitch.tv/?" . $output . "&parent=clanquest.org\" frameborder=\"0\" scrolling=\"no\" allowfullscreen></iframe>\n";
	}

	public static function renderTagYoutube($input, array $args, Parser $parser, PPFRame $frame) {
		$output = $parser->recursiveTagParse($input, $frame);
		return "<iframe class=\"wiki-embed\" width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/" . $output . "\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" allowfullscreen></iframe>\n";
	}

	public static function renderTagVimeo($input, array $args, Parser $parser, PPFRame $frame) {
		$output = $parser->recursiveTagParse($input, $frame);
		return "<iframe class=\"wiki-embed\" width=\"640\" height=\"357\" src=\"https://player.vimeo.com/video/" . $output . "\" frameborder=\"0\" allowfullscreen></iframe>\n";
	}
}
?>
