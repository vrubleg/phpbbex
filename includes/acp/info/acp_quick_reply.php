<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_quick_reply_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_quick_reply',
			'title'		=> 'ACP_QUICK_REPLY',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'quick_reply'		=> array('title' => 'ACP_QUICK_REPLY', 'auth' => 'acl_a_board', 'cat' => array('ACP_MESSAGES')),
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
