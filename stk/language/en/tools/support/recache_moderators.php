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
	'RECACHE_MODERATORS'				=> 'Re-cache moderators',
	'RECACHE_MODERATORS_COMPLETE'		=> 'The moderator cache has been successfully rebuilt.',
	'RECACHE_MODERATORS_CONFIRM'		=> 'Are you sure you want to re-cache the moderators?',
]);
