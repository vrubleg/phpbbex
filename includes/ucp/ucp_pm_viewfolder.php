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
* View message folder
* Called from ucp_pm with mode == 'view' && action == 'view_folder'
*/
function view_folder($id, $mode, $folder_id, $folder)
{
	global $user, $template, $auth, $db, $cache, $config;

	$folder_info = get_pm_from($folder_id, $folder, $user->data['user_id']);

	$user->add_lang('viewforum');

	// Grab icons
	$icons = $cache->obtain_icons();

	$color_rows = ['marked', 'replied'];

	// only show the friend/foe color rows if the module is enabled
	$zebra_enabled = false;

	$_module = new p_master();
	$_module->list_modules('ucp');
	$_module->set_active('zebra');

	$zebra_enabled = ($_module->active_module !== false);

	unset($_module);

	if ($zebra_enabled)
	{
		$color_rows = array_merge($color_rows, ['friend', 'foe']);
	}

	foreach ($color_rows as $var)
	{
		$template->assign_block_vars('pm_colour_info', [
			'IMG'   => $user->img("pm_{$var}", ''),
			'CLASS' => "pm_{$var}_colour",
			'LANG'  => $user->lang[strtoupper($var) . '_MESSAGE']]
		);
	}

	$mark_options = ['mark_important', 'delete_marked'];

	$s_mark_options = '';
	foreach ($mark_options as $mark_option)
	{
		$s_mark_options .= '<option value="' . $mark_option . '">' . $user->lang[strtoupper($mark_option)] . '</option>';
	}

	// We do the folder moving options here too, for template authors to use...
	$s_folder_move_options = '';
	if ($folder_id != PRIVMSGS_NO_BOX && $folder_id != PRIVMSGS_OUTBOX)
	{
		foreach ($folder as $f_id => $folder_ary)
		{
			if ($f_id == PRIVMSGS_OUTBOX || $f_id == PRIVMSGS_SENTBOX || $f_id == $folder_id)
			{
				continue;
			}

			$s_folder_move_options .= '<option' . (($f_id != PRIVMSGS_INBOX) ? ' class="sep"' : '') . ' value="' . $f_id . '">';
			$s_folder_move_options .= sprintf($user->lang['MOVE_MARKED_TO_FOLDER'], $folder_ary['folder_name']);
			$s_folder_move_options .= (($folder_ary['unread_messages']) ? ' [' . $folder_ary['unread_messages'] . '] ' : '') . '</option>';
		}
	}
	$friend = $foe = [];

	// Get friends and foes
	$sql = 'SELECT *
		FROM ' . ZEBRA_TABLE . '
		WHERE user_id = ' . $user->data['user_id'];
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$friend[$row['zebra_id']] = $row['friend'];
		$foe[$row['zebra_id']] = $row['foe'];
	}
	$db->sql_freeresult($result);

	$template->assign_vars([
		'S_MARK_OPTIONS'        => $s_mark_options,
		'S_MOVE_MARKED_OPTIONS' => $s_folder_move_options]
	);

	// Okay, lets dump out the page ...
	if (sizeof($folder_info['pm_list']))
	{
		$address_list = [];

		// Build Recipient List if in outbox/sentbox - max two additional queries
		if ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX)
		{
			$address_list = get_recipient_strings($folder_info['rowset']);
		}

		foreach ($folder_info['pm_list'] as $message_id)
		{
			$row = &$folder_info['rowset'][$message_id];

			$folder_img = ($row['pm_unread']) ? 'pm_unread' : 'pm_read';
			$folder_alt = ($row['pm_unread']) ? 'NEW_MESSAGES' : 'NO_NEW_MESSAGES';

			// Generate all URIs ...
			$view_message_url = append_sid(PHPBB_ROOT_PATH . 'ucp.php', "i={$id}&amp;mode=view&amp;f={$folder_id}&amp;p={$message_id}");
			$remove_message_url = append_sid(PHPBB_ROOT_PATH . 'ucp.php', "i={$id}&amp;mode=compose&amp;action=delete&amp;p={$message_id}");

			$row_indicator = '';
			foreach ($color_rows as $var)
			{
				if (($var != 'friend' && $var != 'foe' && $row['pm_' . $var])
					||
					(($var == 'friend' || $var == 'foe') && isset(${$var}[$row['author_id']]) && ${$var}[$row['author_id']]))
				{
					$row_indicator = $var;
					break;
				}
			}

			// Send vars to template
			$template->assign_block_vars('messagerow', [
				'PM_CLASS'          => ($row_indicator) ? 'pm_' . $row_indicator . '_colour' : '',

				'MESSAGE_AUTHOR_FULL'       => get_username_string('full', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'MESSAGE_AUTHOR_COLOUR'     => get_username_string('colour', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'MESSAGE_AUTHOR'            => get_username_string('username', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'U_MESSAGE_AUTHOR'          => get_username_string('profile', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),

				'FOLDER_ID'         => $folder_id,
				'MESSAGE_ID'        => $message_id,
				'SENT_TIME'         => $user->format_date($row['message_time']),
				'SUBJECT'           => censor_text($row['message_subject']),
				'FOLDER'            => (isset($folder[$row['folder_id']])) ? $folder[$row['folder_id']]['folder_name'] : '',
				'U_FOLDER'          => (isset($folder[$row['folder_id']])) ? append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'folder=' . $row['folder_id']) : '',
				'PM_ICON_IMG'       => (!empty($icons[$row['icon_id']])) ? '<img src="' . TOPIC_ICONS_PATH . '/' . $icons[$row['icon_id']]['img'] . '" width="' . $icons[$row['icon_id']]['width'] . '" height="' . $icons[$row['icon_id']]['height'] . '" alt="" title="" />' : '',
				'PM_ICON_URL'       => (!empty($icons[$row['icon_id']])) ? TOPIC_ICONS_PATH . '/' . $icons[$row['icon_id']]['img'] : '',
				'FOLDER_IMG'        => $user->img($folder_img, $folder_alt),
				'FOLDER_IMG_SRC'    => $user->img($folder_img, $folder_alt, false, '', 'src'),
				'PM_IMG'            => ($row_indicator) ? $user->img('pm_' . $row_indicator, '') : '',
				'ATTACH_ICON_IMG'   => ($auth->acl_get('u_download') && $row['message_attachment'] && $config['allow_pm_attach']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',

				'S_PM_UNREAD'       => (bool) $row['pm_unread'],
				'S_PM_DELETED'      => (bool) $row['pm_deleted'],
				'S_PM_REPORTED'     => isset($row['report_id']),
				'S_AUTHOR_DELETED'  => ($row['author_id'] == ANONYMOUS),

				'U_VIEW_PM'         => ($row['pm_deleted']) ? '' : $view_message_url,
				'U_REMOVE_PM'       => ($row['pm_deleted']) ? $remove_message_url : '',
				'U_MCP_REPORT'      => (isset($row['report_id'])) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=pm_reports&amp;mode=pm_report_details&amp;r=' . $row['report_id']) : '',
				'RECIPIENTS'        => ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? implode(', ', $address_list[$message_id]) : '',
			]);
		}
		unset($folder_info['rowset']);

		$template->assign_vars([
			'S_SHOW_RECIPIENTS'     => ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX),
			'S_SHOW_COLOUR_LEGEND'  => true,

			'REPORTED_IMG'          => $user->img('icon_topic_reported', 'PM_REPORTED'),
			'S_PM_ICONS'            => (bool) $config['enable_pm_icons'],
		]);
	}
}

/**
* Get Messages from folder/user
*/
function get_pm_from($folder_id, $folder, $user_id)
{
	global $user, $db, $template, $config, $auth;

	$start = request_var('start', 0);

	// Additional vars later, pm ordering is mostly different from post ordering. :/
	$sort_days  = request_var('st', 0);
	$sort_key   = request_var('sk', 't');
	$sort_dir   = request_var('sd', 'd');

	// PM ordering options
	$limit_days = [0 => $user->lang['ALL_MESSAGES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']];

	// No sort by Author for sentbox/outbox (already only author available)
	// Also, sort by msg_id for the time - private messages are not as prone to errors as posts are.
	if ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX)
	{
		$sort_by_text = ['t' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']];
		$sort_by_sql = ['t' => 'p.message_time', 's' => ['p.message_subject', 'p.message_time']];
	}
	else
	{
		$sort_by_text = ['a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']];
		$sort_by_sql = ['a' => ['u.username_clean', 'p.message_time'], 't' => 'p.message_time', 's' => ['p.message_subject', 'p.message_time']];
	}

	$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

	$folder_sql = 't.folder_id = ' . (int) $folder_id;

	// Limit pms to certain time frame, obtain correct pm count
	if ($sort_days)
	{
		$min_post_time = time() - ($sort_days * 86400);

		if (isset($_POST['sort']))
		{
			$start = 0;
		}

		$sql = 'SELECT COUNT(t.msg_id) AS pm_count
			FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . " p
			WHERE {$folder_sql}
				AND t.user_id = {$user_id}
				AND t.msg_id = p.msg_id
				AND p.message_time >= {$min_post_time}";
		$result = $db->sql_query_limit($sql, 1);
		$pm_count = (int) $db->sql_fetchfield('pm_count');
		$db->sql_freeresult($result);

		$sql_limit_time = "AND p.message_time >= {$min_post_time}";
	}
	else
	{
		$pm_count = $folder[$folder_id]['num_messages'] ?? 0;
		$sql_limit_time = '';
	}

	$template->assign_vars([
		'PAGINATION'        => generate_pagination(append_sid(PHPBB_ROOT_PATH . 'ucp.php', "i=pm&amp;mode=view&amp;action=view_folder&amp;f={$folder_id}&amp;{$u_sort_param}"), $pm_count, $config['topics_per_page'], $start),
		'PAGE_NUMBER'       => on_page($pm_count, $config['topics_per_page'], $start),
		'TOTAL_MESSAGES'    => (($pm_count == 1) ? $user->lang['VIEW_PM_MESSAGE'] : sprintf($user->lang['VIEW_PM_MESSAGES'], $pm_count)),

		'POST_IMG'      => (!$auth->acl_get('u_sendpm')) ? $user->img('button_topic_locked', 'POST_PM_LOCKED') : $user->img('button_pm_new', 'POST_NEW_PM'),

		'S_NO_AUTH_SEND_MESSAGE'    => !$auth->acl_get('u_sendpm'),

		'S_SELECT_SORT_DIR'     => $s_sort_dir,
		'S_SELECT_SORT_KEY'     => $s_sort_key,
		'S_SELECT_SORT_DAYS'    => $s_limit_days,
		'S_TOPIC_ICONS'         => (bool) $config['enable_pm_icons'],

		'U_POST_NEW_TOPIC'  => ($auth->acl_get('u_sendpm')) ? append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'i=pm&amp;mode=compose') : '',
		'S_PM_ACTION'       => append_sid(PHPBB_ROOT_PATH . 'ucp.php', "i=pm&amp;mode=view&amp;action=view_folder&amp;f={$folder_id}" . (($start !== 0) ? "&amp;start={$start}" : '')),
	]);

	// Grab all pm data
	$rowset = $pm_list = [];

	// If the user is trying to reach late pages, start searching from the end
	$store_reverse = false;
	$sql_limit = $config['topics_per_page'];
	if ($start > $pm_count / 2)
	{
		$store_reverse = true;

		if ($start + $config['topics_per_page'] > $pm_count)
		{
			$sql_limit = min($config['topics_per_page'], max(1, $pm_count - $start));
		}

		// Select the sort order
		$direction = ($sort_dir == 'd') ? 'ASC' : 'DESC';
		$sql_start = max(0, $pm_count - $sql_limit - $start);
	}
	else
	{
		// Select the sort order
		$direction = ($sort_dir == 'd') ? 'DESC' : 'ASC';
		$sql_start = $start;
	}

	// Sql sort order
	if (is_array($sort_by_sql[$sort_key]))
	{
		$sql_sort_order = implode(' ' . $direction . ', ', $sort_by_sql[$sort_key]) . ' ' . $direction;
	}
	else
	{
		$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . $direction;
	}

	$sql = 'SELECT t.*, p.root_level, p.message_time, p.message_subject, p.icon_id, p.to_address, p.message_attachment, p.bcc_address, u.username, u.username_clean, u.user_colour, p.message_reported
		FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . " u
		WHERE t.user_id = {$user_id}
			AND p.author_id = u.user_id
			AND {$folder_sql}
			AND t.msg_id = p.msg_id
			{$sql_limit_time}
		ORDER BY {$sql_sort_order}";
	$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);

	$pm_reported = [];
	while ($row = $db->sql_fetchrow($result))
	{
		$rowset[$row['msg_id']] = $row;
		$pm_list[] = $row['msg_id'];
		if ($row['message_reported'])
		{
			$pm_reported[] = $row['msg_id'];
		}
	}
	$db->sql_freeresult($result);

	// Fetch the report_ids, if there are any reported pms.
	if (!empty($pm_reported) && $auth->acl_getf_global('m_report'))
	{
		$sql = 'SELECT pm_id, report_id
			FROM ' . REPORTS_TABLE . '
			WHERE report_closed = 0
				AND ' . $db->sql_in_set('pm_id', $pm_reported);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$rowset[$row['pm_id']]['report_id'] = $row['report_id'];
		}
		$db->sql_freeresult($result);
	}

	$pm_list = ($store_reverse) ? array_reverse($pm_list) : $pm_list;

	return [
		'pm_count'  => $pm_count,
		'pm_list'   => $pm_list,
		'rowset'    => $rowset
	];
}
