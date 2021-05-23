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
		return array(
			'filename'	=> 'acp_main',
			'title'		=> 'ACP_INDEX',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array('title' => 'ACP_INDEX', 'auth' => '', 'cat' => array('ACP_CAT_GENERAL')),
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
