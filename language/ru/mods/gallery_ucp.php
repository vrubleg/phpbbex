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
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACCESS_CONTROL_ALL'			=> 'Все',
	'ACCESS_CONTROL_REGISTERED'		=> 'Зарегистрированные',
	'ACCESS_CONTROL_NOT_FOES'		=> 'Зарегистрированные, кроме ваших недругов',
	'ACCESS_CONTROL_FRIENDS'		=> 'Только ваши друзья',
	'ALBUMS'						=> 'Альбомы',
	'ALBUM_ACCESS'					=> 'Кто может видеть альбом',
	'ALBUM_ACCESS_EXPLAIN'			=> 'Можно использовать список %1$sдрузей и недругов%2$s для ограничения доступа к альбому. Учтите, что администраторы и модераторы смогут видеть альбом, даже если являются вашими недругами.',
	'ALBUM_DESC'					=> 'Описание альбома',
	'ALBUM_NAME'					=> 'Название альбома',
	'ALBUM_PARENT'					=> 'Родительский альбом',
	'ATTACHED_SUBALBUMS'			=> 'Вложенные альбомы',

	'CREATE_PERSONAL_ALBUM'			=> 'Создать личный альбом',
	'CREATE_SUBALBUM'				=> 'Создать вложенный альбом',
	'CREATE_SUBALBUM_EXP'			=> 'Можно добавить несколько альбомов, вложенных в ваш личный фотоальбом.',
	'CREATED_SUBALBUM'				=> 'Вложенный альбом создан',

	'DELETE_ALBUM'					=> 'Удалить альбом',
	'DELETE_ALBUM_CONFIRM'			=> 'Удалить альбом, все вложенные альбомы и фотографии?',
	'DELETED_ALBUMS'				=> 'Альбом удалён',

	'EDIT'							=> 'Редактировать',
	'EDIT_ALBUM'					=> 'Редактировать альбом',
	'EDIT_SUBALBUM'					=> 'Редактировать вложенный альбом',
	'EDIT_SUBALBUM_EXP'				=> 'Здесь можно редактировать альбомы.',
	'EDITED_SUBALBUM'				=> 'Альбом отредактирован',

	'GOTO'							=> 'Перейти в альбом',

	'MANAGE_SUBALBUMS'				=> 'Управление вложенными альбомами',
	'MISSING_ALBUM_NAME'			=> 'Введите название альбома',

	'NEED_INITIALISE'				=> 'У вас пока нет личного фотоальбома.',
	'NO_ALBUM_STEALING'				=> 'Вы не можете управлять альбомами других пользователей.',
	'NO_FAVORITES'					=> 'У вас нет избранного.',
	'NO_MORE_SUBALBUMS_ALLOWED'		=> 'Вы достигли максимального разрешённого количества вложенных альбомов.',
	'NO_PARENT_ALBUM'				=> '— нет родителя',
	'NO_PERSALBUM_ALLOWED'			=> 'У вас нет права на создание личного альбома',
	'NO_PERSONAL_ALBUM'				=> 'У вас пока нет личного фотоальбома. Здесь можно создать его, а также вложенные альбомы.',
	'NO_SUBALBUMS'					=> 'Нет вложенных альбомов',
	'NO_SUBSCRIPTIONS'				=> 'Вы не подписаны ни на одно фото.',

	'PARSE_BBCODE'					=> 'Разрешить BBCode',
	'PARSE_SMILIES'					=> 'Разрешить смайлики',
	'PARSE_URLS'					=> 'Разрешить ссылки',
	'PERSONAL_ALBUM'				=> 'Личный альбом',

	'REMOVE_FROM_FAVORITES'			=> 'Удалить из избранного',

	'UNSUBSCRIBE'					=> 'Отписаться',
	'USER_ALLOW_COMMENTS'			=> 'Пользователи могут комментировать ваши фото',

	'YOUR_FAVORITE_IMAGES'			=> 'Список избранных вами фотографий.',
	'YOUR_SUBSCRIPTIONS'			=> 'Фотографии и альбомы, на которые вы подписаны.',

	'VIEWEXIFS_DEFAULT'				=> 'Показывать данные EXIF по умолчанию',

	'WATCH_CHANGED'					=> 'Изменения сохранены',
	'WATCH_COM'						=> 'Подписаться на комментированные вами фотографии',
	'WATCH_FAVO'					=> 'Подписаться на избранные вами фотографии',
	'WATCH_NOTE'					=> 'Параметры подписки по умолчанию. Они коснутся только новых фотографий и новых комментариев.',
	'WATCH_OWN'						=> 'Подписаться на комментарии к вашим фотографиям',
));
