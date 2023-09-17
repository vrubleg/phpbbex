<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_board_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_board',
			'title'		=> 'ACP_BOARD_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'settings'		=> array('title' => 'ACP_BOARD_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'features'		=> array('title' => 'ACP_BOARD_FEATURES', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'style'			=> array('title' => 'ACP_STYLE_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'avatar'		=> array('title' => 'ACP_AVATAR_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'message'		=> array('title' => 'ACP_MESSAGE_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION', 'ACP_MESSAGES')),
				'post'			=> array('title' => 'ACP_POST_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION', 'ACP_MESSAGES')),
				'signature'		=> array('title' => 'ACP_SIGNATURE_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'feed'			=> array('title' => 'ACP_FEED_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'registration'	=> array('title' => 'ACP_REGISTER_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),

				'email'		=> array('title' => 'ACP_EMAIL_SETTINGS', 'auth' => 'acl_a_server', 'cat' => array('ACP_CLIENT_COMMUNICATION')),

				'auth'		=> array('title' => 'ACP_AUTH_SETTINGS', 'auth' => 'acl_a_server', 'cat' => array('ACP_SERVER_CONFIGURATION')),
				'cookie'	=> array('title' => 'ACP_COOKIE_SETTINGS', 'auth' => 'acl_a_server', 'cat' => array('ACP_SERVER_CONFIGURATION')),
				'server'	=> array('title' => 'ACP_SERVER_SETTINGS', 'auth' => 'acl_a_server', 'cat' => array('ACP_SERVER_CONFIGURATION')),
				'security'	=> array('title' => 'ACP_SECURITY_SETTINGS', 'auth' => 'acl_a_server', 'cat' => array('ACP_SERVER_CONFIGURATION')),
				'load'		=> array('title' => 'ACP_LOAD_SETTINGS', 'auth' => 'acl_a_server', 'cat' => array('ACP_SERVER_CONFIGURATION')),

				'logs'		=> array('title' => 'ACP_LOGGING_SETTINGS', 'auth' => 'acl_a_clearlogs', 'cat' => array('ACP_FORUM_LOGS')),
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
