<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_send_statistics_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_send_statistics',
			'title'		=> 'ACP_SEND_STATISTICS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'send_statistics'		=> array('title' => 'ACP_SEND_STATISTICS', 'auth' => 'acl_a_server', 'cat' => array('ACP_SERVER_CONFIGURATION')),
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
