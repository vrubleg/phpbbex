<?php

/**
*
* @package - Board3portal
* @version $Id: lang_portal_acp.php 666 2010-07-28 14:40:39Z marc1706 $
* @copyright (c) kevin / saint ( www.board3.de/ ), (c) Ice, (c) nickvergessen ( www.flying-bits.org/ ), (c) redbull254 ( www.digitalfotografie-foren.de ), (c) Christian_N ( www.phpbb-projekt.de )
* @based on: phpBB3 Portal by Sevdin Filiz, www.phpbb3portal.com
* @translator (c) Nekstati (www.phpbbguru.net)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
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
	// general
	'ACP_PORTAL_GENERAL_TITLE'				=> 'Администрирование портала',
	'ACP_PORTAL_GENERAL_TITLE_EXPLAIN'		=> '',
	'ACP_PORTAL_GENERAL_SETTINGS'			=> 'Общие настройки',
	'PORTAL_ENABLE'							=> 'Портал включён',
	'PORTAL_ENABLE_EXPLAIN'					=> '',
	'PORTAL_LEFT_COLUMN'					=> 'Левая колонка включена',
	'PORTAL_LEFT_COLUMN_EXPLAIN'			=> '',
	'PORTAL_RIGHT_COLUMN'					=> 'Правая колонка включена',
	'PORTAL_RIGHT_COLUMN_EXPLAIN'			=> '',
	'PORTAL_VERSION_CHECK'					=> 'Проверка версии портала',
	'PORTAL_ADVANCED_STAT'					=> '«Статистика»',
	'PORTAL_ADVANCED_STAT_EXPLAIN'			=> 'Отображать блок.',
	'PORTAL_LEADERS'						=> '«Команда сайта»',
	'PORTAL_LEADERS_EXPLAIN'				=> 'Отображать блок.',
	'PORTAL_LEADERS_EXT'					=> '«Команда сайта» — расширенный вид',
 	'PORTAL_LEADERS_EXT_EXPLAIN'			=> 'Для отображения этого блока должен быть активирован стандартный блок «Команда сайта».<br />Стандартный блок перечисляет администраторов и модераторов; расширенный блок добавляет отображение групп.',
	'PORTAL_CLOCK'							=> '«Часы»',
	'PORTAL_CLOCK_EXPLAIN'					=> 'Отображать блок.',
	'PORTAL_LINK_US'						=> '«Ссылка на наш сайт»',
	'PORTAL_LINK_US_EXPLAIN'				=> 'Отображать блок.',
	'PORTAL_SEARCH'							=> '«Поиск»',
	'PORTAL_SEARCH_EXPLAIN'					=> 'Отображать блок.',
	'PORTAL_WELCOME'						=> 'Отображать блок «Добро пожаловать»',
	'PORTAL_WELCOME_EXPLAIN'				=> '',
	'PORTAL_WHOIS_ONLINE'					=> '«Кто сейчас на конференции»',
	'PORTAL_WHOIS_ONLINE_EXPLAIN'			=> 'Отображать этот стандартный список в виде блока портала.',
	'PORTAL_CHANGE_STYLE'					=> '«Стиль»',
	'PORTAL_CHANGE_STYLE_EXPLAIN'			=> 'Отображать блок выбора стиля. Если активирован параметр «Заменять стиль пользователя» в настройках форума, этот блок не будет отображаться, независимо от его настроек.',
	'PORTAL_MAIN_MENU'						=> '«Меню сайта»',
	'PORTAL_MAIN_MENU_EXPLAIN'				=> 'Отображать блок основного меню.',
	'PORTAL_PHPBB_MENU'						=> 'Меню конференции',
	'PORTAL_PHPBB_MENU_EXPLAIN'             => 'Показывать стандартное верхнее меню конференции на странице портала.',
	'PORTAL_USER_MENU'						=> '«Личное меню»',
	'PORTAL_USER_MENU_EXPLAIN'				=> 'Отображать блок.',
	'PORTAL_FORUM_INDEX'					=> 'Список форумов',
   	'PORTAL_FORUM_INDEX_EXPLAIN'			=> 'Отображать стандартный список форумов в виде блока портала.',
	'BLOCK_CHANGE_WARN'						=> 'Учтите, что выключение всех блоков сделает страницу портала пустой.<br />Если вы выключили все блоки в боковой колонке, не забудьте отключить и саму колонку. Иначе на её месте появится отступ.',

	// random member
	'PORTAL_RANDOM_MEMBER'					=> '«Случайный участник»',
	'PORTAL_RANDOM_MEMBER_EXPLAIN'			=> 'Отображать блок.',

	// news and announcements
	'PORTAL_SHOW_REPLIES_VIEWS'					=> 'Показывать счётчики просмотров и ответов',
	'PORTAL_SHOW_REPLIES_VIEWS_EXPLAIN'			=> 'Это настройки для компактного режима блоков. Если «Да», счётчики ответов и просмотров будут отображаться в двух дополнительных столбцах. Если «Нет», счётчики отобразятся перед названием форума. Выберите «Нет», если есть проблема с отображением из-за нехватки ширины для двух дополнительных столбцов.',

	// birthdays
	'ACP_PORTAL_BIRTHDAYS_SETTINGS'				=> 'Настройки блока «Дни рождения»',
	'ACP_PORTAL_BIRTHDAYS_SETTINGS_EXPLAIN'		=> '',
	'PORTAL_BIRTHDAYS'							=> 'Отображать «Дни рождения»',
	'PORTAL_BIRTHDAYS_EXPLAIN'					=> '',
	'PORTAL_BIRTHDAYS_AHEAD'					=> 'Упреждение',
	'PORTAL_BIRTHDAYS_AHEAD_EXPLAIN'			=> 'За сколько дней до дня рождения начинать его отображать.<br />«0» отключает показ будущих дней рождения.',
	
	// global announcements
	'ACP_PORTAL_ANNOUNCE_SETTINGS'				=> 'Настройки блока «Объявления»',
	'ACP_PORTAL_ANNOUNCE_SETTINGS_EXPLAIN'		=> '',
	'PORTAL_ANNOUNCEMENTS'						=> 'Отображать «Объявления»',
	'PORTAL_ANNOUNCEMENTS_EXPLAIN'				=> '',
	'PORTAL_ANNOUNCEMENTS_STYLE'				=> 'Компактный режим блока объявлений',
	'PORTAL_ANNOUNCEMENTS_STYLE_EXPLAIN'		=> '«Да» — компактный режим, «Нет» — полный.',
	'PORTAL_NUMBER_OF_ANNOUNCEMENTS'			=> 'Количество объявлений в блоке',
	'PORTAL_NUMBER_OF_ANNOUNCEMENTS_EXPLAIN'	=> '0 — без ограничений.',
	'PORTAL_ANNOUNCEMENTS_DAY'					=> 'Количество дней для отображения объявления',
	'PORTAL_ANNOUNCEMENTS_DAY_EXPLAIN'			=> '0 — без ограничений.',
	'PORTAL_ANNOUNCEMENTS_LENGTH'				=> 'Максимальная длина объявления',
	'PORTAL_ANNOUNCEMENTS_LENGTH_EXPLAIN'		=> '0 — без ограничений.',
	'PORTAL_GLOBAL_ANNOUNCEMENTS_FORUM'			=> 'Форумы с объявлениями',
	'PORTAL_GLOBAL_ANNOUNCEMENTS_FORUM_EXPLAIN'	=> 'Форумы, размещённые в которых объявления будут отображаться порталом. Оставьте пустым для отображения объявлений из всех форумов. Если «Исключить форумы» = «Да», выберите исключаемые форумы.<br />Если «Исключить форумы» = «Нет», выберите форумы, объявления из которых должны отображаться.<br />Можно выбрать несколько форумов, удерживая <samp>CTRL</samp>.',
	'PORTAL_ANNOUNCEMENTS_FORUM_EXCLUDE'		=> 'Исключить форумы',
	'PORTAL_ANNOUNCEMENTS_FORUM_EXCLUDE_EXPLAIN'=> 'Выберите «Да», чтобы исключить выбранные форумы из блока объявлений, или «Нет», чтобы отображались объявления только из выбранных форумов.',
	'PORTAL_ANNOUNCEMENTS_PERMISSIONS'			=> 'Права доступа',
  	'PORTAL_ANNOUNCEMENTS_PERMISSIONS_EXPLAIN'	=> 'Учитывать права доступа пользователей к форумам при отображении объявлений.',
   	'PORTAL_ANNOUNCEMENTS_ARCHIVE'				=> 'Включить архив объявлений',
   	'PORTAL_ANNOUNCEMENTS_ARCHIVE_EXPLAIN'		=> 'Если «Да», объявления отобразятся в несколько страниц и появятся ссылки для их пролистывания, если «Нет» — отобразится только одна страница.',

	// news
	'ACP_PORTAL_NEWS_SETTINGS'			=> 'Настройки блока «Новости»',
	'ACP_PORTAL_NEWS_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_NEWS'						=> 'Отображать «Новости»',
	'PORTAL_NEWS_EXPLAIN'				=> '',
	'PORTAL_NEWS_STYLE'					=> 'Компактный режим блока новостей',
	'PORTAL_NEWS_STYLE_EXPLAIN'			=> '«Да» — компактный режим, «Нет» — полный.',
	'PORTAL_SHOW_ALL_NEWS'				=> 'Отображать все темы из указанных форумов',
	'PORTAL_SHOW_ALL_NEWS_EXPLAIN'		=> 'Включая прилепленные темы.',
	'PORTAL_NUMBER_OF_NEWS'				=> 'Количество отображаемых новостей',
	'PORTAL_NUMBER_OF_NEWS_EXPLAIN'		=> '0 — без ограничений.',
	'PORTAL_NEWS_LENGTH'				=> 'Максимальная длина содержимого новости',
	'PORTAL_NEWS_LENGTH_EXPLAIN'		=> '0 — без ограничений.',
	'PORTAL_NEWS_FORUM'					=> 'Форумы с новостями',
	'PORTAL_NEWS_FORUM_EXPLAIN'			=> 'Форумы, темы из которых будут отображаться порталом как новости. Оставьте пустым для отображения тем из всех форумов. Если «Исключить форумы» = «Да», выберите исключаемые форумы.<br />Если «Исключить форумы» = «Нет», выберите форумы, новости из которых должны отображаться.<br />Можно выбрать несколько форумов, удерживая <samp>CTRL</samp>.',
	'PORTAL_NEWS_EXCLUDE'				=> 'Исключить форумы',
	'PORTAL_NEWS_EXCLUDE_EXPLAIN'		=> 'Выберите «Да», чтобы исключить выбранные форумы из блока новостей, или «Нет», чтобы отображались новости только из выбранных форумов.',
	'PORTAL_NEWS_PERMISSIONS'			=> 'Права доступа',
   	'PORTAL_NEWS_PERMISSIONS_EXPLAIN'	=> 'Учитывать права доступа пользователей к форумам при отображении новостей.',
   	'PORTAL_NEWS_SHOW_LAST'				=> 'Сортировка по последним комментариям',
   	'PORTAL_NEWS_SHOW_LAST_EXPLAIN'		=> '«Да» — новости сортируются по последнему комментарию (сообщению). «Нет» — новости сортируются по последней теме.',
   	'PORTAL_NEWS_ARCHIVE'				=> 'Включить архив новостей',
   	'PORTAL_NEWS_ARCHIVE_EXPLAIN'		=> 'Если «Да», новости отобразятся в несколько страниц и появятся ссылки для их пролистывания, если «Нет» — отобразится только одна страница.',

	// recent topics
	'ACP_PORTAL_RECENT_SETTINGS'			=> 'Настройки блока «Свежие темы»',
	'ACP_PORTAL_RECENT_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_RECENT'							=> 'Отображать «Свежие темы»',
	'PORTAL_RECENT_EXPLAIN'					=> '',
	'PORTAL_MAX_TOPIC'						=> 'Количество свежих тем в блоке',
	'PORTAL_MAX_TOPIC_EXPLAIN'				=> '0 — без ограничений.',
	'PORTAL_RECENT_TITLE_LIMIT'				=> 'Ограничение количества символов',
	'PORTAL_RECENT_TITLE_LIMIT_EXPLAIN'		=> '0 — без ограничений.',
	'PORTAL_RECENT_FORUM'					=> 'Форумы со свежими темами',
	'PORTAL_RECENT_FORUM_EXPLAIN'			=> 'Форумы, из которых извлекаются свежие темы. Оставьте пустым, чтобы брать темы из всех форумов. Если «Исключить форумы» = «Да», выберите исключаемые форумы.<br />Если «Исключить форумы» = «Нет», выберите форумы, темы из которых должны отображаться.<br />Можно выбрать несколько форумов, удерживая <samp>CTRL</samp>.',
	'PORTAL_EXCLUDE_FORUM'					=> 'Исключить форумы',
	'PORTAL_EXCLUDE_FORUM_EXPLAIN'			=> 'Выберите «Да», чтобы исключить выбранные форумы из блока свежих тем, и «Нет», чтобы отображались свежие темы только из выбранных форумов.',

	// paypal
	'ACP_PORTAL_PAYPAL_SETTINGS'			=> 'Настройки блока «Пожертвование»',
	'ACP_PORTAL_PAYPAL_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_PAY_C_BLOCK'					=> 'Отображать центральный блок «Пожертвование»',
	'PORTAL_PAY_C_BLOCK_EXPLAIN'			=> '',
	'PORTAL_PAY_S_BLOCK'					=> 'Отображать малый блок «Пожертвование»',
	'PORTAL_PAY_S_BLOCK_EXPLAIN'			=> '',
	'PORTAL_PAY_ACC'						=> 'Учетная запись Paypal',
	'PORTAL_PAY_ACC_EXPLAIN'				=> 'Введите ваш e-mail, используемый в Paypal, например, хxx@xxx.com',

	// newest members
	'ACP_PORTAL_MEMBERS_SETTINGS'			=> 'Настройки блока «Новые участники»',
	'ACP_PORTAL_MEMBERS_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_LATEST_MEMBERS'					=> 'Отображать блок «Новые участники»',
	'PORTAL_LATEST_MEMBERS_EXPLAIN'			=> '',
	'PORTAL_MAX_LAST_MEMBER'				=> 'Количество новых участников',
	'PORTAL_MAX_LAST_MEMBER_EXPLAIN'		=> '0 — без ограничений.',

	// bots
	'ACP_PORTAL_BOTS_SETTINGS'					=> 'Настройки блока «Роботы»',
	'ACP_PORTAL_BOTS_SETTINGS_EXPLAIN'			=> '',
	'PORTAL_LOAD_LAST_VISITED_BOTS'				=> 'Отображать блок «Роботы»',
	'PORTAL_LOAD_LAST_VISITED_BOTS_EXPLAIN'		=> 'Блок содержит данные о последних поисковых роботах, посетивших сайт.',
	'PORTAL_LAST_VISITED_BOTS_NUMBER'			=> 'Количество отображаемых роботов',
	'PORTAL_LAST_VISITED_BOTS_NUMBER_EXPLAIN'	=> '0 — без ограничений.',

	// polls   
	'ACP_PORTAL_POLLS_SETTINGS'			=> 'Настройки блока «Опросы»',
	'ACP_PORTAL_POLLS_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_POLL_TOPIC'					=> 'Отображать блок «Опросы»',
	'PORTAL_POLL_TOPIC_EXPLAIN'			=> '',
	'PORTAL_POLL_TOPIC_ID'				=> 'Форумы с опросами',
	'PORTAL_POLL_TOPIC_ID_EXPLAIN'		=> 'Укажите форумы, опросы из которых должны быть отображены порталом. Оставьте поле пустым для отображения опросов из всех форумов. Если «Исключить форумы» = «Да», выберите исключаемые форумы.<br />Если «Исключить форумы» = «Нет», выберите форумы, опросы из которых должны отображаться.<br />Можно выбрать несколько форумов, удерживая <samp>CTRL</samp>.',
	'PORTAL_POLL_EXCLUDE_ID'			=> 'Исключить форумы',
	'PORTAL_POLL_EXCLUDE_ID_EXPLAIN'	=> 'Выберите «Да», чтобы исключить выбранные форумы из блока опросов, и «Нет», чтобы отображались опросы только из выбранных форумов.',
	'PORTAL_POLL_LIMIT'					=> 'Количество отображаемых опросов',
	'PORTAL_POLL_LIMIT_EXPLAIN'			=> '',
	'PORTAL_POLL_ALLOW_VOTE'			=> 'Разрешить голосование',
	'PORTAL_POLL_ALLOW_VOTE_EXPLAIN'	=> 'Разрешить пользователям с достаточным количеством прав голосовать с портальной страницы.',
	'PORTAL_POLL_HIDE'                  => 'Скрывать законченные опросы',

	// peak posters
	'ACP_PORTAL_MOST_POSTER_SETTINGS'			=> 'Настройки блока «Активные участники»',
	'ACP_PORTAL_MOST_POSTER_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_TOP_POSTERS'                  		=> 'Отображать блок «Активные участники»',
	'PORTAL_TOP_POSTERS_EXPLAIN'				=> 'Участники, оставившие наибольшее число сообщений на конференции.',
	'PORTAL_MAX_MOST_POSTER'					=> 'Количество отображаемых участников',
	'PORTAL_MAX_MOST_POSTER_EXPLAIN'			=> '0 — без ограничений.',

	// left and right column width 
	'ACP_PORTAL_COLUMN_WIDTH_SETTINGS'			=> 'Настройки ширины колонок',
	'PORTAL_LEFT_COLUMN_WIDTH'					=> 'Ширина левой колонки',
	'PORTAL_LEFT_COLUMN_WIDTH_EXPLAIN'			=> 'В пикселях; рекомендовано 180.',
	'PORTAL_RIGHT_COLUMN_WIDTH'				=> 'Ширина правой колонки',
	'PORTAL_RIGHT_COLUMN_WIDTH_EXPLAIN'		=> 'В пикселях; рекомендовано 180.',

	// attachments    
	'ACP_PORTAL_ATTACHMENTS_NUMBER_SETTINGS'			=> 'Настройки блока «Вложения»',
	'ACP_PORTAL_ATTACHMENTS_NUMBER_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_ATTACHMENTS'								=> 'Отображать блок «Вложения»',
	'PORTAL_ATTACHMENTS_EXPLAIN'						=> '',
	'PORTAL_ATTACHMENTS_NUMBER'							=> 'Количество отображаемых вложений',
	'PORTAL_ATTACHMENTS_NUMBER_EXPLAIN'					=> '0 — без ограничений.',
	'PORTAL_ATTACHMENTS_FORUM_IDS'						=> 'Форумы с вложениями',
   	'PORTAL_ATTACHMENTS_FORUM_IDS_EXPLAIN'				=> 'Форумы, вложения из которых должны быть отображены порталом. Оставьте поле пустым для отображения вложений из всех форумов. Если «Исключить форумы» = «Да», выберите исключаемые форумы.<br />Если «Исключить форумы» = «Нет», выберите форумы, вложения из которых должны отображаться.<br />Можно выбрать несколько форумов, удерживая <samp>CTRL</samp>.',
	'PORTAL_ATTACHMENTS_FORUM_EXCLUDE'					=> 'Исключить форумы',
	'PORTAL_ATTACHMENTS_FORUM_EXCLUDE_EXPLAIN'			=> 'Выберите «Да», чтобы исключить выбранные форумы из блока вложений, и «Нет», чтобы отображались вложения только из выбранных форумов.',
	'PORTAL_ATTACHMENTS_MAX_LENGTH'						=> 'Ограничение количества символов',
	'PORTAL_ATTACHMENTS_MAX_LENGTH_EXPLAIN'				=> '0 — без ограничений.',
	'PORTAL_ATTACHMENTS_FILETYPE' 						=> 'Типы файлов',
	'PORTAL_ATTACHMENTS_FILETYPE_EXPLAIN' 				=> 'Если «Исключить типы файлов» = «Да», выберите исключаемые типы файлов.<br />Если «Исключить типы файлов» = «Нет», выберите типы файлов, которые должны отображаться.<br />Можно выбрать несколько типов, удерживая <samp>CTRL</samp>.',
	'PORTAL_ATTACHMENTS_EXCLUDE'						=> 'Исключить типы файлов',
	'PORTAL_ATTACHMENTS_EXCLUDE_EXPLAIN'				=> 'Выберите «Да», чтобы исключить выбранные типы файлов из блока вложений, и «Нет», чтобы отображались вложения только выбранных типов.',
	
	// friends
	'ACP_PORTAL_FRIENDS_SETTINGS'			=> 'Настройки блока «Друзья»',
	'ACP_PORTAL_FRIENDS_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_FRIENDS'						=> 'Отображать блок «Друзья»',
	'PORTAL_FRIENDS_EXPLAIN'				=> '',
	'PORTAL_MAX_ONLINE_FRIENDS'				=> 'Количество друзей',
	'PORTAL_MAX_ONLINE_FRIENDS_EXPLAIN'		=> 'Ограничение количества отображаемых друзей.',

	// wordgraph
	'ACP_PORTAL_WORDGRAPH_SETTINGS'			=> 'Настройки блока «Ключевые слова»',
	'ACP_PORTAL_WORDGRAPH_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_WORDGRAPH'						=> 'Отображать блок «Ключевые слова»',
	'PORTAL_WORDGRAPH_EXPLAIN'				=> 'Список ключевых слов (облако тэгов). Размер шрифта зависит от частоты использования слова. Внимание: ключевые слова не будут отображаться должным образом, если выбран полнотекстовой mysql-поиск (Fulltext MySQL).',
	'PORTAL_WORDGRAPH_MAX_WORDS'			=> 'Сколько слов отображать',
	'PORTAL_WORDGRAPH_MAX_WORDS_EXPLAIN'	=> '0 — без ограничений.',
	'PORTAL_WORDGRAPH_WORD_COUNTS'			=> 'Отображать частоту употребления',
	'PORTAL_WORDGRAPH_WORD_COUNTS_EXPLAIN'	=> 'Количество упоминаний каждого слова будет отображено в скобках.',
	'PORTAL_WORDGRAPH_RATIO'				=> 'Диапазон размеров шрифта',
	'PORTAL_WORDGRAPH_RATIO_EXPLAIN'		=> 'Соотношение минимального и максимального размера шрифта (по умолчанию 18).',

	// welcome message
	'ACP_PORTAL_WELCOME_SETTINGS'			=> 'Настройки блока «Добро пожаловать»',
	'ACP_PORTAL_WELCOME_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_WELCOME_INTRO'					=> 'Текст приветственного сообщения',
	'PORTAL_WELCOME_GUEST'					=> 'Отображать только для гостей',
	'PORTAL_WELCOME_INTRO_EXPLAIN'			=> 'Вы можете использовать BBCode.',

	// links
   	'ACP_PORTAL_LINKS_SETTINGS' 		=> 'Настройки блока «Ссылки»',
   	'ACP_PORTAL_LINKS_SETTINGS_EXPLAIN' => '',
   	'PORTAL_LINKS'						=> 'Отображать блок «Ссылки»',
   	'PORTAL_LINKS_EXPLAIN'				=> '',
   	'PORTAL_LINK_TEXT'					=> 'Текст и URL',
   	'PORTAL_LINK_TEXT_EXPLAIN'			=> 'В верхнее поле введите текст ссылки, в нижнее — URL. Не забудьте о префиксе http://.',
   	'PORTAL_ADD_LINK_TEXT'				=> 'Добавить ссылку',
   	'PORTAL_ADD_LINK_TEXT_EXPLAIN'		=> '',
   	'PORTAL_LINK_ADD'					=> '<strong>Добавить</strong>',
   
   	// custom
   	'ACP_PORTAL_CUSTOM_SETTINGS'			=> 'Опции настраиваемого блока',
   	'ACP_PORTAL_CUSTOM_SETTINGS_EXPLAIN'	=> 'Здесь вы можете изменить настраиваемые блоки. Можно использовать HTML и BBCode.',
   	'ACP_PORTAL_CUSTOM_SMALL_SETTINGS'		=> 'Малый блок',
   	'PORTAL_CUSTOM_SMALL_HEADLINE'			=> 'Заголовок для малого блока',
   	'PORTAL_CUSTOM_SMALL_HEADLINE_EXPLAIN'	=> '',
   	'PORTAL_CUSTOM_SMALL'					=> 'Отображать малый блок',
   	'PORTAL_CUSTOM_SMALL_EXPLAIN'			=> '',
   	'PORTAL_CUSTOM_SMALL_BBCODE'			=> 'Использовать BBCode',
   	'PORTAL_CUSTOM_SMALL_BBCODE_EXPLAIN'	=> 'Если «Нет», то будут распознаны тэги HTML.',
   	'PORTAL_CUSTOM_CODE_SMALL'				=> 'Код малого блока',
   	'PORTAL_CUSTOM_CODE_SMALL_EXPLAIN'		=> '',
   	'ACP_PORTAL_CUSTOM_CENTER_SETTINGS'		=> 'Центральный блок',
   	'PORTAL_CUSTOM_CENTER'					=> 'Отображать центральный блок',
   	'PORTAL_CUSTOM_CENTER_EXPLAIN'			=> '',
   	'PORTAL_CUSTOM_CENTER_HEADLINE'			=> 'Заголовок для центрального блока',
   	'PORTAL_CUSTOM_CENTER_HEADLINE_EXPLAIN'	=> '',
   	'PORTAL_CUSTOM_CENTER_BBCODE'			=> 'Использовать BBCode',
   	'PORTAL_CUSTOM_CENTER_BBCODE_EXPLAIN'	=> 'Если «Нет», то будут распознаны тэги HTML.',
   	'PORTAL_CUSTOM_CODE_CENTER'				=> 'Код центрального блока',
   	'PORTAL_CUSTOM_CODE_CENTER_EXPLAIN'		=> '',
  
	// minicalendar
	'ACP_PORTAL_MINICALENDAR_SETTINGS'			=> 'Настройки блока «Календарь»',
	'ACP_PORTAL_MINICALENDAR_SETTINGS_EXPLAIN'	=> '',
	'PORTAL_MINICALENDAR'						=> 'Отображать «Календарь»',
	'PORTAL_MINICALENDAR_EXPLAIN'				=> '',
	'PORTAL_MINICALENDAR_TODAY_COLOR'			=> 'Цвет активного дня',
	'PORTAL_MINICALENDAR_TODAY_COLOR_EXPLAIN'	=> 'Код или допустимое в HTML название цвета, например #FFFFFF или white.',
	'PORTAL_MINICALENDAR_SUNDAY_COLOR'			=> 'Цвет воскресенья',
	'PORTAL_MINICALENDAR_SUNDAY_COLOR_EXPLAIN'	=> 'Код или допустимое в HTML название цвета, например #FFFFFF или white.',
	'PORTAL_LONG_MONTH'							=> 'Полные названия месяцев',
	'PORTAL_LONG_MONTH_EXPLAIN'					=> 'Если «Нет», названия будут сокращены до трёх букв.',
	'PORTAL_SUNDAY_FIRST'						=> 'Первый день — воскресенье',
	'PORTAL_SUNDAY_FIRST_EXPLAIN'				=> 'Если «Нет», неделя будет начинаться с понедельника.',
));

/**
* A copy of Handyman` s MOD version check, to view it on the portal general settings
*/
$lang = array_merge($lang, array(
	'ANNOUNCEMENT_TOPIC'	=> 'Объявление о новой версии',
	'CURRENT_VERSION'		=> 'Текущая версия',
	'DOWNLOAD_LATEST'		=> 'Скачать последнюю версию',
	'LATEST_VERSION'		=> 'Последняя версия',
	'NO_INFO'				=> 'Сервер в настоящее время недоступен',
	'NOT_UP_TO_DATE'		=> 'Вы используете устаревшую версию %s',
	'RELEASE_ANNOUNCEMENT'	=> 'Тема с объявлением',
	'UP_TO_DATE'			=> 'Вы используете последнюю версию %s',
	'VERSION_CHECK'			=> 'Проверка версии портала',
));

?>