<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_ban_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_ban',
			'title'		=> 'ACP_BAN',
			'version'	=> '1.0.0',
			'modes'		=> [
				'email'		=> ['title' => 'ACP_BAN_EMAILS', 'auth' => 'acl_a_ban', 'cat' => ['ACP_USER_SECURITY']],
				'ip'		=> ['title' => 'ACP_BAN_IPS', 'auth' => 'acl_a_ban', 'cat' => ['ACP_USER_SECURITY']],
				'user'		=> ['title' => 'ACP_BAN_USERNAMES', 'auth' => 'acl_a_ban', 'cat' => ['ACP_USER_SECURITY']],
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
