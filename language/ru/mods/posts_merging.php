<?php
/** 
*
* posting [Russian]
*
* @package language
* @version $Id: posts_merging.php,v 1.01 2007/10/16 17:20:20 rxu Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
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

	'MERGE_SEPARATOR'		=> "\n\n[size=85][color=gray]%s спустя %s:[/color][/size]\n",
	'MERGE_SUBJECT'			=> "[size=85][color=gray]%s[/color][/size]\n",

// Time delta
	'D_SECONDS'  => array('секунду', 'секунды', 'секунд'),
	'D_MINUTES'  => array('минуту', 'минуты', 'минут'),
	'D_HOURS'    => array('час', 'часа', 'часов'),
	'D_MDAY'     => array('день', 'дня', 'дней'),
	'D_MON'      => array('месяц', 'месяца', 'месяцев'),
	'D_YEAR'     => array('год', 'года', 'лет'),
// ACP block
	'MERGE_INTERVAL'				=> 'Интервал склеивания сообщений',
	'MERGE_INTERVAL_EXPLAIN'		=> 'Количество часов, в течение которого сообщения пользователя будут склеены с его последним сообщением темы. Оставьте поле пустым или установите 0 для отключения этой функции.',
	'MERGE_NO_TOPICS'				=> 'Темы без склеивания',
	'MERGE_NO_TOPICS_EXPLAIN'		=> 'Список разделённых запятыми номеров тем, в которых склеивание сообщений отключено.',
	'MERGE_NO_FORUMS'				=> 'Форумы без склеивания',
	'MERGE_NO_FORUMS_EXPLAIN'		=> 'Список разделённых запятыми номеров форумов, в которых склеивание сообщений отключено.',


));

?>