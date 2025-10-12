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
		return [
			'filename'	=> 'ucp_main',
			'title'		=> 'UCP_MAIN',
			'version'	=> '1.0.0',
			'modes'		=> [
				'front'			=> ['title' => 'UCP_MAIN_FRONT', 'auth' => '', 'cat' => ['UCP_MAIN']],
				'subscribed'	=> ['title' => 'UCP_MAIN_SUBSCRIBED', 'auth' => '', 'cat' => ['UCP_MAIN']],
				'bookmarks'		=> ['title' => 'UCP_MAIN_BOOKMARKS', 'auth' => 'cfg_allow_bookmarks', 'cat' => ['UCP_MAIN']],
				'drafts'		=> ['title' => 'UCP_MAIN_DRAFTS', 'auth' => '', 'cat' => ['UCP_MAIN']],
			],
		];
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
