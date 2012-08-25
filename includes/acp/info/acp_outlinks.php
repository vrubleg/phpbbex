<?php
/**
*
* @package acp
* @version $Id: acp_words.php 8479 2008-03-29 00:22:48Z naderman $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class acp_outlinks_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_outlinks',
			'title'		=> 'ACP_OUTLINKS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'outlinks'		=> array('title' => 'ACP_OUTLINKS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
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