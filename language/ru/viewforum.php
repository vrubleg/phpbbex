<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACTIVE_TOPICS'			=> 'Активные темы',
	'ANNOUNCEMENTS'			=> 'Объявления',

	'FORUM_PERMISSIONS'		=> 'Права доступа',

	'ICON_ANNOUNCEMENT'		=> 'Объявление',
	'ICON_STICKY'			=> 'Закреплённая',

	'LOGIN_NOTIFY_FORUM'	=> 'Вы получили уведомление о новом сообщении в этом разделе, пожалуйста, авторизируйтесь для его просмотра.',

	'MARK_TOPICS_READ'		=> 'Отметить темы как прочтённые',

	'NEW_POSTS_HOT'			=> 'Новые сообщения [ Популярная тема ]', // Больше не используется
	'NEW_POSTS_LOCKED'		=> 'Новые сообщения [ Тема закрыта ]', // Больше не используется
	'NO_NEW_POSTS_HOT'		=> 'Нет новых сообщений [ Популярная тема ]', // Больше не используется
	'NO_NEW_POSTS_LOCKED'	=> 'Нет новых сообщений [ Тема закрыта ]', // Больше не используется
	'NO_UNREAD_POSTS_HOT'	=> 'Нет непрочитанных сообщений [ Популярная тема ]',
	'NO_UNREAD_POSTS_LOCKED'=> 'Нет непрочитанных сообщений [ Тема закрыта ]',

	'POST_FORUM_LOCKED'		=> 'Раздел закрыт',

	'TOPICS_MARKED'			=> 'Все темы в этом разделе были отмечены как прочтённые.',

	'UNREAD_POSTS_HOT'		=> 'Непрочитанные сообщения [ Популярная тема ]',
	'UNREAD_POSTS_LOCKED'	=> 'Непрочитанные сообщения [ Тема закрыта ]',

	'NO_READ_ACCESS'		=> 'У вас нет доступа на чтение тем в этом разделе.',

	'VIEW_FORUM'			=> 'Просмотр раздела',
	'VIEW_FORUM_TOPIC'		=> '1 тема',
	'VIEW_FORUM_TOPICS'		=> 'Тем: %d',
));
