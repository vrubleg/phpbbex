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
	'EXIF_DATA'                 => 'Данные EXIF',
	'EXIF_CAM_MODEL'            => 'Модель камеры',
	'EXIF_DATE'                 => 'Дата съёмки',
]);
