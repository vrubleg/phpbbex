<?php
/**
*
* permissions_gallery [English]
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
**/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang['permission_cat']['gallery'] = 'phpBB Gallery';

// Adding the permissions
$lang = array_merge($lang, array(
	'acl_a_gallery_manage'		=> array('lang' => 'Can manage the phpBB Gallery adjustments',	'cat' => 'gallery'),
	'acl_a_gallery_albums'		=> array('lang' => 'Can add/edit albums and permissions',		'cat' => 'gallery'),
	'acl_a_gallery_import'		=> array('lang' => 'Can use the import-function',				'cat' => 'gallery'),
	'acl_a_gallery_cleanup'		=> array('lang' => 'Can clean up the phpBB Gallery',			'cat' => 'gallery'),
));
