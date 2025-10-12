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
	'ANONYMOUS_CLEANED'					=> 'The Anonymous user’s profile data has been successfully sanitised.',
	'ANONYMOUS_CORRECT'					=> 'The Anonymous user exists and is correctly configured.',
	'ANONYMOUS_CREATED'					=> 'The Anonymous user has been successfully re-created.',
	'ANONYMOUS_CREATION_FAILED'			=> 'It was not possible to recreate the Anonymous user. Please ask for further assistance in the phpBB.com Support Forum.',
	'ANONYMOUS_GROUPS_REMOVED'			=> 'The Anonymous user was successfully removed from all access groups.',
	'ANONYMOUS_MISSING'					=> 'The Anonymous user is missing.',
	'ANONYMOUS_MISSING_CONFIRM'			=> 'The Anonymous user is missing in your database. This user is used to allow guests to visit your board. Do you want to create a new one?',
	'ANONYMOUS_WRONG_DATA'				=> 'The Anonymous user’s profile data is incorrect.',
	'ANONYMOUS_WRONG_DATA_CONFIRM'		=> 'The Anonymous user’s profile data is partially incorrect. Would you like to repair this?',
	'ANONYMOUS_WRONG_GROUPS'			=> 'The Anonymous user improperly belongs to multiple user groups.',
	'ANONYMOUS_WRONG_GROUPS_CONFIRM'	=> 'The Anonymous user improperly belongs to multiple user groups. Would you like to remove the Anonymous user from all but the "GUESTS" group?',

	'REDIRECT_NEXT_STEP'				=> 'You are being redirected to the next step.',

	'SANITISE_ANONYMOUS_USER'			=> 'Sanitise Anonymous User',
]);
