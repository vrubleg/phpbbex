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
		return array(
			'filename'	=> 'acp_groups',
			'title'		=> 'ACP_GROUPS_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'		=> array('title' => 'ACP_GROUPS_MANAGE', 'auth' => 'acl_a_group', 'cat' => array('ACP_GROUPS')),
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
