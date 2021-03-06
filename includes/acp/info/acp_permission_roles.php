<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_permission_roles_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_permission_roles',
			'title'		=> 'ACP_PERMISSION_ROLES',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'admin_roles'		=> array('title' => 'ACP_ADMIN_ROLES', 'auth' => 'acl_a_roles && acl_a_aauth', 'cat' => array('ACP_PERMISSION_ROLES')),
				'user_roles'		=> array('title' => 'ACP_USER_ROLES', 'auth' => 'acl_a_roles && acl_a_uauth', 'cat' => array('ACP_PERMISSION_ROLES')),
				'mod_roles'			=> array('title' => 'ACP_MOD_ROLES', 'auth' => 'acl_a_roles && acl_a_mauth', 'cat' => array('ACP_PERMISSION_ROLES')),
				'forum_roles'		=> array('title' => 'ACP_FORUM_ROLES', 'auth' => 'acl_a_roles && acl_a_fauth', 'cat' => array('ACP_PERMISSION_ROLES')),
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
