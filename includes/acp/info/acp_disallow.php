<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_disallow_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_disallow',
			'title'		=> 'ACP_DISALLOW',
			'version'	=> '1.0.0',
			'modes'		=> [
				'usernames'		=> ['title' => 'ACP_DISALLOW_USERNAMES', 'auth' => 'acl_a_names', 'cat' => ['ACP_USER_SECURITY']],
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
