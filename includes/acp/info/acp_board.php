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
		return [
			'filename'	=> 'acp_board',
			'title'		=> 'ACP_BOARD_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> [
				'settings'		=> ['title' => 'ACP_BOARD_SETTINGS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION']],
				'features'		=> ['title' => 'ACP_BOARD_FEATURES', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION']],
				'style'			=> ['title' => 'ACP_STYLE_SETTINGS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION']],
				'avatar'		=> ['title' => 'ACP_AVATAR_SETTINGS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION']],
				'message'		=> ['title' => 'ACP_MESSAGE_SETTINGS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION', 'ACP_MESSAGES']],
				'post'			=> ['title' => 'ACP_POST_SETTINGS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION', 'ACP_MESSAGES']],
				'signature'		=> ['title' => 'ACP_SIGNATURE_SETTINGS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION']],
				'feed'			=> ['title' => 'ACP_FEED_SETTINGS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION']],
				'registration'	=> ['title' => 'ACP_REGISTER_SETTINGS', 'auth' => 'acl_a_board', 'cat' => ['ACP_BOARD_CONFIGURATION']],

				'email'		=> ['title' => 'ACP_EMAIL_SETTINGS', 'auth' => 'acl_a_server', 'cat' => ['ACP_CLIENT_COMMUNICATION']],

				'auth'		=> ['title' => 'ACP_AUTH_SETTINGS', 'auth' => 'acl_a_server', 'cat' => ['ACP_SERVER_CONFIGURATION']],
				'server'	=> ['title' => 'ACP_SERVER_SETTINGS', 'auth' => 'acl_a_server', 'cat' => ['ACP_SERVER_CONFIGURATION']],
				'security'	=> ['title' => 'ACP_SECURITY_SETTINGS', 'auth' => 'acl_a_server', 'cat' => ['ACP_SERVER_CONFIGURATION']],
				'load'		=> ['title' => 'ACP_LOAD_SETTINGS', 'auth' => 'acl_a_server', 'cat' => ['ACP_SERVER_CONFIGURATION']],

				'logs'		=> ['title' => 'ACP_LOGGING_SETTINGS', 'auth' => 'acl_a_clearlogs', 'cat' => ['ACP_FORUM_LOGS']],
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
