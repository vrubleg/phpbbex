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
		return array(
			'filename'	=> 'ucp_zebra',
			'title'		=> 'UCP_ZEBRA',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'friends'		=> array('title' => 'UCP_ZEBRA_FRIENDS', 'auth' => '', 'cat' => array('UCP_ZEBRA')),
				'foes'			=> array('title' => 'UCP_ZEBRA_FOES', 'auth' => '', 'cat' => array('UCP_ZEBRA')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
