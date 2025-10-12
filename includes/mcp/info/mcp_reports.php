<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class mcp_reports_info
{
	function module()
	{
		return [
			'filename'	=> 'mcp_reports',
			'title'		=> 'MCP_REPORTS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'reports'			=> ['title' => 'MCP_REPORTS_OPEN', 'auth' => 'aclf_m_report', 'cat' => ['MCP_REPORTS']],
				'reports_closed'	=> ['title' => 'MCP_REPORTS_CLOSED', 'auth' => 'aclf_m_report', 'cat' => ['MCP_REPORTS']],
				'report_details'	=> ['title' => 'MCP_REPORT_DETAILS', 'auth' => 'acl_m_report,$id || (!$id && aclf_m_report)', 'cat' => ['MCP_REPORTS']],
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
