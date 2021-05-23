<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class ucp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_main',
			'title'		=> 'UCP_MAIN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'			=> array('title' => 'UCP_MAIN_FRONT', 'auth' => '', 'cat' => array('UCP_MAIN')),
				'subscribed'	=> array('title' => 'UCP_MAIN_SUBSCRIBED', 'auth' => '', 'cat' => array('UCP_MAIN')),
				'bookmarks'		=> array('title' => 'UCP_MAIN_BOOKMARKS', 'auth' => 'cfg_allow_bookmarks', 'cat' => array('UCP_MAIN')),
				'drafts'		=> array('title' => 'UCP_MAIN_DRAFTS', 'auth' => '', 'cat' => array('UCP_MAIN')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
