<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class ucp_zebra_info
{
	function module()
	{
		return [
			'filename'	=> 'ucp_zebra',
			'title'		=> 'UCP_ZEBRA',
			'version'	=> '1.0.0',
			'modes'		=> [
				'friends'		=> ['title' => 'UCP_ZEBRA_FRIENDS', 'auth' => '', 'cat' => ['UCP_ZEBRA']],
				'foes'			=> ['title' => 'UCP_ZEBRA_FOES', 'auth' => '', 'cat' => ['UCP_ZEBRA']],
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
