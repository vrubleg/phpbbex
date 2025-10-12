<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_bbcodes_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_bbcodes',
			'title'		=> 'ACP_BBCODES',
			'version'	=> '1.0.0',
			'modes'		=> [
				'bbcodes'		=> ['title' => 'ACP_BBCODES', 'auth' => 'acl_a_bbcode', 'cat' => ['ACP_MESSAGES']],
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
