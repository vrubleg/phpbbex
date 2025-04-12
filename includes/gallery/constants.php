<?php
/**
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_gallery_constants
{
	// GD library
	const GDLIB1 = 1;
	const GDLIB2 = 2;

	// Watermark positions
	const WATERMARK_TOP = 1;
	const WATERMARK_MIDDLE = 2;
	const WATERMARK_BOTTOM = 4;
	const WATERMARK_LEFT = 8;
	const WATERMARK_CENTER = 16;
	const WATERMARK_RIGHT = 32;

	// Additional constants
	const MODULE_DEFAULT_ACP = 31;
	const MODULE_DEFAULT_LOG = 25;
	const MODULE_DEFAULT_UCP = 0;
	const SEARCH_PAGES_NUMBER = 10;
	const THUMBNAIL_INFO_HEIGHT = 16;
}
