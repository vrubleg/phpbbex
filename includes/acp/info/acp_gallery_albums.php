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
class acp_gallery_albums_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_gallery_albums',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> [
				'manage'	=> ['title' => 'ACP_GALLERY_MANAGE_ALBUMS', 'auth' => 'acl_a_gallery_albums', 'cat' => ['PHPBB_GALLERY']],
			],
		];
	}
}
