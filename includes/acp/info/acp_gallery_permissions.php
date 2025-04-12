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
		return array(
			'filename'	=> 'acp_gallery_permissions',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'	=> array('title' => 'ACP_GALLERY_ALBUM_PERMISSIONS',		'auth' => 'acl_a_gallery_albums',	'cat' => array('PHPBB_GALLERY')),
				'copy'		=> array('title' => 'ACP_GALLERY_ALBUM_PERMISSIONS_COPY',	'auth' => 'acl_a_gallery_albums',	'cat' => array('PHPBB_GALLERY')),
			),
		);
	}
}
