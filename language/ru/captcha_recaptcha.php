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
	'RECAPTCHA_LANG'				=> 'ru',
	'RECAPTCHA_NOT_AVAILABLE'		=> 'Для использования reCaptcha необходимо создать учётную запись на сайте <a href="http://www.google.com/recaptcha">www.google.com/recaptcha</a>.',
	'CAPTCHA_RECAPTCHA'				=> 'reCaptcha',
	'RECAPTCHA_INCORRECT'			=> 'Неверный код визуального подтверждения',

	'RECAPTCHA_PUBLIC'				=> 'Публичный ключ reCaptcha (Public Key)',
	'RECAPTCHA_PUBLIC_EXPLAIN'		=> 'Ваш публичный ключ reCaptcha. Ключи можно получить на сайте <a href="http://www.google.com/recaptcha">www.google.com/recaptcha</a>.',
	'RECAPTCHA_PRIVATE'				=> 'Закрытый ключ reCaptcha (Private Key)',
	'RECAPTCHA_PRIVATE_EXPLAIN'		=> 'Ваш закрытый ключ reCaptcha. Ключи можно получить на сайте <a href="http://www.google.com/recaptcha">www.google.com/recaptcha</a>.',

	'RECAPTCHA_EXPLAIN'				=> 'В целях предотвращения автоматической отправки форм, введите оба отображённых слова в текстовое поле ниже.',
));
