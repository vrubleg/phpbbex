<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*
* NOTE: This is only used for MySQL, as phpbb2 Album MOD by Smartor only was available for MySQL, so I do not stirctly care about the DBAL (e.g.: $db->sql_build_query).
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
		'module_type'		=> 'convert',
		'module_title'		=> 'CONVERT',
		'module_filename'	=> substr(basename(__FILE__), 0, -strlen($phpEx)-1),
		'module_order'		=> 30,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'REQUIREMENTS', 'CREATE_TABLE', 'IN_PROGRESS', 'ADVANCED', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
* @package install
*/
class install_convert extends module
{
	var $batch_size = 500;

	function __construct(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $cache, $phpbb_root_path, $phpEx, $template, $user;

		if ($user->data['user_type'] != USER_FOUNDER)
		{
			trigger_error('FOUNDER_NEEDED', E_USER_ERROR);
		}

		switch ($sub)
		{
			case 'intro':
				$this->page_title = $user->lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $user->lang['CONVERT_SMARTOR_INTRO'],
					'BODY'			=> $user->lang['CONVERT_SMARTOR_INTRO_BODY'],
					'L_SUBMIT'		=> $user->lang['NEXT_STEP'],
					'U_ACTION'		=> append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=requirements"),
				));
			break;

			case 'requirements':
				$this->check_server_requirements($mode, $sub);

			break;

			case 'create_table':
				$this->load_schema($mode, $sub);
			break;

			case 'in_progress':
				$this->convert_data($mode, $sub);
			break;

			case 'advanced':
				$this->obtain_advanced_settings($mode, $sub);
			break;

			case 'final':
				phpbb_gallery_config::set('version', NEWEST_PG_VERSION);
				$cache->purge();

				$template->assign_vars(array(
					'TITLE'		=> $user->lang['INSTALL_CONGRATS'],
					'BODY'		=> sprintf($user->lang['CONVERT_COMPLETE_EXPLAIN'], NEWEST_PG_VERSION),
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
		global $user, $template, $phpbb_root_path, $phpEx, $db;

		$this->page_title = $user->lang['STAGE_REQUIREMENTS'];

		$template->assign_vars(array(
			'TITLE'		=> $user->lang['REQUIREMENTS_TITLE'],
			'BODY'		=> $user->lang['REQUIREMENTS_EXPLAIN'],
		));

		$passed = array('php' => false, 'dirs' => false,);
		$convert_tables = array('album', 'album_cat', 'album_comment', 'album_config', 'album_rate');
		foreach ($convert_tables as $table)
		{
			$passed[$table] = false;
		}

		// Test for basic PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['PHP_SETTINGS'],
			'LEGEND_EXPLAIN'	=> $user->lang['PHP_SETTINGS_EXP'],
		));

		// Check for GD-Library
		if (@extension_loaded('gd') || can_load_dll('gd'))
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

		// Check for php version
		if (version_compare(PHP_VERSION, REQUIRED_PHP_VERSION) >= 0)
		{
			$result = '<strong style="color:green">' . $user->lang['YES'] . ' - ' . PHP_VERSION . '</strong>';
		}
		else
		{
			$passed['php'] = false;
			$result = '<strong style="color:red">' . $user->lang['NO'] . ' - ' . PHP_VERSION . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang('REQ_PHP_VERSION', REQUIRED_PHP_VERSION),
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));

		// Check for phpBB version
		if (version_compare(PHPBB_VERSION, REQUIRED_PHPBB_VERSION) >= 0)
		{
			$result = '<strong style="color:green">' . $user->lang['YES'] . ' - ' . PHPBB_VERSION . '</strong>';
		}
		else
		{
			$passed['phpbb'] = false;
			$result = '<strong style="color:red">' . $user->lang['NO'] . ' - ' . PHPBB_VERSION . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang('REQ_PHPBB_VERSION', REQUIRED_PHPBB_VERSION),
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

		// Check permissions on files/directories we need access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $user->lang['TABLE_PREFIX'],
		));

		$convert_prefix = request_var('convert_prefix', '');
		$template->assign_block_vars('checks', array(
			'TITLE'			=> $user->lang['TABLE_PREFIX_EXPLAIN'],
			'RESULT'		=> '<input id="convert_prefix" size="40" maxlength="255" name="convert_prefix" value="' . $convert_prefix . '" type="text" />',

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));
		if ($convert_prefix)
		{
			$convert_tables = array('album', 'album_cat', 'album_comment', 'album_config', 'album_rate');

			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $user->lang['CHECK_TABLES'],
				'LEGEND_EXPLAIN'	=> $user->lang['CHECK_TABLES_EXPLAIN'],
			));
			foreach ($convert_tables as $table)
			{
				$sql = 'SELECT * FROM ' . $convert_prefix . $table;
				if (@$db->sql_query_limit($sql, 1) == true)
				{
					$result = '<strong style="color:green">' . $user->lang['TABLE_EXISTS'] . '</strong>';
					$passed[$table] = true;
				}
				else
				{
					$result = '<strong style="color:red">' . $user->lang['TABLE_MISSING'] . '</strong>';
					$passed[$table] = false;
				}

				$template->assign_block_vars('checks', array(
					'TITLE'			=> '<strong>' . $convert_prefix . $table . '</strong>',
					'TITLE_EXPLAIN'	=> $user->lang['TABLE_' . strtoupper($table)],
					'RESULT'		=> $result,

					'S_EXPLAIN'		=> true,
					'S_LEGEND'		=> false,
				));
			}
		}

		$url = (!in_array(false, $passed) && $convert_prefix) ? append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;convert_prefix=$convert_prefix&amp;sub=create_table") : append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=requirements");
		$submit = (!in_array(false, $passed) && $convert_prefix) ? $user->lang['INSTALL_START'] : $user->lang['INSTALL_TEST'];

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
		global $cache, $phpbb_root_path, $phpEx, $template, $user;
		include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

		$umil = new umil(true);
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

		$convert_prefix = request_var('convert_prefix', '');
		$template->assign_vars(array(
			'BODY'		=> $user->lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix"),
		));
	}

	/**
	* Load the contents of the old tables into the database
	*/
	function convert_data($mode, $sub)
	{
		global $db, $template, $user, $phpbb_root_path, $phpEx;

		function decode_ip($int_ip)
		{
			$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
			$phpbb3_ip = hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);

			return $phpbb3_ip;
		}

		$this->page_title = $user->lang['STAGE_IN_PROGRESS'];

		$convert_prefix = request_var('convert_prefix', '');
		$step = request_var('step', 0);
		$next_update_url = $body = '';

		switch ($step)
		{
			case 0:
				$batch_ary = $rates_ary = array();
				$current_batch = 1;
				$current_batch_size = 1;

				$sql = 'SELECT *
					FROM ' . $convert_prefix . 'album_rate
					ORDER BY rate_pic_id';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					if ($row['rate_user_id'] < 0)
					{
						// guest ratings are not supported.
						continue;
					}
					$ary = array(
						'rate_image_id'					=> $row['rate_pic_id'],
						'rate_user_id'					=> ($row['rate_user_id'] < 0) ? ANONYMOUS : $row['rate_user_id'],
						'rate_user_ip'					=> decode_ip($row['rate_user_ip']),
						'rate_point'					=> $row['rate_point'],
					);

					if (in_array($ary['rate_image_id'] . '-' . $ary['rate_user_id'], $rates_ary))
					{
						// Duplicated key
						continue;
					}
					$rates_ary[] = $ary['rate_image_id'] . '-' . $ary['rate_user_id'];

					$batch_ary[$current_batch][] = $ary;

					$current_batch_size++;
					if ($current_batch_size = $this->batch_size)
					{
						$current_batch_size = 1;
						$current_batch++;
					}
				}
				$db->sql_freeresult($result);

				foreach ($batch_ary as $batch => $ary)
				{
					$db->sql_multi_insert(GALLERY_RATES_TABLE, $ary);
				}

				$body = $user->lang['CONVERTED_RATES'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix&amp;step=1");
			break;

			case 1:
				$batch_ary = array();
				$current_batch = 1;
				$current_batch_size = 1;

				$sql = 'SELECT c.*, u.user_colour
					FROM ' . $convert_prefix . 'album_comment c
					LEFT JOIN ' . USERS_TABLE . ' u
						ON c.comment_user_id = u.user_id
					ORDER BY c.comment_id';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$row['comment_uid'] = $row['comment_options'] = $row['comment_bitfield'] = '';
					$row['comment'] = $row['comment_text'];
					$comment_text_data = generate_text_for_edit($row['comment'], $row['comment_uid'], $row['comment_options']);
					$comment_data = array(
						'comment_id'			=> $row['comment_id'],
						'comment_image_id'		=> $row['comment_pic_id'],
						'comment_user_id'		=> ($row['comment_user_id'] < 0) ? ANONYMOUS : $row['comment_user_id'],
						'comment_username'		=> $row['comment_username'],
						'comment_user_colour'	=> (isset($row['user_colour'])) ? $row['user_colour'] : '',
						'comment_user_ip'		=> decode_ip($row['comment_user_ip']),
						'comment_time'			=> $row['comment_time'],
						'comment'				=> $comment_text_data['text'],
						'comment_uid'			=> '',
						'comment_bitfield'		=> '',
						'comment_options'		=> 7,
						'comment_edit_time'		=> (isset($row['comment_edit_time']) ? $row['comment_edit_time'] : 0),
						'comment_edit_count'	=> (isset($row['comment_edit_count']) ? $row['comment_edit_count'] : 0),
						'comment_edit_user_id'	=> (isset($row['comment_edit_user_id']) ? ($row['comment_edit_user_id'] < 0) ? ANONYMOUS : $row['comment_edit_user_id'] : 0),
					);
					generate_text_for_storage($comment_data['comment'], $comment_data['comment_uid'], $comment_data['comment_bitfield'], $comment_data['comment_options'], 1, 1, 1);
					unset($comment_data['comment_options']);

					$batch_ary[$current_batch][] = $comment_data;

					$current_batch_size++;
					if ($current_batch_size = $this->batch_size)
					{
						$current_batch_size = 1;
						$current_batch++;
					}
				}
				$db->sql_freeresult($result);

				foreach ($batch_ary as $batch => $ary)
				{
					$db->sql_multi_insert(GALLERY_COMMENTS_TABLE, $ary);
				}

				$body = $user->lang['CONVERTED_COMMENTS'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix&amp;step=2");
			break;

			case 2:
				$personal_albums = '0';
				$left_id = 1;
				$batch_ary = array();
				$current_batch = 1;
				$current_batch_size = 1;

				$sql = 'SELECT *
					FROM ' . $convert_prefix . 'album_cat
					ORDER BY cat_order';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$row['cat_user_id'] = (isset($row['cat_user_id']) ? $row['cat_user_id'] : 0);
					if ($row['cat_user_id'] != 0)
					{
						$personal_albums .= '_' . $row['cat_id'];
					}
					else
					{
						$row['album_desc_uid'] = $row['album_desc_options'] = $row['album_desc_bitfield'] = '';
						$row['album_desc'] = $row['cat_desc'];
						$album_desc_data = generate_text_for_edit($row['album_desc'], $row['album_desc_uid'], $row['album_desc_options']);
						$album_data = array(
							'album_id'						=> $row['cat_id'],
							'album_name'					=> $row['cat_title'],
							'parent_id'						=> 0,
							'left_id'						=> $left_id,
							'right_id'						=> $left_id + 1,
							'album_parents'					=> '',
							'album_type'					=> phpbb_gallery_album::TYPE_CAT,
							'album_status'					=> phpbb_gallery_album::STATUS_OPEN,
							'album_desc'					=> $album_desc_data['text'],
							'album_desc_uid'				=> '',
							'album_desc_bitfield'			=> '',
							'album_desc_options'			=> 7,
						);
						generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], true, true, true);
						$left_id = $left_id + 2;

						$batch_ary[$current_batch][] = $album_data;

						$current_batch_size++;
						if ($current_batch_size = $this->batch_size)
						{
							$current_batch_size = 1;
							$current_batch++;
						}
					}
				}
				$db->sql_freeresult($result);

				foreach ($batch_ary as $batch => $ary)
				{
					$db->sql_multi_insert(GALLERY_ALBUMS_TABLE, $ary);
				}

				$body = $user->lang['CONVERTED_ALBUMS'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix&amp;step=3&amp;personal_albums=$personal_albums");
			break;

			case 3:
				$personal_albums = explode('_', request_var('personal_albums', ''));
				$batch_ary = array();
				$current_batch = 1;
				$current_batch_size = 1;

				$sql = 'SELECT i.*, u.user_colour, u.username
					FROM ' . $convert_prefix . 'album i
					LEFT JOIN ' . USERS_TABLE . ' u
						ON i.pic_user_id = u.user_id
					ORDER BY i.pic_id';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$row['image_desc_uid'] = $row['image_desc_options'] = $row['image_desc_bitfield'] = '';
					$row['image_desc'] = $row['pic_desc'];
					$image_desc_data = generate_text_for_edit($row['image_desc'], $row['image_desc_uid'], $row['image_desc_options']);
					$image_data = array(
						'image_id'				=> $row['pic_id'],
						'image_filename'		=> $row['pic_filename'],
						'image_name'			=> $row['pic_title'],
						'image_name_clean'		=> utf8_clean_string($row['pic_title']),
						'image_desc'			=> $image_desc_data['text'],
						'image_desc_uid'		=> '',
						'image_desc_bitfield'	=> '',
						'image_desc_options'	=> 7,
						'image_user_id'			=> ($row['pic_user_id'] < 0) ? ANONYMOUS : $row['pic_user_id'],
						'image_username'		=> (isset($row['username'])) ? $row['username'] : $row['pic_username'],
						'image_username_clean'	=> (isset($row['username'])) ? utf8_clean_string($row['username']) : utf8_clean_string($row['pic_username']),
						'image_user_colour'		=> (isset($row['user_colour'])) ? $row['user_colour'] : '',
						'image_user_ip'			=> decode_ip($row['pic_user_ip']),
						'image_time'			=> $row['pic_time'],
						'image_album_id'		=> (in_array($row['pic_cat_id'], $personal_albums) ? 0 : $row['pic_cat_id']),
						'image_view_count'		=> $row['pic_view_count'],
						'image_status'			=> ($row['pic_lock']) ? phpbb_gallery_image::STATUS_LOCKED : $row['pic_approval'],
						'image_reported'		=> 0,
						'image_exif_data'		=> '',
					);
					generate_text_for_storage($image_data['image_desc'], $image_data['image_desc_uid'], $image_data['image_desc_bitfield'], $image_data['image_desc_options'], true, true, true);
					unset($image_data['image_desc_options']);
					$batch_ary[$current_batch][] = $image_data;

					$current_batch_size++;
					if ($current_batch_size = $this->batch_size)
					{
						$current_batch_size = 1;
						$current_batch++;
					}
				}
				$db->sql_freeresult($result);

				foreach ($batch_ary as $batch => $ary)
				{
					$db->sql_multi_insert(GALLERY_IMAGES_TABLE, $ary);
				}

				$body = $user->lang['CONVERTED_IMAGES'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix&amp;step=4");
			break;

			case 4:
				$personal_albums = 0;
				$batch_ary = array();
				$current_batch = 1;
				$current_batch_size = 1;

				$sql = 'SELECT i.image_id, i.image_username, image_user_id
					FROM ' . GALLERY_IMAGES_TABLE . " AS i
					WHERE image_album_id = 0
					GROUP BY i.image_user_id DESC";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$album_data = array(
						'album_name'					=> $row['image_username'],
						'parent_id'						=> 0,
						'album_desc_options'			=> 7,
						'album_desc'					=> '',
						'album_parents'					=> '',
						'album_type'					=> phpbb_gallery_album::TYPE_UPLOAD,
						'album_status'					=> phpbb_gallery_album::STATUS_OPEN,
						'album_user_id'					=> ($row['image_user_id'] < 0) ? ANONYMOUS : $row['image_user_id'],
					);
					$db->sql_query('INSERT INTO ' . GALLERY_ALBUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $album_data));
					$new_personal_album_id = $db->sql_nextid();
					$personal_albums++;

					$user_data = array(
						'personal_album_id'		=> $new_personal_album_id,
						'user_id'				=> $row['image_user_id'],
						'user_permissions'		=> '',
					);
					$batch_ary[$current_batch][] = $user_data;

					$current_batch_size++;
					if ($current_batch_size = $this->batch_size)
					{
						$current_batch_size = 1;
						$current_batch++;
					}

					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . "
							SET image_album_id = $new_personal_album_id
							WHERE image_album_id = 0
								AND image_user_id  = " . (int) $row['image_user_id'];
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				if (sizeof($batch_ary))
				{
					foreach ($batch_ary as $batch => $ary)
					{
						$db->sql_multi_insert(GALLERY_USERS_TABLE, $ary);
					}
				}

				// Update the config for the statistic on the index
				$sql = 'SELECT a.album_id, u.user_id, u.username, u.user_colour
					FROM ' . GALLERY_ALBUMS_TABLE . ' a
					LEFT JOIN ' . USERS_TABLE . ' u
						ON u.user_id = a.album_user_id
					WHERE a.album_user_id <> 0
						AND a.parent_id = 0
					ORDER BY a.album_id DESC';
				$result = $db->sql_query_limit($sql, 1);
				$newest_pgallery = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				phpbb_gallery_config::set('newest_pega_user_id', $newest_pgallery['user_id']);
				phpbb_gallery_config::set('newest_pega_username', $newest_pgallery['username']);
				phpbb_gallery_config::set('newest_pega_user_colour', $newest_pgallery['user_colour']);
				phpbb_gallery_config::set('newest_pega_album_id', $newest_pgallery['album_id']);
				phpbb_gallery_config::set('num_pegas', $personal_albums);

				$body = $user->lang['CONVERTED_PERSONALS'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix&amp;step=5");
			break;

			case 5:
				//Step 5.1: Number of public images and last_image_id
				$sql = 'SELECT COUNT(image_id) images, MAX(image_id) last_image_id, image_album_id
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED . '
					GROUP BY image_album_id';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'album_images'			=> $row['images'],
						'album_last_image_id'	=> $row['last_image_id'],
					);
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE ' . $db->sql_in_set('album_id', $row['image_album_id']);
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				//Step 5.2: Number of real images and album_type
				$sql = 'SELECT COUNT(image_id) images, image_album_id
					FROM ' . GALLERY_IMAGES_TABLE . '
					GROUP BY image_album_id';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'album_images_real'	=> $row['images'],
						'album_type'		=> phpbb_gallery_album::TYPE_UPLOAD,
					);
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE ' . $db->sql_in_set('album_id', $row['image_album_id']);
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				//Step 5.3: Last image data
				$sql = 'SELECT a.album_id, a.album_last_image_id, i.image_time, i.image_name, i.image_user_id, i.image_username, i.image_user_colour, u.user_colour
					FROM ' . GALLERY_ALBUMS_TABLE . ' a
					LEFT JOIN ' . GALLERY_IMAGES_TABLE . ' i
						ON a.album_last_image_id = i.image_id
					LEFT JOIN ' . USERS_TABLE . ' u
						ON a.album_user_id = u.user_id
					WHERE a.album_last_image_id > 0';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'album_last_image_time'		=> $row['image_time'],
						'album_last_image_name'		=> $row['image_name'],
						'album_last_username'		=> $row['image_username'],
						'album_last_user_colour'	=> isset($row['user_colour']) ? $row['user_colour'] : '',
						'album_last_user_id'		=> $row['image_user_id'],
					);
					$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE ' . $db->sql_in_set('album_id', $row['album_id']);
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				$body = $user->lang['CONVERTED_RESYNC_ALBUMS'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix&amp;step=6");
			break;

			case 6:
				$num_images = 0;
				$batch_ary = array();
				$current_batch = 1;
				$current_batch_size = 1;

				$sql = 'SELECT u.user_id, COUNT(i.image_id) AS images
					FROM ' . USERS_TABLE . ' u
					LEFT JOIN ' . GALLERY_IMAGES_TABLE . ' i
						ON i.image_user_id = u.user_id
							AND i.image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED . '
					GROUP BY i.image_user_id';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$num_images = $num_images + $row['images'];
					$sql = 'UPDATE ' . GALLERY_USERS_TABLE . ' SET user_images = ' . (int) $row['images'] . '
						WHERE user_id = ' . (int) $row['user_id'];
					$db->sql_query($sql);
					if ($db->sql_affectedrows() <= 0)
					{
						$ary = array(
							'user_id'				=> $row['user_id'],
							'user_images'			=> $row['images'],
							'user_permissions'		=> '',
						);
						$batch_ary[$current_batch][] = $ary;

						$current_batch_size++;
						if ($current_batch_size = $this->batch_size)
						{
							$current_batch_size = 1;
							$current_batch++;
						}
					}
				}
				$db->sql_freeresult($result);
				if (sizeof($batch_ary))
				{
					foreach ($batch_ary as $batch => $ary)
					{
						$db->sql_multi_insert(GALLERY_USERS_TABLE, $ary);
					}
				}
				phpbb_gallery_config::set('num_images', $num_images);

				$body = $user->lang['CONVERTED_RESYNC_COUNTS'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix&amp;step=7");
			break;

			case 7:
				$sql = 'SELECT rate_image_id, COUNT(rate_user_ip) image_rates, AVG(rate_point) image_rate_avg, SUM(rate_point) image_rate_points
					FROM ' . GALLERY_RATES_TABLE . '
					GROUP BY rate_image_id';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_rates = ' . $row['image_rates'] . ',
							image_rate_points = ' . $row['image_rate_points'] . ',
							image_rate_avg = ' . round($row['image_rate_avg'], 2) * 100 . '
						WHERE image_id = ' . $row['rate_image_id'];
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				$body = $user->lang['CONVERTED_RESYNC_RATES'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix&amp;step=8");
			break;

			case 8:
				$sql = 'SELECT COUNT(comment_id) comments, MAX(comment_id) image_last_comment, comment_image_id
					FROM ' . GALLERY_COMMENTS_TABLE . "
					GROUP BY comment_image_id";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET image_comments = ' . $row['comments'] . ',
						image_last_comment = ' . $row['image_last_comment'] . '
						WHERE ' . $db->sql_in_set('image_id', $row['comment_image_id']);
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				$num_comments = 0;
				$sql = 'SELECT SUM(image_comments) comments
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED;
				$result = $db->sql_query($sql);
				$num_comments = (int) $db->sql_fetchfield('comments');
				$db->sql_freeresult($result);
				phpbb_gallery_config::set('num_comments', $num_comments);

				$body = $user->lang['CONVERTED_RESYNC_COMMENTS'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=in_progress&amp;convert_prefix=$convert_prefix&amp;step=9");
			break;

			case 9:
				// Misc updates
				// Set the lastmark to the current time of update
				$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
					SET user_lastmark = ' . time() . '
					WHERE user_lastmark = 0';
				$db->sql_query($sql);

				$body = $user->lang['CONVERTED_MISC'];
				$next_update_url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=advanced&amp;convert_prefix=$convert_prefix");
			break;
		}


		$template->assign_vars(array(
			'BODY'		=> $body,
			'L_SUBMIT'	=> $user->lang['NEXT_STEP'],
			'S_HIDDEN'	=> '',
			'U_ACTION'	=> $next_update_url,
		));
	}

	/**
	* Provide an opportunity to customise some advanced settings during the install
	* in case it is necessary for them to be set to access later
	*/
	function obtain_advanced_settings($mode, $sub)
	{
		global $db, $template, $user, $phpbb_root_path, $phpEx;

		$create = request_var('create', '');
		if ($create)
		{
			$umil = new umil(true);

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
			$url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=final");
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
			$url = append_sid("{$phpbb_root_path}install/index.$phpEx", "mode=$mode&amp;sub=advanced");
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

?>