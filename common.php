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

require($phpbb_root_path . 'includes/startup.' . $phpEx);

if (file_exists($phpbb_root_path . 'config.' . $phpEx))
{
	require($phpbb_root_path . 'config.' . $phpEx);
}

if (!defined('PHPBB_INSTALLED'))
{
	// Redirect the user to the installer.
	$url = (HTTP_SECURE ? 'https://' : 'http://') . HTTP_HOST . (HTTP_PORT ? ':' . HTTP_PORT : '') . HTTP_ROOT . 'install/index.' . $phpEx;
	header('Location: ' . $url);
	exit;
}

if (defined('DEBUG_EXTRA'))
{
	$base_memory_usage = 0;
	if (function_exists('memory_get_usage'))
	{
		$base_memory_usage = memory_get_usage();
	}
}

// Include files
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.' . $phpEx);
require($phpbb_root_path . 'includes/cache.' . $phpEx);
require($phpbb_root_path . 'includes/template.' . $phpEx);
require($phpbb_root_path . 'includes/session.' . $phpEx);
require($phpbb_root_path . 'includes/auth.' . $phpEx);

require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_content.' . $phpEx);

require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/db/mysql.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

// Set PHP error handler to ours
set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
	$msg_handler = defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler';
	$msg_handler($errno, $errstr, $errfile, $errline, array_slice(debug_backtrace(), 1));
});
set_exception_handler(function ($e)
{
	$msg_handler = defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler';
	$msg_handler(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
});

// Instantiate some basic classes
$user		= new phpbb_user();
$auth		= new phpbb_auth();
$template	= new phpbb_template();
$cache		= new phpbb_cache();
$db			= new dbal_mysql();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);

// We do not need this any longer, unset for safety purposes
unset($dbpasswd);

// Grab global variables, re-cache if necessary
$config = $cache->obtain_config();
