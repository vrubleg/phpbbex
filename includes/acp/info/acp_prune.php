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
		return array(
			'filename'	=> 'acp_prune',
			'title'		=> 'ACP_PRUNING',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'forums'	=> array('title' => 'ACP_PRUNE_FORUMS', 'auth' => 'acl_a_prune', 'cat' => array('ACP_MANAGE_FORUMS')),
				'users'		=> array('title' => 'ACP_PRUNE_USERS', 'auth' => 'acl_a_userdel', 'cat' => array('ACP_USER_SECURITY')),
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
