<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_reasons_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_reasons',
			'title'		=> 'ACP_REASONS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array('title' => 'ACP_MANAGE_REASONS', 'auth' => 'acl_a_reasons', 'cat' => array('ACP_GENERAL_TASKS')),
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
