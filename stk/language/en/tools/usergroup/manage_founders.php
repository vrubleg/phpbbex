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
	'BOTH_FIELDS_FILLED'		=> 'Both User Name field and User ID field may not be filled in.',

	'DEMOTE_FAILED'				=> 'Couldn’t remove Founder status of all users!',
	'DEMOTE_FOUNDERS'			=> 'Demote Founders',
	'DEMOTE_SUCCESSFULL'		=> 'Successfully removed the Founder status of %d users!',

	'FOUNDERS'					=> 'Users with Founder status',

	'MAKE_FOUNDER'				=> 'Make a user Board Founder',
	'MAKE_FOUNDER_CONFIRM'		=> 'Are you sure you want to make <a href="%1$s">%2$s</a> a Board Founder?  This will give <a href="%1$s">%2$s</a> the ability to delete your account, among other powers.',
	'MAKE_FOUNDER_FAILED'		=> 'Couldn’t promote this user to a founder',
	'MAKE_FOUNDER_SUCCESS'		=> 'Successfully made <a href="%1$s">%2$s</a> a Board Founder.',
	'MANAGE_FOUNDERS'			=> 'Manage board founders',

	'NO_FOUNDERS'				=> 'No Founders Found',

	'PROMOTE_FOUNDER'			=> 'Promote to Founder',

	'USER_NAME_TO_FOUNDER'			=> 'User name to make Founder',
	'USER_NAME_TO_FOUNDER_EXPLAIN'	=> 'Enter the User Name of the user you would like to make a Board Founder.',
	'USER_ID_TO_FOUNDER'			=> 'User ID to make Founder',
	'USER_ID_TO_FOUNDER_EXPLAIN'	=> 'Enter the User ID of the user you would like to make a Board Founder.',
));
