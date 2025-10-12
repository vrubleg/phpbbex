<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_inactive_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_inactive',
			'title'		=> 'ACP_INACTIVE_USERS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'list'		=> ['title' => 'ACP_INACTIVE_USERS', 'auth' => 'acl_a_user', 'cat' => ['ACP_CAT_USERS']],
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
