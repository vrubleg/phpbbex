<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class mcp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_main',
			'title'		=> 'MCP_MAIN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'			=> array('title' => 'MCP_MAIN_FRONT', 'auth' => '', 'cat' => array('MCP_MAIN')),
				'forum_view'	=> array('title' => 'MCP_MAIN_FORUM_VIEW', 'auth' => 'acl_m_,$id', 'cat' => array('MCP_MAIN')),
				'topic_view'	=> array('title' => 'MCP_MAIN_TOPIC_VIEW', 'auth' => 'acl_m_,$id', 'cat' => array('MCP_MAIN')),
				'post_details'	=> array('title' => 'MCP_MAIN_POST_DETAILS', 'auth' => 'acl_m_,$id || (!$id && aclf_m_)', 'cat' => array('MCP_MAIN')),
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
