<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_outlinks_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_outlinks',
			'title'		=> 'ACP_OUTLINKS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'outlinks'		=> array('title' => 'ACP_OUTLINKS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
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
