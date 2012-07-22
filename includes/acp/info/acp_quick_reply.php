<?php
/** 
*
* @package acp
* @version $Id: acp_quick_reply.php,v 1.00 2007/07/17 00:05:43 davidmj Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
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

?>