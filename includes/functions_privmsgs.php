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
* Get private message folders
*/
function get_folder($user_id, $folder_id = false)
{
	global $db, $user, $template;

	$folder = [
		PRIVMSGS_INBOX => [
			'folder_name'     => $user->lang['PM_INBOX'],
			'num_messages'    => 0,
			'unread_messages' => 0,
		],
		PRIVMSGS_OUTBOX => [
			'folder_name'     => $user->lang['PM_OUTBOX'],
			'num_messages'    => 0,
			'unread_messages' => 0,
		],
		PRIVMSGS_SENTBOX => [
			'folder_name'     => $user->lang['PM_SENTBOX'],
			'num_messages'    => 0,
			'unread_messages' => 0,
		],
	];
	$folder_url_names = [
		PRIVMSGS_INBOX   => 'inbox',
		PRIVMSGS_OUTBOX  => 'outbox',
		PRIVMSGS_SENTBOX => 'sentbox',
	];

	// Get message counts for the system folders.
	$sql = 'SELECT folder_id, COUNT(msg_id) as num_messages, SUM(pm_unread) as num_unread
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE user_id = ' . (int) $user_id . '
			AND ' . $db->sql_in_set('folder_id', array_keys($folder)) . '
		GROUP BY folder_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$f_id = (int) $row['folder_id'];
		$folder[$f_id]['num_messages'] = (int) $row['num_messages'];
		$folder[$f_id]['unread_messages'] = ($f_id == PRIVMSGS_OUTBOX) ? (int) $row['num_messages'] : (int) $row['num_unread'];
	}
	$db->sql_freeresult($result);

	// Define folder array for templates.
	foreach ($folder as $f_id => $folder_ary)
	{
		$template->assign_block_vars('folder', [
			'FOLDER_ID'         => $f_id,
			'FOLDER_NAME'       => $folder_ary['folder_name'],
			'NUM_MESSAGES'      => $folder_ary['num_messages'],
			'UNREAD_MESSAGES'   => $folder_ary['unread_messages'],

			'U_FOLDER'          => append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'i=pm&amp;folder=' . $folder_url_names[$f_id]),

			'S_CUR_FOLDER'      => ($f_id === $folder_id),
			'S_UNREAD_MESSAGES' => (bool) $folder_ary['unread_messages'],
		]);
	}

	if ($folder_id !== false && !isset($folder[$folder_id]))
	{
		trigger_error('UNKNOWN_FOLDER');
	}

	return $folder;
}

/**
* Update user PM count
*/
function update_pm_counts()
{
	global $user, $db;

	// Update unread count
	$sql = 'SELECT COUNT(msg_id) as num_messages
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE pm_unread = 1
			AND folder_id <> ' . PRIVMSGS_OUTBOX . '
			AND user_id = ' . $user->data['user_id'];
	$result = $db->sql_query($sql);
	$user->data['user_unread_privmsg'] = (int) $db->sql_fetchfield('num_messages');
	$db->sql_freeresult($result);

	// Update new pm count
	$sql = 'SELECT COUNT(msg_id) as num_messages
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE pm_new = 1
			AND folder_id = ' . PRIVMSGS_NO_BOX . '
			AND user_id = ' . $user->data['user_id'];
	$result = $db->sql_query($sql);
	$user->data['user_new_privmsg'] = (int) $db->sql_fetchfield('num_messages');
	$db->sql_freeresult($result);

	$db->sql_query('UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', [
		'user_unread_privmsg'   => (int) $user->data['user_unread_privmsg'],
		'user_new_privmsg'      => (int) $user->data['user_new_privmsg'],
	]) . ' WHERE user_id = ' . $user->data['user_id']);

	// Boxes other than PRIVMSGS_NO_BOX should not carry the pm_new flag.
	if (!$user->data['user_new_privmsg'])
	{
		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
			SET pm_new = 0
			WHERE pm_new = 1
				AND folder_id <> ' . PRIVMSGS_NO_BOX . '
				AND user_id = ' . $user->data['user_id'];
		$db->sql_query($sql);
	}
}

/**
* Deliver pending messages to the inbox
*/
function deliver_pending_pms()
{
	global $db, $user;

	if (!$user->data['user_new_privmsg'])
	{
		return;
	}

	$user_id = (int) $user->data['user_id'];
	$msg_ids = [];

	// Get messages not yet placed into the inbox.
	$sql = 'SELECT msg_id
		FROM ' . PRIVMSGS_TO_TABLE . "
		WHERE user_id = {$user_id}
			AND folder_id = " . PRIVMSGS_NO_BOX;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$msg_ids[] = (int) $row['msg_id'];
	}
	$db->sql_freeresult($result);

	if (sizeof($msg_ids))
	{
		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
			SET folder_id = ' . PRIVMSGS_INBOX . ', pm_new = 0
			WHERE folder_id = ' . PRIVMSGS_NO_BOX . "
				AND user_id = {$user_id}
				AND " . $db->sql_in_set('msg_id', $msg_ids);
		$db->sql_query($sql);

		// Move from OUTBOX to SENTBOX
		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
			SET folder_id = ' . PRIVMSGS_SENTBOX . '
			WHERE folder_id = ' . PRIVMSGS_OUTBOX . '
				AND ' . $db->sql_in_set('msg_id', $msg_ids);
		$db->sql_query($sql);
	}

	// Update new/unread count
	update_pm_counts();
}

/**
* Update unread message status
*/
function update_unread_status($unread, $msg_id, $user_id, $folder_id)
{
	if (!$unread)
	{
		return;
	}

	global $db, $user;

	$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . "
		SET pm_unread = 0
		WHERE msg_id = {$msg_id}
			AND user_id = {$user_id}
			AND folder_id = {$folder_id}";
	$db->sql_query($sql);

	$sql = 'UPDATE ' . USERS_TABLE . "
		SET user_unread_privmsg = GREATEST(user_unread_privmsg, 1) - 1
		WHERE user_id = {$user_id}";
	$db->sql_query($sql);

	if ($user->data['user_id'] == $user_id)
	{
		$user->data['user_unread_privmsg'] = max(0, $user->data['user_unread_privmsg'] - 1);
	}
}

/**
* Handle all actions possible with marked messages
*/
function handle_mark_actions($user_id, $mark_action)
{
	global $db, $user;

	$msg_ids        = request_var('marked_msg_id', [0]);
	$cur_folder_id  = request_var('cur_folder_id', PRIVMSGS_NO_BOX);
	$confirm        = isset($_POST['confirm']);

	if (!sizeof($msg_ids))
	{
		return false;
	}

	switch ($mark_action)
	{
		case 'mark_important':

			$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . "
				SET pm_marked = 1 - pm_marked
				WHERE folder_id = {$cur_folder_id}
					AND user_id = {$user_id}
					AND " . $db->sql_in_set('msg_id', $msg_ids);
			$db->sql_query($sql);

		break;

		case 'delete_marked':

			if (confirm_box(true))
			{
				delete_pm($user_id, $msg_ids, $cur_folder_id);

				$success_msg = (sizeof($msg_ids) == 1) ? 'MESSAGE_DELETED' : 'MESSAGES_DELETED';
				$redirect = append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'i=pm&amp;folder=' . $cur_folder_id);

				meta_refresh(3, $redirect);
				trigger_error($user->lang[$success_msg] . '<br /><br />' . sprintf($user->lang['RETURN_FOLDER'], '<a href="' . $redirect . '">', '</a>'));
			}
			else
			{
				$s_hidden_fields = [
					'cur_folder_id' => $cur_folder_id,
					'mark_option'   => 'delete_marked',
					'submit_mark'   => true,
					'marked_msg_id' => $msg_ids
				];

				confirm_box(false, 'DELETE_MARKED_PM', build_hidden_fields($s_hidden_fields));
			}

		break;

		default:
			return false;
	}

	return true;
}

/**
* Delete PM(s)
*/
function delete_pm($user_id, $msg_ids, $folder_id)
{
	global $db, $user;

	$user_id    = (int) $user_id;
	$folder_id  = (int) $folder_id;

	if (!$user_id)
	{
		return false;
	}

	if (!is_array($msg_ids))
	{
		if (!$msg_ids)
		{
			return false;
		}
		$msg_ids = [$msg_ids];
	}

	if (!sizeof($msg_ids))
	{
		return false;
	}

	// Get PM Information for later deleting
	$sql = 'SELECT msg_id, pm_unread, pm_new
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE ' . $db->sql_in_set('msg_id', array_map('intval', $msg_ids)) . "
			AND folder_id = {$folder_id}
			AND user_id = {$user_id}";
	$result = $db->sql_query($sql);

	$delete_rows = [];
	$num_unread = $num_new = $num_deleted = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		$num_unread += (int) $row['pm_unread'];
		$num_new += (int) $row['pm_new'];

		$delete_rows[$row['msg_id']] = 1;
	}
	$db->sql_freeresult($result);
	unset($msg_ids);

	if (!sizeof($delete_rows))
	{
		return false;
	}

	$db->sql_transaction('begin');

	// if no one has read the message yet (meaning it is in users outbox)
	// then mark the message as deleted...
	if ($folder_id == PRIVMSGS_OUTBOX)
	{
		// Remove PM from Outbox
		$sql = 'DELETE FROM ' . PRIVMSGS_TO_TABLE . "
			WHERE user_id = {$user_id} AND folder_id = " . PRIVMSGS_OUTBOX . '
				AND ' . $db->sql_in_set('msg_id', array_keys($delete_rows));
		$db->sql_query($sql);

		// Update PM Information for safety
		$sql = 'UPDATE ' . PRIVMSGS_TABLE . " SET message_text = ''
			WHERE " . $db->sql_in_set('msg_id', array_keys($delete_rows));
		$db->sql_query($sql);

		// Set delete flag for those intended to receive the PM
		// We do not remove the message actually, to retain some basic information (sent time for example)
		$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
			SET pm_deleted = 1
			WHERE ' . $db->sql_in_set('msg_id', array_keys($delete_rows));
		$db->sql_query($sql);

		$num_deleted = $db->sql_affectedrows();
	}
	else
	{
		// Delete private message data
		$sql = 'DELETE FROM ' . PRIVMSGS_TO_TABLE . "
			WHERE user_id = {$user_id}
				AND folder_id = {$folder_id}
				AND " . $db->sql_in_set('msg_id', array_keys($delete_rows));
		$db->sql_query($sql);
		$num_deleted = $db->sql_affectedrows();
	}

	// Update unread and new status field
	if ($num_unread || $num_new)
	{
		$set_sql = ($num_unread) ? 'user_unread_privmsg = user_unread_privmsg - ' . $num_unread : '';

		if ($num_new)
		{
			$set_sql .= ($set_sql != '') ? ', ' : '';
			$set_sql .= 'user_new_privmsg = user_new_privmsg - ' . $num_new;
		}

		$db->sql_query('UPDATE ' . USERS_TABLE . " SET {$set_sql} WHERE user_id = {$user_id}");

		$user->data['user_new_privmsg'] -= $num_new;
		$user->data['user_unread_privmsg'] -= $num_unread;
	}

	// Now we have to check which messages we can delete completely
	$sql = 'SELECT msg_id
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE ' . $db->sql_in_set('msg_id', array_keys($delete_rows));
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		unset($delete_rows[$row['msg_id']]);
	}
	$db->sql_freeresult($result);

	$delete_ids = array_keys($delete_rows);

	if (sizeof($delete_ids))
	{
		// Check if there are any attachments we need to remove
		if (!function_exists('delete_attachments'))
		{
			require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');
		}

		delete_attachments('message', $delete_ids, false);

		$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
			WHERE ' . $db->sql_in_set('msg_id', $delete_ids);
		$db->sql_query($sql);
	}

	$db->sql_transaction('commit');

	return true;
}

/**
* Delete all PM(s) for a given user and delete the ones without references
*
* @param    int     $user_id    ID of the user whose private messages we want to delete
*
* @return   boolean     False if there were no pms found, true otherwise.
*/
function phpbb_delete_user_pms($user_id)
{
	global $db, $user;

	$user_id = (int) $user_id;

	if (!$user_id)
	{
		return false;
	}

	// Get PM Information for later deleting
	// The two queries where split, so we can use our indexes
	$undelivered_msg = $delete_ids = [];

	// Part 1: get PMs the user received
	$sql = 'SELECT msg_id
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE user_id = ' . $user_id;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$msg_id = (int) $row['msg_id'];
		$delete_ids[$msg_id] = $msg_id;
	}
	$db->sql_freeresult($result);

	// Part 2: get PMs the user sent, but have yet to be received
	// We cannot simply delete them. First we have to check,
	// whether another user already received and read the message.
	$sql = 'SELECT msg_id
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE author_id = ' . $user_id . '
			AND folder_id = ' . PRIVMSGS_NO_BOX;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$msg_id = (int) $row['msg_id'];
		$undelivered_msg[$msg_id] = $msg_id;
	}
	$db->sql_freeresult($result);

	if (empty($delete_ids) && empty($undelivered_msg))
	{
		return false;
	}

	$db->sql_transaction('begin');

	if (!empty($undelivered_msg))
	{
		// A pm is delivered, if for any recipient the message was moved
		// from their NO_BOX to another folder. We do not delete such
		// messages, but only delete them for users, who have not yet
		// received them.
		$sql = 'SELECT msg_id
			FROM ' . PRIVMSGS_TO_TABLE . '
			WHERE author_id = ' . $user_id . '
				AND folder_id <> ' . PRIVMSGS_NO_BOX . '
				AND folder_id <> ' . PRIVMSGS_OUTBOX . '
				AND folder_id <> ' . PRIVMSGS_SENTBOX;
		$result = $db->sql_query($sql);

		$delivered_msg = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$msg_id = (int) $row['msg_id'];
			$delivered_msg[$msg_id] = $msg_id;
			unset($undelivered_msg[$msg_id]);
		}
		$db->sql_freeresult($result);

		$undelivered_user = [];

		// Count the messages we delete, so we can correct the user pm data
		$sql = 'SELECT user_id, COUNT(msg_id) as num_undelivered_privmsgs
			FROM ' . PRIVMSGS_TO_TABLE . '
			WHERE author_id = ' . $user_id . '
				AND folder_id = ' . PRIVMSGS_NO_BOX . '
					AND ' . $db->sql_in_set('msg_id', array_merge($undelivered_msg, $delivered_msg)) . '
			GROUP BY user_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$num_pms = (int) $row['num_undelivered_privmsgs'];
			$undelivered_user[$num_pms][] = (int) $row['user_id'];

			if (sizeof($undelivered_user[$num_pms]) > 50)
			{
				// If there are too many users affected the query might get
				// too long, so we update the value for the first bunch here.
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_new_privmsg = user_new_privmsg - ' . $num_pms . ',
						user_unread_privmsg = user_unread_privmsg - ' . $num_pms . '
					WHERE ' . $db->sql_in_set('user_id', $undelivered_user[$num_pms]);
				$db->sql_query($sql);
				unset($undelivered_user[$num_pms]);
			}
		}
		$db->sql_freeresult($result);

		foreach ($undelivered_user as $num_pms => $undelivered_user_set)
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_new_privmsg = user_new_privmsg - ' . $num_pms . ',
					user_unread_privmsg = user_unread_privmsg - ' . $num_pms . '
				WHERE ' . $db->sql_in_set('user_id', $undelivered_user_set);
			$db->sql_query($sql);
		}

		if (!empty($delivered_msg))
		{
			$sql = 'DELETE FROM ' . PRIVMSGS_TO_TABLE . '
				WHERE folder_id = ' . PRIVMSGS_NO_BOX . '
					AND ' . $db->sql_in_set('msg_id', $delivered_msg);
			$db->sql_query($sql);
		}

		if (!empty($undelivered_msg))
		{
			$sql = 'DELETE FROM ' . PRIVMSGS_TO_TABLE . '
				WHERE ' . $db->sql_in_set('msg_id', $undelivered_msg);
			$db->sql_query($sql);

			$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
				WHERE ' . $db->sql_in_set('msg_id', $undelivered_msg);
			$db->sql_query($sql);
		}
	}

	// Reset the user's pm count to 0
	$sql = 'UPDATE ' . USERS_TABLE . '
		SET user_new_privmsg = 0,
			user_unread_privmsg = 0
		WHERE user_id = ' . $user_id;
	$db->sql_query($sql);

	// Delete private message data of the user
	$sql = 'DELETE FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE user_id = ' . (int) $user_id;
	$db->sql_query($sql);

	if (!empty($delete_ids))
	{
		// Now we have to check which messages we can delete completely
		$sql = 'SELECT msg_id
			FROM ' . PRIVMSGS_TO_TABLE . '
			WHERE ' . $db->sql_in_set('msg_id', $delete_ids);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			unset($delete_ids[$row['msg_id']]);
		}
		$db->sql_freeresult($result);

		if (!empty($delete_ids))
		{
			// Check if there are any attachments we need to remove
			if (!function_exists('delete_attachments'))
			{
				require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');
			}

			delete_attachments('message', $delete_ids, false);

			$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
				WHERE ' . $db->sql_in_set('msg_id', $delete_ids);
			$db->sql_query($sql);
		}
	}

	// Set the remaining author id to anonymous
	// This way users are still able to read messages from users being removed
	$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
		SET author_id = ' . ANONYMOUS . '
		WHERE author_id = ' . $user_id;
	$db->sql_query($sql);

	$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
		SET author_id = ' . ANONYMOUS . '
		WHERE author_id = ' . $user_id;
	$db->sql_query($sql);

	$db->sql_transaction('commit');

	return true;
}

/**
* Rebuild message header
*/
function rebuild_header($address_field)
{
	preg_match_all('/:?u_([0-9]+):?/', $address_field, $match);

	return array_values(array_unique(array_map('intval', $match[1])));
}

/**
* Print out/assign recipient information
*/
function write_pm_addresses($address_field, $plaintext = false)
{
	global $db, $template;

	$recipient_ids = rebuild_header($address_field);
	$addresses = [];
	$has_recipients = false;
	if (sizeof($recipient_ids))
	{
		$sql = 'SELECT user_id, username, user_colour
			FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', $recipient_ids);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$has_recipients = true;
			if ($plaintext)
			{
				$addresses[] = $row['username'];
			}
			else
			{
				$template->assign_block_vars('to_recipient', [
					'NAME'      => $row['username'],
					'NAME_FULL' => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				]);
			}
		}
		$db->sql_freeresult($result);

		if ($has_recipients && !$plaintext)
		{
			$template->assign_var('S_TO_RECIPIENT', true);
		}
	}

	return $addresses;
}

/**
* Return recipients who have added the sender to their foes list.
*
* @param int   $sender_id     User attempting to send the private message
* @param array $recipient_ids User IDs that may receive the private message
* @return array Blocked recipient IDs
*/
function get_pm_recipients_blocking_sender($sender_id, $recipient_ids)
{
	global $db;

	$sender_id = (int) $sender_id;
	$recipient_ids = array_values(array_unique(array_filter(array_map('intval', $recipient_ids))));

	if (!$sender_id || empty($recipient_ids))
	{
		return [];
	}

	$blocked = [];
	$sql = 'SELECT user_id
		FROM ' . ZEBRA_TABLE . '
		WHERE zebra_id = ' . $sender_id . '
			AND foe = 1
			AND ' . $db->sql_in_set('user_id', $recipient_ids);
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$blocked[] = (int) $row['user_id'];
	}
	$db->sql_freeresult($result);

	return $blocked;
}

//
// COMPOSE MESSAGES
//

/**
* Submit PM
*/
function submit_pm($mode, $subject, &$data, $put_in_outbox = true)
{
	global $db, $auth, $config, $template, $user;

	// We do not handle erasing pms here
	if ($mode == 'delete')
	{
		return false;
	}

	$current_time = time();

	// Collect some basic information about which tables and which rows to update/insert
	$sql_data = [];
	$root_level = 0;

	// Recipient Information
	$recipients = [];

	if ($mode != 'edit' && $mode != 'reparse')
	{
		// Build recipient list.
		if (!empty($data['address_list']) && is_array($data['address_list']))
		{
			foreach ($data['address_list'] as $id)
			{
				if (!is_scalar($id))
				{
					continue;
				}

				$id = (int) $id;

				// Do not rely on the address list being valid.
				if (!$id || $id == ANONYMOUS)
				{
					continue;
				}

				$recipients[] = $id;
			}

			$recipients = array_values(array_unique($recipients));
		}

		// Silently omit recipients who have added the sender to their foes list.
		$blocked_recipients = get_pm_recipients_blocking_sender($data['from_user_id'], $recipients);
		if (!empty($blocked_recipients))
		{
			$recipients = array_values(array_diff($recipients, $blocked_recipients));
		}

		if (!sizeof($recipients))
		{
			trigger_error('NO_RECIPIENT');
		}

		$to = [];
		foreach ($recipients as $user_id)
		{
			$to[] = 'u_' . $user_id;
		}
	}

	// First of all make sure the subject are having the correct length.
	$subject = truncate_string($subject, 90);

	$db->sql_transaction('begin');

	$sql = '';

	switch ($mode)
	{
		case 'reply':
		case 'quote':
			$root_level = $data['reply_from_root_level'] ?: $data['reply_from_msg_id'];

			// Set message_replied switch for this user
			$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
				SET pm_replied = 1
				WHERE user_id = ' . $data['from_user_id'] . '
					AND msg_id = ' . $data['reply_from_msg_id'];

		// no break

		case 'post':
		case 'quotepost':
			$sql_data = [
				'root_level'        => $root_level,
				'author_id'         => $data['from_user_id'],
				'icon_id'           => $data['icon_id'],
				'author_ip'         => $data['from_user_ip'],
				'message_time'      => $current_time,
				'enable_bbcode'     => $data['enable_bbcode'],
				'enable_smilies'    => $data['enable_smilies'],
				'enable_magic_url'  => $data['enable_urls'],
				'enable_sig'        => $data['enable_sig'],
				'message_subject'   => $subject,
				'message_text'      => $data['message'],
				'message_attachment'=> (!empty($data['attachment_data'])) ? 1 : 0,
				'bbcode_bitfield'   => $data['bbcode_bitfield'],
				'bbcode_uid'        => $data['bbcode_uid'],
				'to_address'        => implode(':', $to),
				'message_reported'  => 0,
			];
		break;

		case 'edit':
		case 'reparse':
			$sql_data = [
				'icon_id'           => $data['icon_id'],
				'enable_bbcode'     => $data['enable_bbcode'],
				'enable_smilies'    => $data['enable_smilies'],
				'enable_magic_url'  => $data['enable_urls'],
				'enable_sig'        => $data['enable_sig'],
				'message_subject'   => $subject,
				'message_text'      => $data['message'],
				'message_attachment'=> (!empty($data['attachment_data'])) ? 1 : 0,
				'bbcode_bitfield'   => $data['bbcode_bitfield'],
				'bbcode_uid'        => $data['bbcode_uid']
			];
			if ($mode == 'edit')
			{
				$sql_data['message_edit_time'] = $current_time;
			}
		break;
	}

	if (sizeof($sql_data))
	{
		$query = '';

		if ($mode == 'post' || $mode == 'reply' || $mode == 'quote' || $mode == 'quotepost')
		{
			$db->sql_query('INSERT INTO ' . PRIVMSGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data));
			$data['msg_id'] = $db->sql_nextid();
		}
		else if ($mode == 'edit')
		{
			$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
				SET message_edit_count = message_edit_count + 1, ' . $db->sql_build_array('UPDATE', $sql_data) . '
				WHERE msg_id = ' . $data['msg_id'];
			$db->sql_query($sql);
		}
		else if ($mode == 'reparse')
		{
			$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_data) . '
				WHERE msg_id = ' . $data['msg_id'];
			$db->sql_query($sql);
		}
	}

	if ($mode != 'edit' && $mode != 'reparse')
	{
		if ($sql)
		{
			$db->sql_query($sql);
		}
		unset($sql);

		$sql_ary = [];
		foreach ($recipients as $user_id)
		{
			$sql_ary[] = [
				'msg_id'        => (int) $data['msg_id'],
				'user_id'       => (int) $user_id,
				'author_id'     => (int) $data['from_user_id'],
				'folder_id'     => PRIVMSGS_NO_BOX,
				'pm_new'        => 1,
				'pm_unread'     => 1
			];
		}

		$db->sql_multi_insert(PRIVMSGS_TO_TABLE, $sql_ary);

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_new_privmsg = user_new_privmsg + 1, user_unread_privmsg = user_unread_privmsg + 1, user_last_privmsg = ' . time() . '
			WHERE ' . $db->sql_in_set('user_id', $recipients);
		$db->sql_query($sql);

		// Put PM into outbox
		if ($put_in_outbox)
		{
			$db->sql_query('INSERT INTO ' . PRIVMSGS_TO_TABLE . ' ' . $db->sql_build_array('INSERT', [
				'msg_id'        => (int) $data['msg_id'],
				'user_id'       => (int) $data['from_user_id'],
				'author_id'     => (int) $data['from_user_id'],
				'folder_id'     => PRIVMSGS_OUTBOX,
				'pm_new'        => 0,
				'pm_unread'     => 0])
			);
		}
	}

	// Set user last post time
	if ($mode == 'reply' || $mode == 'quote' || $mode == 'quotepost' || $mode == 'post')
	{
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_lastpost_time = {$current_time}
			WHERE user_id = " . $data['from_user_id'];
		$db->sql_query($sql);
	}

	// Submit Attachments
	if (!empty($data['attachment_data']) && $data['msg_id'] && in_array($mode, ['post', 'reply', 'edit', 'reparse', 'quote', 'quotepost']))
	{
		$space_taken = $files_added = 0;
		$orphan_rows = [];

		foreach ($data['attachment_data'] as $pos => $attach_row)
		{
			$orphan_rows[(int) $attach_row['attach_id']] = [];
		}

		if (sizeof($orphan_rows))
		{
			$sql = 'SELECT attach_id, filesize, physical_filename
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('attach_id', array_keys($orphan_rows)) . '
					AND in_message = 1
					AND is_orphan = 1
					AND poster_id = ' . $user->data['user_id'];
			$result = $db->sql_query($sql);

			$orphan_rows = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$orphan_rows[$row['attach_id']] = $row;
			}
			$db->sql_freeresult($result);
		}

		foreach ($data['attachment_data'] as $pos => $attach_row)
		{
			if ($attach_row['is_orphan'] && !isset($orphan_rows[$attach_row['attach_id']]))
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
				if (!@file_exists(PHPBB_ROOT_PATH . UPLOADS_PATH . '/' . utf8_basename($orphan_rows[$attach_row['attach_id']]['physical_filename'])))
				{
					continue;
				}

				$space_taken += $orphan_rows[$attach_row['attach_id']]['filesize'];
				$files_added++;

				$attach_sql = [
					'post_msg_id'       => $data['msg_id'],
					'topic_id'          => 0,
					'is_orphan'         => 0,
					'poster_id'         => $data['from_user_id'],
					'attach_comment'    => $attach_row['attach_comment'],
				];

				$sql = 'UPDATE ' . ATTACHMENTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $attach_sql) . '
					WHERE attach_id = ' . $attach_row['attach_id'] . '
						AND is_orphan = 1
						AND poster_id = ' . $user->data['user_id'];
				$db->sql_query($sql);
			}
		}

		if ($space_taken && $files_added)
		{
			set_config_count('upload_dir_size', $space_taken, true);
			set_config_count('num_files', $files_added, true);
		}
	}

	// Delete draft if post was loaded...
	$loaded_draft_id = request_var('loaded_draft_id', 0);
	if ($loaded_draft_id)
	{
		$sql = 'DELETE FROM ' . DRAFTS_TABLE . "
			WHERE draft_id = {$loaded_draft_id}
				AND user_id = " . $data['from_user_id'];
		$db->sql_query($sql);
	}

	$db->sql_transaction('commit');

	// Send Notifications
	if ($mode != 'edit' && $mode != 'reparse')
	{
		pm_notification($mode, $data['from_username'], $recipients, $subject, $data['message'], $data['msg_id']);
	}

	return $data['msg_id'];
}

/**
* PM Notification
*/
function pm_notification($mode, $author, $recipients, $subject, $message, $msg_id)
{
	global $db, $user, $config, $auth;

	$subject = censor_text($subject);

	// Exclude guests, current user and banned users from notifications
	$recipients = array_values(array_diff($recipients, [ANONYMOUS, (int) $user->data['user_id']]));

	if (!sizeof($recipients))
	{
		return;
	}

	if (!function_exists('phpbb_get_banned_user_ids'))
	{
		require_once(PHPBB_ROOT_PATH . 'includes/functions_user.php');
	}
	$banned_users = phpbb_get_banned_user_ids($recipients);
	$recipients = array_values(array_diff($recipients, $banned_users));

	if (!sizeof($recipients))
	{
		return;
	}

	$sql = 'SELECT user_id, username, user_email, user_lang_code, user_notify_pm, user_notify_type, user_jabber
		FROM ' . USERS_TABLE . '
		WHERE ' . $db->sql_in_set('user_id', $recipients);
	$result = $db->sql_query($sql);

	$msg_list_ary = [];
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['user_notify_pm'] == 1 && trim($row['user_email']))
		{
			$msg_list_ary[] = [
				'method'    => $row['user_notify_type'],
				'email'     => $row['user_email'],
				'jabber'    => $row['user_jabber'],
				'name'      => $row['username'],
				'lang'      => $row['user_lang_code']
			];
		}
	}
	$db->sql_freeresult($result);

	if (!sizeof($msg_list_ary))
	{
		return;
	}

	require_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.php');
	$messenger = new messenger();

	foreach ($msg_list_ary as $pos => $addr)
	{
		$messenger->template('privmsg_notify', $addr['lang']);

		$messenger->to($addr['email'], $addr['name']);
		$messenger->im($addr['jabber'], $addr['name']);

		$messenger->assign_vars([
			'SUBJECT'       => htmlspecialchars_decode($subject),
			'AUTHOR_NAME'   => htmlspecialchars_decode($author),
			'USERNAME'      => htmlspecialchars_decode($addr['name']),

			'U_INBOX'           => generate_board_url() . "/ucp.php?i=pm&folder=inbox",
			'U_VIEW_MESSAGE'    => generate_board_url() . "/ucp.php?i=pm&mode=view&p={$msg_id}",
		]);

		$messenger->send($addr['method']);
	}
	unset($msg_list_ary);

	$messenger->save_queue();

	unset($messenger);
}

/**
* Display Message History
*/
function message_history($msg_id, $user_id, $message_row, $folder, $in_post_mode = false)
{
	global $db, $user, $config, $template, $auth, $bbcode;

	// Select all receipts and the author from the pm we currently view, to only display their pm-history
	$sql = 'SELECT author_id, user_id
		FROM ' . PRIVMSGS_TO_TABLE . "
		WHERE msg_id = {$msg_id}";
	$result = $db->sql_query($sql);

	$recipients = [];
	while ($row = $db->sql_fetchrow($result))
	{
		$recipients[] = (int) $row['user_id'];
		$recipients[] = (int) $row['author_id'];
	}
	$db->sql_freeresult($result);
	$recipients = array_unique($recipients);

	// Get History Messages (could be newer)
	$sql = 'SELECT t.*, p.*, u.*
		FROM ' . PRIVMSGS_TABLE . ' p, ' . PRIVMSGS_TO_TABLE . ' t, ' . USERS_TABLE . ' u
		WHERE t.msg_id = p.msg_id
			AND p.author_id = u.user_id
			AND t.folder_id <> ' . PRIVMSGS_NO_BOX . '
			AND ' . $db->sql_in_set('t.author_id', $recipients, false, true) . "
			AND t.user_id = {$user_id}";

	// We no longer need those.
	unset($recipients);

	if (!$message_row['root_level'])
	{
		$sql .= " AND (p.root_level = {$msg_id} OR (p.root_level = 0 AND p.msg_id = {$msg_id}))";
	}
	else
	{
		$sql .= " AND (p.root_level = " . $message_row['root_level'] . ' OR p.msg_id = ' . $message_row['root_level'] . ')';
	}
	$sql .= ' ORDER BY p.message_time DESC';

	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if (!$row)
	{
		$db->sql_freeresult($result);
		return false;
	}

	$title = $row['message_subject'];

	$rowset = [];
	$bbcode_bitfield = '';
	$folder_url = append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'i=pm') . '&amp;folder=';

	do
	{
		$folder_id = (int) $row['folder_id'];

		$row['folder'][] = (isset($folder[$folder_id])) ? '<a href="' . $folder_url . $folder_id . '">' . $folder[$folder_id]['folder_name'] . '</a>' : $user->lang['UNKNOWN_FOLDER'];

		if (isset($rowset[$row['msg_id']]))
		{
			$rowset[$row['msg_id']]['folder'][] = (isset($folder[$folder_id])) ? '<a href="' . $folder_url . $folder_id . '">' . $folder[$folder_id]['folder_name'] . '</a>' : $user->lang['UNKNOWN_FOLDER'];
		}
		else
		{
			$rowset[$row['msg_id']] = $row;
			$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['bbcode_bitfield']);
		}
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	if (sizeof($rowset) == 1 && !$in_post_mode)
	{
		return false;
	}

	// Instantiate BBCode class
	if ((empty($bbcode) || $bbcode === false) && $bbcode_bitfield !== '')
	{
		if (!class_exists('bbcode'))
		{
			require_once(PHPBB_ROOT_PATH . 'includes/bbcode.php');
		}
		$bbcode = new bbcode(base64_encode($bbcode_bitfield));
	}

	$title = censor_text($title);

	$url = append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'i=pm');
	$next_history_pm = $previous_history_pm = $prev_id = 0;

	// Re-order rowset to be able to get the next/prev message rows...
	$rowset = array_values($rowset);

	for ($i = 0, $size = sizeof($rowset); $i < $size; $i++)
	{
		$row = &$rowset[$i];
		$id = (int) $row['msg_id'];

		$author_id  = $row['author_id'];
		$folder_id  = (int) $row['folder_id'];

		$subject    = $row['message_subject'];
		$message    = $row['message_text'];

		$message = censor_text($message);

		$decoded_message = false;

		if ($in_post_mode && $auth->acl_get('u_sendpm') && $author_id != ANONYMOUS)
		{
			$decoded_message = $message;
			decode_message($decoded_message, $row['bbcode_uid']);

			$decoded_message = bbcode_nl2br($decoded_message);
		}

		if ($row['bbcode_bitfield'])
		{
			$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield'], $row['message_time']);
		}

		$message = bbcode_nl2br($message);
		$message = smiley_text($message, !$row['enable_smilies']);

		$subject = censor_text($subject);

		if ($id == $msg_id)
		{
			$next_history_pm = (isset($rowset[$i + 1])) ? (int) $rowset[$i + 1]['msg_id'] : 0;
			$previous_history_pm = $prev_id;
		}

		$template->assign_block_vars('history_row', [
			'MESSAGE_AUTHOR_QUOTE'      => (($decoded_message) ? addslashes(get_username_string('username', $author_id, $row['username'], $row['user_colour'], $row['username'])) : ''),
			'MESSAGE_AUTHOR_FULL'       => get_username_string('full', $author_id, $row['username'], $row['user_colour'], $row['username']),
			'MESSAGE_AUTHOR_COLOUR'     => get_username_string('colour', $author_id, $row['username'], $row['user_colour'], $row['username']),
			'MESSAGE_AUTHOR'            => get_username_string('username', $author_id, $row['username'], $row['user_colour'], $row['username']),
			'U_MESSAGE_AUTHOR'          => get_username_string('profile', $author_id, $row['username'], $row['user_colour'], $row['username']),

			'SUBJECT'           => $subject,
			'SENT_DATE'         => $user->format_date($row['message_time']),
			'MESSAGE'           => $message,
			'FOLDER'            => implode(', ', $row['folder']),
			'DECODED_MESSAGE'   => $decoded_message,

			'S_CURRENT_MSG'     => ($row['msg_id'] == $msg_id),
			'S_AUTHOR_DELETED'  => ($author_id == ANONYMOUS),
			'S_IN_POST_MODE'    => $in_post_mode,

			'MSG_ID'            => $row['msg_id'],
			'U_VIEW_MESSAGE'    => "{$url}&amp;f={$folder_id}&amp;p=" . $row['msg_id'],
			'U_QUOTE'           => (!$in_post_mode && $auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "{$url}&amp;mode=compose&amp;action=quote&amp;f=" . $folder_id . "&amp;p=" . $row['msg_id'] : '',
			'U_POST_REPLY_PM'   => ($author_id != $user->data['user_id'] && $author_id != ANONYMOUS && $auth->acl_get('u_sendpm')) ? "{$url}&amp;mode=compose&amp;action=reply&amp;f={$folder_id}&amp;p=" . $row['msg_id'] : '']
		);
		unset($rowset[$i]);
		$prev_id = $id;
	}

	$template->assign_vars([
		'QUOTE_IMG'         => $user->img('icon_post_quote', $user->lang['REPLY_WITH_QUOTE']),
		'HISTORY_TITLE'     => $title,

		'U_VIEW_NEXT_HISTORY'       => ($next_history_pm) ? "{$url}&amp;p=" . $next_history_pm : '',
		'U_VIEW_PREVIOUS_HISTORY'   => ($previous_history_pm) ? "{$url}&amp;p=" . $previous_history_pm : '',
	]);

	return true;
}

/**
* Generates an array of coloured recipient names from a list of PMs.
*
* @param    array   $pm_by_id   An array of rows from PRIVMSGS_TABLE, keys are the msg_ids.
*
* @return   array               2D Array: array(msg_id => array('username string', ...), ...)
*                               Usernames are generated with {@link get_username_string get_username_string}
*/
function get_recipient_strings($pm_by_id)
{
	global $db, $user;

	$address_list = $recipient_list = $address = [];

	foreach ($pm_by_id as $message_id => $row)
	{
		$address_list[$message_id] = [];
		$address[$message_id] = rebuild_header($row['to_address']);

		foreach ($address[$message_id] as $user_id)
		{
			$recipient_list[$user_id] = ['name' => $user->lang['NA'], 'colour' => ''];
		}
	}

	if (!empty($recipient_list))
	{
		$sql = 'SELECT user_id as id, username as name, user_colour as colour
			FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', array_keys($recipient_list));
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$recipient_list[$row['id']] = ['name' => $row['name'], 'colour' => $row['colour']];
		}
		$db->sql_freeresult($result);
	}

	foreach ($address as $message_id => $user_ids)
	{
		foreach ($user_ids as $user_id)
		{
			$address_list[$message_id][] = get_username_string('full', $user_id, $recipient_list[$user_id]['name'], $recipient_list[$user_id]['colour']);
		}
	}

	return $address_list;
}
