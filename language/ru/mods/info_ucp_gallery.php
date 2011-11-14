<?php
/**
*
* info_ucp_gallery [Russian] (Pthelovod v1.1.4)
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

$lang = array_merge($lang, array(
	'UCP_GALLERY'						=> 'Галерея',
	'UCP_GALLERY_FAVORITES'				=> 'Управление избранными',
	'UCP_GALLERY_PERSONAL_ALBUMS'		=> 'Управление персональными альбомами',
	'UCP_GALLERY_SETTINGS'				=> 'Личные настройки',
	'UCP_GALLERY_WATCH'					=> 'Управление подписками',
));

?>