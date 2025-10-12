<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class mcp_pm_reports_info
{
	function module()
	{
		return [
			'filename'	=> 'mcp_pm_reports',
			'title'		=> 'MCP_PM_REPORTS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'pm_reports'		=> ['title' => 'MCP_PM_REPORTS_OPEN', 'auth' => 'aclf_m_report', 'cat' => ['MCP_REPORTS']],
				'pm_reports_closed'	=> ['title' => 'MCP_PM_REPORTS_CLOSED', 'auth' => 'aclf_m_report', 'cat' => ['MCP_REPORTS']],
				'pm_report_details'	=> ['title' => 'MCP_PM_REPORT_DETAILS', 'auth' => 'aclf_m_report', 'cat' => ['MCP_REPORTS']],
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
