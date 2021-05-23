<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('memberlist');

// Get and set some variables
$mode		= request_var('mode', '');
$session_id	= request_var('s', '');
$start		= request_var('start', 0);
$sort_key	= request_var('sk', 'b');
$sort_dir	= request_var('sd', 'd');
$show_guests= ($config['load_online_guests'] || $auth->acl_get('u_viewonline')) ? request_var('sg', 0) : 0;
$show_bots	= ($config['load_online_bots'] || $auth->acl_get('u_viewonline')) ? request_var('sb', 1) : 0;

// Can this user view profiles/memberlist?
if (!$auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('NO_VIEW_USERS');
	}

	login_box('', $user->lang['LOGIN_EXPLAIN_VIEWONLINE']);
}

$sort_key_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_JOINED'], 'c' => $user->lang['SORT_LOCATION']);
$sort_key_sql = array('a' => 'u.username_clean', 'b' => 's.session_time', 'c' => 's.session_page');

// Sorting and order
if (!isset($sort_key_text[$sort_key]))
{
	$sort_key = 'b';
}

$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

// Whois requested
if ($mode == 'whois' && $auth->acl_get('a_') && $session_id)
{
	include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

	$sql = 'SELECT u.user_id, u.username, u.user_type, s.session_ip
		FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . " s
		WHERE s.session_id = '" . $db->sql_escape($session_id) . "'
			AND	u.user_id = s.session_user_id";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$template->assign_var('WHOIS', user_ipwhois($row['session_ip']));
	}
	$db->sql_freeresult($result);

	// Output the page
	page_header($user->lang['WHO_IS_ONLINE']);

	$template->set_filenames(array(
		'body' => 'viewonline_whois.html')
	);
	make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

	page_footer();
}

// Forum info
$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
	FROM ' . FORUMS_TABLE . '
	ORDER BY left_id ASC';
$result = $db->sql_query($sql, 600);

$forum_data = array();
while ($row = $db->sql_fetchrow($result))
{
	$forum_data[$row['forum_id']] = $row;
}
$db->sql_freeresult($result);

$logged_bots_online = $guest_counter = 0;

// Get number of online guests (if we do not display them)
if (!$show_guests)
{
	$sql = 'SELECT COUNT(DISTINCT session_ip) as num_guests
		FROM ' . SESSIONS_TABLE . '
		WHERE session_user_id = ' . ANONYMOUS . '
			AND session_time >= ' . (time() - ($config['load_online_time'] * 60));
	$result = $db->sql_query($sql);
	$guest_counter = (int) $db->sql_fetchfield('num_guests');
	$db->sql_freeresult($result);
}

// Get number of online bots (if we do not display them)
if (!$show_bots)
{
	$sql = 'SELECT COUNT(DISTINCT session_user_id) as num_bots
		FROM ' . SESSIONS_TABLE . ' s
		LEFT JOIN ' . USERS_TABLE . ' u ON s.session_user_id = u.user_id
		WHERE session_user_id <> ' . ANONYMOUS . '
			AND u.user_type = ' . USER_IGNORE . '
			AND session_time >= ' . (time() - ($config['load_online_time'] * 60));
	$result = $db->sql_query($sql);
	$logged_bots_online = (int) $db->sql_fetchfield('num_bots');
	$db->sql_freeresult($result);
}

// Get user list
$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_type, u.user_colour, s.session_id, s.session_time, s.session_page, s.session_ip, s.session_browser, s.session_viewonline, s.session_forum_id, s.session_album_id
	FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . ' s
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ' . (time() - ($config['load_online_time'] * 60)) .
		((!$show_bots) ? ' AND (u.user_type <> ' . USER_IGNORE . ' OR s.session_user_id = ' . ANONYMOUS . ')' : '') .
		((!$show_guests) ? ' AND s.session_user_id <> ' . ANONYMOUS : '') . '
	ORDER BY ' . $order_by;
$result = $db->sql_query($sql);

$prev_id = $prev_ip = $user_list = array();
$logged_visible_online = $logged_hidden_online = $counter = 0;

while ($row = $db->sql_fetchrow($result))
{
	if ($row['user_id'] != ANONYMOUS && !isset($prev_id[$row['user_id']]))
	{
		$view_online = $s_user_hidden = false;
		$user_colour = ($row['user_colour']) ? ' style="color:#' . $row['user_colour'] . '" class="username-coloured"' : '';

		$username_full = ($row['user_type'] != USER_IGNORE) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : '<span' . $user_colour . '>' . $row['username'] . '</span>';

		if (!$row['session_viewonline'])
		{
			$view_online = ($auth->acl_get('u_viewonline')) ? true : false;
			$logged_hidden_online++;

			$username_full = '<em>' . $username_full . '</em>';
			$s_user_hidden = true;
		}
		else
		{
			if ($row['user_type'] != USER_IGNORE)
			{
				$view_online = true;
				$logged_visible_online++;
			}
			else if ($show_bots)
			{
				$view_online = true;
				$logged_bots_online++;
			}
			else
			{
				$view_online = false;
			}
		}

		$prev_id[$row['user_id']] = 1;

		if ($view_online)
		{
			$counter++;
		}

		if (!$view_online || $counter > $start + $config['topics_per_page'] || $counter <= $start)
		{
			continue;
		}
	}
	else if ($show_guests && $row['user_id'] == ANONYMOUS && !isset($prev_ip[$row['session_ip']]))
	{
		$prev_ip[$row['session_ip']] = 1;
		$guest_counter++;
		$counter++;

		if ($counter > $start + $config['topics_per_page'] || $counter <= $start)
		{
			continue;
		}

		$s_user_hidden = false;
		$username_full = get_username_string('full', $row['user_id'], $user->lang['GUEST']);
	}
	else
	{
		continue;
	}

	preg_match('#^([a-z0-9/_-]+)#i', $row['session_page'], $on_page);
	if (!sizeof($on_page))
	{
		$on_page[1] = '';
	}

	// phpBB Gallery integration
	if (class_exists('phpbb_gallery_integration'))
	{
		phpbb_gallery_integration::viewonline_pre_switch($on_page, $row['session_page']);
	}

	switch ($on_page[1])
	{
		case 'index':
			$location = $user->lang['INDEX'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case 'adm/index':
			$location = $user->lang['ACP'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case 'posting':
		case 'viewforum':
		case 'viewtopic':
			$forum_id = $row['session_forum_id'];

			if ($forum_id && $auth->acl_get('f_list', $forum_id))
			{
				$location = '';
				$location_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id);

				if ($forum_data[$forum_id]['forum_type'] == FORUM_LINK)
				{
					$location = sprintf($user->lang['READING_LINK'], $forum_data[$forum_id]['forum_name']);
					break;
				}

				switch ($on_page[1])
				{
					case 'posting':
						preg_match('#mode=([a-z]+)#', $row['session_page'], $on_page);
						$posting_mode = (!empty($on_page[1])) ? $on_page[1] : '';

						switch ($posting_mode)
						{
							case 'reply':
							case 'quote':
								$location = sprintf($user->lang['REPLYING_MESSAGE'], $forum_data[$forum_id]['forum_name']);
							break;

							default:
								$location = sprintf($user->lang['POSTING_MESSAGE'], $forum_data[$forum_id]['forum_name']);
							break;
						}
					break;

					case 'viewtopic':
						$location = sprintf($user->lang['READING_TOPIC'], $forum_data[$forum_id]['forum_name']);
					break;

					case 'viewforum':
						$location = sprintf($user->lang['READING_FORUM'], $forum_data[$forum_id]['forum_name']);
					break;
				}
			}
			else
			{
				$location = $user->lang['INDEX'];
				$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
			}
		break;

		case 'search':
			$location = $user->lang['SEARCHING_FORUMS'];
			$location_url = append_sid("{$phpbb_root_path}search.$phpEx");
		break;

		case 'faq':
			$location = $user->lang['VIEWING_FAQ'];
			$location_url = append_sid("{$phpbb_root_path}faq.$phpEx");
		break;

		case 'viewonline':
			$location = $user->lang['VIEWING_ONLINE'];
			$location_url = append_sid("{$phpbb_root_path}viewonline.$phpEx");
		break;

		case 'memberlist':
			$location = (strpos($row['session_page'], 'mode=viewprofile') !== false) ? $user->lang['VIEWING_MEMBER_PROFILE'] : $user->lang['VIEWING_MEMBERS'];
			$location_url = append_sid("{$phpbb_root_path}memberlist.$phpEx");
		break;

		case 'mcp':
			$location = $user->lang['VIEWING_MCP'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case 'ucp':
			$location = $user->lang['VIEWING_UCP'];

			// Grab some common modules
			$url_params = array(
				'mode=register'		=> 'VIEWING_REGISTER',
				'i=pm&mode=compose'	=> 'POSTING_PRIVATE_MESSAGE',
				'i=pm&'				=> 'VIEWING_PRIVATE_MESSAGES',
				'i=profile&'		=> 'CHANGING_PROFILE',
				'i=prefs&'			=> 'CHANGING_PREFERENCES',
			);

			foreach ($url_params as $param => $lang)
			{
				if (strpos($row['session_page'], $param) !== false)
				{
					$location = $user->lang[$lang];
					break;
				}
			}

			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case 'download/file':
			$location = $user->lang['DOWNLOADING_FILE'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		case 'report':
			$location = $user->lang['REPORTING_POST'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;

		// phpBB Gallery integration
		case phpbb_gallery_url::path('relative'):
			if (class_exists('phpbb_gallery_integration'))
			{
				phpbb_gallery_integration::viewonline($row['session_album_id']);
			}
		break;

		default:
			$location = $user->lang['INDEX'];
			$location_url = append_sid("{$phpbb_root_path}index.$phpEx");
		break;
	}

	$template->assign_block_vars('user_row', array(
		'USERNAME' 			=> $row['username'],
		'USERNAME_COLOUR'	=> $row['user_colour'],
		'USERNAME_FULL'		=> $username_full,
		'LASTUPDATE'		=> $user->format_date($row['session_time']),
		'FORUM_LOCATION'	=> $location,
		'USER_IP'			=> ($auth->acl_get('a_')) ? (($mode == 'lookup' && $session_id == $row['session_id']) ? gethostbyaddr($row['session_ip']) : $row['session_ip']) : '',
		'USER_BROWSER'		=> ($auth->acl_get('a_user')) ? $row['session_browser'] : '',

		'U_USER_PROFILE'	=> ($row['user_type'] != USER_IGNORE) ? get_username_string('profile', $row['user_id'], '') : '',
		'U_USER_IP'			=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'mode=lookup' . (($mode != 'lookup' || $row['session_id'] != $session_id) ? '&amp;s=' . $row['session_id'] : '') . "&amp;sg=$show_guests&amp;sb=$show_bots&amp;start=$start&amp;sk=$sort_key&amp;sd=$sort_dir"),
		'U_WHOIS'			=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'mode=whois&amp;s=' . $row['session_id']),
		'U_FORUM_LOCATION'	=> $location_url,

		'S_USER_HIDDEN'		=> $s_user_hidden,
		'S_GUEST'			=> ($row['user_id'] == ANONYMOUS) ? true : false,
		'S_USER_TYPE'		=> $row['user_type'],
	));
}
$db->sql_freeresult($result);
unset($prev_id, $prev_ip);

$pagination = generate_pagination(append_sid("{$phpbb_root_path}viewonline.$phpEx", "sg=$show_guests&amp;sb=$show_bots&amp;sk=$sort_key&amp;sd=$sort_dir"), $counter, $config['topics_per_page'], $start);

// Grab group details for legend display
if ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
{
	$sql = 'SELECT group_id, group_name, group_colour, group_type
		FROM ' . GROUPS_TABLE . '
		WHERE group_legend = 1
		ORDER BY group_name ASC';
}
else
{
	$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type
		FROM ' . GROUPS_TABLE . ' g
		LEFT JOIN ' . USER_GROUP_TABLE . ' ug
			ON (
				g.group_id = ug.group_id
				AND ug.user_id = ' . $user->data['user_id'] . '
				AND ug.user_pending = 0
			)
		WHERE g.group_legend = 1
			AND (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $user->data['user_id'] . ')
		ORDER BY g.group_name ASC';
}
$result = $db->sql_query($sql);

$legend = '';
while ($row = $db->sql_fetchrow($result))
{
	if ($row['group_name'] == 'BOTS')
	{
		$legend .= (($legend != '') ? ', ' : '') . '<span style="color:#' . $row['group_colour'] . '">' . $user->lang['G_BOTS'] . '</span>';
	}
	else
	{
		$legend .= (($legend != '') ? ', ' : '') . '<a style="color:#' . $row['group_colour'] . '" href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</a>';
	}
}
$db->sql_freeresult($result);

// Refreshing the page every 60 seconds...
meta_refresh(60, append_sid("{$phpbb_root_path}viewonline.$phpEx", "sg=$show_guests&amp;sb=$show_bots&amp;sk=$sort_key&amp;sd=$sort_dir&amp;start=$start"));

// Send data to template
$template->assign_vars(array(
	'TOTAL_REGISTERED_USERS_ONLINE'	=> $user->lang('ONLINE_REG_USERS', $logged_visible_online + $logged_hidden_online) . ($logged_hidden_online ? (' (' . $user->lang('ONLINE_HIDDEN_USERS', $logged_hidden_online) . ')') : ''),
	'TOTAL_GUEST_USERS_ONLINE'		=> $user->lang('ONLINE_GUEST_USERS', $guest_counter),
	'TOTAL_BOT_USERS_ONLINE'		=> $user->lang('ONLINE_BOT_USERS', $logged_bots_online),
	'LEGEND'						=> $legend,
	'PAGINATION'					=> $pagination,
	'PAGE_NUMBER'					=> on_page($counter, $config['topics_per_page'], $start),

	'U_SORT_USERNAME'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sk=a&amp;sd=' . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests) . '&amp;sb=' . ((int) $show_bots)),
	'U_SORT_UPDATED'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sk=b&amp;sd=' . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests) . '&amp;sb=' . ((int) $show_bots)),
	'U_SORT_LOCATION'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sk=c&amp;sd=' . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests) . '&amp;sb=' . ((int) $show_bots)),

	'U_SWITCH_GUEST_DISPLAY'	=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sg=' . ((int) !$show_guests) . '&amp;sb=' . ((int) $show_bots)),
	'L_SWITCH_GUEST_DISPLAY'	=> $show_guests ? $user->lang['HIDE'] : $user->lang['DISPLAY'],
	'S_SWITCH_GUEST_DISPLAY'	=> $config['load_online_guests'] || $auth->acl_get('u_viewonline'),

	'U_SWITCH_BOTS_DISPLAY'		=> append_sid("{$phpbb_root_path}viewonline.$phpEx", 'sg=' . ((int) $show_guests) . '&amp;sb=' . ((int) !$show_bots)),
	'L_SWITCH_BOTS_DISPLAY'		=> $show_bots ? $user->lang['HIDE'] : $user->lang['DISPLAY'],
	'S_SWITCH_BOTS_DISPLAY'		=> $config['load_online_bots'] || $auth->acl_get('u_viewonline'),
));

// We do not need to load the who is online box here. ;)
$config['load_online'] = false;

// Output the page
page_header($user->lang['WHO_IS_ONLINE']);

$template->set_filenames(array(
	'body' => 'viewonline_body.html')
);
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

page_footer();
