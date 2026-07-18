<?php
/**
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_gallery_config
{
	static public function get($key)
	{
		if (self::$loaded === false)
		{
			self::load();
		}

		return self::$config[$key] ?? null;
	}

	static public function get_array()
	{
		if (self::$loaded === false)
		{
			self::load();
		}

		return self::$config;
	}

	static public function get_default()
	{
		if (self::$loaded === false)
		{
			self::load();
		}

		return self::$default_config;
	}

	static public function set($config_name, $config_value)
	{
		if (self::$loaded === false)
		{
			self::load();
		}

		settype($config_value, gettype(self::$default_config[$config_name]));
		self::$config[$config_name] = $config_value;

		if ((gettype(self::$default_config[$config_name]) == 'bool') || (gettype(self::$default_config[$config_name]) == 'boolean'))
		{
			$update_config = (self::$config[$config_name]) ? '1' : '0';
			set_config(self::$prefix . $config_name, $update_config, self::is_dynamic($config_name));
		}
		else
		{
			set_config(self::$prefix . $config_name, self::$config[$config_name], self::is_dynamic($config_name));
		}
	}

	static public function inc($config_name, $increment)
	{
		if (self::$loaded === false)
		{
			self::load();
		}

		if ((gettype(self::$default_config[$config_name]) != 'int') && (gettype(self::$default_config[$config_name]) != 'integer'))
		{
			return false;
		}

		set_config_count(self::$prefix . $config_name, (int) $increment, self::is_dynamic($config_name));
		self::$config[$config_name] += (int) $increment;
		return true;
	}

	static public function dec($config_name, $decrement)
	{
		if (self::$loaded === false)
		{
			self::load();
		}

		if ((gettype(self::$default_config[$config_name]) != 'int') && (gettype(self::$default_config[$config_name]) != 'integer'))
		{
			return false;
		}

		set_config_count(self::$prefix . $config_name, 0 - (int) $decrement, self::is_dynamic($config_name));
		self::$config[$config_name] -= (int) $decrement;
		return true;
	}

	static public function is_dynamic($config_name)
	{
		if (self::$loaded === false)
		{
			self::load();
		}

		if (isset(self::$is_dynamic[$config_name]))
		{
			return true;
		}
		return false;
	}

	static public function exists($key)
	{
		if (self::$loaded === false)
		{
			self::load();
		}

		if (self::$loaded === false)
		{
			self::load();
		}

		return !empty(self::$config[$key]);
	}

	/**
	* Adds new configs of the plugin to the database.
	*/
	static public function install()
	{
		self::load_core_values();

		foreach (self::$default_config as $name => $value)
		{
			self::set($name, $value);
		}
	}

	static private $is_dynamic = [];

	static private $default_config = [];

	static private $config = [];

	static private $loaded = false;

	// Prefix which is prepend to the configs before they are stored in the config table.
	static private $prefix = 'phpbb_gallery_';

	static public function load($load_default = false)
	{
		global $config;

		self::load_core_values();

		foreach ($config as $config_name => $config_value)
		{
			// Load all config values of the gallery
			if (strpos($config_name, self::$prefix) === 0)
			{
				$config_name = substr($config_name, strlen(self::$prefix));

				if (!isset(self::$default_config[$config_name]))
				{
					// Ignore values from the table which are not defined properly.
					continue;
				}

				settype($config_value, gettype(self::$default_config[$config_name]));
				self::$config[$config_name] = $config_value;
			}
		}

		if ($load_default)
		{
			// Should we load the default-config?
			self::$config = self::$config + self::$default_config;
		}

		self::$loaded = true;
	}

	static private function load_core_values()
	{
		$class_variables = get_class_vars('phpbb_gallery_config_core');

		foreach ($class_variables['configs'] as $name => $value)
		{
			self::$default_config[$class_variables['prefix'] . $name] = $value;
		}

		foreach ($class_variables['is_dynamic'] as $name)
		{
			self::$is_dynamic[] = $class_variables['prefix'] . $name;
		}
	}
}
