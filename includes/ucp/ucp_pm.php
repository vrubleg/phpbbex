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
* Private Message Class
*
* $_REQUEST['folder'] display folder with the id used
* $_REQUEST['folder'] inbox|outbox|sentbox display folder with the associated name
*
*   Display Messages (default to inbox) - mode=view
*   Display single message - mode=view&p=[msg_id] or &p=[msg_id] (short linkage)
*
*   if the folder id with (&f=[folder_id]) is used when displaying messages, one query will be saved. If it is not used, phpBB needs to grab
*   the folder id first in order to display the input boxes and folder names and such things. ;) phpBB always checks this against the database to make
*   sure the user is able to view the message.
*
*   Composing Messages (mode=compose):
*       To specific user (u=[user_id])
*       Quoting a post (action=quotepost&p=[post_id])
*       Quoting a PM (action=quote&p=[msg_id])
*       Forwarding a PM (action=forward&p=[msg_id])
*/
class ucp_pm
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	var $p_name;

	function main($id, $mode)
	{
		global $user, $template, $auth, $db, $config;

		if (!$user->data['is_registered'])
		{
			trigger_error('NO_MESSAGE');
		}

		// Is PM disabled?
		if (!$config['allow_privmsg'])
		{
			trigger_error('PM_DISABLED');
		}

		$user->add_lang('posting');
		$template->assign_var('S_PRIVMSGS', true);

		// Folder directly specified?
		$folder_specified = request_var('folder', '');

		if (!in_array($folder_specified, ['inbox', 'outbox', 'sentbox']))
		{
			$folder_specified = (int) $folder_specified;
		}
		else
		{
			$folder_specified = ($folder_specified == 'inbox') ? PRIVMSGS_INBOX : (($folder_specified == 'outbox') ? PRIVMSGS_OUTBOX : PRIVMSGS_SENTBOX);
		}

		if (!$folder_specified)
		{
			$mode = (!$mode) ? request_var('mode', 'view') : $mode;
		}
		else
		{
			$mode = 'view';
		}

		require_once(PHPBB_ROOT_PATH . 'includes/functions_privmsgs.php');

		switch ($mode)
		{
			// Compose message
			case 'compose':
				$action = request_var('action', 'post');

				get_folder($user->data['user_id']);

				if (!$auth->acl_get('u_sendpm'))
				{
					// trigger_error('NO_AUTH_SEND_MESSAGE');
					$template->assign_vars([
						'S_NO_AUTH_SEND_MESSAGE'    => true,
						'S_COMPOSE_PM_VIEW'         => true,
					]);

					$tpl_file = 'ucp_pm_viewfolder';
					break;
				}

				require_once(PHPBB_ROOT_PATH . 'includes/ucp/ucp_pm_compose.php');
				compose_pm($id, $mode, $action);

				$tpl_file = 'posting_body';
			break;

			case 'drafts':

				get_folder($user->data['user_id']);
				$this->p_name = 'pm';

				// Call another module... please do not try this at home... Hoochie Coochie Man
				require_once(PHPBB_ROOT_PATH . 'includes/ucp/ucp_main.php');

				$module = new ucp_main($this);
				$module->u_action = $this->u_action;
				$module->main($id, $mode);

				$this->tpl_name = $module->tpl_name;
				$this->page_title = 'UCP_PM_DRAFTS';

				unset($module);
				return;

			break;

			case 'view':
				if ($folder_specified)
				{
					$folder_id = $folder_specified;
					$action = 'view_folder';
				}
				else
				{
					$folder_id = request_var('f', PRIVMSGS_NO_BOX);
					$action = request_var('action', 'view_folder');
				}

				$msg_id = request_var('p', 0);
				$view   = request_var('view', '');

				// View message if specified
				if ($msg_id)
				{
					$action = 'view_message';
				}

				if (!$auth->acl_get('u_readpm'))
				{
					trigger_error('NO_AUTH_READ_MESSAGE');
				}

				// Handle actions on marked messages
				$submit_mark    = isset($_POST['submit_mark']);
				$mark_option    = request_var('mark_option', '');

				// Message Mark Options
				if ($submit_mark)
				{
					handle_mark_actions($user->data['user_id'], $mark_option);
				}

				// If new messages arrived, place them into the appropriate folder
				if ($user->data['user_new_privmsg'] && ($action == 'view_folder' || $action == 'view_message'))
				{
					place_pm_into_folder();
				}

				if (!$msg_id && $folder_id == PRIVMSGS_NO_BOX)
				{
					$folder_id = PRIVMSGS_INBOX;
				}
				else if ($msg_id && $folder_id == PRIVMSGS_NO_BOX)
				{
					$sql = 'SELECT folder_id
						FROM ' . PRIVMSGS_TO_TABLE . "
						WHERE msg_id = {$msg_id}
							AND folder_id <> " . PRIVMSGS_NO_BOX . '
							AND user_id = ' . $user->data['user_id'];
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$row)
					{
						trigger_error('NO_MESSAGE');
					}
					$folder_id = (int) $row['folder_id'];
				}

				$message_row = [];
				if ($action == 'view_message' && $msg_id)
				{
					// Get Message user want to see
					if ($view == 'next' || $view == 'previous')
					{
						$sql_condition = ($view == 'next') ? '>' : '<';
						$sql_ordering = ($view == 'next') ? 'ASC' : 'DESC';

						$sql = 'SELECT t.msg_id
							FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . PRIVMSGS_TABLE . " p2
							WHERE p2.msg_id = {$msg_id}
								AND t.folder_id = {$folder_id}
								AND t.user_id = " . $user->data['user_id'] . "
								AND t.msg_id = p.msg_id
								AND p.message_time {$sql_condition} p2.message_time
							ORDER BY p.message_time {$sql_ordering}";
						$result = $db->sql_query_limit($sql, 1);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$row)
						{
							$message = ($view == 'next') ? 'NO_NEWER_PM' : 'NO_OLDER_PM';
							trigger_error($message);
						}
						else
						{
							$msg_id = $row['msg_id'];
						}
					}

					$sql = 'SELECT t.*, p.*, u.*
						FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . ' u
						WHERE t.user_id = ' . $user->data['user_id'] . "
							AND p.author_id = u.user_id
							AND t.folder_id = {$folder_id}
							AND t.msg_id = p.msg_id
							AND p.msg_id = {$msg_id}";
					$result = $db->sql_query($sql);
					$message_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$message_row)
					{
						trigger_error('NO_MESSAGE');
					}

					// Update unread status
					update_unread_status($message_row['pm_unread'], $message_row['msg_id'], $user->data['user_id'], $folder_id);
				}

				$folder = get_folder($user->data['user_id'], $folder_id);

				$template->assign_vars([
					'CUR_FOLDER_ID'         => $folder_id,
					'CUR_FOLDER_NAME'       => $folder[$folder_id]['folder_name'],

					'S_FOLDER_ACTION'       => $this->u_action . '&amp;action=view_folder',
					'S_PM_ACTION'           => $this->u_action . '&amp;action=' . $action,

					'U_INBOX'               => $this->u_action . '&amp;folder=inbox',
					'U_OUTBOX'              => $this->u_action . '&amp;folder=outbox',
					'U_SENTBOX'             => $this->u_action . '&amp;folder=sentbox',
					'U_CURRENT_FOLDER'      => $this->u_action . '&amp;folder=' . $folder_id,

					'S_IN_INBOX'            => ($folder_id == PRIVMSGS_INBOX),
					'S_IN_OUTBOX'           => ($folder_id == PRIVMSGS_OUTBOX),
					'S_IN_SENTBOX'          => ($folder_id == PRIVMSGS_SENTBOX),

					'FOLDER_CUR_MESSAGES'   => $folder[$folder_id]['num_messages'],
				]);

				if ($action == 'view_folder')
				{
					require_once(PHPBB_ROOT_PATH . 'includes/ucp/ucp_pm_viewfolder.php');
					view_folder($id, $mode, $folder_id, $folder);

					$tpl_file = 'ucp_pm_viewfolder';
				}
				else if ($action == 'view_message')
				{
					$template->assign_vars([
						'S_VIEW_MESSAGE'    => true,
						'MSG_ID'            => $msg_id]
					);

					if (!$msg_id)
					{
						trigger_error('NO_MESSAGE');
					}

					require_once(PHPBB_ROOT_PATH . 'includes/ucp/ucp_pm_viewmessage.php');
					view_message($id, $mode, $folder_id, $msg_id, $folder, $message_row);

					$tpl_file = ($view == 'print') ? 'ucp_pm_viewmessage_print' : 'ucp_pm_viewmessage';
				}

			break;

			default:
				trigger_error('NO_ACTION_MODE', E_USER_ERROR);
			break;
		}

		$template->assign_vars([
			'L_TITLE'           => $user->lang['UCP_PM_' . strtoupper($mode)],
			'S_UCP_ACTION'      => $this->u_action . ((isset($action)) ? "&amp;action={$action}" : '')]
		);

		// Set desired template
		$this->tpl_name = $tpl_file;
		$this->page_title = 'UCP_PM_' . strtoupper($mode);
	}
}
