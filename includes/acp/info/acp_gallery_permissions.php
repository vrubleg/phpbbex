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
class acp_gallery_permissions_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_gallery_permissions',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> [
				'manage'	=> ['title' => 'ACP_GALLERY_ALBUM_PERMISSIONS',		'auth' => 'acl_a_gallery_albums',	'cat' => ['PHPBB_GALLERY']],
				'copy'		=> ['title' => 'ACP_GALLERY_ALBUM_PERMISSIONS_COPY',	'auth' => 'acl_a_gallery_albums',	'cat' => ['PHPBB_GALLERY']],
			],
		];
	}
}
