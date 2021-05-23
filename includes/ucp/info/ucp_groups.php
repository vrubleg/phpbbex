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
		return array(
			'filename'	=> 'ucp_groups',
			'title'		=> 'UCP_USERGROUPS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'membership'	=> array('title' => 'UCP_USERGROUPS_MEMBER', 'auth' => '', 'cat' => array('UCP_USERGROUPS')),
				'manage'		=> array('title' => 'UCP_USERGROUPS_MANAGE', 'auth' => '', 'cat' => array('UCP_USERGROUPS')),
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
