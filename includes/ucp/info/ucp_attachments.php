<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class ucp_attachments_info
{
	function module()
	{
		return [
			'filename'	=> 'ucp_attachments',
			'title'		=> 'UCP_ATTACHMENTS',
			'version'	=> '1.0.0',
			'modes'		=> [
				'attachments'	=> ['title' => 'UCP_MAIN_ATTACHMENTS', 'auth' => 'acl_u_attach', 'cat' => ['UCP_MAIN']],
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
