<?php
/**
*
* info_ucp_gallery [Russian]
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
	'UCP_GALLERY'						=> 'Галерея',
	'UCP_GALLERY_FAVORITES'				=> 'Избранное',
	'UCP_GALLERY_PERSONAL_ALBUMS'		=> 'Личный альбом',
	'UCP_GALLERY_SETTINGS'				=> 'Настройка',
	'UCP_GALLERY_WATCH'					=> 'Подписки',
));
