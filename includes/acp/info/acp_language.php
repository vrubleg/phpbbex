<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_language_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_language',
			'title'		=> 'ACP_LANGUAGE',
			'version'	=> '1.0.0',
			'modes'		=> [
				'lang_packs'		=> ['title' => 'ACP_LANGUAGE_PACKS', 'auth' => 'acl_a_language', 'cat' => ['ACP_GENERAL_TASKS']],
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
