<?php
/**
 * Cookie variables container.
 *
 * @copyright (c) 2012 Evgeny Vrublevsky <veg@tut.by>
 */
class cookie
{
	protected static $ready     = false;
	protected static $vars      = array();
	protected static $prefix    = '';
	protected static $expire    = 0;
	protected static $path      = '/';
	protected static $domain    = null;
	protected static $secure    = false;

	/**
	 * Prepares internal variables
	 */
	static function init($config = array())
	{
		if (self::$ready) return;
		
		self::$prefix    = arr::get($config, 'prefix', '');
		self::$expire    = arr::get($config, 'expire', 0);
		self::$path      = arr::get($config, 'path', '/');
		self::$domain    = request::get_domain(true);
		self::$domain    = arr::get($config, 'domain', self::$domain ? ('.'.self::$domain) : null);
		self::$secure    = arr::get($config, 'secure', request::is_https());

		$cookie = request::sanitize($_COOKIE);
		foreach ($cookie as $name => $val)
		{
			if (self::$prefix)
			{
				if (strpos($name, self::$prefix) !== 0) continue;
				$name = substr($name, strlen(self::$prefix));
			}
			self::$vars[$name] = $val;
		}
		unset($_COOKIE);

		self::$ready = true;
	}

	/**
	 * Sets cookie variable
	 */
	static function set($name, $value, $expire = null, $http_only = false)
	{
		if (headers_sent())
		{
			throw new exception('Headers already sent');
		}

		if ($expire === null)
		{
			// Use the default expiration
			$expire = self::$expire;
		}
		if (is_bool($expire))
		{
			// Use "permanent" or "when the browser closes" expiration
			$expire = $expire ? 0 : 5*365*86400;
		}

		if ($expire !== 0)
		{
			// The expiration is expected to be a UNIX timestamp
			$expire = is_numeric($expire) ? ($expire + time()) : strtotime($expire);
			if (time() > $expire)
			{
				// Cookie already expired
				return self::delete($name);
			}
		}

		self::$vars[$name] = $value;
		return setcookie(self::$prefix.$name, $value, $expire, self::$path, self::$domain, self::$secure, $http_only);
	}

	/**
	 * Gets cookie variable with auto casting
	 */
	static function get($var = null, $default = null)
	{
		return arr::get(self::$vars, $var, $default, true);
	}

	/**
	 * Nullify the cookie and make it expire
	 */
	static function delete($name)
	{
		unset(self::$vars[$name]);
		return setcookie(self::$prefix.$name, null, -86400, self::$path, self::$domain, self::$secure, false);
	}

	/**
	 * Deletes all cookies
	 */
	static function clear()
	{
		foreach (array_keys(self::$vars) as $var)
		{
			self::delete($var);
		}
	}
}
