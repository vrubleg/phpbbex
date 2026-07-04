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

class acp_bots
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;

		$action = request_var('action', '');
		$submit = isset($_POST['submit']);
		$mark   = request_var('mark', [0]);
		$bot_id = request_var('id', 0);

		if (isset($_POST['add']))
		{
			$action = 'add';
		}

		$error = [];

		$user->add_lang('acp/bots');
		$this->tpl_name = 'acp_bots';
		$this->page_title = 'ACP_BOTS';
		$form_key = 'acp_bots';
		add_form_key($form_key);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}

		// User wants to do something, how inconsiderate of them!
		switch ($action)
		{
			case 'activate':
				if ($bot_id || sizeof($mark))
				{
					$sql_id = ($bot_id) ? " = {$bot_id}" : ' IN (' . implode(', ', $mark) . ')';

					$sql = 'UPDATE ' . BOTS_TABLE . "
						SET bot_active = 1
						WHERE bot_id {$sql_id}";
					$db->sql_query($sql);
				}

				$cache->destroy('_bots');
			break;

			case 'deactivate':
				if ($bot_id || sizeof($mark))
				{
					$sql_id = ($bot_id) ? " = {$bot_id}" : ' IN (' . implode(', ', $mark) . ')';

					$sql = 'UPDATE ' . BOTS_TABLE . "
						SET bot_active = 0
						WHERE bot_id {$sql_id}";
					$db->sql_query($sql);
				}

				$cache->destroy('_bots');
			break;

			case 'delete':
				if ($bot_id || sizeof($mark))
				{
					if (confirm_box(true))
					{
						$sql_id = ($bot_id) ? " = {$bot_id}" : ' IN (' . implode(', ', $mark) . ')';

						$sql = 'SELECT bot_name
							FROM ' . BOTS_TABLE . "
							WHERE bot_id {$sql_id}";
						$result = $db->sql_query($sql);

						$bot_name_ary = [];
						while ($row = $db->sql_fetchrow($result))
						{
							$bot_name_ary[] = $row['bot_name'];
						}
						$db->sql_freeresult($result);

						$db->sql_transaction('begin');

						$sql = 'DELETE FROM ' . BOTS_TABLE . "
							WHERE bot_id {$sql_id}";
						$db->sql_query($sql);

						$sql = 'UPDATE ' . SESSIONS_TABLE . "
							SET session_bot_id = 0
							WHERE session_bot_id {$sql_id}";
						$db->sql_query($sql);

						$db->sql_transaction('commit');

						$cache->destroy('_bots');

						add_log('admin', 'LOG_BOT_DELETE', implode(', ', $bot_name_ary));
						trigger_error($user->lang['BOT_DELETED'] . adm_back_link($this->u_action));
					}
					else
					{
						confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields([
							'mark'      => $mark,
							'id'        => $bot_id,
							'mode'      => $mode,
							'action'    => $action])
						);
					}
				}
			break;

			case 'edit':
			case 'add':
				$bot_row = [
					'bot_name'      => utf8_normalize_nfc(request_var('bot_name', '', true)),
					'bot_agent'     => request_var('bot_agent', ''),
					'bot_ip'        => request_var('bot_ip', ''),
					'bot_active'    => request_var('bot_active', true),
				];

				if ($submit)
				{
					if (!$bot_row['bot_agent'] && !$bot_row['bot_ip'])
					{
						$error[] = $user->lang['ERR_BOT_NO_MATCHES'];
					}

					if ($bot_row['bot_ip'] && !preg_match('#^[\d\.,:]+$#', $bot_row['bot_ip']))
					{
						if (!$ip_list = gethostbynamel($bot_row['bot_ip']))
						{
							$error[] = $user->lang['ERR_BOT_NO_IP'];
						}
						else
						{
							$bot_row['bot_ip'] = implode(',', $ip_list);
						}
					}
					$bot_row['bot_ip'] = str_replace(' ', '', $bot_row['bot_ip']);

					// Make sure the admin is not adding a bot with an user agent similar to his one
					if ($bot_row['bot_agent'] && substr($user->data['session_browser'], 0, 249) === substr($bot_row['bot_agent'], 0, 249))
					{
						$error[] = $user->lang['ERR_BOT_AGENT_MATCHES_UA'];
					}

					$bot_name = false;
					if ($bot_id)
					{
						$sql = 'SELECT bot_name
							FROM ' . BOTS_TABLE . "
							WHERE bot_id = {$bot_id}";
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$row)
						{
							$error[] = $user->lang['NO_BOT'];
						}
						else
						{
							$bot_name = $row['bot_name'];
						}
					}
					if (!$this->validate_botname($bot_row['bot_name'], $bot_name))
					{
						$error[] = $user->lang['BOT_NAME_TAKEN'];
					}

					if (!sizeof($error))
					{
						if ($action == 'add')
						{
							$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', [
								'bot_name'      => (string) $bot_row['bot_name'],
								'bot_active'    => (int) $bot_row['bot_active'],
								'bot_agent'     => (string) $bot_row['bot_agent'],
								'bot_ip'        => (string) $bot_row['bot_ip'],
							]);
							$db->sql_query($sql);

							$log = 'ADDED';
						}
						else if ($bot_id)
						{
							$sql = 'SELECT bot_name
								FROM ' . BOTS_TABLE . "
								WHERE bot_id = {$bot_id}";
							$result = $db->sql_query($sql);
							$row = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);

							if (!$row)
							{
								trigger_error($user->lang['NO_BOT'] . adm_back_link($this->u_action . "&amp;id={$bot_id}&amp;action={$action}"), E_USER_WARNING);
							}

							$sql = 'UPDATE ' . BOTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', [
								'bot_name'      => (string) $bot_row['bot_name'],
								'bot_active'    => (int) $bot_row['bot_active'],
								'bot_agent'     => (string) $bot_row['bot_agent'],
								'bot_ip'        => (string) $bot_row['bot_ip'],
							]) . " WHERE bot_id = {$bot_id}";
							$db->sql_query($sql);

							$log = 'UPDATED';
						}

						$cache->destroy('_bots');

						add_log('admin', 'LOG_BOT_' . $log, $bot_row['bot_name']);
						trigger_error($user->lang['BOT_' . $log] . adm_back_link($this->u_action));

					}
				}
				else if ($bot_id)
				{
					$sql = 'SELECT *
						FROM ' . BOTS_TABLE . "
						WHERE bot_id = {$bot_id}";
					$result = $db->sql_query($sql);
					$bot_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$bot_row)
					{
						trigger_error($user->lang['NO_BOT'] . adm_back_link($this->u_action . "&amp;id={$bot_id}&amp;action={$action}"), E_USER_WARNING);
					}
				}

				$s_active_options = '';
				$_options = ['0' => 'NO', '1' => 'YES'];
				foreach ($_options as $value => $lang)
				{
					$selected = ($bot_row['bot_active'] == $value) ? ' selected="selected"' : '';
					$s_active_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
				}

				$l_title = ($action == 'edit') ? 'EDIT' : 'ADD';

				$template->assign_vars([
					'L_TITLE'       => $user->lang['BOT_' . $l_title],
					'U_ACTION'      => $this->u_action . "&amp;id={$bot_id}&amp;action={$action}",
					'U_BACK'        => $this->u_action,
					'ERROR_MSG'     => (sizeof($error)) ? implode('<br />', $error) : '',

					'BOT_NAME'      => $bot_row['bot_name'],
					'BOT_IP'        => $bot_row['bot_ip'],
					'BOT_AGENT'     => $bot_row['bot_agent'],

					'S_EDIT_BOT'        => true,
					'S_ACTIVE_OPTIONS'  => $s_active_options,
					'S_ERROR'           => (sizeof($error) > 0),
				]);

				return;

			break;
		}

		$s_options = '';
		$_options = ['activate' => 'BOT_ACTIVATE', 'deactivate' => 'BOT_DEACTIVATE', 'delete' => 'DELETE'];
		foreach ($_options as $value => $lang)
		{
			$s_options .= '<option value="' . $value . '">' . $user->lang[$lang] . '</option>';
		}

		$template->assign_vars([
			'U_ACTION'      => $this->u_action,
			'S_BOT_OPTIONS' => $s_options,
		]);

		$sql = 'SELECT bot_id, bot_name, bot_active, bot_lastvisit
			FROM ' . BOTS_TABLE . '
			ORDER BY bot_lastvisit DESC, bot_name ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$active_lang = (!$row['bot_active']) ? 'BOT_ACTIVATE' : 'BOT_DEACTIVATE';
			$active_value = (!$row['bot_active']) ? 'activate' : 'deactivate';

			$template->assign_block_vars('bots', [
				'BOT_NAME'      => $row['bot_name'],
				'BOT_ID'        => $row['bot_id'],
				'LAST_VISIT'    => ($row['bot_lastvisit']) ? $user->format_date($row['bot_lastvisit']) : $user->lang['BOT_NEVER'],

				'U_ACTIVATE_DEACTIVATE' => $this->u_action . "&amp;id={$row['bot_id']}&amp;action={$active_value}",
				'L_ACTIVATE_DEACTIVATE' => $user->lang[$active_lang],
				'U_EDIT'                => $this->u_action . "&amp;id={$row['bot_id']}&amp;action=edit",
				'U_DELETE'              => $this->u_action . "&amp;id={$row['bot_id']}&amp;action=delete",
			]);
		}
		$db->sql_freeresult($result);
	}

	/**
	* Validate bot name against bots table
	*/
	function validate_botname($newname, $oldname = false)
	{
		global $db;

		if ($oldname && $newname === $oldname)
		{
			return true;
		}

		$sql = 'SELECT bot_name
			FROM ' . BOTS_TABLE . "
			WHERE bot_name = '" . $db->sql_escape($newname) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return !$row;
	}
}
