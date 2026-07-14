<?php
/**
*
* gallery_ucp [Russian]
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
	'ALBUMS'                        => 'Альбомы',
	'ALBUM_DESC'                    => 'Описание альбома',
	'CREATE_PERSONAL_ALBUM'         => 'Создать личный альбом',
	'CREATE_PERSONAL_ALBUM_CONFIRM' => 'Вы хотите создать личный альбом?',

	'DELETE_ALBUM'                  => 'Удалить альбом',
	'DELETE_ALBUM_CONFIRM'          => 'Удалить личный альбом и все фотографии в нём?',
	'DELETED_ALBUMS'                => 'Альбом удалён',

	'EDIT_ALBUM'                    => 'Редактировать альбом',
	'EDIT_PERSONAL_ALBUM'           => 'Редактировать личный альбом',
	'EDIT_PERSONAL_ALBUM_EXP'       => 'Здесь можно изменить описание вашего личного альбома.',
	'EDITED_PERSONAL_ALBUM'         => 'Личный альбом отредактирован',

	'NO_ALBUM_STEALING'             => 'Вы не можете управлять альбомами других пользователей.',
	'NO_FAVORITES'                  => 'У вас нет избранного.',
	'NO_PERSALBUM_ALLOWED'          => 'У вас нет права на создание личного альбома',
	'NO_PERSONAL_ALBUM'             => 'У вас пока нет личного фотоальбома. Здесь можно его создать.',
	'NO_SUBSCRIPTIONS'              => 'Вы не подписаны ни на одно фото.',

	'PARSE_BBCODE'                  => 'Разрешить BBCode',
	'PARSE_SMILIES'                 => 'Разрешить смайлики',
	'PARSE_URLS'                    => 'Разрешить ссылки',
	'PERSONAL_ALBUM'                => 'Личный альбом',

	'REMOVE_FROM_FAVORITES'         => 'Удалить из избранного',

	'UNSUBSCRIBE'                   => 'Отписаться',
	'USER_ALLOW_COMMENTS'           => 'Пользователи могут комментировать ваши фото',

	'YOUR_FAVORITE_IMAGES'          => 'Список избранных вами фотографий.',
	'YOUR_SUBSCRIPTIONS'            => 'Фотографии и альбомы, на которые вы подписаны.',

	'VIEWEXIFS_DEFAULT'             => 'Показывать данные EXIF по умолчанию',

	'WATCH_CHANGED'                 => 'Изменения сохранены',
	'WATCH_COM'                     => 'Подписаться на комментированные вами фотографии',
	'WATCH_FAVO'                    => 'Подписаться на избранные вами фотографии',
	'WATCH_NOTE'                    => 'Параметры подписки по умолчанию. Они коснутся только новых фотографий и новых комментариев.',
	'WATCH_OWN'                     => 'Подписаться на комментарии к вашим фотографиям',
]);
