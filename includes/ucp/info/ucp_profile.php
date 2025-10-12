<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class ucp_profile_info
{
	function module()
	{
		return [
			'filename'	=> 'ucp_profile',
			'title'		=> 'UCP_PROFILE',
			'version'	=> '1.0.0',
			'modes'		=> [
				'profile_info'	=> ['title' => 'UCP_PROFILE_PROFILE_INFO', 'auth' => '', 'cat' => ['UCP_PROFILE']],
				'signature'		=> ['title' => 'UCP_PROFILE_SIGNATURE', 'auth' => 'acl_u_sig', 'cat' => ['UCP_PROFILE']],
				'avatar'		=> ['title' => 'UCP_PROFILE_AVATAR', 'auth' => 'cfg_allow_avatar && (cfg_allow_avatar_local || cfg_allow_avatar_remote || cfg_allow_avatar_upload || cfg_allow_avatar_remote_upload)', 'cat' => ['UCP_PROFILE']],
				'reg_details'	=> ['title' => 'UCP_PROFILE_REG_DETAILS', 'auth' => '', 'cat' => ['UCP_PROFILE']],
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
