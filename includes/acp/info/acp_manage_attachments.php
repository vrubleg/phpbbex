<?php
/** 
*
* @package acp
* @version $Id: acp_manage_attachments.php,v 1.00 2008/01/24 22:23:42 rxu Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
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

?>