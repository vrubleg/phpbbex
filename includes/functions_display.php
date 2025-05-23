<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Display Forums
*/
function display_forums($root_data = '', $display_moderators = true, $return_moderators = false)
{
	global $db, $auth, $user, $template;
	global $config;

	$forum_rows = $subforums = $forum_ids = $forum_ids_moderator = $forum_moderators = $active_forum_ary = array();
	$parent_id = $visible_forums = 0;
	$sql_from = '';

	// Mark forums read?
	$mark_read = request_var('mark', '');

	if ($mark_read == 'all')
	{
		$mark_read = '';
	}

	if (!$root_data)
	{
		if ($mark_read == 'forums')
		{
			$mark_read = 'all';
		}

		$root_data = array('forum_id' => 0);
		$sql_where = '';
	}
	else
	{
		$sql_where = 'left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'];
	}

	// Handle marking everything read
	if ($mark_read == 'all')
	{
		$redirect = build_url(array('mark', 'hash'));
		meta_refresh(3, $redirect);

		if (check_link_hash(request_var('hash', ''), 'global'))
		{
			markread('all');

			if (!empty($config['skip_typical_notices']))
			{
				redirect($redirect);
			}

			trigger_error(
				$user->lang['FORUMS_MARKED'] . '<br /><br />' .
				sprintf($user->lang['RETURN_INDEX'], '<a href="' . $redirect . '">', '</a>')
			);
		}
		else
		{
			trigger_error(sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
		}
	}

	// Display list of active topics for this category?
	$show_active = (isset($root_data['forum_flags']) && ($root_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS)) ? true : false;

	$sql_array = array(
		'SELECT'	=> 'f.*',
		'FROM'		=> array(
			FORUMS_TABLE		=> 'f'
		),
		'LEFT_JOIN'	=> array(),
	);

	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		$sql_array['LEFT_JOIN'][] = array('FROM' => array(FORUMS_TRACK_TABLE => 'ft'), 'ON' => 'ft.user_id = ' . $user->data['user_id'] . ' AND ft.forum_id = f.forum_id');
		$sql_array['SELECT'] .= ', ft.mark_time';
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		$tracking_topics = get_cookie('track', '');
		$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

		if (!$user->data['is_registered'])
		{
			$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
		}
	}

	if ($show_active)
	{
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(FORUMS_ACCESS_TABLE => 'fa'),
			'ON'	=> "fa.forum_id = f.forum_id AND fa.session_id = '" . $db->sql_escape($user->session_id) . "'"
		);

		$sql_array['SELECT'] .= ', fa.user_id';
	}

	$sql = $db->sql_build_query('SELECT', array(
		'SELECT'	=> $sql_array['SELECT'],
		'FROM'		=> $sql_array['FROM'],
		'LEFT_JOIN'	=> $sql_array['LEFT_JOIN'],

		'WHERE'		=> $sql_where,

		'ORDER_BY'	=> 'f.left_id',
	));

	$result = $db->sql_query($sql);

	$forum_tracking_info = array();
	$branch_root_id = $root_data['forum_id'];

	// Check for unread global announcements (index page only)
	$ga_unread = false;
	if ($root_data['forum_id'] == 0)
	{
		$unread_ga_list = get_unread_topics($user->data['user_id'], 'AND t.forum_id = 0', '', 1);

		if (!empty($unread_ga_list))
		{
			$ga_unread = true;
		}
	}

	while ($row = $db->sql_fetchrow($result))
	{
		$forum_id = $row['forum_id'];

		// Mark forums read?
		if ($mark_read == 'forums')
		{
			if ($auth->acl_get('f_list', $forum_id))
			{
				$forum_ids[] = $forum_id;
			}

			continue;
		}

		// Category with no members
		if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
		{
			continue;
		}

		// Skip branch
		if (isset($right_id))
		{
			if ($row['left_id'] < $right_id)
			{
				continue;
			}
			unset($right_id);
		}

		if (!$auth->acl_get('f_list', $forum_id))
		{
			// if the user does not have permissions to list this forum, skip everything until next branch
			$right_id = $row['right_id'];
			continue;
		}

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$forum_tracking_info[$forum_id] = (!empty($row['mark_time'])) ? $row['mark_time'] : $user->data['user_lastmark'];
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			if (!$user->data['is_registered'])
			{
				$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
			}
			$forum_tracking_info[$forum_id] = (isset($tracking_topics['f'][$forum_id])) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate']) : $user->data['user_lastmark'];
		}

		// Count the difference of real to public topics, so we can display an information to moderators
		$row['forum_id_unapproved_topics'] = ($auth->acl_get('m_approve', $forum_id) && ($row['forum_topics_real'] != $row['forum_topics'])) ? $forum_id : 0;
		$row['forum_topics'] = ($auth->acl_get('m_approve', $forum_id)) ? $row['forum_topics_real'] : $row['forum_topics'];

		// Display active topics from this forum?
		if ($show_active && $row['forum_type'] == FORUM_POST && $auth->acl_get('f_read', $forum_id) && ($row['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS))
		{
			if (!isset($active_forum_ary['forum_topics']))
			{
				$active_forum_ary['forum_topics'] = 0;
			}

			if (!isset($active_forum_ary['forum_posts']))
			{
				$active_forum_ary['forum_posts'] = 0;
			}

			$active_forum_ary['forum_id'][]		= $forum_id;
			$active_forum_ary['forum_topics']	+= $row['forum_topics'];
			$active_forum_ary['forum_posts']	+= $row['forum_posts'];

			// If this is a passworded forum we do not show active topics from it if the user is not authorised to view it...
			if ($row['forum_password'] && $row['user_id'] != $user->data['user_id'])
			{
				$active_forum_ary['exclude_forum_id'][] = $forum_id;
			}
		}

		//
		if ($row['parent_id'] == $root_data['forum_id'] || $row['parent_id'] == $branch_root_id)
		{
			if ($row['forum_type'] != FORUM_CAT)
			{
				$forum_ids_moderator[] = (int) $forum_id;
			}

			// Direct child of current branch
			$parent_id = $forum_id;
			$forum_rows[$forum_id] = $row;

			if ($row['forum_type'] == FORUM_CAT && $row['parent_id'] == $root_data['forum_id'])
			{
				$branch_root_id = $forum_id;
			}
			$forum_rows[$parent_id]['forum_id_last_post'] = $row['forum_id'];
			$forum_rows[$parent_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
		}
		else if ($row['forum_type'] != FORUM_CAT)
		{
			$subforums[$parent_id][$forum_id]['display'] = ($row['display_on_index']) ? true : false;
			$subforums[$parent_id][$forum_id]['name'] = $row['forum_name'];
			$subforums[$parent_id][$forum_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
			$subforums[$parent_id][$forum_id]['children'] = array();

			if (isset($subforums[$parent_id][$row['parent_id']]) && !$row['display_on_index'])
			{
				$subforums[$parent_id][$row['parent_id']]['children'][] = $forum_id;
			}

			if (!$forum_rows[$parent_id]['forum_id_unapproved_topics'] && $row['forum_id_unapproved_topics'])
			{
				$forum_rows[$parent_id]['forum_id_unapproved_topics'] = $forum_id;
			}

			$forum_rows[$parent_id]['forum_topics'] += $row['forum_topics'];

			// Do not list redirects in LINK Forums as Posts.
			if ($row['forum_type'] != FORUM_LINK)
			{
				$forum_rows[$parent_id]['forum_posts'] += $row['forum_posts'];
			}

			if ($row['forum_last_post_time'] > $forum_rows[$parent_id]['forum_last_post_time'])
			{
				$forum_rows[$parent_id]['forum_last_post_id'] = $row['forum_last_post_id'];
				$forum_rows[$parent_id]['forum_last_post_subject'] = $row['forum_last_post_subject'];
				$forum_rows[$parent_id]['forum_last_post_time'] = $row['forum_last_post_time'];
				$forum_rows[$parent_id]['forum_last_poster_id'] = $row['forum_last_poster_id'];
				$forum_rows[$parent_id]['forum_last_poster_name'] = $row['forum_last_poster_name'];
				$forum_rows[$parent_id]['forum_last_poster_colour'] = $row['forum_last_poster_colour'];
				$forum_rows[$parent_id]['forum_id_last_post'] = $forum_id;
			}
		}
	}
	$db->sql_freeresult($result);

	// Handle marking posts
	if ($mark_read == 'forums')
	{
		$redirect = build_url(array('mark', 'hash'));
		$token = request_var('hash', '');
		if (check_link_hash($token, 'global'))
		{
			// Add 0 to forums array to mark global announcements correctly
			$forum_ids[] = 0;
			markread('topics', $forum_ids);

			if (!empty($config['skip_typical_notices']))
			{
				redirect($redirect);
			}

			$message = sprintf($user->lang['RETURN_FORUM'], '<a href="' . $redirect . '">', '</a>');
			meta_refresh(3, $redirect);
			trigger_error($user->lang['FORUMS_MARKED'] . '<br /><br />' . $message);
		}
		else
		{
			$message = sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>');
			meta_refresh(3, $redirect);
			trigger_error($message);
		}

	}

	// Grab moderators ... if necessary
	if ($display_moderators)
	{
		if ($return_moderators)
		{
			$forum_ids_moderator[] = $root_data['forum_id'];
		}
		get_moderators($forum_moderators, $forum_ids_moderator);
	}

	// Used to tell whatever we have to create a dummy category or not.
	$last_catless = true;
	$forums_last_posts_ids_list = array();
	foreach ($forum_rows as $row)
	{
		// Empty category
		if ($row['parent_id'] == $root_data['forum_id'] && $row['forum_type'] == FORUM_CAT)
		{
			$template->assign_block_vars('forumrow', array(
				'S_IS_CAT'				=> true,
				'FORUM_ID'				=> $row['forum_id'],
				'FORUM_NAME'			=> $row['forum_name'],
				'FORUM_DESC'			=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
				'FORUM_FOLDER_IMG'		=> '',
				'FORUM_FOLDER_IMG_SRC'	=> '',
				'FORUM_IMAGE'			=> ($row['forum_image']) ? '<img src="' . PHPBB_ROOT_PATH . $row['forum_image'] . '" alt="' . $user->lang['FORUM_CAT'] . '" />' : '',
				'FORUM_IMAGE_SRC'		=> ($row['forum_image']) ? PHPBB_ROOT_PATH . $row['forum_image'] : '',
				'U_VIEWFORUM'			=> append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'f=' . $row['forum_id']))
			);

			continue;
		}

		$visible_forums++;
		$forum_id = $row['forum_id'];

		$forum_unread = (isset($forum_tracking_info[$forum_id]) && $row['orig_forum_last_post_time'] > $forum_tracking_info[$forum_id]) ? true : false;

		// Mark the first visible forum on index as unread if there's any unread global announcement
		if ($ga_unread && !empty($forum_ids_moderator) && $forum_id == $forum_ids_moderator[0])
		{
			$forum_unread = true;
		}

		$folder_image = $folder_alt = $l_subforums = '';
		$subforums_list = array();

		// Generate list of subforums if we need to
		if (isset($subforums[$forum_id]))
		{
			foreach ($subforums[$forum_id] as $subforum_id => $subforum_row)
			{
				$subforum_unread = (isset($forum_tracking_info[$subforum_id]) && $subforum_row['orig_forum_last_post_time'] > $forum_tracking_info[$subforum_id]) ? true : false;

				if (!$subforum_unread && !empty($subforum_row['children']))
				{
					foreach ($subforum_row['children'] as $child_id)
					{
						if (isset($forum_tracking_info[$child_id]) && $subforums[$forum_id][$child_id]['orig_forum_last_post_time'] > $forum_tracking_info[$child_id])
						{
							// Once we found an unread child forum, we can drop out of this loop
							$subforum_unread = true;
							break;
						}
					}
				}

				if ($subforum_row['display'] && $subforum_row['name'])
				{
					$subforums_list[] = array(
						'link'		=> append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'f=' . $subforum_id),
						'name'		=> $subforum_row['name'],
						'unread'	=> $subforum_unread,
					);
				}
				else
				{
					unset($subforums[$forum_id][$subforum_id]);
				}

				// If one subforum is unread the forum gets unread too...
				if ($subforum_unread)
				{
					$forum_unread = true;
				}
			}

			$l_subforums = (sizeof($subforums[$forum_id]) == 1) ? $user->lang['SUBFORUM'] . ': ' : $user->lang['SUBFORUMS'] . ': ';
			$folder_image = ($forum_unread) ? 'forum_unread_subforum' : 'forum_read_subforum';
		}
		else
		{
			switch ($row['forum_type'])
			{
				case FORUM_POST:
					$folder_image = ($forum_unread) ? 'forum_unread' : 'forum_read';
				break;

				case FORUM_LINK:
					$folder_image = 'forum_link';
				break;
			}
		}

		// Which folder should we display?
		if ($row['forum_status'] == ITEM_LOCKED)
		{
			$folder_image = ($forum_unread) ? 'forum_unread_locked' : 'forum_read_locked';
			$folder_alt = 'FORUM_LOCKED';
		}
		else
		{
			$folder_alt = ($forum_unread) ? 'UNREAD_POSTS' : 'NO_UNREAD_POSTS';
		}

		// Create last post link information, if appropriate
		if ($row['forum_last_post_id'])
		{
			$forums_last_posts_ids_list[$row['forum_id']] = $row['forum_last_post_id'];
			$last_post_id = $row['forum_last_post_id'];
			$last_post_subject = $row['forum_last_post_subject'];
			$last_post_time = $user->format_date($row['forum_last_post_time']);
			$last_post_url = append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', 'p=' . $row['forum_last_post_id']) . '#p' . $row['forum_last_post_id'];
		}
		else
		{
			$last_post_subject = $last_post_time = $last_post_url = '';
			$last_post_id = false;
		}

		// Output moderator listing ... if applicable
		$l_moderator = $moderators_list = '';
		if ($display_moderators && !empty($forum_moderators[$forum_id]))
		{
			$l_moderator = (sizeof($forum_moderators[$forum_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode(', ', $forum_moderators[$forum_id]);
		}

		$l_post_click_count = ($row['forum_type'] == FORUM_LINK) ? 'CLICKS' : 'POSTS';
		$post_click_count = ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & FORUM_FLAG_LINK_TRACK) ? $row['forum_posts'] : '';

		$s_subforums_list = array();
		foreach ($subforums_list as $subforum)
		{
			$s_subforums_list[] = '<a href="' . $subforum['link'] . '" class="subforum ' . (($subforum['unread']) ? 'unread' : 'read') . '" title="' . (($subforum['unread']) ? $user->lang['UNREAD_POSTS'] : $user->lang['NO_UNREAD_POSTS']) . '">' . $subforum['name'] . '</a>';
		}
		$s_subforums_list = (string) implode(' ', $s_subforums_list);
		$catless = ($row['parent_id'] == $root_data['forum_id']) ? true : false;

		if ($row['forum_type'] != FORUM_LINK)
		{
			$u_viewforum = append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'f=' . $row['forum_id']);
		}
		else
		{
			// If the forum is a link and we count redirects we need to visit it
			// If the forum is having a password or no read access we do not expose the link, but instead handle it in viewforum
			if (($row['forum_flags'] & FORUM_FLAG_LINK_TRACK) || $row['forum_password'] || !$auth->acl_get('f_read', $forum_id))
			{
				$u_viewforum = append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'f=' . $row['forum_id']);
			}
			else
			{
				$u_viewforum = $row['forum_link'];
			}
		}

		$template->assign_block_vars('forumrow', array(
			'S_IS_CAT'			=> false,
			'S_NO_CAT'			=> $catless && !$last_catless,
			'S_IS_LINK'			=> ($row['forum_type'] == FORUM_LINK) ? true : false,
			'S_UNREAD_FORUM'	=> $forum_unread,
			'S_AUTH_READ'		=> $auth->acl_get('f_read', $row['forum_id']),
			'S_LOCKED_FORUM'	=> ($row['forum_status'] == ITEM_LOCKED) ? true : false,
			'S_LIST_SUBFORUMS'	=> ($row['display_subforum_list']) ? true : false,
			'S_SUBFORUMS'		=> (sizeof($subforums_list)) ? true : false,
			'S_FEED_ENABLED'	=> ($config['feed_forum'] && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $row['forum_options']) && $row['forum_type'] == FORUM_POST) ? true : false,

			'FORUM_ID'				=> $row['forum_id'],
			'FORUM_NAME'			=> $row['forum_name'],
			'FORUM_DESC'			=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
			'TOPICS'				=> $row['forum_topics'],
			$l_post_click_count		=> $post_click_count,
			'FORUM_FOLDER_IMG'		=> $user->img($folder_image, $folder_alt),
			'FORUM_FOLDER_IMG_SRC'	=> $user->img($folder_image, $folder_alt, false, '', 'src'),
			'FORUM_FOLDER_IMG_ALT'	=> isset($user->lang[$folder_alt]) ? $user->lang[$folder_alt] : '',
			'FORUM_IMAGE'			=> ($row['forum_image']) ? '<img src="' . PHPBB_ROOT_PATH . $row['forum_image'] . '" alt="' . $user->lang[$folder_alt] . '" />' : '',
			'FORUM_IMAGE_SRC'		=> ($row['forum_image']) ? PHPBB_ROOT_PATH . $row['forum_image'] : '',
			'LAST_POST_ID'			=> $last_post_id,
			'LAST_POST_SUBJECT'		=> censor_text($last_post_subject),
			'LAST_POST_TIME'		=> $last_post_time,
			'LAST_POSTER'			=> get_username_string('username', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'LAST_POSTER_COLOUR'	=> get_username_string('colour', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'LAST_POSTER_FULL'		=> get_username_string('full', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'MODERATORS'			=> $moderators_list,
			'SUBFORUMS'				=> $s_subforums_list,

			'L_SUBFORUM_STR'		=> $l_subforums,
			'L_MODERATOR_STR'		=> $l_moderator,

			'U_UNAPPROVED_TOPICS'	=> ($row['forum_id_unapproved_topics']) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=queue&amp;mode=unapproved_topics&amp;f=' . $row['forum_id_unapproved_topics']) : '',
			'U_VIEWFORUM'		=> $u_viewforum,
			'U_LAST_POSTER'		=> get_username_string('profile', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'U_LAST_POST'		=> $last_post_url)
		);

		// Assign subforums loop for style authors
		foreach ($subforums_list as $subforum)
		{
			$template->assign_block_vars('forumrow.subforum', array(
				'U_SUBFORUM'	=> $subforum['link'],
				'SUBFORUM_NAME'	=> $subforum['name'],
				'S_UNREAD'		=> $subforum['unread'])
			);
		}

		$last_catless = $catless;
	}

	$template->assign_vars(array(
		'U_MARK_FORUMS'		=> ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'hash=' . generate_link_hash('global') . '&amp;f=' . $root_data['forum_id'] . '&amp;mark=forums') : '',
		'S_HAS_SUBFORUM'	=> ($visible_forums) ? true : false,
		'L_SUBFORUM'		=> ($visible_forums == 1) ? $user->lang['SUBFORUM'] : $user->lang['SUBFORUMS'],
		'LAST_POST_IMG'		=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
		'UNAPPROVED_IMG'	=> $user->img('icon_topic_unapproved', 'TOPICS_UNAPPROVED'),

		// Search in current forum
		'U_SEARCH_IN'				=> append_sid(PHPBB_ROOT_PATH . 'search.php', 'fid[]=' . $root_data['forum_id']),
		'U_SEARCH_SELF_IN'			=> append_sid(PHPBB_ROOT_PATH . 'search.php', 'search_id=egosearch&amp;fid[]=' . $root_data['forum_id']),
		'U_SEARCH_SELF_TOPICS_IN'	=> append_sid(PHPBB_ROOT_PATH . 'search.php', 'search_id=egosearch&amp;sf=firstpost&amp;fid[]=' . $root_data['forum_id']),
		'U_SEARCH_NEW_IN'			=> append_sid(PHPBB_ROOT_PATH . 'search.php', 'search_id=newposts&amp;fid[]=' . $root_data['forum_id']),
		'U_SEARCH_UNANSWERED_IN'	=> append_sid(PHPBB_ROOT_PATH . 'search.php', 'search_id=unanswered&amp;fid[]=' . $root_data['forum_id']),
		'U_SEARCH_UNREAD_IN'		=> append_sid(PHPBB_ROOT_PATH . 'search.php', 'search_id=unreadposts&amp;fid[]=' . $root_data['forum_id']),
		'U_SEARCH_ACTIVE_TOPICS_IN'	=> append_sid(PHPBB_ROOT_PATH . 'search.php', 'search_id=active_topics&amp;fid[]=' . $root_data['forum_id']),
	));

	if(!empty($forums_last_posts_ids_list))
	{
		$sql = 'SELECT topic_id, topic_title, topic_last_post_id, forum_id FROM ' . TOPICS_TABLE . ' WHERE topic_last_post_id IN (' . implode(',', $forums_last_posts_ids_list) . ')';
		$last_topic_result = $db->sql_query($sql);
		while($last_topic_row = $db->sql_fetchrow($last_topic_result))
		{
			$last_topic_title_full = htmlspecialchars_decode(censor_text($last_topic_row['topic_title']));
			$last_topic_title = (utf8_strlen($last_topic_title_full) > 40) ? trim(utf8_substr($last_topic_title_full, 0, 40)) . '…' : $last_topic_title_full;
			$last_topic_url = append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', 't=' . $last_topic_row['topic_id']);

			$template->alter_block_array('forumrow', array(
					'LAST_TOPIC_TITLE'		=> htmlspecialchars($last_topic_title),
					'LAST_TOPIC_TITLE_FULL'	=> htmlspecialchars($last_topic_title_full),
					'U_LAST_TOPIC_URL'		=> $last_topic_url
			), array('LAST_POST_ID'	=> $last_topic_row['topic_last_post_id']), 'change');
		}
		$db->sql_freeresult($last_topic_result);
	}

	if ($return_moderators)
	{
		return array($active_forum_ary, $forum_moderators);
	}

	return array($active_forum_ary, array());
}

/**
* Create forum rules for given forum
*/
function generate_forum_rules(&$forum_data)
{
	if (!$forum_data['forum_rules'] && !$forum_data['forum_rules_link'])
	{
		return;
	}

	global $template;

	if ($forum_data['forum_rules'])
	{
		$forum_data['forum_rules'] = generate_text_for_display($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_bitfield'], $forum_data['forum_rules_options']);
	}

	$template->assign_vars(array(
		'S_FORUM_RULES'	=> true,
		'U_FORUM_RULES'	=> $forum_data['forum_rules_link'],
		'FORUM_RULES'	=> $forum_data['forum_rules'])
	);
}

/**
* Create forum navigation links for given forum, create parent
* list if currently null, assign basic forum info to template
*/
function generate_forum_nav(&$forum_data)
{
	global $db, $user, $template, $auth, $config;

	if (!$auth->acl_get('f_list', $forum_data['forum_id']))
	{
		return;
	}

	// Get forum parents
	$forum_parents = get_forum_parents($forum_data);

	// Build navigation links
	if (!empty($forum_parents))
	{
		foreach ($forum_parents as $parent_forum_id => $parent_data)
		{
			list($parent_name, $parent_type) = array_values($parent_data);

			// Skip this parent if the user does not have the permission to view it
			if (!$auth->acl_get('f_list', $parent_forum_id))
			{
				continue;
			}

			$template->assign_block_vars('navlinks', array(
				'S_IS_CAT'		=> ($parent_type == FORUM_CAT) ? true : false,
				'S_IS_LINK'		=> ($parent_type == FORUM_LINK) ? true : false,
				'S_IS_POST'		=> ($parent_type == FORUM_POST) ? true : false,
				'FORUM_NAME'	=> $parent_name,
				'FORUM_ID'		=> $parent_forum_id,
				'U_VIEW_FORUM'	=> append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'f=' . $parent_forum_id))
			);
		}
	}

	$template->assign_block_vars('navlinks', array(
		'S_IS_CAT'		=> ($forum_data['forum_type'] == FORUM_CAT) ? true : false,
		'S_IS_LINK'		=> ($forum_data['forum_type'] == FORUM_LINK) ? true : false,
		'S_IS_POST'		=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_ID'		=> $forum_data['forum_id'],
		'U_VIEW_FORUM'	=> append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'f=' . $forum_data['forum_id']))
	);

	$template->assign_vars(array(
		'FORUM_ID' 		=> $forum_data['forum_id'],
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_DESC'	=> generate_text_for_display($forum_data['forum_desc'], $forum_data['forum_desc_uid'], $forum_data['forum_desc_bitfield'], $forum_data['forum_desc_options']),

		'S_ENABLE_FEEDS_FORUM'	=> ($config['feed_forum'] && $forum_data['forum_type'] == FORUM_POST && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $forum_data['forum_options'])) ? true : false,
	));

	return;
}

/**
* Returns forum parents as an array. Get them from forum_data if available, or update the database otherwise
*/
function get_forum_parents(&$forum_data)
{
	global $db;

	$forum_parents = array();

	if ($forum_data['parent_id'] > 0)
	{
		if ($forum_data['forum_parents'] == '')
		{
			$sql = 'SELECT forum_id, forum_name, forum_type
				FROM ' . FORUMS_TABLE . '
				WHERE left_id < ' . $forum_data['left_id'] . '
					AND right_id > ' . $forum_data['right_id'] . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$forum_parents[$row['forum_id']] = array($row['forum_name'], (int) $row['forum_type']);
			}
			$db->sql_freeresult($result);

			$forum_data['forum_parents'] = serialize($forum_parents);

			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET forum_parents = '" . $db->sql_escape($forum_data['forum_parents']) . "'
				WHERE parent_id = " . $forum_data['parent_id'];
			$db->sql_query($sql);
		}
		else
		{
			$forum_parents = unserialize($forum_data['forum_parents']);
		}
	}

	return $forum_parents;
}

/**
* Generate topic pagination
*/
function topic_generate_pagination($replies, $url)
{
	global $config, $user;

	// Make sure $per_page is a valid value
	$per_page = ($config['posts_per_page'] <= 0) ? 1 : $config['posts_per_page'];

	if (($replies + 1) > $per_page)
	{
		$total_pages = ceil(($replies + 1) / $per_page);
		$pagination = '';

		$times = 1;
		for ($j = 0; $j < $replies + 1; $j += $per_page)
		{
			$pagination .= '<a href="' . $url . ($j == 0 ? '' : '&amp;start=' . $j) . '">' . $times . '</a>';
			if ($times == 1 && $total_pages > 5)
			{
				$pagination .= '<span class="page-dots"> … </span>';

				// Display the last three pages
				$times = $total_pages - 3;
				$j += ($total_pages - 4) * $per_page;
			}
			else if ($times < $total_pages)
			{
				$pagination .= '<span class="page-sep">' . $user->lang['COMMA_SEPARATOR'] . '</span>';
			}
			$times++;
		}
	}
	else
	{
		$pagination = '';
	}

	return $pagination;
}

/**
* Obtain list of moderators of each forum
*/
function get_moderators(&$forum_moderators, $forum_id = false)
{
	global $config, $template, $db, $user, $auth;

	$forum_id_ary = array();

	if ($forum_id !== false)
	{
		if (!is_array($forum_id))
		{
			$forum_id = array($forum_id);
		}

		// Exchange key/value pair to be able to faster check for the forum id existence
		$forum_id_ary = array_flip($forum_id);
	}

	$sql_array = array(
		'SELECT'	=> 'm.*, u.user_colour, g.group_colour, g.group_type',

		'FROM'		=> array(
			MODERATOR_CACHE_TABLE	=> 'm',
		),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 'm.user_id = u.user_id',
			),
			array(
				'FROM'	=> array(GROUPS_TABLE => 'g'),
				'ON'	=> 'm.group_id = g.group_id',
			),
		),

		'WHERE'		=> 'm.display_on_index = 1',
	);

	// We query every forum here because for caching we should not have any parameter.
	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql, 3600);

	while ($row = $db->sql_fetchrow($result))
	{
		$f_id = (int) $row['forum_id'];

		if (!isset($forum_id_ary[$f_id]))
		{
			continue;
		}

		if (!empty($row['user_id']))
		{
			$forum_moderators[$f_id][] = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		}
		else
		{
			$group_name = (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']);

			if ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile'))
			{
				$forum_moderators[$f_id][] = '<span' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . '>' . $group_name . '</span>';
			}
			else
			{
				$forum_moderators[$f_id][] = '<a' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . ' href="' . append_sid(PHPBB_ROOT_PATH . 'memberlist.php', 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
			}
		}
	}
	$db->sql_freeresult($result);

	return;
}

/**
* User authorisation levels output
*
* @param	string	$mode			Can be forum or topic. Not in use at the moment.
* @param	int		$forum_id		The current forum the user is in.
* @param	int		$forum_status	The forums status bit.
*/
function gen_forum_auth_level($mode, $forum_id, $forum_status)
{
	global $template, $auth, $user, $config;

	$locked = ($forum_status == ITEM_LOCKED && !$auth->acl_get('m_edit', $forum_id)) ? true : false;

	$rules = array(
		($auth->acl_get('f_post', $forum_id) && !$locked) ? $user->lang['RULES_POST_CAN'] : $user->lang['RULES_POST_CANNOT'],
		($auth->acl_get('f_reply', $forum_id) && !$locked) ? $user->lang['RULES_REPLY_CAN'] : $user->lang['RULES_REPLY_CANNOT'],
		($user->data['is_registered'] && $auth->acl_gets('f_edit', 'm_edit', $forum_id) && !$locked) ? $user->lang['RULES_EDIT_CAN'] : $user->lang['RULES_EDIT_CANNOT'],
		($user->data['is_registered'] && $auth->acl_gets('f_delete', 'm_delete', $forum_id) && !$locked) ? $user->lang['RULES_DELETE_CAN'] : $user->lang['RULES_DELETE_CANNOT'],
	);

	if ($config['allow_attachments'])
	{
		$rules[] = ($auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach') && !$locked) ? $user->lang['RULES_ATTACH_CAN'] : $user->lang['RULES_ATTACH_CANNOT'];
	}

	foreach ($rules as $rule)
	{
		$template->assign_block_vars('rules', array('RULE' => $rule));
	}

	return;
}

/**
* Generate topic status
*/
function topic_status(&$topic_row, $replies, $unread_topic, &$folder_img, &$folder_alt, &$topic_type)
{
	global $user, $config;

	$folder = $folder_new = '';

	if ($topic_row['topic_status'] == ITEM_MOVED)
	{
		$topic_type = $user->lang['VIEW_TOPIC_MOVED'];
		$folder_img = 'topic_moved';
		$folder_alt = 'TOPIC_MOVED';
	}
	else
	{
		switch ($topic_row['topic_type'])
		{
			case POST_GLOBAL:
				$topic_type = $user->lang['VIEW_TOPIC_GLOBAL'];
				$folder = 'global_read';
				$folder_new = 'global_unread';
			break;

			case POST_ANNOUNCE:
				$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
				$folder = 'announce_read';
				$folder_new = 'announce_unread';
			break;

			case POST_STICKY:
				$topic_type = $user->lang['VIEW_TOPIC_STICKY'];
				$folder = 'sticky_read';
				$folder_new = 'sticky_unread';
			break;

			default:
				$topic_type = '';
				$folder = 'topic_read';
				$folder_new = 'topic_unread';
			break;
		}

		if ($topic_row['topic_status'] == ITEM_LOCKED)
		{
			$topic_type = $user->lang['VIEW_TOPIC_LOCKED'];
			$folder .= '_locked';
			$folder_new .= '_locked';
		}


		$folder_img = ($unread_topic) ? $folder_new : $folder;
		$folder_alt = ($unread_topic) ? 'UNREAD_POSTS' : (($topic_row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_UNREAD_POSTS');

		// Posted image?
		if (!empty($topic_row['topic_posted']) && $topic_row['topic_posted'])
		{
			$folder_img .= '_mine';
		}
	}

	if ($topic_row['poll_start'] && $topic_row['topic_status'] != ITEM_MOVED)
	{
		$topic_type = $user->lang['VIEW_TOPIC_POLL'];
	}
}

/**
* Display topic rows
*/
function display_topic_rows($tpl_loopname, $topic_ids)
{
	global $auth, $cache, $config, $db, $template, $user;

	// No topics to display
	if (empty($topic_ids))
	{
		return;
	}

	// Grab icons
	$icons = $cache->obtain_icons();

	// Now only pull the data of the requested topics
	$sql = 'SELECT t.*, i.icons_url, i.icons_width, i.icons_height, tp.topic_posted, f.forum_name
		FROM ' . TOPICS_TABLE . ' t
		LEFT JOIN ' . TOPICS_POSTED_TABLE . ' tp
			ON (t.topic_id = tp.topic_id
				AND tp.user_id = ' . $user->data['user_id'] . ')
		LEFT JOIN ' . FORUMS_TABLE . ' f
			ON f.forum_id = t.forum_id
		LEFT JOIN ' . ICONS_TABLE . ' i
			ON t.icon_id = i.icons_id
		WHERE ' . $db->sql_in_set('t.topic_id', $topic_ids);
	$result = $db->sql_query($sql);

	$forums = array();
	$topic_rows = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$topic_id = $row['topic_id'];
		$forum_id = $row['forum_id'];
		$topic_rows[$topic_id] = $row;
		$forums[$forum_id][] = $topic_id;
	}
	$db->sql_freeresult($result);

	foreach ($forums as $forum_id => $forum_topic_ids)
	{
		$topic_tracking_info[$forum_id] = get_complete_topic_tracking($forum_id, $forum_topic_ids);
	}

	foreach ($topic_ids as $topic_id)
	{
		if (empty($topic_rows[$topic_id])) continue;
		$row = $topic_rows[$topic_id];
		$forum_id = $row['forum_id'];

		$s_type_switch_test = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;
		$replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_replies_real'] : $row['topic_replies'];
		$unread_topic = (isset($topic_tracking_info[$forum_id][$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$forum_id][$topic_id]) ? true : false;

		$folder_img = $folder_alt = $topic_type = '';
		topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);

		$view_topic_url = append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', 't=' . $topic_id);
		$view_forum_url = append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'f=' . $forum_id);
		$topic_unapproved = (!$row['topic_approved'] && $auth->acl_get('m_approve', $forum_id)) ? true : false;
		$posts_unapproved = ($row['topic_approved'] && $row['topic_replies'] < $row['topic_replies_real'] && $auth->acl_get('m_approve', $forum_id)) ? true : false;
		$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . "&amp;t=$topic_id", true, $user->session_id) : '';
		$s_type_switch = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;

		$template->assign_block_vars($tpl_loopname, array(
			'FORUM_ID'					=> $forum_id,
			'TOPIC_ID'					=> $topic_id,
			'TOPIC_AUTHOR_FULL'			=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'FIRST_POST_TIME'			=> $user->format_date($row['topic_time']),

			'LAST_POST_SUBJECT'			=> censor_text($row['topic_last_post_subject']),
			'LAST_POST_TIME'			=> $user->format_date($row['topic_last_post_time']),
			'LAST_VIEW_TIME'			=> $user->format_date($row['topic_last_view_time']),
			'LAST_POST_AUTHOR'			=> get_username_string('username', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),

			'PAGINATION'				=> topic_generate_pagination($replies, $view_topic_url),
			'REPLIES'					=> $replies,
			'VIEWS'						=> $row['topic_views'],
			'TOPIC_TITLE'				=> censor_text($row['topic_title']),
			'FORUM_TITLE'				=> $row['forum_name'],

			'TOPIC_TYPE'			=> $topic_type,
			'TOPIC_FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
			'TOPIC_FOLDER_IMG_SRC'	=> $user->img($folder_img, $folder_alt, false, '', 'src'),
			'TOPIC_FOLDER_IMG_ALT'	=> $user->lang[$folder_alt],
			'NEWEST_POST_IMG'		=> $user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
			'TOPIC_ICON_IMG'		=> ($config['enable_topic_icons'] && !empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
			'TOPIC_ICON_IMG_WIDTH'	=> ($config['enable_topic_icons'] && !empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
			'TOPIC_ICON_IMG_HEIGHT'	=> ($config['enable_topic_icons'] && !empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
			'ATTACH_ICON_IMG'		=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
			'UNAPPROVED_IMG'		=> ($topic_unapproved || $posts_unapproved) ? $user->img('icon_topic_unapproved', ($topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '',
			'REPORTED_IMG'			=> ($row['topic_reported'] && $auth->acl_get('m_report', $forum_id)) ? $user->img('icon_topic_reported', 'TOPIC_REPORTED') : '',

			'S_TOPIC_TYPE'			=> $row['topic_type'],
			'S_USER_POSTED'			=> (isset($row['topic_posted']) && $row['topic_posted']) ? true : false,
			'S_UNREAD_TOPIC'		=> $unread_topic,
			'S_TOPIC_REPORTED'		=> ($row['topic_reported'] && $auth->acl_get('m_report', $forum_id)) ? true : false,
			'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
			'S_POSTS_UNAPPROVED'	=> $posts_unapproved,
			'S_HAS_POLL'			=> ($row['poll_start']) ? true : false,
			'S_POST_ANNOUNCE'		=> ($row['topic_type'] == POST_ANNOUNCE) ? true : false,
			'S_POST_GLOBAL'			=> ($row['topic_type'] == POST_GLOBAL) ? true : false,
			'S_POST_STICKY'			=> ($row['topic_type'] == POST_STICKY) ? true : false,
			'S_TOPIC_LOCKED'		=> ($row['topic_status'] == ITEM_LOCKED) ? true : false,
			'S_TOPIC_MOVED'			=> ($row['topic_status'] == ITEM_MOVED) ? true : false,
			'S_TOPIC_TYPE_SWITCH'	=> ($s_type_switch == $s_type_switch_test) ? -1 : $s_type_switch_test,

			'U_NEWEST_POST'			=> $view_topic_url . '&amp;view=unread#unread',
			'U_LAST_POST'			=> $view_topic_url . '&amp;p=' . $row['topic_last_post_id'] . '#p' . $row['topic_last_post_id'],
			'U_LAST_POST_AUTHOR'	=> get_username_string('profile', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'U_TOPIC_AUTHOR'		=> get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'U_VIEW_TOPIC'			=> $view_topic_url,
			'U_VIEW_FORUM'			=> $view_forum_url,
			'U_MCP_REPORT'			=> append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=reports&amp;mode=reports&amp;f=' . $forum_id . '&amp;t=' . $topic_id, true, $user->session_id),
			'U_MCP_QUEUE'			=> $u_mcp_queue,
		));
	}
}

/**
* Display last active topics
*/
function display_active_topics($tpl_loopname, $total_limit)
{
	global $auth, $cache, $config, $db, $template, $user;

	/**
	* Set some internal needed variables
	*/
	$excluded_topic_ids = array('');

	/**
	* Get the forums we take our topics from
	*/
	// Get the allowed forums
	$forum_ary = array();
	$forum_read_ary = $auth->acl_getf('f_read');
	foreach ($forum_read_ary as $forum_id => $allowed)
	{
		if ($allowed['f_read'])
		{
			$forum_ary[] = (int) $forum_id;
		}
	}
	$forum_ids = array_unique($forum_ary);

	if (!sizeof($forum_ids))
	{
		// No forums with f_read
		return;
	}

	$sql = 'SELECT forum_id
		FROM ' . FORUMS_TABLE . '
		WHERE ' . $db->sql_in_set('forum_id', $forum_ids) . '
			AND forum_flags & ' . FORUM_FLAG_ACTIVE_TOPICS;
	$result = $db->sql_query($sql);

	$forum_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$forum_ids[] = $row['forum_id'];
	}
	$db->sql_freeresult($result);

	// No forums with f_read
	if (!sizeof($forum_ids))
	{
		return;
	}

	// Moderator forums
	$m_approve_ids = array();
	$m_approve_ary = $auth->acl_getf('m_approve');
	foreach ($m_approve_ary as $forum_id => $allowed)
	{
		if ($allowed['m_approve'] && in_array($forum_id, $forum_ids))
		{
			$m_approve_ids[] = (int) $forum_id;
		}
	}

	// Get the allowed topics
	$sql = 'SELECT forum_id, topic_id, topic_type
		FROM ' . TOPICS_TABLE . '
		WHERE ' . $db->sql_in_set('topic_id', $excluded_topic_ids, true) . '
			AND ' . $db->sql_in_set('forum_id', $forum_ids) . '
			AND topic_type <> ' . POST_GLOBAL . '
			AND topic_status <> ' . ITEM_MOVED . '
			AND (' . $db->sql_in_set('forum_id', $m_approve_ids, false, true) . '
				OR topic_approved = 1)
		ORDER BY topic_last_post_time DESC';
	$result = $db->sql_query_limit($sql, $total_limit);

	$topic_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$topic_ids[] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	display_topic_rows($tpl_loopname, $topic_ids);
}

/**
* Display global announcements
*/
function display_global_announcements($tpl_loopname)
{
	global $auth, $cache, $config, $db, $template, $user;

	$forum_ary = $auth->acl_getf('f_read', true);
	$forum_ary = array_unique(array_keys($forum_ary));

	if (!sizeof($forum_ary))
	{
		return;
	}

	$sql = 'SELECT t.forum_id, t.topic_id
		FROM ' . TOPICS_TABLE . ' t
		WHERE ' . $db->sql_in_set('t.forum_id', $forum_ary) . "
			AND t.topic_type = " . POST_GLOBAL . '
		ORDER BY t.topic_priority DESC, t.topic_time DESC';
	$result = $db->sql_query($sql);

	$topic_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$topic_ids[] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	display_topic_rows($tpl_loopname, $topic_ids);
}

/**
* Assign/Build custom bbcodes for display in screens supporting using of bbcodes
* The custom bbcodes buttons will be placed within the template block 'custom_codes'
*/
function display_custom_bbcodes()
{
	global $db, $template, $user;

	// Start counting from 26 for the bbcode ids (every bbcode takes two ids - opening/closing)
	$num_predefined_bbcodes = 26;

	$sql = 'SELECT bbcode_id, bbcode_tag, bbcode_helpline
		FROM ' . BBCODES_TABLE . '
		WHERE display_on_posting = 1
		ORDER BY bbcode_order, bbcode_tag';
	$result = $db->sql_query($sql);

	$i = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		// If the helpline is defined within the language file, we will use the localised version, else just use the database entry...
		if (isset($user->lang[strtoupper($row['bbcode_helpline'])]))
		{
			$row['bbcode_helpline'] = $user->lang[strtoupper($row['bbcode_helpline'])];
		}

		$template->assign_block_vars('custom_tags', array(
			'BBCODE_NAME'		=> "'[{$row['bbcode_tag']}]', '[/" . str_replace('=', '', $row['bbcode_tag']) . "]'",
			'BBCODE_ID'			=> $num_predefined_bbcodes + ($i * 2),
			'BBCODE_TAG'		=> $row['bbcode_tag'],
			'BBCODE_HELPLINE'	=> $row['bbcode_helpline'],
			'A_BBCODE_HELPLINE'	=> str_replace(array('&amp;', '&quot;', "'", '&lt;', '&gt;'), array('&', '"', "\'", '<', '>'), $row['bbcode_helpline']),
		));

		$i++;
	}
	$db->sql_freeresult($result);
}

/**
* Display reasons
*/
function display_reasons($reason_id = 0)
{
	global $db, $user, $template;

	$sql = 'SELECT *
		FROM ' . REPORTS_REASONS_TABLE . '
		ORDER BY reason_order ASC';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
		if (isset($user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])]) && isset($user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])]))
		{
			$row['reason_description'] = $user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])];
			$row['reason_title'] = $user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])];
		}

		$template->assign_block_vars('reason', array(
			'ID'			=> $row['reason_id'],
			'TITLE'			=> $row['reason_title'],
			'DESCRIPTION'	=> $row['reason_description'],
			'S_SELECTED'	=> ($row['reason_id'] == $reason_id) ? true : false)
		);
	}
	$db->sql_freeresult($result);
}

/**
* Display user activity (action forum/topic)
*/
function display_user_activity(&$userdata)
{
	global $auth, $template, $db, $user;

	// Do not display user activity for users having more than 5000 posts...
	if ($userdata['user_posts'] > 5000)
	{
		return;
	}

	$forum_ary = array();

	// Do not include those forums the user is not having read access to...
	$forum_read_ary = $auth->acl_getf('!f_read');

	foreach ($forum_read_ary as $forum_id => $not_allowed)
	{
		if ($not_allowed['f_read'])
		{
			$forum_ary[] = (int) $forum_id;
		}
	}

	$forum_ary = array_unique($forum_ary);
	$forum_sql = (sizeof($forum_ary)) ? 'AND ' . $db->sql_in_set('forum_id', $forum_ary, true) : '';

	$fid_m_approve = $auth->acl_getf('m_approve', true);
	$sql_m_approve = (!empty($fid_m_approve)) ? 'OR ' . $db->sql_in_set('forum_id', array_keys($fid_m_approve)) : '';

	// Obtain active forum
	$sql = 'SELECT forum_id, COUNT(post_id) AS num_posts
		FROM ' . POSTS_TABLE . '
		WHERE poster_id = ' . $userdata['user_id'] . "
			AND post_postcount = 1
			AND (post_approved = 1
				$sql_m_approve)
			$forum_sql
		GROUP BY forum_id
		ORDER BY num_posts DESC";
	$result = $db->sql_query_limit($sql, 1);
	$active_f_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!empty($active_f_row))
	{
		$sql = 'SELECT forum_name
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $active_f_row['forum_id'];
		$result = $db->sql_query($sql, 3600);
		$active_f_row['forum_name'] = (string) $db->sql_fetchfield('forum_name');
		$db->sql_freeresult($result);
	}

	// Obtain active topic
	// We need to exclude passworded forums here so we do not leak the topic title
	$forum_ary_topic = array_unique(array_merge($forum_ary, $user->get_passworded_forums()));
	$forum_sql_topic = (!empty($forum_ary_topic)) ? 'AND ' . $db->sql_in_set('forum_id', $forum_ary_topic, true) : '';

	$sql = 'SELECT topic_id, COUNT(post_id) AS num_posts
		FROM ' . POSTS_TABLE . '
		WHERE poster_id = ' . $userdata['user_id'] . "
			AND post_postcount = 1
			AND (post_approved = 1
				$sql_m_approve)
			$forum_sql_topic
		GROUP BY topic_id
		ORDER BY num_posts DESC";
	$result = $db->sql_query_limit($sql, 1);
	$active_t_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!empty($active_t_row))
	{
		$sql = 'SELECT topic_title
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . $active_t_row['topic_id'];
		$result = $db->sql_query($sql);
		$active_t_row['topic_title'] = (string) $db->sql_fetchfield('topic_title');
		$db->sql_freeresult($result);
	}

	$userdata['active_t_row'] = $active_t_row;
	$userdata['active_f_row'] = $active_f_row;

	$active_f_name = $active_f_id = $active_f_count = $active_f_pct = '';
	if (!empty($active_f_row['num_posts']))
	{
		$active_f_name = $active_f_row['forum_name'];
		$active_f_id = $active_f_row['forum_id'];
		$active_f_count = $active_f_row['num_posts'];
		$active_f_pct = ($userdata['user_posts']) ? ($active_f_count / $userdata['user_posts']) * 100 : 0;
	}

	$active_t_name = $active_t_id = $active_t_count = $active_t_pct = '';
	if (!empty($active_t_row['num_posts']))
	{
		$active_t_name = $active_t_row['topic_title'];
		$active_t_id = $active_t_row['topic_id'];
		$active_t_count = $active_t_row['num_posts'];
		$active_t_pct = ($userdata['user_posts']) ? ($active_t_count / $userdata['user_posts']) * 100 : 0;
	}

	$l_active_pct = ($userdata['user_id'] != ANONYMOUS && $userdata['user_id'] == $user->data['user_id']) ? $user->lang['POST_PCT_ACTIVE_OWN'] : $user->lang['POST_PCT_ACTIVE'];

	$template->assign_vars(array(
		'ACTIVE_FORUM'			=> $active_f_name,
		'ACTIVE_FORUM_POSTS'	=> ($active_f_count == 1) ? sprintf($user->lang['USER_POST'], 1) : sprintf($user->lang['USER_POSTS'], $active_f_count),
		'ACTIVE_FORUM_PCT'		=> sprintf($l_active_pct, $active_f_pct),
		'ACTIVE_TOPIC'			=> censor_text($active_t_name),
		'ACTIVE_TOPIC_POSTS'	=> ($active_t_count == 1) ? sprintf($user->lang['USER_POST'], 1) : sprintf($user->lang['USER_POSTS'], $active_t_count),
		'ACTIVE_TOPIC_PCT'		=> sprintf($l_active_pct, $active_t_pct),
		'U_ACTIVE_FORUM'		=> append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'f=' . $active_f_id),
		'U_ACTIVE_TOPIC'		=> append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', 't=' . $active_t_id),
		'S_SHOW_ACTIVITY'		=> true)
	);
}

/**
* Topic and forum watching common code
*/
function watch_topic_forum($mode, &$s_watching, $user_id, $forum_id, $topic_id, $notify_status = 'unset', $start = 0, $item_title = '')
{
	global $template, $db, $user, $start, $config;

	$table_sql = ($mode == 'forum') ? FORUMS_WATCH_TABLE : TOPICS_WATCH_TABLE;
	$where_sql = ($mode == 'forum') ? 'forum_id' : 'topic_id';
	$match_id = ($mode == 'forum') ? $forum_id : $topic_id;
	$u_url = "uid={$user->data['user_id']}";
	$u_url .= ($mode == 'forum') ? '&amp;f' : '&amp;f=' . $forum_id . '&amp;t';

	if ($user_id == ANONYMOUS)
	{
		if ((isset($_GET['unwatch']) && $_GET['unwatch'] == $mode) || (isset($_GET['watch']) && $_GET['watch'] == $mode))
		{
			login_box();
		}
	}
	else
	{
		$is_watching = false;

		if ($notify_status == 'unset')
		{
			$sql = "SELECT notify_status
				FROM $table_sql
				WHERE $where_sql = $match_id
					AND user_id = $user_id";
			$result = $db->sql_query($sql);

			$notify_status = ($row = $db->sql_fetchrow($result)) ? $row['notify_status'] : NULL;
			$db->sql_freeresult($result);
		}

		if (!is_null($notify_status) && $notify_status !== '')
		{

			if (isset($_GET['unwatch']))
			{
				$uid = request_var('uid', 0);
				$token = request_var('hash', '');

				if ($token && check_link_hash($token, "{$mode}_$match_id") || confirm_box(true))
				{
					if ($uid != $user_id || $_GET['unwatch'] != $mode)
					{
						$redirect_url = append_sid(PHPBB_ROOT_PATH . "view$mode.php", "$u_url=$match_id&amp;start=$start");
						$message = $user->lang['ERR_UNWATCHING'] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
						trigger_error($message);
					}

					$sql = 'DELETE FROM ' . $table_sql . "
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					$db->sql_query($sql);

					$redirect_url = append_sid(PHPBB_ROOT_PATH . "view$mode.php", "$u_url=$match_id&amp;start=$start");
					if (!empty($config['skip_typical_notices']))
					{
						redirect($redirect_url);
					}

					$message = $user->lang['NOT_WATCHING_' . strtoupper($mode)] . '<br /><br />';
					$message .= sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
					meta_refresh(3, $redirect_url);
					trigger_error($message);
				}
				else
				{
					$s_hidden_fields = array(
						'uid'		=> $user->data['user_id'],
						'unwatch'	=> $mode,
						'start'		=> $start,
						'f'			=> $forum_id,
					);
					if ($mode != 'forum')
					{
						$s_hidden_fields['t'] = $topic_id;
					}

					if ($item_title == '')
					{
						$confirm_box_message = 'UNWATCH_' . strtoupper($mode);
					}
					else
					{
						$confirm_box_message = $user->lang('UNWATCH_' . strtoupper($mode) . '_DETAILED', $item_title);
					}
					confirm_box(false, $confirm_box_message, build_hidden_fields($s_hidden_fields));
				}
			}
			else
			{
				$is_watching = true;

				if ($notify_status != NOTIFY_YES)
				{
					$sql = 'UPDATE ' . $table_sql . "
						SET notify_status = " . NOTIFY_YES . "
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					$db->sql_query($sql);
				}
			}
		}
		else
		{
			if (isset($_GET['watch']))
			{
				$uid = request_var('uid', 0);
				$token = request_var('hash', '');

				if ($token && check_link_hash($token, "{$mode}_$match_id") || confirm_box(true))
				{
					if ($uid != $user_id || $_GET['watch'] != $mode)
					{
						$redirect_url = append_sid(PHPBB_ROOT_PATH . "view$mode.php", "$u_url=$match_id&amp;start=$start");
						$message = $user->lang['ERR_WATCHING'] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
						trigger_error($message);
					}

					$is_watching = true;

					$sql = 'INSERT INTO ' . $table_sql . " (user_id, $where_sql, notify_status)
						VALUES ($user_id, $match_id, " . NOTIFY_YES . ')';
					$db->sql_query($sql);

					$redirect_url = append_sid(PHPBB_ROOT_PATH . "view$mode.php", "$u_url=$match_id&amp;start=$start");
					if (!empty($config['skip_typical_notices']))
					{
						redirect($redirect_url);
					}

					$message = $user->lang['ARE_WATCHING_' . strtoupper($mode)] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
					meta_refresh(3, $redirect_url);
					trigger_error($message);
				}
				else
				{
					$s_hidden_fields = array(
						'uid'		=> $user->data['user_id'],
						'watch'		=> $mode,
						'start'		=> $start,
						'f'			=> $forum_id,
					);
					if ($mode != 'forum')
					{
						$s_hidden_fields['t'] = $topic_id;
					}

					$confirm_box_message = (($item_title == '') ? 'WATCH_' . strtoupper($mode) : $user->lang('WATCH_' . strtoupper($mode) . '_DETAILED', $item_title));
					confirm_box(false, $confirm_box_message, build_hidden_fields($s_hidden_fields));
				}
			}
		}

		$s_watching['link'] = append_sid(PHPBB_ROOT_PATH . "view$mode.php", "$u_url=$match_id&amp;" . (($is_watching) ? 'unwatch' : 'watch') . "=$mode&amp;start=$start&amp;hash=" . generate_link_hash("{$mode}_$match_id"));
		$s_watching['title'] = $user->lang[(($is_watching) ? 'STOP' : 'START') . '_WATCHING_' . strtoupper($mode)];
		$s_watching['is_watching'] = $is_watching;
	}
}

/**
* Get user rank title and image
*
* @param int $user_rank the current stored users rank id
* @param int $user_posts the users number of posts
* @param string &$rank_title the rank title will be stored here after execution
* @param string &$rank_img the rank image as full img tag is stored here after execution
* @param string &$rank_img_src the rank image source is stored here after execution
*
* Note: since we do not want to break backwards-compatibility, this function will only properly assign ranks to guests if you call it for them with user_posts == false
*/
function get_user_rank($user_rank, $user_posts, &$rank_title, &$rank_img, &$rank_img_src)
{
	global $ranks, $config;

	if (empty($ranks))
	{
		global $cache;
		$ranks = $cache->obtain_ranks();
	}

	if (!empty($user_rank) && isset($ranks['special'][$user_rank]))
	{
		$rank = $ranks['special'][$user_rank];
		$rank_title = (!$rank['rank_hide_title']) ? $rank['rank_title'] : '';
		$rank_img = (!empty($rank['rank_image'])) ? '<img src="' . PHPBB_ROOT_PATH . RANK_IMAGES_PATH . '/' . $rank['rank_image'] . '" alt="' . $rank['rank_title'] . '" title="' . $rank['rank_title'] . '" />' : '';
		$rank_img_src = (!empty($rank['rank_image'])) ? PHPBB_ROOT_PATH . RANK_IMAGES_PATH . '/' . $rank['rank_image'] : '';
	}
	else if ($user_posts !== false)
	{
		if (!empty($ranks['normal']))
		{
			foreach ($ranks['normal'] as $rank)
			{
				if ($user_posts >= $rank['rank_min'])
				{
					$rank_title = (!$rank['rank_hide_title']) ? $rank['rank_title'] : '';
					$rank_img = (!empty($rank['rank_image'])) ? '<img src="' . PHPBB_ROOT_PATH . RANK_IMAGES_PATH . '/' . $rank['rank_image'] . '" alt="' . $rank['rank_title'] . '" title="' . $rank['rank_title'] . '" />' : '';
					$rank_img_src = (!empty($rank['rank_image'])) ? PHPBB_ROOT_PATH . RANK_IMAGES_PATH . '/' . $rank['rank_image'] : '';
					break;
				}
			}
		}
	}
}

/**
* Get user avatar
*
* @param string $avatar Users assigned avatar name
* @param int $avatar_type Type of avatar
* @param string $avatar_width Width of users avatar
* @param string $avatar_height Height of users avatar
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
*
* @return string Avatar image
*/
function get_user_avatar($avatar, $avatar_type, $avatar_width, $avatar_height, $alt = 'USER_AVATAR', $ignore_config = false)
{
	global $user, $config;

	if (empty($avatar) || !$avatar_type || (!$config['allow_avatar'] && !$ignore_config))
	{
		return '';
	}

	$avatar_img = '';

	switch ($avatar_type)
	{
		case AVATAR_UPLOAD:
			if (!$config['allow_avatar_upload'] && !$ignore_config)
			{
				return '';
			}
			$avatar_img = PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH . '/' . $avatar;
		break;

		case AVATAR_GALLERY:
			if (!$config['allow_avatar_local'] && !$ignore_config)
			{
				return '';
			}
			$avatar_img = PHPBB_ROOT_PATH . AVATAR_GALLERY_PATH . '/' . $avatar;
		break;

		case AVATAR_REMOTE:
			if (!$config['allow_avatar_remote'] && !$ignore_config)
			{
				return '';
			}
			$avatar_img = $avatar;
		break;

		default:
			return '';
		break;
	}

	if (!$ignore_config)
	{
		if ($avatar_width > $config['avatar_max_width'] && $avatar_width && $avatar_height)
		{
			$avatar_height = round($config['avatar_max_width']*($avatar_height/$avatar_width));
			$avatar_width = $config['avatar_max_width'];
		}
		if ($avatar_height > $config['avatar_max_height'] && $avatar_width && $avatar_height)
		{
			$avatar_width = round($config['avatar_max_height']*($avatar_width/$avatar_height));
			$avatar_height = $config['avatar_max_height'];
		}
		if (!$avatar_width || !$avatar_height)
		{
			return '';
		}
	}

	return '<img src="' . (str_replace(' ', '%20', $avatar_img)) . '" width="' . $avatar_width . '" height="' . $avatar_height . '" alt="' . ((!empty($user->lang[$alt])) ? $user->lang[$alt] : $alt) . '" />';
}
