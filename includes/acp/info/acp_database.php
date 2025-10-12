<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_database_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_database',
			'title'		=> 'ACP_DATABASE',
			'version'	=> '1.0.0',
			'modes'		=> [
				'backup'	=> ['title' => 'ACP_BACKUP', 'auth' => 'acl_a_backup', 'cat' => ['ACP_CAT_DATABASE']],
				'restore'	=> ['title' => 'ACP_RESTORE', 'auth' => 'acl_a_backup', 'cat' => ['ACP_CAT_DATABASE']],
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
