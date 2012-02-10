<?php
/**
 * Request variables container.
 *
 * @copyright (c) 2011 Evgeny Vrublevsky <veg@tut.by>
 */
class request
{
	protected static $ready      = false;
	protected static $stack      = array();
	protected static $is_https   = false;
	protected static $method     = '';
	protected static $uri        = '';
	protected static $domain     = '';
	protected static $user_agent = '';
	protected static $user_ip    = '';
	protected static $get_vars   = null;
	protected static $post_vars  = null;
	protected static $uri_vars   = null;
	protected static $vars       = null;
	protected static $route      = null;

	/**
	 * Saves $_POST and $_GET global variables into internal variables
	 */
	static function init($unset_superglobals = true)
	{
		if (self::$ready) return;
		self::unregister_globals();
		$_GET  = self::sanitize($_GET);
		$_POST = self::sanitize($_POST);
		self::$is_https   = (strtolower(arr::get($_SERVER, 'HTTPS')) === 'on');
		self::$method     = arr::get($_SERVER, 'REQUEST_METHOD', '');
		self::$uri        = arr::get($_SERVER, 'REQUEST_URI', '');
		self::$domain     = strtolower(arr::get($_SERVER, 'SERVER_NAME', ''));
		self::$user_agent = arr::get($_SERVER, 'HTTP_USER_AGENT', '');
		self::$user_ip    = arr::get($_SERVER, 'REMOTE_ADDR', '');
		self::$get_vars   = $_GET;
		self::$post_vars  = $_POST;
		self::$ready      = true;
		if ($unset_superglobals)
		{
			unset($_POST);
			unset($_GET);
			unset($_REQUEST);
		}
	}

	/**
	 * Recursively sanitizes an input variable:
	 *
	 * - Strips slashes if magic quotes are enabled
	 * - Normalizes all newlines to LF
	 *
	 * @param   mixed  any variable
	 * @return  mixed  sanitized variable
	 */
	static function sanitize($value)
	{
		if (is_array($value))
		{
			foreach ($value as $key => $val)
			{
				$value[$key] = self::sanitize($val);
			}
		}
		elseif (is_string($value))
		{
			if (get_magic_quotes_gpc())
			{
				$value = stripslashes($value);
			}
			if (strpos($value, "\r") !== false)
			{
				$value = str_replace(array("\r\n", "\r"), "\n", $value);
			}
		}
		return $value;
	}

	/**
	 * Reverse the effects of register_globals.
	 */
	protected static function unregister_globals()
	{
		if (!ini_get('register_globals')) return;
		if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) die('Global variable overload attack detected!');
		$global_vars = array_keys($GLOBALS);
		// Remove the standard global variables from the list
		$global_vars = array_diff($global_vars, array('_COOKIE', '_ENV', '_GET', '_FILES', '_POST', '_REQUEST', '_SERVER', '_SESSION', 'GLOBALS'));
		foreach ($global_vars as $name)
		{
			unset($GLOBALS[$name]);
		}
	}

	/**
	 * Pushes new URI for request environment.
	 *
	 *     request::push('/path/to/doc?arg1=test');
	 *     request::push('route_name', array('arg1' => 'test'));
	 *
	 * @param   $uri     URI or route name
	 * @param   $args    arguments for route::format
	 */
	static function push($uri, $args = null)
	{
		self::$stack[] = array(
			'method'    => self::$method,
			'uri'       => self::$uri,
			'get_vars'  => self::$get_vars,
			'post_vars' => self::$post_vars,
			'uri_vars'  => self::$uri_vars,
			'vars'      => self::$vars,
			'route'     => self::$route,
		);

		// Format url from route name ($uri) and $args
		if ($args !== null)
		{
			if (!class_exists('route', false)) throw new exception('The route class is not initialized');
			$uri = route::format($uri, $args);
		}

		// Reset internal vars
		self::$method    = 'GET';
		self::$uri       = $uri;
		self::$get_vars  = array();
		self::$post_vars = array();
		self::$uri_vars  = null;
		self::$vars      = null;
		self::$route     = null;

		// Parse GET parameters
		$path_length = strcspn(self::$uri, '?');
		if ($path_length < strlen(self::$uri))
		{
			$query = substr(self::$uri, $path_length + 1);
			parse_str($query, self::$get_vars);
		}
	}

	/**
	 * Restores request class to last state.
	 */
	static function pop()
	{
		$data = array_pop(self::$stack);
		self::$method    = $data['method'];
		self::$uri       = $data['uri'];
		self::$get_vars  = $data['get_vars'];
		self::$post_vars = $data['post_vars'];
		self::$uri_vars  = $data['uri_vars'];
		self::$vars      = $data['vars'];
		self::$route     = $data['route'];
	}

	/**
	 * Returns current URI.
	 */
	static function get_uri()
	{
		return self::$uri;
	}

	/**
	 * Returns request method (POST/GET/HEAD).
	 */
	static function get_method()
	{
		return self::$method;
	}

	/**
	 * Returns user agent.
	 */
	static function get_user_agent()
	{
		return self::$user_agent;
	}

	/**
	 * Returns user IP.
	 */
	static function get_user_ip()
	{
		return self::$user_ip;
	}

	/**
	 * Returns true if HTTPS.
	 */
	static function is_https()
	{
		return self::$is_https;
	}

	/**
	 * Returns domain name.
	 */
	static function get_domain($trim_www = false)
	{
		return ($trim_www) ? preg_replace('#^www\.#', '', self::$domain) : self::$domain;
	}

	/**
	 * Returns name of matched route.
	 */
	static function route()
	{
		if (!empty(self::$route)) return self::$route;
		if (class_exists('route', false))
		{
			self::$uri_vars = route::match(self::$uri);
			self::$route = route::matched();
		}
		else
		{
			self::$uri_vars = array();
			self::$route = false;
		}
		self::$vars = array_merge(self::$post_vars, self::$get_vars, empty(self::$uri_vars) ? array() : self::$uri_vars);
		return self::$route;
	}

	/**
	 * Gets request variable with auto casting
	 */
	static function get($var = null, $default = null)
	{
		if (self::$vars === null) self::route();
		return arr::get(self::$vars, $var, $default, true);
	}
}

function get($var = null, $default = null)
{
	return request::get($var, $default);
}
