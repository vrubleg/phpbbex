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
	'ACP_FILES'						=> 'Администраторские файлы',
	'ACP_LANGUAGE_PACKS_EXPLAIN'	=> 'Здесь вы можете устанавливать и удалять языковые пакеты. Языковой пакет, используемый на форуме по умолчанию, помечен звёздочкой (*).',

	'EMAIL_FILES'			=> 'Шаблоны email-сообщений',

	'FILE_CONTENTS'				=> 'Содержимое файла',
	'FILE_FROM_STORAGE'			=> 'Файл из каталога хранения',

	'HELP_FILES'				=> 'Справочные файлы',

	'INSTALLED_LANGUAGE_PACKS'	=> 'Установленные языковые пакеты',
	'INVALID_LANGUAGE_PACK'		=> 'Выбранный языковой пакет недопустим. Проверьте пакет и при необходимости повторно загрузите его на сервер.',
	'INVALID_UPLOAD_METHOD'		=> 'Выбранный метод загрузки недопустим. Выберите другой метод.',

	'LANGUAGE_DETAILS_UPDATED'			=> 'Сведения о языке успешно обновлены.',
	'LANGUAGE_ENTRIES'					=> 'Языковые данные',
	'LANGUAGE_ENTRIES_EXPLAIN'			=> 'Здесь вы можете изменять существующие или пока не переведённые записи в файлах языкового пакета.<br /><strong>Примечание:</strong> если вы изменили языковой файл, изменения будут сохранены в отдельном каталоге для последующего скачивания. Изменения не будут видны вашим пользователям до тех пор, пока вы не замените исходные языковые файлы на сервере (загрузив новые).',
	'LANGUAGE_FILES'					=> 'Языковые файлы',
	'LANGUAGE_KEY'						=> 'Ключ языка',
	'LANGUAGE_PACK_ALREADY_INSTALLED'	=> 'Этот языковой пакет уже установлен.',
	'LANGUAGE_PACK_DELETED'				=> 'Языковой пакет <strong>%s</strong> успешно удалён. Все пользователи, использующие этот язык, переключены на язык форума по умолчанию.',
	'LANGUAGE_PACK_DETAILS'				=> 'Информация о языковом пакете',
	'LANGUAGE_PACK_INSTALLED'			=> 'Языковой пакет <strong>%s</strong> успешно установлен.',
	'LANGUAGE_PACK_CPF_UPDATE'			=> 'Языковые строки дополнительных полей профиля были скопированы из языкового пакета по умолчанию. Измените их, если это необходимо.',
	'LANGUAGE_PACK_ISO'					=> 'ISO',
	'LANGUAGE_PACK_LOCALNAME'			=> 'Местное название',
	'LANGUAGE_PACK_NAME'				=> 'Название',
	'LANGUAGE_PACK_NOT_EXIST'			=> 'Выбранный языковой пакет не существует.',
	'LANGUAGE_PACK_USED_BY'				=> 'Используют (включая роботов)',
	'LANGUAGE_VARIABLE'					=> 'Языковая переменная',
	'LANG_AUTHOR'						=> 'Автор языкового пакета',
	'LANG_ENGLISH_NAME'					=> 'Имя на английском',
	'LANG_ISO_CODE'						=> 'Код ISO',
	'LANG_LOCAL_NAME'					=> 'Местное название',

	'MISSING_LANGUAGE_FILE'		=> 'Отсутствует языковой файл: <strong style="color:red">%s</strong>',
	'MISSING_LANG_VARIABLES'	=> 'Отсутствующие языковые переменные',
	'MODS_FILES'				=> 'Языковые файлы модов',

	'NO_FILE_SELECTED'				=> 'Вы не указали языковой файл.',
	'NO_LANG_ID'					=> 'Вы не указали языковой пакет.',
	'NO_REMOVE_DEFAULT_LANG'		=> 'Вы не можете удалить языковой пакет, используемый по умолчанию.<br />Если вы хотите удалить этот пакет, сначала измените язык форума по умолчанию.',
	'NO_UNINSTALLED_LANGUAGE_PACKS'	=> 'Все доступные языковые пакеты установлены',

	'REMOVE_FROM_STORAGE_FOLDER'		=> 'Удалить из каталога хранения',

	'SELECT_DOWNLOAD_FORMAT'	=> 'Выбрать формат скачивания',
	'SUBMIT_AND_DOWNLOAD'		=> 'Отправить форму и скачать файл',
	'SUBMIT_AND_UPLOAD'			=> 'Отправить форму и загрузить файл на сервер',

	'THOSE_MISSING_LANG_FILES'			=> 'Следующие языковые файлы отсутствуют в языковом пакете %s',
	'THOSE_MISSING_LANG_VARIABLES'		=> 'Следующие языковые переменные отсутствуют в языковом пакете <strong>%s</strong>',

	'UNINSTALLED_LANGUAGE_PACKS'	=> 'Языковые пакеты, доступные для установки',

	'UNABLE_TO_WRITE_FILE'		=> 'Не удалось записать файл в %s.',
	'UPLOAD_COMPLETED'			=> 'Загрузка на сервер успешно завершена.',
	'UPLOAD_FAILED'				=> 'Загрузка на сервер не удалась. Может потребоваться заменить соответствующий файл вручную.',
	'UPLOAD_METHOD'				=> 'Метод загрузки на сервер',
	'UPLOAD_SETTINGS'			=> 'Настройки загрузки на сервер',

	'WRONG_LANGUAGE_FILE'		=> 'Выбранный языковой файл недопустим.',
));
