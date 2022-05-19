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
	'RESYNC_USER_GROUPS'			=> 'Resynchronise user groups',
	'RESYNC_USER_GROUPS_EXPLAIN'	=> 'This tool is designed to check whether all users are part of the correct default groups <em>(Registered Users, Registered COPPA Users and Newly Registered Users)</em>.',
	'RESYNC_USER_GROUPS_NO_RUN'		=> 'All groups seem to be up to date!',
	'RESYNC_USER_GROUPS_SETTINGS'	=> 'Resynchronise options',
	'RUN_BOTH_FINISHED'				=> 'All user groups have been resynchronised successfully!',
	'RUN_RNR'						=> 'Resynchronise newly registered users',
	'RUN_RNR_EXPLAIN'				=> 'This will update the "Newly Registered Users" group so that it contains all users that fit the criteria specified in the ACP.',
	'RUN_RNR_FINISHED'				=> 'The Newly Registered Users group was successfully resynchronised!',
	'RUN_RNR_NOT_FINISHED'			=> 'The Newly Registered Users group is currently being resynchronised. Please don’t interrupt this process.',
	'RUN_RR'						=> 'Resynchronise registered users',
	'RUN_RR_EXPLAIN'				=> 'The tool has determined that not all users on your board are part of the "Registered <em>(COPPA)</em> users" group. Do you want to resyncronise these groups?<br /><strong>Note:</strong> If your board has COPPA enabled an a user hasn\'t entered a date of birth the user will be placed in the "Registered COPPA users" group!',
	'RUN_RR_FINISHED'				=> 'The users have been resynchronised successfully!',

	'SELECT_RUN_GROUP'	=> 'Select at least one group type that will be resynchronised.',
));
