<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_profile_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_profile',
			'title'		=> 'ACP_CUSTOM_PROFILE_FIELDS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'profile'	=> ['title' => 'ACP_CUSTOM_PROFILE_FIELDS', 'auth' => 'acl_a_profile', 'cat' => ['ACP_CAT_USERS']],
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
