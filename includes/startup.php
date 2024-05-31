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

// Check PHP version.
if (version_compare(PHP_VERSION, '5.6', '<')) { die('PHP 5.6+ is required.'); }
if (@preg_match('/\p{L}/u', 'a') === false) { die('PCRE with UTF-8 support is required.'); }
if (!extension_loaded('mbstring')) { die('mbstring is required.'); }

if (file_exists($phpbb_root_path . 'config.php'))
{
	require($phpbb_root_path . 'config.php');
}

// Powered by ...
if (!defined('POWERED_BY'))
{
	define('POWERED_BY', '<a href="//phpbbex.com/">phpBBex</a> v1.9.7 &copy; 2001-2023 <a href="//phpbb.com/">phpBB</a> Group, <a href="//vegalogic.com/">Vegalogic</a> Software');
}

// Error reporting.
if (!defined('ERROR_REPORTING'))
{
	define('ERROR_REPORTING', defined('DEBUG') ? E_ALL : (E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED));
}
error_reporting(ERROR_REPORTING);

// Detect if it's HTTPS.
if (!defined('HTTP_SECURE'))
{
	define('HTTP_SECURE', isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'
		|| isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https'
		|| isset($_SERVER['HTTP_FORWARDED']) && stripos($_SERVER['HTTP_FORWARDED'], 'proto=https') !== false);
}

// Define HTTP host and port.
if (!defined('HTTP_HOST') || !defined('HTTP_PORT'))
{
	// Host header can also be IPv4 or IPv6, and should include port number if it's not default for current protocol.
	if (isset($_SERVER['HTTP_HOST']) && preg_match('#^([-_.0-9a-z]+|\[[:0-9a-f]+\])(?::([1-9][0-9]*))?$#i', $_SERVER['HTTP_HOST'], $m))
	{
		// Set HTTP_HOST to hostname without port.
		if (!defined('HTTP_HOST')) { define('HTTP_HOST', strtolower($m[1])); }
		// Set HTTP_PORT if it's a non-default port only.
		if (!defined('HTTP_PORT')) { define('HTTP_PORT', (empty($m[2]) || intval($m[2]) == (HTTP_SECURE ? 443 : 80)) ? null : intval($m[2])); }
	}
	else
	{
		die('HTTP_HOST is invalid.');
	}
}

// Define HTTP root of phpBBex (starting and ending with /) based on SCRIPT_NAME and $phpbb_root_path.
if (!defined('HTTP_ROOT'))
{
	if (empty($_SERVER['SCRIPT_NAME'])) { die('SCRIPT_NAME is invalid.'); }
	$parts = array();
	$parts[] = '';
	foreach (explode('/', str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] . '/../' . $phpbb_root_path)) as $part)
	{
		if ($part === '' || $part == '.') { continue; }
		if ($part == '..') { array_pop($parts); }
		else { $parts[] = $part; }
	}
	$parts[] = '';
	define('HTTP_ROOT', implode('/', $parts));
	unset($parts);
	unset($part);
}

// Define cookie prefix (e.g. phpbbex_path_).
if (!defined('COOKIE_PREFIX'))
{
	define('COOKIE_PREFIX', 'phpbbex' . preg_replace('#[^a-z0-9]+#i', '_', HTTP_ROOT));
}

// Detect if file uploads are allowed.
if (!defined('PHP_FILE_UPLOADS'))
{
	// Booleans from php.ini are normalized as '0' and '1', but a 'on' may come from a php_admin_value in httpd.conf.
	define('PHP_FILE_UPLOADS', in_array(strtolower((string) @ini_get('file_uploads')), ['1', 'on', 'true', 'yes']));
}

// Configure autoloader
require_once(__DIR__.'/autoloader.php');
autoloader::init(__DIR__.'/classes/');
autoloader::add_path(__DIR__.'/modules/', 'module');

/**
 * Check if requested page uses a trailing path
 *
 * @return bool True if trailing path is used, false if not
 */
function phpbb_has_trailing_path()
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
	if (preg_match('#^[^?]+\.php/#', $_SERVER['REQUEST_URI']))
	{
		return true;
	}

	return false;
}

// Check if trailing path is used
if (phpbb_has_trailing_path())
{
	http_response_code(404);
	echo 'Trailing paths are not allowed.';
	exit;
}

// Prevent date/time functions from throwing E_WARNING by setting a default timezone.
// date_default_timezone_get() tries to guess the correct timezone first and then falls back to UTC if everything fails.
date_default_timezone_set(@date_default_timezone_get());

$starttime = microtime(true);
