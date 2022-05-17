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
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_gallery
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $template, $user;

		phpbb_gallery::init();

		$user->add_lang(array('mods/gallery_acp', 'mods/gallery'));
		$this->tpl_name = 'gallery_main';
		add_form_key('acp_gallery');
		$submode = request_var('submode', '');

		switch ($mode)
		{
			case 'overview':
				$title = 'ACP_GALLERY_OVERVIEW';
				$this->page_title = $user->lang[$title];

				$this->overview();
			break;

			case 'import_images':
				$title = 'ACP_IMPORT_ALBUMS';
				$this->page_title = $user->lang[$title];

				$this->import();
			break;

			case 'cleanup':
				$title = 'ACP_GALLERY_CLEANUP';
				$this->page_title = $user->lang[$title];

				$this->cleanup();
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}

	function overview()
	{
		global $auth, $config, $db, $template, $user;

		$action = request_var('action', '');
		$id = request_var('i', '');
		$mode = 'overview';

		if (!confirm_box(true))
		{
			$confirm = false;
			$album_id = 0;
			switch ($action)
			{
				case 'images':
					$confirm = true;
					$confirm_lang = 'RESYNC_IMAGECOUNTS_CONFIRM';
				break;
				case 'personals':
					$confirm = true;
					$confirm_lang = 'CONFIRM_OPERATION';
				break;
				case 'stats':
					$confirm = true;
					$confirm_lang = 'CONFIRM_OPERATION';
				break;
				case 'last_images':
					$confirm = true;
					$confirm_lang = 'CONFIRM_OPERATION';
				break;
				case 'reset_rating':
					$album_id = request_var('reset_album_id', 0);
					$album_data = phpbb_gallery_album::get_info($album_id);
					$confirm = true;
					$confirm_lang = sprintf($user->lang['RESET_RATING_CONFIRM'], $album_data['album_name']);
				break;
				case 'purge_cache':
					$confirm = true;
					$confirm_lang = 'GALLERY_PURGE_CACHE_EXPLAIN';
				break;
				case 'create_pega':
					$confirm = false;
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$username = request_var('username', '', true);
					$user_id = 0;
					if ($username)
					{
						if (!function_exists('user_get_id_name'))
						{
							phpbb_gallery_url::_include('functions_user', 'phpbb');
						}
						user_get_id_name($user_id, $username);
					}
					if (is_array($user_id))
					{
						$user_id = (isset($user_id[0])) ? $user_id[0] : 0;
					}

					$sql = 'SELECT username, user_colour, user_id
						FROM ' . USERS_TABLE . '
						WHERE user_id = ' . $user_id;
					$result = $db->sql_query($sql);
					$user_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					if (!$user_row)
					{
						trigger_error($user->lang['NO_USER'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$image_user = new phpbb_gallery_user($db, $user_row['user_id']);
					$album_id = $image_user->get_data('personal_album_id');
					if ($album_id)
					{
						trigger_error($user->lang('PEGA_ALREADY_EXISTS', $user_row['username']) . adm_back_link($this->u_action), E_USER_WARNING);
					}
					phpbb_gallery_album::generate_personal_album($user_row['username'], $user_row['user_id'], $user_row['user_colour'], $image_user);

					trigger_error($user->lang('PEGA_CREATED', $user_row['username']) . adm_back_link($this->u_action));
				break;
			}

			if ($confirm)
			{
				confirm_box(false, (($album_id) ? $confirm_lang : $user->lang[$confirm_lang]), build_hidden_fields(array(
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> $action,
					'reset_album_id'	=> $album_id,
				)));
			}
		}
		else
		{
			switch ($action)
			{
				case 'images':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$total_images = $total_comments = 0;
					phpbb_gallery_user::update_users('all', array('user_images' => 0));

					$sql = 'SELECT COUNT(image_id) AS num_images, image_user_id AS user_id, SUM(image_comments) AS num_comments
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED . '
							AND image_status <> ' . phpbb_gallery_image::STATUS_ORPHAN . '
						GROUP BY image_user_id';
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$total_images += $row['num_images'];
						$total_comments += $row['num_comments'];

						$image_user = new phpbb_gallery_user($db, $row['user_id'], false);
						$image_user->update_data(array(
							'user_images'		=> $row['num_images'],
						));
					}
					$db->sql_freeresult($result);

					phpbb_gallery_config::set('num_images', $total_images);
					phpbb_gallery_config::set('num_comments', $total_comments);
					trigger_error($user->lang['RESYNCED_IMAGECOUNTS'] . adm_back_link($this->u_action));
				break;

				case 'personals':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					phpbb_gallery_user::update_users('all', array('personal_album_id' => 0));

					$sql = 'SELECT album_id, album_user_id
						FROM ' . GALLERY_ALBUMS_TABLE . '
						WHERE album_user_id <> ' . phpbb_gallery_album::PUBLIC_ALBUM . '
							AND parent_id = 0
						GROUP BY album_user_id, album_id';
					$result = $db->sql_query($sql);

					$number_of_personals = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						$image_user = new phpbb_gallery_user($db, $row['album_user_id'], false);
						$image_user->update_data(array(
							'personal_album_id'		=> $row['album_id'],
						));
						$number_of_personals++;
					}
					$db->sql_freeresult($result);
					phpbb_gallery_config::set('num_pegas', $number_of_personals);

					// Update the config for the statistic on the index
					$sql_array = array(
						'SELECT'		=> 'a.album_id, u.user_id, u.username, u.user_colour',
						'FROM'			=> array(GALLERY_ALBUMS_TABLE => 'a'),

						'LEFT_JOIN'		=> array(
							array(
								'FROM'		=> array(USERS_TABLE => 'u'),
								'ON'		=> 'u.user_id = a.album_user_id',
							),
						),

						'WHERE'			=> 'a.album_user_id <> ' . phpbb_gallery_album::PUBLIC_ALBUM . ' AND a.parent_id = 0',
						'ORDER_BY'		=> 'a.album_id DESC',
					);
					$sql = $db->sql_build_query('SELECT', $sql_array);

					$result = $db->sql_query_limit($sql, 1);
					$newest_pgallery = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					phpbb_gallery_config::set('newest_pega_user_id', $newest_pgallery['user_id']);
					phpbb_gallery_config::set('newest_pega_username', $newest_pgallery['username']);
					phpbb_gallery_config::set('newest_pega_user_colour', $newest_pgallery['user_colour']);
					phpbb_gallery_config::set('newest_pega_album_id', $newest_pgallery['album_id']);

					trigger_error($user->lang['RESYNCED_PERSONALS'] . adm_back_link($this->u_action));
				break;

				case 'stats':
					if (!$auth->acl_get('a_board'))
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// Hopefully this won't take to long! >> I think we must make it batchwise
					$sql = 'SELECT image_id, image_filename
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE filesize_upload = 0';
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql_ary = array(
							'filesize_upload'		=> @filesize(phpbb_gallery_url::path('upload') . $row['image_filename']),
							'filesize_medium'		=> @filesize(phpbb_gallery_url::path('medium') . $row['image_filename']),
							'filesize_cache'		=> @filesize(phpbb_gallery_url::path('thumbnail') . $row['image_filename']),
						);
						$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE ' . $db->sql_in_set('image_id', $row['image_id']);
						$db->sql_query($sql);
					}
					$db->sql_freeresult($result);

					redirect($this->u_action);
				break;

				case 'last_images':
					$sql = 'SELECT album_id
						FROM ' . GALLERY_ALBUMS_TABLE;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						// 5 sql's per album, but you don't run this daily ;)
						phpbb_gallery_album::update_info($row['album_id']);
					}
					$db->sql_freeresult($result);
					trigger_error($user->lang['RESYNCED_LAST_IMAGES'] . adm_back_link($this->u_action));
				break;

				case 'reset_rating':
					$album_id = request_var('reset_album_id', 0);

					$image_ids = array();
					$sql = 'SELECT image_id
						FROM ' . GALLERY_IMAGES_TABLE . '
						WHERE image_album_id = ' . $album_id;
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$image_ids[] = $row['image_id'];
					}
					$db->sql_freeresult($result);

					if (!empty($image_ids))
					{
						phpbb_gallery_image_rating::delete_ratings($image_ids, true);
					}

					trigger_error($user->lang['RESET_RATING_COMPLETED'] . adm_back_link($this->u_action));
				break;

				case 'purge_cache':
					if ($user->data['user_type'] != USER_FOUNDER)
					{
						trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$cache_dir = @opendir(phpbb_gallery_url::path('thumbnail'));
					while ($cache_file = @readdir($cache_dir))
					{
						if (preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $cache_file))
						{
							@unlink(phpbb_gallery_url::path('thumbnail') . $cache_file);
						}
					}
					@closedir($cache_dir);

					$medium_dir = @opendir(phpbb_gallery_url::path('medium'));
					while ($medium_file = @readdir($medium_dir))
					{
						if (preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $medium_file))
						{
							@unlink(phpbb_gallery_url::path('medium') . $medium_file);
						}
					}
					@closedir($medium_dir);

					$sql_ary = array(
						'filesize_medium'		=> 0,
						'filesize_cache'		=> 0,
					);
					$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary);
					$db->sql_query($sql);

					trigger_error($user->lang['PURGED_CACHE'] . adm_back_link($this->u_action));
				break;
			}
		}

		$boarddays = (time() - $config['board_startdate']) / 86400;
		$images_per_day = sprintf('%.2f', phpbb_gallery_config::get('num_images') / $boarddays);

		$sql = 'SELECT COUNT(album_user_id) AS num_albums
			FROM ' . GALLERY_ALBUMS_TABLE . '
			WHERE album_user_id = 0';
		$result = $db->sql_query($sql);
		$num_albums = (int) $db->sql_fetchfield('num_albums');
		$db->sql_freeresult($result);

		$sql = 'SELECT SUM(filesize_upload) AS stat, SUM(filesize_medium) AS stat_medium, SUM(filesize_cache) AS stat_cache
			FROM ' . GALLERY_IMAGES_TABLE;
		$result = $db->sql_query($sql);
		$dir_sizes = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_GALLERY_OVERVIEW'			=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_GALLERY_OVERVIEW'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_GALLERY_OVERVIEW_EXPLAIN'],

			'TOTAL_IMAGES'			=> phpbb_gallery_config::get('num_images'),
			'IMAGES_PER_DAY'		=> $images_per_day,
			'TOTAL_ALBUMS'			=> $num_albums,
			'TOTAL_PERSONALS'		=> phpbb_gallery_config::get('num_pegas'),
			'GUPLOAD_DIR_SIZE'		=> get_formatted_filesize(!empty($dir_sizes['stat']) ? $dir_sizes['stat'] : 0),
			'MEDIUM_DIR_SIZE'		=> get_formatted_filesize(!empty($dir_sizes['stat_medium']) ? $dir_sizes['stat'] : 0),
			'CACHE_DIR_SIZE'		=> get_formatted_filesize(!empty($dir_sizes['stat_cache']) ? $dir_sizes['stat'] : 0),
			'GALLERY_VERSION'		=> phpbb_gallery_config::get('version'),
			'U_FIND_USERNAME'		=> phpbb_gallery_url::append_sid('phpbb', 'memberlist', 'mode=searchuser&amp;form=action_create_pega_form&amp;field=username&amp;select_single=true'),
			'S_SELECT_ALBUM'		=> phpbb_gallery_album::get_albumbox(false, 'reset_album_id', false, false, false, phpbb_gallery_album::PUBLIC_ALBUM, phpbb_gallery_album::TYPE_UPLOAD),

			'S_FOUNDER'				=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,
			'U_ACTION'				=> $this->u_action,
		));
	}

	function import()
	{
		global $db, $template, $user;

		$import_schema = request_var('import_schema', '');
		$images = request_var('images', array(''), true);
		$submit = (isset($_POST['submit'])) ? true : ((empty($images)) ? false : true);

		if ($import_schema)
		{
			if (phpbb_gallery_url::_file_exists($import_schema, 'import', ''))
			{
				include(phpbb_gallery_url::_return_file($import_schema, 'import', ''));
				// Replace the md5 with the ' again and remove the space at the end to prevent \' troubles
				$user_data['username'] = utf8_substr(str_replace("{{$import_schema}}", "'", $user_data['username']), 0, -1);
				$image_name = utf8_substr(str_replace("{{$import_schema}}", "'", $image_name), 0, -1);
			}
			else
			{
				global $phpEx;
				trigger_error(sprintf($user->lang['MISSING_IMPORT_SCHEMA'], ($import_schema . '.' . $phpEx)), E_USER_WARNING);
			}

			$images_loop = 0;
			foreach ($images as $image_src)
			{
				/**
				* Import the images
				*/
				$image_src = str_replace("{{$import_schema}}", "'", $image_src);
				$image_src_full = phpbb_gallery_url::path('import') . $image_src;
				if (file_exists($image_src_full))
				{
					$filetype = getimagesize($image_src_full);
					$filetype_ext = '';

					$error_occured = false;
					switch ($filetype['mime'])
					{
						case 'image/jpeg':
						case 'image/jpg':
						case 'image/pjpeg':
							$filetype_ext = '.jpg';
							$read_function = 'imagecreatefromjpeg';
							if ((substr(strtolower($image_src), -4) != '.jpg') && (substr(strtolower($image_src), -5) != '.jpeg'))
							{
								$this->log_import_error($import_schema, sprintf($user->lang['FILETYPE_MIMETYPE_MISMATCH'], $image_src, $filetype['mime']));
								$error_occured = true;
							}
						break;

						case 'image/png':
						case 'image/x-png':
							$filetype_ext = '.png';
							$read_function = 'imagecreatefrompng';
							if (substr(strtolower($image_src), -4) != '.png')
							{
								$this->log_import_error($import_schema, sprintf($user->lang['FILETYPE_MIMETYPE_MISMATCH'], $image_src, $filetype['mime']));
								$error_occured = true;
							}
						break;

						case 'image/gif':
						case 'image/giff':
							$filetype_ext = '.gif';
							$read_function = 'imagecreatefromgif';
							if (substr(strtolower($image_src), -4) != '.gif')
							{
								$this->log_import_error($import_schema, sprintf($user->lang['FILETYPE_MIMETYPE_MISMATCH'], $image_src, $filetype['mime']));
								$error_occured = true;
							}
						break;

						default:
							$this->log_import_error($import_schema, $user->lang['NOT_ALLOWED_FILE_TYPE']);
							$error_occured = true;
						break;
					}
					$image_filename = md5(unique_id()) . $filetype_ext;

					if (!$error_occured || !@move_uploaded_file($image_src_full, phpbb_gallery_url::path('upload') . $image_filename))
					{
						if (!@copy($image_src_full, phpbb_gallery_url::path('upload') . $image_filename))
						{
							$user->add_lang('posting');
							$this->log_import_error($import_schema, sprintf($user->lang['GENERAL_UPLOAD_ERROR'], phpbb_gallery_url::path('upload') . $image_filename));
							$error_occured = true;
						}
					}

					if (!$error_occured)
					{
						@chmod(phpbb_gallery_url::path('upload') . $image_filename, 0777);
						// The source image is imported, so we delete it.
						@unlink($image_src_full);

						$sql_ary = array(
							'image_filename' 		=> $image_filename,
							'image_desc'			=> '',
							'image_desc_uid'		=> '',
							'image_desc_bitfield'	=> '',
							'image_user_id'			=> $user_data['user_id'],
							'image_username'		=> $user_data['username'],
							'image_username_clean'	=> utf8_clean_string($user_data['username']),
							'image_user_colour'		=> $user_data['user_colour'],
							'image_user_ip'			=> $user->ip,
							'image_time'			=> $start_time + $done_images,
							'image_album_id'		=> $album_id,
							'image_status'			=> phpbb_gallery_image::STATUS_APPROVED,
							'image_exif_data'		=> '',
						);

						$image_tools = new phpbb_gallery_image_file();
						$image_tools->set_image_options(phpbb_gallery_config::get('max_filesize'), phpbb_gallery_config::get('max_height'), phpbb_gallery_config::get('max_width'));
						$image_tools->set_image_data(phpbb_gallery_url::path('upload') . $image_filename);

						// Read exif data from file
						$exif = new phpbb_gallery_exif(phpbb_gallery_url::path('upload') . $image_filename);
						$exif->read();
						$sql_ary['image_exif_data'] = $exif->serialized;
						$sql_ary['image_has_exif'] = $exif->status;
						unset($exif);

						if (($filetype[0] > phpbb_gallery_config::get('max_width')) || ($filetype[1] > phpbb_gallery_config::get('max_height')))
						{
							/**
							* Resize overside images
							*/
							if (phpbb_gallery_config::get('allow_resize'))
							{
								$image_tools->resize_image(phpbb_gallery_config::get('max_width'), phpbb_gallery_config::get('max_height'));
								if ($image_tools->resized)
								{
									$image_tools->write_image(phpbb_gallery_url::path('upload') . $image_filename, phpbb_gallery_config::get('jpg_quality'), true);
								}
							}
						}

						if (!$image_tools->exif_data_force_db && ($sql_ary['image_has_exif'] == phpbb_gallery_exif::DBSAVED))
						{
							// Image was not resized, so we can pull the Exif from the image to save db-memory.
							$sql_ary['image_has_exif'] = phpbb_gallery_exif::AVAILABLE;
							$sql_ary['image_exif_data'] = '';
						}

						// Try to get real filesize from temporary folder (not always working) ;)
						$sql_ary['filesize_upload'] = (@filesize(phpbb_gallery_url::path('upload') . $image_filename)) ? @filesize(phpbb_gallery_url::path('upload') . $image_filename) : 0;

						if ($filename || ($image_name == ''))
						{
							$sql_ary['image_name'] = str_replace("_", " ", utf8_substr($image_src, 0, utf8_strrpos($image_src, '.')));
						}
						else
						{
							$sql_ary['image_name'] = str_replace('{NUM}', $num_offset + $done_images, $image_name);
						}
						$sql_ary['image_name_clean'] = utf8_clean_string($sql_ary['image_name']);

						// Put the images into the database
						$db->sql_query('INSERT INTO ' . GALLERY_IMAGES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
					}
					$done_images++;
				}

				// Remove the image from the list
				unset($images[$images_loop]);
				$images_loop++;
				if ($images_loop == 10)
				{
					// We made 10 images, so we end for this turn
					break;
				}
			}
			if ($images_loop)
			{
				$image_user = new phpbb_gallery_user($db, $user_data['user_id'], false);
				$image_user->update_images($images_loop);

				phpbb_gallery_config::inc('num_images', $images_loop);
				$todo_images = $todo_images - $images_loop;
			}
			phpbb_gallery_album::update_info($album_id);

			if (!$todo_images)
			{
				unlink(phpbb_gallery_url::_return_file($import_schema, 'import', ''));
				$errors = @file_get_contents(phpbb_gallery_url::_return_file($import_schema . '_errors', 'import', ''));
				@unlink(phpbb_gallery_url::_return_file($import_schema . '_errors', 'import', ''));
				if (!$errors)
				{
					trigger_error(sprintf($user->lang['IMPORT_FINISHED'], $done_images) . adm_back_link($this->u_action));
				}
				else
				{
					$errors = explode("\n", $errors);
					trigger_error(sprintf($user->lang['IMPORT_FINISHED_ERRORS'], $done_images - sizeof($errors)) . implode('<br />', $errors) . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}
			else
			{
				// Write the new list
				$this->create_import_schema($import_schema, $album_id, $user_data, $start_time, $num_offset, $done_images, $todo_images, $image_name, $filename, $images);

				// Redirect
				$forward_url = $this->u_action . "&amp;import_schema=$import_schema";
				meta_refresh(1, $forward_url);
				trigger_error(sprintf($user->lang['IMPORT_DEBUG_MES'], $done_images, $todo_images));
			}
		}
		else if ($submit)
		{
			if (!check_form_key('acp_gallery'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}
			if (!$images)
			{
				trigger_error('NO_FILE_SELECTED', E_USER_WARNING);
			}

			// Who is the uploader?
			$username = request_var('username', '', true);
			$user_id = 0;
			if ($username)
			{
				if (!function_exists('user_get_id_name'))
				{
					phpbb_gallery_url::_include('functions_user', 'phpbb');
				}
				user_get_id_name($user_id, $username);
			}
			if (is_array($user_id))
			{
				$user_id = $user_id[0];
			}
			if (!$user_id)
			{
				$user_id = $user->data['user_id'];
			}

			$sql = 'SELECT username, user_colour, user_id
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user_id;
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$user_row)
			{
				trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
			}

			$album_id = request_var('album_id', 0);
			if (isset($_POST['users_pega']))
			{
				if ($user->data['user_id'] != $user_row['user_id'])
				{
					$image_user = new phpbb_gallery_user($db, $user_row['user_id']);
					$album_id = $image_user->get_data('personal_album_id');
					if (!$album_id)
					{
						// The User has no personal album
						$album_id = phpbb_gallery_album::generate_personal_album($user_row['username'], $user_row['user_id'], $user_row['user_colour'], $image_user);
					}
					unset($image_user);
				}
				else
				{
					$album_id = phpbb_gallery::$user->get_data('personal_album_id');
					if (!$album_id)
					{
						$album_id = phpbb_gallery_album::generate_personal_album($user_row['username'], $user_row['user_id'], $user_row['user_colour'], phpbb_gallery::$user);
					}
				}
			}

			// Where do we put them to?
			$sql = 'SELECT album_id, album_name
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_id = ' . $album_id;
			$result = $db->sql_query($sql);
			$album_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if (!$album_row)
			{
				trigger_error('HACKING_ATTEMPT', E_USER_WARNING);
			}

			$start_time = time();
			$import_schema = md5($start_time);
			$filename = (request_var('filename', '') == 'filename') ? true : false;
			$image_name = request_var('image_name', '', true);
			$num_offset = request_var('image_num', 0);

			$this->create_import_schema($import_schema, $album_row['album_id'], $user_row, $start_time, $num_offset, 0, sizeof($images), $image_name, $filename, $images);

			$forward_url = $this->u_action . "&amp;import_schema=$import_schema";
			meta_refresh(2, $forward_url);
			trigger_error('IMPORT_SCHEMA_CREATED');
		}

		$handle = opendir(phpbb_gallery_url::path('import'));
		$files = array();
		while ($file = readdir($handle))
		{
			if (!is_dir(phpbb_gallery_url::path('import') . $file) && (
			((substr(strtolower($file), -4) == '.png') && phpbb_gallery_config::get('allow_png')) ||
			((substr(strtolower($file), -4) == '.gif') && phpbb_gallery_config::get('allow_gif')) ||
			((substr(strtolower($file), -4) == '.jpg') && phpbb_gallery_config::get('allow_jpg')) ||
			((substr(strtolower($file), -5) == '.jpeg') && phpbb_gallery_config::get('allow_jpg'))
			))
			{
				$files[utf8_strtolower($file)] = $file;
			}
		}
		closedir($handle);

		// Sort the files by name again
		ksort($files);
		foreach ($files as $file)
		{
			$template->assign_block_vars('imagerow', array(
				'FILE_NAME'				=> $file,
			));
		}

		$template->assign_vars(array(
			'S_IMPORT_IMAGES'				=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_IMPORT_ALBUMS'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_IMPORT_ALBUMS_EXPLAIN'],
			'L_IMPORT_DIR_EMPTY'			=> sprintf($user->lang['IMPORT_DIR_EMPTY'], phpbb_gallery_url::path('import')),
			'S_ALBUM_IMPORT_ACTION'			=> $this->u_action,
			'S_SELECT_IMPORT' 				=> phpbb_gallery_album::get_albumbox(false, 'album_id', false, false, false, phpbb_gallery_album::PUBLIC_ALBUM, phpbb_gallery_album::TYPE_UPLOAD),
			'U_FIND_USERNAME'				=> phpbb_gallery_url::append_sid('phpbb', 'memberlist', 'mode=searchuser&amp;form=acp_gallery&amp;field=username&amp;select_single=true'),
		));
	}

	function create_import_schema($import_schema, $album_id, $user_row, $start_time, $num_offset, $done_images, $todo_images, $image_name, $filename, $images)
	{
		$import_file = "<?php\n\nif (!defined('IN_PHPBB'))\n{\n	exit;\n}\n\n";
		$import_file .= "\$album_id = " . $album_id . ";\n";
		$import_file .= "\$start_time = " . $start_time . ";\n";
		$import_file .= "\$num_offset = " . $num_offset . ";\n";
		$import_file .= "\$done_images = " . $done_images . ";\n";
		$import_file .= "\$todo_images = " . $todo_images . ";\n";
		// We add a space at the end of the name, to not get troubles with \';
		$import_file .= "\$image_name = '" . str_replace("'", "{{$import_schema}}", $image_name) . " ';\n";
		$import_file .= "\$filename = " . (($filename) ? 'true' : 'false') . ";\n";
		$import_file .= "\$user_data = array(\n";
		$import_file .= "	'user_id'		=> " . $user_row['user_id'] . ",\n";
		// We add a space at the end of the name, to not get troubles with \',
		$import_file .= "	'username'		=> '" . str_replace("'", "{{$import_schema}}", $user_row['username']) . " ',\n";
		$import_file .= "	'user_colour'	=> '" . $user_row['user_colour'] . "',\n";
		$import_file .= ");\n";
		$import_file .= "\$images = array(\n";

		// We need to replace some characters to find the image and not produce syntax errors
		$replace_chars = array("'", "&amp;");
		$replace_with = array("{{$import_schema}}", "&");

		foreach ($images as $image_src)
		{
			$import_file .= "	'" . str_replace($replace_chars, $replace_with, $image_src) . "',\n";
		}
		$import_file .= ");\n\n?" . '>'; // Done this to prevent highlighting editors getting confused!

		// Write to disc
		if ((phpbb_gallery_url::_file_exists($import_schema, 'import', '') && phpbb_gallery_url::_is_writable($import_schema, 'import', '')) || phpbb_gallery_url::_is_writable('', 'import', ''))
		{
			$written = true;
			if (!($fp = @fopen(phpbb_gallery_url::_return_file($import_schema, 'import', ''), 'w')))
			{
				$written = false;
			}
			if (!(@fwrite($fp, $import_file)))
			{
				$written = false;
			}
			@fclose($fp);
		}
	}

	function log_import_error($import_schema, $error)
	{
		$error_file = phpbb_gallery_url::_return_file($import_schema . '_errors', 'import', '');
		$content = @file_get_contents($error_file);
		file_put_contents($error_file, $content .= (($content) ? "\n" : '') . $error);
	}

	function cleanup()
	{
		global $auth, $cache, $db, $template, $user;

		$delete = (isset($_POST['delete'])) ? true : false;
		$prune = (isset($_POST['prune'])) ? true : false;
		$submit = (isset($_POST['submit'])) ? true : false;

		$missing_sources = request_var('source', array(0));
		$missing_entries = request_var('entry', array(''), true);
		$missing_authors = request_var('author', array(0), true);
		$missing_comments = request_var('comment', array(0), true);
		$missing_personals = request_var('personal', array(0), true);
		$personals_bad = request_var('personal_bad', array(0), true);
		$prune_pattern = request_var('prune_pattern', array('' => ''), true);

		if ($prune && empty($prune_pattern))
		{
			$prune_pattern['image_album_id'] = implode(',', request_var('prune_album_ids', array(0)));
			if (isset($_POST['prune_username_check']))
			{
				$usernames = request_var('prune_usernames', '', true);
				$usernames = explode("\n", $usernames);
				$prune_pattern['image_user_id'] = array();
				if (!empty($usernames))
				{
					if (!function_exists('user_get_id_name'))
					{
						phpbb_gallery_url::_include('functions_user', 'phpbb');
					}
					user_get_id_name($user_ids, $usernames);
					$prune_pattern['image_user_id'] = $user_ids;
				}
				if (isset($_POST['prune_anonymous']))
				{
					$prune_pattern['image_user_id'][] = ANONYMOUS;
				}
				$prune_pattern['image_user_id'] = implode(',', $prune_pattern['image_user_id']);
			}
			if (isset($_POST['prune_time_check']))
			{
				$prune_time = explode('-', request_var('prune_time', ''));

				if (sizeof($prune_time) == 3)
				{
					$prune_pattern['image_time'] = @gmmktime(0, 0, 0, (int) $prune_time[1], (int) $prune_time[2], (int) $prune_time[0]);
				}
			}
			if (isset($_POST['prune_comments_check']))
			{
				$prune_pattern['image_comments'] = request_var('prune_comments', 0);
			}
			if (isset($_POST['prune_ratings_check']))
			{
				$prune_pattern['image_rates'] = request_var('prune_ratings', 0);
			}
			if (isset($_POST['prune_rating_avg_check']))
			{
				$prune_pattern['image_rate_avg'] = (int) (request_var('prune_rating_avg', 0.0) * 100);
			}
		}

		$s_hidden_fields = build_hidden_fields(array(
			'source'		=> $missing_sources,
			'entry'			=> $missing_entries,
			'author'		=> $missing_authors,
			'comment'		=> $missing_comments,
			'personal'		=> $missing_personals,
			'personal_bad'	=> $personals_bad,
			'prune_pattern'	=> $prune_pattern,
		));

		if ($submit)
		{
			if ($missing_authors)
			{
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
					SET image_user_id = ' . ANONYMOUS . ",
						image_user_colour = ''
					WHERE " . $db->sql_in_set('image_id', $missing_authors);
				$db->sql_query($sql);
			}
			if ($missing_comments)
			{
				$sql = 'UPDATE ' . GALLERY_COMMENTS_TABLE . '
					SET comment_user_id = ' . ANONYMOUS . ",
						comment_user_colour = ''
					WHERE " . $db->sql_in_set('comment_id', $missing_comments);
				$db->sql_query($sql);
			}
			trigger_error($user->lang['CLEAN_CHANGED'] . adm_back_link($this->u_action));
		}

		if (confirm_box(true))
		{
			$message = array();
			if ($missing_entries)
			{
				$message[] = phpbb_gallery_cleanup::delete_files($missing_entries);
			}
			if ($missing_sources)
			{
				$message[] = phpbb_gallery_cleanup::delete_images($missing_sources);
			}
			if ($missing_authors)
			{
				$message[] = phpbb_gallery_cleanup::delete_author_images($missing_entries);
			}
			if ($missing_comments)
			{
				$message[] = phpbb_gallery_cleanup::delete_author_comments($missing_comments);
			}
			if ($missing_personals || $personals_bad)
			{
				$message = array_merge($message, phpbb_gallery_cleanup::delete_pegas($personals_bad, $missing_personals));

				// Only do this, when we changed something about the albums
				$cache->destroy('_albums');
				phpbb_gallery_auth::set_user_permissions('all', '');
			}
			if ($prune_pattern)
			{
				$message[] = phpbb_gallery_cleanup::prune($prune_pattern);
			}

			if (empty($message))
			{
				trigger_error($user->lang['CLEAN_NO_ACTION'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Make sure the overall image & comment count is correct...
			$sql = 'SELECT COUNT(image_id) AS num_images, SUM(image_comments) AS num_comments
				FROM ' . GALLERY_IMAGES_TABLE . '
				WHERE image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			phpbb_gallery_config::set('num_images', $row['num_images']);
			phpbb_gallery_config::set('num_comments', $row['num_comments']);

			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('sql', GALLERY_COMMENTS_TABLE);
			$cache->destroy('sql', GALLERY_FAVORITES_TABLE);
			$cache->destroy('sql', GALLERY_IMAGES_TABLE);
			$cache->destroy('sql', GALLERY_RATES_TABLE);
			$cache->destroy('sql', GALLERY_REPORTS_TABLE);
			$cache->destroy('sql', GALLERY_WATCH_TABLE);

			$message_string = '';
			foreach ($message as $lang_key)
			{
				$message_string .= (($message_string) ? '<br />' : '') . $user->lang[$lang_key];
			}

			trigger_error($message_string . adm_back_link($this->u_action));
		}
		else if ($delete || $prune || (isset($_POST['cancel'])))
		{
			if (isset($_POST['cancel']))
			{
				trigger_error($user->lang['CLEAN_GALLERY_ABORT'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			else
			{
				$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN'];
				if ($missing_sources)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_SOURCES'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($missing_entries)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_ENTRIES'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($missing_authors)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_AUTHORS'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($missing_comments)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang['CONFIRM_CLEAN_COMMENTS'] . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($personals_bad || $missing_personals)
				{
					$sql = 'SELECT album_name, album_user_id
						FROM ' . GALLERY_ALBUMS_TABLE . '
						WHERE ' . $db->sql_in_set('album_user_id', array_merge($missing_personals, $personals_bad));
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						if (in_array($row['album_user_id'], $personals_bad))
						{
							$personals_bad_names[] = $row['album_name'];
						}
						else
						{
							$missing_personals_names[] = $row['album_name'];
						}
					}
					$db->sql_freeresult($result);
				}
				if ($missing_personals)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang('CONFIRM_CLEAN_PERSONALS', implode(', ', $missing_personals_names)) . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($personals_bad)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang('CONFIRM_CLEAN_PERSONALS_BAD', implode(', ', $personals_bad_names)) . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				if ($prune && empty($prune_pattern))
				{
					trigger_error($user->lang['CLEAN_PRUNE_NO_PATTERN'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				elseif ($prune && $prune_pattern)
				{
					$user->lang['CLEAN_GALLERY_CONFIRM'] = $user->lang('CONFIRM_PRUNE', phpbb_gallery_cleanup::lang_prune_pattern($prune_pattern)) . '<br />' . $user->lang['CLEAN_GALLERY_CONFIRM'];
				}
				confirm_box(false, 'CLEAN_GALLERY', $s_hidden_fields);
			}
		}

		$requested_source = array();
		$sql_array = array(
			'SELECT'		=> 'i.image_id, i.image_name, i.image_filemissing, i.image_filename, i.image_username, u.user_id',
			'FROM'			=> array(GALLERY_IMAGES_TABLE => 'i'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(USERS_TABLE => 'u'),
					'ON'		=> 'u.user_id = i.image_user_id',
				),
			),
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['image_filemissing'])
			{
				$template->assign_block_vars('sourcerow', array(
					'IMAGE_ID'		=> $row['image_id'],
					'IMAGE_NAME'	=> $row['image_name'],
				));
			}
			if (!$row['user_id'])
			{
				$template->assign_block_vars('authorrow', array(
					'IMAGE_ID'		=> $row['image_id'],
					'AUTHOR_NAME'	=> $row['image_username'],
				));
			}
			$requested_source[] = $row['image_filename'];
		}
		$db->sql_freeresult($result);

		$check_mode = request_var('check_mode', '');
		if ($check_mode == 'source')
		{
			$source_missing = array();

			// Reset the status: a image might have been viewed without file but the file is back
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_filemissing = 0';
			$db->sql_query($sql);

			$sql = 'SELECT image_id, image_filename, image_filemissing
				FROM ' . GALLERY_IMAGES_TABLE;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (!file_exists(phpbb_gallery_url::path('upload') . $row['image_filename']))
				{
					$source_missing[] = $row['image_id'];
				}
			}
			$db->sql_freeresult($result);

			if ($source_missing)
			{
				$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . "
					SET image_filemissing = 1
					WHERE " . $db->sql_in_set('image_id', $source_missing);
				$db->sql_query($sql);
			}
		}

		if ($check_mode == 'entry')
		{
			$directory = phpbb_gallery_url::path('upload');
			$handle = opendir($directory);
			while ($file = readdir($handle))
			{
				if (!is_dir($directory . $file) &&
				 ((substr(strtolower($file), '-4') == '.png') || (substr(strtolower($file), '-4') == '.gif') || (substr(strtolower($file), '-4') == '.jpg'))
				 && !in_array($file, $requested_source)
				)
				{
					if ((strpos($file, 'image_not_exist') !== false) || (strpos($file, 'not_authorised') !== false) || (strpos($file, 'no_hotlinking') !== false))
					{
						continue;
					}

					$template->assign_block_vars('entryrow', array(
						'FILE_NAME'				=> $file,
					));
				}
			}
			closedir($handle);
		}


		$sql_array = array(
			'SELECT'		=> 'c.comment_id, c.comment_image_id, c.comment_username, u.user_id',
			'FROM'			=> array(GALLERY_COMMENTS_TABLE => 'c'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(USERS_TABLE => 'u'),
					'ON'		=> 'u.user_id = c.comment_user_id',
				),
			),
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (!$row['user_id'])
			{
				$template->assign_block_vars('commentrow', array(
					'COMMENT_ID'	=> $row['comment_id'],
					'IMAGE_ID'		=> $row['comment_image_id'],
					'AUTHOR_NAME'	=> $row['comment_username'],
				));
			}
		}
		$db->sql_freeresult($result);

		$sql_array = array(
			'SELECT'		=> 'a.album_id, a.album_user_id, a.album_name, u.user_id, a.album_images_real',
			'FROM'			=> array(GALLERY_ALBUMS_TABLE => 'a'),

			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(USERS_TABLE => 'u'),
					'ON'		=> 'u.user_id = a.album_user_id',
				),
			),

			'WHERE'			=> 'a.album_user_id <> ' . phpbb_gallery_album::PUBLIC_ALBUM . ' AND a.parent_id = 0',
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$personalrow = $personal_bad_row = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$album = array(
				'user_id'		=> $row['album_user_id'],
				'album_id'		=> $row['album_id'],
				'album_name'	=> $row['album_name'],
				'images'		=> $row['album_images_real'],
			);
			if (!$row['user_id'])
			{
				$personalrow[$row['album_user_id']] = $album;
			}
			$personal_bad_row[$row['album_user_id']] = $album;
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT ga.album_user_id, ga.album_images_real
			FROM ' . GALLERY_ALBUMS_TABLE . ' ga
			WHERE ga.album_user_id <> ' . phpbb_gallery_album::PUBLIC_ALBUM . '
				AND ga.parent_id <> 0';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (isset($personalrow[$row['album_user_id']]))
			{
				$personalrow[$row['album_user_id']]['images'] = $personalrow[$row['album_user_id']]['images'] + $row['album_images_real'];
			}
			$personal_bad_row[$row['album_user_id']]['images'] = $personal_bad_row[$row['album_user_id']]['images'] + $row['album_images_real'];
		}
		$db->sql_freeresult($result);

		foreach ($personalrow as $key => $row)
		{
			$template->assign_block_vars('personalrow', array(
				'USER_ID'		=> $row['user_id'],
				'ALBUM_ID'		=> $row['album_id'],
				'AUTHOR_NAME'	=> $row['album_name'],
			));
		}
		foreach ($personal_bad_row as $key => $row)
		{
			$template->assign_block_vars('personal_bad_row', array(
				'USER_ID'		=> $row['user_id'],
				'ALBUM_ID'		=> $row['album_id'],
				'AUTHOR_NAME'	=> $row['album_name'],
				'IMAGES'		=> $row['images'],
			));
		}

		$template->assign_vars(array(
			'S_GALLERY_MANAGE_RESTS'		=> true,
			'ACP_GALLERY_TITLE'				=> $user->lang['ACP_GALLERY_CLEANUP'],
			'ACP_GALLERY_TITLE_EXPLAIN'		=> $user->lang['ACP_GALLERY_CLEANUP_EXPLAIN'],
			'CHECK_SOURCE'			=> $this->u_action . '&amp;check_mode=source',
			'CHECK_ENTRY'			=> $this->u_action . '&amp;check_mode=entry',

			'U_FIND_USERNAME'		=> phpbb_gallery_url::append_sid('phpbb', 'memberlist', 'mode=searchuser&amp;form=acp_gallery&amp;field=prune_usernames'),
			'S_SELECT_ALBUM'		=> phpbb_gallery_album::get_albumbox(false, '', false, false, false, phpbb_gallery_album::PUBLIC_ALBUM, phpbb_gallery_album::TYPE_UPLOAD),

			'S_FOUNDER'				=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,
		));
	}
}
