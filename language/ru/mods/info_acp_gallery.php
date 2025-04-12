<?php
/**
*
* info_acp_gallery [Russian]
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
	'ACP_GALLERY_ALBUM_MANAGEMENT'		=> 'Управление альбомом',
	'ACP_GALLERY_ALBUM_PERMISSIONS'		=> 'Права доступа',
	'ACP_GALLERY_ALBUM_PERMISSIONS_COPY'=> 'Копирование прав доступа',
	'ACP_GALLERY_CLEANUP'				=> 'Очистка галереи',
	'ACP_GALLERY_CONFIGURE_GALLERY'		=> 'Настройка галереи',
	'ACP_GALLERY_LOGS'					=> 'Лог галереи',
	'ACP_GALLERY_LOGS_EXPLAIN'			=> 'Список действий, выполненных в галерее, таких как одобрение, отклонение, блокировка и разблокировка, закрытие жалоб и удаление фотографий.',
	'ACP_GALLERY_MANAGE_ALBUMS'			=> 'Управление альбомами',
	'ACP_GALLERY_OVERVIEW'				=> 'Обзор',
	'ACP_IMPORT_ALBUMS'					=> 'Импорт фотографий',

	'GALLERY'							=> 'Галерея',
	'GALLERY_EXPLAIN'					=> 'Фотогалерея',
	'GALLERY_HELPLINE_ALBUM'			=> 'Фото из галереи: [album]ID фото[/album]',
	'GALLERY_POPUP'						=> 'Галерея',
	'GALLERY_POPUP_HELPLINE'			=> 'Выбрать фото из галереи или загрузить новое',

	'IMAGES'							=> 'Фото',
	'IMG_BUTTON_UPLOAD_IMAGE'			=> 'Загрузка фото',

	'PERSONAL_ALBUM'					=> 'Фотоальбом',
	'PHPBB_GALLERY'						=> 'Галерея',

	'TOTAL_IMAGES_SPRINTF'				=> array(
		0		=> 'Фотографий в галерее: <strong>0</strong>',
		1		=> 'Фотографий в галерее: <strong>%d</strong>',
	),
));
