<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_words_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_words',
			'title'		=> 'ACP_WORDS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'words'		=> ['title' => 'ACP_WORDS', 'auth' => 'acl_a_words', 'cat' => ['ACP_MESSAGES']],
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
