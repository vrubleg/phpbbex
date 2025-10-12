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
		return [
			'filename'	=> 'acp_outlinks',
			'title'		=> 'ACP_OUTLINKS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'outlinks'		=> ['title' => 'ACP_OUTLINKS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION']],
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
