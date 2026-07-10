<?php
/**
* @package phpBBex
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
	'ABOUT_USER'            => 'Profile',
	'ACTIVE_IN_FORUM'       => 'Most active forum',
	'ACTIVE_IN_TOPIC'       => 'Most active topic',
	'ADD_FOE'               => 'Add foe',
	'ADD_FRIEND'            => 'Add friend',
	'AFTER'                 => 'After',

	'ALL'                   => 'All',

	'BEFORE'                => 'Before',

	'CONTACT_USER'          => 'Contact',

	'EMAIL_DISABLED'        => 'Sorry but all email related functions have been disabled.',
	'EMPTY_MESSAGE_IM'      => 'You must enter a message to be send.',
	'EQUAL_TO'              => 'Equal to',

	'FIND_USERNAME_EXPLAIN' => 'You do not need to fill out all fields. To match partial data use * as a wildcard. When entering dates use the format <kbd>YYYY-MM-DD</kbd>, e.g. <samp>2004-02-29</samp>.',

	'GROUP_LEADER'          => 'Group leader',

	'HIDE_MEMBER_SEARCH'    => 'Hide member search',

	'LAST_ACTIVE'               => 'Last active',
	'LESS_THAN'                 => 'Less than',
	'LIST_USER'                 => '1 user',
	'LIST_USERS'                => '%d users',
	'LOGIN_EXPLAIN_LEADERS'     => 'Log in to view the team listing',
	'LOGIN_EXPLAIN_MEMBERLIST'  => 'Log in to access the memberlist',
	'LOGIN_EXPLAIN_SEARCHUSER'  => 'Log in to search users',
	'LOGIN_EXPLAIN_VIEWPROFILE' => 'Log in to view profiles',

	'MORE_THAN'             => 'More than',

	'NO_VIEW_USERS'         => 'You are not authorised to view the member list or profiles.',

	'ORDER'                 => 'Order',
	'OTHER'                 => 'Other',

	'POST_IP'               => 'Posted from IP/domain',

	'REMOVE_FOE'            => 'Remove foe',
	'REMOVE_FRIEND'         => 'Remove friend',

	'SEARCH_USER_TOPICS'    => 'Search user’s topics',
	'SELECT_MARKED'         => 'Select marked',
	'SELECT_SORT_METHOD'    => 'Select sort method',
	'SEND_MESSAGE'          => 'Message',
	'SORT_EMAIL'            => 'Email',
	'SORT_LAST_ACTIVE'      => 'Last active',
	'SORT_POST_COUNT'       => 'Post count',
	'SORT_TOPICS_COUNT'     => 'Topics count',

	'USERNAME_BEGINS_WITH'  => 'Username begins with',
	'USER_ADMIN'            => 'Administer user',
	'USER_AGENT'            => 'User Agent',
	'USER_LAST_IP'          => 'Last IP',
	'USER_BAN'              => 'Banning',
	'USER_FORUM'            => 'User statistics',
	'USER_LAST_REMINDED'    => [
		0       => 'No reminder sent at this time',
		1       => '%1$d reminder sent<br />» %2$s',
	],
	'USER_ONLINE'           => 'Online',
	'USER_PRESENCE'         => 'Board presence',
	'USERS_PER_PAGE'        => 'Users per page',

	'VIEWING_PROFILE'       => 'Viewing profile - %s',
	'VISITED'               => 'Last visited',

	'WWW'                   => 'Website',
]);
