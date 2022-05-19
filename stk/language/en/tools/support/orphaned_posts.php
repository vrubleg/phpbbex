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
	'AUTHOR'					=> 'Author',
	'FORUM_NAME'				=> 'Forum Name',
	'NEW_TOPIC_ID'				=> 'New Topic ID',
	'POST_ID'					=> 'Post ID',
	'TOPIC_ID'					=> 'Topic ID',

	'DELETE_EMPTY_TOPICS'		=> 'Delete all selected topics by clicking on this button. (Can’t be undone!)',
	'EMPTY_TOPICS'				=> 'Empty Topics',
	'EMPTY_TOPICS_EXPLAIN'		=> 'These are topics that have no posts associated with them.',
	'NO_EMPTY_TOPICS'			=> 'No empty topics found',
	'NO_TOPICS_SELECTED'		=> 'No topics selected',

	'ORPHANED_POSTS'			=> 'Orphaned Posts',
	'ORPHANED_POSTS_EXPLAIN'	=> 'These are posts that do not have a topic associated with them. Specify a new topic ID to have the post attached to that topic.',
	'NO_ORPHANED_POSTS'			=> 'No orphaned posts found',
	'NO_TOPIC_IDS'				=> 'No topic IDs provided',
	'NONEXISTENT_TOPIC_IDS'		=> 'The following target topic IDs do not exist: %s.<br />Please verify the specified topic IDs.',
	'REASSIGN'					=> 'Reassign',

	'DELETE_SHADOWS'			=> 'Delete all selected shadow topics by clicking on this button. (Can’t be undone!)',
	'ORPHANED_SHADOWS'			=> 'Orphaned Shadow Topics',
	'ORPHANED_SHADOWS_EXPLAIN'	=> 'These are shadow topics whose target topic no longer exists.',
	'NO_ORPHANED_SHADOWS'		=> 'No orphaned shadow topics found',

	'POSTS_DELETED'				=> '%d posts deleted',
	'POSTS_REASSIGNED'			=> '%d posts re-assigned',
	'TOPICS_DELETED'			=> '%d topics deleted',
));
