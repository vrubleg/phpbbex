<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_email_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_email',
			'title'		=> 'ACP_MASS_EMAIL',
			'version'	=> '1.0.0',
			'modes'		=> [
				'email'		=> ['title' => 'ACP_MASS_EMAIL', 'auth' => 'acl_a_email && cfg_email_enable', 'cat' => ['ACP_GENERAL_TASKS']],
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
