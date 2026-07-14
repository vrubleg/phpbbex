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

/**
* @package ucp
*/
class ucp_gallery
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $db, $template, $user;

		phpbb_gallery::init();

		$user->add_lang(['mods/gallery', 'mods/gallery_acp', 'mods/gallery_mcp', 'mods/gallery_ucp']);
		$this->tpl_name = 'gallery/ucp_gallery';
		add_form_key('ucp_gallery');

		$mode = request_var('mode', '');
		$action = request_var('action', '');
		$cancel = isset($_POST['cancel']);
		if ($cancel)
		{
			$action = '';
		}
		switch ($mode)
		{
			case 'manage_albums':
				switch ($action)
				{
					case 'edit':
						$title = 'EDIT_PERSONAL_ALBUM';
						$this->page_title = $user->lang[$title];
						$this->edit_album();
					break;

					case 'delete':
						$title = 'DELETE_ALBUM';
						$this->page_title = $user->lang[$title];
						$this->delete_album();
					break;

					default:
						$title = 'UCP_GALLERY_PERSONAL_ALBUMS';
						$this->page_title = $user->lang[$title];
						if (!phpbb_gallery::$user->get_data('personal_album_id'))
						{
							phpbb_gallery_url::redirect('album', 'mode=personal');
						}
						else
						{
							$this->edit_album();
						}
					break;
				}
			break;

			case 'manage_subscriptions':
				$title = 'UCP_GALLERY_WATCH';
				$this->page_title = $user->lang[$title];
				$this->manage_subscriptions();
			break;

			case 'manage_favorites':
				$title = 'UCP_GALLERY_FAVORITES';
				$this->page_title = $user->lang[$title];
				$this->manage_favorites();
			break;

			case 'manage_settings':
			default:
				$title = 'UCP_GALLERY_SETTINGS';
				$this->page_title = $user->lang[$title];
				$this->set_personal_settings();
			break;
		}
	}

	function set_personal_settings()
	{
		global $db, $template, $user;

		$submit = isset($_POST['submit']);

		if($submit)
		{
			$gallery_settings = [
				'watch_own'             => request_var('watch_own',     false),
				'watch_com'             => request_var('watch_com',     false),
				'watch_favo'            => request_var('watch_favo',    false),
				'user_viewexif'         => request_var('viewexifs',     false),
				'user_allow_comments'   => request_var('allow_comments',false),
			];
			if (!phpbb_gallery_config::get('allow_comments') || !phpbb_gallery_config::get('comment_user_control'))
			{
				unset($gallery_settings['user_allow_comments']);
			}

			phpbb_gallery::$user->update_data($gallery_settings);

			meta_refresh(3, $this->u_action);
			trigger_error($user->lang['WATCH_CHANGED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'));
		}


		$template->assign_vars([
			'S_PERSONAL_SETTINGS'   => true,
			'S_UCP_ACTION'          => $this->u_action,

			'L_TITLE'           => $user->lang['UCP_GALLERY_SETTINGS'],
			'L_TITLE_EXPLAIN'   => $user->lang['WATCH_NOTE'],

			'S_WATCH_OWN'       => phpbb_gallery::$user->get_data('watch_own'),
			'S_WATCH_COM'       => phpbb_gallery::$user->get_data('watch_com'),
			'S_WATCH_FAVO'      => phpbb_gallery::$user->get_data('watch_favo'),
			'S_VIEWEXIFS'       => phpbb_gallery::$user->get_data('user_viewexif'),
			'S_ALLOW_COMMENTS'  => phpbb_gallery::$user->get_data('user_allow_comments'),
			'S_COMMENTS_ENABLED'=> phpbb_gallery_config::get('allow_comments') && phpbb_gallery_config::get('comment_user_control'),
		]);
	}

	function edit_album()
	{
		global $cache, $db, $template, $user;

		phpbb_gallery_url::_include(['bbcode', 'message_parser'], 'phpbb');

		$album_id = (int) phpbb_gallery::$user->get_data('personal_album_id');
		if (!$album_id)
		{
			phpbb_gallery_url::redirect('album', 'mode=personal');
		}
		phpbb_gallery_album::check_user($album_id);

		$submit = isset($_POST['submit']);
		$redirect = request_var('redirect', '');
		if (!$submit)
		{
			$album_data = phpbb_gallery_album::get_info($album_id);
			$album_desc_data = generate_text_for_edit($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_options']);

			$template->assign_vars([
				'S_EDIT_PERSONAL_ALBUM'     => true,
				'L_TITLE'                   => $user->lang['EDIT_PERSONAL_ALBUM'],
				'L_TITLE_EXPLAIN'           => $user->lang['EDIT_PERSONAL_ALBUM_EXP'],
				'S_ALBUM_ACTION'            => $this->u_action . '&amp;action=edit' . (($redirect != '') ? '&amp;redirect=album' : ''),

				'ALBUM_DESC'                => $album_desc_data['text'],
				'S_DESC_BBCODE_CHECKED'     => (bool) $album_desc_data['allow_bbcode'],
				'S_DESC_SMILIES_CHECKED'    => (bool) $album_desc_data['allow_smilies'],
				'S_DESC_URLS_CHECKED'       => (bool) $album_desc_data['allow_urls'],
			]);
		}
		else
		{
			if (!check_form_key('ucp_gallery'))
			{
				trigger_error('FORM_INVALID');
			}

			$album_data = [
				'album_name'                    => $user->data['username'],
				'album_desc_options'            => 7,
				'album_desc'                    => utf8_normalize_nfc(request_var('album_desc', '', true)),
			];
			generate_text_for_storage($album_data['album_desc'], $album_data['album_desc_uid'], $album_data['album_desc_bitfield'], $album_data['album_desc_options'], request_var('desc_parse_bbcode', false), request_var('desc_parse_urls', false), request_var('desc_parse_smilies', false));

			$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $album_data) . '
				WHERE album_id = ' . $album_id;
			$db->sql_query($sql);

			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('_albums');

			trigger_error($user->lang['EDITED_PERSONAL_ALBUM'] . '<br /><br />
				<a href="' . (($redirect) ? phpbb_gallery_url::append_sid('album', "album_id={$album_id}") : $this->u_action) . '">' . $user->lang['BACK_TO_PREV'] . '</a>');
		}
	}
	function delete_album()
	{
		global $cache, $db, $template, $user;
		$album_id = (int) phpbb_gallery::$user->get_data('personal_album_id');
		phpbb_gallery_album::check_user($album_id);

		$s_hidden_fields = build_hidden_fields([
			'album_id'      => $album_id,
		]);

		if (confirm_box(true))
		{
			$left_id = $right_id = 0;
			$deleted_images_na = '';
			$album = $deleted_albums = [];

			// Check for owner
			$sql = 'SELECT album_id, left_id, right_id
				FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE album_user_id = ' . $user->data['user_id'] . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$album[] = $row;
				if ($row['album_id'] == $album_id)
				{
					$left_id = $row['left_id'];
					$right_id = $row['right_id'];
				}
			}
			$db->sql_freeresult($result);

			for ($i = 0, $end = count($album); $i < $end; $i++)
			{
				if (($left_id <= $album[$i]['left_id']) && ($album[$i]['left_id'] <= $right_id))
				{
					$deleted_albums[] = $album[$i]['album_id'];
				}
			}

			// $deleted_albums is the array of albums we are going to delete.
			// Now get the images in $deleted_images
			$sql = 'SELECT image_id, image_filename
				FROM ' . GALLERY_IMAGES_TABLE . '
				WHERE ' . $db->sql_in_set('image_album_id', $deleted_albums) . '
				ORDER BY image_id ASC';
			$result = $db->sql_query($sql);

			$deleted_images = $filenames = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$deleted_images[] = $row['image_id'];
				$filenames[(int) $row['image_id']] = $row['image_filename'];
			}

			// We have all image_ids in $deleted_images which are deleted.
			// Aswell as the album_ids in $deleted_albums.
			// So now drop the comments, ratings, images and albums.
			if (!empty($deleted_images))
			{
				phpbb_gallery_image::delete_images($deleted_images, $filenames);
			}

			$sql = 'DELETE FROM ' . GALLERY_ALBUMS_TABLE . '
				WHERE ' . $db->sql_in_set('album_id', $deleted_albums);
			$db->sql_query($sql);

			// Make sure the overall image & comment count is correct...
			$sql = 'SELECT COUNT(image_id) AS num_images, SUM(image_comments) AS num_comments
				FROM ' . GALLERY_IMAGES_TABLE . '
				WHERE image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED . '
					AND image_status <> ' . phpbb_gallery_image::STATUS_ORPHAN;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			phpbb_gallery_config::set('num_images', $row['num_images']);
			phpbb_gallery_config::set('num_comments', $row['num_comments']);

			$num_images = sizeof($deleted_images);
			if ($num_images)
			{
				phpbb_gallery::$user->update_images((0 - $num_images));
			}

			// Maybe we deleted all, so we have to empty phpbb_gallery::$user->get_data('personal_album_id')
			if (in_array(phpbb_gallery::$user->get_data('personal_album_id'), $deleted_albums))
			{
				phpbb_gallery::$user->update_data([
					'personal_album_id'     => 0,
				]);

				phpbb_gallery_config::dec('num_pegas', 1);

				if (phpbb_gallery_config::get('newest_pega_album_id') == $album_id)
				{
					// Update the config for the statistic on the index
					if (phpbb_gallery_config::get('num_pegas') > 0)
					{
						$sql_array = [
							'SELECT'        => 'a.album_id, u.user_id, u.username, u.user_colour',
							'FROM'          => [GALLERY_ALBUMS_TABLE => 'a'],

							'LEFT_JOIN'     => [
								[
									'FROM'      => [USERS_TABLE => 'u'],
									'ON'        => 'u.user_id = a.album_user_id',
								],
							],

							'WHERE'         => 'a.album_user_id <> ' . phpbb_gallery_album::PUBLIC_ALBUM . ' AND a.parent_id = 0',
							'ORDER_BY'      => 'a.album_id DESC',
						];
						$sql = $db->sql_build_query('SELECT', $sql_array);
						$result = $db->sql_query_limit($sql, 1);
						$newest_pgallery = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						phpbb_gallery_config::set('newest_pega_user_id', $newest_pgallery['user_id']);
						phpbb_gallery_config::set('newest_pega_username', $newest_pgallery['username']);
						phpbb_gallery_config::set('newest_pega_user_colour', $newest_pgallery['user_colour']);
						phpbb_gallery_config::set('newest_pega_album_id', $newest_pgallery['album_id']);
					}
					else
					{
						phpbb_gallery_config::set('newest_pega_user_id', 0);
						phpbb_gallery_config::set('newest_pega_username', '');
						phpbb_gallery_config::set('newest_pega_user_colour', '');
						phpbb_gallery_config::set('newest_pega_album_id', 0);
					}
				}
			}
			else
			{
				// Solve the left_id right_id problem
				$delete_id = $right_id - ($left_id - 1);

				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
					SET left_id = left_id - {$delete_id}
					WHERE left_id > {$left_id}
						AND album_user_id = " . $user->data['user_id'];
				$db->sql_query($sql);

				$sql = 'UPDATE ' . GALLERY_ALBUMS_TABLE . "
					SET right_id = right_id - {$delete_id}
					WHERE right_id > {$right_id}
						AND album_user_id = ". $user->data['user_id'];
				$db->sql_query($sql);
			}

			$cache->destroy('sql', GALLERY_ALBUMS_TABLE);
			$cache->destroy('sql', GALLERY_COMMENTS_TABLE);
			$cache->destroy('sql', GALLERY_FAVORITES_TABLE);
			$cache->destroy('sql', GALLERY_IMAGES_TABLE);
			$cache->destroy('sql', GALLERY_RATES_TABLE);
			$cache->destroy('sql', GALLERY_REPORTS_TABLE);
			$cache->destroy('sql', GALLERY_WATCH_TABLE);
			$cache->destroy('_albums');
			phpbb_gallery_auth::set_user_permissions('all', '');

			trigger_error($user->lang['DELETED_ALBUMS'] . '<br /><br />
				<a href="' . phpbb_gallery_url::append_sid('index') . '">' . $user->lang['BACK_TO_PREV'] . '</a>');
		}
		else
		{
			confirm_box(false, 'DELETE_ALBUM', $s_hidden_fields);
		}
	}

	function manage_subscriptions()
	{
		global $db, $template, $user;

		$action = request_var('action', '');
		$image_id_ary = request_var('image_id_ary', [0]);
		$album_id_ary = request_var('album_id_ary', [0]);
		if (($image_id_ary || $album_id_ary) && ($action == 'unsubscribe'))
		{
			if ($album_id_ary)
			{
				phpbb_gallery_notification::remove_albums($album_id_ary);
			}
			if ($image_id_ary)
			{
				phpbb_gallery_notification::remove($image_id_ary);
			}

			meta_refresh(3, $this->u_action);
			$message = '';
			if ($album_id_ary)
			{
				$message .= $user->lang['UNWATCHED_ALBUMS'] . '<br />';
			}
			if ($image_id_ary)
			{
				$message .= $user->lang['UNWATCHED_IMAGES'] . '<br />';
			}
			$message .= '<br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
			trigger_error($message);
		}

		// Subscribed albums
		$sql_array = [
			'SELECT'        => '*',
			'FROM'          => [GALLERY_WATCH_TABLE => 'w'],

			'LEFT_JOIN'     => [
				[
					'FROM'      => [GALLERY_ALBUMS_TABLE => 'a'],
					'ON'        => 'w.album_id = a.album_id',
				],
			],

			'WHERE'         => 'w.album_id <> 0 AND w.user_id = ' . $user->data['user_id'],
		];
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('album_row', [
				'ALBUM_ID'          => $row['album_id'],
				'ALBUM_NAME'        => $row['album_name'],
				'U_VIEW_ALBUM'      => phpbb_gallery_url::append_sid('album', 'album_id=' . $row['album_id']),
				'ALBUM_DESC'        => generate_text_for_display($row['album_desc'], $row['album_desc_uid'], $row['album_desc_bitfield'], $row['album_desc_options']),

				'UC_IMAGE_NAME'     => phpbb_gallery_image::generate_link('image_name', phpbb_gallery_config::get('link_image_name'), $row['album_last_image_id'], $row['album_last_image_name'], $row['album_id']),
				'UC_FAKE_THUMBNAIL' => phpbb_gallery_image::generate_link('fake_thumbnail', phpbb_gallery_config::get('link_thumbnail'), $row['album_last_image_id'], $row['album_last_image_name'], $row['album_id']),
				'UPLOADER'          => get_username_string('full', $row['album_last_user_id'], $row['album_last_username'], $row['album_last_user_colour']),
				'LAST_IMAGE_TIME'   => $user->format_date($row['album_last_image_time']),
				'LAST_IMAGE'        => $row['album_last_image_id'],
				'U_IMAGE'           => phpbb_gallery_url::append_sid('image_page', 'album_id=' . $row['album_id'] . '&amp;image_id=' . $row['album_last_image_id']),
			]);
		}
		$db->sql_freeresult($result);

		// Subscribed images
		$start              = request_var('start', 0);
		$images_per_page    = phpbb_gallery_config::get('album_rows') * phpbb_gallery_config::get('album_columns');
		$total_images       = 0;

		$sql = 'SELECT COUNT(image_id) as images
			FROM ' . GALLERY_WATCH_TABLE . '
			WHERE image_id <> 0
				AND user_id = ' . $user->data['user_id'];
		$result = $db->sql_query($sql);
		$total_images = (int) $db->sql_fetchfield('images');
		$db->sql_freeresult($result);

		$sql_array = [
			'SELECT'        => 'w.*, i.*, a.album_name, c.*',
			'FROM'          => [GALLERY_WATCH_TABLE => 'w'],

			'LEFT_JOIN'     => [
				[
					'FROM'      => [GALLERY_IMAGES_TABLE => 'i'],
					'ON'        => 'w.image_id = i.image_id',
				],
				[
					'FROM'      => [GALLERY_ALBUMS_TABLE => 'a'],
					'ON'        => 'a.album_id = i.image_album_id',
				],
				[
					'FROM'      => [GALLERY_COMMENTS_TABLE => 'c'],
					'ON'        => 'i.image_last_comment = c.comment_id',
				],
			],

			'WHERE'         => 'w.image_id <> 0 AND w.user_id = ' . $user->data['user_id'],
		];
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql, $images_per_page, $start);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('image_row', [
				'UPLOADER'          => get_username_string('full', $row['image_user_id'], $row['image_username'], $row['image_user_colour']),
				'LAST_COMMENT_BY'   => get_username_string('full', $row['comment_user_id'], $row['comment_username'], $row['comment_user_colour']),
				'COMMENT'           => $row['image_comments'],
				'LAST_COMMENT_TIME' => $user->format_date($row['comment_time']),
				'IMAGE_TIME'        => $user->format_date($row['image_time']),
				'UC_IMAGE_NAME'     => phpbb_gallery_image::generate_link('image_name', phpbb_gallery_config::get('link_image_name'), $row['image_id'], $row['image_name'], $row['album_id']),
				'UC_FAKE_THUMBNAIL' => phpbb_gallery_image::generate_link('fake_thumbnail', phpbb_gallery_config::get('link_thumbnail'), $row['image_id'], $row['image_name'], $row['album_id']),
				'ALBUM_NAME'        => $row['album_name'],
				'IMAGE_ID'          => $row['image_id'],
				'U_VIEW_ALBUM'      => phpbb_gallery_url::append_sid('album', 'album_id=' . $row['image_album_id']),
				'U_IMAGE'           => phpbb_gallery_url::append_sid('image_page', 'album_id=' . $row['image_album_id'] . '&amp;image_id=' . $row['image_id']),
			]);
		}
		$db->sql_freeresult($result);

		$template->assign_vars([
			'S_MANAGE_SUBSCRIPTIONS'    => true,
			'S_UCP_ACTION'              => $this->u_action,

			'L_TITLE'                   => $user->lang['UCP_GALLERY_WATCH'],
			'L_TITLE_EXPLAIN'           => $user->lang['YOUR_SUBSCRIPTIONS'],

			'PAGINATION'                => generate_pagination(phpbb_gallery_url::append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_subscriptions'), $total_images, $images_per_page, $start),
			'PAGE_NUMBER'               => on_page($total_images, $images_per_page, $start),
			'TOTAL_IMAGES'              => $user->lang('VIEW_ALBUM_IMAGES', $total_images),

			'DISP_FAKE_THUMB'           => true,
			'FAKE_THUMB_SIZE'           => phpbb_gallery_config::get('mini_thumbnail_size'),
		]);
	}

	function manage_favorites()
	{
		global $db, $template, $user;

		$action = request_var('action', '');
		$image_id_ary = request_var('image_id_ary', [0]);
		if ($image_id_ary && ($action == 'remove_favorite'))
		{
			phpbb_gallery_image_favorite::remove($image_id_ary);

			meta_refresh(3, $this->u_action);
			trigger_error($user->lang['UNFAVORITED_IMAGES'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'));
		}

		$start              = request_var('start', 0);
		$images_per_page    = phpbb_gallery_config::get('album_rows') * phpbb_gallery_config::get('album_columns');
		$total_images       = 0;

		$sql = 'SELECT COUNT(image_id) as images
			FROM ' . GALLERY_FAVORITES_TABLE . '
			WHERE user_id = ' . $user->data['user_id'];
		$result = $db->sql_query($sql);
		$total_images = (int) $db->sql_fetchfield('images');
		$db->sql_freeresult($result);

		$sql_array = [
			'SELECT'        => 'f.*, i.*, a.album_name',
			'FROM'          => [GALLERY_FAVORITES_TABLE => 'f'],

			'LEFT_JOIN'     => [
				[
					'FROM'      => [GALLERY_IMAGES_TABLE => 'i'],
					'ON'        => 'f.image_id = i.image_id',
				],
				[
					'FROM'      => [GALLERY_ALBUMS_TABLE => 'a'],
					'ON'        => 'a.album_id = i.image_album_id',
				],
			],

			'WHERE'         => 'f.user_id = ' . $user->data['user_id'],
		];
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $images_per_page, $start);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('image_row', [
				'UC_IMAGE_NAME'     => phpbb_gallery_image::generate_link('image_name', phpbb_gallery_config::get('link_image_name'), $row['image_id'], $row['image_name'], $row['image_album_id']),
				'UC_FAKE_THUMBNAIL' => phpbb_gallery_image::generate_link('fake_thumbnail', phpbb_gallery_config::get('link_thumbnail'), $row['image_id'], $row['image_name'], $row['image_album_id']),
				'UPLOADER'          => get_username_string('full', $row['image_user_id'], $row['image_username'], $row['image_user_colour']),
				'IMAGE_TIME'        => $user->format_date($row['image_time']),
				'ALBUM_NAME'        => $row['album_name'],
				'IMAGE_ID'          => $row['image_id'],
				'U_VIEW_ALBUM'      => phpbb_gallery_url::append_sid('album', 'album_id=' . $row['image_album_id']),
				'U_IMAGE'           => phpbb_gallery_url::append_sid('image_page', 'album_id=' . $row['image_album_id'] . '&amp;image_id=' . $row['image_id']),
			]);
		}
		$db->sql_freeresult($result);

		$template->assign_vars([
			'S_MANAGE_FAVORITES'    => true,
			'S_UCP_ACTION'          => $this->u_action,

			'L_TITLE'               => $user->lang['UCP_GALLERY_FAVORITES'],
			'L_TITLE_EXPLAIN'       => $user->lang['YOUR_FAVORITE_IMAGES'],

			'PAGINATION'                => generate_pagination(phpbb_gallery_url::append_sid('phpbb', 'ucp', 'i=gallery&amp;mode=manage_favorites'), $total_images, $images_per_page, $start),
			'PAGE_NUMBER'               => on_page($total_images, $images_per_page, $start),
			'TOTAL_IMAGES'              => $user->lang('VIEW_ALBUM_IMAGES', $total_images),

			'DISP_FAKE_THUMB'           => true,
			'FAKE_THUMB_SIZE'           => phpbb_gallery_config::get('mini_thumbnail_size'),
		]);
	}

}
