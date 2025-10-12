<?php
/**
*
* gallery_mcp [Russian]
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
	'CHOOSE_ACTION'					=> 'Выберите желаемое действие',

	'GALLERY_MCP_MAIN'				=> 'Главная',
	'GALLERY_MCP_OVERVIEW'			=> 'Обзор',
	'GALLERY_MCP_QUEUE'				=> 'Очередь на модерацию',
	'GALLERY_MCP_QUEUE_DETAIL'		=> 'Информация о фото',
	'GALLERY_MCP_REPORTED'			=> 'Жалобы на фото',
	'GALLERY_MCP_REPO_DONE'			=> 'Закрытые жалобы',
	'GALLERY_MCP_REPO_OPEN'			=> 'Открытые жалобы',
	'GALLERY_MCP_REPO_DETAIL'		=> 'Детали жалобы',
	'GALLERY_MCP_UNAPPROVED'		=> 'Фото, ожидающие одобрения',
	'GALLERY_MCP_APPROVED'			=> 'Одобренные фото',
	'GALLERY_MCP_LOCKED'			=> 'Блокированные фото',
	'GALLERY_MCP_VIEWALBUM'			=> 'Просмотр альбома',

	'IMAGE_REPORTED'				=> 'Это фото обжаловано.',
	'IMAGE_UNAPPROVED'				=> 'Это фото ожидает одобрения.',

	'MODERATE_ALBUM'				=> 'Модерация альбома',

	'LATEST_IMAGES_REPORTED'		=> 'Последние 5 жалоб',
	'LATEST_IMAGES_UNAPPROVED'		=> 'Последние 5 фото, ожидающих одобрения',

	'QUEUE_A_APPROVE'				=> 'Одобрить фотографию',
	'QUEUE_A_APPROVE2'				=> 'Одобрить фотографию?',
	'QUEUE_A_APPROVE2_CONFIRM'		=> 'Подтвердите одобрение фотографии.',
	'QUEUE_A_DELETE'				=> 'Удалить фотографию',
	'QUEUE_A_DELETE2'				=> 'Удалить фотографию?',
	'QUEUE_A_DELETE2_CONFIRM'		=> 'Подтвердите удаление фотографии.',
	'QUEUE_A_LOCK'					=> 'Блокировать фотографию',
	'QUEUE_A_LOCK2'					=> 'Блокировать фотографию?',
	'QUEUE_A_LOCK2_CONFIRM'			=> 'Подтвердите блокировку фотографии.',
	'QUEUE_A_MOVE'					=> 'Переместить фотографию',
	'QUEUE_A_UNAPPROVE'				=> 'Отклонить фотографию',
	'QUEUE_A_UNAPPROVE2'			=> 'Отклонить фотографию?',
	'QUEUE_A_UNAPPROVE2_CONFIRM'	=> 'Подтвердите, что хотите отклонить эту фотографию.',

	'QUEUE_STATUS_0'				=> 'Ожидание',
	'QUEUE_STATUS_1'				=> 'Одобрено',
	'QUEUE_STATUS_2'				=> 'Блокировано',

	'QUEUES_A_APPROVE'				=> 'Одобрить фотографии',
	'QUEUES_A_APPROVE2'				=> 'Одобрить фотографии?',
	'QUEUES_A_APPROVE2_CONFIRM'		=> 'Подтвердите одобрение фотографий.',
	'QUEUES_A_DELETE'				=> 'Удалить фотографии',
	'QUEUES_A_DELETE2'				=> 'Удалить фотографии?',
	'QUEUES_A_DELETE2_CONFIRM'		=> 'Подтвердите удаление фотографий.',
	'QUEUES_A_LOCK'					=> 'Заблокировать фотографии',
	'QUEUES_A_LOCK2'				=> 'Заблокировать фотографии?',
	'QUEUES_A_LOCK2_CONFIRM'		=> 'Подтвердите блокировку фотографий.',
	'QUEUES_A_MOVE'					=> 'Переместить фотографии',
	'QUEUES_A_UNAPPROVE'			=> 'Отклонить фотографии',
	'QUEUES_A_UNAPPROVE2'			=> 'Отклонить фотографии?',
	'QUEUES_A_UNAPPROVE2_CONFIRM'	=> 'Подтвердите, что хотите отклонить эти фотографии.',

	'REPORT_A_CLOSE'				=> 'Закрыть жалобу',
	'REPORT_A_CLOSE2'				=> 'Закрыть жалобу?',
	'REPORT_A_CLOSE2_CONFIRM'		=> 'Подтвердите закрытие жалобы.',
	'REPORT_A_DELETE'				=> 'Удалить жалобу',
	'REPORT_A_DELETE2'				=> 'Удалить жалобу?',
	'REPORT_A_DELETE2_CONFIRM'		=> 'Подтвердите удаление жалобы.',
	'REPORT_A_OPEN'					=> 'Открыть жалобу',
	'REPORT_A_OPEN2'				=> 'Открыть жалобу?',
	'REPORT_A_OPEN2_CONFIRM'		=> 'Подтвердите открытие жалобы.',

	'REPORT_NOT_FOUND'				=> 'Жалоба не найдена.',
	'REPORT_STATUS_1'				=> 'Эта жалоба нуждается в рассмотрении.',
	'REPORT_STATUS_2'				=> 'Эта жалоба закрыта.',

	'REPORTS_A_CLOSE'				=> 'Закрыть жалобы',
	'REPORTS_A_CLOSE2'				=> 'Закрыть жалобы?',
	'REPORTS_A_CLOSE2_CONFIRM'		=> 'Подтвердите закрытие жалоб.',
	'REPORTS_A_DELETE'				=> 'Удалить жалобы',
	'REPORTS_A_DELETE2'				=> 'Удалить жалобы?',
	'REPORTS_A_DELETE2_CONFIRM'		=> 'Подтвердите удаление жалоб.',
	'REPORTS_A_OPEN'				=> 'Открыть жалобы',
	'REPORTS_A_OPEN2'				=> 'Открыть жалобы?',
	'REPORTS_A_OPEN2_CONFIRM'		=> 'Подтвердите открытие жалоб.',

	'REPORT_MOD'					=> 'Редактировал',
	'REPORTED_IMAGES'				=> 'Жалобы на фото',
	'REPORTER'						=> 'Пожаловался',
	'REPORTER_AND_ALBUM'			=> 'Жалующийся и альбом',

	'WAITING_APPROVED_IMAGE'		=> [
		0			=> 'Нет одобренных фотографий.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> фотографий одобрено.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> фотографий одобрено.',
	],
	'WAITING_LOCKED_IMAGE'			=> [
		0			=> 'Нет блокированных фотографий.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> фотография блокорована.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> фотографий блокировано.',
	],
	'WAITING_REPORTED_DONE'			=> [
		0			=> 'Нет рассмотренных жалоб.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> жалоба рассмотрена.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> жалоб рассмотрено.',
	],
	'WAITING_REPORTED_IMAGE'		=> [
		0			=> 'Нет жалоб на рассмотрение.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> жалоба на рассмотрение.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> жалоб на рассмотрение.',
	],
	'WAITING_UNAPPROVED_IMAGE'		=> [
		0			=> 'Нет фотографий, ожидающих одобрения.',
		1			=> 'Всего <span style="font-weight: bold;">1</span> фотография ожидает одобрения.',
		2			=> 'Всего <span style="font-weight: bold;">%s</span> фотографий ожидают одобрения.',
	],
]);
