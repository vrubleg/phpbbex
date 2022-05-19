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

class erk
{
	function display_options()
	{
		return 'CAT_ERK';
	}

	function run_tool()
	{
		$allow_file = PHPBB_ROOT_PATH . 'cache/allow_erk_' . substr(md5(intval(time() / 1200)), 0, 8) . '.key';
		touch($allow_file);
		header('Location: erk.php');
		die('Redirecting to erk.php...');
	}
}
