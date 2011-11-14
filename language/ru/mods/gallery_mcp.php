<?php
/*
*
* gallery_mcp [Russian] (Pthelovod v1.1.4)
*
* @package phpBB Gallery 
* @version $Id: gallery_mcp.php 915 2009-01-21 22:01:12Z nickvergessen $
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

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

$lang = array_merge($lang, array(
	'CHOOSE_ACTION'					=> 'Выберите желаемое действие',

	'GALLERY_MCP_MAIN'				=> 'Главная',
	'GALLERY_MCP_OVERVIEW'			=> 'Обзор',
	'GALLERY_MCP_QUEUE'				=> 'Очередь на модерацию',
	'GALLERY_MCP_QUEUE_DETAIL'		=> 'Подробности изображения',
	'GALLERY_MCP_REPORTED'			=> 'Обжалованные изображения',
	'GALLERY_MCP_REPO_DONE'			=> 'Закрытые жалобы',
	'GALLERY_MCP_REPO_OPEN'			=> 'Открытые жалобы',
	'GALLERY_MCP_REPO_DETAIL'		=> 'Подробности жалобы',
	'GALLERY_MCP_UNAPPROVED'		=> 'Изображения, ожидающие одобрения',
	'GALLERY_MCP_APPROVED'			=> 'Одобренные изображения',
	'GALLERY_MCP_LOCKED'			=> 'Заблокированные изображения',
	'GALLERY_MCP_VIEWALBUM'			=> 'Посмотреть альбом',

	'IMAGE_REPORTED'				=> 'Это изображение обжалованно.',
	'IMAGE_UNAPPROVED'				=> 'Это изображение ожидает одобрения.',

	'MODERATE_ALBUM'				=> 'Модерировать альбом',

	'LATEST_IMAGES_REPORTED'		=> 'Последние 5 отчетов',
	'LATEST_IMAGES_UNAPPROVED'		=> 'Последние 5 изображений ожидаюших одобрения',
	
	'QUEUE_A_APPROVE'				=> 'Одобрить изображение',
	'QUEUE_A_APPROVE2'				=> 'Одобрить изображение?',
	'QUEUE_A_APPROVE2_CONFIRM'		=> 'Вы уверены что хотите одобрить это изображение?',
	'QUEUE_A_DELETE'				=> 'Удалить изображение',
	'QUEUE_A_DELETE2'				=> 'Удалить изображение?',
	'QUEUE_A_DELETE2_CONFIRM'		=> 'Вы уверены что хотите удалить это изображение?',
	'QUEUE_A_LOCK'					=> 'Заблокировать изображение',
	'QUEUE_A_LOCK2'					=> 'Заблокировать изображение?',
	'QUEUE_A_LOCK2_CONFIRM'			=> 'Вы уверены что хотите заблокировать это изображение?',
	'QUEUE_A_MOVE'					=> 'Переместить изображение',
	'QUEUE_A_UNAPPROVE'				=> 'Направить изображение на одобрение',
	'QUEUE_A_UNAPPROVE2'			=> 'Вы уверены что надо направить изображение на одобрение?',
	'QUEUE_A_UNAPPROVE2_CONFIRM'	=> 'Вы уверены что не хотите одобрить это изображение?',
	
	'QUEUE_STATUS_0'				=> 'Это изображение ожидает одобрения.',
	'QUEUE_STATUS_1'				=> 'Это изображение одобрено.',
	'QUEUE_STATUS_2'				=> 'Это изображение заблокироанно.',

	'QUEUES_A_APPROVE'				=> 'Одобрить изображения',
	'QUEUES_A_APPROVE2'				=> 'Одобрить изображения?',
	'QUEUES_A_APPROVE2_CONFIRM'		=> 'Вы уверены что хотите одобрить эти изображения?',
	'QUEUES_A_DELETE'				=> 'Удалить изображения',
	'QUEUES_A_DELETE2'				=> 'Удалить изображения?',
	'QUEUES_A_DELETE2_CONFIRM'		=> 'Вы уверены что хотите удалить эти изображения?',
	'QUEUES_A_LOCK'					=> 'Заблокировать изображения',
	'QUEUES_A_LOCK2'				=> 'Заблокировать изображения?',
	'QUEUES_A_LOCK2_CONFIRM'		=> 'Вы уверенны что хотите заблокировать эти изображения?',
	'QUEUES_A_MOVE'					=> 'Переместить изображения',
	'QUEUES_A_UNAPPROVE'			=> 'Не одобрять изображения',
	'QUEUES_A_UNAPPROVE2'			=> 'Не одобрять изображения?',
	'QUEUES_A_UNAPPROVE2_CONFIRM'	=> 'Вы уверены что не хотите одобрить эти изображения?',

	'REPORT_A_CLOSE'				=> 'Закрыть жалобу',
	'REPORT_A_CLOSE2'				=> 'Закрыть жалобу?',
	'REPORT_A_CLOSE2_CONFIRM'		=> 'Вы уверенны что хотите закрыть жалобу?',
	'REPORT_A_DELETE'				=> 'Удалить жалобу',
	'REPORT_A_DELETE2'				=> 'Удалить жалобу?',
	'REPORT_A_DELETE2_CONFIRM'		=> 'Вы уверены что хотите удалить жалобу?',
	'REPORT_A_OPEN'					=> 'Открыть жалобу',
	'REPORT_A_OPEN2'				=> 'Открыть жалобу?',
	'REPORT_A_OPEN2_CONFIRM'		=> 'Вы уверены что хотите открыть жалобу?',

	'REPORT_NOT_FOUND'				=> 'Отчет не может быть найден.',
	'REPORT_STATUS_1'				=> 'Эта жалоба нуждается в рассмотрении.',
	'REPORT_STATUS_2'				=> 'Эта жалоба закрыта.',

	'REPORTS_A_CLOSE'				=> 'Закрыть жалобы',
	'REPORTS_A_CLOSE2'				=> 'Закрыть жалобы?',
	'REPORTS_A_CLOSE2_CONFIRM'		=> 'Вы уверены что хотите закрыть жалобы?',
	'REPORTS_A_DELETE'				=> 'Удалить жалобы',
	'REPORTS_A_DELETE2'				=> 'Удалить жалобы?',
	'REPORTS_A_DELETE2_CONFIRM'		=> 'Вы уверены что хотите удалить жалобы?',
	'REPORTS_A_OPEN'				=> 'Открыть жалобы',
	'REPORTS_A_OPEN2'				=> 'Открыть жалобы?',
	'REPORTS_A_OPEN2_CONFIRM'		=> 'Вы уверены что хотите открыть жалобы?',

	'REPORT_MOD'					=> 'Редактировал',
	'REPORTED_IMAGES'				=> 'Обжалованные изображения',
	'REPORTER'						=> 'Пожаловался',
	'REPORTER_AND_ALBUM'			=> 'Жалующийся и альбом',

	'WAITING_APPROVED_IMAGE'		=> array(
		0			=> 'Нет одобренных изображений.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> изображение одобрено.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> изображений одобрено.',
	),
	'WAITING_LOCKED_IMAGE'			=> array(
		0			=> 'Нет заблокированных изображений.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> изображение заблокоровано.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> изображений заблокировано.',
	),
	'WAITING_REPORTED_DONE'			=> array(
		0			=> 'Нет рассмотренных жалоб.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> халоба рассмотрена.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> жалоб рассмотрено.',
	),
	'WAITING_REPORTED_IMAGE'		=> array(
		0			=> 'Нет жалоб на рассмотрение.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> жалоба на рассмотрение.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> жалоб на рассмотрение.',
	),
	'WAITING_UNAPPROVED_IMAGE'		=> array(
		0			=> 'Нет изображений ожидающих одобрения.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> изображение ожидает одобрения.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> изображений ожидают одобрения.',
	),
));

?>