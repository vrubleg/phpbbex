<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_ranks_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_ranks',
			'title'		=> 'ACP_RANKS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'ranks'		=> array('title' => 'ACP_MANAGE_RANKS', 'auth' => 'acl_a_ranks', 'cat' => array('ACP_CAT_USERS')),
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
