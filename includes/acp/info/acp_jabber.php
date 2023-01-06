<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_jabber_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_jabber',
			'title'		=> 'ACP_JABBER_SETTINGS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'settings'		=> array('title' => 'ACP_JABBER_SETTINGS', 'auth' => 'acl_a_server', 'cat' => array('ACP_CLIENT_COMMUNICATION')),
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
