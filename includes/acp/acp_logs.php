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

class acp_logs
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;
	var $log_type;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $config;

		$user->add_lang('mcp');

		// Set up general vars
		$action		= request_var('action', '');
		$forum_id	= request_var('f', 0);
		$topic_id	= request_var('t', 0);
		$start		= request_var('start', 0);
		$deletemark = (!empty($_POST['delmarked'])) ? true : false;
		$deleteall	= (!empty($_POST['delall'])) ? true : false;
		$marked		= request_var('mark', [0]);

		// Sort keys
		$sort_days	= request_var('st', 0);
		$sort_key	= request_var('sk', 't');
		$sort_dir	= request_var('sd', 'd');

		$this->tpl_name = 'acp_logs';
		$this->log_type = constant('LOG_' . strtoupper($mode));

		// Delete entries if requested and able
		if (($deletemark || $deleteall) && $auth->acl_get('a_clearlogs'))
		{
			if (confirm_box(true))
			{
				$where_sql = '';

				if ($deletemark && sizeof($marked))
				{
					$sql_in = [];
					foreach ($marked as $mark)
					{
						$sql_in[] = $mark;
					}
					$where_sql = ' AND ' . $db->sql_in_set('log_id', $sql_in);
					unset($sql_in);
				}

				if ($where_sql || $deleteall)
				{
					$sql = 'DELETE FROM ' . LOG_TABLE . "
						WHERE log_type = {$this->log_type}
						$where_sql";
					$db->sql_query($sql);

					add_log('admin', 'LOG_CLEAR_' . strtoupper($mode));
				}
			}
			else
			{
				confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields([
					'f'			=> $forum_id,
					'start'		=> $start,
					'delmarked'	=> $deletemark,
					'delall'	=> $deleteall,
					'mark'		=> $marked,
					'st'		=> $sort_days,
					'sk'		=> $sort_key,
					'sd'		=> $sort_dir,
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> $action])
				);
			}
		}

		// Sorting
		$limit_days = [0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']];
		$sort_by_text = ['u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']];
		$sort_by_sql = ['u' => 'u.username_clean', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation'];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

		$keywords = utf8_normalize_nfc(request_var('keywords', '', true));
		$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';

		$l_title = $user->lang['ACP_' . strtoupper($mode) . '_LOGS'];
		$l_title_explain = $user->lang['ACP_' . strtoupper($mode) . '_LOGS_EXPLAIN'];

		$this->page_title = $l_title;

		// Define forum list if we're looking @ mod logs
		if ($mode == 'mod')
		{
			$forum_box = '<option value="0">' . $user->lang['ALL_FORUMS'] . '</option>' . make_forum_select($forum_id, false, true);

			$template->assign_vars([
				'S_SHOW_FORUMS'			=> true,
				'S_FORUM_BOX'			=> $forum_box]
			);
		}

		// Grab log data
		$log_data = [];
		$log_count = 0;
		$start = view_log($mode, $log_data, $log_count, $config['topics_per_page'], $start, $forum_id, 0, 0, $sql_where, $sql_sort, $keywords);

		$template->assign_vars([
			'L_TITLE'		=> $l_title,
			'L_EXPLAIN'		=> $l_title_explain,
			'U_ACTION'		=> $this->u_action . "&amp;$u_sort_param$keywords_param&amp;start=$start",

			'S_ON_PAGE'		=> on_page($log_count, $config['topics_per_page'], $start),
			'PAGINATION'	=> generate_pagination($this->u_action . "&amp;$u_sort_param$keywords_param", $log_count, $config['topics_per_page'], $start, true),

			'S_LIMIT_DAYS'	=> $s_limit_days,
			'S_SORT_KEY'	=> $s_sort_key,
			'S_SORT_DIR'	=> $s_sort_dir,
			'S_CLEARLOGS'	=> $auth->acl_get('a_clearlogs'),
			'S_KEYWORDS'	=> $keywords,
			]
		);

		foreach ($log_data as $row)
		{
			$data = [];

			$checks = ['viewtopic', 'viewlogs', 'viewforum'];
			if ($mode == 'gallery')
			{
				$checks = array('viewimage', 'viewalbum');
			}
			foreach ($checks as $check)
			{
				if (isset($row[$check]) && $row[$check])
				{
					$data[] = '<a href="' . $row[$check] . '">' . $user->lang['LOGVIEW_' . strtoupper($check)] . '</a>';
				}
			}

			$template->assign_block_vars('log', [
				'USERNAME'			=> $row['username_full'],
				'REPORTEE_USERNAME'	=> ($row['reportee_username'] && $row['user_id'] != $row['reportee_id']) ? $row['reportee_username_full'] : '',

				'IP'				=> $row['ip'],
				'DATE'				=> $user->format_date($row['time']),
				'ACTION'			=> $row['action'],
				'DATA'				=> (sizeof($data)) ? implode(' | ', $data) : '',
				'ID'				=> $row['id'],
				]
			);
		}
	}
}
