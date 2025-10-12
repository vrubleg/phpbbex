<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_attachments_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_attachments',
			'title'		=> 'ACP_ATTACHMENTS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'attach'		=> ['title' => 'ACP_ATTACHMENT_SETTINGS', 'auth' => 'acl_a_attach', 'cat' => ['ACP_BOARD_CONFIGURATION', 'ACP_ATTACHMENTS']],
				'extensions'	=> ['title' => 'ACP_MANAGE_EXTENSIONS', 'auth' => 'acl_a_attach', 'cat' => ['ACP_ATTACHMENTS']],
				'ext_groups'	=> ['title' => 'ACP_EXTENSION_GROUPS', 'auth' => 'acl_a_attach', 'cat' => ['ACP_ATTACHMENTS']],
				'orphan'		=> ['title' => 'ACP_ORPHAN_ATTACHMENTS', 'auth' => 'acl_a_attach', 'cat' => ['ACP_ATTACHMENTS']]
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
