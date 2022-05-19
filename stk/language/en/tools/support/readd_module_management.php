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
	'READD_MODULE_MANAGEMENT'			=> 'Recreate Module Management modules',
	'READD_MODULE_MANAGEMENT_CONFIRM'	=> 'Are you sure you want to recreate the Module Management modules in the ACP?',
	'READD_MODULE_MANAGEMENT_SUCCESS'	=> 'The modules have been recreated successfully!',
));
