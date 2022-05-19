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
	'ADD_USER'				=> 'Add user',
	'ADD_USER_GROUP'		=> 'Add user to groups',

	'DEFAULT_GROUP'			=> 'Default group',
	'DEFAULT_GROUP_EXPLAIN'	=> 'The default group for this user.',

	'GROUP_LEADER'			=> 'Group leader',
	'GROUP_LEADER_EXPLAIN'	=> 'Make this user the group leader of the selected groups.',

	'USER_ADDED'			=> 'The user was sucessfully created!',
	'USER_GROUPS'			=> 'User groups',
	'USER_GROUPS_EXPLAIN'	=> 'Make this user a member of the selected groups.',
));
