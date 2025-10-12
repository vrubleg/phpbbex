<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class mcp_notes_info
{
	function module()
	{
		return [
			'filename'	=> 'mcp_notes',
			'title'		=> 'MCP_NOTES',
			'version'	=> '1.0.0',
			'modes'		=> [
				'front'				=> ['title' => 'MCP_NOTES_FRONT', 'auth' => '', 'cat' => ['MCP_NOTES']],
				'user_notes'		=> ['title' => 'MCP_NOTES_USER', 'auth' => '', 'cat' => ['MCP_NOTES']],
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
