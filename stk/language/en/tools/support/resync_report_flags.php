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
	'RESYNC_REPORT_FLAGS'			=> 'Resynchronise report flags',
	'RESYNC_REPORT_FLAGS_CONFIRM'	=> 'This tool will resynchronise the report flags for all posts, topics and private messages.',
	'RESYNC_REPORT_FLAGS_FINISHED'	=> 'All report flags have successfully been resynchronised!',
	'RESYNC_REPORT_FLAGS_NEXT'		=> 'Resynchronising report flags in progress. Please do not interrupt this process.',
));
