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
	$lang = array();
}

$lang = array_merge($lang, array(
	'ABOUT_USER'			=> 'Profile',
	'ACTIVE_IN_FORUM'		=> 'Most active forum',
	'ACTIVE_IN_TOPIC'		=> 'Most active topic',
	'ADD_FOE'				=> 'Add foe',
	'ADD_FRIEND'			=> 'Add friend',
	'AFTER'					=> 'After',

	'ALL'					=> 'All',

	'BEFORE'				=> 'Before',

	'CC_EMAIL'				=> 'Send a copy of this email to yourself.',
	'CONTACT_USER'			=> 'Contact',

	'DEST_LANG'				=> 'Language',
	'DEST_LANG_EXPLAIN'		=> 'Select an appropriate language (if available) for the recipient of this message.',

	'EMAIL_BODY_EXPLAIN'	=> 'This message will be sent as plain text, do not include any HTML or BBCode. The return address for this message will be set to your email address.',
	'EMAIL_DISABLED'		=> 'Sorry but all email related functions have been disabled.',
	'EMAIL_SENT'			=> 'The email has been sent.',
	'EMAIL_TOPIC_EXPLAIN'	=> 'This message will be sent as plain text, do not include any HTML or BBCode. Please note that the topic information is already included in the message. The return address for this message will be set to your email address.',
	'EMPTY_ADDRESS_EMAIL'	=> 'You must provide a valid email address for the recipient.',
	'EMPTY_MESSAGE_EMAIL'	=> 'You must enter a message to be emailed.',
	'EMPTY_MESSAGE_IM'		=> 'You must enter a message to be send.',
	'EMPTY_NAME_EMAIL'		=> 'You must enter the real name of the recipient.',
	'EMPTY_SUBJECT_EMAIL'	=> 'You must specify a subject for the email.',
	'EQUAL_TO'				=> 'Equal to',

	'FIND_USERNAME_EXPLAIN'	=> 'You do not need to fill out all fields. To match partial data use * as a wildcard. When entering dates use the format <kbd>YYYY-MM-DD</kbd>, e.g. <samp>2004-02-29</samp>.',
	'FLOOD_EMAIL_LIMIT'		=> 'You cannot send another email at this time. Please try again later.',

	'GROUP_LEADER'			=> 'Group leader',

	'HIDE_MEMBER_SEARCH'	=> 'Hide member search',

	'IM_ADD_CONTACT'		=> 'Add Contact',
	'IM_DOWNLOAD_APP'		=> 'Download application',
	'IM_JABBER'				=> 'Please note that users may have selected to not receive unsolicited instant messages.',
	'IM_JABBER_SUBJECT'		=> 'This is an automated message please do not reply! Message from user %1$s at %2$s.',
	'IM_MESSAGE'			=> 'Your message',
	'IM_NAME'				=> 'Your Name',
	'IM_NO_DATA'			=> 'There is no suitable contact information for this user.',
	'IM_NO_JABBER'			=> 'Sorry, direct messaging of Jabber users is not supported on this board. You will need a Jabber client installed on your system to contact the recipient above.',
	'IM_RECIPIENT'			=> 'Recipient',
	'IM_SEND'				=> 'Send message',
	'IM_SEND_MESSAGE'		=> 'Send message',
	'IM_SENT_JABBER'		=> 'Your message to %1$s has been sent successfully.',
	'IM_USER'				=> 'Send an instant message',

	'LAST_ACTIVE'				=> 'Last active',
	'LESS_THAN'					=> 'Less than',
	'LIST_USER'					=> '1 user',
	'LIST_USERS'				=> '%d users',
	'LOGIN_EXPLAIN_LEADERS'		=> 'Log in to view the team listing',
	'LOGIN_EXPLAIN_MEMBERLIST'	=> 'Log in to access the memberlist',
	'LOGIN_EXPLAIN_SEARCHUSER'	=> 'Log in to search users',
	'LOGIN_EXPLAIN_VIEWPROFILE'	=> 'Log in to view profiles',

	'MORE_THAN'				=> 'More than',

	'NO_EMAIL'				=> 'You are not permitted to send email to this user.',
	'NO_VIEW_USERS'			=> 'You are not authorised to view the member list or profiles.',

	'ORDER'					=> 'Order',
	'OTHER'					=> 'Other',

	'POST_IP'				=> 'Posted from IP/domain',

	'REAL_NAME'				=> 'Recipient name',
	'RECIPIENT'				=> 'Recipient',
	'REMOVE_FOE'			=> 'Remove foe',
	'REMOVE_FRIEND'			=> 'Remove friend',

	'SEARCH_USER_TOPICS'	=> 'Search user’s topics',
	'SELECT_MARKED'			=> 'Select marked',
	'SELECT_SORT_METHOD'	=> 'Select sort method',
	'SEND_IM'				=> 'Instant messaging',
	'SEND_JABBER_MESSAGE'	=> 'Send Jabber message',
	'SEND_MESSAGE'			=> 'Message',
	'SORT_EMAIL'			=> 'Email',
	'SORT_LAST_ACTIVE'		=> 'Last active',
	'SORT_POST_COUNT'		=> 'Post count',
	'SORT_TOPICS_COUNT'		=> 'Topics count',

	'USERNAME_BEGINS_WITH'	=> 'Username begins with',
	'USER_ADMIN'			=> 'Administer user',
	'USER_AGENT'			=> 'User Agent',
	'USER_LAST_IP'			=> 'Last IP',
	'USER_BAN'				=> 'Banning',
	'USER_FORUM'			=> 'User statistics',
	'USER_LAST_REMINDED'	=> array(
		0		=> 'No reminder sent at this time',
		1		=> '%1$d reminder sent<br />» %2$s',
	),
	'USER_ONLINE'			=> 'Online',
	'USER_PRESENCE'			=> 'Board presence',
	'USERS_PER_PAGE'		=> 'Users per page',

	'VIEWING_PROFILE'		=> 'Viewing profile - %s',
	'VISITED'				=> 'Last visited',

	'WWW'					=> 'Website',
));
