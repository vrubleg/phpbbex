<?php
/**
*
* permissions_gallery [Russian] (Pthelovod v1.1.4)
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang['permission_cat']['gallery'] = 'phpBB Галерея';

// Adding the permissions
$lang = array_merge($lang, array(
	'acl_a_gallery_manage'		=> array('lang' => 'Может управлять настройками phpBB галереи',				'cat' => 'gallery'),
	'acl_a_gallery_albums'		=> array('lang' => 'Может добавлять/редактировать альбомы и права доступа',	'cat' => 'gallery'),
	'acl_a_gallery_import'		=> array('lang' => 'Может использовать функцию импорта',					'cat' => 'gallery'),
	'acl_a_gallery_cleanup'		=> array('lang' => 'Может делать очистку phpBB галереи',					'cat' => 'gallery'),
));
?>