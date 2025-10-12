<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_prune_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_prune',
			'title'		=> 'ACP_PRUNING',
			'version'	=> '1.0.0',
			'modes'		=> [
				'forums'	=> ['title' => 'ACP_PRUNE_FORUMS', 'auth' => 'acl_a_prune', 'cat' => ['ACP_MANAGE_FORUMS']],
				'users'		=> ['title' => 'ACP_PRUNE_USERS', 'auth' => 'acl_a_userdel', 'cat' => ['ACP_USER_SECURITY']],
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
