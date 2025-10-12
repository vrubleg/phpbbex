<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_modules_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_modules',
			'title'		=> 'ACP_MODULE_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> [
				'acp'		=> ['title' => 'ACP', 'auth' => 'acl_a_modules', 'cat' => ['ACP_MODULE_MANAGEMENT']],
				'ucp'		=> ['title' => 'UCP', 'auth' => 'acl_a_modules', 'cat' => ['ACP_MODULE_MANAGEMENT']],
				'mcp'		=> ['title' => 'MCP', 'auth' => 'acl_a_modules', 'cat' => ['ACP_MODULE_MANAGEMENT']],
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
