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

$lang = array_merge($lang, array(
	'ACTIVE_TOPICS'			=> 'Активные темы',
	'ANNOUNCEMENTS'			=> 'Объявления',

	'FORUM_PERMISSIONS'		=> 'Права доступа',

	'ICON_ANNOUNCEMENT'		=> 'Объявление',
	'ICON_STICKY'			=> 'Закреплённая',

	'LOGIN_NOTIFY_FORUM'	=> 'Вы получили уведомление о новом сообщении в этом разделе, пожалуйста, авторизируйтесь для его просмотра.',
	'NO_READ_ACCESS'		=> 'У вас нет доступа на чтение тем в этом разделе.',

	'MARK_TOPICS_READ'		=> 'Отметить темы как прочтённые',

	'NEW_POSTS_LOCKED'		=> 'Новые сообщения [ Тема закрыта ]', // Больше не используется
	'NO_NEW_POSTS_LOCKED'	=> 'Нет новых сообщений [ Тема закрыта ]', // Больше не используется
	'NO_UNREAD_POSTS_LOCKED'=> 'Нет непрочитанных сообщений [ Тема закрыта ]',

	'POST_FORUM_LOCKED'		=> 'Раздел закрыт',

	'TOPICS_MARKED'			=> 'Все темы в этом разделе были отмечены как прочтённые.',

	'UNREAD_POSTS_LOCKED'	=> 'Непрочитанные сообщения [ Тема закрыта ]',

	'VIEW_FORUM'			=> 'Просмотр раздела',
	'VIEW_FORUM_TOPIC'		=> '1 тема',
	'VIEW_FORUM_TOPICS'		=> 'Тем: %d',
));
