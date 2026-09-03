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
* Execute message options
*/
function message_options($mode)
{
	global $user, $template, $config, $db;

	$redirect_url = append_sid(PHPBB_ROOT_PATH . 'ucp.php', "i=pm&amp;mode=options");

	add_form_key('ucp_pm_options');
	// Change "full folder" setting - what to do if folder is full
	if (isset($_POST['fullfolder']))
	{
		if (!check_form_key('ucp_pm_options'))
		{
			trigger_error('FORM_INVALID');
		}

		$full_action = request_var('full_action', 0);

		$set_folder_id = 0;
		switch ($full_action)
		{
			case 1:
				$set_folder_id = FULL_FOLDER_DELETE;
			break;

			case 2:
				$set_folder_id = request_var('full_move_to', PRIVMSGS_INBOX);
			break;

			case 3:
				$set_folder_id = FULL_FOLDER_HOLD;
			break;

			default:
				$full_action = 0;
			break;
		}

		if ($full_action)
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_full_folder = ' . $set_folder_id . '
				WHERE user_id = ' . $user->data['user_id'];
			$db->sql_query($sql);

			$user->data['user_full_folder'] = $set_folder_id;

			$message = $user->lang['FULL_FOLDER_OPTION_CHANGED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $redirect_url . '">', '</a>');
			meta_refresh(3, $redirect_url);
			trigger_error($message);
		}
	}

	// Add Folder
	if (isset($_POST['addfolder']))
	{
		if (check_form_key('ucp_pm_options'))
		{
			$folder_name = utf8_normalize_nfc(request_var('foldername', '', true));
			$msg = '';

			if ($folder_name)
			{
				$sql = 'SELECT folder_name
					FROM ' . PRIVMSGS_FOLDER_TABLE . "
					WHERE folder_name = '" . $db->sql_escape($folder_name) . "'
						AND user_id = " . $user->data['user_id'];
				$result = $db->sql_query_limit($sql, 1);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row)
				{
					trigger_error(sprintf($user->lang['FOLDER_NAME_EXIST'], $folder_name));
				}

				$sql = 'SELECT COUNT(folder_id) as num_folder
					FROM ' . PRIVMSGS_FOLDER_TABLE . '
						WHERE user_id = ' . $user->data['user_id'];
				$result = $db->sql_query($sql);
				$num_folder = (int) $db->sql_fetchfield('num_folder');
				$db->sql_freeresult($result);

				if ($num_folder >= $config['pm_max_boxes'])
				{
					trigger_error('MAX_FOLDER_REACHED');
				}

				$sql = 'INSERT INTO ' . PRIVMSGS_FOLDER_TABLE . ' ' . $db->sql_build_array('INSERT', [
					'user_id'       => (int) $user->data['user_id'],
					'folder_name'   => $folder_name]
				);
				$db->sql_query($sql);
				$msg = $user->lang['FOLDER_ADDED'];
			}
			else
			{
				$msg = $user->lang['FOLDER_NAME_EMPTY'];
			}
		}
		else
		{
			$msg = $user->lang['FORM_INVALID'];
		}
		$message = $msg . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $redirect_url . '">', '</a>');
		meta_refresh(3, $redirect_url);
		trigger_error($message);
	}

	// Rename folder
	if (isset($_POST['rename_folder']))
	{
		if (check_form_key('ucp_pm_options'))
		{
			$new_folder_name = utf8_normalize_nfc(request_var('new_folder_name', '', true));
			$rename_folder_id= request_var('rename_folder_id', 0);

			if (!$new_folder_name)
			{
				trigger_error('NO_NEW_FOLDER_NAME');
			}

			// Select custom folder
			$sql = 'SELECT folder_name, pm_count
				FROM ' . PRIVMSGS_FOLDER_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND folder_id = {$rename_folder_id}";
			$result = $db->sql_query_limit($sql, 1);
			$folder_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$folder_row)
			{
				trigger_error('CANNOT_RENAME_FOLDER');
			}

			$sql = 'UPDATE ' . PRIVMSGS_FOLDER_TABLE . "
				SET folder_name = '" . $db->sql_escape($new_folder_name) . "'
				WHERE folder_id = {$rename_folder_id}
					AND user_id = {$user->data['user_id']}";
			$db->sql_query($sql);
			$msg = $user->lang['FOLDER_RENAMED'];
		}
		else
		{
			$msg = $user->lang['FORM_INVALID'];
		}

		$message = $msg . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $redirect_url . '">', '</a>');

		meta_refresh(3, $redirect_url);
		trigger_error($message);
	}

	// Remove Folder
	if (isset($_POST['remove_folder']))
	{
		$remove_folder_id = request_var('remove_folder_id', 0);

		// Default to "move all messages to inbox"
		$remove_action = request_var('remove_action', 1);
		$move_to = request_var('move_to', PRIVMSGS_INBOX);

		// Move to same folder?
		if ($remove_action == 1 && $remove_folder_id == $move_to)
		{
			trigger_error('CANNOT_MOVE_TO_SAME_FOLDER');
		}

		// Select custom folder
		$sql = 'SELECT folder_name, pm_count
			FROM ' . PRIVMSGS_FOLDER_TABLE . "
			WHERE user_id = {$user->data['user_id']}
				AND folder_id = {$remove_folder_id}";
		$result = $db->sql_query_limit($sql, 1);
		$folder_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$folder_row)
		{
			trigger_error('CANNOT_REMOVE_FOLDER');
		}

		$s_hidden_fields = [
			'remove_folder_id'  => $remove_folder_id,
			'remove_action'     => $remove_action,
			'move_to'           => $move_to,
			'remove_folder'     => 1
		];

		// Do we need to confirm?
		if (confirm_box(true))
		{
			// Gather message ids
			$sql = 'SELECT msg_id
				FROM ' . PRIVMSGS_TO_TABLE . '
				WHERE user_id = ' . $user->data['user_id'] . "
					AND folder_id = {$remove_folder_id}";
			$result = $db->sql_query($sql);

			$msg_ids = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$msg_ids[] = (int) $row['msg_id'];
			}
			$db->sql_freeresult($result);

			// First of all, copy all messages to another folder... or delete all messages
			switch ($remove_action)
			{
				// Move Messages
				case 1:
					$num_moved = move_pm($user->data['user_id'], $user->data['message_limit'], $msg_ids, $move_to, $remove_folder_id);

					// Something went wrong, only partially moved?
					if ($num_moved != $folder_row['pm_count'])
					{
						trigger_error(sprintf($user->lang['MOVE_PM_ERROR'], $num_moved, $folder_row['pm_count']));
					}
				break;

				// Remove Messages
				case 2:
					delete_pm($user->data['user_id'], $msg_ids, $remove_folder_id);
				break;
			}

			// Remove folder
			$sql = 'DELETE FROM ' . PRIVMSGS_FOLDER_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND folder_id = {$remove_folder_id}";
			$db->sql_query($sql);

			// Check full folder option. If the removed folder has been specified as destination switch back to inbox
			if ($user->data['user_full_folder'] == $remove_folder_id)
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_full_folder = ' . PRIVMSGS_INBOX . '
					WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);

				$user->data['user_full_folder'] = PRIVMSGS_INBOX;
			}

			$meta_info = append_sid(PHPBB_ROOT_PATH . 'ucp.php', "i=pm&amp;mode={$mode}");
			$message = $user->lang['FOLDER_REMOVED'];

			meta_refresh(3, $meta_info);
			$message .= '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $meta_info . '">', '</a>');
			trigger_error($message);
		}
		else
		{
			confirm_box(false, 'REMOVE_FOLDER', build_hidden_fields($s_hidden_fields));
		}
	}

	$folder = [];

	$sql = 'SELECT COUNT(msg_id) as num_messages
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE user_id = ' . $user->data['user_id'] . '
			AND folder_id = ' . PRIVMSGS_INBOX;
	$result = $db->sql_query($sql);
	$num_messages = (int) $db->sql_fetchfield('num_messages');
	$db->sql_freeresult($result);

	$folder[PRIVMSGS_INBOX] = [
		'folder_name'       => $user->lang['PM_INBOX'],
		'message_status'    => sprintf($user->lang['FOLDER_MESSAGE_STATUS'], $num_messages, $user->data['message_limit'])
	];

	$sql = 'SELECT folder_id, folder_name, pm_count
		FROM ' . PRIVMSGS_FOLDER_TABLE . '
			WHERE user_id = ' . $user->data['user_id'];
	$result = $db->sql_query($sql);

	$num_user_folder = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		$num_user_folder++;
		$folder[$row['folder_id']] = [
			'folder_name'       => $row['folder_name'],
			'message_status'    => sprintf($user->lang['FOLDER_MESSAGE_STATUS'], $row['pm_count'], $user->data['message_limit'])
		];
	}
	$db->sql_freeresult($result);

	$s_full_folder_options = $s_to_folder_options = $s_folder_options = '';

	if ($user->data['user_full_folder'] == FULL_FOLDER_NONE)
	{
		// -3 here to let the correct folder id be selected
		$to_folder_id = $config['full_folder_action'] - 3;
	}
	else
	{
		$to_folder_id = $user->data['user_full_folder'];
	}

	foreach ($folder as $folder_id => $folder_ary)
	{
		$s_full_folder_options .= '<option value="' . $folder_id . '"' . (($user->data['user_full_folder'] == $folder_id) ? ' selected="selected"' : '') . '>' . $folder_ary['folder_name'] . ' (' . $folder_ary['message_status'] . ')</option>';
		$s_to_folder_options .= '<option value="' . $folder_id . '"' . (($to_folder_id == $folder_id) ? ' selected="selected"' : '') . '>' . $folder_ary['folder_name'] . ' (' . $folder_ary['message_status'] . ')</option>';

		if ($folder_id != PRIVMSGS_INBOX)
		{
			$s_folder_options .= '<option value="' . $folder_id . '">' . $folder_ary['folder_name'] . ' (' . $folder_ary['message_status'] . ')</option>';
		}
	}

	$s_delete_checked = ($user->data['user_full_folder'] == FULL_FOLDER_DELETE) ? ' checked="checked"' : '';
	$s_hold_checked = ($user->data['user_full_folder'] == FULL_FOLDER_HOLD) ? ' checked="checked"' : '';
	$s_move_checked = ($user->data['user_full_folder'] >= 0) ? ' checked="checked"' : '';

	if ($user->data['user_full_folder'] == FULL_FOLDER_NONE)
	{
		switch ($config['full_folder_action'])
		{
			case 1:
				$s_delete_checked = ' checked="checked"';
			break;

			case 2:
				$s_hold_checked = ' checked="checked"';
			break;
		}
	}

	$template->assign_vars([
		'S_FULL_FOLDER_OPTIONS' => $s_full_folder_options,
		'S_TO_FOLDER_OPTIONS'   => $s_to_folder_options,
		'S_FOLDER_OPTIONS'      => $s_folder_options,
		'S_DELETE_CHECKED'      => $s_delete_checked,
		'S_HOLD_CHECKED'        => $s_hold_checked,
		'S_MOVE_CHECKED'        => $s_move_checked,
		'S_MAX_FOLDER_REACHED'  => ($num_user_folder >= $config['pm_max_boxes']),
		'S_MAX_FOLDER_ZERO'     => ($config['pm_max_boxes'] == 0),

		'DEFAULT_ACTION'        => ($config['full_folder_action'] == 1) ? $user->lang['DELETE_OLDEST_MESSAGES'] : $user->lang['HOLD_NEW_MESSAGES'],
	]);
}
