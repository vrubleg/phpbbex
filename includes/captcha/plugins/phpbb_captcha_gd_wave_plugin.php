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
	include($phpbb_root_path . 'includes/captcha/plugins/captcha_abstract.' . $phpEx);
}

class phpbb_captcha_gd_wave extends phpbb_default_captcha
{

	function __construct()
	{
		global $phpbb_root_path, $phpEx;

		if (!class_exists('captcha'))
		{
			include_once($phpbb_root_path . 'includes/captcha/captcha_gd_wave.' . $phpEx);
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
		return @extension_loaded('gd');
	}

	static function get_name()
	{
		return 'CAPTCHA_GD_3D';
	}

	static function get_class_name()
	{
		return 'phpbb_captcha_gd_wave';
	}

	function acp_page($id, &$module)
	{
		global $config, $db, $template, $user;

		trigger_error($user->lang['CAPTCHA_NO_OPTIONS'] . adm_back_link($module->u_action));
	}
}
