<?php
/**
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
*/

define('IN_PHPBB', true);
require_once('common.php');
require_once(PHPBB_ROOT_PATH . 'common.php');

phpbb_gallery::setup(['mods/gallery_ucp', 'mods/gallery']);
phpbb_gallery_url::_include('functions_display', 'phpbb');

/**
* Check the request
*/
$user_id    = request_var('user_id', 0);
$album_id   = request_var('album_id', 0);
$start      = request_var('start', 0);
$mode       = request_var('mode', '');

if ($mode == 'personal')
{
	if (!$user->data['is_registered'])
	{
		trigger_error('NOT_AUTHORISED');
	}

	$album_id = (int) phpbb_gallery::$user->get_data('personal_album_id');
	if (!$album_id)
	{
		if (!phpbb_gallery::$auth->acl_check('i_upload', phpbb_gallery_auth::OWN_ALBUM))
		{
			trigger_error('NO_PERSALBUM_ALLOWED');
		}

		if (isset($_POST['cancel']))
		{
			phpbb_gallery_url::redirect('index');
		}

		if (confirm_box(true))
		{
			$album_id = phpbb_gallery_album::generate_personal_album($user->data['username'], $user->data['user_id'], $user->data['user_colour'], phpbb_gallery::$user);
		}
		else
		{
			confirm_box(false, 'CREATE_PERSONAL_ALBUM', build_hidden_fields([
				'mode' => 'personal',
			]));
		}
	}

	phpbb_gallery_url::redirect('album', 'album_id=' . $album_id);
}

$album_data = phpbb_gallery_album::get_info($album_id);
$sort_days  = request_var('st', 0);
$sort_key   = request_var('sk', $album_data['album_sort_key'] ?: phpbb_gallery_config::get('default_sort_key'));
$sort_dir   = request_var('sd', $album_data['album_sort_dir'] ?: phpbb_gallery_config::get('default_sort_dir'));

/**
* Build auth-list
*/
phpbb_gallery::$auth->gen_auth_level('album', $album_id, $album_data['album_status'], $album_data['album_user_id']);

if (!phpbb_gallery::$auth->acl_check('i_view', $album_id, $album_data['album_user_id']))
{
	if ($user->data['is_bot'])
	{
		phpbb_gallery_url::redirect('index');
	}
	if (!$user->data['is_registered'])
	{
		login_box(phpbb_gallery_url::append_sid('relative', 'album', "album_id={$album_id}"), $user->lang['LOGIN_EXPLAIN_GALLERY_VIEW']);
	}
	else
	{
		trigger_error('NOT_AUTHORISED');
	}
}

/**
* Are we (un)watching the album?
*/
$token = request_var('hash', '');
if ((($mode == 'watch') || ($mode == 'unwatch')) && check_link_hash($token, "{$mode}_{$album_id}"))
{
	$backlink = phpbb_gallery_url::append_sid('album', "album_id={$album_id}");

	if ($mode == 'watch')
	{
		phpbb_gallery_notification::add_albums($album_id);
	}
	if ($mode == 'unwatch')
	{
		phpbb_gallery_notification::remove_albums($album_id);
	}

	redirect($backlink);
}

// Build the navigation & display subalbums
phpbb_gallery_album::generate_nav($album_data);
phpbb_gallery_album::display_albums($album_data, $config['load_moderators']);

// Set some variables to their defaults
$image_counter = 0;
$l_moderator = $moderators_list = $s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$grouprows = $album_moderators = [];
$images_per_page = phpbb_gallery_config::get('album_rows') * phpbb_gallery_config::get('album_columns');

/**
* We have album_type so that there may be images ...
*/
if ($album_data['album_type'] != phpbb_gallery_album::TYPE_CAT)
{
	if ($config['load_moderators'])
	{
		phpbb_gallery_album::get_moderators($album_moderators, $album_id);
	}
	if (!empty($album_moderators[$album_id]))
	{
		$l_moderator = (sizeof($album_moderators[$album_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
		$moderators_list = implode(', ', $album_moderators[$album_id]);
	}

	/**
	* Build the sort options
	*/
	$limit_days = [0 => $user->lang['ALL_IMAGES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']];
	$sort_by_text = ['t' => $user->lang['TIME'], 'n' => $user->lang['IMAGE_NAME'], 'vc' => $user->lang['GALLERY_VIEWS']];
	$sort_by_sql = ['t' => 'image_time', 'n' => 'image_name_clean', 'vc' => 'image_view_count'];

	$sort_by_text['u'] = $user->lang['SORT_USERNAME'];
	$sort_by_sql['u'] = 'image_username_clean';

	if (phpbb_gallery_config::get('allow_rates'))
	{
		$sort_by_text['ra'] = $user->lang['RATING'];
		$sort_by_sql['ra'] = 'image_rate_avg';
		$sort_by_text['r'] = $user->lang['RATES_COUNT'];
		$sort_by_sql['r'] = 'image_rates';
	}
	if (phpbb_gallery_config::get('allow_comments'))
	{
		$sort_by_text['c'] = $user->lang['COMMENTS'];
		$sort_by_sql['c'] = 'image_comments';
		$sort_by_text['lc'] = $user->lang['NEW_COMMENT'];
		$sort_by_sql['lc'] = 'image_last_comment';
	}
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);
	$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

	if ($album_data['album_images_real'] > 0)
	{
		$image_status_check = ' AND image_status <> ' . phpbb_gallery_image::STATUS_UNAPPROVED;
		$image_counter = $album_data['album_images'];
		if (phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']))
		{
			$image_status_check = '';
			$image_counter = $album_data['album_images_real'];
		}

		if (in_array($sort_key, ['r', 'ra']))
		{
			$sql_help_sort = ', image_id ' . (($sort_dir == 'd') ? 'ASC' : 'DESC');
		}
		else
		{
			$sql_help_sort = ', image_id ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
		}

		$images = [];
		$sql = 'SELECT *
			FROM ' . GALLERY_IMAGES_TABLE . '
			WHERE image_album_id = ' . (int) $album_id . "
				{$image_status_check}
				AND image_status <> " . phpbb_gallery_image::STATUS_ORPHAN . "
			ORDER BY {$sql_sort_order}" . $sql_help_sort;

		$result = $db->sql_query_limit($sql, $images_per_page, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$images[] = $row;
		}
		$db->sql_freeresult($result);

		$init_block = true;

		for ($i = 0, $end = count($images); $i < $end; $i += phpbb_gallery_config::get('album_columns'))
		{
			if ($init_block)
			{
				$template->assign_block_vars('imageblock', [
					//'U_BLOCK'     => phpbb_gallery_url::append_sid('album', 'album_id=' . $album_data['album_id']),
					'BLOCK_NAME'    => $album_data['album_name'],
					'S_COL_WIDTH'   => (100 / phpbb_gallery_config::get('album_columns')) . '%',
					'S_COLS'        => phpbb_gallery_config::get('album_columns'),
				]);
				$init_block = false;
			}

			$template->assign_block_vars('imageblock.imagerow', []);

			for ($j = $i, $end_columns = ($i + phpbb_gallery_config::get('album_columns')); $j < $end_columns; $j++)
			{
				if ($j >= $end)
				{
					$template->assign_block_vars('imageblock.imagerow.no_image', []);
					continue;
				}

				// Assign the image to the template-block
				$images[$j]['album_name'] = $album_data['album_name'];
				phpbb_gallery_image::assign_block('imageblock.imagerow.image', $images[$j], $album_data['album_status'], phpbb_gallery_config::get('album_display'), $album_data['album_user_id']);
			}
		}
	}
}
// End of "We have album_type so that there may be images ..."

// Page is ready loaded, mark album as "read"
phpbb_gallery_misc::markread('album', $album_id);

$watch_mode = ($album_data['watch_id']) ? 'unwatch' : 'watch';

$template->assign_vars([
	'S_IN_ALBUM'                => true, // used for some templating in subsilver2
	'S_IS_POSTABLE'             => ($album_data['album_type'] != phpbb_gallery_album::TYPE_CAT),
	'S_IS_LOCKED'               => ($album_data['album_status'] == phpbb_gallery_album::STATUS_LOCKED),
	'UPLOAD_IMG'                => ($album_data['album_status'] == phpbb_gallery_album::STATUS_LOCKED) ? $user->img('button_topic_locked', 'ALBUM_LOCKED') : $user->img('button_upload_image', 'UPLOAD_IMAGE'),
	'S_MODE'                    => $album_data['album_type'],
	'L_MODERATORS'              => $l_moderator,
	'MODERATORS'                => $moderators_list,

	'U_UPLOAD_IMAGE'            => ((!$album_data['album_user_id'] || ($album_data['album_user_id'] == $user->data['user_id'])) && (($user->data['user_id'] == ANONYMOUS) || phpbb_gallery::$auth->acl_check('i_upload', $album_id, $album_data['album_user_id']))) ?
										phpbb_gallery_url::append_sid('posting', "mode=upload&amp;album_id={$album_id}") : '',
	'S_DISPLAY_SEARCHBOX'       => ($auth->acl_get('u_search') && $config['load_search']),
	'S_SEARCHBOX_ACTION'        => phpbb_gallery_url::append_sid('search', 'aid[]=' . $album_id),
	'S_ENABLE_FEEDS_ALBUM'      => $album_data['album_feed'] && (phpbb_gallery_config::get('feed_enable_pegas') || !$album_data['album_user_id']),

	'S_THUMBNAIL_SIZE'          => phpbb_gallery_config::get('thumbnail_height') + 20 + ((phpbb_gallery_config::get('thumbnail_infoline')) ? phpbb_gallery_constants::THUMBNAIL_INFO_HEIGHT : 0),
	'S_ALBUM_ACTION'            => phpbb_gallery_url::append_sid('album', "album_id={$album_id}"),

	'S_SELECT_SORT_DIR'         => $s_sort_dir,
	'S_SELECT_SORT_KEY'         => $s_sort_key,

	'U_RETURN_LINK'             => phpbb_gallery_url::append_sid('index'),
	'S_RETURN_LINK'             => $user->lang['GALLERY'],

	'PAGINATION'                => generate_pagination(phpbb_gallery_url::append_sid('album', "album_id={$album_id}&amp;sk={$sort_key}&amp;sd={$sort_dir}&amp;st={$sort_days}"), $image_counter, $images_per_page, $start),
	'TOTAL_IMAGES'              => $user->lang('VIEW_ALBUM_IMAGES', $image_counter),
	'PAGE_NUMBER'               => on_page($image_counter, $images_per_page, $start),

	'L_WATCH_TOPIC'             => ($album_data['watch_id']) ? $user->lang['UNWATCH_ALBUM'] : $user->lang['WATCH_ALBUM'],
	'U_WATCH_TOPIC'             => (($album_data['album_type'] != phpbb_gallery_album::TYPE_CAT) && ($user->data['user_id'] != ANONYMOUS)) ? phpbb_gallery_url::append_sid('album', "mode=" . $watch_mode . "&amp;album_id={$album_id}&amp;hash=" . generate_link_hash("{$watch_mode}_{$album_id}")) : '',
	'S_WATCHING_TOPIC'          => (bool) $album_data['watch_id'],
]);


page_header($user->lang['VIEW_ALBUM'] . ' - ' . $album_data['album_name'], true, $album_id, 'album');

if (($album_data['album_type'] != phpbb_gallery_album::TYPE_CAT) && phpbb_gallery::$auth->acl_check('m_', $album_id, $album_data['album_user_id']))
{
	$template->assign_var('U_MCP', phpbb_gallery_url::append_sid('mcp', "album_id={$album_id}"));
}

$template->set_filenames(['body' => 'gallery/album_body.html']);

page_footer();
