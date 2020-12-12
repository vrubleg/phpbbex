<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Report all errors
error_reporting(E_ALL);

// Check PHP version
if (version_compare(PHP_VERSION, '5.4', '<')) die('PHP 5.4+ is required.');
if (@preg_match('/\p{L}/u', 'a') === false) die('PCRE with UTF8 support is required.');

// Powered by ...
define('POWERED_BY', '<a href="//phpbbex.com/">phpBBex</a> &copy; 2015 <a href="//phpbb.com/">phpBB</a> Group, <a href="//vegalogic.com/">Vegalogic</a> Software');

// Configure autoloader
require(dirname(__FILE__).'/../classes/autoloader.php');
autoloader::init(dirname(__FILE__).'/../classes/');
autoloader::add_path(dirname(__FILE__).'/../modules/', 'module');

/**
 * Check if requested page uses a trailing path
 *
 * @param string $phpEx PHP extension
 *
 * @return bool True if trailing path is used, false if not
 */
function phpbb_has_trailing_path($phpEx)
{
	// Check if path_info is being used
	if (!empty($_SERVER['PATH_INFO']) || (!empty($_SERVER['ORIG_PATH_INFO']) && $_SERVER['SCRIPT_NAME'] != $_SERVER['ORIG_PATH_INFO']))
	{
		return true;
	}

	// Match any trailing path appended to a php script in the REQUEST_URI.
	// It is assumed that only actual PHP scripts use names like foo.php. Due
	// to this, any phpBB board inside a directory that has the php extension
	// appended to its name will stop working, i.e. if the board is at
	// example.com/phpBB/test.php/ or example.com/test.php/
	if (preg_match('#^[^?]+\.' . preg_quote($phpEx, '#') . '/#', $_SERVER['REQUEST_URI']))
	{
		return true;
	}

	return false;
}

// Check if trailing path is used
if (phpbb_has_trailing_path($phpEx))
{
	if (substr(strtolower(@php_sapi_name()), 0, 3) === 'cgi')
	{
		$prefix = 'Status:';
	}
	else if (!empty($_SERVER['SERVER_PROTOCOL']) && is_string($_SERVER['SERVER_PROTOCOL']) && preg_match('#^HTTP/[0-9]\.[0-9]$#', $_SERVER['SERVER_PROTOCOL']))
	{
		$prefix = $_SERVER['SERVER_PROTOCOL'];
	}
	else
	{
		$prefix = 'HTTP/1.0';
	}
	header("$prefix 404 Not Found", true, 404);
	echo 'Trailing paths and PATH_INFO is not supported by phpBB 3.0';
	exit;
}

// Prevent date/time functions from throwing E_WARNING on PHP 5.3 by setting a default timezone
if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get'))
{
	// So what we basically want to do is set our timezone to UTC,
	// but we don't know what other scripts (such as bridges) are involved,
	// so we check whether a timezone is already set by calling date_default_timezone_get().

	// Unfortunately, date_default_timezone_get() itself might throw E_WARNING
	// if no timezone has been set, so we have to keep it quiet with @.

	// date_default_timezone_get() tries to guess the correct timezone first
	// and then falls back to UTC when everything fails.
	// We just set the timezone to whatever date_default_timezone_get() returns.
	date_default_timezone_set(@date_default_timezone_get());
}

$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];
