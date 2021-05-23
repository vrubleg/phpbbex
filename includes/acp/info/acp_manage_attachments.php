<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_manage_attachments_info
{
	function module()
	{
		global $user;

		return array(
			'filename'	=> 'acp_manage_attachments',
			'title'		=> 'ACP_ATTACHMENTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'attachments'	=> array('title' => 'ACP_MANAGE_ATTACHMENTS', 'auth' => 'acl_a_attach', 'cat' => array('ACP_ATTACHMENTS'))
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
