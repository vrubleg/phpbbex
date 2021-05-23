<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class mcp_ban_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_ban',
			'title'		=> 'MCP_BAN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'user'		=> array('title' => 'MCP_BAN_USERNAMES', 'auth' => 'acl_m_ban', 'cat' => array('MCP_BAN')),
				'ip'		=> array('title' => 'MCP_BAN_IPS', 'auth' => 'acl_m_ban', 'cat' => array('MCP_BAN')),
				'email'		=> array('title' => 'MCP_BAN_EMAILS', 'auth' => 'acl_m_ban', 'cat' => array('MCP_BAN')),
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
