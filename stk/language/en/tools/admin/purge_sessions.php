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
	'PURGE_SESSIONS'			=> 'Purge Sessions',
	'PURGE_SESSIONS_COMPLETE'	=> 'Sessions have been purged successfully.',
	'PURGE_SESSIONS_CONFIRM'	=> 'This tool will remove all current sessions and log out all users. Are you sure that you want to continue?',
]);
