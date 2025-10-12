<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class ucp_pm_info
{
	function module()
	{
		return [
			'filename'	=> 'ucp_pm',
			'title'		=> 'UCP_PM',
			'version'	=> '1.0.0',
			'modes'		=> [
				'view'		=> ['title' => 'UCP_PM_VIEW', 'auth' => 'cfg_allow_privmsg', 'display' => false, 'cat' => ['UCP_PM']],
				'compose'	=> ['title' => 'UCP_PM_COMPOSE', 'auth' => 'cfg_allow_privmsg', 'cat' => ['UCP_PM']],
				'drafts'	=> ['title' => 'UCP_PM_DRAFTS', 'auth' => 'cfg_allow_privmsg', 'cat' => ['UCP_PM']],
				'options'	=> ['title' => 'UCP_PM_OPTIONS', 'auth' => 'cfg_allow_privmsg', 'cat' => ['UCP_PM']],
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
