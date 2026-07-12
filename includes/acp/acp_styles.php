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

class acp_styles
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $db, $user, $config;

		$user->add_lang('acp/styles');

		$this->tpl_name = 'acp_styles';
		$this->page_title = 'ACP_STYLES';

		if ($mode != 'style')
		{
			trigger_error($user->lang['NO_MODE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$action = request_var('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;
		$style_id = request_var('id', 0);

		switch ($action)
		{
			case 'delete':
				if ($style_id)
				{
					$this->remove($style_id);
					return;
				}
			break;

			case 'install':
				$this->install();
				return;
			break;

			case 'add':
				$this->add();
				return;
			break;

			case 'details':
				if ($style_id)
				{
					$this->details($style_id);
					return;
				}
			break;

			case 'activate':
			case 'deactivate':
				if ($style_id == $config['default_style'])
				{
					trigger_error($user->lang['DEACTIVATE_DEFAULT'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (($action == 'deactivate' && confirm_box(true)) || $action == 'activate')
				{
					$sql = 'UPDATE ' . STYLES_TABLE . '
						SET style_active = ' . (($action == 'activate') ? 1 : 0) . '
						WHERE style_id = ' . $style_id;
					$db->sql_query($sql);

					if ($action == 'deactivate')
					{
						$sql = 'UPDATE ' . USERS_TABLE . '
							SET user_style = ' . $config['default_style'] . "
							WHERE user_style = {$style_id}";
						$db->sql_query($sql);
					}
				}
				else if ($action == 'deactivate')
				{
					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields([
						'i'         => $id,
						'mode'      => $mode,
						'action'    => $action,
						'id'        => $style_id,
					]));
				}
			break;
		}

		$this->frontend();
	}

	/**
	* Build styles overview.
	*/
	function frontend()
	{
		global $user, $template, $db, $config, $cache;

		$sql = 'SELECT user_style, COUNT(user_style) AS style_count
			FROM ' . USERS_TABLE . '
			GROUP BY user_style';
		$result = $db->sql_query($sql);

		$style_count = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$style_count[$row['user_style']] = $row['style_count'];
		}
		$db->sql_freeresult($result);

		$this->page_title = 'ACP_STYLES';

		$template->assign_vars([
			'S_FRONTEND'        => true,
			'S_STYLE'           => true,

			'L_TITLE'           => $user->lang[$this->page_title],
			'L_EXPLAIN'         => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'            => $user->lang['STYLE_NAME'],
			'L_INSTALLED'       => $user->lang['INSTALLED_STYLE'],
			'L_UNINSTALLED'     => $user->lang['UNINSTALLED_STYLE'],
			'L_NO_UNINSTALLED'  => $user->lang['NO_UNINSTALLED_STYLE'],
			'L_CREATE'          => $user->lang['CREATE_STYLE'],

			'U_ACTION'          => $this->u_action,
		]);

		$sql = 'SELECT *
			FROM ' . STYLES_TABLE . '
			ORDER BY style_active DESC, LOWER(style_name) ASC';
		$result = $db->sql_query($sql);

		$installed = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$installed[] = $row['style_name'];

			$stylevis = (!$row['style_active']) ? 'activate' : 'deactivate';
			$s_actions = [
				'<a href="' . $this->u_action . '&amp;action=details&amp;id=' . $row['style_id'] . '">' . $user->lang['DETAILS'] . '</a>',
				'<a href="' . $this->u_action . '&amp;action=' . $stylevis . '&amp;id=' . $row['style_id'] . '">' . $user->lang['STYLE_' . strtoupper($stylevis)] . '</a>',
				'<a href="' . $this->u_action . '&amp;action=delete&amp;id=' . $row['style_id'] . '">' . $user->lang['DELETE'] . '</a>',
				'<a href="' . append_sid(PHPBB_ROOT_PATH . 'index.php', 'style=' . $row['style_id']) . '">' . $user->lang['PREVIEW'] . '</a>',
			];

			$template->assign_block_vars('installed', [
				'S_DEFAULT_STYLE'   => ($row['style_id'] == $config['default_style']),
				'S_ACTIONS'         => implode(' | ', $s_actions),
				'S_INACTIVE'        => !$row['style_active'],

				'NAME'              => $row['style_name'],
				'STYLE_COUNT'       => $style_count[$row['style_id']] ?? 0,
			]);
		}
		$db->sql_freeresult($result);

		$new_ary = [];
		foreach ($this->available_style_dirs() as $dir => $cfg)
		{
			$name = $cfg['name'] ?? '';
			if (!$name || in_array($name, $installed))
			{
				continue;
			}

			$new_ary[$name . $dir] = [
				'path'      => $dir,
				'name'      => $name,
				'copyright' => $cfg['copyright'] ?? '',
			];
		}
		unset($installed);

		if (sizeof($new_ary))
		{
			ksort($new_ary);

			foreach ($new_ary as $cfg)
			{
				$template->assign_block_vars('uninstalled', [
					'NAME'          => $cfg['name'],
					'COPYRIGHT'     => $cfg['copyright'] ?? '',
					'U_INSTALL'     => $this->u_action . '&amp;action=install&amp;path=' . urlencode($cfg['path']),
				]);
			}
		}
	}

	/**
	* Remove style preset from the database.
	*/
	function remove($style_id)
	{
		global $db, $template, $user, $cache, $config;

		$new_id = request_var('new_id', 0);
		$update = isset($_POST['update']);

		$sql = 'SELECT style_id, style_name
			FROM ' . STYLES_TABLE . "
			WHERE style_id = {$style_id}";
		$result = $db->sql_query($sql);
		$style_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$style_row)
		{
			trigger_error($user->lang['NO_STYLE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$s_replace_options = $this->style_options($new_id, $style_id);
		if (!$s_replace_options)
		{
			trigger_error($user->lang['ONLY_STYLE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($update)
		{
			if (!$new_id)
			{
				trigger_error($user->lang['STYLE_ERR_NO_IDS'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'DELETE FROM ' . STYLES_TABLE . "
				WHERE style_id = {$style_id}";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_style = {$new_id}
				WHERE user_style = {$style_id}";
			$db->sql_query($sql);

			if ($style_id == $config['default_style'])
			{
				set_config('default_style', $new_id);
			}

			$cache->destroy('sql', STYLES_TABLE);

			add_log('admin', 'LOG_STYLE_DELETE', $style_row['style_name']);
			trigger_error($user->lang['STYLE_DELETED'] . adm_back_link($this->u_action));
		}

		$this->page_title = 'DELETE_STYLE';

		$template->assign_vars([
			'S_DELETE'           => true,
			'S_REPLACE_OPTIONS'  => $s_replace_options,

			'L_TITLE'            => $user->lang[$this->page_title],
			'L_EXPLAIN'          => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'             => $user->lang['STYLE_NAME'],
			'L_REPLACE'          => $user->lang['REPLACE_STYLE'],
			'L_REPLACE_EXPLAIN'  => $user->lang['REPLACE_STYLE_EXPLAIN'],

			'U_ACTION'           => $this->u_action . "&amp;action=delete&amp;id={$style_id}",
			'U_BACK'             => $this->u_action,

			'NAME'               => $style_row['style_name'],
		]);
	}

	/**
	* Display details.
	*/
	function details($style_id)
	{
		global $template, $db, $config, $user, $cache;

		$update = isset($_POST['update']);
		$error = [];

		$sql = 'SELECT *
			FROM ' . STYLES_TABLE . "
			WHERE style_id = {$style_id}";
		$result = $db->sql_query($sql);
		$style_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$style_row)
		{
			trigger_error($user->lang['NO_STYLE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$style_row['style_default'] = ($config['default_style'] == $style_id) ? 1 : 0;

		if ($update)
		{
			$style_row = array_merge($style_row, [
				'style_name'      => utf8_normalize_nfc(request_var('name', '', true)),
				'template_dir'    => request_var('template_dir', ''),
				'theme_dir'       => request_var('theme_dir', ''),
				'imageset_dir'    => request_var('imageset_dir', ''),
				'style_active'    => request_var('style_active', 0),
				'style_default'   => request_var('style_default', 0),
			]);

			if ($style_row['style_default'])
			{
				$style_row['style_active'] = 1;
			}

			$this->validate_style($style_row, $error, $style_id);
		}

		if ($update && !sizeof($error))
		{
			$sql_ary = [
				'style_name'        => $style_row['style_name'],
				'template_dir'      => $style_row['template_dir'],
				'theme_dir'         => $style_row['theme_dir'],
				'imageset_dir'      => $style_row['imageset_dir'],
				'style_active'      => (int) $style_row['style_active'],
			];

			$sql = 'UPDATE ' . STYLES_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE style_id = {$style_id}";
			$db->sql_query($sql);

			if ($style_row['style_default'])
			{
				set_config('default_style', $style_id);
			}

			$cache->destroy('sql', STYLES_TABLE);

			add_log('admin', 'LOG_STYLE_EDIT_DETAILS', $style_row['style_name']);
			trigger_error($user->lang['STYLE_DETAILS_UPDATED'] . adm_back_link($this->u_action));
		}

		$copyright = $cache->obtain_style_cfg($style_row['style_name'], 'style')['copyright'] ?? '';

		$this->page_title = 'EDIT_DETAILS_STYLE';

		$template->assign_vars([
			'S_DETAILS'             => true,
			'S_ERROR_MSG'           => (sizeof($error) > 0),
			'S_STYLE'               => true,
			'S_STYLE_ACTIVE'        => $style_row['style_active'] ?? 0,
			'S_STYLE_DEFAULT'       => $style_row['style_default'] ?? 0,

			'S_TEMPLATE_OPTIONS'    => $this->component_options('template', $style_row['template_dir']),
			'S_THEME_OPTIONS'       => $this->component_options('theme', $style_row['theme_dir']),
			'S_IMAGESET_OPTIONS'    => $this->component_options('imageset', $style_row['imageset_dir']),

			'U_ACTION'              => $this->u_action . '&amp;action=details&amp;id=' . $style_id,
			'U_BACK'                => $this->u_action,

			'L_TITLE'               => $user->lang[$this->page_title],
			'L_EXPLAIN'             => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'                => $user->lang['STYLE_NAME'],

			'ERROR_MSG'             => (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'                  => $style_row['style_name'],
			'COPYRIGHT'             => $copyright,
		]);
	}

	/**
	* Install style from style.cfg.
	*/
	function install()
	{
		global $cache, $user, $template;

		$error = [];
		$install_path = request_var('path', '');
		$update = isset($_POST['update']);
		$installcfg = $cache->obtain_style_cfg($install_path, 'style');

		if (!$installcfg)
		{
			trigger_error($user->lang['NO_STYLE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$style_row = [
			'style_id'       => 0,
			'style_name'     => $installcfg['name'] ?? '',
			'template_dir'   => $installcfg['required_template'] ?? $install_path,
			'theme_dir'      => $installcfg['required_theme'] ?? $install_path,
			'imageset_dir'   => $installcfg['required_imageset'] ?? $install_path,
			'style_active'   => request_var('style_active', 1),
			'style_default'  => request_var('style_default', 0),
		];

		if (!version_compare($installcfg['version'] ?? '', PHPBBEX_VERSION, '=='))
		{
			$error[] = sprintf($user->lang['STYLE_ERR_VERSION'], PHPBBEX_VERSION, $installcfg['version'] ?? '');
		}
		$this->validate_style($style_row, $error);

		if ($update && !sizeof($error))
		{
			$this->install_style($error, $style_row);

			if (!sizeof($error))
			{
				trigger_error($user->lang['STYLE_ADDED'] . adm_back_link($this->u_action));
			}
		}

		$this->page_title = 'INSTALL_STYLE';

		$template->assign_vars([
			'S_DETAILS'         => true,
			'S_INSTALL'         => true,
			'S_ERROR_MSG'       => (sizeof($error) > 0),
			'S_STYLE'           => true,
			'S_STYLE_ACTIVE'    => $style_row['style_active'] ?? 0,
			'S_STYLE_DEFAULT'   => $style_row['style_default'] ?? 0,

			'U_ACTION'          => $this->u_action . '&amp;action=install&amp;path=' . urlencode($install_path),
			'U_BACK'            => $this->u_action,

			'L_TITLE'           => $user->lang[$this->page_title],
			'L_EXPLAIN'         => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'            => $user->lang['STYLE_NAME'],

			'ERROR_MSG'         => (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'              => $style_row['style_name'],
			'COPYRIGHT'         => $installcfg['copyright'] ?? '',
			'TEMPLATE_NAME'     => $style_row['template_dir'],
			'THEME_NAME'        => $style_row['theme_dir'],
			'IMAGESET_NAME'     => $style_row['imageset_dir'],
		]);
	}

	/**
	* Add new style preset.
	*/
	function add()
	{
		global $db, $user, $template;

		$error = [];
		$update = isset($_POST['update']);

		$style_row = [
			'style_name'      => utf8_normalize_nfc(request_var('name', '', true)),
			'template_dir'    => '',
			'theme_dir'       => '',
			'imageset_dir'    => '',
			'style_active'    => request_var('style_active', 1),
			'style_default'   => request_var('style_default', 0),
		];

		if ($update)
		{
			$style_row['template_dir'] = request_var('template_dir', $style_row['template_dir']);
			$style_row['theme_dir'] = request_var('theme_dir', $style_row['theme_dir']);
			$style_row['imageset_dir'] = request_var('imageset_dir', $style_row['imageset_dir']);

			$this->validate_style($style_row, $error);
		}

		if ($update && !sizeof($error))
		{
			$this->install_style($error, $style_row);

			if (!sizeof($error))
			{
				trigger_error($user->lang['STYLE_ADDED'] . adm_back_link($this->u_action));
			}
		}

		$this->page_title = 'ADD_STYLE';

		$template->assign_vars([
			'S_DETAILS'             => true,
			'S_ADD'                 => true,
			'S_ERROR_MSG'           => (sizeof($error) > 0),
			'S_STYLE'               => true,
			'S_STYLE_ACTIVE'        => $style_row['style_active'] ?? 0,
			'S_STYLE_DEFAULT'       => $style_row['style_default'] ?? 0,
			'S_TEMPLATE_OPTIONS'    => $this->component_options('template', $style_row['template_dir']),
			'S_THEME_OPTIONS'       => $this->component_options('theme', $style_row['theme_dir']),
			'S_IMAGESET_OPTIONS'    => $this->component_options('imageset', $style_row['imageset_dir']),

			'U_ACTION'              => $this->u_action . '&amp;action=add',
			'U_BACK'                => $this->u_action,

			'L_TITLE'               => $user->lang[$this->page_title],
			'L_EXPLAIN'             => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'                => $user->lang['STYLE_NAME'],

			'ERROR_MSG'             => (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'                  => $style_row['style_name'],
		]);
	}

	/**
	* Insert style preset.
	*/
	function install_style(&$error, $style_row)
	{
		global $config, $db, $cache;

		if (sizeof($error))
		{
			return false;
		}

		$db->sql_transaction('begin');

		$sql_ary = [
			'style_name'        => $style_row['style_name'],
			'style_active'      => (int) $style_row['style_active'],
			'template_dir'      => $style_row['template_dir'],
			'theme_dir'         => $style_row['theme_dir'],
			'imageset_dir'      => $style_row['imageset_dir'],
		];

		$sql = 'INSERT INTO ' . STYLES_TABLE . '
			' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$style_id = $db->sql_nextid();

		if ($style_row['style_default'])
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_style = {$style_id}
				WHERE user_style = " . $config['default_style'];
			$db->sql_query($sql);

			set_config('default_style', $style_id);
		}

		$db->sql_transaction('commit');

		$cache->destroy('sql', STYLES_TABLE);
		add_log('admin', 'LOG_STYLE_ADD', $style_row['style_name']);
	}

	/**
	* Validate style preset fields.
	*/
	function validate_style($style_row, &$error, $style_id = 0)
	{
		global $db, $user;

		if (!$style_row['style_name'])
		{
			$error[] = $user->lang['STYLE_ERR_STYLE_NAME'];
		}

		if (utf8_strlen($style_row['style_name']) > 30)
		{
			$error[] = $user->lang['STYLE_ERR_NAME_LONG'];
		}

		$sql = 'SELECT style_id
			FROM ' . STYLES_TABLE . "
			WHERE style_id <> {$style_id}
				AND style_name = '" . $db->sql_escape($style_row['style_name']) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$error[] = $user->lang['STYLE_ERR_NAME_EXIST'];
		}

		$component_dirs = [];
		foreach (['template', 'theme', 'imageset'] as $component)
		{
			$component_dirs[$component] = $this->available_component_dirs($component);
			if (!$style_row[$component . '_dir'] || !isset($component_dirs[$component][$style_row[$component . '_dir']]))
			{
				$error[] = $user->lang['STYLE_ERR_NO_IDS'];
			}
		}

		if (!empty($style_row['template_dir']) && isset($component_dirs['template'][$style_row['template_dir']]))
		{
			$cfg = $component_dirs['template'][$style_row['template_dir']];
			if (!empty($cfg['inherit_from']) && empty($component_dirs['template'][$cfg['inherit_from']]))
			{
				$error[] = sprintf($user->lang['TEMPLATE_ERR_REQUIRED_OR_INCOMPLETE'], $cfg['inherit_from']);
			}
		}
	}

	/**
	* Build select options for a style replacement.
	*/
	function style_options($selected = 0, $exclude = 0, $active_only = true)
	{
		global $db;

		$sql_where = $active_only ? 'WHERE style_active = 1' : '';

		$sql = 'SELECT style_id, style_name
			FROM ' . STYLES_TABLE . "
			{$sql_where}
			ORDER BY style_name ASC";
		$result = $db->sql_query($sql);

		$options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['style_id'] == $exclude)
			{
				continue;
			}

			$selected_attr = ($row['style_id'] == $selected) ? ' selected="selected"' : '';
			$options .= '<option value="' . $row['style_id'] . '"' . $selected_attr . '>' . $row['style_name'] . '</option>';
		}
		$db->sql_freeresult($result);

		return $options;
	}

	/**
	* Build select options for a style component directory.
	*/
	function component_options($component, $selected = '')
	{
		$options = '';
		foreach ($this->available_component_dirs($component) as $dir => $cfg)
		{
			$selected_attr = ($dir == $selected) ? ' selected="selected"' : '';
			$options .= '<option value="' . htmlspecialchars($dir) . '"' . $selected_attr . '>' . htmlspecialchars($dir) . '</option>';
		}

		return $options;
	}

	/**
	* Find style dirs with valid style.cfg files.
	*/
	function available_style_dirs()
	{
		global $cache;

		$styles = [];
		foreach ($this->style_dirs() as $dir)
		{
			$cfg = $cache->obtain_style_cfg($dir, 'style');
			if ($cfg && version_compare($cfg['version'] ?? '', PHPBBEX_VERSION, '=='))
			{
				$styles[$dir] = $cfg;
			}
		}

		return $styles;
	}

	/**
	* Find style component dirs with valid cfg files.
	*/
	function available_component_dirs($component)
	{
		global $cache;

		$components = [];
		foreach ($this->style_dirs() as $dir)
		{
			$cfg = $cache->obtain_style_cfg($dir, $component);
			if ($cfg && version_compare($cfg['version'] ?? '', PHPBBEX_VERSION, '=='))
			{
				$components[$dir] = $cfg;
			}
		}
		ksort($components);

		return $components;
	}

	/**
	* Find subdirectories in styles/.
	*/
	function style_dirs()
	{
		$dirs = [];
		$dp = @opendir(PHPBB_ROOT_PATH . 'styles');

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ($file[0] != '.' && is_dir(PHPBB_ROOT_PATH . 'styles/' . $file) && preg_match('#^[a-z0-9_-]{1,50}$#i', $file))
				{
					$dirs[] = $file;
				}
			}
			closedir($dp);
		}

		return $dirs;
	}
}
