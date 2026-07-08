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
		global $db, $user, $auth, $template, $cache, $config;

		// Hardcoded template bitfield to add for new templates
		$bitfield = new bitfield();
		$bitfield->set(0);
		$bitfield->set(1);
		$bitfield->set(2);
		$bitfield->set(3);
		$bitfield->set(4);
		$bitfield->set(8);
		$bitfield->set(9);
		$bitfield->set(11);
		$bitfield->set(12);
		define('TEMPLATE_BITFIELD', $bitfield->get_base64());
		unset($bitfield);

		$user->add_lang('acp/styles');

		$this->tpl_name = 'acp_styles';
		$this->page_title = 'ACP_CAT_STYLES';

		$action = request_var('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;
		$style_id = request_var('id', 0);

		// Execute overall actions
		switch ($action)
		{
			case 'delete':
				if ($style_id)
				{
					$this->remove($mode, $style_id);
					return;
				}
			break;

			case 'install':
				$this->install($mode);
				return;
			break;

			case 'add':
				$this->add($mode);
				return;
			break;

			case 'details':
				if ($style_id)
				{
					$this->details($mode, $style_id);
					return;
				}
			break;

		}

		switch ($mode)
		{
			case 'style':

				switch ($action)
				{
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

							// Set style to default for any member using deactivated style
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
							$s_hidden_fields = [
								'i'         => $id,
								'mode'      => $mode,
								'action'    => $action,
								'style_id'  => $style_id,
							];
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($s_hidden_fields));
						}
					break;
				}

				$this->frontend('style', ['details', 'activate_deactivate', 'delete', 'preview']);
			break;

			case 'template':

				switch ($action)
				{
					// Clear compiled template cache.
					case 'refresh':

						$sql = 'SELECT *
							FROM ' . STYLES_TEMPLATE_TABLE . "
							WHERE template_id = {$style_id}";
						$result = $db->sql_query($sql);
						$template_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$template_row)
						{
							trigger_error($user->lang['NO_TEMPLATE'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if (confirm_box(true))
						{
							$this->clear_template_cache($template_row);

							trigger_error($user->lang['TEMPLATE_CACHE_CLEARED'] . adm_back_link($this->u_action));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_TEMPLATE_CLEAR_CACHE'], build_hidden_fields([
								'i'         => $id,
								'mode'      => $mode,
								'action'    => $action,
								'id'        => $style_id
							]));
						}

					break;
				}

				$this->frontend('template', ['refresh', 'delete']);
			break;

			case 'theme':
				switch ($action)
				{
					case 'refresh':

						$sql = 'SELECT *
							FROM ' . STYLES_THEME_TABLE . "
							WHERE theme_id = {$style_id}";
						$result = $db->sql_query($sql);
						$theme_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$theme_row)
						{
							trigger_error($user->lang['NO_THEME'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if (confirm_box(true))
						{
							$sql = 'UPDATE ' . STYLES_THEME_TABLE . '
								SET theme_mtime = ' . time() . "
								WHERE theme_id = {$style_id}";
							$db->sql_query($sql);

							$cache->destroy('sql', STYLES_THEME_TABLE);

							add_log('admin', 'LOG_THEME_REFRESHED', $theme_row['theme_dir']);
							trigger_error($user->lang['THEME_REFRESHED'] . adm_back_link($this->u_action));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_THEME_REFRESH'], build_hidden_fields([
								'i'         => $id,
								'mode'      => $mode,
								'action'    => $action,
								'id'        => $style_id
							]));
						}
					break;
				}

				$this->frontend('theme', ['refresh', 'delete']);
			break;

			case 'imageset':

				switch ($action)
				{
					case 'refresh':

						$sql = 'SELECT *
							FROM ' . STYLES_IMAGESET_TABLE . "
							WHERE imageset_id = {$style_id}";
						$result = $db->sql_query($sql);
						$imageset_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$imageset_row)
						{
							trigger_error($user->lang['NO_IMAGESET'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if (confirm_box(true))
						{
							$cache->destroy("_style_{$imageset_row['imageset_dir']}_imageset_cfg");

							$sql = 'SELECT lang_dir
								FROM ' . LANG_TABLE;
							$result = $db->sql_query($sql);

							while ($row = $db->sql_fetchrow($result))
							{
								$cache->destroy("_style_{$imageset_row['imageset_dir']}_imageset_{$row['lang_dir']}");
								$cache->destroy("_style_{$imageset_row['imageset_dir']}_imageset_{$row['lang_dir']}_cfg");
							}
							$db->sql_freeresult($result);

							add_log('admin', 'LOG_IMAGESET_REFRESHED', $imageset_row['imageset_dir']);
							trigger_error($user->lang['IMAGESET_REFRESHED'] . adm_back_link($this->u_action));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_IMAGESET_REFRESH'], build_hidden_fields([
								'i'         => $id,
								'mode'      => $mode,
								'action'    => $action,
								'id'        => $style_id
							]));
						}
					break;
				}

				$this->frontend('imageset', ['refresh', 'delete']);
			break;
		}
	}

	/**
	* Build Frontend with supplied options
	*/
	function frontend($mode, $actions)
	{
		global $user, $template, $db, $config;

		$sql_from = '';
		$name_field = ($mode == 'style') ? 'style_name' : $mode . '_dir';
		$sql_sort = 'LOWER(' . $name_field . ')';
		$style_count = [];

		switch ($mode)
		{
			case 'style':
				$sql_from = STYLES_TABLE;
				$sql_sort = 'style_active DESC, ' . $sql_sort;

				$sql = 'SELECT user_style, COUNT(user_style) AS style_count
					FROM ' . USERS_TABLE . '
					GROUP BY user_style';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$style_count[$row['user_style']] = $row['style_count'];
				}
				$db->sql_freeresult($result);

			break;

			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGESET_TABLE;
			break;

			default:
				trigger_error($user->lang['NO_MODE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$l_prefix = strtoupper($mode);

		$this->page_title = 'ACP_' . $l_prefix . 'S';

		$template->assign_vars([
			'S_FRONTEND'        => true,
			'S_STYLE'           => ($mode == 'style'),

			'L_TITLE'           => $user->lang[$this->page_title],
			'L_EXPLAIN'         => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'            => $user->lang[$l_prefix . '_NAME'],
			'L_INSTALLED'       => $user->lang['INSTALLED_' . $l_prefix],
			'L_UNINSTALLED'     => $user->lang['UNINSTALLED_' . $l_prefix],
			'L_NO_UNINSTALLED'  => $user->lang['NO_UNINSTALLED_' . $l_prefix],
			'L_CREATE'          => ($mode == 'style') ? $user->lang['CREATE_STYLE'] : '',

			'U_ACTION'          => $this->u_action,
		]);

		$sql = "SELECT *
			FROM {$sql_from}
			ORDER BY {$sql_sort} ASC";
		$result = $db->sql_query($sql);

		$installed = [];

		$basis_options = '<option class="sep" value="">' . $user->lang['OPTIONAL_BASIS'] . '</option>';
		while ($row = $db->sql_fetchrow($result))
		{
			$installed[] = $row[$name_field];
			$basis_options .= '<option value="' . $row[$mode . '_id'] . '">' . $row[$name_field] . '</option>';

			$stylevis = ($mode == 'style' && !$row['style_active']) ? 'activate' : 'deactivate';

			$s_actions = [];
			foreach ($actions as $option)
			{
				switch ($option)
				{
					case 'activate_deactivate':
						$s_actions[] = '<a href="' . $this->u_action . '&amp;action=' . $stylevis . '&amp;id=' . $row[$mode . '_id'] . '">' . $user->lang['STYLE_' . strtoupper($stylevis)] . '</a>';
					break;

					case 'preview':
						$s_actions[] = '<a href="' . append_sid(PHPBB_ROOT_PATH . 'index.php', "{$mode}=" . $row[$mode . '_id']) . '">' . $user->lang['PREVIEW'] . '</a>';
					break;

					default:
						$s_actions[] = '<a href="' . $this->u_action . "&amp;action={$option}&amp;id=" . $row[$mode . '_id'] . '">' . $user->lang[strtoupper($option)] . '</a>';
					break;
				}
			}

			$template->assign_block_vars('installed', [
				'S_DEFAULT_STYLE'       => ($mode == 'style' && $row['style_id'] == $config['default_style']),
				'S_ACTIONS'             => implode(' | ', $s_actions),

				'NAME'                  => $row[$name_field],
				'STYLE_COUNT'           => ($mode == 'style' && isset($style_count[$row['style_id']])) ? $style_count[$row['style_id']] : 0,

				'S_INACTIVE'            => ($mode == 'style' && !$row['style_active']),
			]);
		}
		$db->sql_freeresult($result);

		// Grab uninstalled items
		$new_ary = $cfg = [];

		$dp = @opendir(PHPBB_ROOT_PATH . 'styles');

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ($file[0] == '.' || !is_dir(PHPBB_ROOT_PATH . 'styles/' . $file))
				{
					continue;
				}

				$subpath = ($mode != 'style') ? "{$mode}/" : '';
				if (file_exists(PHPBB_ROOT_PATH . "styles/{$file}/{$subpath}{$mode}.cfg"))
				{
					if ($cfg = file(PHPBB_ROOT_PATH . "styles/{$file}/{$subpath}{$mode}.cfg"))
					{
						$items = parse_cfg_file('', $cfg);
						$name = (isset($items['name'])) ? trim($items['name']) : false;
						$display_name = ($mode == 'style') ? $name : $file;

						if (($mode != 'style' || $name) && !in_array($display_name, $installed))
						{
							// The array key is used for sorting later on.
							// $file is appended because $name doesn't have to be unique.
							$new_ary[$display_name . $file] = [
								'path'      => $file,
								'name'      => $display_name,
								'copyright' => $items['copyright'] ?? '',
							];
						}
					}
				}
			}
			closedir($dp);
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
		unset($new_ary);

		$template->assign_vars([
			'S_BASIS_OPTIONS'       => $basis_options,
		]);

	}

	/**
	* Remove style/template/theme/imageset
	*/
	function remove($mode, $style_id)
	{
		global $db, $template, $user, $cache, $config;

		$new_id = request_var('new_id', 0);
		$update = isset($_POST['update']);
		$sql_where = '';

		switch ($mode)
		{
			case 'style':
				$sql_from = STYLES_TABLE;
				$sql_select = 'style_id, style_name, template_id, theme_id, imageset_id';
				$sql_where = 'AND style_active = 1';
			break;

			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
				$sql_select = 'template_id, template_dir';
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
				$sql_select = 'theme_id, theme_dir';
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGESET_TABLE;
				$sql_select = 'imageset_id, imageset_dir';
			break;
		}

		if ($mode === 'template' && ($conflicts = $this->check_inheritance($mode, $style_id)))
		{
			$l_type = strtoupper($mode);
			$msg = $user->lang[$l_type . '_DELETE_DEPENDENT'];
			foreach ($conflicts as $id => $values)
			{
				$msg .= '<br />' . $values['template_dir'];
			}

			trigger_error($msg . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$l_prefix = strtoupper($mode);

		$sql = "SELECT {$sql_select}
			FROM {$sql_from}
			WHERE {$mode}_id = {$style_id}";
		$result = $db->sql_query($sql);
		$style_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$style_row)
		{
			trigger_error($user->lang['NO_' . $l_prefix] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$s_only_component = $this->display_component_options($mode, $style_row[$mode . '_id'], $style_row);

		if ($s_only_component)
		{
			trigger_error($user->lang['ONLY_' . $l_prefix] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($update)
		{
			if ($mode == 'style')
			{
				$sql = "DELETE FROM {$sql_from}
					WHERE {$mode}_id = {$style_id}";
				$db->sql_query($sql);

				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_style = {$new_id}
					WHERE user_style = {$style_id}";
				$db->sql_query($sql);

				if ($style_id == $config['default_style'])
				{
					set_config('default_style', $new_id);
				}

				// Remove the components
				$components = ['template', 'theme', 'imageset'];
				foreach ($components as $component)
				{
					$new_id = request_var('new_' . $component . '_id', 0);
					$component_id = $style_row[$component . '_id'];
					$this->remove_component($component, $component_id, $new_id, $style_id);
				}
			}
			else
			{
				$this->remove_component($mode, $style_id, $new_id);
			}

			$cache->destroy('sql', STYLES_TABLE);

			add_log('admin', 'LOG_' . $l_prefix . '_DELETE', $style_row[($mode == 'style') ? 'style_name' : $mode . '_dir']);
			$message = ($mode != 'style') ? $l_prefix . '_DELETED_FS' : $l_prefix . '_DELETED';
			trigger_error($user->lang[$message] . adm_back_link($this->u_action));
		}

		$this->page_title = 'DELETE_' . $l_prefix;

		$template->assign_vars([
			'S_DELETE'          => true,

			'L_TITLE'           => $user->lang[$this->page_title],
			'L_EXPLAIN'         => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'            => $user->lang[$l_prefix . '_NAME'],
			'L_REPLACE'         => $user->lang['REPLACE_' . $l_prefix],
			'L_REPLACE_EXPLAIN' => $user->lang['REPLACE_' . $l_prefix . '_EXPLAIN'],

			'U_ACTION'      => $this->u_action . "&amp;action=delete&amp;id={$style_id}",
			'U_BACK'        => $this->u_action,

			'NAME'          => $style_row[($mode == 'style') ? 'style_name' : $mode . '_dir'],
		]);

		if ($mode == 'style')
		{
			$template->assign_vars([
				'S_DELETE_STYLE'        => true,
			]);
		}
	}

	/**
	* Remove template/theme/imageset entry from the database
	*/
	function remove_component($component, $component_id, $new_id, $style_id = false)
	{
		global $db;

		if (($new_id == 0) || ($component === 'template' && ($conflicts = $this->check_inheritance($component, $component_id))))
		{
			// We can not delete the template, as the user wants to keep the component or an other template is inheriting from this one.
			return;
		}

		$component_in_use = [];
		if ($component != 'style')
		{
			$component_in_use = $this->component_in_use($component, $component_id, $style_id);
		}

		if (($new_id == -1) && !empty($component_in_use))
		{
			// We can not delete the component, as it is still in use
			return;
		}

		switch ($component)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGESET_TABLE;;
			break;
		}

		$sql = "DELETE FROM {$sql_from}
			WHERE {$component}_id = {$component_id}";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . STYLES_TABLE . "
			SET {$component}_id = {$new_id}
			WHERE {$component}_id = {$component_id}";
		$db->sql_query($sql);
	}

	/**
	* Display the options which can be used to replace a style/template/theme/imageset
	*
	* @return boolean Returns true if the component is the only component and can not be deleted.
	*/
	function display_component_options($component, $component_id, $style_row = false, $style_id = false)
	{
		global $db, $template, $user;

		$is_only_component = true;
		$component_in_use = [];
		if ($component != 'style')
		{
			$component_in_use = $this->component_in_use($component, $component_id, $style_id);
		}

		$sql_where = '';
		switch ($component)
		{
			case 'style':
				$sql_from = STYLES_TABLE;
				$sql_where = 'WHERE style_active = 1';
			break;

			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
				$sql_where = 'WHERE template_inherit_id <> ' . $component_id;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGESET_TABLE;
			break;
		}

		$s_options = '';
		if (($component != 'style') && empty($component_in_use))
		{
			// If it is not in use, there must be another component
			$is_only_component = false;

			$name_field = ($component == 'style') ? 'style_name' : $component . '_dir';

			$sql = "SELECT {$component}_id, {$name_field}
				FROM {$sql_from}
				WHERE {$component}_id = {$component_id}";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$s_options .= '<option value="-1" selected="selected">' . $user->lang['DELETE_' . strtoupper($component)] . '</option>';
			$s_options .= '<option value="0">' . sprintf($user->lang['KEEP_' . strtoupper($component)], $row[$name_field]) . '</option>';
		}
		else
		{
			$name_field = ($component == 'style') ? 'style_name' : $component . '_dir';

			$sql = "SELECT {$component}_id, {$name_field}
				FROM {$sql_from}
				{$sql_where}
				ORDER BY {$name_field} ASC";
			$result = $db->sql_query($sql);

			$s_keep_option = $s_options = '';
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row[$component . '_id'] != $component_id)
				{
					$is_only_component = false;
					$s_options .= '<option value="' . $row[$component . '_id'] . '">' . sprintf($user->lang['REPLACE_WITH_OPTION'], $row[$name_field]) . '</option>';
				}
				else if ($component != 'style')
				{
					$s_keep_option = '<option value="0" selected="selected">' . sprintf($user->lang['KEEP_' . strtoupper($component)], $row[$name_field]) . '</option>';
				}
			}
			$db->sql_freeresult($result);
			$s_options = $s_keep_option . $s_options;
		}

		if (!$style_row)
		{
			$template->assign_var('S_REPLACE_' . strtoupper($component) . '_OPTIONS', $s_options);
		}
		else
		{
			$template->assign_var('S_REPLACE_OPTIONS', $s_options);
			if ($component == 'style')
			{
				$components = ['template', 'theme', 'imageset'];
				foreach ($components as $component)
				{
					$this->display_component_options($component, $style_row[$component . '_id'], false, $component_id, true);
				}
			}
		}

		return $is_only_component;
	}

	/**
	* Check whether the component is still used by another style or component
	*/
	function component_in_use($component, $component_id, $style_id = false)
	{
		global $db;

		$component_in_use = [];

		if ($style_id)
		{
			$sql = 'SELECT style_id, style_name
				FROM ' . STYLES_TABLE . "
				WHERE {$component}_id = {$component_id}
					AND style_id <> {$style_id}
				ORDER BY style_name ASC";
		}
		else
		{
			$sql = 'SELECT style_id, style_name
				FROM ' . STYLES_TABLE . "
				WHERE {$component}_id = {$component_id}
				ORDER BY style_name ASC";
		}
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$component_in_use[] = $row['style_name'];
		}
		$db->sql_freeresult($result);

		if ($component === 'template' && ($conflicts = $this->check_inheritance($component, $component_id)))
		{
			foreach ($conflicts as $temp_id => $conflict_data)
			{
				$component_in_use[] = $conflict_data['template_dir'];
			}
		}

		return $component_in_use;
	}

	/**
	* Display details
	*/
	function details($mode, $style_id)
	{
		global $template, $db, $config, $user, $cache;

		$update = isset($_POST['update']);

		if ($mode != 'style')
		{
			trigger_error($user->lang['NO_MODE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$error = [];
		$element_ary = ['template' => STYLES_TEMPLATE_TABLE, 'theme' => STYLES_THEME_TABLE, 'imageset' => STYLES_IMAGESET_TABLE];

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
			$name = utf8_normalize_nfc(request_var('name', '', true));

			$template_id = request_var('template_id', 0);
			$theme_id = request_var('theme_id', 0);
			$imageset_id = request_var('imageset_id', 0);

			$style_active = request_var('style_active', 0);
			$style_default = request_var('style_default', 0);
			// If the admin selected the style to be the default style, but forgot to activate it... we will do it for him
			if ($style_default)
			{
				$style_active = 1;
			}

			$sql = 'SELECT style_id, style_name
				FROM ' . STYLES_TABLE . "
				WHERE style_id <> {$style_id}
					AND LOWER(style_name) = '" . $db->sql_escape(strtolower($name)) . "'";
			$result = $db->sql_query($sql);
			$conflict = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$template_id || !$theme_id || !$imageset_id)
			{
				$error[] = $user->lang['STYLE_ERR_NO_IDS'];
			}

			if ($style_row['style_active'] && !$style_active && $config['default_style'] == $style_id)
			{
				$error[] = $user->lang['DEACTIVATE_DEFAULT'];
			}

			if (!$name || $conflict)
			{
				$error[] = $user->lang['STYLE_ERR_STYLE_NAME'];
			}

			if (!sizeof($error))
			{
				// Check length settings
				if (utf8_strlen($name) > 30)
				{
					$error[] = $user->lang['STYLE_ERR_NAME_LONG'];
				}
			}
		}

		if ($update && sizeof($error))
		{
			$style_row = array_merge($style_row, [
				'template_id'           => $template_id,
				'theme_id'              => $theme_id,
				'imageset_id'           => $imageset_id,
				'style_active'          => $style_active,
				'style_name'            => $name,
			]);
		}

		// User has submitted form and no errors have occurred
		if ($update && !sizeof($error))
		{
			$sql_ary = [
				'style_name'        => $name,
				'template_id'       => (int) $template_id,
				'theme_id'          => (int) $theme_id,
				'imageset_id'       => (int) $imageset_id,
				'style_active'      => (int) $style_active,
			];

			$sql = 'UPDATE ' . STYLES_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE style_id = {$style_id}";
			$db->sql_query($sql);

			// Making this the default style?
			if ($style_default)
			{
				set_config('default_style', $style_id);
			}

			$cache->destroy('sql', STYLES_TABLE);

			add_log('admin', 'LOG_STYLE_EDIT_DETAILS', $name);
			if (sizeof($error))
			{
				trigger_error(implode('<br />', $error) . adm_back_link($this->u_action), E_USER_WARNING);
			}
			else
			{
				trigger_error($user->lang['STYLE_DETAILS_UPDATED'] . adm_back_link($this->u_action));
			}
		}

		foreach ($element_ary as $element => $table)
		{
			$sql = "SELECT {$element}_id, {$element}_dir
				FROM {$table}
				ORDER BY {$element}_id ASC";
			$result = $db->sql_query($sql);

			${$element . '_options'} = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$selected = ($row[$element . '_id'] == $style_row[$element . '_id']) ? ' selected="selected"' : '';
				${$element . '_options'} .= '<option value="' . $row[$element . '_id'] . '"' . $selected . '>' . $row[$element . '_dir'] . '</option>';
			}
			$db->sql_freeresult($result);
		}

		// Get optional copyright information from the related cfg file.
		$cfg_file = PHPBB_ROOT_PATH . "styles/{$style_row['style_name']}/style.cfg";
		$copyright = (file_exists($cfg_file) ? (parse_cfg_file($cfg_file)['copyright'] ?? '') : '');

		$this->page_title = 'EDIT_DETAILS_STYLE';

		$template->assign_vars([
			'S_DETAILS'             => true,
			'S_ERROR_MSG'           => (sizeof($error) > 0),
			'S_STYLE'               => true,
			'S_TEMPLATE'            => false,
			'S_THEME'               => false,
			'S_IMAGESET'            => false,
			'S_STYLE_ACTIVE'        => $style_row['style_active'] ?? 0,
			'S_STYLE_DEFAULT'       => $style_row['style_default'] ?? 0,
			'S_SUPERTEMPLATE'       => 0,

			'S_TEMPLATE_OPTIONS'    => $template_options,
			'S_THEME_OPTIONS'       => $theme_options,
			'S_IMAGESET_OPTIONS'    => $imageset_options,

			'U_ACTION'      => $this->u_action . '&amp;action=details&amp;id=' . $style_id,
			'U_BACK'        => $this->u_action,

			'L_TITLE'               => $user->lang[$this->page_title],
			'L_EXPLAIN'             => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'                => $user->lang['STYLE_NAME'],

			'ERROR_MSG'     => (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'          => $style_row['style_name'],
			'COPYRIGHT'     => $copyright,
		]);
	}

	/**
	* Returns an array containing all template filenames for one template that are currently cached.
	*
	* @param string $template_dir contains the name of the template's folder in /styles/
	*
	* @return array of filenames that exist in /styles/$template_dir/template/ (without extension!)
	*/
	function template_cache_filelist($template_dir)
	{
		global $user;

		$cache_prefix = 'tpl_' . str_replace('_', '-', $template_dir);

		if (!($dp = @opendir(PHPBB_ROOT_PATH . 'cache')))
		{
			trigger_error($user->lang['TEMPLATE_ERR_CACHE_READ'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$file_ary = [];
		while ($file = readdir($dp))
		{
			if ($file[0] == '.')
			{
				continue;
			}

			if (is_file(PHPBB_ROOT_PATH . 'cache/' . $file) && (strpos($file, $cache_prefix) === 0))
			{
				$file_ary[] = str_replace('.', '/', preg_replace('#^' . preg_quote($cache_prefix, '#') . '_(.*?)\.html\.php' . '$#i', '\1', $file));
			}
		}
		closedir($dp);

		return $file_ary;
	}

	/**
	* Destroys cached versions of template files
	*
	* @param array $template_row contains the template's row in the STYLES_TEMPLATE_TABLE database table
	* @param mixed $file_ary is optional and may contain an array of template file names which should be refreshed in the cache.
	*   The file names should be the original template file names and not the cache file names.
	*/
	function clear_template_cache($template_row, $file_ary = false)
	{
		global $user;

		$cache_prefix = 'tpl_' . str_replace('_', '-', $template_row['template_dir']);

		if (!$file_ary || !is_array($file_ary))
		{
			$file_ary = $this->template_cache_filelist($template_row['template_dir']);
			$log_file_list = $user->lang['ALL_FILES'];
		}
		else
		{
			$log_file_list = implode(', ', $file_ary);
		}

		foreach ($file_ary as $file)
		{
			$file = str_replace('/', '.', $file);

			$file = PHPBB_ROOT_PATH . "cache/{$cache_prefix}_{$file}.html.php";
			if (file_exists($file) && is_file($file))
			{
				@unlink($file);
			}
		}
		unset($file_ary);

		add_log('admin', 'LOG_TEMPLATE_CACHE_CLEARED', $template_row['template_dir'], $log_file_list);
	}

	/**
	* Install Style/Template/Theme/Imageset
	*/
	function install($mode)
	{
		global $config, $db, $cache, $user, $template;

		$l_type = strtoupper($mode);

		$error = $installcfg = $style_row = [];
		$root_path = $cfg_file = '';
		$element_ary = ['template' => STYLES_TEMPLATE_TABLE, 'theme' => STYLES_THEME_TABLE, 'imageset' => STYLES_IMAGESET_TABLE];

		$install_path = request_var('path', '');
		$update = isset($_POST['update']);

		// Installing, obtain cfg file contents
		if ($install_path)
		{
			$root_path = PHPBB_ROOT_PATH . 'styles/' . $install_path . '/';
			$cfg_file = ($mode == 'style') ? "{$root_path}{$mode}.cfg" : "{$root_path}{$mode}/{$mode}.cfg";

			if (!file_exists($cfg_file))
			{
				$error[] = $user->lang[$l_type . '_ERR_NOT_' . $l_type];
			}
			else
			{
				$installcfg = parse_cfg_file($cfg_file);
			}
		}

		// Installing
		if (sizeof($installcfg))
		{
			$name       = $installcfg['name'] ?? '';
			$copyright  = $installcfg['copyright'] ?? '';
			$version    = $installcfg['version'] ?? '';

			if (!version_compare($version, PHPBBEX_VERSION, '=='))
			{
				$error[] = sprintf($user->lang[$l_type . '_ERR_VERSION'], PHPBBEX_VERSION, $version);
			}

			$style_row = [
				$mode . '_id'           => 0,
				$mode . '_dir'          => $install_path,
			];

			switch ($mode)
			{
				case 'style':

					$style_row = [
						'style_id'          => 0,
						'style_name'        => $installcfg['name'] ?? '',
					];

					$reqd_template = $installcfg['required_template'] ?? false;
					$reqd_theme = $installcfg['required_theme'] ?? false;
					$reqd_imageset = $installcfg['required_imageset'] ?? false;

					// Check to see if each element is already installed, if it is grab the id
					foreach ($element_ary as $element => $table)
					{
						$style_row = array_merge($style_row, [
							$element . '_id'            => 0,
							$element . '_dir'           => ${'reqd_' . $element} ?: $install_path,
						]);

						$this->test_installed($element, $error, (${'reqd_' . $element}) ? PHPBB_ROOT_PATH . 'styles/' . ${'reqd_' . $element} . '/' : $root_path, ${'reqd_' . $element}, $style_row[$element . '_id'], $style_row[$element . '_dir']);

						// Merge other information to installcfg... if present
						$cfg_file = PHPBB_ROOT_PATH . 'styles/' . $install_path . '/' . $element . '/' . $element . '.cfg';

						if (file_exists($cfg_file))
						{
							$cfg_contents = parse_cfg_file($cfg_file);

							// Merge only specific things. We may need them later.
							foreach (['inherit_from', 'parse_css_file'] as $key)
							{
								if (!empty($cfg_contents[$key]) && !isset($installcfg[$key]))
								{
									$installcfg[$key] = $cfg_contents[$key];
								}
							}
						}
					}

				break;

				case 'template':
					$this->test_installed('template', $error, $root_path, false, $style_row['template_id'], $style_row['template_dir']);
				break;

				case 'theme':
					$this->test_installed('theme', $error, $root_path, false, $style_row['theme_id'], $style_row['theme_dir']);
				break;

				case 'imageset':
					$this->test_installed('imageset', $error, $root_path, false, $style_row['imageset_id'], $style_row['imageset_dir']);
				break;
			}
		}
		else
		{
			trigger_error($user->lang['NO_' . $l_type] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$style_row['style_active'] = request_var('style_active', 1);
		$style_row['style_default'] = request_var('style_default', 0);

		// User has submitted form and no errors have occurred
		if ($update && !sizeof($error))
		{
			if ($mode == 'style')
			{
				foreach ($element_ary as $element => $table)
				{
					${$element . '_root_path'} = (${'reqd_' . $element}) ? PHPBB_ROOT_PATH . 'styles/' . ${'reqd_' . $element} . '/' : false;
					${$element . '_dir'} = (${'reqd_' . $element}) ?: false;
				}
				$this->install_style($error, 'install', $root_path, $style_row['style_id'], $style_row['style_name'], $install_path, $style_row['style_active'], $style_row['style_default'], $style_row, $template_root_path, $template_dir, $theme_root_path, $theme_dir, $imageset_root_path, $imageset_dir);
			}
			else
			{
				$this->install_element($mode, $error, 'install', $root_path, $style_row[$mode . '_id'], $style_row[$mode . '_dir'], $install_path);
			}

			if (!sizeof($error))
			{
				$cache->destroy('sql', STYLES_TABLE);

				trigger_error($user->lang[$l_type . '_ADDED'] . adm_back_link($this->u_action));
			}
		}

		$this->page_title = 'INSTALL_' . $l_type;

		$template->assign_vars([
			'S_DETAILS'         => true,
			'S_INSTALL'         => true,
			'S_ERROR_MSG'       => (sizeof($error) > 0),
			'S_LOCATION'        => empty($installcfg['inherit_from']),
			'S_STYLE'           => ($mode == 'style'),
			'S_TEMPLATE'        => ($mode == 'template'),
			'S_SUPERTEMPLATE'   => $installcfg['inherit_from'] ?? '',
			'S_THEME'           => ($mode == 'theme'),

			'S_STYLE_ACTIVE'        => $style_row['style_active'] ?? 0,
			'S_STYLE_DEFAULT'       => $style_row['style_default'] ?? 0,

			'U_ACTION'          => $this->u_action . "&amp;action=install&amp;path=" . urlencode($install_path),
			'U_BACK'            => $this->u_action,

			'L_TITLE'               => $user->lang[$this->page_title],
			'L_EXPLAIN'             => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'                => $user->lang[$l_type . '_NAME'],

			'ERROR_MSG'         => (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'              => ($mode == 'style') ? $style_row['style_name'] : $style_row[$mode . '_dir'],
			'COPYRIGHT'         => $copyright,
			'TEMPLATE_NAME'     => ($mode == 'style') ? $style_row['template_dir'] : '',
			'THEME_NAME'        => ($mode == 'style') ? $style_row['theme_dir'] : '',
			'IMAGESET_NAME'     => ($mode == 'style') ? $style_row['imageset_dir'] : '',
		]);
	}

	/**
	* Add new style
	*/
	function add($mode)
	{
		global $config, $db, $cache, $user, $template;

		$l_type = strtoupper($mode);
		$element_ary = ['template' => STYLES_TEMPLATE_TABLE, 'theme' => STYLES_THEME_TABLE, 'imageset' => STYLES_IMAGESET_TABLE];
		$error = [];

		if ($mode != 'style')
		{
			trigger_error($user->lang['NO_MODE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$style_row = [
			$mode . '_name'         => utf8_normalize_nfc(request_var('name', '', true)),
			'template_id'           => 0,
			'theme_id'              => 0,
			'imageset_id'           => 0,
			'style_active'          => request_var('style_active', 1),
			'style_default'         => request_var('style_default', 0),
		];

		$basis = request_var('basis', 0);
		$update = isset($_POST['update']);

		if ($basis)
		{
			switch ($mode)
			{
				case 'style':
					$sql_select = 'template_id, theme_id, imageset_id';
					$sql_from = STYLES_TABLE;
				break;

				case 'template':
					$sql_select = 'template_id';
					$sql_from = STYLES_TEMPLATE_TABLE;
				break;

				case 'theme':
					$sql_select = 'theme_id';
					$sql_from = STYLES_THEME_TABLE;
				break;

				case 'imageset':
					$sql_select = 'imageset_id';
					$sql_from = STYLES_IMAGESET_TABLE;
				break;
			}

			$sql = "SELECT {$sql_select}
				FROM {$sql_from}
				WHERE {$mode}_id = {$basis}";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				$error[] = $user->lang['NO_' . $l_type];
			}

			if (!sizeof($error))
			{
				$style_row['template_id']   = $row['template_id'] ?? $style_row['template_id'];
				$style_row['theme_id']      = $row['theme_id'] ?? $style_row['theme_id'];
				$style_row['imageset_id']   = $row['imageset_id'] ?? $style_row['imageset_id'];
			}
		}

		if ($update)
		{
			$style_row['template_id'] = request_var('template_id', $style_row['template_id']);
			$style_row['theme_id'] = request_var('theme_id', $style_row['theme_id']);
			$style_row['imageset_id'] = request_var('imageset_id', $style_row['imageset_id']);

			if ($mode == 'style' && (!$style_row['template_id'] || !$style_row['theme_id'] || !$style_row['imageset_id']))
			{
				$error[] = $user->lang['STYLE_ERR_NO_IDS'];
			}
		}

		// User has submitted form and no errors have occurred
		if ($update && !sizeof($error))
		{
			if ($mode == 'style')
			{
				$style_row['style_id'] = 0;

				$this->install_style($error, 'add', '', $style_row['style_id'], $style_row['style_name'], '', $style_row['style_active'], $style_row['style_default'], $style_row);
			}

			if (!sizeof($error))
			{
				$cache->destroy('sql', STYLES_TABLE);

				trigger_error($user->lang[$l_type . '_ADDED'] . adm_back_link($this->u_action));
			}
		}

		if ($mode == 'style')
		{
			foreach ($element_ary as $element => $table)
			{
				$sql = "SELECT {$element}_id, {$element}_dir
					FROM {$table}
					ORDER BY {$element}_id ASC";
				$result = $db->sql_query($sql);

				${$element . '_options'} = '';
				while ($row = $db->sql_fetchrow($result))
				{
					$selected = ($row[$element . '_id'] == $style_row[$element . '_id']) ? ' selected="selected"' : '';
					${$element . '_options'} .= '<option value="' . $row[$element . '_id'] . '"' . $selected . '>' . $row[$element . '_dir'] . '</option>';
				}
				$db->sql_freeresult($result);
			}
		}

		$this->page_title = 'ADD_' . $l_type;

		$template->assign_vars([
			'S_DETAILS'         => true,
			'S_ADD'             => true,
			'S_ERROR_MSG'       => (sizeof($error) > 0),
			'S_STYLE'           => ($mode == 'style'),
			'S_TEMPLATE'        => ($mode == 'template'),
			'S_THEME'           => ($mode == 'theme'),
			'S_BASIS'           => (bool) $basis,

			'S_STYLE_ACTIVE'        => $style_row['style_active'] ?? 0,
			'S_STYLE_DEFAULT'       => $style_row['style_default'] ?? 0,
			'S_TEMPLATE_OPTIONS'    => ($mode == 'style') ? $template_options : '',
			'S_THEME_OPTIONS'       => ($mode == 'style') ? $theme_options : '',
			'S_IMAGESET_OPTIONS'    => ($mode == 'style') ? $imageset_options : '',

			'U_ACTION'          => $this->u_action . '&amp;action=add&amp;basis=' . $basis,
			'U_BACK'            => $this->u_action,

			'L_TITLE'               => $user->lang[$this->page_title],
			'L_EXPLAIN'             => $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'                => $user->lang[$l_type . '_NAME'],

			'ERROR_MSG'         => (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'              => $style_row[$mode . '_name'],
		]);

	}

	/**
	* Is this element installed? If not, grab its cfg details
	*/
	function test_installed($element, &$error, $root_path, $reqd_name, &$id, &$dir)
	{
		global $db, $user;

		switch ($element)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGESET_TABLE;
			break;
		}

		$l_element = strtoupper($element);

		$chk_dir = ($reqd_name !== false) ? $reqd_name : $dir;

		$sql = "SELECT {$element}_id, {$element}_dir
			FROM {$sql_from}
			WHERE {$element}_dir = '" . $db->sql_escape($chk_dir) . "'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$dir = $row[$element . '_dir'];
			$id = $row[$element . '_id'];
		}
		else
		{
			if (!($cfg = @file("{$root_path}{$element}/{$element}.cfg")))
			{
				$error[] = sprintf($user->lang['REQUIRES_' . $l_element], $reqd_name);
				return false;
			}

			$cfg = parse_cfg_file("{$root_path}{$element}/{$element}.cfg", $cfg);

			$dir = $chk_dir;
			$id = 0;

			unset($cfg);
		}
		$db->sql_freeresult($result);
	}

	/**
	* Install/Add style
	*/
	function install_style(&$error, $action, $root_path, &$id, $name, $path, $active, $default, &$style_row, $template_root_path = false, $template_dir = false, $theme_root_path = false, $theme_dir = false, $imageset_root_path = false, $imageset_dir = false)
	{
		global $config, $db, $user;

		$element_ary = ['template', 'theme', 'imageset'];

		if (!$name)
		{
			$error[] = $user->lang['STYLE_ERR_STYLE_NAME'];
		}

		// Check length settings
		if (utf8_strlen($name) > 30)
		{
			$error[] = $user->lang['STYLE_ERR_NAME_LONG'];
		}

		// Check if the name already exist
		$sql = 'SELECT style_id
			FROM ' . STYLES_TABLE . "
			WHERE style_name = '" . $db->sql_escape($name) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$error[] = $user->lang['STYLE_ERR_NAME_EXIST'];
		}

		if (sizeof($error))
		{
			return false;
		}

		foreach ($element_ary as $element)
		{
			// Zero id value ... need to install element ... run usual checks
			// and do the install if necessary
			if (!$style_row[$element . '_id'])
			{
				$this->install_element($element, $error, $action, (${$element . '_root_path'}) ?: $root_path, $style_row[$element . '_id'], $style_row[$element . '_dir'], (${$element . '_dir'}) ?: $path);
			}
		}

		if (!$style_row['template_id'] || !$style_row['theme_id'] || !$style_row['imageset_id'])
		{
			$error[] = $user->lang['STYLE_ERR_NO_IDS'];
		}

		if (sizeof($error))
		{
			return false;
		}

		$db->sql_transaction('begin');

		$sql_ary = [
			'style_name'        => $name,
			'style_active'      => (int) $active,
			'template_id'       => (int) $style_row['template_id'],
			'theme_id'          => (int) $style_row['theme_id'],
			'imageset_id'       => (int) $style_row['imageset_id'],
		];

		$sql = 'INSERT INTO ' . STYLES_TABLE . '
			' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$id = $db->sql_nextid();

		if ($default)
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_style = {$id}
				WHERE user_style = " . $config['default_style'];
			$db->sql_query($sql);

			set_config('default_style', $id);
		}

		$db->sql_transaction('commit');

		add_log('admin', 'LOG_STYLE_ADD', $name);
	}

	/**
	* Install/add an element, doing various checks as we go
	*/
	function install_element($mode, &$error, $action, $root_path, &$id, $dir, $path)
	{
		global $db, $user;

		// we parse the cfg here (again)
		$cfg_data = parse_cfg_file("{$root_path}{$mode}/{$mode}.cfg");

		switch ($mode)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGESET_TABLE;
			break;
		}

		$l_type = strtoupper($mode);

		if (!version_compare($cfg_data['version'] ?? '', PHPBBEX_VERSION, '=='))
		{
			$error[] = sprintf($user->lang[$l_type . '_ERR_VERSION'], PHPBBEX_VERSION, $cfg_data['version'] ?? '');
		}

		if (!$dir)
		{
			$error[] = $user->lang[$l_type . '_ERR_STYLE_NAME'];
		}

		// Check length settings
		if (utf8_strlen($dir) > 100)
		{
			$error[] = $user->lang[$l_type . '_ERR_NAME_LONG'];
		}

		// Check if the directory already exists
		$sql = "SELECT {$mode}_id
			FROM {$sql_from}
			WHERE {$mode}_dir = '" . $db->sql_escape($dir) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			// If it exist, we just use the style on installation
			if ($action == 'install')
			{
				$id = $row[$mode . '_id'];
				return false;
			}

			$error[] = $user->lang[$l_type . '_ERR_NAME_EXIST'];
		}

		if (isset($cfg_data['inherit_from']) && $cfg_data['inherit_from'])
		{
			if ($mode === 'template')
			{
				$select_bf = ', bbcode_bitfield';
			}
			else
			{
				$select_bf = '';
			}

			$sql = "SELECT {$mode}_id, {$mode}_dir{$select_bf}
				FROM {$sql_from}
				WHERE {$mode}_dir = '" . $db->sql_escape($cfg_data['inherit_from']) . "'
					AND {$mode}_inherit_id = 0";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$row)
			{
				$error[] = sprintf($user->lang[$l_type . '_ERR_REQUIRED_OR_INCOMPLETE'], $cfg_data['inherit_from']);
			}
			else
			{
				$inherit_id = $row["{$mode}_id"];
				$inherit_dir = $row["{$mode}_dir"];
				$inherit_bf = ($mode === 'template') ? $row["bbcode_bitfield"] : false;
			}
		}
		else
		{
			$inherit_id = 0;
			$inherit_dir = '';
			$inherit_bf = false;
		}

		if (sizeof($error))
		{
			return false;
		}

		$sql_ary = [
			$mode . '_dir'          => $path,
		];

		switch ($mode)
		{
			case 'template':
				// We check if the template author defined a different bitfield
				if (!empty($cfg_data['template_bitfield']))
				{
					$sql_ary['bbcode_bitfield'] = $cfg_data['template_bitfield'];
				}
				else if ($inherit_bf)
				{
					$sql_ary['bbcode_bitfield'] = $inherit_bf;
				}
				else
				{
					$sql_ary['bbcode_bitfield'] = TEMPLATE_BITFIELD;
				}

				if (isset($cfg_data['inherit_from']) && $cfg_data['inherit_from'])
				{
					$sql_ary += [
						'template_inherit_id'   => $inherit_id,
						'template_inherit_dir'  => $inherit_dir,
					];
				}
			break;

			// all the heavy lifting is done later
			case 'theme':
				$sql_ary['theme_mtime'] = (int) filemtime(PHPBB_ROOT_PATH . "styles/{$path}/theme/stylesheet.css");
			break;

			case 'imageset':
			break;
		}

		$db->sql_transaction('begin');

		$sql = "INSERT INTO {$sql_from}
			" . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$id = $db->sql_nextid();

		$db->sql_transaction('commit');

		add_log('admin', 'LOG_' . $l_type . '_ADD_FS', $dir);
	}

	/**
	* Checks downwards dependencies
	*
	* @access public
	* @param string $mode The element type to check - only template is supported
	* @param int $id The template id
	* @returns false if no component inherits, array with dir and id for each subtemplate otherwise
	*/
	function check_inheritance($mode, $id)
	{
		global $db;

		$l_type = strtoupper($mode);

		switch ($mode)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGESET_TABLE;
			break;
		}

		$sql = "SELECT {$mode}_id, {$mode}_dir
			FROM {$sql_from}
			WHERE {$mode}_inherit_id = " . (int) $id;
		$result = $db->sql_query($sql);

		$names = [];
		while ($row = $db->sql_fetchrow($result))
		{

			$names[$row["{$mode}_id"]] = [
				"{$mode}_id" => $row["{$mode}_id"],
				"{$mode}_dir" => $row["{$mode}_dir"],
			];
		}
		$db->sql_freeresult($result);

		if (sizeof($names))
		{
			return $names;
		}
		else
		{
			return false;
		}
	}

	/**
	* Checks upwards dependencies
	*
	* @access public
	* @param string $mode The element type to check - only template is supported
	* @param int $id The template id
	* @returns false if the component does not inherit, array with dir and id otherwise
	*/
	function get_super($mode, $id)
	{
		global $db;

		$l_type = strtoupper($mode);

		switch ($mode)
		{
			case 'template':
				$sql_from = STYLES_TEMPLATE_TABLE;
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGESET_TABLE;
			break;
		}

		$sql = "SELECT {$mode}_inherit_id
			FROM {$sql_from}
			WHERE {$mode}_id = " . (int) $id;
		$result = $db->sql_query_limit($sql, 1);

		if ($row = $db->sql_fetchrow($result))
		{
			$db->sql_freeresult($result);
		}
		else
		{
			return false;
		}

		$super_id = $row["{$mode}_inherit_id"];

		$sql = "SELECT {$mode}_id, {$mode}_dir
			FROM {$sql_from}
			WHERE {$mode}_id = " . (int) $super_id;

		$result = $db->sql_query_limit($sql, 1);
		if ($row = $db->sql_fetchrow($result))
		{
			$db->sql_freeresult($result);
			return $row;
		}

		return false;
	}

}
