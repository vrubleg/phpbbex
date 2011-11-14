<?php
/**
*
* info_acp_gallery_logs [Russian] (Pthelovod v1.1.4)
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
	'LOG_ALBUM_ADD'							=> '<strong>Создан новый альбом</strong><br />» %s',
	'LOG_ALBUM_DEL_ALBUM'					=> '<strong>Удален альбом</strong><br />» %s',
	'LOG_ALBUM_DEL_ALBUMS'					=> '<strong>Удален альбом и его под-альбомы</strong><br />» %s',
	'LOG_ALBUM_DEL_MOVE_ALBUMS'				=> '<strong>Удален альбом, под-альбомы перемещены</strong> в %1$s<br />» %2$s',
	'LOG_ALBUM_DEL_MOVE_IMAGES'				=> '<strong>Удален альбом, изображения перемещены </strong> в %1$s<br />» %2$s',
	'LOG_ALBUM_DEL_MOVE_IMAGES_ALBUMS'		=> '<strong>Удален альбом и его под-альбомы, изображения перемещены</strong> в %1$s<br />» %2$s',
	'LOG_ALBUM_DEL_MOVE_IMAGES_MOVE_ALBUMS'	=> '<strong>Удален альбом, изображения перемещены</strong> в %1$s <strong>, под-альбомы перемещены</strong> в %2$s<br />» %3$s',
	'LOG_ALBUM_DEL_IMAGES'					=> '<strong>Удален альбом и его изображения</strong><br />» %s',
	'LOG_ALBUM_DEL_IMAGES_ALBUMS'			=> '<strong>Удален альбом, его изображения и под-альбомы</strong><br />» %s',
	'LOG_ALBUM_DEL_IMAGES_MOVE_ALBUMS'		=> '<strong>Удален альбом и его изображения, под-альбомы перемещены</strong> в %1$s<br />» %2$s',
	'LOG_ALBUM_EDIT'						=> '<strong>Изменены настройки альбома</strong><br />» %s',
	'LOG_ALBUM_MOVE_DOWN'					=> '<strong>Альбом перемещен</strong> %1$s <strong>ниже</strong> %2$s',
	'LOG_ALBUM_MOVE_UP'						=> '<strong>Альбом перемещен</strong> %1$s <strong>выше</strong> %2$s',
	'LOG_ALBUM_SYNC'						=> '<strong>Альбом ресинхронизирован</strong><br />» %s',

	'LOG_CLEAR_GALLERY'					=> 'Лог галереи очищен',

	'LOG_GALLERY_APPROVED'				=> '<strong>Изображение одобрено</strong><br />» %s',
	'LOG_GALLERY_COMMENT_DELETED'		=> '<strong>Комментарий удален</strong><br />» %s',
	'LOG_GALLERY_COMMENT_EDITED'		=> '<strong>Комментарий изменен</strong><br />» %s',
	'LOG_GALLERY_DELETED'				=> '<strong>Изображение удалено</strong><br />» %s',
	'LOG_GALLERY_EDITED'				=> '<strong>Изображение отредактировано</strong><br />» %s',
	'LOG_GALLERY_LOCKED'				=> '<strong>Изображение закрыто</strong><br />» %s',
	'LOG_GALLERY_MOVED'					=> '<strong>Изображение перемещено</strong><br />» from %1$s to %2$s',
	'LOG_GALLERY_REPORT_CLOSED'			=> '<strong>Жалоба закрыта</strong><br />» %s',
	'LOG_GALLERY_REPORT_DELETED'		=> '<strong>Жалоба удалена</strong><br />» %s',
	'LOG_GALLERY_REPORT_OPENED'			=> '<strong>Жалоба открыта</strong><br />» %s',
	'LOG_GALLERY_UNAPPROVED'			=> '<strong>Изображение не одобрено</strong><br />» %s',

	'LOGVIEW_VIEWALBUM'					=> 'Просмотр альбома',
	'LOGVIEW_VIEWIMAGE'					=> 'Просмотр изображения',
));

?>