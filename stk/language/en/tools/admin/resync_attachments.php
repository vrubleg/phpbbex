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
	'RESYNC_ATTACHMENTS'			=> 'Resynchronise attachments',
	'RESYNC_ATTACHMENTS_CONFIRM'	=> 'This tool will make sure that all attachments stored in the database actually have a file on the server. If the file is missing, this tool will remove the attachment from the database. Are you sure that you want to continue?',
	'RESYNC_ATTACHMENTS_FINISHED'	=> 'Attachments successfully resynchronised!',
	'RESYNC_ATTACHMENTS_PROGRESS'	=> 'Resynchronising attachments in progress. Please do not interrupt this process.',
]);
