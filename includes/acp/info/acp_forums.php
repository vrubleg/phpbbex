<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_forums_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_forums',
			'title'		=> 'ACP_FORUM_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'	=> array('title' => 'ACP_MANAGE_FORUMS', 'auth' => 'acl_a_forum', 'cat' => array('ACP_MANAGE_FORUMS')),
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
