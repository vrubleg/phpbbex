<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class mcp_warn_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_warn',
			'title'		=> 'MCP_WARN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'				=> array('title' => 'MCP_WARN_FRONT', 'auth' => 'aclf_m_warn', 'cat' => array('MCP_WARN')),
				'list'				=> array('title' => 'MCP_WARN_LIST', 'auth' => 'aclf_m_warn', 'cat' => array('MCP_WARN')),
				'warn_user'			=> array('title' => 'MCP_WARN_USER', 'auth' => 'aclf_m_warn', 'cat' => array('MCP_WARN')),
				'warn_post'			=> array('title' => 'MCP_WARN_POST', 'auth' => 'acl_m_warn && acl_f_read,$id', 'cat' => array('MCP_WARN')),
				'warn_edit'			=> array('title' => 'MCP_WARN_EDIT', 'auth' => 'acl_m_warn', 'cat' => array('MCP_WARN')),
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
