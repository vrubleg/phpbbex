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
		global $config, $db, $user, $auth, $template, $cache;

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

			$template->assign_vars([
				'S_RESTORE_PERMISSIONS'     => true,
				'U_RESTORE_PERMISSIONS'     => append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'mode=restore_perm'),
				'PERM_FROM'                 => $perm_from,
				'L_PERMISSIONS_TRANSFERRED_EXPLAIN' => sprintf($user->lang['PERMISSIONS_TRANSFERRED_EXPLAIN'], $perm_from, append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'mode=restore_perm')),
			]);

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
					case 'stats':
						$confirm = true;
						$confirm_lang = 'RESYNC_STATS_CONFIRM';
					break;
					case 'user_stats':
						$confirm = true;
						$confirm_lang = 'RESYNC_USER_STATS_CONFIRM';
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
					confirm_box(false, $user->lang[$confirm_lang], build_hidden_fields([
						'i'         => $id,
						'mode'      => $mode,
						'action'    => $action,
					]));
				}
			}
			else
			{
				switch ($action)
				{
					case 'stats':

						set_time_limit(0);
						ignore_user_abort(true);

						sync('topic', '', '', false, true);
						sync('forum', '', '', false, true);

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

					case 'user_stats':

						set_time_limit(0);
						ignore_user_abort(true);

						// Resync post counts.
						$sql = 'UPDATE ' . USERS_TABLE . ' u
							LEFT JOIN (
								SELECT poster_id, COUNT(post_id) AS num_posts
								FROM ' . POSTS_TABLE . '
								WHERE post_postcount = 1
									AND post_approved = 1
								GROUP BY poster_id
							) post_counts ON post_counts.poster_id = u.user_id
							SET u.user_posts = COALESCE(post_counts.num_posts, 0)';
						$db->sql_query($sql);

						// Resync topic counts.
						$sql = 'UPDATE ' . USERS_TABLE . ' u
							LEFT JOIN (
								SELECT p.poster_id AS user_id, COUNT(t.topic_id) AS num_topics
								FROM ' . TOPICS_TABLE . ' t
								INNER JOIN ' . POSTS_TABLE . ' p ON p.post_id = t.topic_first_post_id
								WHERE t.topic_moved_id = 0
									AND p.post_postcount = 1
									AND p.post_approved = 1
								GROUP BY p.poster_id
							) topic_counts ON topic_counts.user_id = u.user_id
							SET u.user_topics = COALESCE(topic_counts.num_topics, 0)';
						$db->sql_query($sql);

						// Resync ratings.
						require_once(PHPBB_ROOT_PATH . 'includes/functions_rating.php');
						resync_rates();

						add_log('admin', 'LOG_RESYNC_USER_STATS');

					break;

					case 'purge_cache':

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

						$tables = [CONFIRM_TABLE, SESSIONS_TABLE, SESSIONS_KEYS_TABLE];

						foreach ($tables as $table)
						{
							$db->sql_query("TRUNCATE TABLE {$table}");
						}

						// let's restore the admin session
						$reinsert_ary = [
								'session_id'            => (string) $user->session_id,
								'session_user_id'       => (int) $user->data['user_id'],
								'session_start'         => (int) $user->data['session_start'],
								'session_last_visit'    => (int) $user->data['session_last_visit'],
								'session_time'          => (int) $user->time_now,
								'session_browser_ua'    => (string) $user->browser_ua,
								'session_forwarded_for' => (string) $user->forwarded_for,
								'session_ip'            => (string) $user->ip,
								'session_autologin'     => (int) $user->data['session_autologin'],
								'session_admin'         => 1,
								'session_viewonline'    => (int) $user->data['session_viewonline'],
						];

						$sql = 'INSERT INTO ' . SESSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $reinsert_ary);
						$db->sql_query($sql);

						add_log('admin', 'LOG_PURGE_SESSIONS');

					break;
				}
			}
		}

		// Version check
		if ($auth->acl_get('a_server') && PHP_VERSION_ID < 70400)
		{
			$template->assign_vars([
				'S_PHP_VERSION_OLD' => true,
			]);
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

			$template->assign_vars([
				'S_VERSION_UP_TO_DATE'  => version_compare($latest_version, $config['phpbbex_version'], '<='),
				'L_UPDATE_AVAILABLE'    => $user->lang('UPDATE_AVAILABLE', $latest_version, $announcement_url),
				'U_UPDATE_ANNOUNCEMENT' => $announcement_url,
			]);
		}

		// Get forum statistics.

		$avatar_dir_size = 0;

		if ($avatar_dir = @opendir(PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH))
		{
			while (($file = readdir($avatar_dir)) !== false)
			{
				if ($file[0] != '.' && strpos($file, 'index.') === false)
				{
					$avatar_dir_size += filesize(PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH . '/' . $file);
				}
			}
			closedir($avatar_dir);

			$avatar_dir_size = get_formatted_filesize($avatar_dir_size);
		}
		else
		{
			$avatar_dir_size = $user->lang['NOT_AVAILABLE'];
		}

		$sql = 'SELECT COUNT(attach_id) AS total_orphan
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE is_orphan = 1
				AND filetime < ' . (time() - 3*60*60);
		$result = $db->sql_query($sql);
		$total_orphan = (int) $db->sql_fetchfield('total_orphan');
		$db->sql_freeresult($result);

		$board_days = max(1.0, (time() - $config['board_startdate']) / 86400);

		$template->assign_vars([
			'START_DATE'        => $user->format_date($config['board_startdate'], false, true, true),
			'START_TIME_AGO'    => sprintf($user->lang['N_AGO'], get_verbal_time_delta($config['board_startdate'], time(), 'days', 2)),
			'PHPBBEX_VERSION'   => $config['phpbbex_version'],
			'TOTAL_POSTS'       => $config['num_posts'],
			'TOTAL_TOPICS'      => $config['num_topics'],
			'TOTAL_USERS'       => $config['num_users'],
			'TOTAL_FILES'       => $config['num_files'],
			'POSTS_PER_DAY'     => sprintf($user->lang['N_PER_DAY'], $config['num_posts'] / $board_days),
			'TOPICS_PER_DAY'    => sprintf($user->lang['N_PER_DAY'], $config['num_topics'] / $board_days),
			'USERS_PER_DAY'     => sprintf($user->lang['N_PER_DAY'], $config['num_users'] / $board_days),
			'FILES_PER_DAY'     => sprintf($user->lang['N_PER_DAY'], $config['num_files'] / $board_days),
			'TOTAL_ORPHAN'      => $total_orphan,
			'UPLOAD_DIR_SIZE'   => get_formatted_filesize($config['upload_dir_size']),
			'UPLOAD_DIR_QUOTA'  => $config['attachment_quota'] ? sprintf($user->lang['N_QUOTA'], get_formatted_filesize($config['attachment_quota'])) : false,
			'S_UPLOAD_DIR_WARN' => $config['attachment_quota'] ? $config['upload_dir_size'] > ($config['attachment_quota'] * 0.8) : false,
			'AVATAR_DIR_SIZE'   => $avatar_dir_size,
			'DBSIZE'            => get_database_size(),
			'DATABASE_INFO'     => $db->sql_server_info(),

			'U_ACTION'          => $this->u_action,
			'U_ADMIN_LOG'       => append_sid(PHPBB_ADMIN_PATH . 'index.php', 'i=logs&amp;mode=admin'),
			'U_INACTIVE_USERS'  => append_sid(PHPBB_ADMIN_PATH . 'index.php', 'i=inactive&amp;mode=list'),
			'U_VERSIONCHECK'    => append_sid(PHPBB_ADMIN_PATH . 'index.php', ''),
			'U_VERSIONCHECK_FORCE'  => append_sid(PHPBB_ADMIN_PATH . 'index.php', 'versioncheck_force=1'),

			'S_ACTION_OPTIONS'  => (bool) $auth->acl_get('a_board'),
			'S_FOUNDER'         => ($user->data['user_type'] == USER_FOUNDER),
			]
		);

		$log_data = [];
		$log_count = false;

		if ($auth->acl_get('a_viewlogs'))
		{
			view_log('admin', $log_data, $log_count, 5);

			foreach ($log_data as $row)
			{
				$template->assign_block_vars('log', [
					'USERNAME'  => $row['username_full'],
					'IP'        => $row['ip'],
					'DATE'      => $user->format_date($row['time']),
					'ACTION'    => $row['action']]
				);
			}
		}

		if ($auth->acl_get('a_user'))
		{
			$user->add_lang('memberlist');

			$inactive = [];
			$inactive_count = 0;

			view_inactive_users($inactive, $inactive_count, 10);

			foreach ($inactive as $row)
			{
				$template->assign_block_vars('inactive', [
					'INACTIVE_DATE' => $user->format_date($row['user_inactive_time']),
					'REMINDED_DATE' => $user->format_date($row['user_reminded_time']),
					'JOINED'        => $user->format_date($row['user_regdate']),
					'LAST_VISIT'    => (!$row['user_lastvisit']) ? ' - ' : $user->format_date($row['user_lastvisit']),

					'REASON'        => $row['inactive_reason'],
					'USER_ID'       => $row['user_id'],
					'POSTS'         => $row['user_posts'] ?: 0,
					'REMINDED'      => $row['user_reminded'],

					'REMINDED_EXPLAIN'  => $user->lang('USER_LAST_REMINDED', (int) $row['user_reminded'], $user->format_date($row['user_reminded_time'])),

					'USERNAME_FULL'     => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, append_sid(PHPBB_ADMIN_PATH . 'index.php', 'i=users&amp;mode=overview')),
					'USERNAME'          => get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
					'USER_COLOR'        => get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),

					'U_USER_ADMIN'  => append_sid(PHPBB_ADMIN_PATH . 'index.php', "i=users&amp;mode=overview&amp;u={$row['user_id']}"),
					'U_SEARCH_USER' => ($auth->acl_get('u_search')) ? append_sid(PHPBB_ROOT_PATH . 'search.php', "author_id={$row['user_id']}&amp;sr=posts") : '',
				]);
			}

			$option_ary = ['activate' => 'ACTIVATE', 'delete' => 'DELETE'];
			if ($config['email_enable'])
			{
				$option_ary += ['remind' => 'REMIND'];
			}

			$template->assign_vars([
				'S_INACTIVE_USERS'      => true,
				'S_INACTIVE_OPTIONS'    => build_select($option_ary)]
			);
		}

		if (!(stripos(PHP_OS, 'WIN') === 0) && !defined('PHPBB_DISABLE_CONFIG_CHECK') && file_exists(PHPBB_ROOT_PATH . 'config.php') && phpbb_is_writable(PHPBB_ROOT_PATH . 'config.php'))
		{
			// World-Writable? (000x)
			$template->assign_var('S_WRITABLE_CONFIG', (bool) (@fileperms(PHPBB_ROOT_PATH . 'config.php') & 0x0002));
		}

		$template->assign_vars([
			'S_MBSTRING_FUNC_OVERLOAD_FAIL'         => !!@ini_get('mbstring.func_overload'),
			'S_MBSTRING_ENCODING_TRANSLATION_FAIL'  => !!@ini_get('mbstring.encoding_translation'),
		]);

		$this->tpl_name = 'acp_main';
		$this->page_title = 'ACP_MAIN';
	}
}
