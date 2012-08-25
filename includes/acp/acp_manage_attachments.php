<?php
/** 
*
* @package acp
* @version $Id: acp_manage_attachments.php,v 1.04 2008/03/14 19:19:47 rxu Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_manage_attachments
{
	var $u_action;
	
	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$user->add_lang(array('posting', 'viewtopic', 'acp/attachments'));

		$error = $notify = array();
		$submit = (isset($_POST['submit'])) ? true : false;
		$action = request_var('action', '');
		$start = request_var('start', 0);

		// Sort keys
		$sort_days	= request_var('st', 0);
		$sort_key	= request_var('sk', 't');
		$sort_dir	= request_var('sd', 'd');

		$form_key = 'acp_attach';
		add_form_key($form_key);

		if ($submit && !check_form_key($form_key))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$this->tpl_name = 'acp_manage_attachments';
		$this->page_title = 'ACP_MANAGE_ATTACHMENTS';

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action)
		);

		if ($submit)
		{
			$delete_files = (isset($_POST['delete'])) ? array_keys(request_var('delete', array('' => 0))) : array();
			$add_files = (isset($_POST['add'])) ? array_keys(request_var('add', array('' => 0))) : array();
			$post_ids = request_var('post_id', array('' => 0));
			
			$current_post_ids = request_var('current_post_id', array('' => 0));
			$current_topic_ids = request_var('current_topic_id', array('' => 0));
			$next_post_ids = $post_ids;
			$unset_topic_ids = $unset_post_ids = array();
			

			if (sizeof($delete_files) && sizeof($add_files))
			{
				foreach ($delete_files as $attach_id)
				{
					if (!empty($current_post_ids[$attach_id]) && !empty($next_post_ids[$attach_id]))
					{
						unset($current_post_ids[$attach_id]);
						unset($next_post_ids[$attach_id]);
					}
				}
			}

			if (sizeof($add_files))
			{
				$unset_post_ids = array_diff($current_post_ids, $next_post_ids);
				
				$sql = 'SELECT topic_id
					FROM ' . POSTS_TABLE . '
					WHERE ' . $db->sql_in_set('post_id', $next_post_ids);
				$result = $db->sql_query($sql);
				$next_topic_ids = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$next_topic_ids[] = $row['topic_id'];
				}
				$db->sql_freeresult($result);
				$unset_topic_ids = array_diff($current_topic_ids, $next_topic_ids);
			}


			if (sizeof($delete_files))
			{
				// Select those attachments we want to delete...
				$sql = 'SELECT real_filename
					FROM ' . ATTACHMENTS_TABLE . '
					WHERE ' . $db->sql_in_set('attach_id', $delete_files) . '
						AND is_orphan = 0';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$deleted_filenames[] = $row['real_filename'];
				}
				$db->sql_freeresult($result);
				delete_attachments('attach', $delete_files);
				add_log('admin', 'LOG_ATTACH_DEL', implode(', ', $deleted_filenames));
				$notify[] = sprintf($user->lang['LOG_ATTACH_DEL'], implode(', ', $deleted_filenames));
			}

			$upload_list = array();
			foreach ($add_files as $attach_id)
			{
				if (!in_array($attach_id, array_keys($delete_files)) && !empty($post_ids[$attach_id]) && $post_ids[$attach_id] != $current_post_ids[$attach_id])
				{
					$upload_list[$attach_id] = $post_ids[$attach_id];
				}
			}
			unset($add_files);

			if (sizeof($upload_list))
			{
				$template->assign_var('S_UPLOADING_FILES', true);

				$sql = 'SELECT forum_id, forum_name
					FROM ' . FORUMS_TABLE;
				$result = $db->sql_query($sql, 86400);

				$forum_names = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$forum_names[$row['forum_id']] = $row['forum_name'];
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT forum_id, topic_id, post_id, poster_id
					FROM ' . POSTS_TABLE . '
					WHERE ' . $db->sql_in_set('post_id', $upload_list);
				$result = $db->sql_query($sql);

				$post_info = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$post_info[$row['post_id']] = $row;
				}
				$db->sql_freeresult($result);

				// Select those attachments we want to change...
				$sql = 'SELECT *
					FROM ' . ATTACHMENTS_TABLE . '
					WHERE ' . $db->sql_in_set('attach_id', array_keys($upload_list)) . '
						AND is_orphan = 0';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$post_row = $post_info[$upload_list[$row['attach_id']]];

					$template->assign_block_vars('upload', array(
						'FILE_INFO'		=> sprintf($user->lang['LOG_ATTACH_REASSIGNED'], $post_row['post_id'], $row['real_filename']),
						'S_DENIED'		=> (!$auth->acl_get('f_attach', $post_row['forum_id'])) ? true : false,
						'L_DENIED'		=> (!$auth->acl_get('f_attach', $post_row['forum_id'])) ? sprintf($user->lang['UPLOAD_DENIED_FORUM'], $forum_names[$row['forum_id']]) : '')
					);

					if (!$auth->acl_get('f_attach', $post_row['forum_id']))
					{
						continue;
					}

					// Adjust attachment entry
					$sql_ary = array(
						'in_message'	=> 0,
						'is_orphan'		=> 0,
						'poster_id'		=> $post_row['poster_id'],
						'post_msg_id'	=> $post_row['post_id'],
						'topic_id'		=> $post_row['topic_id'],
					);

					$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE attach_id = ' . $row['attach_id'];
					$db->sql_query($sql);

					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET post_attachment = 1
						WHERE post_id = ' . $post_row['post_id'];
					$db->sql_query($sql);
					
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_attachment = 1
						WHERE topic_id = ' . $post_row['topic_id'];
					$db->sql_query($sql);

					if (sizeof($unset_post_ids))
					{
						$sql = 'UPDATE ' . POSTS_TABLE . '
							SET post_attachment = 0
							WHERE ' . $db->sql_in_set('post_id', $unset_post_ids);
						$db->sql_query($sql);
					}

					if (sizeof($unset_topic_ids))
					{
						$sql = 'UPDATE ' . TOPICS_TABLE . '
							SET topic_attachment = 0
							WHERE ' . $db->sql_in_set('topic_id', $unset_topic_ids);
						$db->sql_query($sql);
					}
					
					add_log('admin', 'LOG_ATTACH_REASSIGNED', $post_row['post_id'], $row['real_filename']);
				}
				$db->sql_freeresult($result);
			}
		}

		// Sorting
		$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
		$sort_by_text = array('f' => $user->lang['FILENAME'], 't' => $user->lang['FILEDATE'], 's' => $user->lang['FILESIZE'], 'x' => $user->lang['EXTENSION'], 'd' => $user->lang['DOWNLOADS'],'p' => $user->lang['ATTACH_POST_ID'], 'u' => $user->lang['AUTHOR']);
		$sort_by_sql = array('f' => 'a.real_filename', 't' => 'a.filetime', 's' => 'a.filesize', 'x' => 'a.extension', 'd' => 'a.download_count', 'p' => 'a.post_msg_id', 'u' => 'u.username');

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		$min_filetime = ($sort_days) ? (time() - ($sort_days * 86400)) : '';
		$limit_filetime = ($min_filetime) ? " AND a.filetime >= $min_filetime " : '';
		$start = ($sort_days && isset($_POST['sort'])) ? 0 : $start;

		$sql = 'SELECT COUNT(a.attach_id) AS num_files, SUM(a.filesize) AS total_size
			FROM ' . ATTACHMENTS_TABLE . " a
				WHERE a.is_orphan = 0
					$limit_filetime";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$num_files = (int) $row['num_files'];
		$total_size = (int) $row['total_size'];
		$total_size = ($total_size >= 1048576) ? sprintf('%.2f ' . $user->lang['MB'], ($total_size / 1048576)) : (($total_size >= 1024) ? sprintf('%.2f ' . $user->lang['KB'], ($total_size / 1024)) : sprintf('%.2f ' . $user->lang['BYTES'], $total_size));
		$db->sql_freeresult($result);		

		// Make sure $start is set to the last page if it exceeds the amount
		if ($start < 0 || $start > $num_files)
		{
			$start = ($start < 0) ? 0 : floor(($num_files - 1) / $config['posts_per_page']) * $config['posts_per_page'];
		}

		// If the user is trying to reach the second half of the attachments list, fetch it starting from the end
		$store_reverse = false;
		$sql_limit = $config['posts_per_page'];

		if ($start > $num_files / 2)
		{
			$store_reverse = true;

			if ($start + $config['posts_per_page'] > $num_files)
			{
				$sql_limit = min($config['posts_per_page'], max(1, $num_files - $start));
			}

			// Select the sort order. Add time sort anchor for non-time sorting cases
			$sql_sort_anchor = ($sort_key != 't') ? ', a.filetime ' . (($sort_dir == 'd') ? 'ASC' : 'DESC') : '';
			$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'ASC' : 'DESC') . $sql_sort_anchor;
			$sql_start = max(0, $num_files - $sql_limit - $start);
		}
		else
		{
			// Select the sort order. Add time sort anchor for non-time sorting cases
			$sql_sort_anchor = ($sort_key != 't') ? ', a.filetime ' . (($sort_dir == 'd') ? 'DESC' : 'ASC') : '';
			$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC') . $sql_sort_anchor;
			$sql_start = $start;
		}
		
		$attachments_list = array();

		// Just get the files
		$sql = 'SELECT a.*, u.username, u.user_colour, t.topic_title
			FROM ' . ATTACHMENTS_TABLE . ' a 
			LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = a.poster_id) 
			LEFT JOIN ' . TOPICS_TABLE . " t ON (a.topic_id = t.topic_id AND a.in_message = 0)
				WHERE a.is_orphan = 0
					$limit_filetime
						ORDER BY $sql_sort_order";
		$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);

		$i = ($store_reverse) ? $sql_limit - 1 : 0;
		while ($attachment_row = $db->sql_fetchrow($result))
		{
			$attachments_list[$i] = $attachment_row;
			($store_reverse) ? $i-- : $i++;
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'TOTAL_FILES'		=> $num_files,
			'TOTAL_SIZE'		=> $total_size,
			'PAGINATION'		=> generate_pagination($this->u_action . "&amp;$u_sort_param", $num_files, $config['posts_per_page'], $start, true),

			'S_ATTACHMENTS'		=> true,
			'S_ON_PAGE'			=> on_page($num_files, $config['posts_per_page'], $start),
			'S_LIMIT_DAYS'		=> $s_limit_days,
			'S_SORT_KEY'		=> $s_sort_key,
			'S_SORT_DIR'		=> $s_sort_dir)
		);

		// Grab extensions
		$extensions = array();
		$extensions = $cache->obtain_attach_extensions(true);

		for ($i = 0, $end = sizeof($attachments_list); $i < $end; ++$i)
		{
			$row =& $attachments_list[$i];
			$size_lang = ($row['filesize'] >= 1048576) ? $user->lang['MB'] : (($row['filesize'] >= 1024) ? $user->lang['KB'] : $user->lang['BYTES']);
			$row['filesize'] = ($row['filesize'] >= 1048576) ? round((round($row['filesize'] / 1048576 * 100) / 100), 2) : (($row['filesize'] >= 1024) ? round((round($row['filesize'] / 1024 * 100) / 100), 2) : $row['filesize']);

			$row['extension'] = strtolower(trim($row['extension']));
			$display_cat = $extensions[$row['extension']]['display_cat'];
			$l_downloaded_viewed = ($display_cat == ATTACHMENT_CATEGORY_NONE) ? 'DOWNLOAD_COUNT' : 'VIEWED_COUNT';
			$l_download_count = (!isset($row['download_count']) || $row['download_count'] == 0) ? $user->lang[$l_downloaded_viewed . '_NONE'] : (($row['download_count'] == 1) ? sprintf($user->lang[$l_downloaded_viewed], $row['download_count']) : sprintf($user->lang[$l_downloaded_viewed . 'S'], $row['download_count']));

			$template->assign_block_vars('attachments', array(
				'ATTACHMENT_POSTER'	=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['username']),
				'FILESIZE'			=> $row['filesize'] . ' ' . $size_lang,
				'FILETIME'			=> $user->format_date($row['filetime']),
				'REAL_FILENAME'		=> basename($row['real_filename']),
				'PHYSICAL_FILENAME'	=> basename($row['physical_filename']),
				'TOPIC_TITLE'		=> (!$row['in_message']) ? $row['topic_title'] : '',
				'DISABLED'			=> ($row['in_message']) ? 'disabled="disabled"' : '',
				'ATTACH_ID'			=> $row['attach_id'],
				'POST_ID'			=> $row['post_msg_id'],
				'TOPIC_ID'			=> $row['topic_id'],
				'POST_IDS'			=> (!empty($post_ids[$row['attach_id']])) ? $post_ids[$row['attach_id']] : '',
				
				'L_DOWNLOAD_COUNT'	=> $l_download_count,

				'S_IN_MESSAGE'		=> $row['in_message'],

				'U_VIEW_TOPIC'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t={$row['topic_id']}&amp;p={$row['post_msg_id']}") . "#p{$row['post_msg_id']}",
				'U_FILE'			=> append_sid($phpbb_root_path . 'download/file.' . $phpEx, 'mode=view&amp;id=' . $row['attach_id']))
			);
		}

		if (sizeof($error))
		{
			$template->assign_vars(array(
				'S_WARNING'		=> true,
				'WARNING_MSG'	=> implode('<br />', $error))
			);
		}

		if (sizeof($notify))
		{
			$template->assign_vars(array(
				'S_NOTIFY'		=> true,
				'NOTIFY_MSG'	=> implode('<br />', $notify))
			);
		}
	}
}

?>