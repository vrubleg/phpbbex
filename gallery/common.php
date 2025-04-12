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

define('IN_PHPBB_GALLERY', true);

// Give admins the easy opertunity to move the gallery beside the forum (root-path example: "photos/../forum/")
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
