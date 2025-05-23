<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
define('IN_CRON', true);
if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './'); }
require_once(PHPBB_ROOT_PATH . 'common.php');

// Do not update users last page entry
$user->session_begin(false);
$auth->acl($user->data);

$cron_type = request_var('cron_type', '');

// Output transparent gif
header('Cache-Control: no-cache');
header('Content-type: image/gif');
header('Content-length: 43');

echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

// Flush here to prevent browser from showing the page as loading while running cron.
flush();

if (!isset($config['cron_lock']))
{
	set_config('cron_lock', '0', true);
}

// make sure cron doesn't run multiple times in parallel
if ($config['cron_lock'])
{
	// if the other process is running more than an hour already we have to assume it
	// aborted without cleaning the lock
	$time = explode(' ', $config['cron_lock']);
	$time = $time[0];

	if ($time + 3600 >= time())
	{
		exit;
	}
}

define('CRON_ID', time() . ' ' . unique_id());

$sql = 'UPDATE ' . CONFIG_TABLE . "
	SET config_value = '" . $db->sql_escape(CRON_ID) . "'
	WHERE config_name = 'cron_lock' AND config_value = '" . $db->sql_escape($config['cron_lock']) . "'";
$db->sql_query($sql);

// another cron process altered the table between script start and UPDATE query so exit
if ($db->sql_affectedrows() != 1)
{
	exit;
}

/**
* Run cron-like action
* Real cron-based layer will be introduced in 3.2
*/
switch ($cron_type)
{
	case 'queue':

		if (time() - $config['queue_interval'] <= $config['last_queue_run'] || !file_exists(PHPBB_ROOT_PATH . 'cache/queue.php'))
		{
			break;
		}

		require_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.php');
		$queue = new queue();

		$queue->process();

	break;

	case 'tidy_cache':

		if (time() - $config['cache_gc'] <= $config['cache_last_gc'] || !method_exists($cache, 'tidy'))
		{
			break;
		}

		$cache->tidy();

	break;

	case 'tidy_search':

		// Select the search method
		$search_type = basename($config['search_type']);

		if (time() - $config['search_gc'] <= $config['search_last_gc'] || !file_exists(PHPBB_ROOT_PATH . 'includes/search/' . $search_type . '.php'))
		{
			break;
		}

		require_once(PHPBB_ROOT_PATH . "includes/search/$search_type.php");

		// We do some additional checks in the module to ensure it can actually be utilised
		$error = false;
		$search = new $search_type($error);

		if ($error)
		{
			break;
		}

		$search->tidy();

	break;

	case 'tidy_warnings':

		if (time() - $config['warnings_gc'] <= $config['warnings_last_gc'])
		{
			break;
		}

		require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');

		tidy_warnings();

	break;

	case 'tidy_database':

		if (time() - $config['database_gc'] <= $config['database_last_gc'])
		{
			break;
		}

		require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');

		tidy_database();

	break;

	case 'tidy_sessions':

		if (time() - $config['session_gc'] <= $config['session_last_gc'])
		{
			break;
		}

		$user->session_gc();

	break;

	case 'prune_forum':

		$forum_id = request_var('f', 0);

		$sql = 'SELECT forum_id, prune_next, enable_prune, prune_days, prune_viewed, forum_flags, prune_freq
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id = $forum_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			break;
		}

		// Do the forum Prune thang
		if ($row['prune_next'] < time() && $row['enable_prune'])
		{
			require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');

			if ($row['prune_days'])
			{
				auto_prune($row['forum_id'], 'posted', $row['forum_flags'], $row['prune_days'], $row['prune_freq']);
			}

			if ($row['prune_viewed'])
			{
				auto_prune($row['forum_id'], 'viewed', $row['forum_flags'], $row['prune_viewed'], $row['prune_freq']);
			}
		}

	break;
}

// Unloading cache and closing db after having done the dirty work.
unlock_cron();
garbage_collection();

exit;


/**
* Unlock cron script
*/
function unlock_cron()
{
	global $db;

	$sql = 'UPDATE ' . CONFIG_TABLE . "
		SET config_value = '0'
		WHERE config_name = 'cron_lock' AND config_value = '" . $db->sql_escape(CRON_ID) . "'";
	$db->sql_query($sql);
}
