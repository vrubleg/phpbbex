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
	// Redirect the user to the installer
	require($phpbb_root_path . 'includes/functions.' . $phpEx);

	// We have to generate a full HTTP/1.1 header here since we can't guarantee to have any of the information
	// available as used by the redirect function
	$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
	$server_port = (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');
	$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;

	$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
	if (!$script_name)
	{
		$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	}

	// $phpbb_root_path accounts for redirects from e.g. /adm
	$script_path = trim(dirname($script_name)) . '/' . $phpbb_root_path . 'install/index.' . $phpEx;
	// Replace any number of consecutive backslashes and/or slashes with a single slash
	// (could happen on some proxy setups and/or Windows servers)
	$script_path = preg_replace('#[\\\\/]{2,}#', '/', $script_path);
	// Eliminate . and .. from the path
	$script_path = phpbb_clean_path($script_path);

	$url = (($secure) ? 'https://' : 'http://') . $server_name;

	if ($server_port && !$secure && $server_port <> 80)
	{
		// HTTP HOST can carry a port number...
		if (strpos($server_name, ':') === false)
		{
			$url .= ':' . $server_port;
		}
	}

	$url .= $script_path;
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
require($phpbb_root_path . 'includes/db/mysqli.' . $phpEx);
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
$db			= new dbal_mysqli();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);

// We do not need this any longer, unset for safety purposes
unset($dbpasswd);

// Grab global variables, re-cache if necessary
$config = $cache->obtain_config();
