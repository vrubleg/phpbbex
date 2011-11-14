<?php
/**
*
* info_acp_gallery [Russian] (Pthelovod v1.1.4)
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
	'ACP_GALLERY_ALBUM_MANAGEMENT'		=> 'Управление альбомом',
	'ACP_GALLERY_ALBUM_PERMISSIONS'		=> 'Права доступа',
	'ACP_GALLERY_ALBUM_PERMISSIONS_COPY'=> 'Копировать права доступа',
	'ACP_GALLERY_CLEANUP'				=> 'Очистка альбомов галереи',
	'ACP_GALLERY_CONFIGURE_GALLERY'		=> 'Конфигурация галереи',
	'ACP_GALLERY_LOGS'					=> 'Лог галереи',
	'ACP_GALLERY_LOGS_EXPLAIN'			=> 'Это список действий, выполненных в галерее, таких как, одобрение, неодобрение, блокировка и разблокировка, закрытие жалоб и удаление изображений.',
	'ACP_GALLERY_MANAGE_ALBUMS'			=> 'Управление общими альбомами',
	'ACP_GALLERY_OVERVIEW'				=> 'Статистика и обновление',
	'ACP_IMPORT_ALBUMS'					=> 'Импорт изображений',

	'GALLERY'							=> 'Галереи',
	'GALLERY_EXPLAIN'					=> 'Галерея изображений',
	'GALLERY_HELPLINE_ALBUM'			=> 'Изображение из галереи: [album]ID изображения[/album], с помощью этого BBCode Вы можете добавить изображение из галереи в свое сообщение на форуме.',
	'GALLERY_POPUP'						=> 'Быстрая вставка ссылки из галереи',
	'GALLERY_POPUP_HELPLINE'			=> 'Данная опция предназначена для мгновенной вставки в сообщение на форуме ссылки только на любое ваше изображение из галереи.',
	
	// A little line where you can give yourself some credits on the translation.
	//'GALLERY_TRANSLATION_INFO'			=> 'English "phpBB Gallery"-Translation by <a href="http://www.flying-bits.org/">nickvergessen</a>',
	'GALLERY_TRANSLATION_INFO'			=> 'Русский перевод "phpBB Gallery" - <a href="http://www.phpbbguru.net/">www.phpbbguru.net</a>',

	'IMAGES'							=> 'Изображения',
	'IMG_BUTTON_UPLOAD_IMAGE'			=> 'Загрузка изображения',

	'PERSONAL_ALBUM'					=> 'Персональный альбом',
	'PHPBB_GALLERY'						=> 'phpBB галерея',

	'TOTAL_IMAGES_SPRINTF'				=> array(
		0		=> 'Всего изображений <strong>0</strong>',
		1		=> 'Всего изображений <strong>%d</strong>',
	),
));

?>