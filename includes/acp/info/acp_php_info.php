<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_php_info_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_php_info',
			'title'		=> 'ACP_PHP_INFO',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'info'		=> array('title' => 'ACP_PHP_INFO', 'auth' => 'acl_a_phpinfo', 'cat' => array('ACP_GENERAL_TASKS')),
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
