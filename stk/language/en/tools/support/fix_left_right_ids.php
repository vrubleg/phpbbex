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
	'FIX_LEFT_RIGHT_IDS'			=> 'Fix Left/Right IDs',
	'FIX_LEFT_RIGHT_IDS_CONFIRM'	=> 'Are you sure you want to fix the left and right IDs?<br /><br /><strong>Backup your database before running this tool!</strong>',

	'LEFT_RIGHT_IDS_FIX_SUCCESS'	=> 'The left/right IDs have been successfully fixed.',
	'LEFT_RIGHT_IDS_NO_CHANGE'		=> 'The tool has finished going through all of the left and right IDs and all rows are already correct so no changes were made.',
]);
