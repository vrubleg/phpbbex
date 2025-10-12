<?php
/**
*
* info_acp_gallery_logs [Russian]
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

$lang = array_merge($lang, [
	'LOG_ALBUM_ADD'							=> '<strong>Создан новый альбом</strong><br />» %s',
	'LOG_ALBUM_DEL_ALBUM'					=> '<strong>Удалён альбом</strong><br />» %s',
	'LOG_ALBUM_DEL_ALBUMS'					=> '<strong>Удалён альбом и вложенные альбомы</strong><br />» %s',
	'LOG_ALBUM_DEL_MOVE_ALBUMS'				=> '<strong>Удалён альбом, вложенные альбомы перемещены</strong> в %1$s<br />» %2$s',
	'LOG_ALBUM_DEL_MOVE_IMAGES'				=> '<strong>Удалён альбом, фото перемещены </strong> в %1$s<br />» %2$s',
	'LOG_ALBUM_DEL_MOVE_IMAGES_ALBUMS'		=> '<strong>Удалён альбом и вложенные альбомы, фото перемещены</strong> в %1$s<br />» %2$s',
	'LOG_ALBUM_DEL_MOVE_IMAGES_MOVE_ALBUMS'	=> '<strong>Удалён альбом, фото перемещены</strong> в %1$s <strong>, вложенные альбомы перемещены</strong> в %2$s<br />» %3$s',
	'LOG_ALBUM_DEL_IMAGES'					=> '<strong>Удалён альбом и его фото</strong><br />» %s',
	'LOG_ALBUM_DEL_IMAGES_ALBUMS'			=> '<strong>Удалён альбом, его фото и вложенные альбомы</strong><br />» %s',
	'LOG_ALBUM_DEL_IMAGES_MOVE_ALBUMS'		=> '<strong>Удалён альбом и его фото, вложенные альбомы перемещены</strong> в %1$s<br />» %2$s',
	'LOG_ALBUM_EDIT'						=> '<strong>Изменены параметры альбома</strong><br />» %s',
	'LOG_ALBUM_MOVE_DOWN'					=> '<strong>Альбом перемещён</strong> %1$s <strong>ниже</strong> %2$s',
	'LOG_ALBUM_MOVE_UP'						=> '<strong>Альбом перемещён</strong> %1$s <strong>выше</strong> %2$s',
	'LOG_ALBUM_SYNC'						=> '<strong>Альбом синхронизирован</strong><br />» %s',

	'LOG_CLEAR_GALLERY'					=> 'Лог галереи очищен',

	'LOG_GALLERY_APPROVED'				=> '<strong>Фото одобрено</strong><br />» %s',
	'LOG_GALLERY_COMMENT_DELETED'		=> '<strong>Комментарий удалён</strong><br />» %s',
	'LOG_GALLERY_COMMENT_EDITED'		=> '<strong>Комментарий изменён</strong><br />» %s',
	'LOG_GALLERY_DELETED'				=> '<strong>Фото удалено</strong><br />» %s',
	'LOG_GALLERY_EDITED'				=> '<strong>Фото отредактировано</strong><br />» %s',
	'LOG_GALLERY_LOCKED'				=> '<strong>Фото закрыто</strong><br />» %s',
	'LOG_GALLERY_MOVED'					=> '<strong>Фото перемещено</strong><br />» from %1$s to %2$s',
	'LOG_GALLERY_REPORT_CLOSED'			=> '<strong>Жалоба закрыта</strong><br />» %s',
	'LOG_GALLERY_REPORT_DELETED'		=> '<strong>Жалоба удалена</strong><br />» %s',
	'LOG_GALLERY_REPORT_OPENED'			=> '<strong>Жалоба открыта</strong><br />» %s',
	'LOG_GALLERY_UNAPPROVED'			=> '<strong>Фото отклонено</strong><br />» %s',

	'LOGVIEW_VIEWALBUM'					=> 'Просмотр альбома',
	'LOGVIEW_VIEWIMAGE'					=> 'Просмотр фото',
]);
