<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './'); }
require_once(PHPBB_ROOT_PATH . 'common.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_display.php');
require_once(PHPBB_ROOT_PATH . 'includes/bbcode.php');

// Start session management
$user->session_begin();
$auth->acl($user->data);

// Initial var setup
$forum_id	= request_var('f', 0);
$topic_id	= request_var('t', 0);
$post_id	= request_var('p', 0);

$start		= request_var('start', 0);
$view		= request_var('view', '');

$default_sort_days	= 0;
$default_sort_key	= $user->data['user_post_sortby_type'] ?: 't';
$default_sort_dir	= $user->data['user_post_sortby_dir'] ?: 'a';

$sort_days	= request_var('st', $default_sort_days);
$sort_key	= request_var('sk', $default_sort_key);
$sort_dir	= request_var('sd', $default_sort_dir);

/**
* @todo normalize?
*/
$hilit_words = empty($config['search_highlight_keywords']) ? '' : request_var('hilit', '', true);

// Do we have a topic or post id?
if (!$topic_id && !$post_id)
{
	trigger_error('NO_TOPIC');
}

// Find first unread post if requested.
if ($view && !$post_id)
{
	if (!$forum_id)
	{
		$sql = 'SELECT forum_id
			FROM ' . TOPICS_TABLE . "
			WHERE topic_id = $topic_id";
		$result = $db->sql_query($sql);
		$forum_id = (int) $db->sql_fetchfield('forum_id');
		$db->sql_freeresult($result);

		if (!$forum_id)
		{
			trigger_error('NO_TOPIC');
		}
	}

	if ($view == 'unread')
	{
		// Get topic tracking info
		$topic_tracking_info = get_complete_topic_tracking($forum_id, $topic_id);

		$topic_last_read = $topic_tracking_info[$topic_id] ?? 0;

		$sql = 'SELECT post_id, topic_id, forum_id
			FROM ' . POSTS_TABLE . "
			WHERE topic_id = $topic_id
				" . (($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND post_approved = 1') . "
				AND (post_time > $topic_last_read OR post_merged > $topic_last_read)
				AND forum_id = $forum_id
			ORDER BY post_time ASC";
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			$sql = 'SELECT topic_last_post_id as post_id, topic_id, forum_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . $topic_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		if (!$row)
		{
			// Setup user environment so we can process lang string
			$user->setup('viewtopic');

			trigger_error('NO_TOPIC');
		}

		$post_id = $row['post_id'];
		$topic_id = $row['topic_id'];
		$forum_id = $row['forum_id'];
	}
}

// This rather complex gaggle of code handles querying for topics but
// also allows for direct linking to a post (and the calculation of which
// page the post is on and the correct display of viewtopic)
$sql_array = [
	'SELECT'	=> 't.*, f.*',

	'FROM'		=> [FORUMS_TABLE => 'f'],
];

// The FROM-Order is quite important here, else t.* columns can not be correctly bound.
if ($post_id)
{
	$sql_array['SELECT'] .= ', p.post_approved, p.post_time, p.post_id';
	$sql_array['FROM'][POSTS_TABLE] = 'p';
}

// Topics table need to be the last in the chain
$sql_array['FROM'][TOPICS_TABLE] = 't';

if ($user->data['is_registered'])
{
	$sql_array['SELECT'] .= ', tw.notify_status';
	$sql_array['LEFT_JOIN'] = [];

	$sql_array['LEFT_JOIN'][] = [
		'FROM'	=> [TOPICS_WATCH_TABLE => 'tw'],
		'ON'	=> 'tw.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = tw.topic_id'
	];

	if ($config['allow_bookmarks'])
	{
		$sql_array['SELECT'] .= ', bm.topic_id as bookmarked';
		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [BOOKMARKS_TABLE => 'bm'],
			'ON'	=> 'bm.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = bm.topic_id'
		];
	}

	if ($config['load_db_lastread'])
	{
		$sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time as forum_mark_time';

		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [TOPICS_TRACK_TABLE => 'tt'],
			'ON'	=> 'tt.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = tt.topic_id'
		];

		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [FORUMS_TRACK_TABLE => 'ft'],
			'ON'	=> 'ft.user_id = ' . $user->data['user_id'] . ' AND t.forum_id = ft.forum_id'
		];
	}
}

if (!$post_id)
{
	$sql_array['WHERE'] = "t.topic_id = $topic_id";
}
else
{
	$sql_array['WHERE'] = "p.post_id = $post_id AND t.topic_id = p.topic_id";
}

$sql_array['WHERE'] .= ' AND f.forum_id = t.forum_id';

$sql = $db->sql_build_query('SELECT', $sql_array);
$result = $db->sql_query($sql);
$topic_data = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

// link to unapproved post or incorrect link
if (!$topic_data)
{
	// If post_id was submitted, we try at least to display the topic as a last resort...
	if ($post_id && $topic_id)
	{
		redirect(append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id"));
	}

	trigger_error('NO_TOPIC');
}

$forum_id = (int) $topic_data['forum_id'];
// This is for determining where we are (page)
if ($post_id)
{
	// are we where we are supposed to be?
	if (!$topic_data['post_approved'] && !$auth->acl_get('m_approve', $topic_data['forum_id']))
	{
		// If post_id was submitted, we try at least to display the topic as a last resort...
		if ($topic_id)
		{
			redirect(append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id"));
		}

		trigger_error('NO_TOPIC');
	}
	if ($post_id == $topic_data['topic_first_post_id'] || $post_id == $topic_data['topic_last_post_id'])
	{
		$check_sort = ($post_id == $topic_data['topic_first_post_id']) ? 'd' : 'a';

		if ($sort_dir == $check_sort)
		{
			$topic_data['prev_posts'] = ($auth->acl_get('m_approve', $forum_id)) ? $topic_data['topic_replies_real'] : $topic_data['topic_replies'];
		}
		else
		{
			$topic_data['prev_posts'] = 0;
		}
	}
	else
	{
		$sql = 'SELECT COUNT(p.post_id) AS prev_posts
			FROM ' . POSTS_TABLE . " p
			WHERE p.topic_id = {$topic_data['topic_id']}
				" . ((!$auth->acl_get('m_approve', $forum_id)) ? 'AND p.post_approved = 1' : '');

		if ($sort_dir == 'd')
		{
			$sql .= " AND (p.post_time > {$topic_data['post_time']} OR (p.post_time = {$topic_data['post_time']} AND p.post_id >= {$topic_data['post_id']}))";
		}
		else
		{
			$sql .= " AND (p.post_time < {$topic_data['post_time']} OR (p.post_time = {$topic_data['post_time']} AND p.post_id <= {$topic_data['post_id']}))";
		}

		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$topic_data['prev_posts'] = $row['prev_posts'] - 1;
	}
}

$topic_id = (int) $topic_data['topic_id'];
//
$topic_replies = ($auth->acl_get('m_approve', $forum_id)) ? $topic_data['topic_replies_real'] : $topic_data['topic_replies'];

// Check sticky/announcement time limit
if ($topic_data['topic_type'] != POST_NORMAL && $topic_data['topic_time_limit'] && ($topic_data['topic_time'] + $topic_data['topic_time_limit']) < time())
{
	$sql = 'UPDATE ' . TOPICS_TABLE . '
		SET topic_type = ' . POST_NORMAL . ', topic_time_limit = 0
		WHERE topic_id = ' . $topic_id;
	$db->sql_query($sql);

	$topic_data['topic_type'] = POST_NORMAL;
	$topic_data['topic_time_limit'] = 0;
}

// Setup look and feel
$user->setup('viewtopic');

if (!$topic_data['topic_approved'] && !$auth->acl_get('m_approve', $forum_id))
{
	trigger_error('NO_TOPIC');
}

// Start auth check
if (!$auth->acl_get('f_read', $forum_id))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('SORRY_AUTH_READ');
	}

	login_box('', $user->lang['LOGIN_EXPLAIN_VIEWFORUM']);
}

// Forum is passworded ... check whether access has been granted to this
// user this session, if not show login box
if ($topic_data['forum_password'])
{
	login_forum_box($topic_data);
}

// Redirect to login or to the correct post upon emailed notification links
if (isset($_GET['e']))
{
	$jump_to = request_var('e', 0);

	$redirect_url = append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id");

	if ($user->data['user_id'] == ANONYMOUS)
	{
		login_box($redirect_url . "&amp;p=$post_id&amp;e=$jump_to", $user->lang['LOGIN_NOTIFY_TOPIC']);
	}

	if ($jump_to > 0)
	{
		// We direct the already logged in user to the correct post...
		redirect($redirect_url . ((!$post_id) ? "&amp;p=$jump_to" : "&amp;p=$post_id") . "#p$jump_to");
	}
}

// Recalc start position, should be after $user->setup()
if ($start)
{
	$start = floor($start / $config['posts_per_page']) * $config['posts_per_page'];
}

// What is start equal to?
if ($post_id)
{
	$start = floor(($topic_data['prev_posts']) / $config['posts_per_page']) * $config['posts_per_page'];
}

// Get topic tracking info
if (!isset($topic_tracking_info))
{
	$topic_tracking_info = [];

	// Get topic tracking info
	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		$tmp_topic_data = [$topic_id => $topic_data];
		$topic_tracking_info = get_topic_tracking($forum_id, $topic_id, $tmp_topic_data, [$forum_id => $topic_data['forum_mark_time']]);
		unset($tmp_topic_data);
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		$topic_tracking_info = get_complete_topic_tracking($forum_id, $topic_id);
	}
}

// Post ordering options
$limit_days = [0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']];

$sort_by_text = ['t' => $user->lang['POST_TIME'], 'a' => $user->lang['AUTHOR'], 's' => $user->lang['SUBJECT']];
$sort_by_sql = ['t' => 'p.post_time', 'a' => ['u.username_clean', 'p.post_id'], 's' => ['p.post_subject', 'p.post_id']];
$join_user_sql = ['a' => true, 't' => false, 's' => false];

$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';

gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param, $default_sort_days, $default_sort_key, $default_sort_dir);

// Obtain correct post count and ordering SQL if user has
// requested anything different
if ($sort_days)
{
	$min_post_time = time() - ($sort_days * 86400);

	$sql = 'SELECT COUNT(post_id) AS num_posts
		FROM ' . POSTS_TABLE . "
		WHERE topic_id = $topic_id
			AND post_time >= $min_post_time
		" . (($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND post_approved = 1');
	$result = $db->sql_query($sql);
	$total_posts = (int) $db->sql_fetchfield('num_posts');
	$db->sql_freeresult($result);

	$limit_posts_time = "AND p.post_time >= $min_post_time ";

	if (isset($_POST['sort']))
	{
		$start = 0;
	}
}
else
{
	$total_posts = $topic_replies + 1;
	$limit_posts_time = '';
}

// Was a highlight request part of the URI?
$highlight_match = $highlight = '';
if ($hilit_words)
{
	$highlight_match = phpbb_clean_search_string($hilit_words);
	$highlight = urlencode($highlight_match);
	$highlight_match = str_replace('\*', '\w+?', preg_quote($highlight_match, '#'));
	$highlight_match = preg_replace('#(?<=^|\s)\\\\w\*\?(?=\s|$)#', '\w+?', $highlight_match);
	$highlight_match = str_replace(' ', '|', $highlight_match);
}

// Make sure $start is set to the last page if it exceeds the amount
if ($start < 0 || $start >= $total_posts)
{
	$start = ($start < 0) ? 0 : floor(($total_posts - 1) / $config['posts_per_page']) * $config['posts_per_page'];
}

// General Viewtopic URL for return links
$viewtopic_url = append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start") . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '') . (($highlight_match) ? "&amp;hilit=$highlight" : ''));

// Are we watching this topic?
$s_watching_topic = [
	'link'			=> '',
	'title'			=> '',
	'is_watching'	=> false,
];

if (($config['email_enable'] || $config['jab_enable']) && $config['allow_topic_notify'])
{
	$notify_status = $topic_data['notify_status'] ?? null;
	watch_topic_forum('topic', $s_watching_topic, $user->data['user_id'], $forum_id, $topic_id, $notify_status, $start, $topic_data['topic_title']);

	// Reset forum notification if forum notify is set
	if ($config['allow_forum_notify'])
	{
		$s_watching_forum = $s_watching_topic;
		watch_topic_forum('forum', $s_watching_forum, $user->data['user_id'], $forum_id, 0);
	}
}

// Bookmarks
if ($config['allow_bookmarks'] && $user->data['is_registered'] && request_var('bookmark', 0))
{
	if (check_link_hash(request_var('hash', ''), "topic_$topic_id"))
	{
		if (!$topic_data['bookmarked'])
		{
			$sql = 'INSERT INTO ' . BOOKMARKS_TABLE . ' ' . $db->sql_build_array('INSERT', [
				'user_id'	=> $user->data['user_id'],
				'topic_id'	=> $topic_id,
			]);
			$db->sql_query($sql);
		}
		else
		{
			$sql = 'DELETE FROM ' . BOOKMARKS_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND topic_id = $topic_id";
			$db->sql_query($sql);
		}
		if (!empty($config['skip_typical_notices']))
		{
			redirect($viewtopic_url);
		}
		$message = (($topic_data['bookmarked']) ? $user->lang['BOOKMARK_REMOVED'] : $user->lang['BOOKMARK_ADDED']) . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $viewtopic_url . '">', '</a>');
	}
	else
	{
		$message = $user->lang['BOOKMARK_ERR'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $viewtopic_url . '">', '</a>');
	}
	meta_refresh(3, $viewtopic_url);

	trigger_error($message);
}

// Grab icons
$icons = $cache->obtain_icons();

// Grab extensions
$extensions = [];
if ($topic_data['topic_attachment'])
{
	$extensions = $cache->obtain_attach_extensions($forum_id);
}

// Forum rules listing
$s_forum_rules = '';
gen_forum_auth_level('topic', $forum_id, $topic_data['forum_status']);

// Quick mod tools
$allow_change_type = ($auth->acl_get('m_', $forum_id) || ($user->data['is_registered'] && $user->data['user_id'] == $topic_data['topic_poster']));

$mod_action = append_sid(PHPBB_ROOT_PATH . 'mcp.php', "f=$forum_id&amp;t=$topic_id&amp;quickmod=1&amp;redirect=" . urlencode(str_replace('&amp;', '&', $viewtopic_url)), true, $user->session_id);
$mod_lock = ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && $user->data['user_id'] == $topic_data['topic_poster'] && $topic_data['topic_status'] == ITEM_UNLOCKED)) ? (($topic_data['topic_status'] == ITEM_UNLOCKED) ? 'lock' : 'unlock') : false;

$topic_mod = '';
$topic_mod .= ($auth->acl_get('m_move', $forum_id) && $topic_data['topic_status'] != ITEM_MOVED) ? '<option value="move">' . $user->lang['MOVE_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_split', $forum_id)) ? '<option value="split">' . $user->lang['SPLIT_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_merge', $forum_id)) ? '<option value="merge">' . $user->lang['MERGE_POSTS'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_merge', $forum_id)) ? '<option value="merge_topic">' . $user->lang['MERGE_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_move', $forum_id)) ? '<option value="fork">' . $user->lang['FORK_TOPIC'] . '</option>' : '';
$topic_mod .= ($allow_change_type && $auth->acl_gets('f_sticky', 'f_announce', $forum_id) && $topic_data['topic_type'] != POST_NORMAL) ? '<option value="make_normal">' . $user->lang['MAKE_NORMAL'] . '</option>' : '';
$topic_mod .= ($allow_change_type && $auth->acl_get('f_sticky', $forum_id) && $topic_data['topic_type'] != POST_STICKY) ? '<option value="make_sticky">' . $user->lang['MAKE_STICKY'] . '</option>' : '';
$topic_mod .= ($allow_change_type && $auth->acl_get('f_announce', $forum_id) && $topic_data['topic_type'] != POST_ANNOUNCE) ? '<option value="make_announce">' . $user->lang['MAKE_ANNOUNCE'] . '</option>' : '';
$topic_mod .= ($allow_change_type && $auth->acl_get('f_announce', $forum_id) && $topic_data['topic_type'] != POST_GLOBAL) ? '<option value="make_global">' . $user->lang['MAKE_GLOBAL'] . '</option>' : '';
$topic_mod .= ($mod_lock!==false) ? (($topic_data['topic_status'] == ITEM_UNLOCKED) ? '<option value="lock">' . $user->lang['LOCK_TOPIC'] . '</option>' : '<option value="unlock">' . $user->lang['UNLOCK_TOPIC'] . '</option>') : '';
$topic_mod .= ($auth->acl_get('m_delete', $forum_id)) ? '<option value="delete_topic">' . $user->lang['DELETE_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_', $forum_id)) ? '<option value="topic_logs">' . $user->lang['VIEW_TOPIC_LOGS'] . '</option>' : '';

// If we've got a hightlight set pass it on to pagination.
$pagination = generate_pagination(append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id" . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '') . (($highlight_match) ? "&amp;hilit=$highlight" : '')), $total_posts, $config['posts_per_page'], $start);

// Navigation links
generate_forum_nav($topic_data);

// Forum Rules
generate_forum_rules($topic_data);

// Moderators
$forum_moderators = [];
get_moderators($forum_moderators, $forum_id);

// This is only used for print view so ...
$server_path = (!$view) ? PHPBB_ROOT_PATH : generate_board_url() . '/';

// Replace naughty words in title
$topic_data['topic_title'] = censor_text($topic_data['topic_title']);

$s_search_hidden_fields = [
	't' => $topic_id,
	'sf' => 'msgonly',
];

if (!empty($_EXTRA_URL))
{
	foreach ($_EXTRA_URL as $url_param)
	{
		$url_param = explode('=', $url_param, 2);
		$s_search_hidden_fields[$url_param[0]] = $url_param[1];
	}
}

// Send vars to template
$template->assign_vars([
	'FORUM_ID' 		=> $forum_id,
	'FORUM_NAME' 	=> $topic_data['forum_name'],
	'FORUM_DESC'	=> generate_text_for_display($topic_data['forum_desc'], $topic_data['forum_desc_uid'], $topic_data['forum_desc_bitfield'], $topic_data['forum_desc_options']),
	'TOPIC_ID' 		=> $topic_id,
	'TOPIC_TITLE' 	=> $topic_data['topic_title'],
	'TOPIC_POSTER'	=> $topic_data['topic_poster'],

	'TOPIC_AUTHOR_FULL'		=> get_username_string('full', $topic_data['topic_poster'], $topic_data['topic_first_poster_name'], $topic_data['topic_first_poster_colour']),
	'TOPIC_AUTHOR_COLOUR'	=> get_username_string('colour', $topic_data['topic_poster'], $topic_data['topic_first_poster_name'], $topic_data['topic_first_poster_colour']),
	'TOPIC_AUTHOR'			=> get_username_string('username', $topic_data['topic_poster'], $topic_data['topic_first_poster_name'], $topic_data['topic_first_poster_colour']),

	'PAGINATION' 	=> $pagination,
	'PAGE_NUMBER' 	=> on_page($total_posts, $config['posts_per_page'], $start),
	'TOTAL_POSTS'	=> ($total_posts == 1) ? $user->lang['VIEW_TOPIC_POST'] : sprintf($user->lang['VIEW_TOPIC_POSTS'], $total_posts),
	'U_MCP_FORUM'	=> ($auth->acl_get('m_', $forum_id)) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', "i=main&amp;mode=forum_view&amp;f=$forum_id", true, $user->session_id) : '',
	'U_MCP_TOPIC'	=> ($auth->acl_get('m_', $forum_id)) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', "i=main&amp;mode=topic_view&amp;f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start") . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : ''), true, $user->session_id) : '',
	'MODERATORS'	=> (isset($forum_moderators[$forum_id]) && sizeof($forum_moderators[$forum_id])) ? implode(', ', $forum_moderators[$forum_id]) : '',

	'POST_IMG' 			=> ($topic_data['forum_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', 'FORUM_LOCKED') : $user->img('button_topic_new', 'POST_NEW_TOPIC'),
	'QUOTE_IMG' 		=> $user->img('icon_post_quote', 'REPLY_WITH_QUOTE'),
	'REPLY_IMG'			=> ($topic_data['forum_status'] == ITEM_LOCKED || $topic_data['topic_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', 'TOPIC_LOCKED') : $user->img('button_topic_reply', 'REPLY_TO_TOPIC'),
	'EDIT_IMG' 			=> $user->img('icon_post_edit', 'EDIT_POST'),
	'DELETE_IMG' 		=> $user->img('icon_post_delete', 'DELETE_POST'),
	'INFO_IMG' 			=> $user->img('icon_post_info', 'VIEW_INFO'),
	'PROFILE_IMG'		=> $user->img('icon_user_profile', 'READ_PROFILE'),
	'SEARCH_IMG' 		=> $user->img('icon_user_search', 'SEARCH_USER_POSTS'),
	'PM_IMG' 			=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
	'EMAIL_IMG' 		=> $user->img('icon_contact_email', 'SEND_EMAIL'),
	'WWW_IMG' 			=> $user->img('icon_contact_www', 'VISIT_WEBSITE'),
	'JABBER_IMG'		=> $user->img('icon_contact_jabber', 'JABBER'),
	'TELEGRAM_IMG'		=> $user->img('icon_contact_telegram', 'TELEGRAM'),
	'GALLERY_IMG'		=> $user->img('icon_contact_gallery', 'PERSONAL_ALBUM'),
	'REPORT_IMG'		=> $user->img('icon_post_report', 'REPORT_POST'),
	'REPORTED_IMG'		=> $user->img('icon_topic_reported', 'POST_REPORTED'),
	'UNAPPROVED_IMG'	=> $user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
	'WARN_IMG'			=> $user->img('icon_user_warn', 'WARN_USER'),

	'S_IS_LOCKED'			=> ($topic_data['topic_status'] != ITEM_UNLOCKED || $topic_data['forum_status'] != ITEM_UNLOCKED),
	'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
	'S_SELECT_SORT_KEY' 	=> $s_sort_key,
	'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
	'S_SINGLE_MODERATOR'	=> count($forum_moderators[$forum_id] ?? []) == 1,
	'S_TOPIC_ACTION' 		=> append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start")),
	'S_TOPIC_MOD' 			=> ($topic_mod != '') ? '<select name="action" id="quick-mod-select">' . $topic_mod . '</select>' : '',
	'S_MOD_ACTION' 			=> $mod_action,
	'U_LOCK_TOPIC'			=> ($mod_lock ? ($mod_action . "&action=" . $mod_lock) : false),

	'S_VIEWTOPIC'			=> true,
	'S_DISPLAY_SEARCHBOX'	=> ($auth->acl_get('u_search') && $auth->acl_get('f_search', $forum_id) && $config['load_search']),
	'S_SEARCHBOX_ACTION'	=> append_sid(PHPBB_ROOT_PATH . 'search.php'),
	'S_SEARCH_LOCAL_HIDDEN_FIELDS'	=> build_hidden_fields($s_search_hidden_fields),

	'S_DISPLAY_POST_INFO'	=> ($topic_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS)),
	'S_DISPLAY_REPLY_INFO'	=> ($topic_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_reply', $forum_id) || $user->data['user_id'] == ANONYMOUS)),
	'S_ENABLE_FEEDS_TOPIC'	=> ($config['feed_topic'] && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $topic_data['forum_options'])),
	'S_RATE_ENABLED'		=> $config['rate_enabled'] && (!$config['rate_no_negative'] || !$config['rate_no_positive']),

	'U_CANONICAL'			=> generate_board_url() . "/viewtopic.php?t=$topic_id" . (($start) ? "&amp;start=$start" : ''),
	'U_TOPIC'				=> "{$server_path}viewtopic.php?t=$topic_id",
	'U_FORUM'				=> $server_path,
	'U_VIEW_TOPIC' 			=> $viewtopic_url,
	'U_VIEW_FORUM' 			=> append_sid(PHPBB_ROOT_PATH . 'viewforum.php', 'f=' . $forum_id),
	'U_PRINT_TOPIC'			=> $viewtopic_url . '&amp;view=print',
	'U_EMAIL_TOPIC'			=> ($config['email_enable'] && $config['board_email_form'] && $user->data['is_registered'] && $auth->acl_get('u_sendemail')) ? append_sid(PHPBB_ROOT_PATH . 'memberlist.php', "mode=email&amp;t=$topic_id") : '',

	'U_WATCH_TOPIC' 		=> $s_watching_topic['link'],
	'L_WATCH_TOPIC' 		=> $s_watching_topic['title'],
	'S_WATCHING_TOPIC'		=> $s_watching_topic['is_watching'],

	'U_BOOKMARK_TOPIC'		=> ($user->data['is_registered'] && $config['allow_bookmarks']) ? $viewtopic_url . '&amp;bookmark=1&amp;hash=' . generate_link_hash("topic_$topic_id") : '',
	'L_BOOKMARK_TOPIC'		=> ($user->data['is_registered'] && $config['allow_bookmarks'] && $topic_data['bookmarked']) ? $user->lang['BOOKMARK_TOPIC_REMOVE'] : $user->lang['BOOKMARK_TOPIC'],
	'S_BOOKMARKED_TOPIC'	=> ($user->data['is_registered'] && $config['allow_bookmarks'] && $topic_data['bookmarked']),

	'U_POST_NEW_TOPIC' 		=> ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS) ? append_sid(PHPBB_ROOT_PATH . 'posting.php', "mode=post&amp;f=$forum_id") : '',
	'U_POST_REPLY_TOPIC' 	=> ($auth->acl_get('f_reply', $forum_id) || $user->data['user_id'] == ANONYMOUS) ? append_sid(PHPBB_ROOT_PATH . 'posting.php', "mode=reply&amp;f=$forum_id&amp;t=$topic_id") : '',
	'U_BUMP_TOPIC'			=> (bump_topic_allowed($forum_id, $topic_data['topic_bumped'], $topic_data['topic_last_post_time'], $topic_data['topic_poster'], $topic_data['topic_last_poster_id'])) ? append_sid(PHPBB_ROOT_PATH . 'posting.php', "mode=bump&amp;f=$forum_id&amp;t=$topic_id&amp;hash=" . generate_link_hash("topic_$topic_id")) : '']
);

// Does this topic contain a poll?
$s_can_vote = false;
if (!empty($topic_data['poll_start']))
{
	$sql = 'SELECT o.*, p.bbcode_bitfield, p.bbcode_uid
		FROM ' . POLL_OPTIONS_TABLE . ' o, ' . POSTS_TABLE . " p
		WHERE o.topic_id = $topic_id
			AND p.post_id = {$topic_data['topic_first_post_id']}
			AND p.topic_id = o.topic_id
		ORDER BY o.poll_option_id";
	$result = $db->sql_query($sql);

	$poll_info = [];
	while ($row = $db->sql_fetchrow($result))
	{
		$poll_info[] = $row;
	}
	$db->sql_freeresult($result);

	$cur_voted_id = [];
	if ($user->data['is_registered'])
	{
		$sql = 'SELECT poll_option_id
			FROM ' . POLL_VOTES_TABLE . '
			WHERE topic_id = ' . $topic_id . '
				AND vote_user_id = ' . $user->data['user_id'];
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$cur_voted_id[] = $row['poll_option_id'];
		}
		$db->sql_freeresult($result);
	}

	// Can not vote at all if no vote permission
	$s_can_vote = $user->data['is_registered'] && $auth->acl_get('f_vote', $forum_id)
		&& (($topic_data['poll_length'] != 0 && $topic_data['poll_start'] + $topic_data['poll_length'] > time()) || $topic_data['poll_length'] == 0)
		&& $topic_data['topic_status'] != ITEM_LOCKED
		&& $topic_data['forum_status'] != ITEM_LOCKED
		&& (!sizeof($cur_voted_id) || ($auth->acl_get('f_votechg', $forum_id) && $topic_data['poll_vote_change']));
	$s_display_results = (!$s_can_vote || ($s_can_vote && sizeof($cur_voted_id)) || $view == 'viewpoll');

	$update = request_var('update', false);
	$unvote = request_var('unvote', false);

	if ($s_can_vote && ($update || $unvote))
	{
		$voted_id	= request_var('vote_id', ['' => 0]);
		$voted_id = (sizeof($voted_id) > 1) ? array_unique($voted_id) : $voted_id;

		if (!$unvote && (!sizeof($voted_id) || sizeof($voted_id) > $topic_data['poll_max_options']) || in_array(VOTE_CONVERTED, $cur_voted_id) || !check_form_key('posting'))
		{
			$redirect_url = append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start"));

			meta_refresh(5, $redirect_url);
			if (!$unvote && !sizeof($voted_id))
			{
				$message = 'NO_VOTE_OPTION';
			}
			else if (!$unvote && sizeof($voted_id) > $topic_data['poll_max_options'])
			{
				$message = 'TOO_MANY_VOTE_OPTIONS';
			}
			else if (in_array(VOTE_CONVERTED, $cur_voted_id))
			{
				$message = 'VOTE_CONVERTED';
			}
			else
			{
				$message = 'FORM_INVALID';
			}

			$message = $user->lang[$message] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $redirect_url . '">', '</a>');
			trigger_error($message);
		}

		if ($unvote)
		{
			$voted_id = [];
		}

		foreach ($voted_id as $option)
		{
			if (in_array($option, $cur_voted_id))
			{
				continue;
			}

			$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . '
				SET poll_option_total = poll_option_total + 1
				WHERE poll_option_id = ' . (int) $option . '
					AND topic_id = ' . (int) $topic_id;
			$db->sql_query($sql);

			$sql_ary = [
				'topic_id'			=> (int) $topic_id,
				'poll_option_id'	=> (int) $option,
				'vote_user_id'		=> (int) $user->data['user_id'],
				'vote_time'			=> (int) time(),
				'vote_user_ip'		=> (string) $user->ip,
			];

			$sql = 'INSERT INTO ' . POLL_VOTES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}

		foreach ($cur_voted_id as $option)
		{
			if (!in_array($option, $voted_id))
			{
				$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . '
					SET poll_option_total = poll_option_total - 1
					WHERE poll_option_id = ' . (int) $option . '
						AND topic_id = ' . (int) $topic_id;
				$db->sql_query($sql);

				$sql = 'DELETE FROM ' . POLL_VOTES_TABLE . '
					WHERE topic_id = ' . (int) $topic_id . '
						AND poll_option_id = ' . (int) $option . '
						AND vote_user_id = ' . (int) $user->data['user_id'];
				$db->sql_query($sql);
			}
		}

		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET poll_last_vote = ' . time() . "
			WHERE topic_id = $topic_id";
		$db->sql_query($sql);

		$redirect_url = append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start"));
		if (!empty($config['skip_typical_notices']))
		{
			redirect($redirect_url);
		}
		meta_refresh(3, $redirect_url);
		trigger_error($user->lang[($unvote ? 'VOTE_CANCELLED' : 'VOTE_SUBMITTED')] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $redirect_url . '">', '</a>'));
	}

	if ($poll_info[0]['bbcode_bitfield'])
	{
		$poll_bbcode = new bbcode();
	}
	else
	{
		$poll_bbcode = false;
	}

	for ($i = 0, $size = sizeof($poll_info); $i < $size; $i++)
	{
		$poll_info[$i]['poll_option_text'] = censor_text($poll_info[$i]['poll_option_text']);

		if ($poll_bbcode !== false)
		{
			$poll_bbcode->bbcode_second_pass($poll_info[$i]['poll_option_text'], $poll_info[$i]['bbcode_uid'], $poll_info[$i]['bbcode_bitfield']);
		}

		$poll_info[$i]['poll_option_text'] = bbcode_nl2br($poll_info[$i]['poll_option_text']);
		$poll_info[$i]['poll_option_text'] = smiley_text($poll_info[$i]['poll_option_text']);
	}

	$topic_data['poll_title'] = censor_text($topic_data['poll_title']);

	if ($poll_bbcode !== false)
	{
		$poll_bbcode->bbcode_second_pass($topic_data['poll_title'], $poll_info[0]['bbcode_uid'], $poll_info[0]['bbcode_bitfield']);
	}

	$topic_data['poll_title'] = bbcode_nl2br($topic_data['poll_title']);
	$topic_data['poll_title'] = smiley_text($topic_data['poll_title']);

	unset($poll_bbcode);

	// Get poll voters.
	$poll_total = 0;
	if($topic_data['poll_show_voters'])
	{
		$sql = '
			SELECT u.user_id, u.username, u.user_colour, pv.poll_option_id, pv.vote_time
			FROM ' . POLL_VOTES_TABLE . ' pv
			LEFT JOIN ' . USERS_TABLE . ' u ON pv.vote_user_id = u.user_id
			WHERE pv.topic_id = ' . $topic_id . '
			ORDER BY pv.vote_time ASC, pv.vote_user_id ASC';
		$result = $db->sql_query($sql);

		$voters = [];
		$votes = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$voters[(int)$row['user_id']] = true;
			$votes[(int)$row['poll_option_id']][] = $row;
		}
		$poll_total = count($voters);
		unset($voters);
		$db->sql_freeresult($result);

		foreach ($poll_info as &$option)
		{
			$option['poll_option_voters'] = '';
			$option['poll_option_total'] = 0;

			if (empty($votes[(int)$option['poll_option_id']]))
			{
				continue;
			}

			$option['poll_option_total'] = count($votes[(int)$option['poll_option_id']]);
			foreach ($votes[(int)$option['poll_option_id']] as $vote)
			{
				$option['poll_option_voters'] .= ', ' . get_username_string('full', $vote['user_id'], $vote['username'], $vote['user_colour'], $vote['username'], false, $vote['vote_time'] ? $user->format_date($vote['vote_time']) : '');
			}
			$option['poll_option_voters'] = ltrim($option['poll_option_voters'], ', ');
		}
		unset($option);
	}
	else
	{
		$sql = 'SELECT COUNT(DISTINCT vote_user_id) as count
			FROM ' . POLL_VOTES_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);
		$poll_total = (int) $db->sql_fetchfield('count');
		$db->sql_freeresult($result);
	}

	foreach ($poll_info as $poll_option)
	{
		$option_pct = ($poll_total > 0) ? $poll_option['poll_option_total'] / $poll_total : 0;
		$option_pct_txt = sprintf("%.1d%%", round($option_pct * 100));

		$template->assign_block_vars('poll_option', [
			'POLL_OPTION_ID'		=> $poll_option['poll_option_id'],
			'POLL_OPTION_CAPTION'	=> $poll_option['poll_option_text'],
			'POLL_OPTION_RESULT'	=> $poll_option['poll_option_total'],
			'POLL_OPTION_PERCENT'	=> $option_pct_txt,
			'POLL_OPTION_PCT'		=> round($option_pct * 100),
			'POLL_OPTION_IMG'		=> $user->img('poll_center', $option_pct_txt, round($option_pct * 250)),
			'POLL_OPTION_VOTERS'	=> $poll_option['poll_option_voters'] ?? '',
			'POLL_OPTION_VOTED'		=> (in_array($poll_option['poll_option_id'], $cur_voted_id)),
		]);
	}

	$poll_end = $topic_data['poll_length'] + $topic_data['poll_start'];

	$template->assign_vars([
		'POLL_QUESTION'		=> $topic_data['poll_title'],
		'POLL_VOTED'		=> count($cur_voted_id) > 0,
		'TOTAL_VOTERS'		=> $poll_total,
		'POLL_LEFT_CAP_IMG'	=> $user->img('poll_left'),
		'POLL_RIGHT_CAP_IMG'=> $user->img('poll_right'),

		'L_MAX_VOTES'		=> ($topic_data['poll_max_options'] == 1) ? $user->lang['MAX_OPTION_SELECT'] : sprintf($user->lang['MAX_OPTIONS_SELECT'], $topic_data['poll_max_options']),
		'L_POLL_LENGTH'		=> ($topic_data['poll_length']) ? sprintf($user->lang[($poll_end > time()) ? 'POLL_RUN_TILL' : 'POLL_ENDED_AT'], $user->format_date($poll_end)) : '',

		'S_HAS_POLL'		=> true,
		'S_CAN_VOTE'		=> $s_can_vote,
		'S_DISPLAY_RESULTS'	=> $s_display_results,
		'S_IS_MULTI_CHOICE'	=> ($topic_data['poll_max_options'] > 1),
		'S_POLL_ACTION'		=> $viewtopic_url,
		'S_SHOW_VOTERS'		=> (bool) $topic_data['poll_show_voters'],

		'U_VIEW_RESULTS'	=> $viewtopic_url . '&amp;view=viewpoll',
	]);

	unset($poll_end, $poll_info);
}

// If the user is trying to reach the second half of the topic, fetch it starting from the end
$store_reverse = false;
$sql_limit = $config['posts_per_page'];
$sql_sort_order = $direction = '';

if ($start > $total_posts / 2)
{
	$store_reverse = true;

	if ($start + $config['posts_per_page'] > $total_posts)
	{
		$sql_limit = min($config['posts_per_page'], max(1, $total_posts - $start));
	}

	// Select the sort order
	$direction = (($sort_dir == 'd') ? 'ASC' : 'DESC');
	$sql_start = max(0, $total_posts - $sql_limit - $start);
}
else
{
	// Select the sort order
	$direction = (($sort_dir == 'd') ? 'DESC' : 'ASC');
	$sql_start = $start;
}

if (is_array($sort_by_sql[$sort_key]))
{
	$sql_sort_order = implode(' ' . $direction . ', ', $sort_by_sql[$sort_key]) . ' ' . $direction;
}
else
{
	$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . $direction;
}

// Container for user details, only process once
$post_list = $user_cache = $id_cache = $attachments = $attach_list = $rowset = $update_count = $post_edit_list = [];
$has_attachments = $display_notice = false;
$bbcode_bitfield = '';

// Go ahead and pull all data for this topic
$sql = 'SELECT p.post_id
	FROM ' . POSTS_TABLE . ' p' . (($join_user_sql[$sort_key]) ? ', ' . USERS_TABLE . ' u' : '') . "
	WHERE p.topic_id = $topic_id
		" . ((!$auth->acl_get('m_approve', $forum_id)) ? 'AND p.post_approved = 1' : '') . "
		" . (($join_user_sql[$sort_key]) ? 'AND u.user_id = p.poster_id' : '') . "
		$limit_posts_time
	ORDER BY $sql_sort_order";
$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);

while ($row = $db->sql_fetchrow($result))
{
	if ($topic_data['topic_first_post_show'] && $row['post_id'] == $topic_data['topic_first_post_id'])
	{
		// Skip first post if it is pinned
		continue;
	}
	$post_list[] = (int) $row['post_id'];
}
$db->sql_freeresult($result);

// If reversed order is used for storing
if ($store_reverse)
{
	$post_list = array_reverse($post_list);
}

// Show first post on every page if needed
if ($topic_data['topic_first_post_show'])
{
	array_unshift($post_list, (int) $topic_data['topic_first_post_id']);
}

if (!sizeof($post_list))
{
	if ($sort_days)
	{
		trigger_error('NO_POSTS_TIME_FRAME');
	}
	else
	{
		trigger_error('NO_TOPIC');
	}
}

// Holding maximum post time for marking topic read
// We need to grab it because we do reverse ordering sometimes
$max_post_time = 0;

$sql = $db->sql_build_query('SELECT', [
	'SELECT'	=> 'u.*, z.friend, z.foe, p.*, gu.personal_album_id, gu.user_images',

	'FROM'		=> [
		USERS_TABLE		=> 'u',
		POSTS_TABLE		=> 'p',
	],

	'LEFT_JOIN'	=> [
		[
			'FROM'	=> [ZEBRA_TABLE => 'z'],
			'ON'	=> 'z.user_id = ' . $user->data['user_id'] . ' AND z.zebra_id = p.poster_id'
		],
		[
			'FROM'	=> [GALLERY_USERS_TABLE => 'gu'],
			'ON'	=> 'gu.user_id = p.poster_id'
		]
	],

	'WHERE'		=> $db->sql_in_set('p.post_id', $post_list) . '
		AND u.user_id = p.poster_id'
]);

$result = $db->sql_query($sql);

$now = phpbb_gmgetdate(time() + $user->timezone + $user->dst);

// Posts are stored in the $rowset array while $attach_list, $user_cache
// and the global bbcode_bitfield are built
while ($row = $db->sql_fetchrow($result))
{
	// Set max_post_time
	if ($row['post_time'] > $max_post_time)
	{
		$max_post_time = max($row['post_time'], $row['post_merged']);
	}

	$poster_id = (int) $row['poster_id'];

	// Does post have an attachment? If so, add it to the list
	if ($row['post_attachment'] && $config['allow_attachments'])
	{
		$attach_list[] = (int) $row['post_id'];

		if ($row['post_approved'])
		{
			$has_attachments = true;
		}
	}

	$rowset[$row['post_id']] = [
		'hide_post'			=> ($row['foe'] && ($view != 'show' || $post_id != $row['post_id'])),

		'post_id'			=> $row['post_id'],
		'post_time'			=> $row['post_time'],
		'post_merged'		=> $row['post_merged'],
		'user_id'			=> $row['user_id'],
		'username'			=> $row['username'],
		'user_colour'		=> $row['user_colour'],
		'topic_id'			=> $row['topic_id'],
		'forum_id'			=> $row['forum_id'],
		'post_subject'		=> $row['post_subject'],
		'post_edit_count'	=> $row['post_edit_count'],
		'post_edit_time'	=> $row['post_edit_time'],
		'post_edit_reason'	=> $row['post_edit_reason'],
		'post_edit_user'	=> $row['post_edit_user'],
		'post_edit_locked'	=> $row['post_edit_locked'],

		'post_rating_negative'	=> $row['post_rating_negative'],
		'post_rating_positive'	=> $row['post_rating_positive'],
		'warnings_data'			=> [],

		// Make sure the icon actually exists
		'icon_id'			=> (isset($icons[$row['icon_id']]['img'], $icons[$row['icon_id']]['height'], $icons[$row['icon_id']]['width'])) ? $row['icon_id'] : 0,
		'post_attachment'	=> $row['post_attachment'],
		'post_approved'		=> $row['post_approved'],
		'post_reported'		=> $row['post_reported'],
		'post_username'		=> $row['post_username'],
		'post_text'			=> $row['post_text'],
		'bbcode_uid'		=> $row['bbcode_uid'],
		'bbcode_bitfield'	=> $row['bbcode_bitfield'],
		'enable_smilies'	=> $row['enable_smilies'],
		'enable_sig'		=> $row['enable_sig'],
		'friend'			=> $row['friend'],
		'foe'				=> $row['foe'],
	];

	// Define the global bbcode bitfield, will be used to load bbcodes
	$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['bbcode_bitfield']);

	// Is a signature attached? Are we going to display it?
	if ($row['enable_sig'] && $config['allow_sig'] && $user->optionget('viewsigs'))
	{
		$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['user_sig_bbcode_bitfield']);
	}

	// Cache various user specific data ... so we don't have to recompute
	// this each time the same user appears on this page
	if (!isset($user_cache[$poster_id]))
	{
		if ($poster_id == ANONYMOUS)
		{
			$user_cache[$poster_id] = [
				'joined'		=> '',
				'with_us'		=> '',
				'posts'			=> '',
				'topics'		=> '',
				'from'			=> '',

				'rating'			=> 0,
				'rating_positive'	=> 0,
				'rating_negative'	=> 0,
				'rated'				=> 0,
				'rated_positive'	=> 0,
				'rated_negative'	=> 0,

				'sig'					=> '',
				'sig_bbcode_uid'		=> '',
				'sig_bbcode_bitfield'	=> '',

				'online'			=> false,
				'avatar'			=> ($user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',
				'sig'				=> '',
				'profile'			=> '',
				'pm'				=> '',
				'email'				=> '',
				'www'				=> '',
				'jabber'			=> '',
				'telegram'			=> '',
				'search'			=> '',
				'age'				=> '',
				'gender'			=> 0,

				'gallery_album'		=> '',
				'gallery_images'	=> '',
				'gallery_search'	=> '',

				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],

				'warnings'			=> 0,
				'warnings_data'		=> [],
				'allow_pm'			=> 0,
			];

			get_user_rank($row['user_rank'], false, $user_cache[$poster_id]['rank_title'], $user_cache[$poster_id]['rank_image'], $user_cache[$poster_id]['rank_image_src']);
		}
		else
		{
			$user_sig = '';

			// We add the signature to every posters entry because enable_sig is post dependant
			if ($row['user_sig'] && $config['allow_sig'] && $user->optionget('viewsigs'))
			{
				$user_sig = $row['user_sig'];
			}

			$id_cache[] = $poster_id;

			$user_cache[$poster_id] = [
				'joined'		=> $user->format_date($row['user_regdate'], false, false, true),
				'with_us'		=> !empty($config['style_mp_show_with_us']) ? get_verbal_time_delta($row['user_regdate'], time(), false, 2) : '',
				'posts'			=> $row['user_posts'],
				'topics'		=> $row['user_topics'],
				'warnings'		=> $row['user_warnings'] ?? 0,
				'warnings_data'	=> [],
				'from'			=> $row['user_from'] ?? '',

				'rating'			=> ($config['rate_no_positive'] ? 0 : $row['user_rating_positive']) - ($config['rate_no_negative'] ? 0 : $row['user_rating_negative']),
				'rating_positive'	=> $row['user_rating_positive'],
				'rating_negative'	=> $row['user_rating_negative'],
				'rated'				=> ($config['rate_no_positive'] ? 0 : $row['user_rated_positive']) - ($config['rate_no_negative'] ? 0 : $row['user_rated_negative']),
				'rated_positive'	=> $row['user_rated_positive'],
				'rated_negative'	=> $row['user_rated_negative'],

				'sig'					=> $user_sig,
				'sig_bbcode_uid'		=> $row['user_sig_bbcode_uid'] ?? '',
				'sig_bbcode_bitfield'	=> $row['user_sig_bbcode_bitfield'] ?? '',

				'viewonline'	=> $row['user_allow_viewonline'],
				'allow_pm'		=> $row['user_allow_pm'],

				'avatar'		=> ($user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
				'age'			=> '',
				'gender'		=> (int)$row['user_gender'],

				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',

				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],

				'online'		=> false,
				'profile'		=> append_sid(PHPBB_ROOT_PATH . 'memberlist.php', "mode=viewprofile&amp;u=$poster_id"),
				'www'			=> $row['user_website'],
				'jabber'		=> ($row['user_jabber']) ? ('xmpp:' . $row['user_jabber']) : '',
				'telegram'		=> ($row['user_telegram']) ? ('tg://resolve?domain=' . $row['user_telegram']) : '',
				'search'		=> ($auth->acl_get('u_search')) ? append_sid(PHPBB_ROOT_PATH . 'search.php', "author_id=$poster_id&amp;sr=posts") : '',

				'gallery_album'		=> (phpbb_gallery_config::get('viewtopic_icon') && $row['personal_album_id']) ? phpbb_gallery_url::append_sid('album', "album_id=" . $row['personal_album_id']) : '',
				'gallery_images'	=> (phpbb_gallery_config::get('viewtopic_images')) ? $row['user_images'] : 0,
				'gallery_search'	=> (phpbb_gallery_config::get('viewtopic_images') && phpbb_gallery_config::get('viewtopic_link') && $row['user_images']) ? phpbb_gallery_url::append_sid('search', "user_id=$poster_id") : '',

				'author_full'		=> get_username_string('full', $poster_id, $row['username'], $row['user_colour']),
				'author_colour'		=> get_username_string('colour', $poster_id, $row['username'], $row['user_colour']),
				'author_username'	=> get_username_string('username', $poster_id, $row['username'], $row['user_colour']),
				'author_profile'	=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour']),
			];

			get_user_rank($row['user_rank'], $row['user_posts'], $user_cache[$poster_id]['rank_title'], $user_cache[$poster_id]['rank_image'], $user_cache[$poster_id]['rank_image_src']);

			if ((!empty($row['user_allow_viewemail']) && $auth->acl_get('u_sendemail')) || $auth->acl_get('a_email'))
			{
				$user_cache[$poster_id]['email'] = ($config['board_email_form'] && $config['email_enable']) ? append_sid(PHPBB_ROOT_PATH . 'memberlist.php', "mode=email&amp;u=$poster_id") : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']);
			}
			else
			{
				$user_cache[$poster_id]['email'] = '';
			}

			if ($config['allow_birthdays'] && !empty($row['user_birthday']))
			{
				[$bday_day, $bday_month, $bday_year] = array_map('intval', explode('-', $row['user_birthday']));

				if ($bday_year)
				{
					$diff = $now['mon'] - $bday_month;
					if ($diff == 0)
					{
						$diff = ($now['mday'] - $bday_day < 0) ? 1 : 0;
					}
					else
					{
						$diff = ($diff < 0) ? 1 : 0;
					}

					$user_cache[$poster_id]['age'] = (int) ($now['year'] - $bday_year - $diff);
				}
			}
		}
	}
}
$db->sql_freeresult($result);

// Load custom profile fields
if ($config['load_cpf_viewtopic'])
{
	if (!class_exists('custom_profile'))
	{
		require_once(PHPBB_ROOT_PATH . 'includes/functions_profile_fields.php');
	}
	$cp = new custom_profile();

	// Grab all profile fields from users in id cache for later use - similar to the poster cache
	$profile_fields_tmp = $cp->generate_profile_fields_template('grab', $id_cache);

	// filter out fields not to be displayed on viewtopic. Yes, it's a hack, but this shouldn't break any MODs.
	$profile_fields_cache = [];
	foreach ($profile_fields_tmp as $profile_user_id => $profile_fields)
	{
		$profile_fields_cache[$profile_user_id] = [];
		foreach ($profile_fields as $used_ident => $profile_field)
		{
			if ($profile_field['data']['field_show_on_vt'])
			{
				$profile_fields_cache[$profile_user_id][$used_ident] = $profile_field;
			}
		}
	}
	unset($profile_fields_tmp);
}

// Generate online information for user
if ($config['load_onlinetrack'] && sizeof($id_cache))
{
	$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
		FROM ' . SESSIONS_TABLE . '
		WHERE ' . $db->sql_in_set('session_user_id', $id_cache) . '
		GROUP BY session_user_id';
	$result = $db->sql_query($sql);

	$update_time = $config['load_online_time'] * 60;
	while ($row = $db->sql_fetchrow($result))
	{
		$user_cache[$row['session_user_id']]['online'] = (time() - $update_time < $row['online_time'] && (($row['viewonline']) || $auth->acl_get('u_viewonline')));
	}
	$db->sql_freeresult($result);
}
unset($id_cache);

// Pull attachment data
if (sizeof($attach_list))
{
	if ($auth->acl_get('u_download') && $auth->acl_get('f_download', $forum_id))
	{
		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('post_msg_id', $attach_list) . '
				AND in_message = 0
			ORDER BY filetime DESC, post_msg_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$attachments[$row['post_msg_id']][] = $row;
		}
		$db->sql_freeresult($result);

		// No attachments exist, but post table thinks they do so go ahead and reset post_attach flags
		if (!sizeof($attachments))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_attachment = 0
				WHERE ' . $db->sql_in_set('post_id', $attach_list);
			$db->sql_query($sql);

			// We need to update the topic indicator too if the complete topic is now without an attachment
			if (sizeof($rowset) != $total_posts)
			{
				// Not all posts are displayed so we query the db to find if there's any attachment for this topic
				$sql = 'SELECT a.post_msg_id as post_id
					FROM ' . ATTACHMENTS_TABLE . ' a, ' . POSTS_TABLE . " p
					WHERE p.topic_id = $topic_id
						AND p.post_approved = 1
						AND p.topic_id = a.topic_id";
				$result = $db->sql_query_limit($sql, 1);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . "
						SET topic_attachment = 0
						WHERE topic_id = $topic_id";
					$db->sql_query($sql);
				}
			}
			else
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . "
					SET topic_attachment = 0
					WHERE topic_id = $topic_id";
				$db->sql_query($sql);
			}
		}
		else if ($has_attachments && !$topic_data['topic_attachment'])
		{
			// Topic has approved attachments but its flag is wrong
			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_attachment = 1
				WHERE topic_id = $topic_id";
			$db->sql_query($sql);

			$topic_data['topic_attachment'] = 1;
		}
	}
	else
	{
		$display_notice = true;
	}
}

// Pull warnings data
if (!empty($post_list))
{
	$user_list = array_keys($user_cache);
	$sql = 'SELECT w.*, u.username AS issuer_name, u.user_colour AS issuer_colour
		FROM ' . WARNINGS_TABLE . ' w
		LEFT JOIN ' . USERS_TABLE . ' u
		ON w.issuer_id = u.user_id
		WHERE ' . $db->sql_in_set('w.post_id', $post_list) . '
			OR (' . $db->sql_in_set('w.user_id', $user_list) . ' AND w.warning_active = 1)
		ORDER BY w.warning_id';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		// Warnings by users
		if (!empty($user_cache[$row['user_id']]))
		{
			$user_cache[$row['user_id']]['warnings_data'][] = $row;
		}

		// Warnings by posts
		if (!empty($rowset[$row['post_id']]))
		{
			$rowset[$row['post_id']]['warnings_data'][] = $row;
		}
	}
	$db->sql_freeresult($result);
}

// Instantiate BBCode if need be
if ($bbcode_bitfield !== '')
{
	$bbcode = new bbcode(base64_encode($bbcode_bitfield));
}

$i_total = sizeof($rowset) - 1;
$prev_post_id = '';

$template->assign_vars([
	'S_NUM_POSTS' => sizeof($post_list)]
);

// Get user rates
$user_rates = [];
if ($config['rate_enabled'])
{
	$sql = 'SELECT *
		FROM ' . POST_RATES_TABLE . '
		WHERE user_id = ' . $user->data['user_id'] . '
			AND ' . $db->sql_in_set('post_id', $post_list);
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$user_rates[$row['post_id']] = $row;
	}
}

// Get list of rates for displaying
$post_raters = [];
if ($config['rate_enabled'] && $config['display_raters'])
{
	$sql = 'SELECT r.*, u.username, u.user_colour
		FROM ' . POST_RATES_TABLE . ' r
		LEFT JOIN ' . USERS_TABLE . ' u ON r.user_id = u.user_id
		WHERE ' . $db->sql_in_set('r.post_id', $post_list) . '
		ORDER BY r.post_id, r.rate_time';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!isset($post_raters[$row['post_id']]))
		{
			$post_raters[$row['post_id']] = [];
		}
		$post_raters[$row['post_id']][] = $row;
	}
}

// Output the posts
$first_unread = $post_unread = false;
for ($i = 0, $end = sizeof($post_list); $i < $end; ++$i)
{
	// A non-existing rowset only happens if there was no user present for the entered poster_id
	// This could be a broken posts table.
	if (!isset($rowset[$post_list[$i]]))
	{
		continue;
	}

	$row =& $rowset[$post_list[$i]];
	$poster_id = $row['user_id'];

	// End signature parsing, only if needed
	if ($user_cache[$poster_id]['sig'] && $row['enable_sig'] && empty($user_cache[$poster_id]['sig_parsed']))
	{
		$user_cache[$poster_id]['sig'] = censor_text($user_cache[$poster_id]['sig']);

		if ($user_cache[$poster_id]['sig_bbcode_bitfield'])
		{
			$bbcode->bbcode_second_pass($user_cache[$poster_id]['sig'], $user_cache[$poster_id]['sig_bbcode_uid'], $user_cache[$poster_id]['sig_bbcode_bitfield']);
		}

		$user_cache[$poster_id]['sig'] = bbcode_nl2br($user_cache[$poster_id]['sig']);
		$user_cache[$poster_id]['sig'] = smiley_text($user_cache[$poster_id]['sig']);
		$user_cache[$poster_id]['sig_parsed'] = true;
	}

	// Parse the message and subject
	$message = censor_text($row['post_text']);

	// Prepare decoded message if needed
	$decoded_message = false;
	if (!empty($config['allow_quick_reply']) && !empty($config['allow_quick_full_quote']) && $auth->acl_get('f_reply', $forum_id))
	{
		$decoded_message = $message;
		decode_message($decoded_message, $row['bbcode_uid']);
		$decoded_message = bbcode_nl2br($decoded_message);
	}

	// Second parse bbcode here
	if ($row['bbcode_bitfield'])
	{
		$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield'], $row['post_time']);
	}

	$message = bbcode_nl2br($message);
	$message = smiley_text($message);

	if (!empty($attachments[$row['post_id']]))
	{
		parse_attachments($forum_id, $message, $attachments[$row['post_id']], $update_count);
	}

	// Replace naughty words such as farty pants
	$row['post_subject'] = censor_text($row['post_subject']);

	// Highlight active words (primarily for search)
	if ($highlight_match)
	{
		$message = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $message);
		$row['post_subject'] = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $row['post_subject']);
	}

	// Editing information
	if (($row['post_edit_count'] && $config['display_last_edited']) || $row['post_edit_reason'])
	{
		// Get usernames for all following posts if not already stored
		if (!sizeof($post_edit_list) && ($row['post_edit_reason'] || ($row['post_edit_user'] && !isset($user_cache[$row['post_edit_user']]))))
		{
			// Remove all post_ids already parsed (we do not have to check them)
			$post_storage_list = (!$store_reverse) ? array_slice($post_list, $i) : array_slice(array_reverse($post_list), $i);

			$sql = 'SELECT DISTINCT u.user_id, u.username, u.user_colour
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE ' . $db->sql_in_set('p.post_id', $post_storage_list) . '
					AND p.post_edit_count <> 0
					AND p.post_edit_user <> 0
					AND p.post_edit_user = u.user_id';
			$result2 = $db->sql_query($sql);
			while ($user_edit_row = $db->sql_fetchrow($result2))
			{
				$post_edit_list[$user_edit_row['user_id']] = $user_edit_row;
			}
			$db->sql_freeresult($result2);

			unset($post_storage_list);
		}

		$l_edit_time_total = ($row['post_edit_count'] == 1) ? $user->lang['EDITED_TIME_TOTAL'] : $user->lang['EDITED_TIMES_TOTAL'];

		// User having edited the post also being the post author?
		if (!$row['post_edit_user'] || $row['post_edit_user'] == $poster_id)
		{
			$display_username = get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']);
		}
		else if (isset($user_cache[$row['post_edit_user']]))
		{
			$display_username = get_username_string('full', $row['post_edit_user'], $user_cache[$row['post_edit_user']]['username'], $user_cache[$row['post_edit_user']]['user_colour']);
		}
		else if (isset($post_edit_list[$row['post_edit_user']]))
		{
			$display_username = get_username_string('full', $row['post_edit_user'], $post_edit_list[$row['post_edit_user']]['username'], $post_edit_list[$row['post_edit_user']]['user_colour']);
		}
		else
		{
			$display_username = get_username_string('full', ANONYMOUS, '');
		}

		$l_edited_by = sprintf($l_edit_time_total, $display_username, $user->format_date($row['post_edit_time'], false, true), $row['post_edit_count']);
	}
	else
	{
		$l_edited_by = '';
	}

	// Bump information
	if ($topic_data['topic_bumped'] && $row['post_id'] == $topic_data['topic_last_post_id'] && isset($user_cache[$topic_data['topic_bumper']]) )
	{
		// It is safe to grab the username from the user cache array, we are at the last
		// post and only the topic poster and last poster are allowed to bump.
		// Admins and mods are bound to the above rules too...
		$template->assign_var('BUMPED_MESSAGE', sprintf($user->lang['BUMPED_BY'], $user_cache[$topic_data['topic_bumper']]['username'], $user->format_date($topic_data['topic_last_post_time'], false, true)));
	}

	$cp_row = [];

	//
	if ($config['load_cpf_viewtopic'])
	{
		$cp_row = (isset($profile_fields_cache[$poster_id])) ? $cp->generate_profile_fields_template('show', false, $profile_fields_cache[$poster_id]) : [];
	}

	$post_unread = (isset($topic_tracking_info[$topic_id]) && ($row['post_time'] > $topic_tracking_info[$topic_id] || $row['post_merged'] > $topic_tracking_info[$topic_id]));

	$s_first_unread = false;
	if (!$first_unread && $post_unread)
	{
		$s_first_unread = $first_unread = true;
	}

	$edit_allowed = ($user->data['is_registered'] && ($auth->acl_get('m_edit', $forum_id) || (
		$user->data['user_id'] == $poster_id &&
		$auth->acl_get('f_edit', $forum_id) &&
		$topic_data['topic_status'] != ITEM_LOCKED &&
		!$row['post_edit_locked'] &&
		($row['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time'] || $auth->acl_get('u_ignoreedittime')
			|| ($topic_data['topic_first_post_id'] == $row['post_id'] && $auth->acl_get('u_ignorefpedittime')))
	)));

	$quote_allowed = ($topic_data['topic_status'] != ITEM_LOCKED && $auth->acl_get('f_reply', $forum_id) || $auth->acl_get('m_edit', $forum_id));

	$delete_allowed = ($user->data['is_registered'] && ($auth->acl_get('m_delete', $forum_id) || (
		$user->data['user_id'] == $poster_id &&
		$auth->acl_get('f_delete', $forum_id) &&
		$topic_data['topic_status'] != ITEM_LOCKED &&
		$topic_data['topic_last_post_id'] == $row['post_id'] &&
		($row['post_time'] > time() - ($config['delete_time'] * 60) || !$config['delete_time']) &&
		// we do not want to allow removal of the last post if a moderator locked it!
		!$row['post_edit_locked']
	)));

	$user_rate = $user_rates[$row['post_id']] ?? ['rate' => 0, 'rate_time' => 0];
	$rate_time = ($topic_data['topic_first_post_id'] != $row['post_id'] || !isset($config['rate_topic_time']) || $config['rate_topic_time'] == -1) ? $config['rate_time'] : $config['rate_topic_time'];

	$post_number = ($topic_data['topic_first_post_show'] && $start != 0) ? ($topic_data['topic_first_post_id'] == $row['post_id'] ? 1 : $i + $start) : $i + $start + 1;

	//
	$postrow = [
		'POST_AUTHOR_FULL'		=> ($poster_id != ANONYMOUS) ? $user_cache[$poster_id]['author_full'] : get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
		'POST_AUTHOR_COLOUR'	=> ($poster_id != ANONYMOUS) ? $user_cache[$poster_id]['author_colour'] : get_username_string('colour', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
		'POST_AUTHOR'			=> ($poster_id != ANONYMOUS) ? $user_cache[$poster_id]['author_username'] : get_username_string('username', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
		'U_POST_AUTHOR'			=> ($poster_id != ANONYMOUS) ? $user_cache[$poster_id]['author_profile'] : get_username_string('profile', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),

		'RANK_TITLE'		=> $user_cache[$poster_id]['rank_title'],
		'RANK_IMG'			=> $user_cache[$poster_id]['rank_image'],
		'RANK_IMG_SRC'		=> $user_cache[$poster_id]['rank_image_src'],
		'POSTER_JOINED'		=> $user_cache[$poster_id]['joined'],
		'POSTER_WITH_US'	=> $user_cache[$poster_id]['with_us'],
		'POSTER_POSTS'		=> $user_cache[$poster_id]['posts'],
		'POSTER_TOPICS'		=> $user_cache[$poster_id]['topics'],
		'POSTER_FROM'		=> $user_cache[$poster_id]['from'],
		'POSTER_AVATAR'		=> $user_cache[$poster_id]['avatar'],
		'POSTER_WARNINGS'	=> $user_cache[$poster_id]['warnings'],
		'POSTER_AGE'		=> $user_cache[$poster_id]['age'],

		'S_POSTER_RATING'			=> $config['rate_enabled'] && (!$config['rate_no_negative'] || !$config['rate_no_positive']) && ($poster_id != ANONYMOUS),
		'POSTER_RATING'				=> $user_cache[$poster_id]['rating'],
		'POSTER_RATING_POSITIVE'	=> $user_cache[$poster_id]['rating_positive'],
		'POSTER_RATING_NEGATIVE'	=> $user_cache[$poster_id]['rating_negative'],
		'POSTER_RATED'				=> $user_cache[$poster_id]['rated'],
		'POSTER_RATED_POSITIVE'		=> $user_cache[$poster_id]['rated_positive'],
		'POSTER_RATED_NEGATIVE'		=> $user_cache[$poster_id]['rated_negative'],

		// This value will be used as a parameter for JS insert_text() function, so we use addslashes to handle "special" usernames properly ;)
		'POSTER_QUOTE'		=> addslashes(get_username_string('username', $poster_id, $row['username'], $row['user_colour'], $row['post_username'])),

		'S_POSTER_GENDER_X'	=> $user_cache[$poster_id]['gender'] == GENDER_X,
		'S_POSTER_GENDER_M'	=> $user_cache[$poster_id]['gender'] == GENDER_M,
		'S_POSTER_GENDER_F'	=> $user_cache[$poster_id]['gender'] == GENDER_F,

		'POST_DATE'			=> $user->format_date($row['post_time'], false, ($view == 'print')),
		'POST_SUBJECT'		=> $row['post_subject'],
		'MESSAGE'			=> $message,
		'DECODED_MESSAGE'	=> $decoded_message,
		'SIGNATURE'			=> ($row['enable_sig']) ? $user_cache[$poster_id]['sig'] : '',
		'EDITED_MESSAGE'	=> $l_edited_by,
		'EDIT_REASON'		=> $row['post_edit_reason'],

		'MINI_POST_IMG'			=> ($post_unread) ? $user->img('icon_post_target_unread', "{$user->lang['UNREAD_POST']} #{$post_number}") : $user->img('icon_post_target', "{$user->lang['POST']} #{$post_number}"),
		'POST_ICON_IMG'			=> ($config['enable_topic_icons'] && !empty($row['icon_id'])) ? $icons[$row['icon_id']]['img'] : '',
		'POST_ICON_IMG_WIDTH'	=> ($config['enable_topic_icons'] && !empty($row['icon_id'])) ? $icons[$row['icon_id']]['width'] : '',
		'POST_ICON_IMG_HEIGHT'	=> ($config['enable_topic_icons'] && !empty($row['icon_id'])) ? $icons[$row['icon_id']]['height'] : '',
		'ONLINE_IMG'			=> ($poster_id == ANONYMOUS || !$config['load_onlinetrack']) ? '' : (($user_cache[$poster_id]['online']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
		'S_ONLINE'				=> ($poster_id == ANONYMOUS || !$config['load_onlinetrack']) ? false : (bool) $user_cache[$poster_id]['online'],

		'U_EDIT'			=> ($edit_allowed) ? append_sid(PHPBB_ROOT_PATH . 'posting.php', "mode=edit&amp;f=$forum_id&amp;p={$row['post_id']}") : '',
		'U_QUOTE'			=> ($quote_allowed) ? append_sid(PHPBB_ROOT_PATH . 'posting.php', "mode=quote&amp;f=$forum_id&amp;p={$row['post_id']}") : '',
		'U_INFO'			=> ($auth->acl_get('m_info', $forum_id)) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, $user->session_id) : '',
		'U_DELETE'			=> ($delete_allowed) ? append_sid(PHPBB_ROOT_PATH . 'posting.php', "mode=delete&amp;f=$forum_id&amp;p={$row['post_id']}") : '',

		'U_PROFILE'		=> $user_cache[$poster_id]['profile'],
		'U_SEARCH'		=> $user_cache[$poster_id]['search'],
		'U_PM'			=> ($poster_id != ANONYMOUS && $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($user_cache[$poster_id]['allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))) ? append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'i=pm&amp;mode=compose&amp;action=quotepost&amp;p=' . $row['post_id']) : '',
		'U_EMAIL'		=> $user_cache[$poster_id]['email'],
		'U_WWW'			=> $user_cache[$poster_id]['www'],
		'U_JABBER'		=> $user_cache[$poster_id]['jabber'],
		'U_TELEGRAM'	=> $user_cache[$poster_id]['telegram'],

		'U_GALLERY'			=> $user_cache[$poster_id]['gallery_album'],
		'GALLERY_IMAGES'	=> $user_cache[$poster_id]['gallery_images'],
		'U_GALLERY_SEARCH'	=> $user_cache[$poster_id]['gallery_search'],

		'U_REPORT'			=> ($auth->acl_get('f_report', $forum_id)) ? append_sid(PHPBB_ROOT_PATH . 'report.php', 'f=' . $forum_id . '&amp;p=' . $row['post_id']) : '',
		'U_MCP_REPORT'		=> ($auth->acl_get('m_report', $forum_id)) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=reports&amp;mode=report_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',
		'U_MCP_APPROVE'		=> ($auth->acl_get('m_approve', $forum_id)) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=queue&amp;mode=approve_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',
		'U_MINI_POST'		=> append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', 'p=' . $row['post_id']) . '#p' . $row['post_id'],
		'U_NEXT_POST_ID'	=> ($i < $i_total && isset($rowset[$post_list[$i + 1]])) ? $rowset[$post_list[$i + 1]]['post_id'] : '',
		'U_PREV_POST_ID'	=> $prev_post_id,
		'U_NOTES'			=> ($auth->acl_getf_global('m_')) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', 'i=notes&amp;mode=user_notes&amp;u=' . $poster_id, true, $user->session_id) : '',
		'U_WARN'			=> ($auth->acl_get('m_warn') && $poster_id != ANONYMOUS) ? append_sid(PHPBB_ROOT_PATH . 'mcp.php', count($row['warnings_data']) ? 'i=warn&amp;mode=warn_edit&amp;warning_id=' . $row['warnings_data'][0]['warning_id'] : 'i=warn&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',

		'POST_ID'			=> $row['post_id'],
		'POST_NUMBER'		=> $post_number,
		'POSTER_ID'			=> $poster_id,

		'POST_RATING_SHOW'		=> $config['rate_enabled'] && (!$config['rate_no_negative'] || !$config['rate_no_positive']) && ($row['post_rating_negative'] != 0 || $row['post_rating_positive'] != 0 || (empty($config['rate_only_topics']) || $topic_data['topic_first_post_id'] == $row['post_id']) && ($rate_time > 0 ? $rate_time + $row['post_time'] > time() : true)),
		'POST_RATING'			=> ($config['rate_no_positive'] ? 0 : $row['post_rating_positive']) - ($config['rate_no_negative'] ? 0 : $row['post_rating_negative']),
		'POST_RATING_NEGATIVE'	=> $row['post_rating_negative'],
		'POST_RATING_POSITIVE'	=> $row['post_rating_positive'],
		'USER_RATE'				=> $user_rate['rate'],
		'USER_CAN_MINUS'		=> $config['rate_enabled'] && ($user->data['user_id'] != ANONYMOUS) && ($user->data['user_id'] != $poster_id) && (empty($config['rate_only_topics']) || $topic_data['topic_first_post_id'] == $row['post_id']) && ($rate_time > 0 ? $rate_time + $row['post_time'] > time() : true) && ($user_rate['rate'] >= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_negative'] ? $user_rate['rate'] != 0 : true) && $auth->acl_get('u_canminus'),
		'USER_CAN_PLUS'			=> $config['rate_enabled'] && ($user->data['user_id'] != ANONYMOUS) && ($user->data['user_id'] != $poster_id) && (empty($config['rate_only_topics']) || $topic_data['topic_first_post_id'] == $row['post_id']) && ($rate_time > 0 ? $rate_time + $row['post_time'] > time() : true) && ($user_rate['rate'] <= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_positive'] ? $user_rate['rate'] != 0 : true) && $auth->acl_get('u_canplus'),

		'S_HAS_ATTACHMENTS'	=> !empty($attachments[$row['post_id']]),
		'S_POST_UNAPPROVED'	=> !$row['post_approved'],
		'S_POST_REPORTED'	=> ($row['post_reported'] && $auth->acl_get('m_report', $forum_id)),
		'S_DISPLAY_NOTICE'	=> $display_notice && $row['post_attachment'],
		'S_FRIEND'			=> (bool) $row['friend'],
		'S_UNREAD_POST'		=> $post_unread,
		'S_FIRST_UNREAD'	=> $s_first_unread,
		'S_CUSTOM_FIELDS'	=> (isset($cp_row['row']) && sizeof($cp_row['row'])),
		'S_ANONYMOUS'		=> ($poster_id == ANONYMOUS),
		'S_TOPIC_POSTER'	=> ($poster_id != ANONYMOUS ? ($topic_data['topic_poster'] == $poster_id) : (!empty($topic_data['topic_first_poster_name']) && $topic_data['topic_first_poster_name'] == $row['post_username'])),

		'S_IGNORE_POST'		=> (bool) $row['hide_post'],
		'L_IGNORE_POST'		=> ($row['hide_post']) ? sprintf($user->lang['POST_BY_FOE'], get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']), '<a href="' . $viewtopic_url . "&amp;p={$row['post_id']}&amp;view=show#p{$row['post_id']}" . '">', '</a>') : '',
	];

	if (isset($cp_row['row']) && sizeof($cp_row['row']))
	{
		$postrow = array_merge($postrow, $cp_row['row']);
	}

	// Dump vars into template
	$template->assign_block_vars('postrow', $postrow);

	// Display raters if needed
	if ($config['rate_enabled'] && $config['display_raters'] && isset($post_raters[$row['post_id']]))
	{
		$is_first_row = true;
		foreach ($post_raters[$row['post_id']] as $rater)
		{
			$template->assign_block_vars('postrow.postrater', [
				'S_FIRST_ROW'	=> $is_first_row,
				'RATER_ID'		=> $rater['user_id'],
				'POST_ID'		=> $rater['post_id'],
				'RATE'			=> $rater['rate'],
				'RATE_TEXT'		=> ($rater['rate'] > 0 ? '+'.$rater['rate'] : '−'.abs($rater['rate'])),
				'DATETIME'		=> $user->format_date($rater['rate_time']),
				'DATE'			=> $user->format_date($rater['rate_time'], false, false, true),
				'U_RATER'		=> get_username_string('profile', 	$rater['user_id'], $rater['username'], $rater['user_colour']),
				'RATER_NAME'	=> get_username_string('username', 	$rater['user_id'], $rater['username'], $rater['user_colour']),
				'RATER_COLOUR'	=> get_username_string('colour', 	$rater['user_id'], $rater['username'], $rater['user_colour']),
				'RATER_FULL'	=> get_username_string('full', 		$rater['user_id'], $rater['username'], $rater['user_colour']),
			]);
			$is_first_row = false;
		}
	}

	// Display user warnings
	foreach ($user_cache[$poster_id]['warnings_data'] as $warning)
	{
		switch ($warning['warning_type'])
		{
			case 'remark':
				$warn_title = $user->lang['REMARK'];
			break;
			case 'warning':
			case 'ban':
				$warn_type = strtoupper($warning['warning_type']);
				$warn_title = ($warning['warning_days'])
					? sprintf($user->lang[$warn_type.'_X_DAYS'], $warning['warning_days'])
					: $user->lang['PERMANENT_'.$warn_type];
			break;
		}

		$template->assign_block_vars('postrow.userwarning', [
			'TITLE'			=> $warn_title,
			'WARNING_ID'	=> $warning['warning_id'],
			'ISSUER_ID'		=> $warning['issuer_id'],
			'U_ISSUER'		=> get_username_string('profile', 	$warning['issuer_id'], $warning['issuer_name'], $warning['issuer_colour']),
			'ISSUER_NAME'	=> get_username_string('username', 	$warning['issuer_id'], $warning['issuer_name'], $warning['issuer_colour']),
			'ISSUER_COLOUR'	=> get_username_string('colour', 	$warning['issuer_id'], $warning['issuer_name'], $warning['issuer_colour']),
			'ISSUER_FULL'	=> get_username_string('full', 		$warning['issuer_id'], $warning['issuer_name'], $warning['issuer_colour']),
			'TIME'			=> $user->format_date($warning['warning_time']),
			'EXPIRES'		=> $user->format_date($warning['warning_time'] + $warning['warning_days'] * 86400),
			'ACTIVE'		=> (bool) $warning['warning_active'],
			'TYPE'			=> $warning['warning_type'],
			'TEXT'			=> $warning['warning_text'],
			'U_POST'		=> ($warning['post_id']) ? append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', 'p=' . $warning['post_id']) . '#p' . $warning['post_id'] : '',
		]);
	}

	// Display post warnings
	foreach ($row['warnings_data'] as $warning)
	{
		switch ($warning['warning_type'])
		{
			case 'remark':
				$warn_title = $user->lang['REMARK'];
			break;
			case 'warning':
			case 'ban':
				$warn_type = strtoupper($warning['warning_type']);
				$warn_title = ($warning['warning_days'])
					? sprintf($user->lang[$warn_type.'_X_DAYS'], $warning['warning_days'])
					: $user->lang['PERMANENT_'.$warn_type];
			break;
		}

		$template->assign_block_vars('postrow.postwarning', [
			'TITLE'			=> $warn_title,
			'WARNING_ID'	=> $warning['warning_id'],
			'ISSUER_ID'		=> $warning['issuer_id'],
			'U_ISSUER'		=> get_username_string('profile', 	$warning['issuer_id'], $warning['issuer_name'], $warning['issuer_colour']),
			'ISSUER_NAME'	=> get_username_string('username', 	$warning['issuer_id'], $warning['issuer_name'], $warning['issuer_colour']),
			'ISSUER_COLOUR'	=> get_username_string('colour', 	$warning['issuer_id'], $warning['issuer_name'], $warning['issuer_colour']),
			'ISSUER_FULL'	=> get_username_string('full', 		$warning['issuer_id'], $warning['issuer_name'], $warning['issuer_colour']),
			'TIME'			=> $user->format_date($warning['warning_time']),
			'EXPIRES'		=> $user->format_date($warning['warning_time'] + $warning['warning_days'] * 86400),
			'ACTIVE'		=> (bool) $warning['warning_active'],
			'TYPE'			=> $warning['warning_type'],
			'TEXT'			=> $warning['warning_text'],
		]);
	}

	if (!empty($cp_row['blockrow']))
	{
		foreach ($cp_row['blockrow'] as $field_data)
		{
			$template->assign_block_vars('postrow.custom_fields', $field_data);
		}
	}

	// Display not already displayed Attachments for this post, we already parsed them. ;)
	if (!empty($attachments[$row['post_id']]))
	{
		foreach ($attachments[$row['post_id']] as $attachment)
		{
			$template->assign_block_vars('postrow.attachment', [
				'DISPLAY_ATTACHMENT'	=> $attachment]
			);
		}
	}

	$prev_post_id = $row['post_id'];

	unset($rowset[$post_list[$i]]);
	unset($attachments[$row['post_id']]);
}
unset($rowset, $user_cache);

// Update topic view and if necessary attachment view counters ... but only for humans and if this is the first 'page view'
if (isset($user->data['session_page']) && !$user->data['is_bot'] && (strpos($user->data['session_page'], '&t=' . $topic_id) === false || isset($user->data['session_created'])))
{
	$sql = 'UPDATE ' . TOPICS_TABLE . '
		SET topic_views = topic_views + 1, topic_last_view_time = ' . time() . "
		WHERE topic_id = $topic_id";
	$db->sql_query($sql);

	// Update the attachment download counts
	if (sizeof($update_count))
	{
		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
			SET download_count = download_count + 1
			WHERE ' . $db->sql_in_set('attach_id', array_unique($update_count));
		$db->sql_query($sql);
	}
}

$last_page = ((floor($start / $config['posts_per_page']) + 1) == max(ceil($total_posts / $config['posts_per_page']), 1));
if ($last_page)
{
	$max_post_time = max($topic_data['topic_last_post_time'], $max_post_time);
}

// Only mark topic if it's currently unread. Also make sure we do not set topic tracking back if earlier pages are viewed.
if (isset($topic_tracking_info[$topic_id]) && $topic_data['topic_last_post_time'] > $topic_tracking_info[$topic_id] && $max_post_time > $topic_tracking_info[$topic_id])
{
	markread('topic', $forum_id, $topic_id, $max_post_time);

	// Update forum info
	$all_marked_read = update_forum_tracking_info($forum_id, $topic_data['forum_last_post_time'], $topic_data['forum_mark_time'] ?? false, false);
}
else
{
	$all_marked_read = true;
}

// If there are absolutely no more unread posts in this forum and unread posts shown, we can savely show the #unread link
if ($all_marked_read)
{
	if ($post_unread)
	{
		$template->assign_vars([
			'U_VIEW_UNREAD_POST'	=> '#unread',
		]);
	}
	else if (isset($topic_tracking_info[$topic_id]) && $topic_data['topic_last_post_time'] > $topic_tracking_info[$topic_id])
	{
		$template->assign_vars([
			'U_VIEW_UNREAD_POST'	=> append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id&amp;view=unread") . '#unread',
		]);
	}
}
else if (!$all_marked_read)
{
	// What can happen is that we are at the last displayed page. If so, we also display the #unread link based in $post_unread
	if ($last_page && $post_unread)
	{
		$template->assign_vars([
			'U_VIEW_UNREAD_POST'	=> '#unread',
		]);
	}
	else if (!$last_page)
	{
		$template->assign_vars([
			'U_VIEW_UNREAD_POST'	=> append_sid(PHPBB_ROOT_PATH . 'viewtopic.php', "t=$topic_id&amp;view=unread") . '#unread',
		]);
	}
}

// let's set up quick_reply
$s_quick_reply = false;
if ($config['allow_quick_reply'])
{
	$s_quick_reply = require(PHPBB_ROOT_PATH . 'includes/quick_reply.php');
}

if ($s_can_vote && !$s_quick_reply)
{
	add_form_key('posting');
}

// We overwrite $_REQUEST['f'] if there is no forum specified
// to be able to display the correct online list.
// One downside is that the user currently viewing this topic/post is not taken into account.
if (empty($_REQUEST['f']))
{
	$_REQUEST['f'] = $forum_id;
}

// We need to do the same with the topic_id. See #53025.
if (empty($_REQUEST['t']) && !empty($topic_id))
{
	$_REQUEST['t'] = $topic_id;
}

// Output the page
page_header($topic_data['topic_title'] . ' - ' . $topic_data['forum_name'], true, $forum_id);

$template->set_filenames([
	'body' => ($view == 'print') ? 'viewtopic_print.html' : 'viewtopic_body.html']
);
make_jumpbox(append_sid(PHPBB_ROOT_PATH . 'viewforum.php'), $forum_id);

page_footer();
