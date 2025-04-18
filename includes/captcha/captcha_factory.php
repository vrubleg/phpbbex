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
* A small class for 3.0.x (no autoloader in 3.0.x)
*/
class phpbb_captcha_factory
{
	/**
	* return an instance of class $name in file $name_plugin.php
	*/
	static function get_instance($name)
	{
		$name = basename($name);
		if (!class_exists($name))
		{
			require_once(PHPBB_ROOT_PATH . "includes/captcha/plugins/{$name}_plugin.php");
		}
		return call_user_func(array($name, 'get_instance'));
	}

	/**
	* Call the garbage collector
	*/
	static function garbage_collect($name)
	{
		$name = basename($name);
		if (!class_exists($name))
		{
			require_once(PHPBB_ROOT_PATH . "includes/captcha/plugins/{$name}_plugin.php");
		}
		call_user_func(array($name, 'garbage_collect'), 0);
	}

	/**
	* return a list of all discovered CAPTCHA plugins
	*/
	static function get_captcha_types()
	{
		$captchas = array(
			'available'		=> array(),
			'unavailable'	=> array(),
		);

		$dp = @opendir(PHPBB_ROOT_PATH . 'includes/captcha/plugins');

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ((preg_match('#_plugin\.php' . '$#', $file)))
				{
					$name = preg_replace('#^(.*?)_plugin\.php' . '$#', '\1', $file);
					if (!class_exists($name))
					{
						require_once(PHPBB_ROOT_PATH . "includes/captcha/plugins/$file");
					}

					if (call_user_func(array($name, 'is_available')))
					{
						$captchas['available'][$name] = call_user_func(array($name, 'get_name'));
					}
					else
					{
						$captchas['unavailable'][$name] = call_user_func(array($name, 'get_name'));
					}
				}
			}
			closedir($dp);
		}

		return $captchas;
	}
}
