<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_disallow_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_disallow',
			'title'		=> 'ACP_DISALLOW',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'usernames'		=> array('title' => 'ACP_DISALLOW_USERNAMES', 'auth' => 'acl_a_names', 'cat' => array('ACP_USER_SECURITY')),
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
