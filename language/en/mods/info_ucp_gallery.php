<?php
/**
*
* info_ucp_gallery [English]
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

$lang = array_merge($lang, array(
	'UCP_GALLERY'						=> 'Gallery',
	'UCP_GALLERY_FAVORITES'				=> 'Manage favorites',
	'UCP_GALLERY_PERSONAL_ALBUMS'		=> 'Manage personal albums',
	'UCP_GALLERY_SETTINGS'				=> 'Personal settings',
	'UCP_GALLERY_WATCH'					=> 'Manage subscriptions',
));
