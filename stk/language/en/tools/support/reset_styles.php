<?php
/**
* @package phpBBex Support Toolkit
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
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
	'RESET_STYLES'			=> 'Reset Styles',
	'RESET_STYLES_EXPLAIN'	=> 'This tool allows you to change a board’s default style.',
	'RESET_STYLE_COMPLETE'	=> 'The default style has been changed successfully.',

	'STYLE'					=> 'Style',
	'STYLE_EXPLAIN'			=> 'Select the style you want set as the default.',
));
