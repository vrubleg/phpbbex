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
* Placeholder for autoload
*/
if (!class_exists('phpbb_default_captcha'))
{
	include($phpbb_root_path . 'includes/captcha/plugins/captcha_abstract.php');
}

class phpbb_captcha_nogd extends phpbb_default_captcha
{

	function __construct()
	{
		global $phpbb_root_path;

		if (!class_exists('captcha'))
		{
			include_once($phpbb_root_path . 'includes/captcha/captcha_non_gd.php');
		}
	}

	static function get_instance()
	{
		static $instance = null;
		if ($instance === null) { $instance = new self(); }
		return $instance;
	}

	static function is_available()
	{
		return true;
	}

	static function get_name()
	{
		return 'CAPTCHA_NO_GD';
	}

	static function get_class_name()
	{
		return 'phpbb_captcha_nogd';
	}

	function acp_page($id, &$module)
	{
		global $user;

		trigger_error($user->lang['CAPTCHA_NO_OPTIONS'] . adm_back_link($module->u_action));
	}
}
