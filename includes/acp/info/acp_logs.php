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
		return array(
			'filename'	=> 'acp_logs',
			'title'		=> 'ACP_LOGGING',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'admin'		=> array('title' => 'ACP_ADMIN_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
				'mod'		=> array('title' => 'ACP_MOD_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
				'users'		=> array('title' => 'ACP_USERS_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
				'critical'	=> array('title' => 'ACP_CRITICAL_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
				'register'	=> array('title' => 'ACP_REGISTER_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
				'gallery'	=> array('title' => 'ACP_GALLERY_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
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
