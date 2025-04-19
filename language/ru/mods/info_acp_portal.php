<?php

/**
*
* @package - Board3portal
* @version $Id: info_acp_portal.php 503 2009-04-20 18:34:29Z kevin74 $
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

$lang = array_merge($lang, array(
	'ACP_PORTAL_INFO'							=> 'Портал',
	'ACP_PORTAL_GENERAL_INFO'					=> 'Общее',
	'ACP_PORTAL_ANNOUNCEMENTS_INFO'				=> 'Объявления',
	'ACP_PORTAL_NEWS_INFO'						=> 'Новости',
	'ACP_PORTAL_RECENT_INFO'					=> 'Свежие темы',
	'ACP_PORTAL_WORDGRAPH_INFO'					=> 'Ключевые слова',
	'ACP_PORTAL_GENERAL_INFO'					=> 'Общие настройки',
	'ACP_PORTAL_PAYPAL_INFO'					=> 'Пожертвование',
	'ACP_PORTAL_ATTACHMENTS_INFO'				=> 'Вложения',
	'ACP_PORTAL_MEMBERS_INFO'					=> 'Новые участники',
	'ACP_PORTAL_POLLS_INFO'						=> 'Опросы',
	'ACP_PORTAL_BOTS_INFO'						=> 'Роботы',
	'ACP_PORTAL_POSTER_INFO'					=> 'Активные участники',
	'ACP_PORTAL_WELCOME_INFO'					=> 'Добро пожаловать',
	'ACP_PORTAL_CUSTOMBLOCK_INFO'				=> 'Настраиваемый блок',
	'ACP_PORTAL_MINICALENDAR_INFO'				=> 'Календарь',
	'ACP_PORTAL_LINKS_INFO'                     => 'Ссылки',
	'ACP_PORTAL_BIRTHDAYS_INFO'					=> 'Дни рождения',
	'ACP_PORTAL_FRIENDS_INFO'					=> 'Друзья',
	
	// Logs
	'LOG_PORTAL_CONFIG'							=> '<strong>Настройки портала изменены</strong><br />&raquo; %s',
	
	// Adding the permissions
	'acl_a_portal_manage'           => array('lang' => 'Может изменять настройки портала', 'cat' => 'misc'),
));

?>