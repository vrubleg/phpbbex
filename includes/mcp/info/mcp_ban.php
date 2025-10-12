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
		return [
			'filename'	=> 'mcp_ban',
			'title'		=> 'MCP_BAN',
			'version'	=> '1.0.0',
			'modes'		=> [
				'user'		=> ['title' => 'MCP_BAN_USERNAMES', 'auth' => 'acl_m_ban', 'cat' => ['MCP_BAN']],
				'ip'		=> ['title' => 'MCP_BAN_IPS', 'auth' => 'acl_m_ban', 'cat' => ['MCP_BAN']],
				'email'		=> ['title' => 'MCP_BAN_EMAILS', 'auth' => 'acl_m_ban', 'cat' => ['MCP_BAN']],
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
