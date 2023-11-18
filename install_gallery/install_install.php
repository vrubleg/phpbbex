<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (!defined('IN_INSTALL'))
{
	exit;
}

if (!empty($setmodules))
{
	$module[] = array(
		'module_type'		=> 'install',
		'module_title'		=> 'INSTALL',
		'module_filename'	=> substr(basename(__FILE__), 0, -4),
		'module_order'		=> 10,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'REQUIREMENTS', 'CREATE_TABLE', 'ADVANCED', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_install extends module
{
	var $p_master;
	var $page_title;

	function __construct(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $cache, $phpbb_root_path, $template, $user;

		if ($user->data['user_type'] != USER_FOUNDER)
		{
			trigger_error('FOUNDER_NEEDED', E_USER_ERROR);
		}

		switch ($sub)
		{
			case 'intro':
				$this->page_title = $user->lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $user->lang['INSTALL_INTRO'],
					'BODY'			=> $user->lang['INSTALL_INTRO_BODY'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> append_sid("{$phpbb_root_path}install/index.php", "mode=$mode&amp;sub=requirements"),
				));
			break;

			case 'requirements':
				$this->check_server_requirements($mode, $sub);
			break;

			case 'create_table':
				$this->load_schema($mode, $sub);
			break;

			case 'advanced':
				$this->obtain_advanced_settings($mode, $sub);
			break;

			case 'final':
				phpbb_gallery_config::set('version', NEWEST_PG_VERSION);
				$cache->purge();

				$template->assign_vars(array(
					'TITLE'		=> $user->lang['INSTALL_CONGRATS'],
					'BODY'		=> sprintf($user->lang['INSTALL_CONGRATS_EXPLAIN'], NEWEST_PG_VERSION),
					'L_SUBMIT'	=> $user->lang['GOTO_GALLERY'],
					'U_ACTION'	=> phpbb_gallery_url::append_sid('index'),
				));
			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Checks that the server we are installing on meets the requirements for running phpBB
	*/
	function check_server_requirements($mode, $sub)
	{
		global $user, $template, $phpbb_root_path;

		$this->page_title = $user->lang['STAGE_REQUIREMENTS'];

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['REQUIREMENTS_TITLE'],
			'BODY'		=> $user->lang['REQUIREMENTS_EXPLAIN'],
		));

		$passed = array('php' => false, 'dirs' => false,);

		// Test for basic PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['PHP_SETTINGS'],
			'LEGEND_EXPLAIN'	=> $user->lang['PHP_SETTINGS_EXP'],
		));

		// Check for GD-Library
		if (@extension_loaded('gd'))
		{
			$passed['php'] = true;
			$result = '<strong style="color:green">' . $user->lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $user->lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang['REQ_GD_LIBRARY'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));

		// Test for optional PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['PHP_SETTINGS_OPTIONAL'],
			'LEGEND_EXPLAIN'	=> $user->lang['PHP_SETTINGS_OPTIONAL_EXP'],
		));

		// Image rotate
		if (function_exists('imagerotate'))
		{
			$result = '<strong style="color:green">' . $user->lang['YES'] . '</strong>';
		}
		else
		{
			$gd_info = gd_info();
			$result = '<strong style="color:red">' . $user->lang['NO'] . '</strong><br />' . sprintf($user->lang['OPTIONAL_IMAGEROTATE_EXP'], $gd_info['GD Version']);
		}
		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang['OPTIONAL_IMAGEROTATE'],
			'TITLE_EXPLAIN'	=> $user->lang['OPTIONAL_IMAGEROTATE_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Exif data
		if (function_exists('exif_read_data'))
		{
			$result = '<strong style="color:green">' . $user->lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $user->lang['NO'] . '</strong><br />' . $user->lang['OPTIONAL_EXIFDATA_EXP'];
		}
		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang['OPTIONAL_EXIFDATA'],
			'TITLE_EXPLAIN'	=> $user->lang['OPTIONAL_EXIFDATA_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Check permissions on files/directories we need access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['FILES_REQUIRED'],
			'LEGEND_EXPLAIN'	=> $user->lang['FILES_REQUIRED_EXPLAIN'],
		));

		$directories = array(
			'import',
			'upload',
			'medium',
			'thumbnail',
		);

		umask(0);

		$passed['dirs'] = true;
		foreach ($directories as $dir)
		{
			$write = false;

			// Now really check
			if (phpbb_gallery_url::_file_exists('', $dir, '') && is_dir(phpbb_gallery_url::_return_file('', $dir, '')))
			{
				if (!phpbb_gallery_url::_is_writable('', $dir, ''))
				{
					@chmod(phpbb_gallery_url::_return_file('', $dir, ''), 0777);
				}
			}

			// Now check if it is writable by storing a simple file
			$fp = @fopen(phpbb_gallery_url::_return_file('', $dir, '') . 'test_lock', 'wb');
			if ($fp !== false)
			{
				$write = true;
			}
			@fclose($fp);

			@unlink(phpbb_gallery_url::_return_file('', $dir, '') . 'test_lock');

			$passed['dirs'] = ($write && $passed['dirs']) ? true : false;

			$write = ($write) ? '<strong style="color:green">' . $user->lang['WRITABLE'] . '</strong>' : '<strong style="color:red">' . $user->lang['UNWRITABLE'] . '</strong>';

			$template->assign_block_vars('checks', array(
				'TITLE'		=> phpbb_gallery_url::_return_file('', $dir . '_noroot', ''),
				'RESULT'	=> $write,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// Check whether the gallery is already installed
		$gallery_version = get_gallery_version();
		if (version_compare($gallery_version, '0.0.0', '>'))
		{
			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $user->lang['FOUND_INSTALL'],
				'LEGEND_EXPLAIN'	=> sprintf($user->lang['FOUND_INSTALL_EXPLAIN'], '<a href="' . append_sid("{$phpbb_root_path}install/index.php", 'mode=update') . '">', '</a>'),
			));
			$template->assign_block_vars('checks', array(
				'TITLE'		=> $user->lang['FOUND_VERSION'],
				'RESULT'	=> '<strong style="color:red">' . $gallery_version . '</strong>',

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		$url = (!in_array(false, $passed)) ? append_sid("{$phpbb_root_path}install/index.php", "mode=$mode&amp;sub=create_table") : append_sid("{$phpbb_root_path}install/index.php", "mode=$mode&amp;sub=requirements");
		$submit = (!in_array(false, $passed)) ? $user->lang['INSTALL_START'] : $user->lang['INSTALL_TEST'];

		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		));
	}


	/**
	* Load the contents of the schema into the database and then alter it based on what has been input during the installation
	*/
	function load_schema($mode, $sub)
	{
		global $cache, $phpbb_root_path, $template, $user;

		$umil = new phpbb_umil();
		$this->page_title = $user->lang['STAGE_CREATE_TABLE'];
		$s_hidden_fields = '';

		// Create the tables
		$umil->table_add(array(
			array(GALLERY_ALBUMS_TABLE,			phpbb_gallery_dbal_schema::get_table_data('albums')),
			array(GALLERY_ATRACK_TABLE,			phpbb_gallery_dbal_schema::get_table_data('albums_track')),
			array(GALLERY_COMMENTS_TABLE,		phpbb_gallery_dbal_schema::get_table_data('comments')),
			array(GALLERY_CONFIG_TABLE,			phpbb_gallery_dbal_schema::get_table_data('config')),
			array(GALLERY_CONTESTS_TABLE,		phpbb_gallery_dbal_schema::get_table_data('contests')),
			array(GALLERY_FAVORITES_TABLE,		phpbb_gallery_dbal_schema::get_table_data('favorites')),
			array(GALLERY_IMAGES_TABLE,			phpbb_gallery_dbal_schema::get_table_data('images')),
			array(GALLERY_MODSCACHE_TABLE,		phpbb_gallery_dbal_schema::get_table_data('modscache')),
			array(GALLERY_PERMISSIONS_TABLE,	phpbb_gallery_dbal_schema::get_table_data('permissions')),
			array(GALLERY_RATES_TABLE,			phpbb_gallery_dbal_schema::get_table_data('rates')),
			array(GALLERY_REPORTS_TABLE,		phpbb_gallery_dbal_schema::get_table_data('reports')),
			array(GALLERY_ROLES_TABLE,			phpbb_gallery_dbal_schema::get_table_data('roles')),
			array(GALLERY_USERS_TABLE,			phpbb_gallery_dbal_schema::get_table_data('users')),
			array(GALLERY_WATCH_TABLE,			phpbb_gallery_dbal_schema::get_table_data('watch')),
		));

		// Create columns
		$umil->table_column_add(array(
			array(LOG_TABLE,		'album_id',			array('UINT', 0)),
			array(LOG_TABLE,		'image_id',			array('UINT', 0)),
		));

		// Set default config
		phpbb_gallery_config::install();

		// Add ACP permissions
		$umil->permission_add(array(
			array('a_gallery_manage'),
			array('a_gallery_albums'),
			array('a_gallery_import'),
			array('a_gallery_cleanup'),
		));
		$cache->destroy('acl_options');

		$submit = $user->lang['NEXT_STEP'];

		$url = append_sid("{$phpbb_root_path}install/index.php", "mode=$mode&amp;sub=advanced");

		$template->assign_vars(array(
			'BODY'		=> $user->lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub)
	{
		global $db, $template, $user, $phpbb_root_path;

		$create = request_var('create', '');
		if ($create)
		{
			$umil = new phpbb_umil();

			// Add modules
			$choosen_acp_module = request_var('acp_module', 0);
			$choosen_log_module = request_var('log_module', 0);
			$choosen_ucp_module = request_var('ucp_module', 0);
			if ($choosen_acp_module < 0)
			{
				$umil->module_add('acp', 0, 'ACP_CAT_DOT_MODS');
				$choosen_acp_module = 'ACP_CAT_DOT_MODS';
			}
			// ACP
			$umil->module_add('acp', $choosen_acp_module, 'PHPBB_GALLERY');
			$umil->module_add('acp', 'PHPBB_GALLERY', array(
				'module_basename'	=> 'gallery',
				'module_langname'	=> 'ACP_GALLERY_OVERVIEW',
				'module_mode'		=> 'overview',
				'module_auth'		=> 'acl_a_gallery_manage',
			));
			$umil->module_add('acp', 'PHPBB_GALLERY', array(
				'module_basename'	=> 'gallery_config',
				'module_langname'	=> 'ACP_GALLERY_CONFIGURE_GALLERY',
				'module_mode'		=> 'main',
				'module_auth'		=> 'acl_a_gallery_manage',
			));
			$umil->module_add('acp', 'PHPBB_GALLERY', array(
				'module_basename'	=> 'gallery_albums',
				'module_langname'	=> 'ACP_GALLERY_MANAGE_ALBUMS',
				'module_mode'		=> 'manage',
				'module_auth'		=> 'acl_a_gallery_albums',
			));
			$umil->module_add('acp', 'PHPBB_GALLERY', array(
				'module_basename'	=> 'gallery_permissions',
				'module_langname'	=> 'ACP_GALLERY_ALBUM_PERMISSIONS',
				'module_mode'		=> 'manage',
				'module_auth'		=> 'acl_a_gallery_albums',
			));
			$umil->module_add('acp', 'PHPBB_GALLERY', array(
				'module_basename'	=> 'gallery_permissions',
				'module_langname'	=> 'ACP_GALLERY_ALBUM_PERMISSIONS_COPY',
				'module_mode'		=> 'copy',
				'module_auth'		=> 'acl_a_gallery_albums',
			));
			$umil->module_add('acp', 'PHPBB_GALLERY', array(
				'module_basename'	=> 'gallery',
				'module_langname'	=> 'ACP_IMPORT_ALBUMS',
				'module_mode'		=> 'import_images',
				'module_auth'		=> 'acl_a_gallery_import',
			));
			$umil->module_add('acp', 'PHPBB_GALLERY', array(
				'module_basename'	=> 'gallery',
				'module_langname'	=> 'ACP_GALLERY_CLEANUP',
				'module_mode'		=> 'cleanup',
				'module_auth'		=> 'acl_a_gallery_cleanup',
			));

			// UCP
			$umil->module_add('ucp', $choosen_ucp_module, 'UCP_GALLERY');
			$umil->module_add('ucp', 'UCP_GALLERY', array(
				'module_basename'	=> 'gallery',
				'module_langname'	=> 'UCP_GALLERY_SETTINGS',
				'module_mode'		=> 'manage_settings',
				'module_auth'		=> '',
			));
			$umil->module_add('ucp', 'UCP_GALLERY', array(
				'module_basename'	=> 'gallery',
				'module_langname'	=> 'UCP_GALLERY_PERSONAL_ALBUMS',
				'module_mode'		=> 'manage_albums',
				'module_auth'		=> '',
			));
			$umil->module_add('ucp', 'UCP_GALLERY', array(
				'module_basename'	=> 'gallery',
				'module_langname'	=> 'UCP_GALLERY_WATCH',
				'module_mode'		=> 'manage_subscriptions',
				'module_auth'		=> '',
			));
			$umil->module_add('ucp', 'UCP_GALLERY', array(
				'module_basename'	=> 'gallery',
				'module_langname'	=> 'UCP_GALLERY_FAVORITES',
				'module_mode'		=> 'manage_favorites',
				'module_auth'		=> '',
			));

			// Logs
			$umil->module_add('acp', $choosen_log_module, array(
				'module_basename'	=> 'logs',
				'module_langname'	=> 'ACP_GALLERY_LOGS',
				'module_mode'		=> 'gallery',
				'module_auth'		=> 'acl_a_viewlogs',
			));

			// Add album-BBCode
			add_bbcode('album');
			$s_hidden_fields = '';
			$url = append_sid("{$phpbb_root_path}install/index.php", "mode=$mode&amp;sub=final");
		}
		else
		{
			$data = array(
				'acp_module'		=> phpbb_gallery_constants::MODULE_DEFAULT_ACP,
				'log_module'		=> phpbb_gallery_constants::MODULE_DEFAULT_LOG,
				'ucp_module'		=> phpbb_gallery_constants::MODULE_DEFAULT_UCP,
			);

			foreach ($this->gallery_config_options as $config_key => $vars)
			{
				if (!is_array($vars) && strpos($config_key, 'legend') === false)
				{
					continue;
				}

				if (strpos($config_key, 'legend') !== false)
				{
					$template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> $user->lang[$vars])
					);

					continue;
				}

				$options = isset($vars['options']) ? $vars['options'] : '';
				$template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> $user->lang[$vars['lang']],
					'S_EXPLAIN'		=> $vars['explain'],
					'S_LEGEND'		=> false,
					'TITLE_EXPLAIN'	=> ($vars['explain']) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '',
					'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
					)
				);
			}
			$s_hidden_fields = '<input type="hidden" name="create" value="true" />';
			$url = append_sid("{$phpbb_root_path}install/index.php", "mode=$mode&amp;sub=advanced");
		}

		$submit = $user->lang['NEXT_STEP'];

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['STAGE_ADVANCED'],
			'BODY'		=> $user->lang['STAGE_ADVANCED_EXPLAIN'],
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $gallery_config_options = array(
		'legend1'				=> 'MODULES_PARENT_SELECT',
		'acp_module'			=> array('lang' => 'MODULES_SELECT_4ACP', 'type' => 'select', 'options' => 'module_select(\'acp\', 31, \'ACP_CAT_DOT_MODS\')', 'explain' => false),
		'log_module'			=> array('lang' => 'MODULES_SELECT_4LOG', 'type' => 'select', 'options' => 'module_select(\'acp\', 25, \'ACP_FORUM_LOGS\')', 'explain' => false),
		'ucp_module'			=> array('lang' => 'MODULES_SELECT_4UCP', 'type' => 'select', 'options' => 'module_select(\'ucp\', 0, \'\')', 'explain' => false),
	);
}
