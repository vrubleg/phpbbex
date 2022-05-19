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
	'RESYNC_AVATARS'			=> 'Resynchronise avatars',
	'RESYNC_AVATARS_CONFIRM'	=> 'This tool will make sure that all avatars used on the board actually exist on the server. When missing files are found the avatar will be removed from the users profile. Are you sure you want to continue?',
	'RESYNC_AVATARS_FINISHED'	=> 'Avatars successfully resynchronised!',
	'RESYNC_AVATARS_NEXT_MODE'	=> 'Switching to the group avatars, please don’t interrupt this process!',
	'RESYNC_AVATARS_PROGRESS'	=> 'Resynchronising avatars in process, please don’t interrupt this process!',
));
