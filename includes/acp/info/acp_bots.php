<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_bots_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_bots',
			'title'		=> 'ACP_BOTS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'bots'		=> ['title' => 'ACP_BOTS', 'auth' => 'acl_a_bots', 'cat' => ['ACP_GENERAL_TASKS']],
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
