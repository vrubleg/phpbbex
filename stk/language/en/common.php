<?php
/**
* @package phpBBex Support Toolkit
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'BACK_TOOL'							=> 'Back to last tool',
	'BOARD_FOUNDER_ONLY'					=> 'Only Board Founders may access the Support Toolkit.',

	'CAT_ADMIN'							=> 'Admin Tools',
	'CAT_ADMIN_EXPLAIN'					=> 'Administrative Tools may be used by an administrator to manage particular aspects of their forum and solve common problems.',
	'CAT_DEV'							=> 'Developer Tools',
	'CAT_DEV_EXPLAIN'					=> 'Developer Tools may be used by phpBB Developers and MODders to perform common tasks.',
	'CAT_ERK'							=> 'Emergency Repair Kit',
	'CAT_ERK_CONFIRM'					=> 'The emergency repair kit is a seperate package of the STK that is build to run some checks that can detect issues within your phpBB install that might prevent your board from working. Do you want to run it?',
	'CAT_MAIN'							=> 'Main',
	'CAT_MAIN_EXPLAIN'					=> 'The Support Toolkit (STK) may be used to fix common issues within a working installation of phpBB 3.0.x. It serves as a second Administration Control Panel, providing an administrator with a set of tools to resolve common problems that may prevent a phpBB3 installation from functioning properly.',
	'CAT_SUPPORT'						=> 'Support Tools',
	'CAT_SUPPORT_EXPLAIN'				=> 'Support Tools may be used to aid in the recovery of a phpBB 3.0.x installation that is no longer functioning properly.',
	'CAT_USERGROUP'						=> 'User/Group Tools',
	'CAT_USERGROUP_EXPLAIN'				=> 'User and Group Tools may be used to manage users and groups in ways that are not available in a stock phpBB 3.0.x installation.',
	'CONFIG_NOT_FOUND'					=> 'The STK configuration file couldn’t be loaded. Please check your installation',

	'DOWNLOAD_PASS'						=> 'Download the password file.',

	'EMERGENCY_LOGIN_NAME'				=> 'STK Emergency Login',
	'ERK'								=> 'Emergency Repair Kit',

	'FAIL_REMOVE_PASSWD'					=> 'Couldn’t remove the expired password file. Please remove this file manually!',

	'GEN_PASS_FAILED'					=> 'The Support Toolkit was unable to generate a new password. Please try again.',
	'GEN_PASS_FILE'						=> 'Generate password file.',
	'GEN_PASS_FILE_EXPLAIN'				=> 'If you aren’t able to login to phpBB you can use the internal authentication method of the Support Toolkit. To use this method you must <a href="%s"><strong>generate</strong></a> a new password file.',

	'INCORRECT_CLASS'					=> 'Incorrect class in: stk/tools/%1$s.%2$s',
	'INCORRECT_PASSWORD'					=> 'Password is incorrect',
	'INCORRECT_PHPBB_VERSION'			=> 'Your version of phpBB isn’t compatible with this tool. Your phpBB installation must be version %1$s or later in order to run this tool.',

	'LOGIN_STK_SUCCESS'					=> 'You have successfully authenticated and will now be redirected to the Support Toolkit.',

	'NOTICE'								=> 'Notice',

	'PASS_GENERATED'						=> 'Your STK password file was successfully generated!<br/>The password that was generated for you is: <em>%1$s</em><br />This password will expire on: <span style="text-decoration: underline;">%2$s</span>. After this time you <strong>must</strong> generate a new password file in order to keep using the emergency login feature!<br /><br />Use the following button to download the file. Once you’ve downloaded this file you must upload it to your server into the "stk" directory',
	'PASS_GENERATED_REDIRECT'			=> 'Once you have uploaded the password file to the correct location, click <a href="%s">here</a> to go back to the login page.',
	'PLUGIN_INCOMPATIBLE_PHPBB_VERSION'	=> 'This tool isn’t compatible with the version of phpBB that you are running',
	'PROCEED_TO_STK'						=> '%sProceed to the Support Toolkit%s',

	'STK_FOUNDER_ONLY'					=> 'You must re-authenticate yourself before you can use the Support Toolkit.',
	'STK_LOGIN'							=> 'Support Toolkit Login',
	'STK_LOGIN_WAIT'						=> 'You must wait three seconds before re-attempting login. Please try again.',
	'STK_LOGOUT'							=> 'STK Logout',
	'STK_LOGOUT_SUCCESS'					=> 'You have successfully logged out from the Support Toolkit.',
	'STK_NON_LOGIN'						=> 'Login to access the STK.',
	'SUPPORT_TOOL_KIT'					=> 'Support Toolkit',
	'SUPPORT_TOOL_KIT_INDEX'				=> 'Support Toolkit index',
	'SUPPORT_TOOL_KIT_PASSWORD'			=> 'Password',
	'SUPPORT_TOOL_KIT_PASSWORD_EXPLAIN'	=> 'Since you are not logged in to phpBB3 you must verify that you are a board founder by entering the Support Toolkit Password.<br /><br /><strong>Cookies MUST be allowed by your browser or you will not be able to stay logged in.</strong>',

	'TOOL_INCLUTION_NOT_FOUND'			=> 'This tool is attempting to load a file (%1$s) that does not exist.',
	'TOOL_NAME'							=> 'Tool Name',
	'TOOL_NOT_AVAILABLE'					=> 'The requested tool is not available.',

	'USING_STK_LOGIN'					=> 'You are logged in using the internal STK authentication method. It is advised to use this method <strong>only</strong> when you are unable to login to phpBB.<br />To disable this authentication method click <a href="%1$s">here</a>.',
));
