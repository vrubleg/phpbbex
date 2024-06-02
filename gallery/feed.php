<?php
/**
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2011 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
define('IN_FEED_GALLERY', true);
require_once('common.php');
require_once($phpbb_root_path . 'common.php');

phpbb_gallery::setup(array('mods/gallery'));
phpbb_gallery_url::_include('functions_display', 'phpbb');

if (!phpbb_gallery_config::get('feed_enable'))
{
	trigger_error('NO_FEED_ENABLED');
}

// Initial var setup
$mode		= request_var('mode', '');
$album_id	= request_var('album_id', 0);

$feed = new phpbb_gallery_feed($album_id);

if ($album_id)
{
	$back_link = phpbb_gallery_url::append_sid('full', 'album', 'album_id=' . $album_id);
	$self_link = phpbb_gallery_url::append_sid('full', 'feed', 'album_id=' . $album_id);
}
else
{
	$back_link = phpbb_gallery_url::append_sid('full', 'search', 'search_id=recent');
	$self_link = phpbb_gallery_url::append_sid('full', 'feed');
}

$feed->send_header($config['sitename'], $config['site_desc'], $self_link, $back_link);

$feed->send_images();

$feed->send_footer();
