<?php
/**
*
* @package phpBB3
* @version $Id: posts_merging.php,v 1.100 2008/04/10 22:20:15 rxu Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

$post_need_approval = (!$auth->acl_get('f_noapprove', $data['forum_id']) && !$auth->acl_get('m_approve', $data['forum_id'])) ? true : false;

if (!$post_need_approval && ($mode == 'reply' || $mode == 'quote') && $config['merge_interval'] > 0)
{
	$sql = 'SELECT f.*, t.*, p.* FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f
		WHERE p.post_id = t.topic_last_post_id
			AND t.topic_id = ' . (int) $topic_id . "
			AND (f.forum_id = t.forum_id
					OR f.forum_id = $forum_id)";

	$result = $db->sql_query($sql);
	$merge_post_data = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$merge_post_id = $merge_post_data['post_id'];

	if (!$merge_post_id)
	{
		$user->setup('posting');
		trigger_error('NO_POST');
	}

	// Do merging
	if (!request_var('do_not_merge', false)
		&& (!$merge_post_data['post_edit_locked'] && ($current_time - $merge_post_data['topic_last_post_time']) < intval($config['merge_interval']) * 3600)
		&& ($merge_post_data['poster_id'] == $user->data['user_id'])
		&& ($user->data['user_id'] != ANONYMOUS && $user->data['is_registered'] || $user->data['user_id'] == ANONYMOUS && request_var($config['cookie_name'] . '_bid', '', false, true) == $merge_post_data['poster_browser_id']))
	{
		$message_parser = new parse_message();

		$message_parser->message = &$merge_post_data['post_text'];
		unset($merge_post_data['post_text']);

		// Decode text for update properly
		$message_parser->decode_message($merge_post_data['bbcode_uid']);
		$merge_post_data['post_text'] = html_entity_decode($message_parser->message,  ENT_COMPAT, 'UTF-8');
		unset($message_parser);

		//Handle with inline attachments
		if (sizeof($data['attachment_data']))
		{
			for($i = 0; $i < sizeof($data['attachment_data']); $i++)
			{
				$merge_post_data['post_text'] = preg_replace('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#e', "'[attachment='.(\\1 + 1).']\\2[/attachment]'", $merge_post_data['post_text']);
			}
		}

		// Make sure the message is safe
		set_var($merge_post_data['post_text'], $merge_post_data['post_text'], 'string', true);

		// Calculate last merge time
		$merge_time = $merge_post_data['post_time'];
		$merge_upds = array();
		if (preg_match_all('#\[upd=(\d+(?:[:]\d+){0,3})\](.*?)\[/upd\]#uis', $merge_post_data['post_text'], $merge_upds))
		{
			foreach ($merge_upds[1] as $merge_upd)
			{
				$merge_upd = explode(':', $merge_upd);
				$merge_time += array_pop($merge_upd);
				$merge_time += array_pop($merge_upd) * 60;
				$merge_time += array_pop($merge_upd) * 3600;
				$merge_time += array_pop($merge_upd) * 86400;
			}
			
			$merge_time = min($merge_time, $current_time);
		}

		// Convert it into DD:HH:MM:SS format
		$time_delta = $current_time - $merge_time;
		$time_parts = array();
		if (($time_part = (int)($time_delta / 86400)) > 0)
		{
			$time_parts[] = $time_part;
			$time_delta = $time_delta % 86400;
		}
		if (($time_part = (int)($time_delta / 3600)) > 0)
		{
			$time_parts[] = str_pad($time_part, 2, '0', STR_PAD_LEFT);
			$time_delta = $time_delta % 3600;
		}
		$time_part = (int)($time_delta / 60);
		$time_parts[] = str_pad($time_part, 2, '0', STR_PAD_LEFT);
		$time_delta = $time_delta % 60;
		$time_parts[] = str_pad($time_delta, 2, '0', STR_PAD_LEFT);

		// Merge posts
		$subject = $post_data['post_subject'];
		$separator = "\n\n[upd=" . implode(':', $time_parts) . ']' . $subject . "[/upd]\n";
		$merge_post_data['post_text'] = $merge_post_data['post_text'] . $separator . $addon_for_merge;

		//Prepare post for submit
		$options = '';
		generate_text_for_storage($merge_post_data['post_text'], $merge_post_data['bbcode_uid'], $merge_post_data['bbcode_bitfield'], $options, $merge_post_data['enable_bbcode'], $merge_post_data['enable_magic_url'], $merge_post_data['enable_smilies']);

		$poster_id = (int) $merge_post_data['poster_id'];

		// Prepare post data for update
		$sql_data[POSTS_TABLE]['sql'] = array(
			'bbcode_uid'		=> $merge_post_data['bbcode_uid'],
			'bbcode_bitfield'	=> $merge_post_data['bbcode_bitfield'],
			'post_text'			=> $merge_post_data['post_text'],
			'post_checksum'		=> md5($merge_post_data['post_text']),
			'post_merged'		=> $current_time,
			'post_attachment'	=> (!empty($data['attachment_data'])) ? 1 : ($merge_post_data['post_attachment'] ? 1 : 0),
		);

		$sql_data[TOPICS_TABLE]['sql'] = array(
			'topic_last_post_id'		=> $merge_post_id,
			'topic_last_poster_id'		=> $poster_id,
			'topic_last_poster_name'	=> (!$user->data['is_registered'] && $post_data['username']) ? $post_data['username'] : (($user->data['user_id'] != ANONYMOUS) ? $user->data['username'] : ''),
			'topic_last_poster_colour'	=> ($user->data['user_id'] != ANONYMOUS) ? $user->data['user_colour'] : '',
			'topic_last_post_subject'	=> utf8_normalize_nfc($merge_post_data['post_subject']),
			'topic_last_post_time'		=> $current_time,
			'topic_attachment'			=> (!empty($data['attachment_data']) || (isset($merge_post_data['topic_attachment']) && $merge_post_data['topic_attachment'])) ? 1 : 0,
		);

		$sql_data[FORUMS_TABLE]['sql'] = array(
			'forum_last_post_id'		=> $merge_post_id,
			'forum_last_post_subject'	=> utf8_normalize_nfc($merge_post_data['post_subject']),
			'forum_last_post_time'		=> $current_time,
			'forum_last_poster_id'		=> $poster_id,
			'forum_last_poster_name'	=> (!$user->data['is_registered'] && $post_data['username']) ? $post_data['username'] : (($user->data['user_id'] != ANONYMOUS) ? $user->data['username'] : ''),
			'forum_last_poster_colour'	=> ($user->data['user_id'] != ANONYMOUS) ? $user->data['user_colour'] : '',
		);

		// Update post information - submit merged post
		$sql = 'UPDATE ' . POSTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_data[POSTS_TABLE]['sql']) . " WHERE post_id = $merge_post_id";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_data[TOPICS_TABLE]['sql']) . " WHERE topic_id = $topic_id";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_data[FORUMS_TABLE]['sql']) . "  WHERE forum_id = $forum_id";
		$db->sql_query($sql);

		// Submit Attachments
		if (!empty($data['attachment_data']))
		{
			$space_taken = $files_added = 0;
			$orphan_rows = array();

			foreach ($data['attachment_data'] as $pos => $attach_row)
			{
				$orphan_rows[(int) $attach_row['attach_id']] = array();
			}

			if (sizeof($orphan_rows))
			{
				$sql = 'SELECT attach_id, filesize, physical_filename
					FROM ' . ATTACHMENTS_TABLE . '
					WHERE ' . $db->sql_in_set('attach_id', array_keys($orphan_rows)) . '
						AND is_orphan = 1
						AND poster_id = ' . $user->data['user_id'];
				$result = $db->sql_query($sql);

				$orphan_rows = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$orphan_rows[$row['attach_id']] = $row;
				}
				$db->sql_freeresult($result);
			}

			foreach ($data['attachment_data'] as $pos => $attach_row)
			{
				if ($attach_row['is_orphan'] && !in_array($attach_row['attach_id'], array_keys($orphan_rows)))
				{
					continue;
				}

				if (!$attach_row['is_orphan'])
				{
					// update entry in db if attachment already stored in db and filespace
					$sql = 'UPDATE ' . ATTACHMENTS_TABLE . "
						SET attach_comment = '" . $db->sql_escape($attach_row['attach_comment']) . "'
						WHERE attach_id = " . (int) $attach_row['attach_id'] . '
							AND is_orphan = 0';
					$db->sql_query($sql);
				}
				else
				{
					// insert attachment into db
					if (!@file_exists($phpbb_root_path . $config['upload_path'] . '/' . basename($orphan_rows[$attach_row['attach_id']]['physical_filename'])))
					{
						continue;
					}

					$space_taken += $orphan_rows[$attach_row['attach_id']]['filesize'];
					$files_added++;

					$attach_sql = array(
						'post_msg_id'		=> $merge_post_id,
						'topic_id'			=> $topic_id,
						'is_orphan'			=> 0,
						'poster_id'			=> $poster_id,
						'attach_comment'	=> $attach_row['attach_comment'],
					);

					$sql = 'UPDATE ' . ATTACHMENTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $attach_sql) . '
						WHERE attach_id = ' . $attach_row['attach_id'] . '
							AND is_orphan = 1
							AND poster_id = ' . $user->data['user_id'];
					$db->sql_query($sql);
				}
			}

			if ($space_taken && $files_added)
			{
				set_config('upload_dir_size', $config['upload_dir_size'] + $space_taken, true);
				set_config('num_files', $config['num_files'] + $files_added, true);
			}
		}

		// Index message contents
		if ($merge_post_data['enable_indexing'])
		{
			// Select the search method and do some additional checks to ensure it can actually be utilised
			$search_type = basename($config['search_type']);

			if (!file_exists($phpbb_root_path . 'includes/search/' . $search_type . '.' . $phpEx))
			{
				trigger_error('NO_SUCH_SEARCH_MODULE');
			}

			require_once("{$phpbb_root_path}includes/search/$search_type.$phpEx");

			$error = false;
			$search = new $search_type($error);

			if ($error)
			{
				trigger_error($error);
			}

			$search->index('edit', $merge_post_id, $merge_post_data['post_text'], $subject, $poster_id, $forum_id);
		}

		// Mark the post and the topic read
		markread('post', $forum_id, $topic_id, $current_time);
		markread('topic', $forum_id, $topic_id, $current_time);

		//
		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$sql = 'SELECT mark_time
				FROM ' . FORUMS_TRACK_TABLE . '
				WHERE user_id = ' . $user->data['user_id'] . '
					AND forum_id = ' . $forum_id;
			$result = $db->sql_query($sql);
			$f_mark_time = (int) $db->sql_fetchfield('mark_time');
			$db->sql_freeresult($result);
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			$f_mark_time = false;
		}

		if (($config['load_db_lastread'] && $user->data['is_registered']) || $config['load_anon_lastread'] || $user->data['is_registered'])
		{
			// Update forum info
			$sql = 'SELECT forum_last_post_time
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . $forum_id;
			$result = $db->sql_query($sql);
			$forum_last_post_time = (int) $db->sql_fetchfield('forum_last_post_time');
			$db->sql_freeresult($result);

			update_forum_tracking_info($forum_id, $forum_last_post_time, $f_mark_time, false);
		}

		// Send Notifications
		if ($auth->acl_get('f_noapprove', $data['forum_id']) || $auth->acl_get('m_approve', $data['forum_id']))
		{
			user_notification($mode, $subject, $data['topic_title'], $data['forum_name'], $data['forum_id'], $data['topic_id'], $merge_post_id);
		}

		//Generate redirection URL and redirecting
		$params = $add_anchor = '';
		$params .= '&amp;t=' . $topic_id;
		$params .= '&amp;p=' . $merge_post_id;
		$add_anchor = '#p' . $merge_post_id;
		$redirect_url = "{$phpbb_root_path}viewtopic.$phpEx";
		$redirect_url = append_sid($redirect_url, 'f=' . $forum_id . $params) . $add_anchor;

		if (!empty($config['no_typical_info_pages']))
		{
			redirect($redirect_url);
		}

		meta_refresh(3, $redirect_url);

		$message = (!$auth->acl_get('f_noapprove', $merge_post_data['forum_id']) && !$auth->acl_get('m_approve', $merge_post_data['forum_id'])) ? 'POST_STORED_MOD' : 'POST_STORED';
		$message = $user->lang[$message] . (($auth->acl_get('f_noapprove', $merge_post_data['forum_id']) || $auth->acl_get('m_approve', $merge_post_data['forum_id'])) ? '<br /><br />' . sprintf($user->lang['VIEW_MESSAGE'], '<a href="' . $redirect_url . '">', '</a>') : '');
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $merge_post_data['forum_id']) . '">', '</a>');
		trigger_error($message);
	}
}
