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
	'ACP_SEARCH_SETTINGS_EXPLAIN'           => 'Здесь можно настроить поиск и ограничения поисковых запросов.',

	'CREATE_INDEX'                          => 'Создать индекс',
	'SEARCH_INDEX_CREATE_CONFIRM'           => 'Создание полнотекстового поискового индекса может занять несколько минут, в зависимости от количества сообщений. Вы готовы?',

	'DELETE_INDEX'                          => 'Удалить индекс',
	'SEARCH_INDEX_DELETE_CONFIRM'           => 'Вы действительно хотите удалить полнотекстовый поисковый индекс? Поиск по ключевым словам перестанет работать.',

	'FULLTEXT_MYSQL_INDEX'                  => 'Полнотекстовый индекс MySQL',
	'FULLTEXT_MYSQL_INDEXED_POSTS'          => '%s сообщений',
	'FULLTEXT_MYSQL_NOT_SUPPORTED'          => 'Для использования полнотекстового индекса с таблицами InnoDB требуется MySQL 5.6.4 или более поздней версии.',
	'FULLTEXT_MYSQL_STATUS'                 => 'Статус',
	'FULLTEXT_MYSQL_STATUS_EXPLAIN'         => 'Если индекс не создан, поиск по ключевым словам будет недоступен.',
	'FULLTEXT_MYSQL_STATUS_INDEXED'         => 'Проиндексировано',
	'FULLTEXT_MYSQL_STATUS_MISSING'         => 'Отсутствует',
	'FULLTEXT_MYSQL_WORD_LENGTH'             => 'Длина индексируемых слов',
	'FULLTEXT_MYSQL_WORD_LENGTH_EXPLAIN'     => 'Эти значения определяются настройками сервера MySQL и не могут быть изменены в phpBBex.',
	'FULLTEXT_MYSQL_WORD_LENGTH_RANGE'       => 'от %1$d до %2$d символов',

	'GENERAL_SEARCH_SETTINGS'               => 'Общие настройки поиска',

	'MAX_NUM_SEARCH_KEYWORDS'               => 'Максимальное число искомых слов',
	'MAX_NUM_SEARCH_KEYWORDS_EXPLAIN'       => 'Максимальное количество слов, которые пользователь может искать одновременно. Установите 0 для снятия ограничений.',
	'MIN_SEARCH_AUTHOR_CHARS'               => 'Минимальное число символов в именах',
	'MIN_SEARCH_AUTHOR_CHARS_EXPLAIN'       => 'Пользователи должны будут ввести не меньше указанного количества символов при осуществлении поиска автора по маске. Если имя автора короче указанного значения, то можно осуществлять поиск по полному имени автора.',

	'SEARCH_GUEST_INTERVAL'                 => 'Интервал между запросами для гостей',
	'SEARCH_GUEST_INTERVAL_EXPLAIN'         => 'Время в секундах, которое гость должен выждать перед выполнением следующего поискового запроса. Если поиском пользуется один гость, то все остальные в это время ждут указанное здесь время.',
	'SEARCH_INDEX_CREATED'                  => 'Все сообщения в базе данных успешно проиндексированы.',
	'SEARCH_INDEX_REMOVED'                  => 'Поисковый индекс успешно удалён.',
	'SEARCH_INTERVAL'                       => 'Интервал между поисковыми запросами',
	'SEARCH_INTERVAL_EXPLAIN'               => 'Время в секундах, которое пользователь должен выждать перед выполнением следующего поискового запроса. Этот интервал проверяется для каждого пользователя.',
	'SEARCH_STORE_RESULTS'                  => 'Кэширование результатов поиска',
	'SEARCH_STORE_RESULTS_EXPLAIN'          => 'Длительность кэширования результатов поиска (в секундах). Введите 0 для отключения кэширования результатов.',

	'YES_SEARCH'                            => 'Включить поисковые возможности',
	'YES_SEARCH_EXPLAIN'                    => 'Включение поисковых возможностей, включая поиск пользователей.',

	'DEFAULT_SEARCH_TITLEONLY'              => 'По умолчанию искать только по названиям тем',
	'DEFAULT_SEARCH_TITLEONLY_EXPLAIN'      => 'Затрагивает глобальный поиск и поиск по конкретному разделу.',

	'SEARCH_HIGHLIGHT_KEYWORDS'             => 'Подсвечивать найденные слова',
	'SEARCH_HIGHLIGHT_KEYWORDS_EXPLAIN'     => 'Создаёт ссылки на viewtopic.php с параметром hilit для найденных тем.',
]);
