<?php
/**
*
* @package Support Toolkit - Recache moderators
* @copyright (c) 2010 phpBB Group
* @license GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class recache_moderators
{
	/**
	* Display Options
	*
	* Output the options available
	*/
	function display_options()
	{
		return 'RECACHE_MODERATORS';
	}

	/**
	* Run Tool
	*
	* Does the actual stuff we want the tool to do after submission
	*/
	function run_tool()
	{
		if (!function_exists('cache_moderators'))
		{
			require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');
		}

		cache_moderators();

		trigger_error('RECACHE_MODERATORS_COMPLETE');
	}
}
