<?php
/**
*
* exif_data [Russian]
*
* @package phpBB Gallery / NV Exif Data
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
	'EXIF-DATA'					=> 'Данные EXIF',
	'EXIF_APERTURE'				=> 'Диафрагма',
	'EXIF_CAM_MODEL'			=> 'Модель камеры',
	'EXIF_DATE'					=> 'Дата съёмки',
	'EXIF_EXPOSURE'				=> 'Выдержка',
		'EXIF_EXPOSURE_EXP'			=> '%s с',// 'EXIF_EXPOSURE' unit
	'EXIF_EXPOSURE_BIAS'		=> 'Экспокоррекция',
		'EXIF_EXPOSURE_BIAS_EXP'	=> '%s EV',// 'EXIF_EXPOSURE_BIAS' unit
	'EXIF_EXPOSURE_PROG'		=> 'Режим съёмки',
		'EXIF_EXPOSURE_PROG_0'		=> 'Не определён',
		'EXIF_EXPOSURE_PROG_1'		=> 'Ручной',
		'EXIF_EXPOSURE_PROG_2'		=> 'Программный',
		'EXIF_EXPOSURE_PROG_3'		=> 'Приоритет диафрагмы',
		'EXIF_EXPOSURE_PROG_4'		=> 'Приоритет выдержки',
		'EXIF_EXPOSURE_PROG_5'		=> 'Творческий (с уклоном к глубине резкости)',
		'EXIF_EXPOSURE_PROG_6'		=> 'Спортивный (с уклоном к минимальной выдержке)',
		'EXIF_EXPOSURE_PROG_7'		=> 'Портретный (с уклоном к фокусировке на переднем плане)',
		'EXIF_EXPOSURE_PROG_8'		=> 'Пейзажный (с уклоном к фокусировке на заднем плане)',
	'EXIF_FLASH'				=> 'Вспышка',
		'EXIF_FLASH_CASE_0'			=> 'Выспышка не срабатывала',
		'EXIF_FLASH_CASE_1'			=> 'Выспышка срабатывала',
		'EXIF_FLASH_CASE_5'			=> 'отражённый свет не обнаружен',
		'EXIF_FLASH_CASE_7'			=> 'отражённый свет обнаружен',
		'EXIF_FLASH_CASE_8'			=> 'Включена, вспышка не сработала',
		'EXIF_FLASH_CASE_9'			=> 'Вспышка сработала, ручной режим',
		'EXIF_FLASH_CASE_13'		=> 'Вспышка сработала, ручной режим, отражённый свет не обнаружен',
		'EXIF_FLASH_CASE_15'		=> 'Вспышка сработала, ручной режим, отражённый свет обнаружен',
		'EXIF_FLASH_CASE_16'		=> 'Вспышка не сработала, ручной режим',
		'EXIF_FLASH_CASE_20'		=> 'Выключено, вспышка не сработала, отражённый свет не обнаружен',
		'EXIF_FLASH_CASE_24'		=> 'Вспышка не сработала, автоматический режим',
		'EXIF_FLASH_CASE_25'		=> 'Вспышка сработала, автоматический режим',
		'EXIF_FLASH_CASE_29'		=> 'Вспышка сработала, автоматический режим, отражённый свет не обнаружен',
		'EXIF_FLASH_CASE_31'		=> 'Вспышка сработала, автоматический режим, отражённый свет обнаружен',
		'EXIF_FLASH_CASE_32'		=> 'Нет вспышки',
		'EXIF_FLASH_CASE_48'		=> 'Выключено. Нет вспышки',
		'EXIF_FLASH_CASE_65'		=> 'Вспышка сработала, режим красных глаз',
		'EXIF_FLASH_CASE_69'		=> 'Вспышка сработала, режим красных глаз, отражённый свет не обнаружен',
		'EXIF_FLASH_CASE_71'		=> 'Вспышка сработала, режим красных глаз, отражённый свет обнаружен',
		'EXIF_FLASH_CASE_73'		=> 'Вспышка сработала, ручной режим, режим красных глаз',
		'EXIF_FLASH_CASE_77'		=> 'Вспышка сработала, ручной режим, режим красных глаз, отражённый свет не обнаружен',
		'EXIF_FLASH_CASE_79'		=> 'Вспышка сработала, ручной режим, режим красных глаз, отражённый свет обнаружен',
		'EXIF_FLASH_CASE_80'		=> 'Выключено, режим красных глаз',
		'EXIF_FLASH_CASE_88'		=> 'Автоматический режим, вспышка не сработала, режим красных глаз',
		'EXIF_FLASH_CASE_89'		=> 'Вспышка сработала, автоматический режим, режим красных глаз',
		'EXIF_FLASH_CASE_93'		=> 'Вспышка сработала, автоматический режим, отражённый свет не обнаружен, режим красных глаз',
		'EXIF_FLASH_CASE_95'		=> 'Вспышка сработала, автоматический режим, отражённый свет обнаружен, режим красных глаз',
	'EXIF_FOCAL'				=> 'Фокусное расстояние',
		'EXIF_FOCAL_EXP'			=> '%s мм',// 'EXIF_FOCAL' unit
	'EXIF_ISO'					=> 'Светочувствительность',
	'EXIF_METERING_MODE'		=> 'Режим экспозамера',
		'EXIF_METERING_MODE_0'		=> 'Неизвестно',
		'EXIF_METERING_MODE_1'		=> 'Среднее',
		'EXIF_METERING_MODE_2'		=> 'Средневзвешенное',
		'EXIF_METERING_MODE_3'		=> 'Точечный',
		'EXIF_METERING_MODE_4'		=> 'Мультиточечный',
		'EXIF_METERING_MODE_5'		=> 'Шаблон',
		'EXIF_METERING_MODE_6'		=> 'Частичный',
		'EXIF_METERING_MODE_255'	=> 'Другое',
	'EXIF_NOT_AVAILABLE'		=> 'недоступно',
	'EXIF_WHITEB'				=> 'Баланс белого',
		'EXIF_WHITEB_AUTO'			=> 'Автоматический',
		'EXIF_WHITEB_MANU'			=> 'Ручной',

	'SHOW_EXIF'					=> 'показать/скрыть',
]);
