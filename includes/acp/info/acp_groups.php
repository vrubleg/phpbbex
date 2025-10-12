<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_groups_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_groups',
			'title'		=> 'ACP_GROUPS_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> [
				'manage'		=> ['title' => 'ACP_GROUPS_MANAGE', 'auth' => 'acl_a_group', 'cat' => ['ACP_GROUPS']],
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
