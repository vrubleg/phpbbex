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

class acp_language
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;

		require_once(PHPBB_ROOT_PATH . 'includes/functions_user.php');

		// Check and set some common vars

		$action     = (isset($_POST['update_details'])) ? 'update_details' : '';

		$submit = !empty($action);
		$action = (empty($action)) ? request_var('action', '') : $action;

		$form_name = 'acp_lang';
		add_form_key('acp_lang');

		$lang_code = basename(request_var('id', ''));

		$user->add_lang('acp/language');
		$this->tpl_name = 'acp_language';
		$this->page_title = 'ACP_LANGUAGE_PACKS';

		switch ($action)
		{
			case 'update_details':

				if (!$submit || !check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$lang_code)
				{
					trigger_error($user->lang['NO_LANG_CODE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . LANG_TABLE . "
					WHERE lang_code = '" . $db->sql_escape($lang_code) . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$sql_ary    = [
					'lang_english_name'     => request_var('lang_english_name', $row['lang_english_name']),
					'lang_local_name'       => utf8_normalize_nfc(request_var('lang_local_name', $row['lang_local_name'], true)),
				];

				$db->sql_query('UPDATE ' . LANG_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE lang_code = '" . $db->sql_escape($lang_code) . "'");

				add_log('admin', 'LOG_LANGUAGE_PACK_UPDATED', $sql_ary['lang_english_name']);

				trigger_error($user->lang['LANGUAGE_DETAILS_UPDATED'] . adm_back_link($this->u_action));
			break;

			case 'details':

				if (!$lang_code)
				{
					trigger_error($user->lang['NO_LANG_CODE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$this->page_title = 'LANGUAGE_PACK_DETAILS';

				$sql = 'SELECT *
					FROM ' . LANG_TABLE . "
					WHERE lang_code = '" . $db->sql_escape($lang_code) . "'";
				$result = $db->sql_query($sql);
				$lang_entries = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$template->assign_vars([
					'S_DETAILS'         => true,
					'U_ACTION'          => $this->u_action . "&amp;action=details&amp;id={$lang_code}",
					'U_BACK'            => $this->u_action,
					'LANG_LOCAL_NAME'   => $lang_entries['lang_local_name'],
					'LANG_ENGLISH_NAME' => $lang_entries['lang_english_name'],
					'LANG_CODE'         => $lang_entries['lang_code'],
				]);

				return;

			break;

			case 'delete':

				if (!$lang_code)
				{
					trigger_error($user->lang['NO_LANG_CODE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . LANG_TABLE . "
					WHERE lang_code = '" . $db->sql_escape($lang_code) . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['LANGUAGE_PACK_NOT_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if ($row['lang_code'] == $config['default_lang_code'])
				{
					trigger_error($user->lang['NO_REMOVE_DEFAULT_LANG'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$db->sql_query('DELETE FROM ' . LANG_TABLE . " WHERE lang_code = '" . $db->sql_escape($lang_code) . "'");

					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_lang_code = '" . $db->sql_escape($config['default_lang_code']) . "'
						WHERE user_lang_code = '" . $db->sql_escape($row['lang_code']) . "'";
					$db->sql_query($sql);

					// We also need to remove the translated entries for custom profile fields - we want clean tables, don't we?
					$sql = 'DELETE FROM ' . PROFILE_LANG_TABLE . " WHERE lang_code = '" . $db->sql_escape($lang_code) . "'";
					$db->sql_query($sql);

					$sql = 'DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . " WHERE lang_code = '" . $db->sql_escape($lang_code) . "'";
					$db->sql_query($sql);

					$cache->purge();

					add_log('admin', 'LOG_LANGUAGE_PACK_DELETED', $row['lang_english_name']);

					trigger_error(sprintf($user->lang['LANGUAGE_PACK_DELETED'], $row['lang_english_name']) . adm_back_link($this->u_action));
				}
				else
				{
					$s_hidden_fields = [
						'i'         => $id,
						'mode'      => $mode,
						'action'    => $action,
						'id'        => $lang_code,
					];
					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($s_hidden_fields));
				}
			break;

			case 'install':
				$lang_code = basename(request_var('code', ''));

				if (!$lang_code || strlen($lang_code) > 5 || !file_exists(PHPBB_ROOT_PATH . "language/{$lang_code}/iso.txt"))
				{
					trigger_error($user->lang['LANGUAGE_PACK_NOT_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$file = file(PHPBB_ROOT_PATH . "language/{$lang_code}/iso.txt");

				$lang_pack = [
					'code'      => $lang_code,
					'name'      => trim(htmlspecialchars($file[0])),
					'local_name'=> trim(htmlspecialchars($file[1], ENT_COMPAT, 'UTF-8')),
				];
				unset($file);

				$sql = 'SELECT lang_code
					FROM ' . LANG_TABLE . "
					WHERE lang_code = '" . $db->sql_escape($lang_code) . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row)
				{
					trigger_error($user->lang['LANGUAGE_PACK_ALREADY_INSTALLED'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$lang_pack['name'] || !$lang_pack['local_name'])
				{
					trigger_error($user->lang['INVALID_LANGUAGE_PACK'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Add language pack
				$sql_ary = [
					'lang_code'         => $lang_pack['code'],
					'lang_english_name' => $lang_pack['name'],
					'lang_local_name'   => $lang_pack['local_name'],
				];

				$db->sql_query('INSERT INTO ' . LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				$lang_code = $lang_pack['code'];
				$default_lang_code = $config['default_lang_code'];

				// We want to notify the admin that custom profile fields need to be updated for the new language.
				$notify_cpf_update = false;

				// From the mysql documentation:
				// Prior to MySQL 4.0.14, the target table of the INSERT statement cannot appear in the FROM clause of the SELECT part of the query. This limitation is lifted in 4.0.14.
				// Due to this we stay on the safe side if we do the insertion "the manual way"

				$sql = 'SELECT field_id, lang_name, lang_explain, lang_default_value
					FROM ' . PROFILE_LANG_TABLE . "
					WHERE lang_code = '" . $db->sql_escape($default_lang_code) . "'";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$row['lang_code'] = $lang_code;
					$db->sql_query('INSERT INTO ' . PROFILE_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $row));
					$notify_cpf_update = true;
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT field_id, option_id, field_type, lang_value
					FROM ' . PROFILE_FIELDS_LANG_TABLE . "
					WHERE lang_code = '" . $db->sql_escape($default_lang_code) . "'";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$row['lang_code'] = $lang_code;
					$db->sql_query('INSERT INTO ' . PROFILE_FIELDS_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $row));
					$notify_cpf_update = true;
				}
				$db->sql_freeresult($result);

				$cache->purge();

				add_log('admin', 'LOG_LANGUAGE_PACK_INSTALLED', $lang_pack['name']);

				$message = sprintf($user->lang['LANGUAGE_PACK_INSTALLED'], $lang_pack['name']);
				$message .= ($notify_cpf_update) ? '<br /><br />' . $user->lang['LANGUAGE_PACK_CPF_UPDATE'] : '';
				trigger_error($message . adm_back_link($this->u_action));

			break;

		}

		$sql = 'SELECT user_lang_code, COUNT(user_lang_code) AS lang_count
			FROM ' . USERS_TABLE . '
			GROUP BY user_lang_code';
		$result = $db->sql_query($sql);

		$lang_count = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$lang_count[$row['user_lang_code']] = $row['lang_count'];
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT *
			FROM ' . LANG_TABLE . '
			ORDER BY lang_english_name';
		$result = $db->sql_query($sql);

		$installed = [];

		while ($row = $db->sql_fetchrow($result))
		{
			$installed[] = $row['lang_code'];
			$tagstyle = ($row['lang_code'] == $config['default_lang_code']) ? '*' : '';

			$template->assign_block_vars('lang', [
				'U_DETAILS'         => $this->u_action . "&amp;action=details&amp;id={$row['lang_code']}",
				'U_DELETE'          => $this->u_action . "&amp;action=delete&amp;id={$row['lang_code']}",

				'ENGLISH_NAME'      => $row['lang_english_name'],
				'TAG'               => $tagstyle,
				'LOCAL_NAME'        => $row['lang_local_name'],
				'CODE'              => $row['lang_code'],
				'USED_BY'           => $lang_count[$row['lang_code']] ?? 0,
			]);
		}
		$db->sql_freeresult($result);

		$new_ary = $iso = [];
		$dp = @opendir(PHPBB_ROOT_PATH . 'language');

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ($file[0] == '.' || strlen($file) > 5 || !is_dir(PHPBB_ROOT_PATH . 'language/' . $file))
				{
					continue;
				}

				if (file_exists(PHPBB_ROOT_PATH . "language/{$file}/iso.txt"))
				{
					if (!in_array($file, $installed))
					{
						if ($iso = file(PHPBB_ROOT_PATH . "language/{$file}/iso.txt"))
						{
							if (sizeof($iso) >= 2)
							{
								$new_ary[$file] = [
									'code'      => $file,
									'name'      => trim($iso[0]),
									'local_name'=> trim($iso[1]),
								];
							}
						}
					}
				}
			}
			closedir($dp);
		}

		unset($installed);

		if (sizeof($new_ary))
		{
			foreach ($new_ary as $code => $lang_ary)
			{
				$template->assign_block_vars('notinst', [
					'CODE'          => htmlspecialchars($lang_ary['code']),
					'LOCAL_NAME'    => htmlspecialchars($lang_ary['local_name'], ENT_COMPAT, 'UTF-8'),
					'NAME'          => htmlspecialchars($lang_ary['name'], ENT_COMPAT, 'UTF-8'),
					'U_INSTALL'     => $this->u_action . '&amp;action=install&amp;code=' . urlencode($lang_ary['code']),
				]);
			}
		}

		unset($new_ary);
	}

}
