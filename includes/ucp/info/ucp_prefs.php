<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class ucp_prefs_info
{
	function module()
	{
		return [
			'filename'	=> 'ucp_prefs',
			'title'		=> 'UCP_PREFS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'personal'	=> ['title' => 'UCP_PREFS_PERSONAL', 'auth' => '', 'cat' => ['UCP_PREFS']],
				'post'		=> ['title' => 'UCP_PREFS_POST', 'auth' => '', 'cat' => ['UCP_PREFS']],
				'view'		=> ['title' => 'UCP_PREFS_VIEW', 'auth' => '', 'cat' => ['UCP_PREFS']],
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
