<?php

/**
*
* @package - Board3portal
* @version $Id: lang_portal.php 508 2009-06-09 14:02:54Z Christian_N $
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
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine


$lang = array_merge($lang, array(
	// General
	'PORTAL'				=> 'Портал',
	'WELCOME'				=> 'Добро пожаловать',

	'PORTAL_INSTALL'		=> 'Обнаружен установочный каталог',
	'PORTAL_INSTALL_TEXT'	=> 'Обнаружены установочные файлы. Если вы хотите обновить портал (или другой мод), запустите установщик. Если вы уже это сделали, удалите или переименуйте установочный каталог в целях безопасности.',

	// news & global announcements
	'LATEST_ANNOUNCEMENTS'	=> 'Объявления',
	'GLOBAL_ANNOUNCEMENT'	=> 'Важное объявление',
	'VIEW_LATEST_ANNOUNCEMENT'	=> '1 объявление',
	'VIEW_LATEST_ANNOUNCEMENTS'	=> 'Объявлений: %d',	
	'LATEST_NEWS'			=> 'Новости',
	'READ_FULL'				=> 'Развернуть',
	'NO_NEWS'				=> 'Нет новостей',
	'NO_ANNOUNCEMENTS'		=> 'Нет объявлений',
	'POSTED_BY'				=> 'Автор',
	'COMMENTS'				=> 'Комментарии',
	'VIEW_COMMENTS'			=> 'Просмотр комментариев',
	'POST_REPLY'			=> 'Комментировать',
	'TOPIC_VIEWS'			=> 'Просмотры',
	'JUMP_NEWEST'			=> 'Перейти к последнему сообщению',
	'JUMP_FIRST'			=> 'Перейти к первому сообщению',
	'JUMP_TO_POST'			=> 'Перейти к сообщению',
	'BACK'					=> 'Свернуть',

	// Birthday
	'BIRTHDAYS_AHEAD'       => 'В следующие %s дней',
	'NO_BIRTHDAYS_AHEAD'    => 'В ближайшее время дни рождения не ожидаются.',

	// user menu
	'USER_MENU'			=> 'Личное меню',
	'UM_LOG_ME_IN'		=> 'Запомнить меня',
	'UM_HIDE_ME'		=> 'Скрыть меня',
	'UM_MAIN_SUBSCRIBED'=> 'Подписки',
	'UM_BOOKMARKS'		=> 'Закладки',

	// statistics
	'ST_TOP'		=> 'Всего',
	'ST_TOP_ANNS'	=> 'Объявлений:',
	'ST_TOP_STICKYS'=> 'Прилепленных тем:',
	'ST_TOT_ATTACH'	=> 'Вложений:',

	// search
	'SH'		=> 'Искать',
	'SH_SITE'	=> 'Форум',
	'SH_POSTS'	=> 'Сообщения',
	'SH_AUTHOR'	=> 'Автор',
	'SH_ENGINE'	=> 'Поисковые системы',
	'SH_ADV'	=> 'Расширенный поиск',
	
	// recent
	'RECENT_NEWS'		=> 'Свежие темы',
	'RECENT_TOPIC'		=> 'Обсуждения',
	'RECENT_ANN'		=> 'Объявления',
	'RECENT_HOT_TOPIC'	=> 'Популярные',

	// random member
	'RND_MEMBER'	=> 'Случайный участник',
	'RND_JOIN'		=> 'Зарегистрирован',
	'RND_POSTS'		=> 'Сообщений',
	'RND_OCC'		=> 'Занятия',
	'RND_FROM'		=> 'Откуда',
	'RND_WWW'		=> 'Web-страница',

	// top poster
	'TOP_POSTER'	=> 'Активные участники',
	
	// attachments
	'DOWNLOADS'			=> 'Просмотров',
	'NO_ATTACHMENTS'	=> 'Нет вложений',

	// links
	'LINKS'				=> 'Ссылки',
	'NO_LINKS'			=> 'Нет ссылок',

	// latest members
	'LATEST_MEMBERS'	=> 'Новые участники',
	
	// make donation
	'DONATION' 		=> 'Пожертвование',
	'DONATION_TEXT'	=> '— некоммерческий сайт, он существует на энтузиазме нашей команды. Хотите нас поддержать? Вы можете нам очень помочь, пожертвовав немного на содержание сайта.',
	'PAY_MSG'		=> '',
	'PAY_ITEM'		=> 'Внести', // paypal item
	
	'AUD'						=> 'Австралийские доллары (AUD)',
	'CAD'						=> 'Канадские доллары (CAD)',
   	'CZK'						=> 'Чешские кроны (CZK)',
   	'DKK'						=> 'Датские кроны (DKK)',
   	'HKD'						=> 'Гонконгские доллары (HKD)',
   	'HUF'						=> 'Венгерские форинты (HUF)',
   	'NZD'						=> 'Новозеландские доллары (NZD)',
   	'NOK'						=> 'Норвежские кроны (NOK)',
   	'PLN'						=> 'Польские злотые (PLN)',
   	'GBP'						=> 'Британские фунты (GBP)',
   	'SGD'						=> 'Сингапуские доллары (SGD)',
   	'SEK'						=> 'Шведские кроны (SEK)',
   	'CHF'						=> 'Швейцарские франки (CHF)',
   	'JPY'						=> 'Японские йены (JPY)',
   	'USD'						=> 'Доллары США (USD)',
   	'EUR'						=> 'Евро (EUR)',
   	'MXN'						=> 'Мексиканские песо (MXN)',
   	'ILS'						=> 'Новые израильские шекели (ILS)',

	// main menu
	'M_MENU' 	=> 'Меню',
	'M_CONTENT'	=> 'Разделы',
	'M_ACP'		=> 'Администраторский раздел',
	'M_HELP'	=> 'Помощь',
	'M_BBCODE'	=> 'Руководство по BBCode',
	'M_TERMS'	=> 'Общие правила',
	'M_PRV'		=> 'Соглашение о конфиденциальности',
	'M_SEARCH'	=> 'Поиск',

	// link us
	'LINK_US'		=> 'Ссылка на наш сайт',
	'LINK_US_TXT'	=> 'Если хотите разместить ссылку на <strong>%s</strong>, используйте этот HTML-код:',

	// friends
	'FRIENDS'				=> 'Друзья',
	'FRIENDS_OFFLINE'		=> 'Отсутствуют',
	'FRIENDS_ONLINE'		=> 'На форуме',
	'NO_FRIENDS'			=> 'Нет указанных друзей',
	'NO_FRIENDS_OFFLINE'	=> 'Нет отсутствующих друзей',
	'NO_FRIENDS_ONLINE'		=> 'Друзья не на форуме',
	
	// last bots
	'LAST_VISITED_BOTS'		=> 'Роботы',
	
	// wordgraph
	'WORDGRAPH'				=> 'Ключевые слова',

	// change style
	'BOARD_STYLE'			=> 'Стиль',
	'STYLE_CHOOSE'			=> 'Выберите стиль оформления',
		
	// team
	'NO_ADMINISTRATORS_P'	=> 'Нет администраторов',
	'NO_MODERATORS_P'		=> 'Нет модераторов',
	'NO_GROUPS_P'           => 'Нет групп',

	// average Statistics
	'TOPICS_PER_DAY_OTHER'	=> 'Тем за день: <strong>%d</strong>',
	'TOPICS_PER_DAY_ZERO'	=> 'Тем за день: <strong>0</strong>',
	'POSTS_PER_DAY_OTHER'	=> 'Сообщений в день: <strong>%d</strong>',
	'POSTS_PER_DAY_ZERO'	=> 'Сообщений в день: <strong>0</strong>',
	'USERS_PER_DAY_OTHER'	=> 'Посетителей за день: <strong>%d</strong>',
	'USERS_PER_DAY_ZERO'	=> 'Посетителей за день: <strong>0</strong>',
	'TOPICS_PER_USER_OTHER'	=> 'Тем на участника: <strong>%d</strong>',
	'TOPICS_PER_USER_ZERO'	=> 'Тем на участника: <strong>0</strong>',
	'POSTS_PER_USER_OTHER'	=> 'Сообщений на участника: <strong>%d</strong>',
	'POSTS_PER_USER_ZERO'	=> 'Сообщений на участника: <strong>0</strong>',
	'POSTS_PER_TOPIC_OTHER'	=> 'Сообщений на тему: <strong>%d</strong>',
	'POSTS_PER_TOPIC_ZERO'	=> 'Сообщений на тему: <strong>0</strong>',

	// Poll
	'POLL'					=> 'Опросы',
	'LATEST_POLLS'			=> 'Последние опросы',
	'NO_OPTIONS'			=> 'В этом опросе нет доступных вариантов.',
	'NO_POLL'				=> 'Нет доступных опросов',
	'RETURN_PORTAL'			=> '%sВернуться к порталу%s',

	// other
	'VIEWING_PORTAL'    => 'Портал',
	'CLOCK'				=> 'Часы',
	'SPONSOR'			=> 'Спонсоры',
	
	/**
	* DO NOT REMOVE or CHANGE
	*/
	'PORTAL_COPY'	=> '<a href="http://www.board3.de" title="board3.de">board3 Portal</a> &ndash; based on <a href="http://www.phpbb3portal.com" title="phpBB3 Portal">phpBB3 Portal</a>',
	)
);

// mini calendar
$lang = array_merge($lang, array(
	'MINI_CALENDAR'		=> 'Календарь',
	'VIEW_NEXT_MONTH'		=> 'Следующий месяц',
	'VIEW_PREVIOUS_MONTH'	=> 'Предыдущий месяц',


	'mini_cal'	=> array(
		'day'	=> array(
			'1'	=> 'Вс',
			'2'	=> 'Пн',
			'3'	=> 'Вт',
			'4'	=> 'Ср',
			'5'	=> 'Чт',
			'6'	=> 'Пт',
			'7'	=> 'Сб',
		),

		'month'	=> array(
			'1'	=> 'Янв',
			'2'	=> 'Фев',
			'3'	=> 'Мар',
			'4'	=> 'Апр',
			'5'	=> 'Май',
			'6'	=> 'Июн',
			'7'	=> 'Июл',
			'8'	=> 'Авг',
			'9'	=> 'Сен',
			'10'=> 'Окт',
			'11'=> 'Ноя',
			'12'=> 'Дек',
		),

		'long_month'=> array(
			'1'	=> 'Январь',
			'2'	=> 'Февраль',
			'3'	=> 'Март',
			'4'	=> 'Апрель',
			'5'	=> 'Май',
			'6'	=> 'Июнь',
			'7'	=> 'Июль',
			'8'	=> 'Август',
			'9'	=> 'Сентябрь',
			'10'=> 'Октябрь',
			'11'=> 'Ноябрь',
			'12'=> 'Декабрь',
		),
	),
));

?>