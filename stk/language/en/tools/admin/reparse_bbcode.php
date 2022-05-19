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
	'REPARSE_ALL'				=> 'Reparse all BBCodes',
	'REPARSE_ALL_EXPLAIN'		=> 'When checked the BBCode reparse will reparse all content on the board; by default, the tool will only reparse posts/private messages/signatures that have been previously parsed by phpBB. This option will be ignored if specific posts or PMs are specified above.',
	'REPARSE_BBCODE'			=> 'Reparse BBCode',
	'REPARSE_BBCODE_COMPLETE'	=> 'BBCodes have been reparsed.',
	'REPARSE_BBCODE_CONFIRM'	=> 'Are you sure you want to reparse all BBCodes? Please note that this tool has the potential to damage your database beyond repair; therefore, <strong>be sure to backup your database before proceeding</strong>. Moreover, note that this tool may take some time to complete.',
	'REPARSE_BBCODE_PROGRESS'	=> 'Step %1$d completed. Moving on to step %2$d in a moment...',
	'REPARSE_BBCODE_SWITCH_MODE'	=> array(
		1	=> 'Finished reparsing the posts, moving on to private messages.',
		2	=> 'Finished reparsing private messages, moving on to signatures.',
	),
	'REPARSE_IDS_INVALID'			=> 'The IDs you submitted were not valid; please ensure that post IDs are listed as a comma separated list (e.g. 1,2,3,5,8,13).',
	'REPARSE_POST_IDS'				=> 'Reparse Specific Posts',
	'REPARSE_POST_IDS_EXPLAIN'		=> 'To reparse specific posts only, specify post IDs in a comma-separated list (e.g. 1,2,3,5,8,13).',
	'REPARSE_PM_IDS'				=> 'Reparse Specific PMs',
	'REPARSE_PM_IDS_EXPLAIN'		=> 'To reparse specific PMs only, specifiy PM IDs in a comma-separated list (e.g. 1,2,3,5,8,13).',
));
