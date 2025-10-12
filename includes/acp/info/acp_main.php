<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_main_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_main',
			'title'		=> 'ACP_INDEX',
			'version'	=> '1.0.0',
			'modes'		=> [
				'main'		=> ['title' => 'ACP_INDEX', 'auth' => '', 'cat' => ['ACP_CAT_GENERAL']],
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
