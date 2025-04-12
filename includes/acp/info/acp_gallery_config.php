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
class acp_gallery_config_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_gallery_config',
			'title'		=> 'PHPBB_GALLERY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'			=> array('title' => 'ACP_GALLERY_CONFIGURE_GALLERY', 'auth' => 'acl_a_gallery_manage', 'cat' => array('PHPBB_GALLERY')),
			),
		);
	}
}
