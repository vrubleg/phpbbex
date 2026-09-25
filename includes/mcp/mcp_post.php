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
* Handling actions in post details screen
*/
function mcp_post_details($id, $mode, $action)
{
	global $template, $db, $user, $auth, $cache;

	$user->add_lang('posting');

	$post_id = request_var('p', 0);
	$start  = request_var('start', 0);

	// Get post data
	$post_info = get_post_data([$post_id], false, true);

	add_form_key('mcp_post_details');

	if (!sizeof($post_info))
	{
		trigger_error('POST_NOT_EXIST');
	}

	$post_info = $post_info[$post_id];
	$url = append_sid(PHPBB_ROOT_PATH . 'mcp.php?' . extra_url());

	switch ($action)
	{
		case 'whois':

			if ($auth->acl_get('m_info', $post_info['forum_id']))
			{
				$ip = request_var('ip', '');
				require_once(PHPBB_ROOT_PATH . 'includes/functions_user.php');

				$template->assign_vars([
					'RETURN_POST'   => sprintf($user->lang['RETURN_POST'], '<a href="' . append_sid(PHPBB_ROOT_PATH . 'mcp.php', "i={$id}&amp;mode={$mode}&amp;p={$post_id}") . '">', '</a>'),
					'U_RETURN_POST' => append_sid(PHPBB_ROOT_PATH . 'mcp.php', "i={$id}&amp;mode={$mode}&amp;p={$post_id}"),
					'L_RETURN_POST' => sprintf($user->lang['RETURN_POST'], '', ''),
					'WHOIS'         => user_ipwhois($ip),
				]);
			}

			// We're done with the whois page so return
			return;

		break;

		case 'chgposter':
		case 'chgposter_ip':

			if ($action == 'chgposter')
			{
				$username = request_var('username', '', true);
				$sql_where = "username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
			}
			else
			{
				$new_user_id = request_var('u', 0);
				$sql_where = 'user_id = ' . $new_user_id;
			}

			$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE ' . $sql_where;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				trigger_error('NO_USER');
			}

			if ($auth->acl_get('m_chgposter', $post_info['forum_id']))
			{
				if (check_form_key('mcp_post_details'))
				{
					change_poster($post_info, $row);
				}
				else
				{
					trigger_error('FORM_INVALID');
				}
			}

		break;
	}

	// Set some vars
	$users_ary = $usernames_ary = [];
	$attachments = $extensions = [];
	$post_id = $post_info['post_id'];
	$topic_tracking_info = [];

	// Get topic tracking info
	$tmp_topic_data = [$post_info['topic_id'] => $post_info];
	$topic_tracking_info = get_topic_tracking($post_info['topic_id'], $tmp_topic_data);
	unset($tmp_topic_data);

	$post_unread = (isset($topic_tracking_info[$post_info['topic_id']]) && $post_info['post_time'] > $topic_tracking_info[$post_info['topic_id']]);

	// Process message, leave it uncensored
	$message = $post_info['post_text'];

	if ($post_info['bbcode_bitfield'])
	{
		require_once(PHPBB_ROOT_PATH . 'includes/bbcode.php');
		$bbcode = new bbcode($post_info['bbcode_bitfield']);
		$bbcode->bbcode_second_pass($message, $post_info['bbcode_uid'], $post_info['bbcode_bitfield'], $post_info['post_time']);
	}

	$message = bbcode_nl2br($message);
	$message = smiley_text($message);

	if ($post_info['post_attachment'] && $auth->acl_get('u_download') && $auth->acl_get('f_download', $post_info['forum_id']))
	{
		$extensions = $cache->obtain_attach_extensions($post_info['forum_id']);

		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE post_msg_id = ' . $post_id . '
				AND in_message = 0
			ORDER BY filetime DESC, post_msg_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$attachments[] = $row;
		}
		$db->sql_freeresult($result);

		if (sizeof($attachments))
		{
			$user->add_lang('viewtopic');
			parse_attachments($post_info['forum_id'], $message, $attachments);
		}

		// Display not already displayed Attachments for this post, we already parsed them. ;)
		if (!empty($attachments))
		{
			$template->assign_var('S_HAS_ATTACHMENTS', true);

			foreach ($attachments as $attachment)
			{
				$template->assign_block_vars('attachment', [
					'DISPLAY_ATTACHMENT'    => $attachment]
				);
			}
		}
	}

	$template->assign_vars([
		'U_MCP_ACTION'          => "{$url}&amp;i=main&amp;quickmod=1&amp;mode=post_details", // Use this for mode paramaters
		'U_POST_ACTION'         => "{$url}&amp;i={$id}&amp;mode=post_details", // Use this for action parameters
		'U_APPROVE_ACTION'      => append_sid(PHPBB_ROOT_PATH . 'mcp.php', "i=queue&amp;p={$post_id}&amp;f={$post_info['forum_id']}"),

		'S_CAN_VIEWIP'          => $auth->acl_get('m_info', $post_info['forum_id']),
		'S_CAN_CHGPOSTER'       => $auth->acl_get('m_chgposter', $post_info['forum_id']),
		'S_CAN_LOCK_POST'       => $auth->acl_get('m_lock', $post_info['forum_id']),
		'S_CAN_DELETE_POST'     => $auth->acl_get('m_delete', $post_info['forum_id']),

		'S_POST_REPORTED'       => (bool) $post_info['post_reported'],
		'S_POST_UNAPPROVED'     => !$post_info['post_approved'],
		'S_POST_LOCKED'         => (bool) $post_info['post_edit_locked'],

		'U_EDIT'                => ($auth->acl_get('m_edit', $post_info['forum_id'])) ? append_sid(PHPBB_ROOT_PATH . 'posting.php', "mode=edit&amp;p={$post_info['post_id']}") : '',
		'U_FIND_USERNAME'       => append_sid(PHPBB_ROOT_PATH . 'memberlist.php', 'mode=searchuser&amp;form=mcp_chgposter&amp;field=username&amp;select_single=true'),
		'U_MCP_APPROVE'         => append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=queue&amp;mode=approve_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
		'U_MCP_REPORT'          => append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=reports&amp;mode=report_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
		'U_MCP_WARN_USER'       => ($auth->acl_get('m_warn')) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=warn&amp;mode=warn_user&amp;u=' . $post_info['user_id']) : '',
		'U_VIEW_POST'           => append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', 'p=' . $post_info['post_id'] . '#p' . $post_info['post_id']),
		'U_VIEW_TOPIC'          => append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', 't=' . $post_info['topic_id']),

		'MINI_POST_IMG'         => ($post_unread) ? $user->img('icon_post_target_unread', 'UNREAD_POST') : $user->img('icon_post_target', 'POST'),

		'RETURN_TOPIC'          => sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "p={$post_id}") . "#p{$post_id}\">", '</a>'),
		'RETURN_FORUM'          => sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid(PHPBB_ROOT_PATH . 'viewforum.php', "f={$post_info['forum_id']}&amp;start={$start}") . '">', '</a>'),
		'REPORTED_IMG'          => $user->img('icon_topic_reported', 'POST_REPORTED'),
		'UNAPPROVED_IMG'        => $user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
		'EDIT_IMG'              => $user->img('icon_post_edit', 'EDIT_POST'),
		'SEARCH_IMG'            => $user->img('icon_user_search', 'SEARCH'),

		'POST_AUTHOR_FULL'      => get_username_string('full', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
		'POST_AUTHOR_COLOUR'    => get_username_string('colour', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
		'POST_AUTHOR'           => get_username_string('username', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
		'U_POST_AUTHOR'         => get_username_string('profile', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),

		'POST_PREVIEW'          => $message,
		'POST_SUBJECT'          => $post_info['post_subject'],
		'POST_DATE'             => $user->format_date($post_info['post_time']),
		'POST_IP'               => $post_info['poster_ip'],
		'POST_IPADDR'           => ($auth->acl_get('m_info', $post_info['forum_id']) && request_var('lookup', '')) ? @gethostbyaddr($post_info['poster_ip']) : '',
		'POST_ID'               => $post_info['post_id'],

		'U_LOOKUP_IP'           => ($auth->acl_get('m_info', $post_info['forum_id'])) ? "{$url}&amp;i={$id}&amp;mode={$mode}&amp;lookup={$post_info['poster_ip']}#ip" : '',
		'U_WHOIS'               => ($auth->acl_get('m_info', $post_info['forum_id'])) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', "i={$id}&amp;mode={$mode}&amp;action=whois&amp;p={$post_id}&amp;ip={$post_info['poster_ip']}") : '',
	]);

	// Get Reports
	if ($auth->acl_get('m_report', $post_info['forum_id']))
	{
		$sql = 'SELECT r.*, u.user_id, u.username
			FROM ' . REPORTS_TABLE . ' r, ' . USERS_TABLE . " u
			WHERE r.post_id = {$post_id}
				AND u.user_id = r.user_id
			ORDER BY r.report_time DESC";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$template->assign_var('S_SHOW_REPORTS', true);

			do
			{
				$template->assign_block_vars('reports', [
					'REPORT_ID'     => $row['report_id'],
					'REPORTER'      => ($row['user_id'] != ANONYMOUS) ? $row['username'] : $user->lang['GUEST'],
					'U_REPORTER'    => ($row['user_id'] != ANONYMOUS) ? append_sid(PHPBB_ROOT_PATH . 'memberlist.php', 'mode=viewprofile&amp;u=' . $row['user_id']) : '',
					'USER_NOTIFY'   => (bool) $row['user_notify'],
					'REPORT_TIME'   => $user->format_date($row['report_time']),
					'REPORT_TEXT'   => bbcode_nl2br(trim($row['report_text'])),
				]);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
	}

	// Get IP
	if ($auth->acl_get('m_info', $post_info['forum_id']))
	{
		$rdns_ip_num = request_var('rdns', '');

		if ($rdns_ip_num != 'all')
		{
			$template->assign_vars([
				'U_LOOKUP_ALL'  => "{$url}&amp;i=main&amp;mode=post_details&amp;rdns=all"]
			);
		}

		// Get other users who've posted under this IP
		$sql = 'SELECT poster_id, COUNT(poster_id) as postings
			FROM ' . POSTS_TABLE . "
			WHERE poster_ip = '" . $db->sql_escape($post_info['poster_ip']) . "'
			GROUP BY poster_id
			ORDER BY postings DESC";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// Fill the user select list with users who have posted under this IP
			if ($row['poster_id'] != $post_info['poster_id'])
			{
				$users_ary[$row['poster_id']] = $row;
			}
		}
		$db->sql_freeresult($result);

		if (sizeof($users_ary))
		{
			// Get the usernames
			$sql = 'SELECT user_id, username
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', array_keys($users_ary));
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$users_ary[$row['user_id']]['username'] = $row['username'];
				$usernames_ary[utf8_clean_string($row['username'])] = $users_ary[$row['user_id']];
			}
			$db->sql_freeresult($result);

			foreach ($users_ary as $user_id => $user_row)
			{
				$template->assign_block_vars('userrow', [
					'USERNAME'      => ($user_id == ANONYMOUS) ? $user->lang['GUEST'] : $user_row['username'],
					'NUM_POSTS'     => $user_row['postings'],
					'L_POST_S'      => ($user_row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS'],

					'U_PROFILE'     => ($user_id == ANONYMOUS) ? '' : append_sid(PHPBB_ROOT_PATH . 'memberlist.php', 'mode=viewprofile&amp;u=' . $user_id),
					'U_SEARCHPOSTS' => append_sid(PHPBB_ROOT_PATH . 'search.php', 'author_id=' . $user_id . '&amp;sr=topics')]
				);
			}
		}

		// Get other IP's this user has posted under

		// A compound index on poster_id, poster_ip (posts table) would help speed up this query a lot,
		// but the extra size is only valuable if there are persons having more than a thousands posts.
		// This is better left to the really really big forums.

		$sql = 'SELECT poster_ip, COUNT(poster_ip) AS postings
			FROM ' . POSTS_TABLE . '
			WHERE poster_id = ' . $post_info['poster_id'] . "
			GROUP BY poster_ip
			ORDER BY postings DESC";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$hostname = (($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') && $row['poster_ip']) ? @gethostbyaddr($row['poster_ip']) : '';

			$template->assign_block_vars('iprow', [
				'IP'            => $row['poster_ip'],
				'HOSTNAME'      => $hostname,
				'NUM_POSTS'     => $row['postings'],
				'L_POST_S'      => ($row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS'],

				'U_LOOKUP_IP'   => ($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') ? '' : "{$url}&amp;i={$id}&amp;mode=post_details&amp;rdns={$row['poster_ip']}#ip",
				'U_WHOIS'       => append_sid(PHPBB_ROOT_PATH . 'mcp.php', "i={$id}&amp;mode={$mode}&amp;action=whois&amp;p={$post_id}&amp;ip={$row['poster_ip']}")]
			);
		}
		$db->sql_freeresult($result);

		$user_select = '';

		if (sizeof($usernames_ary))
		{
			ksort($usernames_ary);

			foreach ($usernames_ary as $row)
			{
				$user_select .= '<option value="' . $row['poster_id'] . '">' . $row['username'] . "</option>\n";
			}
		}

		$template->assign_var('S_USER_SELECT', $user_select);
	}

}

/**
* Change a post's poster
*/
function change_poster(&$post_info, $userdata)
{
	global $auth, $db, $config;

	if (empty($userdata) || $userdata['user_id'] == $post_info['user_id'])
	{
		return;
	}

	$post_id = $post_info['post_id'];

	$sql = 'UPDATE ' . POSTS_TABLE . "
		SET poster_id = {$userdata['user_id']}
		WHERE post_id = {$post_id}";
	$db->sql_query($sql);

	// Resync topic/forum if needed
	if ($post_info['topic_last_post_id'] == $post_id || $post_info['forum_last_post_id'] == $post_id || $post_info['topic_first_post_id'] == $post_id)
	{
		sync('topic', 'topic_id', $post_info['topic_id'], false, false);
		sync('forum', 'forum_id', $post_info['forum_id'], false, false);
	}

	// Adjust post counts... only if the post is approved (else, it was not added the users post count anyway)
	if ($post_info['post_postcount'] && $post_info['post_approved'])
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_posts = user_posts - 1
			WHERE user_id = ' . $post_info['user_id'] .'
			AND user_posts > 0';
		$db->sql_query($sql);

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_posts = user_posts + 1
			WHERE user_id = ' . $userdata['user_id'];
		$db->sql_query($sql);
	}

	// change the poster_id within the attachments table, else the data becomes out of sync and errors displayed because of wrong ownership
	if ($post_info['post_attachment'])
	{
		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
			SET poster_id = ' . $userdata['user_id'] . '
			WHERE poster_id = ' . $post_info['user_id'] . '
				AND post_msg_id = ' . $post_info['post_id'] . '
				AND topic_id = ' . $post_info['topic_id'];
		$db->sql_query($sql);
	}

	// refresh search cache of this post
	require_once(PHPBB_ROOT_PATH . 'includes/search/fulltext_mysql.php');
	$search = new fulltext_mysql();
	$search->destroy_cache([], [$post_info['user_id'], $userdata['user_id']]);

	$from_username = $post_info['username'];
	$to_username = $userdata['username'];

	// Renew post info
	$post_info = get_post_data([$post_id], false, true);

	if (!sizeof($post_info))
	{
		trigger_error('POST_NOT_EXIST');
	}

	$post_info = $post_info[$post_id];

	// Now add log entry
	add_log('mod', $post_info['forum_id'], $post_info['topic_id'], 'LOG_MCP_CHANGE_POSTER', $post_info['topic_title'], $from_username, $to_username);
}
