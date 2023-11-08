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

/**
* Visual confirmation
*
* Note to potential users of this code ...
*
* Remember this is released under the _GPL_ and is subject
* to that licence. Do not incorporate this within software
* released or distributed in any way under a licence other
* than the GPL. We will be watching ... ;)
*/
class ucp_confirm
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $db, $user, $phpbb_root_path, $config;

		include($phpbb_root_path . 'includes/captcha/captcha_factory.php');
		$captcha = phpbb_captcha_factory::get_instance($config['captcha_plugin']);
		$captcha->init(request_var('type', 0));
		$captcha->execute();

		garbage_collection();
		exit_handler();
	}
}
