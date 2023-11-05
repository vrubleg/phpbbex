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

define('IN_PHPBB', true);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include('common.' . $phpEx);
include($phpbb_root_path . 'common.' . $phpEx);

phpbb_gallery::setup(array('mods/gallery'), false);
//phpbb_gallery_url::_include('functions_display', 'phpbb');

// Get general album information
define('S_GALLERY_PLUGINS', false);

/**
* Check whether the requested image & album exit.
*/
$image_id = request_var('image_id', 0);
$image_data = phpbb_gallery_image::get_info($image_id);

$album_id = $image_data['image_album_id'];
$album_data = phpbb_gallery_album::get_info($album_id);

$image_error = '';

$image_filetype = utf8_substr($image_data['image_filename'], strlen($image_data['image_filename']) - 4, 4);
if (!file_exists(phpbb_gallery_url::path('upload') . $image_data['image_filename']))
{
	$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
		SET image_filemissing = 1
		WHERE image_id = ' . $image_id;
	$db->sql_query($sql);
	//trigger_error('IMAGE_NOT_EXIST');
	$image_error = 'image_not_exist.jpg';
	http_response_code(404);
}

/**
* Check permissions and hotlinking
*/
if (($image_data['image_user_id'] != $user->data['user_id']) && ($image_data['image_status'] == phpbb_gallery_image::STATUS_ORPHAN))
{
	//trigger_error('NOT_AUTHORISED');
	$image_error = 'not_authorised.jpg';
	http_response_code(403);
}

if ((!phpbb_gallery::$auth->acl_check('i_view', $album_id, $album_data['album_user_id'])) || (!phpbb_gallery::$auth->acl_check('m_status', $album_id, $album_data['album_user_id']) && ($image_data['image_status'] == phpbb_gallery_image::STATUS_UNAPPROVED)))
{
	//trigger_error('NOT_AUTHORISED');
	$image_error = 'not_authorised.jpg';
	http_response_code(403);
}

/**
* Hotlink prevention
*/
if (!phpbb_gallery_config::get('allow_hotlinking') && isset($_SERVER['HTTP_REFERER']))
{
	$good_referers = array(preg_replace('#^(www\.)+#i', '', HTTP_HOST));
	if (phpbb_gallery_config::get('hotlinking_domains') != '')
	{
		$good_referers = array_merge($good_referers, explode(',', phpbb_gallery_config::get('hotlinking_domains')));
	}

	$referer = @parse_url(trim($_SERVER['HTTP_REFERER']), PHP_URL_HOST);
	// Do not shorten IP adresses...
	if (substr_count($referer, '.') >= 2 && !preg_match(get_preg_expression('ipv4'), $referer) && !preg_match(get_preg_expression('ipv6'), $referer))
	{
		$position = strrpos($referer, '.');
		$position = strrpos(substr($referer, 0, $position), '.');
		$referer_host = substr($referer, $position + 1);
	}

	// Is the host (flying-bits.org) or the full domain (xyz.flying-bits.org) in the white-list?
	if (!in_array($referer, $good_referers) && !in_array($referer_host, $good_referers))
	{
		//trigger_error('NOT_AUTHORISED');
		$image_error = 'no_hotlinking.jpg';
		http_response_code(403);
	}
}



/**
* Main work here...
*/
$mode = request_var('mode', '');
switch ($mode)
{
	case 'medium':
		$filesize_var = 'filesize_medium';
		$image_source_path = phpbb_gallery_url::path('medium');
		$possible_watermark = true;
	break;
	case 'thumbnail':
		$filesize_var = 'filesize_cache';
		$image_source_path = phpbb_gallery_url::path('thumbnail');
		$possible_watermark = false;
	break;
	default:
		$filesize_var = 'filesize_upload';
		$image_source_path = phpbb_gallery_url::path('upload');
		$possible_watermark = true;

		// Increase the view count only for full images, if not already counted
		$view = request_var('view', '');
		if (!$user->data['is_bot'] && !$image_error && ($view != 'no_count'))
		{
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
				SET image_view_count = image_view_count + 1
				WHERE image_id = ' . $image_id;
			$db->sql_query($sql);
		}
	break;
}
$image_source = $image_source_path  . $image_data['image_filename'];

// There was a reason to not display the image, so we send an error-image
if ($image_error)
{
	$image_data['image_filename'] = $user->data['user_lang'] . '_' . $image_error;
	if (!file_exists($image_source_path . $image_data['image_filename']))
	{
		$image_data['image_filename'] = $image_error;
	}
	$image_source = $image_source_path . $image_data['image_filename'];
	$possible_watermark = false;
}

$image_tools = new phpbb_gallery_image_file(phpbb_gallery_config::get('gdlib_version'));
$image_tools->set_image_options(phpbb_gallery_config::get('max_filesize'), phpbb_gallery_config::get('max_height'), phpbb_gallery_config::get('max_width'));
$image_tools->set_image_data($image_source, $image_data['image_name']);

if ($image_error || !$user->data['is_registered'])
{
	$image_tools->disable_browser_cache();
}

// Generate the sourcefile, if it's missing
if (($mode == 'medium') || ($mode == 'thumbnail'))
{
	$filesize_var = '';
	if ($mode == 'thumbnail')
	{
		$resize_width = phpbb_gallery_config::get('thumbnail_width');
		$resize_height = phpbb_gallery_config::get('thumbnail_height');
	}
	else
	{
		$resize_width = phpbb_gallery_config::get('medium_width');
		$resize_height = phpbb_gallery_config::get('medium_height');
	}

	if (!file_exists($image_source))
	{
		$image_tools->set_image_data(phpbb_gallery_url::path('upload') . $image_data['image_filename']);
		$image_tools->read_image(true);

		$image_size['file'] = $image_tools->image_size['file'];
		$image_size['width'] = $image_tools->image_size['width'];
		$image_size['height'] = $image_tools->image_size['height'];

		$image_tools->set_image_data($image_source);

		if (($image_size['width'] > $resize_width) || ($image_size['height'] > $resize_height))
		{
			$put_details = (phpbb_gallery_config::get('thumbnail_infoline') && ($mode == 'thumbnail')) ? true : false;
			$image_tools->create_thumbnail($resize_width, $resize_height, $put_details, phpbb_gallery_constants::THUMBNAIL_INFO_HEIGHT, $image_size);
		}

		if (phpbb_gallery_config::get($mode . '_cache'))
		{
			$image_tools->write_image($image_source, (($mode == 'thumbnail') ? phpbb_gallery_config::get('thumbnail_quality') : phpbb_gallery_config::get('jpg_quality')), false);

			if ($mode == 'thumbnail')
			{
				$image_data['filesize_cache'] = @filesize($image_source);
				$sql_ary = array('filesize_cache' => $image_data['filesize_cache']);
			}
			else
			{
				$image_data['filesize_medium'] = @filesize($image_source);
				$sql_ary = array('filesize_medium' => $image_data['filesize_medium']);
			}
			$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE ' . $db->sql_in_set('image_id', $image_id);
			$db->sql_query($sql);
		}
	}
}

$image_tools->set_last_modified(phpbb_gallery::$user->get_data('user_permissions_changed'));
$image_tools->set_last_modified(phpbb_gallery_config::get('watermark_changed'));

// Watermark
if (phpbb_gallery_config::get('watermark_enabled') && $album_data['album_watermark'] && !phpbb_gallery::$auth->acl_check('i_watermark', $album_id, $album_data['album_user_id']) && $possible_watermark)
{
	$filesize_var = '';
	$image_tools->set_last_modified(@filemtime(phpbb_gallery_url::path('phpbb') . phpbb_gallery_config::get('watermark_source')));
	$image_tools->watermark_image(phpbb_gallery_url::path('phpbb') . phpbb_gallery_config::get('watermark_source'), phpbb_gallery_config::get('watermark_position'), phpbb_gallery_config::get('watermark_height'), phpbb_gallery_config::get('watermark_width'));
}

$image_tools->send_image_to_browser();

?>