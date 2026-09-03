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
					$num_moved = move_pm($user->data['user_id'], $msg_ids, $move_to, $remove_folder_id);

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
		'message_status'    => ($num_messages == 1) ? $user->lang['VIEW_PM_MESSAGE'] : sprintf($user->lang['VIEW_PM_MESSAGES'], $num_messages)
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
			'message_status'    => ($row['pm_count'] == 1) ? $user->lang['VIEW_PM_MESSAGE'] : sprintf($user->lang['VIEW_PM_MESSAGES'], $row['pm_count'])
		];
	}
	$db->sql_freeresult($result);

	$s_to_folder_options = $s_folder_options = '';

	foreach ($folder as $folder_id => $folder_ary)
	{
		$s_to_folder_options .= '<option value="' . $folder_id . '">' . $folder_ary['folder_name'] . ' (' . $folder_ary['message_status'] . ')</option>';

		if ($folder_id != PRIVMSGS_INBOX)
		{
			$s_folder_options .= '<option value="' . $folder_id . '">' . $folder_ary['folder_name'] . ' (' . $folder_ary['message_status'] . ')</option>';
		}
	}

	$template->assign_vars([
		'S_TO_FOLDER_OPTIONS'   => $s_to_folder_options,
		'S_FOLDER_OPTIONS'      => $s_folder_options,
		'S_MAX_FOLDER_REACHED'  => ($num_user_folder >= $config['pm_max_boxes']),
		'S_MAX_FOLDER_ZERO'     => ($config['pm_max_boxes'] == 0),
	]);
}
