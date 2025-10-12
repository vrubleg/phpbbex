<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_users_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_users',
			'title'		=> 'ACP_USER_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> [
				'overview'		=> ['title' => 'ACP_MANAGE_USERS', 'auth' => 'acl_a_user', 'cat' => ['ACP_CAT_USERS']],
				'feedback'		=> ['title' => 'ACP_USER_FEEDBACK', 'auth' => 'acl_a_user', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
				'warnings'		=> ['title' => 'ACP_USER_WARNINGS', 'auth' => 'acl_a_user', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
				'profile'		=> ['title' => 'ACP_USER_PROFILE', 'auth' => 'acl_a_user', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
				'prefs'			=> ['title' => 'ACP_USER_PREFS', 'auth' => 'acl_a_user', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
				'avatar'		=> ['title' => 'ACP_USER_AVATAR', 'auth' => 'acl_a_user', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
				'rank'			=> ['title' => 'ACP_USER_RANK', 'auth' => 'acl_a_user', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
				'sig'			=> ['title' => 'ACP_USER_SIG', 'auth' => 'acl_a_user', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
				'groups'		=> ['title' => 'ACP_USER_GROUPS', 'auth' => 'acl_a_user && acl_a_group', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
				'perm'			=> ['title' => 'ACP_USER_PERM', 'auth' => 'acl_a_user && acl_a_viewauth', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
				'attach'		=> ['title' => 'ACP_USER_ATTACH', 'auth' => 'acl_a_user', 'display' => false, 'cat' => ['ACP_CAT_USERS']],
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
