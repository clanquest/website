<div class="home-content">
	<div class="main-content" id="hub">
		<?php
		$wiki_contents = file_get_contents('https://clanquest.org/wiki/api.php?action=parse&page=RuneFest_2018/News&prop=text&disableeditsection=true&format=json');
		$json = json_decode($wiki_contents);
		$news = $json->parse->text->{'*'};

		if (!empty($news)) {
			echo '<h2>Announcements at RuneFest</h2>';
			echo '<div class="wiki-include">';
			echo $news;
			echo '</div>';
		}
		?>
		
		<div id="tweets">
			<div class="tweet-container" style="margin-right: 10px;">
				<h2>Clan Quest @ RuneFest</h2>
				<a class="twitter-timeline" data-lang="en" data-height="800" data-dnt="true" data-theme="dark" data-chrome="transparent nofooter noheader noborders" href="https://twitter.com/ClanQuest/lists/clan-quest-runefest?ref_src=twsrc%5Etfw">A Twitter List by ClanQuest</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
			</div>
			<div class="tweet-container" style="margin-left: 10px;">
				<h2>RuneFest Top Tweets</h2>
				<a class="twitter-timeline" data-lang="en" data-height="800" data-dnt="true" data-theme="dark" data-chrome="transparent nofooter noheader noborders" href="https://twitter.com/ClanQuest/timelines/1044695384084242433?ref_src=twsrc%5Etfw">RuneFest Showcase - Curated tweets by ClanQuest</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
			</div>
		</div>
	</div>
</div>