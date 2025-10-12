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
	'ACTIVE_TOPICS'			=> 'Active topics',
	'ANNOUNCEMENTS'			=> 'Announcements',

	'FORUM_PERMISSIONS'		=> 'Forum permissions',

	'ICON_ANNOUNCEMENT'		=> 'Announcement',
	'ICON_STICKY'			=> 'Sticky',

	'LOGIN_NOTIFY_FORUM'	=> 'You have been notified about this forum, please login to view it.',
	'NO_READ_ACCESS'		=> 'You do not have the required permissions to read topics within this forum.',

	'MARK_TOPICS_READ'		=> 'Mark topics read',

	'NEW_POSTS_LOCKED'		=> 'New posts [ Locked ]',	// Not used anymore
	'NO_NEW_POSTS_LOCKED'	=> 'No new posts [ Locked ]',	// Not used anymore
	'NO_UNREAD_POSTS_LOCKED'	=> 'No unread posts [ Locked ]',

	'POST_FORUM_LOCKED'		=> 'Forum is locked',

	'TOPICS_MARKED'			=> 'The topics for this forum have now been marked read.',

	'UNREAD_POSTS_LOCKED'	=> 'Unread posts [ Locked ]',

	'VIEW_FORUM'			=> 'View forum',
	'VIEW_FORUM_TOPIC'		=> '1 topic',
	'VIEW_FORUM_TOPICS'		=> '%d topics',
]);
