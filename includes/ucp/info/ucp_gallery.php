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
* @package module_install
*/
class ucp_gallery_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_gallery',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> array(
					'manage_albums'			=> array('title' => 'UCP_GALLERY_PERSONAL_ALBUMS', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
					'manage_settings'		=> array('title' => 'UCP_GALLERY_SETTINGS', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
					'manage_subscriptions'	=> array('title' => 'UCP_GALLERY_WATCH', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
					'manage_favorites'		=> array('title' => 'UCP_GALLERY_FAVORITES', 'auth' => '', 'cat' => array('PHPBB_GALLERY')),
				),
			);
	}
}
