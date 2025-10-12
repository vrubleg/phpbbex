<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_permissions_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_permissions',
			'title'		=> 'ACP_PERMISSIONS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'intro'					=> ['title' => 'ACP_PERMISSIONS', 'auth' => 'acl_a_authusers || acl_a_authgroups || acl_a_viewauth', 'cat' => ['ACP_CAT_PERMISSIONS']],
				'trace'					=> ['title' => 'ACP_PERMISSION_TRACE', 'auth' => 'acl_a_viewauth', 'display' => false, 'cat' => ['ACP_PERMISSION_MASKS']],

				'setting_forum_local'	=> ['title' => 'ACP_FORUM_PERMISSIONS', 'auth' => 'acl_a_fauth && (acl_a_authusers || acl_a_authgroups)', 'cat' => ['ACP_FORUM_BASED_PERMISSIONS']],
				'setting_forum_copy'	=> ['title' => 'ACP_FORUM_PERMISSIONS_COPY', 'auth' => 'acl_a_fauth && acl_a_authusers && acl_a_authgroups && acl_a_mauth', 'cat' => ['ACP_FORUM_BASED_PERMISSIONS']],
				'setting_mod_local'		=> ['title' => 'ACP_FORUM_MODERATORS', 'auth' => 'acl_a_mauth && (acl_a_authusers || acl_a_authgroups)', 'cat' => ['ACP_FORUM_BASED_PERMISSIONS']],
				'setting_user_global'	=> ['title' => 'ACP_USERS_PERMISSIONS', 'auth' => 'acl_a_authusers && (acl_a_aauth || acl_a_mauth || acl_a_uauth)', 'cat' => ['ACP_GLOBAL_PERMISSIONS', 'ACP_CAT_USERS']],
				'setting_user_local'	=> ['title' => 'ACP_USERS_FORUM_PERMISSIONS', 'auth' => 'acl_a_authusers && (acl_a_mauth || acl_a_fauth)', 'cat' => ['ACP_FORUM_BASED_PERMISSIONS', 'ACP_CAT_USERS']],
				'setting_group_global'	=> ['title' => 'ACP_GROUPS_PERMISSIONS', 'auth' => 'acl_a_authgroups && (acl_a_aauth || acl_a_mauth || acl_a_uauth)', 'cat' => ['ACP_GLOBAL_PERMISSIONS', 'ACP_GROUPS']],
				'setting_group_local'	=> ['title' => 'ACP_GROUPS_FORUM_PERMISSIONS', 'auth' => 'acl_a_authgroups && (acl_a_mauth || acl_a_fauth)', 'cat' => ['ACP_FORUM_BASED_PERMISSIONS', 'ACP_GROUPS']],
				'setting_admin_global'	=> ['title' => 'ACP_ADMINISTRATORS', 'auth' => 'acl_a_aauth && (acl_a_authusers || acl_a_authgroups)', 'cat' => ['ACP_GLOBAL_PERMISSIONS']],
				'setting_mod_global'	=> ['title' => 'ACP_GLOBAL_MODERATORS', 'auth' => 'acl_a_mauth && (acl_a_authusers || acl_a_authgroups)', 'cat' => ['ACP_GLOBAL_PERMISSIONS']],

				'view_admin_global'		=> ['title' => 'ACP_VIEW_ADMIN_PERMISSIONS', 'auth' => 'acl_a_viewauth', 'cat' => ['ACP_PERMISSION_MASKS']],
				'view_user_global'		=> ['title' => 'ACP_VIEW_USER_PERMISSIONS', 'auth' => 'acl_a_viewauth', 'cat' => ['ACP_PERMISSION_MASKS']],
				'view_mod_global'		=> ['title' => 'ACP_VIEW_GLOBAL_MOD_PERMISSIONS', 'auth' => 'acl_a_viewauth', 'cat' => ['ACP_PERMISSION_MASKS']],
				'view_mod_local'		=> ['title' => 'ACP_VIEW_FORUM_MOD_PERMISSIONS', 'auth' => 'acl_a_viewauth', 'cat' => ['ACP_PERMISSION_MASKS']],
				'view_forum_local'		=> ['title' => 'ACP_VIEW_FORUM_PERMISSIONS', 'auth' => 'acl_a_viewauth', 'cat' => ['ACP_PERMISSION_MASKS']],
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
