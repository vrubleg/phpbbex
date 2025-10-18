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
		$plugins = self::get_classes(['core'], true);

		foreach ($plugins as $plugin_name)
		{
			self::load_class_values($plugin_name);
		}

		foreach (self::$default_config as $name => $value)
		{
			self::set($name, $value);
		}

		self::destroy_plugins_cache();
	}

	/**
	* Call this function, when adding or deleting a plugin!
	*/
	static public function destroy_plugins_cache()
	{
		global $cache;

		$cache->destroy('class_loader');
		$cache->destroy(self::$cache_file);
	}

	static private $is_dynamic = [];

	static private $default_config = [];

	/**
	* Functions for loading the configs from core and plugins
	*/
	static private $config = [];

	static private $loaded = false;

	// Prefix which is prepend to the configs before they are stored in the config table.
	static private $prefix = 'phpbb_gallery_';

	static private $cache_file = '_gallery_config_plugins';

	static public function load($load_default = false)
	{
		global $config, $cache;

		$plugins = self::get_plugins();
		$plugins = self::get_classes($plugins);

		if ($plugins === false)
		{
			$plugins = self::get_plugins(false);
			$plugins = self::get_classes($plugins, true);
		}

		foreach ($plugins as $plugin_name)
		{
			self::load_class_values($plugin_name);
		}

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

	static private function get_plugins($allow_using_cache = true)
	{
		global $cache;

		if (!$allow_using_cache || ($plugins = $cache->get(self::$cache_file)) === false)
		{
			$plugins = self::get_plugins_list();
			$cache->put(self::$cache_file, $plugins);
		}

		return $plugins;
	}

	static private function get_plugins_list()
	{
		$plugins = [];
		$plugins[] = 'core';
		$dir = @opendir(phpbb_gallery_url::_return_file('plugins/', 'phpbb', 'includes/gallery/config/'));

		if ($dir)
		{
			while (($entry = readdir($dir)) !== false)
			{
				if ((substr(strrchr($entry, '.'), 1) == 'php') && (isset($entry[0]) && $entry[0] != '_'))
				{
					$plugins[] = substr(basename($entry), 0, -4);
				}
			}
			closedir($dir);
		}
		return $plugins;
	}

	static private function get_classes($plugins, $display_error = false)
	{
		$classes = [];

		foreach ($plugins as $plugin_name)
		{
			if ($plugin_name == 'core')
			{
				$classes[] = 'phpbb_gallery_config_core';
			}
			elseif (class_exists('phpbb_gallery_config_plugins_' . $plugin_name))
			{
				$classes[] = 'phpbb_gallery_config_plugins_' . $plugin_name;
			}
			elseif ($display_error)
			{
				global $user;

				self::destroy_plugins_cache();
				$user->add_lang('mods/gallery');

				trigger_error($user->lang('PLUGIN_CLASS_MISSING', 'phpbb_gallery_config_plugins_' . $plugin_name));
			}
			else
			{
				// Recieved an error, but we try whether refreshing the cache helps.
				return false;
			}
		}

		return $classes;
	}

	/**
	* Prior to php 5.3 you can not access static variables by $var::$reference
	* So we just use a work around to get them
	*
	static private function load_values($class_name)
	{
		foreach ($class_name::$configs as $name => $value)
		{
			self::$default_config[$class_name::$prefix . $name] = $value;
		}

		foreach ($class_name::$is_dynamic as $name)
		{
			self::$is_dynamic[] = $class_name::$prefix . $name;
		}
	}
	*/
	static private function load_class_values($class_name)
	{
		$class_variables = get_class_vars($class_name);

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
