<?php
/**
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
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
	$module[] = [
		'module_type'		=> 'uninstall',
		'module_title'		=> 'UNINSTALL',
		'module_filename'	=> substr(basename(__FILE__), 0, -4),
		'module_order'		=> 30,
		'module_subs'		=> '',
		'module_stages'		=> ['INTRO', 'REQUIREMENTS', 'DELETE_TABLES', 'FINAL'],
		'module_reqs'		=> ''
	];
}

/**
* Installation
* @package install
*/
class install_uninstall extends module
{
	var $p_master;
	var $page_title;

	function __construct(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $cache, $template, $user;

		if ($user->data['user_type'] != USER_FOUNDER)
		{
			trigger_error('FOUNDER_NEEDED', E_USER_ERROR);
		}

		switch ($sub)
		{
			case 'intro':
				$this->page_title = $user->lang['SUB_INTRO'];

				$template->assign_vars([
					'TITLE'			=> $user->lang['UNINSTALL_INTRO'],
					'BODY'			=> $user->lang['UNINSTALL_INTRO_BODY'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> append_sid(PHPBB_ROOT_PATH . 'install/index.php', "mode=$mode&amp;sub=requirements"),
				]);
			break;

			case 'requirements':
				$this->check_requirements($mode, $sub);
			break;

			case 'delete_tables':
				$this->delete_tables($mode, $sub);
			break;

			case 'final':
				$template->assign_vars([
					'TITLE'		=> $user->lang['UNINSTALL_FINISHED'],
					'BODY'		=> $user->lang['UNINSTALL_FINISHED_EXPLAIN'],
					'L_SUBMIT'	=> $user->lang['GOTO_INDEX'],
					'U_ACTION'	=> append_sid(PHPBB_ROOT_PATH . 'index.php'),
				]);
			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Checks that the server we are installing on meets the requirements for running phpBB
	*/
	function check_requirements($mode, $sub)
	{
		global $user, $template;

		$this->page_title = $user->lang['STAGE_REQUIREMENTS'];

		$template->assign_vars([
			'TITLE'		=> $user->lang['UNINSTALL_REQUIREMENTS'],
			'BODY'		=> $user->lang['UNINSTALL_REQUIREMENTS_EXPLAIN'],
		]);

		$passed = ['installed' => false];

		// Test for basic PHP settings
		$template->assign_block_vars('checks', [
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['UNINSTALL_REQUIREMENTS'],
		]);

		$gallery_version = get_gallery_version();
		if (version_compare($gallery_version, '0.0.0', '>'))
		{
			$passed['installed'] = true;
			$result = '<strong style="color: green;">' . $gallery_version . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $user->lang['NO_INSTALL_FOUND'] . '</strong>';
		}
		$template->assign_block_vars('checks', [
			'TITLE'		=> $user->lang['FOUND_VERSION'],
			'RESULT'	=> $result,

			'S_EXPLAIN'	=> false,
			'S_LEGEND'	=> false,
		]);

		$url = (!in_array(false, $passed)) ? append_sid(PHPBB_ROOT_PATH . 'install/index.php', "mode=$mode&amp;sub=delete_tables") : append_sid(PHPBB_ROOT_PATH . 'install/index.php', "mode=$mode&amp;sub=requirements");
		$submit = (!in_array(false, $passed)) ? $user->lang['UNINSTALL_START'] : $user->lang['INSTALL_TEST'];

		$template->assign_vars([
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $url,
		]);
	}


	/**
	* Load the contents of the schema into the database and then alter it based on what has been input during the installation
	*/
	function delete_tables($mode, $sub)
	{
		global $auth, $cache, $db, $template, $user;

		$this->page_title = $user->lang['STAGE_DELETE_TABLES'];

		$db->sql_return_on_error(true);
		$umil = new phpbb_umil();

		// Delete the tables
		$umil->table_remove([
			[GALLERY_ALBUMS_TABLE],
			[GALLERY_ATRACK_TABLE],
			[GALLERY_COMMENTS_TABLE],
			[GALLERY_CONFIG_TABLE],
			[GALLERY_CONTESTS_TABLE],
			[GALLERY_FAVORITES_TABLE],
			[GALLERY_IMAGES_TABLE],
			[GALLERY_MODSCACHE_TABLE],
			[GALLERY_PERMISSIONS_TABLE],
			[GALLERY_RATES_TABLE],
			[GALLERY_REPORTS_TABLE],
			[GALLERY_ROLES_TABLE],
			[GALLERY_USERS_TABLE],
			[GALLERY_WATCH_TABLE],
			['phpbb_album'],
			['phpbb_album_cat'],
			['phpbb_album_comment'],
			['phpbb_album_config'],
			['phpbb_album_rate'],
		]);

		// Delete columns
		$umil->table_column_remove([
			[LOG_TABLE,		'album_id'],
			[LOG_TABLE,		'image_id'],
			[USERS_TABLE,		'album_id'],
		]);

		$db->sql_return_on_error(false);

		// Delete config
		$config_ary = ['gallery_user_images_profil', 'gallery_personal_album_profil', 'gallery_viewtopic_icon', 'gallery_viewtopic_images', 'gallery_viewtopic_link', 'num_images', 'gallery_total_images'];
		$sql = 'DELETE FROM ' . CONFIG_TABLE . '
			WHERE ' . $db->sql_in_set('config_name', $config_ary);
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . CONFIG_TABLE . "
			WHERE config_name LIKE 'phpbb_gallery_%'";
		$db->sql_query($sql);

		$umil->permission_remove([
			['a_gallery_manage'],
			['a_gallery_albums'],
			['a_gallery_import'],
			['a_gallery_cleanup'],
		]);

		// Purge the auth cache
		$cache->destroy('_acl_options');
		$auth->acl_clear_prefetch();

		$log_modules = "(module_basename = 'logs'
			AND module_class = 'acp'
			AND module_mode = 'gallery')";

		$ucp_modules = "(module_class = 'ucp'
			AND (module_basename = 'gallery'
				OR module_langname = 'UCP_GALLERY'))";

		$acp_modules = "(module_class = 'acp'
			AND (module_basename LIKE 'gallery%'
				OR module_langname = 'PHPBB_GALLERY'))";

		$sql = 'SELECT module_id, module_class
			FROM ' . MODULES_TABLE . '
			WHERE ' . $log_modules . ' OR ' . $ucp_modules . ' OR ' . $acp_modules . '
			ORDER BY left_id DESC';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$umil->module_remove($row['module_class'], false, $row['module_id']);
		}
		$db->sql_freeresult($result);

		$p_class = str_replace(['.', '/', '\\'], '', basename('acp'));
		$cache->destroy('_modules_' . $p_class);

		$p_class = str_replace(['.', '/', '\\'], '', basename('ucp'));
		$cache->destroy('_modules_' . $p_class);

		// Additionally remove sql cache
		$cache->destroy('sql', MODULES_TABLE);

		$db->sql_query('DELETE FROM ' . BBCODES_TABLE . "
			WHERE bbcode_tag = 'album'");
		$cache->destroy('sql', BBCODES_TABLE);

		$template->assign_vars([
			'BODY'		=> $user->lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> append_sid(PHPBB_ROOT_PATH . 'install/index.php', "mode=$mode&amp;sub=final"),
		]);
	}
}
