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
	'UPDATE_EMAIL_HASHES'				=> 'Update email hashes',
	'UPDATE_EMAIL_HASHES_CONFIRM'		=> 'In phpBB installations prior to phpBB 3.0.7, a switch from a 32 bit OS to a 64 bit OS would break email hashes. <em>(<a href="http://tracker.phpbb.com/browse/PHPBB3-9072">See the related bug report</a>)</em><br />This tool allows you update the hashes in the database so that they function properly.',
	'UPDATE_EMAIL_HASHES_COMPLETE'		=> 'All email hashes have been updated successfully!',
	'UPDATE_EMAIL_HASHES_NOT_COMPLETE'	=> 'Updating email hashes in progress.',
));
