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

	var $imageset_keys;

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

		$this->imageset_keys = [
			'logos' => [
				'site_logo',
			],
			'buttons'	=> [
				'icon_contact_email', 'icon_contact_jabber', 'icon_contact_pm', 'icon_contact_www', 'icon_post_delete', 'icon_post_edit', 'icon_post_info', 'icon_post_quote', 'icon_post_report', 'icon_user_online', 'icon_user_offline', 'icon_user_profile', 'icon_user_search', 'icon_user_warn', 'button_pm_forward', 'button_pm_new', 'button_pm_reply', 'button_topic_locked', 'button_topic_new', 'button_topic_reply',
			],
			'icons'		=> [
				'icon_post_target', 'icon_post_target_unread', 'icon_topic_attach', 'icon_topic_latest', 'icon_topic_newest', 'icon_topic_reported', 'icon_topic_unapproved', 'icon_friend', 'icon_foe',
			],
			'forums'	=> [
				'forum_link', 'forum_read', 'forum_read_locked', 'forum_read_subforum', 'forum_unread', 'forum_unread_locked', 'forum_unread_subforum', 'subforum_read', 'subforum_unread'
			],
			'folders'	=> [
				'topic_moved', 'topic_read', 'topic_read_mine', 'topic_read_locked', 'topic_read_locked_mine', 'topic_unread', 'topic_unread_mine', 'topic_unread_locked', 'topic_unread_locked_mine', 'sticky_read', 'sticky_read_mine', 'sticky_read_locked', 'sticky_read_locked_mine', 'sticky_unread', 'sticky_unread_mine', 'sticky_unread_locked', 'sticky_unread_locked_mine', 'announce_read', 'announce_read_mine', 'announce_read_locked', 'announce_read_locked_mine', 'announce_unread', 'announce_unread_mine', 'announce_unread_locked', 'announce_unread_locked_mine', 'global_read', 'global_read_mine', 'global_read_locked', 'global_read_locked_mine', 'global_unread', 'global_unread_mine', 'global_unread_locked', 'global_unread_locked_mine', 'pm_read', 'pm_unread',
			],
			'polls'		=> [
				'poll_left', 'poll_center', 'poll_right',
			],
			'user'		=> [
				'user_icon1', 'user_icon2', 'user_icon3', 'user_icon4', 'user_icon5', 'user_icon6', 'user_icon7', 'user_icon8', 'user_icon9', 'user_icon10',
			],
		];

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

			case 'edit':
				if ($style_id)
				{
					switch ($mode)
					{
						case 'imageset':
							return $this->edit_imageset($style_id);
					}
				}
			break;

			case 'cache':
				if ($style_id)
				{
					switch ($mode)
					{
						case 'template':
							return $this->template_cache($style_id);
					}
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
									WHERE user_style = $style_id";
								$db->sql_query($sql);
							}
						}
						else if ($action == 'deactivate')
						{
							$s_hidden_fields = [
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action,
								'style_id'	=> $style_id,
							];
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($s_hidden_fields));
						}
					break;
				}

				$this->frontend('style', ['details'], ['delete']);
			break;

			case 'template':

				switch ($action)
				{
					// Clear compiled template cache.
					case 'refresh':

						$sql = 'SELECT *
							FROM ' . STYLES_TEMPLATE_TABLE . "
							WHERE template_id = $style_id";
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
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action,
								'id'		=> $style_id
							]));
						}

					break;
				}

				$this->frontend('template', ['cache', 'details'], ['refresh', 'delete']);
			break;

			case 'theme':
				switch ($action)
				{
					case 'refresh':

						$sql = 'SELECT *
							FROM ' . STYLES_THEME_TABLE . "
							WHERE theme_id = $style_id";
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
								WHERE theme_id = $style_id";
							$db->sql_query($sql);

							$cache->destroy('sql', STYLES_THEME_TABLE);

							add_log('admin', 'LOG_THEME_REFRESHED', $theme_row['theme_name']);
							trigger_error($user->lang['THEME_REFRESHED'] . adm_back_link($this->u_action));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_THEME_REFRESH'], build_hidden_fields([
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action,
								'id'		=> $style_id
							]));
						}
					break;
				}

				$this->frontend('theme', ['details'], ['refresh', 'delete']);
			break;

			case 'imageset':

				switch ($action)
				{
					case 'refresh':

						$sql = 'SELECT *
							FROM ' . STYLES_IMAGESET_TABLE . "
							WHERE imageset_id = $style_id";
						$result = $db->sql_query($sql);
						$imageset_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$imageset_row)
						{
							trigger_error($user->lang['NO_IMAGESET'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if (confirm_box(true))
						{
							$sql_ary = [];

							$imageset_definitions = [];
							foreach ($this->imageset_keys as $topic => $key_array)
							{
								$imageset_definitions = array_merge($imageset_definitions, $key_array);
							}

							$cfg_data_imageset = parse_cfg_file(PHPBB_ROOT_PATH . "styles/{$imageset_row['imageset_path']}/imageset/imageset.cfg");

							$db->sql_transaction('begin');

							$sql = 'DELETE FROM ' . STYLES_IMAGESET_DATA_TABLE . '
								WHERE imageset_id = ' . $style_id;
							$result = $db->sql_query($sql);

							foreach ($cfg_data_imageset as $image_name => $value)
							{
								if (strpos($value, '*') !== false)
								{
									if (substr($value, -1, 1) === '*')
									{
										[$image_filename, $image_height] = explode('*', $value);
										$image_width = 0;
									}
									else
									{
										[$image_filename, $image_height, $image_width] = explode('*', $value);
									}
								}
								else
								{
									$image_filename = $value;
									$image_height = $image_width = 0;
								}

								if (strpos($image_name, 'img_') === 0 && $image_filename)
								{
									$image_name = substr($image_name, 4);
									if (in_array($image_name, $imageset_definitions))
									{
										$sql_ary[] = [
											'image_name'		=> (string) $image_name,
											'image_filename'	=> (string) $image_filename,
											'image_height'		=> (int) $image_height,
											'image_width'		=> (int) $image_width,
											'imageset_id'		=> (int) $style_id,
											'image_lang'		=> '',
										];
									}
								}
							}

							$sql = 'SELECT lang_dir
								FROM ' . LANG_TABLE;
							$result = $db->sql_query($sql);

							while ($row = $db->sql_fetchrow($result))
							{
								if (@file_exists(PHPBB_ROOT_PATH . "styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg"))
								{
									$cfg_data_imageset_data = parse_cfg_file(PHPBB_ROOT_PATH . "styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg");
									foreach ($cfg_data_imageset_data as $image_name => $value)
									{
										if (strpos($value, '*') !== false)
										{
											if (substr($value, -1, 1) === '*')
											{
												[$image_filename, $image_height] = explode('*', $value);
												$image_width = 0;
											}
											else
											{
												[$image_filename, $image_height, $image_width] = explode('*', $value);
											}
										}
										else
										{
											$image_filename = $value;
											$image_height = $image_width = 0;
										}

										if (strpos($image_name, 'img_') === 0 && $image_filename)
										{
											$image_name = substr($image_name, 4);
											if (in_array($image_name, $imageset_definitions))
											{
												$sql_ary[] = [
													'image_name'		=> (string) $image_name,
													'image_filename'	=> (string) $image_filename,
													'image_height'		=> (int) $image_height,
													'image_width'		=> (int) $image_width,
													'imageset_id'		=> (int) $style_id,
													'image_lang'		=> (string) $row['lang_dir'],
												];
											}
										}
									}
								}
							}
							$db->sql_freeresult($result);

							$db->sql_multi_insert(STYLES_IMAGESET_DATA_TABLE, $sql_ary);

							$db->sql_transaction('commit');

							$cache->destroy('sql', STYLES_IMAGESET_DATA_TABLE);

							add_log('admin', 'LOG_IMAGESET_REFRESHED', $imageset_row['imageset_name']);
							trigger_error($user->lang['IMAGESET_REFRESHED'] . adm_back_link($this->u_action));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_IMAGESET_REFRESH'], build_hidden_fields([
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action,
								'id'		=> $style_id
							]));
						}
					break;
				}

				$this->frontend('imageset', ['edit', 'details'], ['refresh', 'delete']);
			break;
		}
	}

	/**
	* Build Frontend with supplied options
	*/
	function frontend($mode, $options, $actions)
	{
		global $user, $template, $db, $config;

		$sql_from = '';
		$sql_sort = 'LOWER(' . $mode . '_name)';
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
			'S_FRONTEND'		=> true,
			'S_STYLE'			=> ($mode == 'style'),

			'L_TITLE'			=> $user->lang[$this->page_title],
			'L_EXPLAIN'			=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'			=> $user->lang[$l_prefix . '_NAME'],
			'L_INSTALLED'		=> $user->lang['INSTALLED_' . $l_prefix],
			'L_UNINSTALLED'		=> $user->lang['UNINSTALLED_' . $l_prefix],
			'L_NO_UNINSTALLED'	=> $user->lang['NO_UNINSTALLED_' . $l_prefix],
			'L_CREATE'			=> $user->lang['CREATE_' . $l_prefix],

			'U_ACTION'			=> $this->u_action,
		]);

		$sql = "SELECT *
			FROM $sql_from
			ORDER BY $sql_sort ASC";
		$result = $db->sql_query($sql);

		$installed = [];

		$basis_options = '<option class="sep" value="">' . $user->lang['OPTIONAL_BASIS'] . '</option>';
		while ($row = $db->sql_fetchrow($result))
		{
			$installed[] = $row[$mode . '_name'];
			$basis_options .= '<option value="' . $row[$mode . '_id'] . '">' . $row[$mode . '_name'] . '</option>';

			$stylevis = ($mode == 'style' && !$row['style_active']) ? 'activate' : 'deactivate';

			$s_options = [];
			foreach ($options as $option)
			{
				$s_options[] = '<a href="' . $this->u_action . "&amp;action=$option&amp;id=" . $row[$mode . '_id'] . '">' . $user->lang[strtoupper($option)] . '</a>';
			}

			$s_actions = [];
			foreach ($actions as $option)
			{
				$s_actions[] = '<a href="' . $this->u_action . "&amp;action=$option&amp;id=" . $row[$mode . '_id'] . '">' . $user->lang[strtoupper($option)] . '</a>';
			}

			$template->assign_block_vars('installed', [
				'S_DEFAULT_STYLE'		=> ($mode == 'style' && $row['style_id'] == $config['default_style']),
				'U_EDIT'				=> $this->u_action . '&amp;action=' . (($mode == 'style') ? 'details' : 'edit') . '&amp;id=' . $row[$mode . '_id'],
				'U_STYLE_ACT_DEACT'		=> $this->u_action . '&amp;action=' . $stylevis . '&amp;id=' . $row[$mode . '_id'],
				'L_STYLE_ACT_DEACT'		=> $user->lang['STYLE_' . strtoupper($stylevis)],
				'S_OPTIONS'				=> implode(' | ', $s_options),
				'S_ACTIONS'				=> implode(' | ', $s_actions),
				'U_PREVIEW'				=> ($mode == 'style') ? append_sid(PHPBB_ROOT_PATH . 'index.php', "$mode=" . $row[$mode . '_id']) : '',

				'NAME'					=> $row[$mode . '_name'],
				'STYLE_COUNT'			=> ($mode == 'style' && isset($style_count[$row['style_id']])) ? $style_count[$row['style_id']] : 0,

				'S_INACTIVE'			=> ($mode == 'style' && !$row['style_active']),
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

				$subpath = ($mode != 'style') ? "$mode/" : '';
				if (file_exists(PHPBB_ROOT_PATH . "styles/$file/$subpath$mode.cfg"))
				{
					if ($cfg = file(PHPBB_ROOT_PATH . "styles/$file/$subpath$mode.cfg"))
					{
						$items = parse_cfg_file('', $cfg);
						$name = (isset($items['name'])) ? trim($items['name']) : false;

						if ($name && !in_array($name, $installed))
						{
							// The array key is used for sorting later on.
							// $file is appended because $name doesn't have to be unique.
							$new_ary[$name . $file] = [
								'path'		=> $file,
								'name'		=> $name,
								'copyright'	=> $items['copyright'],
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
					'NAME'			=> $cfg['name'],
					'COPYRIGHT'		=> $cfg['copyright'],
					'U_INSTALL'		=> $this->u_action . '&amp;action=install&amp;path=' . urlencode($cfg['path'])]
				);
			}
		}
		unset($new_ary);

		$template->assign_vars([
			'S_BASIS_OPTIONS'		=> $basis_options]
		);

	}

	/**
	* Allows the admin to view cached versions of template files and clear single template cache files
	*
	* @param int $template_id specifies which template's cache is shown
	*/
	function template_cache($template_id)
	{
		global $config, $db, $cache, $user, $template;

		$source		= str_replace('/', '.', request_var('source', ''));
		$file_ary	= array_diff(request_var('delete', ['']), ['']);
		$submit		= isset($_POST['submit']);

		$sql = 'SELECT *
			FROM ' . STYLES_TEMPLATE_TABLE . "
			WHERE template_id = $template_id";
		$result = $db->sql_query($sql);
		$template_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$template_row)
		{
			trigger_error($user->lang['NO_TEMPLATE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// User wants to delete one or more files ...
		if ($submit && $file_ary)
		{
			$this->clear_template_cache($template_row, $file_ary);
			trigger_error($user->lang['TEMPLATE_CACHE_CLEARED'] . adm_back_link($this->u_action . "&amp;action=cache&amp;id=$template_id"));
		}

		$cache_prefix = 'tpl_' . str_replace('_', '-', $template_row['template_path']);

		// Someone wants to see the cached source ... so we'll highlight it,
		// add line numbers and indent it appropriately. This could be nasty
		// on larger source files ...
		if ($source && file_exists(PHPBB_ROOT_PATH . "cache/{$cache_prefix}_$source.html.php"))
		{
			adm_page_header($user->lang['TEMPLATE_CACHE']);

			$template->set_filenames([
				'body'	=> 'viewsource.html']
			);

			$template->assign_vars([
				'FILENAME'	=> str_replace('.', '/', $source) . '.html']
			);

			$code = str_replace(["\r\n", "\r"], ["\n", "\n"], file_get_contents(PHPBB_ROOT_PATH . "cache/{$cache_prefix}_$source.html.php"));

			$conf = ['highlight.bg', 'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string'];
			foreach ($conf as $ini_var)
			{
				@ini_set($ini_var, str_replace('highlight.', 'syntax', $ini_var));
			}

			$marker = 'MARKER' . time();
			$code = highlight_string(str_replace("\n", $marker, $code), true);
			$code = str_replace($marker, "\n", $code);
			$str_from = ['<span style="color: ', '<font color="syntax', '</font>', '<code>', '</code>','[', ']', '.', ':'];
			$str_to = ['<span class="', '<span class="syntax', '</span>', '', '', '&#91;', '&#93;', '&#46;', '&#58;'];

			$code = str_replace($str_from, $str_to, $code);
			$code = preg_replace('#^(<span class="[a-z_]+">)\n?(.*?)\n?(</span>)$#ism', '$1$2$3', $code);
			$code = substr($code, strlen('<span class="syntaxhtml">'));
			$code = substr($code, 0, -1 * strlen('</ span>'));
			$code = explode("\n", $code);

			foreach ($code as $key => $line)
			{
				$template->assign_block_vars('source', [
					'LINENUM'	=> $key + 1,
					'LINE'		=> preg_replace('#([^ ;])&nbsp;([^ &])#', '$1 $2', $line)]
				);
				unset($code[$key]);
			}

			adm_page_footer();
		}

		// Get a list of cached template files and then retrieve additional information about them
		$file_ary = $this->template_cache_filelist($template_row['template_path']);

		foreach ($file_ary as $file)
		{
			$file		= str_replace('/', '.', $file);

			// perform some dirty guessing to get the path right.
			// We assume that three dots in a row were '../'
			$tpl_file	= str_replace('.', '/', $file);
			$tpl_file	= str_replace('///', '../', $tpl_file);

			$cache_file = PHPBB_ROOT_PATH . "cache/{$cache_prefix}_{$file}.html.php";

			if (!file_exists($cache_file))
			{
				continue;
			}

			$file_tpl = PHPBB_ROOT_PATH . "styles/{$template_row['template_path']}/template/$tpl_file.html";
			$inherited = false;

			if (isset($template_row['template_inherits_id']) && $template_row['template_inherits_id'])
			{
				if (!file_exists($file_tpl))
				{
					$file_tpl = PHPBB_ROOT_PATH . "styles/{$template_row['template_inherit_path']}/template/$tpl_file.html";
					$inherited = true;
				}
			}

			$template->assign_block_vars('file', [
				'U_VIEWSOURCE'	=> $this->u_action . "&amp;action=cache&amp;id=$template_id&amp;source=$file",

				'CACHED'		=> $user->format_date(filemtime($cache_file)),
				'FILENAME'		=> $file,
				'FILENAME_PATH'	=> $file_tpl,
				'FILESIZE'		=> get_formatted_filesize(filesize($cache_file)),
				'MODIFIED'		=> file_exists($file_tpl) ? $user->format_date(filemtime($file_tpl)) : '-',
			]);
		}

		$template->assign_vars([
			'S_CACHE'			=> true,
			'S_TEMPLATE'		=> true,

			'U_ACTION'			=> $this->u_action . "&amp;action=cache&amp;id=$template_id",
			'U_BACK'			=> $this->u_action]
		);
	}

	/**
	* Edit imagesets
	*
	* @param int $imageset_id specifies which imageset is being edited
	*/
	function edit_imageset($imageset_id)
	{
		global $db, $user, $cache, $template;

		$this->page_title = 'EDIT_IMAGESET';

		if (!$imageset_id)
		{
			trigger_error($user->lang['NO_IMAGESET'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$update		= isset($_POST['update']);

		$imgname	= request_var('imgname', 'site_logo');
		$imgname	= preg_replace('#[^a-z0-9\-+_]#i', '', $imgname);
		$sql_extra = $imgnamelang = '';

		$sql = 'SELECT imageset_path, imageset_name
			FROM ' . STYLES_IMAGESET_TABLE . "
			WHERE imageset_id = $imageset_id";
		$result = $db->sql_query($sql);
		$imageset_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$imageset_row)
		{
			trigger_error($user->lang['NO_IMAGESET'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$imageset_path		= $imageset_row['imageset_path'];
		$imageset_name		= $imageset_row['imageset_name'];

		if (strpos($imgname, '-') !== false)
		{
			[$imgname, $imgnamelang] = explode('-', $imgname);
			$sql_extra = " AND image_lang IN ('" . $db->sql_escape($imgnamelang) . "', '')";
		}

		$sql = 'SELECT image_filename, image_width, image_height, image_lang, image_id
			FROM ' . STYLES_IMAGESET_DATA_TABLE . "
			WHERE imageset_id = $imageset_id
				AND image_name = '" . $db->sql_escape($imgname) . "'$sql_extra";
		$result = $db->sql_query($sql);
		$imageset_data_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$image_filename	= $imageset_data_row['image_filename'];
		$image_width	= $imageset_data_row['image_width'];
		$image_height	= $imageset_data_row['image_height'];
		$image_lang		= $imageset_data_row['image_lang'];
		$image_id		= $imageset_data_row['image_id'];
		$imgsize		= ($imageset_data_row['image_width'] && $imageset_data_row['image_height']) ? 1 : 0;

		// Check to see whether the selected image exists in the table
		$valid_name = !$update;

		foreach ($this->imageset_keys as $category => $img_ary)
		{
			if (in_array($imgname, $img_ary))
			{
				$valid_name = true;
				break;
			}
		}

		if ($update && isset($_POST['imgpath']) && $valid_name)
		{
			// If imgwidth and imgheight are non-zero grab the actual size
			// from the image itself ... we ignore width settings for the poll center image
			$imgwidth	= request_var('imgwidth', 0);
			$imgheight	= request_var('imgheight', 0);
			$imgsize	= request_var('imgsize', 0);
			$imgpath	= request_var('imgpath', '');
			$imgpath	= str_replace('..', '.', $imgpath);

			// If no dimensions selected, we reset width and height to 0 ;)
			if (!$imgsize)
			{
				$imgwidth = $imgheight = 0;
			}

			$imglang = '';

			if ($imgpath && !file_exists(PHPBB_ROOT_PATH . "styles/$imageset_path/imageset/$imgpath"))
			{
				trigger_error($user->lang['NO_IMAGE_ERROR'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Determine width/height. If dimensions included and no width/height given, we detect them automatically...
			if ($imgsize && $imgpath)
			{
				if (!$imgwidth || !$imgheight)
				{
					[$imgwidth_file, $imgheight_file] = getimagesize(PHPBB_ROOT_PATH . "styles/$imageset_path/imageset/$imgpath");
					$imgwidth = $imgwidth ?: $imgwidth_file;
					$imgheight = $imgheight ?: $imgheight_file;
				}
				$imgwidth	= ($imgname != 'poll_center') ? (int) $imgwidth : 0;
				$imgheight	= (int) $imgheight;
			}

			if (strpos($imgpath, '/') !== false)
			{
				[$imglang, $imgfilename] = explode('/', $imgpath);
			}
			else
			{
				$imgfilename = $imgpath;
			}

			$sql_ary = [
				'image_filename'	=> (string) $imgfilename,
				'image_width'		=> (int) $imgwidth,
				'image_height'		=> (int) $imgheight,
				'image_lang'		=> (string) $imglang,
			];

			// already exists
			if ($imageset_data_row)
			{
				$sql = 'UPDATE ' . STYLES_IMAGESET_DATA_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE image_id = $image_id";
				$db->sql_query($sql);
			}
			// does not exist
			else if (!$imageset_data_row)
			{
				$sql_ary['image_name']	= $imgname;
				$sql_ary['imageset_id']	= (int) $imageset_id;
				$db->sql_query('INSERT INTO ' . STYLES_IMAGESET_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
			}

			$cache->destroy('sql', STYLES_IMAGESET_DATA_TABLE);

			add_log('admin', 'LOG_IMAGESET_EDIT', $imageset_name);

			$template->assign_var('SUCCESS', true);

			$image_filename = $imgfilename;
			$image_width	= $imgwidth;
			$image_height	= $imgheight;
			$image_lang		= $imglang;
		}

		$imglang = '';
		$imagesetlist = ['nolang' => [], 'lang' => []];
		$langs = [];

		$dir = PHPBB_ROOT_PATH . "styles/$imageset_path/imageset";
		$dp = @opendir($dir);

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ($file[0] != '.' && strtoupper($file) != 'CVS' && !is_file($dir . '/' . $file) && !is_link($dir . '/' . $file))
				{
					$langs[] = $file;
				}
				else if (preg_match('#\.(?:gif|jpg|png)$#', $file))
				{
					$imagesetlist['nolang'][] = $file;
				}
			}

			if ($sql_extra)
			{
				$dp2 = @opendir("$dir/$imgnamelang");

				if ($dp2)
				{
					while (($file2 = readdir($dp2)) !== false)
					{
						if (preg_match('#\.(?:gif|jpg|png)$#', $file2))
						{
							$imagesetlist['lang'][] = "$imgnamelang/$file2";
						}
					}
					closedir($dp2);
				}
			}
			closedir($dp);
		}

		// Generate list of image options
		$img_options = '';
		foreach ($this->imageset_keys as $category => $img_ary)
		{
			$template->assign_block_vars('category', [
				'NAME'			=> $user->lang['IMG_CAT_' . strtoupper($category)]
			]);

			foreach ($img_ary as $img)
			{
				if ($category == 'buttons')
				{
					foreach ($langs as $language)
					{
						$template->assign_block_vars('category.images', [
							'SELECTED'			=> ($img == $imgname && $language == $imgnamelang),
							'VALUE'				=> $img . '-' . $language,
							'TEXT'				=> $user->lang['IMG_' . strtoupper($img)] . ' [ ' . $language . ' ]'
						]);
					}
				}
				else
				{
					$template->assign_block_vars('category.images', [
						'SELECTED'			=> ($img == $imgname),
						'VALUE'				=> $img,
						'TEXT'				=> (($category == 'custom') ? $img : $user->lang['IMG_' . strtoupper($img)])
					]);
				}
			}
		}

		// Make sure the list of possible images is sorted alphabetically
		sort($imagesetlist['lang']);
		sort($imagesetlist['nolang']);

		$image_found = false;
		$img_val = '';
		foreach ($imagesetlist as $type => $img_ary)
		{
			if ($type !== 'lang' || $sql_extra)
			{
				$template->assign_block_vars('imagesetlist', [
					'TYPE'	=> ($type == 'lang')
				]);
			}

			foreach ($img_ary as $img)
			{
				$imgtext = preg_replace('/^([^\/]+\/)/', '', $img);
				$selected = (!empty($imgname) && strpos($image_filename, $imgtext) !== false);
				if ($selected)
				{
					$image_found = true;
					$img_val = htmlspecialchars($img);
				}
				$template->assign_block_vars('imagesetlist.images', [
					'SELECTED'			=> $selected,
					'TEXT'				=> $imgtext,
					'VALUE'				=> htmlspecialchars($img)
				]);
			}
		}

		$imgsize_bool = (!empty($imgname) && $image_width && $image_height);
		$image_request = '../styles/' . $imageset_path . '/imageset/' . ($image_lang ? $imgnamelang . '/' : '') . $image_filename;

		$template->assign_vars([
			'S_EDIT_IMAGESET'	=> true,
			'L_TITLE'			=> $user->lang[$this->page_title],
			'L_EXPLAIN'			=> $user->lang[$this->page_title . '_EXPLAIN'],
			'IMAGE_OPTIONS'		=> $img_options,
			'IMAGE_SIZE'		=> $image_width,
			'IMAGE_HEIGHT'		=> $image_height,
			'IMAGE_REQUEST'		=> (empty($image_filename)) ? 'images/no_image.png' : $image_request,
			'U_ACTION'			=> $this->u_action . "&amp;action=edit&amp;id=$imageset_id",
			'U_BACK'			=> $this->u_action,
			'NAME'				=> $imageset_name,
			'A_NAME'			=> addslashes($imageset_name),
			'PATH'				=> $imageset_path,
			'A_PATH'			=> addslashes($imageset_path),
			'ERROR'				=> !$valid_name,
			'IMG_SRC'			=> ($image_found) ? '../styles/' . $imageset_path . '/imageset/' . $img_val : 'images/no_image.png',
			'IMAGE_SELECT'		=> $image_found
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
				$sql_select = 'template_id, template_name, template_path';
			break;

			case 'theme':
				$sql_from = STYLES_THEME_TABLE;
				$sql_select = 'theme_id, theme_name, theme_path';
			break;

			case 'imageset':
				$sql_from = STYLES_IMAGESET_TABLE;
				$sql_select = 'imageset_id, imageset_name, imageset_path';
			break;
		}

		if ($mode === 'template' && ($conflicts = $this->check_inheritance($mode, $style_id)))
		{
			$l_type = strtoupper($mode);
			$msg = $user->lang[$l_type . '_DELETE_DEPENDENT'];
			foreach ($conflicts as $id => $values)
			{
				$msg .= '<br />' . $values['template_name'];
			}

			trigger_error($msg . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$l_prefix = strtoupper($mode);

		$sql = "SELECT $sql_select
			FROM $sql_from
			WHERE {$mode}_id = $style_id";
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
				$sql = "DELETE FROM $sql_from
					WHERE {$mode}_id = $style_id";
				$db->sql_query($sql);

				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_style = $new_id
					WHERE user_style = $style_id";
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

			add_log('admin', 'LOG_' . $l_prefix . '_DELETE', $style_row[$mode . '_name']);
			$message = ($mode != 'style') ? $l_prefix . '_DELETED_FS' : $l_prefix . '_DELETED';
			trigger_error($user->lang[$message] . adm_back_link($this->u_action));
		}

		$this->page_title = 'DELETE_' . $l_prefix;

		$template->assign_vars([
			'S_DELETE'			=> true,

			'L_TITLE'			=> $user->lang[$this->page_title],
			'L_EXPLAIN'			=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'			=> $user->lang[$l_prefix . '_NAME'],
			'L_REPLACE'			=> $user->lang['REPLACE_' . $l_prefix],
			'L_REPLACE_EXPLAIN'	=> $user->lang['REPLACE_' . $l_prefix . '_EXPLAIN'],

			'U_ACTION'		=> $this->u_action . "&amp;action=delete&amp;id=$style_id",
			'U_BACK'		=> $this->u_action,

			'NAME'			=> $style_row[$mode . '_name'],
			]
		);

		if ($mode == 'style')
		{
			$template->assign_vars([
				'S_DELETE_STYLE'		=> true,
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

		if ($component == 'imageset')
		{
			$sql = 'DELETE FROM ' . STYLES_IMAGESET_DATA_TABLE . "
				WHERE imageset_id = $component_id";
			$db->sql_query($sql);
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

		$sql = "DELETE FROM $sql_from
			WHERE {$component}_id = $component_id";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . STYLES_TABLE . "
			SET {$component}_id = $new_id
			WHERE {$component}_id = $component_id";
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
				$sql_where = 'WHERE template_inherits_id <> ' . $component_id;
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

			$sql = "SELECT {$component}_id, {$component}_name
				FROM $sql_from
				WHERE {$component}_id = {$component_id}";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$s_options .= '<option value="-1" selected="selected">' . $user->lang['DELETE_' . strtoupper($component)] . '</option>';
			$s_options .= '<option value="0">' . sprintf($user->lang['KEEP_' . strtoupper($component)], $row[$component . '_name']) . '</option>';
		}
		else
		{
			$sql = "SELECT {$component}_id, {$component}_name
				FROM $sql_from
				$sql_where
				ORDER BY {$component}_name ASC";
			$result = $db->sql_query($sql);

			$s_keep_option = $s_options = '';
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row[$component . '_id'] != $component_id)
				{
					$is_only_component = false;
					$s_options .= '<option value="' . $row[$component . '_id'] . '">' . sprintf($user->lang['REPLACE_WITH_OPTION'], $row[$component . '_name']) . '</option>';
				}
				else if ($component != 'style')
				{
					$s_keep_option = '<option value="0" selected="selected">' . sprintf($user->lang['KEEP_' . strtoupper($component)], $row[$component . '_name']) . '</option>';
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
				$component_in_use[] = $conflict_data['template_name'];
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
		$l_type = strtoupper($mode);

		$error = [];
		$element_ary = ['template' => STYLES_TEMPLATE_TABLE, 'theme' => STYLES_THEME_TABLE, 'imageset' => STYLES_IMAGESET_TABLE];

		switch ($mode)
		{
			case 'style':
				$sql_from = STYLES_TABLE;
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
		}

		$sql = "SELECT *
			FROM $sql_from
			WHERE {$mode}_id = $style_id";
		$result = $db->sql_query($sql);
		$style_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$style_row)
		{
			trigger_error($user->lang['NO_' . $l_type] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$style_row['style_default'] = ($mode == 'style' && $config['default_style'] == $style_id) ? 1 : 0;

		if ($update)
		{
			$name = utf8_normalize_nfc(request_var('name', '', true));
			$copyright = utf8_normalize_nfc(request_var('copyright', '', true));

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

			$sql = "SELECT {$mode}_id, {$mode}_name
				FROM $sql_from
				WHERE {$mode}_id <> $style_id
				AND LOWER({$mode}_name) = '" . $db->sql_escape(strtolower($name)) . "'";
			$result = $db->sql_query($sql);
			$conflict = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($mode == 'style' && (!$template_id || !$theme_id || !$imageset_id))
			{
				$error[] = $user->lang['STYLE_ERR_NO_IDS'];
			}

			if ($mode == 'style' && $style_row['style_active'] && !$style_active && $config['default_style'] == $style_id)
			{
				$error[] = $user->lang['DEACTIVATE_DEFAULT'];
			}

			if (!$name || $conflict)
			{
				$error[] = $user->lang[$l_type . '_ERR_STYLE_NAME'];
			}

			if (!sizeof($error))
			{
				// Check length settings
				if (utf8_strlen($name) > 30)
				{
					$error[] = $user->lang[$l_type . '_ERR_NAME_LONG'];
				}

				if (utf8_strlen($copyright) > 60)
				{
					$error[] = $user->lang[$l_type . '_ERR_COPY_LONG'];
				}
			}
		}

		if ($update && sizeof($error))
		{
			$style_row = array_merge($style_row, [
				'template_id'			=> $template_id,
				'theme_id'				=> $theme_id,
				'imageset_id'			=> $imageset_id,
				'style_active'			=> $style_active,
				$mode . '_name'			=> $name,
				$mode . '_copyright'	=> $copyright]
			);
		}

		// User has submitted form and no errors have occurred
		if ($update && !sizeof($error))
		{
			$sql_ary = [
				$mode . '_name'			=> $name,
				$mode . '_copyright'	=> $copyright
			];

			switch ($mode)
			{
				case 'style':

					$sql_ary += [
						'template_id'		=> (int) $template_id,
						'theme_id'			=> (int) $theme_id,
						'imageset_id'		=> (int) $imageset_id,
						'style_active'		=> (int) $style_active,
					];
				break;

				case 'imageset':
				break;

				case 'theme':
				break;
			}

			if (sizeof($sql_ary))
			{
				$sql = "UPDATE $sql_from
					SET " . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE {$mode}_id = $style_id";
				$db->sql_query($sql);

				// Making this the default style?
				if ($mode == 'style' && $style_default)
				{
					set_config('default_style', $style_id);
				}
			}

			$cache->destroy('sql', STYLES_TABLE);

			add_log('admin', 'LOG_' . $l_type . '_EDIT_DETAILS', $name);
			if (sizeof($error))
			{
				trigger_error(implode('<br />', $error) . adm_back_link($this->u_action), E_USER_WARNING);
			}
			else
			{
				trigger_error($user->lang[$l_type . '_DETAILS_UPDATED'] . adm_back_link($this->u_action));
			}
		}

		if ($mode == 'style')
		{
			foreach ($element_ary as $element => $table)
			{
				$sql = "SELECT {$element}_id, {$element}_name
					FROM $table
					ORDER BY {$element}_id ASC";
				$result = $db->sql_query($sql);

				${$element . '_options'} = '';
				while ($row = $db->sql_fetchrow($result))
				{
					$selected = ($row[$element . '_id'] == $style_row[$element . '_id']) ? ' selected="selected"' : '';
					${$element . '_options'} .= '<option value="' . $row[$element . '_id'] . '"' . $selected . '>' . $row[$element . '_name'] . '</option>';
				}
				$db->sql_freeresult($result);
			}
		}

		if ($mode == 'template')
		{
			$super = [];
			if (isset($style_row[$mode . '_inherits_id']) && $style_row['template_inherits_id'])
			{
				$super = $this->get_super($mode, $style_row['template_id']);
			}
		}

		$this->page_title = 'EDIT_DETAILS_' . $l_type;

		$template->assign_vars([
			'S_DETAILS'				=> true,
			'S_ERROR_MSG'			=> (sizeof($error) > 0),
			'S_STYLE'				=> ($mode == 'style'),
			'S_TEMPLATE'			=> ($mode == 'template'),
			'S_THEME'				=> ($mode == 'theme'),
			'S_IMAGESET'			=> ($mode == 'imageset'),
			'S_STYLE_ACTIVE'		=> $style_row['style_active'] ?? 0,
			'S_STYLE_DEFAULT'		=> $style_row['style_default'] ?? 0,
			'S_SUPERTEMPLATE'		=> (isset($style_row[$mode . '_inherits_id']) && $style_row[$mode . '_inherits_id']) ? $super['template_name'] : 0,

			'S_TEMPLATE_OPTIONS'	=> ($mode == 'style') ? $template_options : '',
			'S_THEME_OPTIONS'		=> ($mode == 'style') ? $theme_options : '',
			'S_IMAGESET_OPTIONS'	=> ($mode == 'style') ? $imageset_options : '',

			'U_ACTION'		=> $this->u_action . '&amp;action=details&amp;id=' . $style_id,
			'U_BACK'		=> $this->u_action,

			'L_TITLE'				=> $user->lang[$this->page_title],
			'L_EXPLAIN'				=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'				=> $user->lang[$l_type . '_NAME'],

			'ERROR_MSG'		=> (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'			=> $style_row[$mode . '_name'],
			'COPYRIGHT'		=> $style_row[$mode . '_copyright'],
			]
		);
	}

	/**
	* Returns an array containing all template filenames for one template that are currently cached.
	*
	* @param string $template_path contains the name of the template's folder in /styles/
	*
	* @return array of filenames that exist in /styles/$template_path/template/ (without extension!)
	*/
	function template_cache_filelist($template_path)
	{
		global $user;

		$cache_prefix = 'tpl_' . str_replace('_', '-', $template_path);

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
	*	The file names should be the original template file names and not the cache file names.
	*/
	function clear_template_cache($template_row, $file_ary = false)
	{
		global $user;

		$cache_prefix = 'tpl_' . str_replace('_', '-', $template_row['template_path']);

		if (!$file_ary || !is_array($file_ary))
		{
			$file_ary = $this->template_cache_filelist($template_row['template_path']);
			$log_file_list = $user->lang['ALL_FILES'];
		}
		else
		{
			$log_file_list = implode(', ', $file_ary);
		}

		foreach ($file_ary as $file)
		{
			$file = str_replace('/', '.', $file);

			$file = PHPBB_ROOT_PATH . "cache/{$cache_prefix}_$file.html.php";
			if (file_exists($file) && is_file($file))
			{
				@unlink($file);
			}
		}
		unset($file_ary);

		add_log('admin', 'LOG_TEMPLATE_CACHE_CLEARED', $template_row['template_name'], $log_file_list);
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
			$cfg_file = ($mode == 'style') ? "$root_path$mode.cfg" : "$root_path$mode/$mode.cfg";

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
			$name		= $installcfg['name'];
			$copyright	= $installcfg['copyright'];
			$version	= $installcfg['version'];

			$style_row = [
				$mode . '_id'			=> 0,
				$mode . '_name'			=> '',
				$mode . '_copyright'	=> ''
			];

			switch ($mode)
			{
				case 'style':

					$style_row = [
						'style_id'			=> 0,
						'style_name'		=> $installcfg['name'],
						'style_copyright'	=> $installcfg['copyright']
					];

					$reqd_template = $installcfg['required_template'] ?? false;
					$reqd_theme = $installcfg['required_theme'] ?? false;
					$reqd_imageset = $installcfg['required_imageset'] ?? false;

					// Check to see if each element is already installed, if it is grab the id
					foreach ($element_ary as $element => $table)
					{
						$style_row = array_merge($style_row, [
							$element . '_id'			=> 0,
							$element . '_name'			=> '',
							$element . '_copyright'		=> '']
						);

			 			$this->test_installed($element, $error, (${'reqd_' . $element}) ? PHPBB_ROOT_PATH . 'styles/' . $reqd_template . '/' : $root_path, ${'reqd_' . $element}, $style_row[$element . '_id'], $style_row[$element . '_name'], $style_row[$element . '_copyright']);

						if (!$style_row[$element . '_name'])
						{
							$style_row[$element . '_name'] = $reqd_template;
						}

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
					$this->test_installed('template', $error, $root_path, false, $style_row['template_id'], $style_row['template_name'], $style_row['template_copyright']);
				break;

				case 'theme':
					$this->test_installed('theme', $error, $root_path, false, $style_row['theme_id'], $style_row['theme_name'], $style_row['theme_copyright']);
				break;

				case 'imageset':
					$this->test_installed('imageset', $error, $root_path, false, $style_row['imageset_id'], $style_row['imageset_name'], $style_row['imageset_copyright']);
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
					${$element . '_path'} = (${'reqd_' . $element}) ?: false;
				}
				$this->install_style($error, 'install', $root_path, $style_row['style_id'], $style_row['style_name'], $install_path, $style_row['style_copyright'], $style_row['style_active'], $style_row['style_default'], $style_row, $template_root_path, $template_path, $theme_root_path, $theme_path, $imageset_root_path, $imageset_path);
			}
			else
			{
				$this->install_element($mode, $error, 'install', $root_path, $style_row[$mode . '_id'], $style_row[$mode . '_name'], $install_path, $style_row[$mode . '_copyright']);
			}

			if (!sizeof($error))
			{
				$cache->destroy('sql', STYLES_TABLE);

				trigger_error($user->lang[$l_type . '_ADDED'] . adm_back_link($this->u_action));
			}
		}

		$this->page_title = 'INSTALL_' . $l_type;

		$template->assign_vars([
			'S_DETAILS'			=> true,
			'S_INSTALL'			=> true,
			'S_ERROR_MSG'		=> (sizeof($error) > 0),
			'S_LOCATION'		=> empty($installcfg['inherit_from']),
			'S_STYLE'			=> ($mode == 'style'),
			'S_TEMPLATE'		=> ($mode == 'template'),
			'S_SUPERTEMPLATE'	=> $installcfg['inherit_from'] ?? '',
			'S_THEME'			=> ($mode == 'theme'),

			'S_STYLE_ACTIVE'		=> $style_row['style_active'] ?? 0,
			'S_STYLE_DEFAULT'		=> $style_row['style_default'] ?? 0,

			'U_ACTION'			=> $this->u_action . "&amp;action=install&amp;path=" . urlencode($install_path),
			'U_BACK'			=> $this->u_action,

			'L_TITLE'				=> $user->lang[$this->page_title],
			'L_EXPLAIN'				=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'				=> $user->lang[$l_type . '_NAME'],

			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'				=> $style_row[$mode . '_name'],
			'COPYRIGHT'			=> $style_row[$mode . '_copyright'],
			'TEMPLATE_NAME'		=> ($mode == 'style') ? $style_row['template_name'] : '',
			'THEME_NAME'		=> ($mode == 'style') ? $style_row['theme_name'] : '',
			'IMAGESET_NAME'		=> ($mode == 'style') ? $style_row['imageset_name'] : '']
		);
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

		$style_row = [
			$mode . '_name'			=> utf8_normalize_nfc(request_var('name', '', true)),
			$mode . '_copyright'	=> utf8_normalize_nfc(request_var('copyright', '', true)),
			'template_id'			=> 0,
			'theme_id'				=> 0,
			'imageset_id'			=> 0,
			'style_active'			=> request_var('style_active', 1),
			'style_default'			=> request_var('style_default', 0),
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

			$sql = "SELECT $sql_select
				FROM $sql_from
				WHERE {$mode}_id = $basis";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				$error[] = $user->lang['NO_' . $l_type];
			}

			if (!sizeof($error))
			{
				$style_row['template_id']	= $row['template_id'] ?? $style_row['template_id'];
				$style_row['theme_id']		= $row['theme_id'] ?? $style_row['theme_id'];
				$style_row['imageset_id']	= $row['imageset_id'] ?? $style_row['imageset_id'];
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

				$this->install_style($error, 'add', '', $style_row['style_id'], $style_row['style_name'], '', $style_row['style_copyright'], $style_row['style_active'], $style_row['style_default'], $style_row);
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
				$sql = "SELECT {$element}_id, {$element}_name
					FROM $table
					ORDER BY {$element}_id ASC";
				$result = $db->sql_query($sql);

				${$element . '_options'} = '';
				while ($row = $db->sql_fetchrow($result))
				{
					$selected = ($row[$element . '_id'] == $style_row[$element . '_id']) ? ' selected="selected"' : '';
					${$element . '_options'} .= '<option value="' . $row[$element . '_id'] . '"' . $selected . '>' . $row[$element . '_name'] . '</option>';
				}
				$db->sql_freeresult($result);
			}
		}

		$this->page_title = 'ADD_' . $l_type;

		$template->assign_vars([
			'S_DETAILS'			=> true,
			'S_ADD'				=> true,
			'S_ERROR_MSG'		=> (sizeof($error) > 0),
			'S_STYLE'			=> ($mode == 'style'),
			'S_TEMPLATE'		=> ($mode == 'template'),
			'S_THEME'			=> ($mode == 'theme'),
			'S_BASIS'			=> (bool) $basis,

			'S_STYLE_ACTIVE'		=> $style_row['style_active'] ?? 0,
			'S_STYLE_DEFAULT'		=> $style_row['style_default'] ?? 0,
			'S_TEMPLATE_OPTIONS'	=> ($mode == 'style') ? $template_options : '',
			'S_THEME_OPTIONS'		=> ($mode == 'style') ? $theme_options : '',
			'S_IMAGESET_OPTIONS'	=> ($mode == 'style') ? $imageset_options : '',

			'U_ACTION'			=> $this->u_action . '&amp;action=add&amp;basis=' . $basis,
			'U_BACK'			=> $this->u_action,

			'L_TITLE'				=> $user->lang[$this->page_title],
			'L_EXPLAIN'				=> $user->lang[$this->page_title . '_EXPLAIN'],
			'L_NAME'				=> $user->lang[$l_type . '_NAME'],

			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
			'NAME'				=> $style_row[$mode . '_name'],
			'COPYRIGHT'			=> $style_row[$mode . '_copyright']]
		);

	}

	/**

					$reqd_template = $installcfg['required_template'] ?? false;
					$reqd_theme = $installcfg['required_theme'] ?? false;
					$reqd_imageset = $installcfg['required_imageset'] ?? false;

					// Check to see if each element is already installed, if it is grab the id
					foreach ($element_ary as $element => $table)
					{
						$style_row = array_merge($style_row, array(
							$element . '_id'			=> 0,
							$element . '_name'			=> '',
							$element . '_copyright'		=> '')
						);

			 			$this->test_installed($element, $error, $root_path, ${'reqd_' . $element}, $style_row[$element . '_id'], $style_row[$element . '_name'], $style_row[$element . '_copyright']);
	* Is this element installed? If not, grab its cfg details
	*/
	function test_installed($element, &$error, $root_path, $reqd_name, &$id, &$name, &$copyright)
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

		$chk_name = ($reqd_name !== false) ? $reqd_name : $name;

		$sql = "SELECT {$element}_id, {$element}_name
			FROM $sql_from
			WHERE {$element}_name = '" . $db->sql_escape($chk_name) . "'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$name = $row[$element . '_name'];
			$id = $row[$element . '_id'];
		}
		else
		{
			if (!($cfg = @file("$root_path$element/$element.cfg")))
			{
				$error[] = sprintf($user->lang['REQUIRES_' . $l_element], $reqd_name);
				return false;
			}

			$cfg = parse_cfg_file("$root_path$element/$element.cfg", $cfg);

			$name = $cfg['name'];
			$copyright = $cfg['copyright'];
			$id = 0;

			unset($cfg);
		}
		$db->sql_freeresult($result);
	}

	/**
	* Install/Add style
	*/
	function install_style(&$error, $action, $root_path, &$id, $name, $path, $copyright, $active, $default, &$style_row, $template_root_path = false, $template_path = false, $theme_root_path = false, $theme_path = false, $imageset_root_path = false, $imageset_path = false)
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

		if (utf8_strlen($copyright) > 60)
		{
			$error[] = $user->lang['STYLE_ERR_COPY_LONG'];
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
				$this->install_element($element, $error, $action, (${$element . '_root_path'}) ?: $root_path, $style_row[$element . '_id'], $style_row[$element . '_name'], (${$element . '_path'}) ?: $path, $style_row[$element . '_copyright']);
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
			'style_name'		=> $name,
			'style_copyright'	=> $copyright,
			'style_active'		=> (int) $active,
			'template_id'		=> (int) $style_row['template_id'],
			'theme_id'			=> (int) $style_row['theme_id'],
			'imageset_id'		=> (int) $style_row['imageset_id'],
		];

		$sql = 'INSERT INTO ' . STYLES_TABLE . '
			' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$id = $db->sql_nextid();

		if ($default)
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_style = $id
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
	function install_element($mode, &$error, $action, $root_path, &$id, $name, $path, $copyright)
	{
		global $db, $user;

		// we parse the cfg here (again)
		$cfg_data = parse_cfg_file("$root_path$mode/$mode.cfg");

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

		if (!$name)
		{
			$error[] = $user->lang[$l_type . '_ERR_STYLE_NAME'];
		}

		// Check length settings
		if (utf8_strlen($name) > 30)
		{
			$error[] = $user->lang[$l_type . '_ERR_NAME_LONG'];
		}

		if (utf8_strlen($copyright) > 60)
		{
			$error[] = $user->lang[$l_type . '_ERR_COPY_LONG'];
		}

		// Check if the name already exist
		$sql = "SELECT {$mode}_id
			FROM $sql_from
			WHERE {$mode}_name = '" . $db->sql_escape($name) . "'";
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

			$sql = "SELECT {$mode}_id, {$mode}_name, {$mode}_path$select_bf
				FROM $sql_from
				WHERE {$mode}_name = '" . $db->sql_escape($cfg_data['inherit_from']) . "'
					AND {$mode}_inherits_id = 0";
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
				$inherit_path = $row["{$mode}_path"];
				$inherit_bf = ($mode === 'template') ? $row["bbcode_bitfield"] : false;
			}
		}
		else
		{
			$inherit_id = 0;
			$inherit_path = '';
			$inherit_bf = false;
		}

		if (sizeof($error))
		{
			return false;
		}

		$sql_ary = [
			$mode . '_name'			=> $name,
			$mode . '_copyright'	=> $copyright,
			$mode . '_path'			=> $path,
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
						'template_inherits_id'	=> $inherit_id,
						'template_inherit_path' => $inherit_path,
					];
				}
			break;

			// all the heavy lifting is done later
			case 'theme':
				$sql_ary['theme_mtime'] = (int) filemtime(PHPBB_ROOT_PATH . "styles/$path/theme/stylesheet.css");
			break;

			case 'imageset':
			break;
		}

		$db->sql_transaction('begin');

		$sql = "INSERT INTO $sql_from
			" . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$id = $db->sql_nextid();

		if ($mode == 'imageset')
		{
			$cfg_data = parse_cfg_file("$root_path$mode/imageset.cfg");

			$imageset_definitions = [];
			foreach ($this->imageset_keys as $topic => $key_array)
			{
				$imageset_definitions = array_merge($imageset_definitions, $key_array);
			}

			foreach ($cfg_data as $key => $value)
			{
				if (strpos($value, '*') !== false)
				{
					if (substr($value, -1, 1) === '*')
					{
						[$image_filename, $image_height] = explode('*', $value);
						$image_width = 0;
					}
					else
					{
						[$image_filename, $image_height, $image_width] = explode('*', $value);
					}
				}
				else
				{
					$image_filename = $value;
					$image_height = $image_width = 0;
				}

				if (strpos($key, 'img_') === 0 && $image_filename)
				{
					$key = substr($key, 4);
					if (in_array($key, $imageset_definitions))
					{
						$sql_ary = [
							'image_name'		=> $key,
							'image_filename'	=> str_replace('{PATH}', "styles/$path/imageset/", trim($image_filename)),
							'image_height'		=> (int) $image_height,
							'image_width'		=> (int) $image_width,
							'imageset_id'		=> (int) $id,
							'image_lang'		=> '',
						];
						$db->sql_query('INSERT INTO ' . STYLES_IMAGESET_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
					}
				}
			}
			unset($cfg_data);

			$sql = 'SELECT lang_dir
				FROM ' . LANG_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if (@file_exists("$root_path$mode/{$row['lang_dir']}/imageset.cfg"))
				{
					$cfg_data_imageset_data = parse_cfg_file("$root_path$mode/{$row['lang_dir']}/imageset.cfg");
					foreach ($cfg_data_imageset_data as $image_name => $value)
					{
						if (strpos($value, '*') !== false)
						{
							if (substr($value, -1, 1) === '*')
							{
								[$image_filename, $image_height] = explode('*', $value);
								$image_width = 0;
							}
							else
							{
								[$image_filename, $image_height, $image_width] = explode('*', $value);
							}
						}
						else
						{
							$image_filename = $value;
							$image_height = $image_width = 0;
						}

						if (strpos($image_name, 'img_') === 0 && $image_filename)
						{
							$image_name = substr($image_name, 4);
							if (in_array($image_name, $imageset_definitions))
							{
								$sql_ary = [
									'image_name'		=> $image_name,
									'image_filename'	=> $image_filename,
									'image_height'		=> (int) $image_height,
									'image_width'		=> (int) $image_width,
									'imageset_id'		=> (int) $id,
									'image_lang'		=> $row['lang_dir'],
								];
								$db->sql_query('INSERT INTO ' . STYLES_IMAGESET_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
							}
						}
					}
					unset($cfg_data_imageset_data);
				}
			}
			$db->sql_freeresult($result);
		}

		$db->sql_transaction('commit');

		add_log('admin', 'LOG_' . $l_type . '_ADD_FS', $name);
	}

	/**
	* Checks downwards dependencies
	*
	* @access public
	* @param string $mode The element type to check - only template is supported
	* @param int $id The template id
	* @returns false if no component inherits, array with name, path and id for each subtemplate otherwise
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

		$sql = "SELECT {$mode}_id, {$mode}_name, {$mode}_path
			FROM $sql_from
			WHERE {$mode}_inherits_id = " . (int) $id;
		$result = $db->sql_query($sql);

		$names = [];
		while ($row = $db->sql_fetchrow($result))
		{

			$names[$row["{$mode}_id"]] = [
				"{$mode}_id" => $row["{$mode}_id"],
				"{$mode}_name" => $row["{$mode}_name"],
				"{$mode}_path" => $row["{$mode}_path"],
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
	* @returns false if the component does not inherit, array with name, path and id otherwise
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

		$sql = "SELECT {$mode}_inherits_id
			FROM $sql_from
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

		$super_id = $row["{$mode}_inherits_id"];

		$sql = "SELECT {$mode}_id, {$mode}_name, {$mode}_path
			FROM $sql_from
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
