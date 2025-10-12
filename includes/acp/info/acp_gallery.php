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
class acp_gallery_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_gallery',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> [
				'overview'			=> ['title' => 'ACP_GALLERY_OVERVIEW',				'auth' => 'acl_a_gallery_manage',	'cat' => ['PHPBB_GALLERY']],
				'import_images'		=> ['title' => 'ACP_IMPORT_ALBUMS',				'auth' => 'acl_a_gallery_import',	'cat' => ['PHPBB_GALLERY']],
				'cleanup'			=> ['title' => 'ACP_GALLERY_CLEANUP',				'auth' => 'acl_a_gallery_cleanup',	'cat' => ['PHPBB_GALLERY']],
				],
			];
	}
}
