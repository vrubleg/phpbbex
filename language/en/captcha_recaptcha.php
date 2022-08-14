<?php
/**
* @package phpBBex
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
	'RECAPTCHA_LANG'				=> 'en',
	'RECAPTCHA_NOT_AVAILABLE'		=> 'In order to use reCaptcha, you must create an account on <a href="http://www.google.com/recaptcha">www.google.com/recaptcha</a>.',
	'CAPTCHA_RECAPTCHA'				=> 'reCaptcha',
	'RECAPTCHA_INCORRECT'			=> 'The visual confirmation code you submitted was incorrect',

	'RECAPTCHA_PUBLIC'				=> 'Public reCaptcha key',
	'RECAPTCHA_PUBLIC_EXPLAIN'		=> 'Your public reCaptcha key. Keys can be obtained on <a href="http://www.google.com/recaptcha">www.google.com/recaptcha</a>.',
	'RECAPTCHA_PRIVATE'				=> 'Private reCaptcha key',
	'RECAPTCHA_PRIVATE_EXPLAIN'		=> 'Your private reCaptcha key. Keys can be obtained on <a href="http://www.google.com/recaptcha">www.google.com/recaptcha</a>.',

	'RECAPTCHA_EXPLAIN'				=> 'In an effort to prevent automatic submissions, we require that you enter both of the words displayed into the text field underneath.',
));
