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
		return [
			'filename'	=> 'acp_jabber',
			'title'		=> 'ACP_JABBER_SETTINGS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'settings'		=> ['title' => 'ACP_JABBER_SETTINGS', 'auth' => 'acl_a_server', 'cat' => ['ACP_CLIENT_COMMUNICATION']],
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
