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
		return array(
			'filename'	=> 'mcp_pm_reports',
			'title'		=> 'MCP_PM_REPORTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'pm_reports'		=> array('title' => 'MCP_PM_REPORTS_OPEN', 'auth' => 'aclf_m_report', 'cat' => array('MCP_REPORTS')),
				'pm_reports_closed'	=> array('title' => 'MCP_PM_REPORTS_CLOSED', 'auth' => 'aclf_m_report', 'cat' => array('MCP_REPORTS')),
				'pm_report_details'	=> array('title' => 'MCP_PM_REPORT_DETAILS', 'auth' => 'aclf_m_report', 'cat' => array('MCP_REPORTS')),
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
