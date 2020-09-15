<div class="home-content">
	<div class="main-content">
	<?php
	// handle phpbb permissions
	$phpbb_content_visibility = $phpbb_container->get('content.visibility');
	$ex_fid_ary = array_unique(array_merge(array_keys($auth->acl_getf('!f_read', true)), array_keys($auth->acl_getf('!f_search', true))));
	$not_in_fid = (count($ex_fid_ary)) ? 'WHERE ' . $db->sql_in_set('f.forum_id', $ex_fid_ary, true) . " OR (f.forum_password <> '' AND fa.user_id <> " . (int) $user->data['user_id'] . ')' : "";

	// get forums we have access to
	$sql = "SELECT f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.right_id, f.forum_password, f.forum_flags, fa.user_id
		FROM phpbb_forums f
		LEFT JOIN  phpbb_forums_access fa ON (fa.forum_id = f.forum_id
			AND fa.session_id = '" . $db->sql_escape($user->session_id) . "')
		$not_in_fid
		ORDER BY f.left_id";
	$result = $db->sql_query($sql);

	$right_id = 0;
	$reset_search_forum = true;
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['forum_password'] && $row['user_id'] != $user->data['user_id'])
		{
			$ex_fid_ary[] = (int) $row['forum_id'];
			continue;
		}
	}
	$db->sql_freeresult($result);

	$m_approve_posts_fid_sql = $phpbb_content_visibility->get_global_visibility_sql('post', $ex_fid_ary, 'p.');
	$m_approve_topics_fid_sql = $phpbb_content_visibility->get_global_visibility_sql('topic', $ex_fid_ary, 't.');

	// get unread post ids
	$sql_where = 'AND t.topic_moved_id = 0
					AND ' . $m_approve_topics_fid_sql . '
					' . ((count($ex_fid_ary)) ? 'AND ' . $db->sql_in_set('t.forum_id', $ex_fid_ary, true) : '');

	$unread_ids = get_unread_topics($user->data['user_id'], $sql_where);

	// set up priority forums, then where we post the most
	$priority_forums = [30, 32, 46, 59, 91, 92, 98, 108, 109, 114, 115];
	$sql = 'SELECT COUNT(post_id) AS post_count_in_forum, forum_id 
		FROM phpbb_posts 
		WHERE poster_id = ' . $db->sql_escape($user->data['user_id']) . ' 
		GROUP BY forum_id ORDER BY post_count_in_forum DESC LIMIT 5';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
		$priority_forums[] = $row['forum_id'];
	
	$unread_posts = [];
	$priority_posts = [];
	if (count($unread_ids) > 0) { // if we have unread post ids, gather their post data
		$sql = 'SELECT topic_id, forum_id, topic_title, topic_last_post_time, topic_last_poster_name,
			topic_last_poster_colour, topic_last_post_id, topic_poster 
				FROM phpbb_topics t
				WHERE ' . $db->sql_in_set('topic_id', array_keys($unread_ids)) . ' ORDER BY topic_last_post_time DESC';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			// format our data
			$datetime = new DateTime("now", new DateTimeZone($user->data['user_timezone']));
			$datetime->setTimestamp($row['topic_last_post_time']);
			$forum_post = [
				'topic_id' 				=> 	$row['topic_id'],
				'forum_id' 				=> 	$row['forum_id'],
				'topic_title' 			=> 	$row['topic_title'],
				'last_post_time' 		=> 	$row['topic_last_post_time'],
				'last_poster'	 		=> 	$row['topic_last_poster_name'],
				'last_poster_colour' 	=> 	$row['topic_last_poster_colour'],
				'last_post_id'			=> 	$row['topic_last_post_id'],
				'datetime_formatted'	=> 	$datetime->format($user->data['user_dateformat'])
			];

			// if we started the topic or it's posted in a priority forum, bring it to the front
			if ($row['topic_poster'] == $user->data['user_id'] || in_array($row['forum_id'], $priority_forums))
				$priority_posts[] = $forum_post;
			else // otherwise it's just a normal post
				$unread_posts[] = $forum_post;
		}

		// merge the arrays of priority posts and regular unreads
		$unread_posts = array_merge($priority_posts, $unread_posts);
	}

	if (count($unread_posts) > 0) { // do we have unreads to output?
		$count = 0;
		$display = 10;
		echo '<h2>Unread Posts</h2>';
		echo '<ul>';
		foreach ($unread_posts as $p) {
			echo '<li><a href="/forums/viewtopic.php?f=' . $p['forum_id'] . '&t=' . $p['topic_id'] . '#p' . $p['last_post_id'].'">';
			echo $p['topic_title'];
			echo ' by ' . $p['last_poster'] . ' at ' . $p['datetime_formatted'];
			echo '</a></li>';

			$count++;
			if ($count >= $display)
				break;
		}
		if ($count >= $display)
			echo '<li><a href="/forums/search.php?search_id=unreadposts">' . (count($unread_posts) - $display) . ' more unread posts, continue reading on the forums</a></li>';
		echo '</ul>';
	}
	?>
	<h2>Clan News</h2>
	<?php
	$db->sql_query("SET character_set_results='utf8mb4'"); // set an appropriate charset for pulling emoji
	$result = $db->sql_query('SELECT message, author, embed_href, timestamp FROM cq_announcements ORDER BY timestamp DESC LIMIT 10');
	$dmyDate = 0;
	$news_items = $db->sql_fetchrowset($result);
	$news_item_count = count($news_items);
	$count = 0;
	foreach ($news_items as $news_item)
	{
		$timestamp = (int)$news_item['timestamp'] / 1000; // convert to php format ms instead of microseconds
		$dmyDateTemp = date('dmY', $timestamp); // temporary date for checking if we need <ul> formatting

		// formatted dates for output
		$dmyDateFormatted = date('F j, Y', $timestamp);
		$timeFormatted = date('G:i', $timestamp);

		if ($dmyDateTemp != $dmyDate) {
			// if the new date is different than the old date, export a date header and <ul> tags
			if ($dmyDate != 0)
				echo '</ul>';

			echo '<h3>' . $dmyDateFormatted . '</h3>';

			// if we're not at the last item, start a new <ul>
			if ($count != $news_item_count - 1)
				echo '<ul>';

			$dmyDate = $dmyDateTemp;
		}

		// use the phpbb message parser to convert links and emojis
		$bbcode_post = $news_item['message'];
		$poll = $uid = $bitfield = $options = '';
		generate_text_for_storage($bbcode_post, $uid, $bitfield, $options, true, true, true);
		$parsed_post = generate_text_for_display($bbcode_post, $uid, $bitfield, $options);

		// basic bold italic markdown conversion for _ and * formatting
		preg_match_all('/([*_]+)((?:(?!\1).)+)\1/', $parsed_post, $matches, PREG_SET_ORDER);

		foreach($matches as $set)
		{
			//echo 'Match set: ' . $set[0] . ' ' . $set[1] . ' ' . $set[2] . '<br>';
			if ($set[1] == '*')
				$parsed_post = str_replace($set[0], '<em>' . $set[2] . '</em>', $parsed_post);
			if ($set[1] == '**')
				$parsed_post = str_replace($set[0], '<strong>' . $set[2] . '</strong>', $parsed_post);
			if ($set[1] == '***')
				$parsed_post = str_replace($set[0], '<strong><em>' . $set[2] . '</em></strong>', $parsed_post);
			
			if ($set[1] == '___')
				$parsed_post = str_replace($set[0], '<em><u>' . $set[2] . '</em></u>', $parsed_post);
			if ($set[1] == '__')
				$parsed_post = str_replace($set[0], '<u>' . $set[2] . '</u>', $parsed_post);
			if ($set[1] == '_')
				$parsed_post = str_replace($set[0], '<em>' . $set[2] . '</em>', $parsed_post);
		}

		echo '<li>';
		echo $parsed_post;
		echo !empty($news_item['embed_href']) ? ' [<a href="' . $news_item['embed_href'] . '">read more</a>]' : '';
		echo '<br>Posted on ' . $dmyDateFormatted . ' at ' . $timeFormatted . ' UTC by ' . $news_item['author'];
		echo '</li>';
		$count++;
	}
	?>
	</div>
	<aside class="side-widgets cq-info-box">
		<h2>Upcoming Events</h2>
		<iframe
			src="https://calendar.google.com/calendar/embed?title=Clan%20Quest%20Events&amp;showTitle=0&amp;showNav=0&amp;showTabs=0&amp;showPrint=0&amp;showCalendars=0&amp;mode=AGENDA&amp;height=600&amp;wkst=1&amp;bgcolor=%23ffffff&amp;src=clanquest.org_47b3e6k7791rj8al8mr18iujm0%40group.calendar.google.com&amp;color=%235F6B02&amp;src=clanquest.org_egecc810jrhbl9p7au75rvd49c%40group.calendar.google.com&amp;color=%2342104A&amp;src=clanquest.org_ecai4tp12r30p792i7tghbghos%40group.calendar.google.com&amp;color=%2328754E&amp;src=clanquest.org_iikv32sksnohap5iscjut6fi98%40group.calendar.google.com&amp;color=%23B1440E&amp;src=clanquest.org_cnjh5q7ci6fqic3tnap4r7asr4%40group.calendar.google.com&amp;color=%23711616&amp;src=clanquest.org_s9393s458a7ffaps1deoft1pck%40group.calendar.google.com&amp;color=%23B1440E&amp;src=clanquest.org_bu3rhro5uufh6amfg5fd23nof8%40group.calendar.google.com&amp;color=%23711616&amp;ctz=UTC"
			frameborder="0"
			scrolling="no"
			class="google-calendar-embed"></iframe>
		<h2>Social Media</h2>
		<a class="twitter-timeline" data-width="350" data-height="400" data-theme="light" href="https://twitter.com/ClanQuest?ref_src=twsrc%5Etfw">&nbsp;</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
	</aside>
</div>
