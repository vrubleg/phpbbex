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

class acp_disallow
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $config;

		require_once(PHPBB_ROOT_PATH . 'includes/functions_user.php');

		$user->add_lang('acp/posting');

		// Set up general vars
		$this->tpl_name = 'acp_disallow';
		$this->page_title = 'ACP_DISALLOW_USERNAMES';

		$form_key = 'acp_disallow';
		add_form_key($form_key);

		$disallow = (isset($_POST['disallow'])) ? true : false;
		$allow = (isset($_POST['allow'])) ? true : false;

		if (($allow || $disallow) && !check_form_key($form_key))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($disallow)
		{
			$disallowed_user = str_replace('*', '%', utf8_normalize_nfc(request_var('disallowed_user', '', true)));

			if (!$disallowed_user)
			{
				trigger_error($user->lang['NO_USERNAME_SPECIFIED'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT disallow_id
				FROM ' . DISALLOW_TABLE . "
				WHERE disallow_username = '" . $db->sql_escape($disallowed_user) . "'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row)
			{
				trigger_error($user->lang['DISALLOWED_ALREADY'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'INSERT INTO ' . DISALLOW_TABLE . ' ' . $db->sql_build_array('INSERT', array('disallow_username' => $disallowed_user));
			$db->sql_query($sql);

			$cache->destroy('_disallowed_usernames');

			$message = $user->lang['DISALLOW_SUCCESSFUL'];
			add_log('admin', 'LOG_DISALLOW_ADD', str_replace('%', '*', $disallowed_user));

			trigger_error($message . adm_back_link($this->u_action));
		}
		else if ($allow)
		{
			$disallowed_id = request_var('disallowed_id', 0);

			if (!$disallowed_id)
			{
				trigger_error($user->lang['NO_USERNAME_SPECIFIED'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'DELETE FROM ' . DISALLOW_TABLE . '
				WHERE disallow_id = ' . $disallowed_id;
			$db->sql_query($sql);

			$cache->destroy('_disallowed_usernames');

			add_log('admin', 'LOG_DISALLOW_DELETE');

			trigger_error($user->lang['DISALLOWED_DELETED'] . adm_back_link($this->u_action));
		}

		// Grab the current list of disallowed usernames...
		$sql = 'SELECT *
			FROM ' . DISALLOW_TABLE;
		$result = $db->sql_query($sql);

		$disallow_select = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$disallow_select .= '<option value="' . $row['disallow_id'] . '">' . str_replace('%', '*', $row['disallow_username']) . '</option>';
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'S_DISALLOWED_NAMES'	=> $disallow_select)
		);
	}
}
