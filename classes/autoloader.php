<?php
/**
 * Simple autoloader.
 *
 * @copyright (c) 2011 Evgeny Vrublevsky <veg@tut.by>
 */
class autoloader
{
	protected static $pathes = array();
	protected static $registered = false;

	static function init($path = '')
	{
		autoloader::register();
		if(!empty($path)) autoloader::add_path($path);
	}

	static function add_path($path, $prefix = '')
	{
		$prefix = strtolower($prefix);
		if(!isset(autoloader::$pathes[$prefix]))
		{
			autoloader::$pathes[$prefix] = array();
		}
		$path = str_replace('\\', '/', $path);
		if($path{strlen($path)-1} !== '/') $path .= '/';
		autoloader::$pathes[$prefix][] = $path;
	}

	static function register()
	{
		if (autoloader::$registered) return;
		if (!spl_autoload_register(array('autoloader','load')))
		{
			throw new Exception('Could not register autoload function');
		}
		autoloader::$registered = true;
	}

	static function unregister()
	{
		if (!autoloader::$registered) return;
		if (!spl_autoload_unregister(array('autoloader','load')))
		{
			throw new Exception('Could not unregister autoload function');
		}
		autoloader::$registered = false;
	}

	static function load($class)
	{
		if (class_exists($class, false)) return true;
		$parts = explode('_', $class);
		// Start searching without prefix
		$prefix = '';
		while(count($parts) > 0)
		{
			if(isset(autoloader::$pathes[$prefix]))
			{
				$filename = implode('_', $parts) . '.php';
				$filename = strtolower($filename);
				foreach (autoloader::$pathes[$prefix] as $path)
				{
					$file = $path . $filename;
					if (file_exists($file))
					{
						include_once($file);
						return true;
					}
				}
			}
			// Trying with prefix
			$prefix = empty($prefix)
				? array_shift($parts)
				: ($prefix.'_'.array_shift($parts));
			$prefix = strtolower($prefix);
		}
		return false;
	}
}
