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

$lang = array_merge($lang, [
	'ACP_LANGUAGE_PACKS_EXPLAIN'    => 'Здесь вы можете устанавливать и удалять языковые пакеты. Языковой пакет, используемый на форуме по умолчанию, помечен звёздочкой (*).',

	'INSTALLED_LANGUAGE_PACKS'  => 'Установленные языковые пакеты',
	'INVALID_LANGUAGE_PACK'     => 'Выбранный языковой пакет недопустим. Проверьте пакет и при необходимости повторно загрузите его на сервер.',

	'LANGUAGE_DETAILS_UPDATED'          => 'Сведения о языке успешно обновлены.',
	'LANGUAGE_PACK_ALREADY_INSTALLED'   => 'Этот языковой пакет уже установлен.',
	'LANGUAGE_PACK_DELETED'             => 'Языковой пакет <strong>%s</strong> успешно удалён. Все пользователи, использующие этот язык, переключены на язык форума по умолчанию.',
	'LANGUAGE_PACK_DETAILS'             => 'Информация о языковом пакете',
	'LANGUAGE_PACK_INSTALLED'           => 'Языковой пакет <strong>%s</strong> успешно установлен.',
	'LANGUAGE_PACK_CPF_UPDATE'          => 'Языковые строки дополнительных полей профиля были скопированы из языкового пакета по умолчанию. Измените их, если это необходимо.',
	'LANGUAGE_PACK_ISO'                 => 'ISO',
	'LANGUAGE_PACK_LOCALNAME'           => 'Местное название',
	'LANGUAGE_PACK_NAME'                => 'Название',
	'LANGUAGE_PACK_NOT_EXIST'           => 'Выбранный языковой пакет не существует.',
	'LANGUAGE_PACK_USED_BY'             => 'Используют (включая роботов)',
	'LANG_AUTHOR'                       => 'Автор языкового пакета',
	'LANG_ENGLISH_NAME'                 => 'Имя на английском',
	'LANG_ISO_CODE'                     => 'Код ISO',
	'LANG_LOCAL_NAME'                   => 'Местное название',

	'NO_LANG_ID'                    => 'Вы не указали языковой пакет.',
	'NO_REMOVE_DEFAULT_LANG'        => 'Вы не можете удалить языковой пакет, используемый по умолчанию.<br />Если вы хотите удалить этот пакет, сначала измените язык форума по умолчанию.',
	'NO_UNINSTALLED_LANGUAGE_PACKS' => 'Все доступные языковые пакеты установлены',

	'UNINSTALLED_LANGUAGE_PACKS'    => 'Языковые пакеты, доступные для установки',
]);
