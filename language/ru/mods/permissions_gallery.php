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
	$lang = [];
}

$lang['permission_cat']['gallery'] = 'Галерея';

// Adding the permissions
$lang = array_merge($lang, [
	'acl_a_gallery_manage'		=> ['lang' => 'Может настраивать галерею',				'cat' => 'gallery'],
	'acl_a_gallery_albums'		=> ['lang' => 'Может добавлять/редактировать альбомы и права доступа',	'cat' => 'gallery'],
	'acl_a_gallery_import'		=> ['lang' => 'Может импортировать фотографии',					'cat' => 'gallery'],
	'acl_a_gallery_cleanup'		=> ['lang' => 'Может очищать галерею',					'cat' => 'gallery'],
]);
