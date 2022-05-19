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
	'RECLEAN_USERNAMES'					=> 'Reclean Usernames',
	'RECLEAN_USERNAMES_COMPLETE'		=> 'All usernames have been recleaned successfully.',
	'RECLEAN_USERNAMES_CONFIRM'			=> 'Are you sure you want to reclean all usernames?',
	'RECLEAN_USERNAMES_NOT_COMPLETE'	=> 'The reclean usernames tool is currently in progress... please do not interrupt this process.',
));
