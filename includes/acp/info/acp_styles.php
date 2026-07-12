<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_styles_info
{
	function module()
	{
		return [
			'filename'  => 'acp_styles',
			'title'     => 'ACP_STYLES',
			'version'   => '1.0.0',
			'modes'     => [
				'style'     => ['title' => 'ACP_STYLES', 'auth' => 'acl_a_styles', 'cat' => ['ACP_GENERAL_TASKS']],
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
