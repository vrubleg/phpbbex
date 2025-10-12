<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class ucp_groups_info
{
	function module()
	{
		return [
			'filename'	=> 'ucp_groups',
			'title'		=> 'UCP_USERGROUPS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'membership'	=> ['title' => 'UCP_USERGROUPS_MEMBER', 'auth' => '', 'cat' => ['UCP_USERGROUPS']],
				'manage'		=> ['title' => 'UCP_USERGROUPS_MANAGE', 'auth' => '', 'cat' => ['UCP_USERGROUPS']],
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
