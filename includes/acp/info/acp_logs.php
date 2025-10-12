<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_logs_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_logs',
			'title'		=> 'ACP_LOGGING',
			'version'	=> '1.0.0',
			'modes'		=> [
				'admin'		=> ['title' => 'ACP_ADMIN_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => ['ACP_FORUM_LOGS']],
				'mod'		=> ['title' => 'ACP_MOD_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => ['ACP_FORUM_LOGS']],
				'users'		=> ['title' => 'ACP_USERS_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => ['ACP_FORUM_LOGS']],
				'critical'	=> ['title' => 'ACP_CRITICAL_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => ['ACP_FORUM_LOGS']],
				'register'	=> ['title' => 'ACP_REGISTER_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => ['ACP_FORUM_LOGS']],
				'gallery'	=> ['title' => 'ACP_GALLERY_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => ['ACP_FORUM_LOGS']],
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
