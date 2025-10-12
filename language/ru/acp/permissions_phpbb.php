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
	$lang = [];
}

/**
*	MODDERS PLEASE NOTE
*
*	You are able to put your permission sets into a separate file too by
*	prefixing the new file with permissions_ and putting it into the acp
*	language folder.
*
*	An example of how the file could look like:
*
*	<code>
*
*	if (empty($lang) || !is_array($lang))
*	{
*		$lang = array();
*	}
*
*	// Adding new category
*	$lang['permission_cat']['bugs'] = 'Bugs';
*
*	// Adding new permission set
*	$lang['permission_type']['bug_'] = 'Bug Permissions';
*
*	// Adding the permissions
*	$lang = array_merge($lang, array(
*		'acl_bug_view'		=> array('lang' => 'Can view bug reports', 'cat' => 'bugs'),
*		'acl_bug_post'		=> array('lang' => 'Can post bugs', 'cat' => 'post'), // Using a phpBB category here
*	));
*
*	</code>
*/

// Define categories and permission types
$lang = array_merge($lang, [
	'permission_cat'	=> [
		'actions'		=> 'Действия',
		'content'		=> 'Содержимое',
		'forums'		=> 'Разделы',
		'misc'			=> 'Разное',
		'permissions'	=> 'Права доступа',
		'pm'			=> 'Личные сообщения',
		'polls'			=> 'Опросы',
		'post'			=> 'Размещение сообщений',
		'post_actions'	=> 'Действия с сообщениями',
		'posting'		=> 'Сообщения',
		'profile'		=> 'Профиль',
		'settings'		=> 'Установки',
		'topic_actions'	=> 'Действия с темами',
		'user_group'	=> 'Пользователи',
	],

	// With defining 'global' here we are able to specify what is printed out if the permission is within the global scope.
	'permission_type'	=> [
		'u_'			=> 'Права доступа пользователя',
		'a_'			=> 'Права доступа администратора',
		'm_'			=> 'Права доступа модератора',
		'f_'			=> 'Права доступа для раздела',
		'global'		=> [
			'm_'			=> 'Глобальные права модератора',
		],
	],
]);

// User Permissions
$lang = array_merge($lang, [
	'acl_u_viewprofile'	=> ['lang' => 'Может просматривать профили, список пользователей и страницу «Кто сейчас на форуме»', 'cat' => 'profile'],
	'acl_u_chgname'		=> ['lang' => 'Может менять имя', 'cat' => 'profile'],
	'acl_u_chgpasswd'	=> ['lang' => 'Может менять пароль', 'cat' => 'profile'],
	'acl_u_chgemail'	=> ['lang' => 'Может менять email-адрес', 'cat' => 'profile'],
	'acl_u_chgavatar'	=> ['lang' => 'Может менять аватару', 'cat' => 'profile'],
	'acl_u_chggrp'		=> ['lang' => 'Может менять группу по умолчанию', 'cat' => 'profile'],

	'acl_u_attach'		=> ['lang' => 'Может прикреплять вложения', 'cat' => 'post'],
	'acl_u_download'	=> ['lang' => 'Может скачивать файлы', 'cat' => 'post'],
	'acl_u_chgcensors'	=> ['lang' => 'Может отключать автоцензора', 'cat' => 'post'],
	'acl_u_sig'			=> ['lang' => 'Может использовать подпись', 'cat' => 'post'],
	'acl_u_ignorefpedittime'	=> ['lang' => 'Может игнорировать ограничение времени на редактирование первого сообщения', 'cat' => 'post'],
	'acl_u_ignoreedittime'		=> ['lang' => 'Может игнорировать ограничение времени на редактирование всех сообщений', 'cat' => 'post'],

	'acl_u_sendpm'		=> ['lang' => 'Может посылать ЛС', 'cat' => 'pm'],
	'acl_u_masspm'		=> ['lang' => 'Может рассылать ЛС нескольким пользователям', 'cat' => 'pm'],
	'acl_u_masspm_group'=> ['lang' => 'Может рассылать ЛС группам пользователей', 'cat' => 'pm'],
	'acl_u_readpm'		=> ['lang' => 'Может читать ЛС', 'cat' => 'pm'],
	'acl_u_pm_edit'		=> ['lang' => 'Может редактировать собственные ЛС', 'cat' => 'pm'],
	'acl_u_pm_attach'	=> ['lang' => 'Может прикреплять вложения в ЛС', 'cat' => 'pm'],
	'acl_u_pm_download'	=> ['lang' => 'Может скачивать файлы из ЛС', 'cat' => 'pm'],
	'acl_u_pm_bbcode'	=> ['lang' => 'Может использовать BBCode в ЛС', 'cat' => 'pm'],
	'acl_u_pm_smilies'	=> ['lang' => 'Может использовать смайлики в ЛС', 'cat' => 'pm'],
	'acl_u_pm_img'		=> ['lang' => 'Может использовать тег [img] в ЛС', 'cat' => 'pm'],
	'acl_u_pm_flash'	=> ['lang' => 'Может использовать тег [flash] в ЛС', 'cat' => 'pm'],

	'acl_u_canplus'		=> ['lang' => 'Может ставить положительные оценки', 'cat' => 'misc'],
	'acl_u_canminus'	=> ['lang' => 'Может ставить отрицательные оценки', 'cat' => 'misc'],
	'acl_u_sendemail'	=> ['lang' => 'Может посылать email-сообщения', 'cat' => 'misc'],
	'acl_u_sendim'		=> ['lang' => 'Может использовать систему мгновенных сообщений', 'cat' => 'misc'],
	'acl_u_ignoreflood'	=> ['lang' => 'Может игнорировать флуд-контроль', 'cat' => 'misc'],
	'acl_u_hideonline'	=> ['lang' => 'Может прятать статус присутствия', 'cat' => 'misc'],
	'acl_u_viewonline'	=> ['lang' => 'Может видеть статус присутствия', 'cat' => 'misc'],
	'acl_u_search'		=> ['lang' => 'Может использовать поиск', 'cat' => 'misc'],
]);

// Forum Permissions
$lang = array_merge($lang, [
	'acl_f_list'		=> ['lang' => 'Может видеть раздел', 'cat' => 'post'],
	'acl_f_read'		=> ['lang' => 'Может читать раздел', 'cat' => 'post'],
	'acl_f_post'		=> ['lang' => 'Может создавать темы', 'cat' => 'post'],
	'acl_f_reply'		=> ['lang' => 'Может отвечать в темах', 'cat' => 'post'],
	'acl_f_announce'	=> ['lang' => 'Может создавать объявления', 'cat' => 'post'],
	'acl_f_sticky'		=> ['lang' => 'Может закреплять темы', 'cat' => 'post'],

	'acl_f_poll'		=> ['lang' => 'Может создавать опросы', 'cat' => 'polls'],
	'acl_f_vote'		=> ['lang' => 'Может голосовать в опросах', 'cat' => 'polls'],
	'acl_f_votechg'		=> ['lang' => 'Может переголосовать', 'cat' => 'polls'],

	'acl_f_attach'		=> ['lang' => 'Может прикреплять вложения', 'cat' => 'content'],
	'acl_f_download'	=> ['lang' => 'Может скачивать файлы', 'cat' => 'content'],
	'acl_f_sigs'		=> ['lang' => 'Может использовать подпись', 'cat' => 'content'],
	'acl_f_bbcode'		=> ['lang' => 'Может использовать BBCode', 'cat' => 'content'],
	'acl_f_smilies'		=> ['lang' => 'Может использовать смайлики', 'cat' => 'content'],
	'acl_f_img'			=> ['lang' => 'Может использовать тег [img]', 'cat' => 'content'],
	'acl_f_flash'		=> ['lang' => 'Может использовать тег [flash]', 'cat' => 'content'],

	'acl_f_edit'		=> ['lang' => 'Может редактировать собственные сообщения', 'cat' => 'actions'],
	'acl_f_delete'		=> ['lang' => 'Может удалять собственные сообщения', 'cat' => 'actions'],
	'acl_f_user_lock'	=> ['lang' => 'Может закрывать свои темы', 'cat' => 'actions'],
	'acl_f_bump'		=> ['lang' => 'Может поднимать темы', 'cat' => 'actions'],
	'acl_f_report'		=> ['lang' => 'Может размещать жалобы', 'cat' => 'actions'],

	'acl_f_search'		=> ['lang' => 'Может использовать поиск в разделе', 'cat' => 'misc'],
	'acl_f_ignoreflood' => ['lang' => 'Может игнорировать флуд-контроль', 'cat' => 'misc'],
	'acl_f_postcount'	=> ['lang' => 'Счётчик сообщений включён<br /><em>Учтите, что данная установка эффективна только при создании новых сообщений.</em>', 'cat' => 'misc'],
	'acl_f_noapprove'	=> ['lang' => 'Может размещать сообщения без одобрения', 'cat' => 'misc'],
]);

// Moderator Permissions
$lang = array_merge($lang, [
	'acl_m_edit'		=> ['lang' => 'Может редактировать сообщения', 'cat' => 'post_actions'],
	'acl_m_delete'		=> ['lang' => 'Может удалять сообщения', 'cat' => 'post_actions'],
	'acl_m_approve'		=> ['lang' => 'Может одобрять сообщения', 'cat' => 'post_actions'],
	'acl_m_report'		=> ['lang' => 'Может закрывать и удалять жалобы', 'cat' => 'post_actions'],
	'acl_m_chgposter'	=> ['lang' => 'Может менять автора сообщений', 'cat' => 'post_actions'],

	'acl_m_move'	=> ['lang' => 'Может перемещать темы', 'cat' => 'topic_actions'],
	'acl_m_lock'	=> ['lang' => 'Может закрывать темы', 'cat' => 'topic_actions'],
	'acl_m_split'	=> ['lang' => 'Может разделять темы', 'cat' => 'topic_actions'],
	'acl_m_merge'	=> ['lang' => 'Может объединять темы', 'cat' => 'topic_actions'],

	'acl_m_info'	=> ['lang' => 'Может просматривать подробности о сообщениях', 'cat' => 'misc'],
	'acl_m_warn'	=> ['lang' => 'Может объявлять предупреждения<br /><em>Это право может быть назначено только глобально, а не на уровне разделов.</em>', 'cat' => 'misc'], // This moderator setting is only global (and not local)
	'acl_m_ban'		=> ['lang' => 'Может управлять блокировкой<br /><em>Это право может быть назначено только глобально, а не на уровне разделов.</em>', 'cat' => 'misc'], // This moderator setting is only global (and not local)
]);

// Admin Permissions
$lang = array_merge($lang, [
	'acl_a_board'		=> ['lang' => 'Может изменять настройки форума и проверять обновления', 'cat' => 'settings'],
	'acl_a_server'		=> ['lang' => 'Может изменять параметры настройки сервера', 'cat' => 'settings'],
	'acl_a_phpinfo'		=> ['lang' => 'Может просматривать сведения о php', 'cat' => 'settings'],

	'acl_a_forum'		=> ['lang' => 'Может управлять разделами', 'cat' => 'forums'],
	'acl_a_forumadd'	=> ['lang' => 'Может создавать разделы', 'cat' => 'forums'],
	'acl_a_forumdel'	=> ['lang' => 'Может удалять разделы', 'cat' => 'forums'],
	'acl_a_prune'		=> ['lang' => 'Может очищать разделы', 'cat' => 'forums'],

	'acl_a_icons'		=> ['lang' => 'Может изменять иконки тем и смайлики', 'cat' => 'posting'],
	'acl_a_words'		=> ['lang' => 'Может настраивать автоцензор', 'cat' => 'posting'],
	'acl_a_bbcode'		=> ['lang' => 'Может определять BBCode', 'cat' => 'posting'],
	'acl_a_attach'		=> ['lang' => 'Может изменять настройки вложений', 'cat' => 'posting'],

	'acl_a_user'		=> ['lang' => 'Может управлять пользователями<br /><em>Право также включает просмотр типа браузера пользователей в списке находящихся на форуме.</em>', 'cat' => 'user_group'],
	'acl_a_userdel'		=> ['lang' => 'Может удалять пользователей', 'cat' => 'user_group'],
	'acl_a_group'		=> ['lang' => 'Может управлять группами', 'cat' => 'user_group'],
	'acl_a_groupadd'	=> ['lang' => 'Может создавать группы', 'cat' => 'user_group'],
	'acl_a_groupdel'	=> ['lang' => 'Может удалять группы', 'cat' => 'user_group'],
	'acl_a_ranks'		=> ['lang' => 'Может управлять званиями', 'cat' => 'user_group'],
	'acl_a_profile'		=> ['lang' => 'Может управлять дополнительными полями профиля', 'cat' => 'user_group'],
	'acl_a_names'		=> ['lang' => 'Может управлять запрещёнными именами', 'cat' => 'user_group'],
	'acl_a_ban'			=> ['lang' => 'Может управлять блокировкой', 'cat' => 'user_group'],

	'acl_a_viewauth'	=> ['lang' => 'Может просматривать права доступа', 'cat' => 'permissions'],
	'acl_a_authgroups'	=> ['lang' => 'Может изменять права доступа для конкретной группы', 'cat' => 'permissions'],
	'acl_a_authusers'	=> ['lang' => 'Может изменять права доступа для конкретного пользователя', 'cat' => 'permissions'],
	'acl_a_fauth'		=> ['lang' => 'Может изменять права доступа для разделов', 'cat' => 'permissions'],
	'acl_a_mauth'		=> ['lang' => 'Может изменять права доступа для модераторов', 'cat' => 'permissions'],
	'acl_a_aauth'		=> ['lang' => 'Может изменять права доступа для администраторов', 'cat' => 'permissions'],
	'acl_a_uauth'		=> ['lang' => 'Может изменять права доступа для пользователей', 'cat' => 'permissions'],
	'acl_a_roles'		=> ['lang' => 'Может управлять ролями', 'cat' => 'permissions'],
	'acl_a_switchperm'	=> ['lang' => 'Может изменять другие права доступа', 'cat' => 'permissions'],

	'acl_a_styles'		=> ['lang' => 'Может управлять стилями', 'cat' => 'misc'],
	'acl_a_viewlogs'	=> ['lang' => 'Может просматривать логи', 'cat' => 'misc'],
	'acl_a_clearlogs'	=> ['lang' => 'Может очищать логи', 'cat' => 'misc'],
	'acl_a_modules'		=> ['lang' => 'Может управлять модулями', 'cat' => 'misc'],
	'acl_a_language'	=> ['lang' => 'Может управлять языковыми пакетами', 'cat' => 'misc'],
	'acl_a_email'		=> ['lang' => 'Может осуществлять массовую рассылку почты', 'cat' => 'misc'],
	'acl_a_bots'		=> ['lang' => 'Может управлять ботами', 'cat' => 'misc'],
	'acl_a_reasons'		=> ['lang' => 'Может управлять списком жалоб/причин', 'cat' => 'misc'],
	'acl_a_backup'		=> ['lang' => 'Может сохранять/восстанавливать базу данных', 'cat' => 'misc'],
	'acl_a_search'		=> ['lang' => 'Может управлять поисковыми индексами/установками поиска', 'cat' => 'misc'],
]);
