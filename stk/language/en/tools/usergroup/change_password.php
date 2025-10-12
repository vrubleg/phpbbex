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
	$lang = [];
}

$lang = array_merge($lang, [
	'CHANGE_PASSWORD'			=> 'Change Password',
	'CHANGE_PASSWORD_EXPLAIN'	=> 'Change a user’s password.<br /><strong>You may enter either the Username or User ID, not both.</strong>',
	'CHANGE_PASSWORD_SUCCESS'	=> 'The password for <a href="%s">%s</a> has been successfully changed.',

	'FIELDS_NOT_FILLED'			=> 'One field must be filled in.',
	'FIELDS_BOTH_FILLED'		=> 'Only one field may be filled in.',

	'PASSWORD_CONFIRM'			=> 'Re-Enter Password',

	'USERNAME_NAME'				=> 'Username',
	'USERNAME_NAME_EXPLAIN'		=> 'Enter the Username of the user whose password you want to change.',
	'USERNAMEID'				=> 'User ID',
	'USERNAMEID_EXPLAIN'		=> 'Enter the User ID of the user whose password you want to change.',
]);
