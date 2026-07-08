<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './'); }
require_once(PHPBB_ROOT_PATH . 'common.php');

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('memberlist');

// Get and set some variables
$mode       = request_var('mode', '');
$session_id = request_var('s', '');
$start      = request_var('start', 0);
$sort_key   = request_var('sk', 'b');
$sort_dir   = request_var('sd', 'd');
$show_guests= $auth->acl_get('u_viewonline') ? request_var('sg', 0) : 0;
$show_bots  = $auth->acl_get('u_viewonline') ? request_var('sb', 0) : 0;

// Can this user view profiles/memberlist?
if (!$auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('NO_VIEW_USERS');
	}

	login_box('', $user->lang['LOGIN_EXPLAIN_VIEWONLINE']);
}


// Sorting and order
$sort_key_sql = ['a' => 'COALESCE(u.username_clean, b.bot_name, \'\')', 'b' => 's.session_time'];
if (!isset($sort_key_sql[$sort_key])) { $sort_key = 'b'; }
$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

// Whois requested
if ($mode == 'whois' && $auth->acl_get('a_') && $session_id)
{
	require_once(PHPBB_ROOT_PATH . 'includes/functions_user.php');

	$sql = 'SELECT session_ip
		FROM ' . SESSIONS_TABLE . "
		WHERE session_id = '" . $db->sql_escape($session_id) . "'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$template->assign_var('WHOIS', user_ipwhois($row['session_ip']));
	}
	$db->sql_freeresult($result);

	// Output the page
	page_header($user->lang['WHO_IS_ONLINE']);

	$template->set_filenames(['body' => 'viewonline_whois.html']);

	page_footer();
}

$logged_bots_online = $guest_counter = 0;

// Get number of online guests (if we do not display them)
if (!$show_guests)
{
	$sql = 'SELECT COUNT(DISTINCT session_ip) as num_guests
		FROM ' . SESSIONS_TABLE . '
		WHERE session_user_id = ' . ANONYMOUS . '
			AND session_bot_id = 0
			AND session_time <> session_start
			AND session_time >= ' . (time() - ($config['load_online_time'] * 60));
	$result = $db->sql_query($sql);
	$guest_counter = (int) $db->sql_fetchfield('num_guests');
	$db->sql_freeresult($result);
}

// Get number of online bots (if we do not display them)
if (!$show_bots)
{
	$sql = 'SELECT COUNT(DISTINCT session_bot_id) as num_bots
		FROM ' . SESSIONS_TABLE . '
		WHERE session_bot_id <> 0
			AND session_time >= ' . (time() - ($config['load_online_time'] * 60));
	$result = $db->sql_query($sql);
	$logged_bots_online = (int) $db->sql_fetchfield('num_bots');
	$db->sql_freeresult($result);
}

// Get user list
$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_type, u.user_colour, b.bot_name, s.*
	FROM ' . SESSIONS_TABLE . ' s
	LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.session_user_id
	LEFT JOIN ' . BOTS_TABLE . ' b ON b.bot_id = s.session_bot_id
	WHERE s.session_time >= ' . (time() - ($config['load_online_time'] * 60)) .
		((!$show_bots) ? ' AND s.session_bot_id = 0' : '') .
		((!$show_guests) ? ' AND (s.session_user_id <> ' . ANONYMOUS . ' OR s.session_bot_id <> 0)' : '') . '
	ORDER BY ' . $order_by;
$result = $db->sql_query($sql);

$prev_id = $prev_ip = $prev_bot = $user_list = [];
$logged_visible_online = $logged_hidden_online = $counter = 0;

while ($row = $db->sql_fetchrow($result))
{
	if ($row['session_bot_id'])
	{
		if (!$show_bots || isset($prev_bot[$row['session_bot_id']]))
		{
			continue;
		}

		$logged_bots_online++;
		$counter++;
		$prev_bot[$row['session_bot_id']] = 1;

		if ($counter > $start + $config['topics_per_page'] || $counter <= $start)
		{
			continue;
		}

		$s_user_hidden = false;
		$row['username'] = $row['bot_name'];
		$row['user_colour'] = '';
		$row['user_type'] = USER_IGNORE;
		$username_full = '<span class="botname">' . $row['username'] . '</span>';
	}
	else if ($row['user_id'] != ANONYMOUS && !isset($prev_id[$row['user_id']]))
	{
		$view_online = $s_user_hidden = false;
		$user_colour = ($row['user_colour']) ? ' style="color:#' . $row['user_colour'] . '" class="username-coloured"' : '';

		$username_full = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

		if (!$row['session_viewonline'])
		{
			$view_online = (bool) $auth->acl_get('u_viewonline');
			$logged_hidden_online++;

			$username_full = '<em>' . $username_full . '</em>';
			$s_user_hidden = true;
		}
		else
		{
			$view_online = true;
			$logged_visible_online++;
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
	else if ($show_guests && $row['user_id'] == ANONYMOUS)
	{
		if ($row['session_time'] == $row['session_start'])
		{
			continue;
		}

		$guest_counter++;
		$counter++;

		if (isset($prev_ip[$row['session_ip']]))
		{
			continue;
		}

		$prev_ip[$row['session_ip']] = 1;

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

	$template->assign_block_vars('user_row', [
		'USERNAME'          => $row['username'],
		'USERNAME_COLOUR'   => $row['user_colour'],
		'USERNAME_FULL'     => $username_full,
		'LASTUPDATE'        => $user->format_date($row['session_time']),
		'USER_IP'           => ($auth->acl_get('a_user')) ? (($mode == 'lookup' && $session_id == $row['session_id']) ? gethostbyaddr($row['session_ip']) : $row['session_ip']) : '',
		'USER_BROWSER'      => ($auth->acl_get('a_user')) ? $row['session_browser_ua'] : '',

		'U_USER_PROFILE'    => ($row['user_type'] != USER_IGNORE) ? get_username_string('profile', $row['user_id'], '') : '',
		'U_USER_IP'         => append_sid(PHPBB_ROOT_PATH . 'viewonline.php', 'mode=lookup' . (($mode != 'lookup' || $row['session_id'] != $session_id) ? '&amp;s=' . $row['session_id'] : '') . "&amp;sg={$show_guests}&amp;sb={$show_bots}&amp;start={$start}&amp;sk={$sort_key}&amp;sd={$sort_dir}"),
		'U_WHOIS'           => append_sid(PHPBB_ROOT_PATH . 'viewonline.php', 'mode=whois&amp;s=' . $row['session_id']),

		'S_USER_HIDDEN'     => $s_user_hidden,
		'S_GUEST'           => ($row['user_id'] == ANONYMOUS && !$row['session_bot_id']),
		'S_USER_TYPE'       => $row['user_type'],
	]);
}
$db->sql_freeresult($result);
unset($prev_id, $prev_ip, $prev_bot);

$pagination = generate_pagination(append_sid(PHPBB_ROOT_PATH . 'viewonline.php', "sg={$show_guests}&amp;sb={$show_bots}&amp;sk={$sort_key}&amp;sd={$sort_dir}"), $counter, $config['topics_per_page'], $start);

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
	$legend .= (($legend != '') ? ', ' : '') . '<a style="color:#' . $row['group_colour'] . '" href="' . append_sid(PHPBB_ROOT_PATH . 'memberlist.php', 'mode=group&amp;g=' . $row['group_id']) . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</a>';
}
$db->sql_freeresult($result);

// Refreshing the page every 60 seconds...
meta_refresh(60);

// Send data to template
$template->assign_vars([
	'TOTAL_REGISTERED_USERS_ONLINE' => $user->lang('ONLINE_REG_USERS', $logged_visible_online + $logged_hidden_online) . ($logged_hidden_online ? (' (' . $user->lang('ONLINE_HIDDEN_USERS', $logged_hidden_online) . ')') : ''),
	'TOTAL_GUEST_USERS_ONLINE'      => $user->lang('ONLINE_GUEST_USERS', $guest_counter),
	'TOTAL_BOT_USERS_ONLINE'        => $user->lang('ONLINE_BOT_USERS', $logged_bots_online),
	'LEGEND'                        => $legend,
	'PAGINATION'                    => $pagination,
	'PAGE_NUMBER'                   => on_page($counter, $config['topics_per_page'], $start),

	'U_SORT_USERNAME'       => append_sid(PHPBB_ROOT_PATH . 'viewonline.php', 'sk=a&amp;sd=' . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests) . '&amp;sb=' . ((int) $show_bots)),
	'U_SORT_UPDATED'        => append_sid(PHPBB_ROOT_PATH . 'viewonline.php', 'sk=b&amp;sd=' . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a') . '&amp;sg=' . ((int) $show_guests) . '&amp;sb=' . ((int) $show_bots)),

	'U_SWITCH_GUEST_DISPLAY'    => append_sid(PHPBB_ROOT_PATH . 'viewonline.php', 'sg=' . ((int) !$show_guests) . '&amp;sb=' . ((int) $show_bots)),
	'L_SWITCH_GUEST_DISPLAY'    => $show_guests ? $user->lang['HIDE'] : $user->lang['DISPLAY'],
	'S_SWITCH_GUEST_DISPLAY'    => $auth->acl_get('u_viewonline'),

	'U_SWITCH_BOTS_DISPLAY'     => append_sid(PHPBB_ROOT_PATH . 'viewonline.php', 'sg=' . ((int) $show_guests) . '&amp;sb=' . ((int) !$show_bots)),
	'L_SWITCH_BOTS_DISPLAY'     => $show_bots ? $user->lang['HIDE'] : $user->lang['DISPLAY'],
	'S_SWITCH_BOTS_DISPLAY'     => $auth->acl_get('u_viewonline'),
]);

// We do not need to load the who is online box here. ;)
$config['load_online'] = false;

// Output the page
page_header($user->lang['WHO_IS_ONLINE']);

$template->set_filenames(['body' => 'viewonline_body.html']);

page_footer();
