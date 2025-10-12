<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_captcha_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_captcha',
			'title'		=> 'ACP_CAPTCHA',
			'version'	=> '1.0.0',
			'modes'		=> [
				'visual'		=> ['title' => 'ACP_VC_SETTINGS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION']],
				'img'			=> ['title' => 'ACP_VC_CAPTCHA_DISPLAY', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION'], 'display' => false]
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
