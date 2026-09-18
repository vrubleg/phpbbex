<?php
/**
*
* exif_data [English]
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
	'EXIF_DATA'                 => 'EXIF Data',
	'EXIF_CAM_MODEL'            => 'Camera',
	'EXIF_DATE'                 => 'Image taken on',
]);
