<?php
/**
* @package phpBBex Support Toolkit
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
define('IN_ERK', true);

if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './../'); }
if (!defined('PHPBB_CACHE_PATH')) { define('PHPBB_CACHE_PATH', PHPBB_ROOT_PATH . 'cache/'); }
if (!defined('STK_DIR_NAME')) { define('STK_DIR_NAME', substr(strrchr(__DIR__, DIRECTORY_SEPARATOR), 1)); }	// Get the name of the stk directory
if (!defined('STK_ROOT_PATH')) { define('STK_ROOT_PATH', './'); }
if (!defined('STK_INDEX')) { define('STK_INDEX', STK_ROOT_PATH . 'index.php'); }

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';

require_once(STK_ROOT_PATH . 'includes/critical_repair.php');
$critical_repair = new critical_repair();

// Check if there is a recent ERK allow key file, not older than 60 minutes.

$time_span = intval(time() / 1200);
$curr_keys = [
	substr(md5($time_span - 0), 0, 8),
	substr(md5($time_span - 1), 0, 8),
	substr(md5($time_span - 2), 0, 8)
];

$allowed = false;
foreach ($curr_keys as $key)
{
	if (file_exists(PHPBB_CACHE_PATH . 'allow_erk_' . $key . '.key'))
	{
		$allowed = true;
		break;
	}
}

if (!$allowed)
{
	$critical_repair->trigger_error('Run ERK through STK. If you cannot login, create an empty file at <tt>/cache/allow_erk_' . $curr_keys[0] . '.key</tt> to to allow running ERK directly.', false);
}

// Try to override some limits - maybe it helps some...

@ini_set('memory_limit', '128M');
@set_time_limit(3600);

// Init critical repair and run the tools that *must* be ran before initing anything else

$critical_repair->initialize();
$critical_repair->run_tool('bom_sniffer');
$critical_repair->run_tool('config_repair');

require_once(STK_ROOT_PATH . 'common.php');

// We'll run the rest of the critical repair tools automatically now
$critical_repair->autorun_tools();

// At this point things should be runnable
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('acp/common', $config['default_style']);

// Purge teh caches
$umil = new phpbb_umil();
$umil->cache_purge(array(
	'data',
	'template',
	'theme',
	'imageset',
));

// Remove old ERK allow key files.
if ($dir = opendir(PHPBB_CACHE_PATH))
{
	while (($entry = readdir($dir)) !== false)
	{
		if (strpos($entry, 'allow_erk_') === 0)
		{
			@unlink(PHPBB_CACHE_PATH . $entry);
		}
	}
	closedir($dir);
}

// Let's tell the user all is okay :)
$critical_repair->trigger_error("The Emergency Repair Kit hasn't found any critical issues within your phpBB installation.", true);
