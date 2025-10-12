<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_icons_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_icons',
			'title'		=> 'ACP_ICONS_SMILIES',
			'version'	=> '1.0.0',
			'modes'		=> [
				'icons'		=> ['title' => 'ACP_ICONS', 'auth' => 'acl_a_icons', 'cat' => ['ACP_MESSAGES']],
				'smilies'	=> ['title' => 'ACP_SMILIES', 'auth' => 'acl_a_icons', 'cat' => ['ACP_MESSAGES']],
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
