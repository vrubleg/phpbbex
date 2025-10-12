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
		return [
			'filename'	=> 'acp_php_info',
			'title'		=> 'ACP_PHP_INFO',
			'version'	=> '1.0.0',
			'modes'		=> [
				'info'		=> ['title' => 'ACP_PHP_INFO', 'auth' => 'acl_a_phpinfo', 'cat' => ['ACP_GENERAL_TASKS']],
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
