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
	'DUPLICATES_FOUND'						=> 'The tool has found and removed all duplicate permissions.',

	'NO_DUPLICATES_FOUND'					=> 'The tool has finished checking for duplicate permissions and has found none.',

	'REMOVE_DUPLICATE_PERMISSIONS'			=> 'Remove duplicate permissions',
	'REMOVE_DUPLICATE_PERMISSIONS_CONFIRM'	=> 'Are you sure you want to remove the duplicate permissions?',
]);
