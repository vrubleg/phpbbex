<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_search_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_search',
			'title'		=> 'ACP_SEARCH',
			'version'	=> '1.0.0',
			'modes'		=> [
				'settings'	=> ['title' => 'ACP_SEARCH_SETTINGS', 'auth' => 'acl_a_search', 'cat' => ['ACP_SERVER_CONFIGURATION']],
				'index'		=> ['title' => 'ACP_SEARCH_INDEX', 'auth' => 'acl_a_search', 'cat' => ['ACP_CAT_DATABASE']],
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
