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
		return [
			'filename'	=> 'ucp_gallery',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> [
					'manage_albums'			=> ['title' => 'UCP_GALLERY_PERSONAL_ALBUMS', 'auth' => '', 'cat' => ['PHPBB_GALLERY']],
					'manage_settings'		=> ['title' => 'UCP_GALLERY_SETTINGS', 'auth' => '', 'cat' => ['PHPBB_GALLERY']],
					'manage_subscriptions'	=> ['title' => 'UCP_GALLERY_WATCH', 'auth' => '', 'cat' => ['PHPBB_GALLERY']],
					'manage_favorites'		=> ['title' => 'UCP_GALLERY_FAVORITES', 'auth' => '', 'cat' => ['PHPBB_GALLERY']],
				],
			];
	}
}
