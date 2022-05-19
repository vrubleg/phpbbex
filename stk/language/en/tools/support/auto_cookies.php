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
	'AUTO_COOKIES'				=> 'Auto Cookies',
	'AUTO_COOKIES_EXPLAIN'		=> 'This tool allows you to change your forum’s cookie settings. The suggested settings should be correct in most cases. If you are unsure of the correct settings, please seek guidance in the Support Forum before changing any settings as incorrect settings may prevent you from being able to log into your forum.',

	'COOKIE_SETTINGS_UPDATED'	=> 'Cookie settings successfully updated.',
));
