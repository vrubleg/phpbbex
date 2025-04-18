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

class acp_main
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template;

		// Show restore permissions notice
		if ($user->data['user_perm_from'] && $auth->acl_get('a_switchperm'))
		{
			$this->tpl_name = 'acp_main';
			$this->page_title = 'ACP_MAIN';

			$sql = 'SELECT user_id, username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user->data['user_perm_from'];
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$perm_from = '<strong' . (($user_row['user_colour']) ? ' style="color: #' . $user_row['user_colour'] . '">' : '>');
			$perm_from .= ($user_row['user_id'] != ANONYMOUS) ? '<a href="' . append_sid(PHPBB_ROOT_PATH . 'memberlist.php', 'mode=viewprofile&amp;u=' . $user_row['user_id']) . '">' : '';
			$perm_from .= $user_row['username'];
			$perm_from .= ($user_row['user_id'] != ANONYMOUS) ? '</a>' : '';
			$perm_from .= '</strong>';

			$template->assign_vars(array(
				'S_RESTORE_PERMISSIONS'		=> true,
				'U_RESTORE_PERMISSIONS'		=> append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'mode=restore_perm'),
				'PERM_FROM'					=> $perm_from,
				'L_PERMISSIONS_TRANSFERRED_EXPLAIN'	=> sprintf($user->lang['PERMISSIONS_TRANSFERRED_EXPLAIN'], $perm_from, append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'mode=restore_perm')),
			));

			return;
		}

		$action = request_var('action', '');

		if ($action)
		{
			if ($action === 'admlogout')
			{
				$user->unset_admin();
				$redirect_url = append_sid(PHPBB_ROOT_PATH . 'index.php');
				meta_refresh(3, $redirect_url);
				trigger_error($user->lang['ADM_LOGGED_OUT'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . $redirect_url . '">', '</a>'));
			}

			if (!confirm_box(true))
			{
				switch ($action)
				{
					case 'online':
						$confirm = true;
						$confirm_lang = 'RESET_ONLINE_CONFIRM';
					break;
					case 'stats':
						$confirm = true;
						$confirm_lang = 'RESYNC_STATS_CONFIRM';
					break;
					case 'user':
						$confirm = true;
						$confirm_lang = 'RESYNC_POSTCOUNTS_CONFIRM';
					break;
					case 'topics':
						$confirm = true;
						$confirm_lang = 'RESYNC_TOPICCOUNTS_CONFIRM';
					break;
					case 'rates':
						$confirm = true;
						$confirm_lang = 'RESYNC_RATES_CONFIRM';
					break;
					case 'date':
						$confirm = true;
						$confirm_lang = 'RESET_DATE_CONFIRM';
					break;
					case 'db_track':
						$confirm = true;
						$confirm_lang = 'RESYNC_POST_MARKING_CONFIRM';
					break;
					case 'purge_cache':
						$confirm = true;
						$confirm_lang = 'PURGE_CACHE_CONFIRM';
					break;
					case 'purge_sessions':
						$confirm = true;
						$confirm_lang = 'PURGE_SESSIONS_CONFIRM';
					break;

					default:
						$confirm = true;
						$confirm_lang = 'CONFIRM_OPERATION';
				}

				if ($confirm)
				{
					confirm_box(false, $user->lang[$confirm_lang], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action,
					)));
				}
			}
			else
			{
				switch ($action)
				{

					case 'online':
						if (!$auth->acl_get('a_board'))
						{
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						set_config('record_online_users', 1, true);
						set_config('record_online_date', time(), true);
						add_log('admin', 'LOG_RESET_ONLINE');
					break;

					case 'stats':
						if (!$auth->acl_get('a_board'))
						{
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$sql = 'SELECT COUNT(post_id) AS stat
							FROM ' . POSTS_TABLE . '
							WHERE post_approved = 1';
						$result = $db->sql_query($sql);
						set_config('num_posts', (int) $db->sql_fetchfield('stat'), true);
						$db->sql_freeresult($result);

						$sql = 'SELECT COUNT(topic_id) AS stat
							FROM ' . TOPICS_TABLE . '
							WHERE topic_approved = 1';
						$result = $db->sql_query($sql);
						set_config('num_topics', (int) $db->sql_fetchfield('stat'), true);
						$db->sql_freeresult($result);

						$sql = 'SELECT COUNT(user_id) AS stat
							FROM ' . USERS_TABLE . '
							WHERE user_type IN (' . USER_NORMAL . ',' . USER_FOUNDER . ')';
						$result = $db->sql_query($sql);
						set_config('num_users', (int) $db->sql_fetchfield('stat'), true);
						$db->sql_freeresult($result);

						$sql = 'SELECT COUNT(attach_id) as stat
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE is_orphan = 0';
						$result = $db->sql_query($sql);
						set_config('num_files', (int) $db->sql_fetchfield('stat'), true);
						$db->sql_freeresult($result);

						$sql = 'SELECT SUM(filesize) as stat
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE is_orphan = 0';
						$result = $db->sql_query($sql);
						set_config('upload_dir_size', (float) $db->sql_fetchfield('stat'), true);
						$db->sql_freeresult($result);

						if (!function_exists('update_last_username'))
						{
							require_once(PHPBB_ROOT_PATH . "includes/functions_user.php");
						}
						update_last_username();

						add_log('admin', 'LOG_RESYNC_STATS');
					break;

					case 'user':
						if (!$auth->acl_get('a_board'))
						{
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						// Resync post counts
						$start = $max_post_id = 0;

						// Find the maximum post ID, we can only stop the cycle when we've reached it
						$sql = 'SELECT MAX(forum_last_post_id) as max_post_id
							FROM ' . FORUMS_TABLE;
						$result = $db->sql_query($sql);
						$max_post_id = (int) $db->sql_fetchfield('max_post_id');
						$db->sql_freeresult($result);

						// No maximum post id? :o
						if (!$max_post_id)
						{
							$sql = 'SELECT MAX(post_id) as max_post_id
								FROM ' . POSTS_TABLE;
							$result = $db->sql_query($sql);
							$max_post_id = (int) $db->sql_fetchfield('max_post_id');
							$db->sql_freeresult($result);
						}

						// Still no maximum post id? Then we are finished
						if (!$max_post_id)
						{
							add_log('admin', 'LOG_RESYNC_POSTCOUNTS');
							break;
						}

						$step = ($config['num_posts']) ? (max((int) ($config['num_posts'] / 5), 20000)) : 20000;
						$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_posts = 0');

						while ($start < $max_post_id)
						{
							$sql = 'SELECT COUNT(post_id) AS num_posts, poster_id
								FROM ' . POSTS_TABLE . '
								WHERE post_id BETWEEN ' . ($start + 1) . ' AND ' . ($start + $step) . '
									AND post_postcount = 1 AND post_approved = 1
								GROUP BY poster_id';
							$result = $db->sql_query($sql);

							if ($row = $db->sql_fetchrow($result))
							{
								do
								{
									$sql = 'UPDATE ' . USERS_TABLE . " SET user_posts = user_posts + {$row['num_posts']} WHERE user_id = {$row['poster_id']}";
									$db->sql_query($sql);
								}
								while ($row = $db->sql_fetchrow($result));
							}
							$db->sql_freeresult($result);

							$start += $step;
						}

						add_log('admin', 'LOG_RESYNC_POSTCOUNTS');

					break;

					case 'topics':
						if (!$auth->acl_get('a_board'))
						{
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$sql = 'SELECT COUNT(p.post_id) AS num_topics, u.user_id
							FROM ' . USERS_TABLE . ' u
							LEFT JOIN  ' . TOPICS_TABLE . ' t ON (u.user_id = t.topic_poster)
							LEFT JOIN  ' . POSTS_TABLE . ' p ON (p.post_id = t.topic_first_post_id AND p.post_postcount = 1)
							GROUP BY u.user_id';
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_topics = {$row['num_topics']} WHERE user_id = {$row['user_id']}");
						}
						$db->sql_freeresult($result);

						add_log('admin', 'LOG_RESYNC_TOPICCOUNTS');

					break;

					case 'rates':
						if (!$auth->acl_get('a_board'))
						{
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if (!function_exists('resync_rates'))
						{
							require_once(PHPBB_ROOT_PATH . 'includes/functions_rating.php');
						}
						resync_rates();

						add_log('admin', 'LOG_RESYNC_RATES');

					break;

					case 'date':
						if (!$auth->acl_get('a_board'))
						{
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						set_config('board_startdate', time() - 1);
						add_log('admin', 'LOG_RESET_DATE');
					break;

					case 'db_track':
						$db->sql_query('TRUNCATE TABLE ' . TOPICS_POSTED_TABLE);

						// This can get really nasty... therefore we only do the last six months
						$get_from_time = time() - (6 * 4 * 7 * 24 * 60 * 60);

						// Select forum ids, do not include categories
						$sql = 'SELECT forum_id
							FROM ' . FORUMS_TABLE . '
							WHERE forum_type <> ' . FORUM_CAT;
						$result = $db->sql_query($sql);

						$forum_ids = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$forum_ids[] = $row['forum_id'];
						}
						$db->sql_freeresult($result);

						// Any global announcements? ;)
						$forum_ids[] = 0;

						// Now go through the forums and get us some topics...
						foreach ($forum_ids as $forum_id)
						{
							$sql = 'SELECT p.poster_id, p.topic_id
								FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t
								WHERE t.forum_id = ' . $forum_id . '
									AND t.topic_moved_id = 0
									AND t.topic_last_post_time > ' . $get_from_time . '
									AND t.topic_id = p.topic_id
									AND p.poster_id <> ' . ANONYMOUS . '
								GROUP BY p.poster_id, p.topic_id';
							$result = $db->sql_query($sql);

							$posted = array();
							while ($row = $db->sql_fetchrow($result))
							{
								$posted[$row['poster_id']][] = $row['topic_id'];
							}
							$db->sql_freeresult($result);

							$sql_ary = array();
							foreach ($posted as $user_id => $topic_row)
							{
								foreach ($topic_row as $topic_id)
								{
									$sql_ary[] = array(
										'user_id'		=> (int) $user_id,
										'topic_id'		=> (int) $topic_id,
										'topic_posted'	=> 1,
									);
								}
							}
							unset($posted);

							if (sizeof($sql_ary))
							{
								$db->sql_multi_insert(TOPICS_POSTED_TABLE, $sql_ary);
							}
						}

						add_log('admin', 'LOG_RESYNC_POST_MARKING');
					break;

					case 'purge_cache':
						if ((int) $user->data['user_type'] !== USER_FOUNDER)
						{
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						global $cache;
						$cache->purge();

						// Clear permissions
						$auth->acl_clear_prefetch();
						cache_moderators();

						add_log('admin', 'LOG_PURGE_CACHE');
					break;

					case 'purge_sessions':
						if ((int) $user->data['user_type'] !== USER_FOUNDER)
						{
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$tables = array(CONFIRM_TABLE, SESSIONS_TABLE);

						foreach ($tables as $table)
						{
							$db->sql_query("TRUNCATE TABLE $table");
						}

						// let's restore the admin session
						$reinsert_ary = array(
								'session_id'			=> (string) $user->session_id,
								'session_page'			=> (string) substr($user->page['page'], 0, 199),
								'session_user_id'		=> (int) $user->data['user_id'],
								'session_start'			=> (int) $user->data['session_start'],
								'session_last_visit'	=> (int) $user->data['session_last_visit'],
								'session_time'			=> (int) $user->time_now,
								'session_browser'		=> (string) trim(substr($user->browser, 0, 249)),
								'session_forwarded_for'	=> (string) $user->forwarded_for,
								'session_ip'			=> (string) $user->ip,
								'session_autologin'		=> (int) $user->data['session_autologin'],
								'session_admin'			=> 1,
								'session_viewonline'	=> (int) $user->data['session_viewonline'],
						);

						$sql = 'INSERT INTO ' . SESSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $reinsert_ary);
						$db->sql_query($sql);

						add_log('admin', 'LOG_PURGE_SESSIONS');
					break;
				}
			}
		}

		// Version check
		$user->add_lang('install');

		if ($auth->acl_get('a_server') && version_compare(PHP_VERSION, '7.4.0', '<'))
		{
			$template->assign_vars(array(
				'S_PHP_VERSION_OLD'	=> true,
			));
		}

		$latest_version_info = false;
		if (($latest_version_info = obtain_latest_version_info(request_var('versioncheck_force', false))) === false)
		{
			$template->assign_var('S_VERSIONCHECK_FAIL', true);
		}
		else
		{
			$info = explode("\n", $latest_version_info);
			$latest_version = trim($info[0]);
			$announcement_url = trim($info[1]);
			$announcement_url = (strpos($announcement_url, '&amp;') === false) ? str_replace('&', '&amp;', $announcement_url) : $announcement_url;

			$template->assign_vars(array(
				'S_VERSION_UP_TO_DATE'	=> phpbb_version_compare($latest_version, $config['phpbbex_version'], '<='),
				'UPDATE_INSTRUCTIONS'	=> $user->lang('UPDATE_INSTRUCTIONS', $latest_version, $announcement_url),
				'U_UPDATE_ANNOUNCEMENT'	=> $announcement_url,
			));
		}

		// Get forum statistics
		$total_posts = $config['num_posts'];
		$total_topics = $config['num_topics'];
		$total_users = $config['num_users'];
		$total_files = $config['num_files'];

		$start_date = $user->format_date($config['board_startdate']);

		$boarddays = (time() - $config['board_startdate']) / 86400;

		$posts_per_day = sprintf('%.2f', $total_posts / $boarddays);
		$topics_per_day = sprintf('%.2f', $total_topics / $boarddays);
		$users_per_day = sprintf('%.2f', $total_users / $boarddays);
		$files_per_day = sprintf('%.2f', $total_files / $boarddays);

		$upload_dir_size = get_formatted_filesize($config['upload_dir_size']);

		$avatar_dir_size = 0;

		if ($avatar_dir = @opendir(PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH))
		{
			while (($file = readdir($avatar_dir)) !== false)
			{
				if ($file[0] != '.' && $file != 'CVS' && strpos($file, 'index.') === false)
				{
					$avatar_dir_size += filesize(PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH . '/' . $file);
				}
			}
			closedir($avatar_dir);

			$avatar_dir_size = get_formatted_filesize($avatar_dir_size);
		}
		else
		{
			// Couldn't open Avatar dir.
			$avatar_dir_size = $user->lang['NOT_AVAILABLE'];
		}

		if ($posts_per_day > $total_posts)
		{
			$posts_per_day = $total_posts;
		}

		if ($topics_per_day > $total_topics)
		{
			$topics_per_day = $total_topics;
		}

		if ($users_per_day > $total_users)
		{
			$users_per_day = $total_users;
		}

		if ($files_per_day > $total_files)
		{
			$files_per_day = $total_files;
		}

		if ($config['allow_attachments'] || $config['allow_pm_attach'])
		{
			$sql = 'SELECT COUNT(attach_id) AS total_orphan
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE is_orphan = 1
					AND filetime < ' . (time() - 3*60*60);
			$result = $db->sql_query($sql);
			$total_orphan = (int) $db->sql_fetchfield('total_orphan');
			$db->sql_freeresult($result);
		}
		else
		{
			$total_orphan = false;
		}

		$dbsize = get_database_size();

		$template->assign_vars(array(
			'TOTAL_POSTS'		=> $total_posts,
			'POSTS_PER_DAY'		=> $posts_per_day,
			'TOTAL_TOPICS'		=> $total_topics,
			'TOPICS_PER_DAY'	=> $topics_per_day,
			'TOTAL_USERS'		=> $total_users,
			'USERS_PER_DAY'		=> $users_per_day,
			'TOTAL_FILES'		=> $total_files,
			'FILES_PER_DAY'		=> $files_per_day,
			'START_DATE'		=> $start_date,
			'AVATAR_DIR_SIZE'	=> $avatar_dir_size,
			'DBSIZE'			=> $dbsize,
			'UPLOAD_DIR_SIZE'	=> $upload_dir_size,
			'TOTAL_ORPHAN'		=> $total_orphan,
			'S_TOTAL_ORPHAN'	=> ($total_orphan === false) ? false : true,
			'GZIP_COMPRESSION'	=> ($config['gzip_compress'] && @extension_loaded('zlib')) ? $user->lang['ON'] : $user->lang['OFF'],
			'DATABASE_INFO'		=> $db->sql_server_info(),
			'PHPBBEX_VERSION'	=> $config['phpbbex_version'],

			'U_ACTION'			=> $this->u_action,
			'U_ADMIN_LOG'		=> append_sid(PHPBB_ADMIN_PATH . 'index.php', 'i=logs&amp;mode=admin'),
			'U_INACTIVE_USERS'	=> append_sid(PHPBB_ADMIN_PATH . 'index.php', 'i=inactive&amp;mode=list'),
			'U_VERSIONCHECK'	=> append_sid(PHPBB_ADMIN_PATH . 'index.php', ''),
			'U_VERSIONCHECK_FORCE'	=> append_sid(PHPBB_ADMIN_PATH . 'index.php', 'versioncheck_force=1'),

			'S_ACTION_OPTIONS'	=> ($auth->acl_get('a_board')) ? true : false,
			'S_FOUNDER'			=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,
			)
		);

		$log_data = array();
		$log_count = false;

		if ($auth->acl_get('a_viewlogs'))
		{
			view_log('admin', $log_data, $log_count, 5);

			foreach ($log_data as $row)
			{
				$template->assign_block_vars('log', array(
					'USERNAME'	=> $row['username_full'],
					'IP'		=> $row['ip'],
					'DATE'		=> $user->format_date($row['time']),
					'ACTION'	=> $row['action'])
				);
			}
		}

		if ($auth->acl_get('a_user'))
		{
			$user->add_lang('memberlist');

			$inactive = array();
			$inactive_count = 0;

			view_inactive_users($inactive, $inactive_count, 10);

			foreach ($inactive as $row)
			{
				$template->assign_block_vars('inactive', array(
					'INACTIVE_DATE'	=> $user->format_date($row['user_inactive_time']),
					'REMINDED_DATE'	=> $user->format_date($row['user_reminded_time']),
					'JOINED'		=> $user->format_date($row['user_regdate']),
					'LAST_VISIT'	=> (!$row['user_lastvisit']) ? ' - ' : $user->format_date($row['user_lastvisit']),

					'REASON'		=> $row['inactive_reason'],
					'USER_ID'		=> $row['user_id'],
					'POSTS'			=> ($row['user_posts']) ? $row['user_posts'] : 0,
					'REMINDED'		=> $row['user_reminded'],

					'REMINDED_EXPLAIN'	=> $user->lang('USER_LAST_REMINDED', (int) $row['user_reminded'], $user->format_date($row['user_reminded_time'])),

					'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, append_sid(PHPBB_ADMIN_PATH . 'index.php', 'i=users&amp;mode=overview')),
					'USERNAME'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
					'USER_COLOR'		=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),

					'U_USER_ADMIN'	=> append_sid(PHPBB_ADMIN_PATH . 'index.php', "i=users&amp;mode=overview&amp;u={$row['user_id']}"),
					'U_SEARCH_USER'	=> ($auth->acl_get('u_search')) ? append_sid(PHPBB_ROOT_PATH . 'search.php', "author_id={$row['user_id']}&amp;sr=posts") : '',
				));
			}

			$option_ary = array('activate' => 'ACTIVATE', 'delete' => 'DELETE');
			if ($config['email_enable'])
			{
				$option_ary += array('remind' => 'REMIND');
			}

			$template->assign_vars(array(
				'S_INACTIVE_USERS'		=> true,
				'S_INACTIVE_OPTIONS'	=> build_select($option_ary))
			);
		}

		// Warn if install is still present
		/*
		if (file_exists(PHPBB_ROOT_PATH . 'install') && !is_file(PHPBB_ROOT_PATH . 'install'))
		{
			$template->assign_var('S_REMOVE_INSTALL', true);
		}
		*/

		if (!(stripos(PHP_OS, 'WIN') === 0) && !defined('PHPBB_DISABLE_CONFIG_CHECK') && file_exists(PHPBB_ROOT_PATH . 'config.php') && phpbb_is_writable(PHPBB_ROOT_PATH . 'config.php'))
		{
			// World-Writable? (000x)
			$template->assign_var('S_WRITABLE_CONFIG', (bool) (@fileperms(PHPBB_ROOT_PATH . 'config.php') & 0x0002));
		}

		if (extension_loaded('mbstring'))
		{
			$template->assign_vars(array(
				'S_MBSTRING_LOADED'						=> true,
				'S_MBSTRING_FUNC_OVERLOAD_FAIL'			=> defined('MB_OVERLOAD_MAIL') && defined('MB_OVERLOAD_STRING') && (intval(@ini_get('mbstring.func_overload')) & (MB_OVERLOAD_MAIL | MB_OVERLOAD_STRING)),
				'S_MBSTRING_ENCODING_TRANSLATION_FAIL'	=> (@ini_get('mbstring.encoding_translation') != 0),
				'S_MBSTRING_HTTP_INPUT_FAIL'			=> !in_array(@ini_get('mbstring.http_input'), array('pass', '')),
				'S_MBSTRING_HTTP_OUTPUT_FAIL'			=> !in_array(@ini_get('mbstring.http_output'), array('pass', '')),
			));
		}

		$this->tpl_name = 'acp_main';
		$this->page_title = 'ACP_MAIN';
	}
}
