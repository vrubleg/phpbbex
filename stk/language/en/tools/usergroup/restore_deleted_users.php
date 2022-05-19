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
	'DAMAGED_POSTS'			=> 'Damaged Posts',
	'DAMAGED_POSTS_EXPLAIN'	=> 'The following post IDs contain user information that is too damaged to restore. Please visit the <a href="https://www.phpbb.com/community/viewforum.php?f=46">support forums</a> to receive assistance to resolve this issue.',

	'NO_DELETED_USERS'	=> 'There are no deleted users that can be restored',
	'NO_USER_SELECTED'	=> 'No users selected!',

	'RESTORE_DELETED_USERS'						=> 'Restore Deleted Users',
	'RESTORE_DELETED_USERS_CONFLICT'			=> 'Restore Deleted Users :: Conflicted',
	'RESTORE_DELETED_USERS_CONFLICT_EXPLAIN'	=> 'This tool allows you to restore users that are deleted from the board but still have "guest" posts on the board.<br />These users will be assigned a random password that you must reset manually after the tool has been run; this tool does <b>not</b> provide a list of these generated passwords.<br /><br />During the last run this tool found some usernames that already exist on this board. Please provide a new name for these users.',
	'RESTORE_DELETED_USERS_EXPLAIN'				=> 'This tool allows you to restore users that are deleted from the board but still have "guest" posts on the board.<br />These users will be assigned a random password that you must reset manually after the tool has been run; this tool does <b>not</b> provide a list of these generated passwords.',

	'SELECT_USERS'	=> 'Select users to restore',

	'USER_RESTORED_SUCCESSFULLY'	=> 'The selected user has been restored successfully.',
	'USERS_RESTORED_SUCCESSFULLY'	=> 'The selected users have been restored successfully.',
));
