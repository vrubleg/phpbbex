<?php
/**
*
* exif_data [Russian] (Phelovod v1.1.4)
*
* @package phpBB Gallery / NV Exif Data
* @version $Id$
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
	    'EXIF-DATA'					=> 'EXIF-данные',
	    'EXIF_APERTURE'				=> 'Диафрагма',
	    'EXIF_CAM_MODEL'			=> 'Модель камеры',
	    'EXIF_DATE'					=> 'Дата съемки',
	    'EXIF_EXPOSURE'				=> 'Выдержка',
		'EXIF_EXPOSURE_EXP'			=> '%s Сек',// 'EXIF_EXPOSURE' unit
		'EXIF_EXPOSURE_BIAS'		=> 'Коррекция экспозиции',
		'EXIF_EXPOSURE_BIAS_EXP'	=> '%s EV',// 'EXIF_EXPOSURE_BIAS' unit
     	'EXIF_EXPOSURE_PROG'		=> 'Режим съемки',
		'EXIF_EXPOSURE_PROG_0'		=> 'Не определен',
		'EXIF_EXPOSURE_PROG_1'		=> 'Ручной',
		'EXIF_EXPOSURE_PROG_2'		=> 'Программный',
		'EXIF_EXPOSURE_PROG_3'		=> 'Приоритет диафрагмы',
		'EXIF_EXPOSURE_PROG_4'		=> 'Приоритет выдержки',
		'EXIF_EXPOSURE_PROG_5'		=> 'Творческий режим (режим с ручными настройками)',
		'EXIF_EXPOSURE_PROG_6'		=> 'Режим СПОРТ (режим сьемки на быстрых выдержках)',
		'EXIF_EXPOSURE_PROG_7'		=> 'Портретный (для сьемок на небольшие расстояния)',
		'EXIF_EXPOSURE_PROG_8'		=> 'Пейзажный (для сьемок на большие расстояния)',
    	'EXIF_FLASH'				=> 'Вспышка',
		'EXIF_FLASH_CASE_0'			=> 'Вспышка не срабатывала',
		'EXIF_FLASH_CASE_1'			=> 'Вспышка срабатывала',
		'EXIF_FLASH_CASE_5'			=> 'режим подавление эффекта «красных глаз» выключен',
		'EXIF_FLASH_CASE_7'			=> 'режим подавление эффекта «красных глаз» включен',
		'EXIF_FLASH_CASE_8'			=> 'Включена, вспышка не сработала',
		'EXIF_FLASH_CASE_9'			=> 'Вспышка сработала, ручной режим',
		'EXIF_FLASH_CASE_13'		=> 'Вспышка сработала, ручной режим, эффект «красных глаз»  не обнаружен',
		'EXIF_FLASH_CASE_15'		=> 'Вспышка сработала, ручной режим, эффект «красных глаз»  обнаружен',
		'EXIF_FLASH_CASE_16'		=> 'Вспышка не сработала, ручной режим',
		'EXIF_FLASH_CASE_20'		=> 'Выключено, вспышка не сработала, эффект «красных глаз»  не обнаружен',
		'EXIF_FLASH_CASE_24'		=> 'Вспышка не сработала, автоматический режим',
		'EXIF_FLASH_CASE_25'		=> 'Вспышка сработала, автоматический режим',
		'EXIF_FLASH_CASE_29'		=> 'Вспышка сработала, автоматический режим, эффект «красных глаз»  не обнаружен',
		'EXIF_FLASH_CASE_31'		=> 'Вспышка сработала, автоматический режим, эффект «красных глаз»  обнаружен',
		'EXIF_FLASH_CASE_32'		=> 'Нет вспышки',
		'EXIF_FLASH_CASE_48'		=> 'Выключено. Нет вспышки',
		'EXIF_FLASH_CASE_65'		=> 'Вспышка сработала, режим красных глаз',
		'EXIF_FLASH_CASE_69'		=> 'Вспышка сработала, режим красных глаз, эффект «красных глаз»  не обнаружен',
		'EXIF_FLASH_CASE_71'		=> 'Вспышка сработала, режим красных глаз, эффект «красных глаз»  обнаружен',
		'EXIF_FLASH_CASE_73'		=> 'Вспышка сработала, ручной режим, режим подавление эффекта «красных глаз» ',
		'EXIF_FLASH_CASE_77'		=> 'Вспышка сработала, ручной режим, режим подавление эффекта «красных глаз», эффект «красных глаз» не обнаружен',
		'EXIF_FLASH_CASE_79'		=> 'Вспышка сработала, ручной режим, режим подавление эффекта «красных глаз», эффект «красных глаз» обнаружен',
		'EXIF_FLASH_CASE_80'		=> 'Выключено, режим подавление эффекта «красных глаз»',
		'EXIF_FLASH_CASE_88'		=> 'Автоматический режим, вспышка не сработала, режим подавление эффекта «красных глаз»',
		'EXIF_FLASH_CASE_89'		=> 'Вспышка сработала, автоматический режим, режим подавление эффекта «красных глаз»',
		'EXIF_FLASH_CASE_93'		=> 'Вспышка сработала, автоматический режим, эффект «красных глаз» не обнаружен, режим подавление эффекта «красных глаз»',
		'EXIF_FLASH_CASE_95'		=> 'Вспышка сработала, автоматический режим, эффект «красных глаз» обнаружен, режим подавление эффекта «красных глаз»',
     	'EXIF_FOCAL'				=> 'Фокусное расстояние',
		'EXIF_FOCAL_EXP'			=> '%s мм',// 'EXIF_FOCAL' unit
	    'EXIF_ISO'					=> 'ISO чуствительность',
	    'EXIF_METERING_MODE'		=> 'Режим замера экспозиции',
		'EXIF_METERING_MODE_0'		=> 'Неизвестно',
		'EXIF_METERING_MODE_1'		=> 'Среднее',
		'EXIF_METERING_MODE_2'		=> 'Средне-взвешенное',
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
));

?>