<?php
/**
*
* permissions_gallery [Russian]
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

$lang['permission_cat']['gallery'] = 'Галерея';

// Adding the permissions
$lang = array_merge($lang, array(
	'acl_a_gallery_manage'		=> array('lang' => 'Может настраивать галерею',				'cat' => 'gallery'),
	'acl_a_gallery_albums'		=> array('lang' => 'Может добавлять/редактировать альбомы и права доступа',	'cat' => 'gallery'),
	'acl_a_gallery_import'		=> array('lang' => 'Может импортировать фотографии',					'cat' => 'gallery'),
	'acl_a_gallery_cleanup'		=> array('lang' => 'Может очищать галерею',					'cat' => 'gallery'),
));
