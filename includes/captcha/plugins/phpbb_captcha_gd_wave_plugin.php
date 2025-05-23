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
	require_once(PHPBB_ROOT_PATH . 'includes/captcha/plugins/captcha_abstract.php');
}

class phpbb_captcha_gd_wave extends phpbb_default_captcha
{

	function __construct()
	{
		if (!class_exists('captcha'))
		{
			require_once(PHPBB_ROOT_PATH . 'includes/captcha/captcha_gd_wave.php');
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
