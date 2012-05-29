<?php
/**
 * Routes are used to determine parameters for a requested URI.
 * Routes also provide a way to generate URIs (called "reverse routing"), which
 * makes them an extremely powerful and flexible way to generate internal links.
 *
 *   Route example:
 *     /demo/:cat_id[](/:cat_id[])?column&order&page
 *
 *   TODO: Extended syntax
 *     GET //{:subdomain}.* /(catalog|ctlg)!/({:cat_id[]}/)+
 *
 * @copyright (c) 2011 Evgeny Vrublevsky <veg@tut.by>
 */
class route
{
	static protected $routes    = array();
	static protected $matched   = false;
	static protected $prefix    = '';

	/**
	 * Sets prefix for all routes
	 */
	static function init($prefix = '')
	{
		self::$prefix = $prefix;
	}

	/**
	 * Adds new route
	 */
	static function add($name, $route, $defaults = array(), $var_regex = null)
	{
		self::$routes[$name] = new route(self::$prefix . $route, $defaults, $var_regex);
	}

	/**
	 * Returns count of routes
	 */
	static function count()
	{
		return count(self::$routes);
	}

	/**
	 * Match given URI
	 */
	static function match($uri)
	{
		$path = urldecode(substr($uri, 0, strcspn($uri, '?')));
		foreach (self::$routes as $name => $route)
		{
			$matches = $route->_match($path);
			if ($matches !== false)
			{
				self::$matched = $name;
				return $matches;
			}
		}
		self::$matched = false;
		return false;
	}

	/**
	 * Returns matched rule name or false if not
	 */
	static function matched()
	{
		return self::$matched;
	}

	/**
	 * Builds url for given route
	 */
	static function format($name, array $vars = array())
	{
		if (!isset(self::$routes[$name]))
		{
			throw new exception('Unknown route: ' . $name);
		}
		return self::$routes[$name]->_format($vars);
	}

	protected $rule       = '';
	protected $defaults   = array();
	protected $var_regex  = null;
	protected $normalized = false;
	protected $is_static  = false;
	protected $static     = '';
	protected $compiled   = '';
	protected $args       = array();

	protected function __construct($rule, $defaults = null, $var_regex = null)
	{
		if (empty($rule) || $rule[0] != '/') throw new exception("Invalid route '{$rule}'");
		$path_length = strcspn($rule, '?');
		if ($path_length < strlen($rule))
		{
			$args = substr($rule, $path_length + 1);
			$this->args = explode('&', $args);
			$rule = substr($rule, 0, $path_length);
		}
		$static_length = strcspn($rule, ':(){}');
		$this->rule      = $rule;
		$this->defaults  = $defaults ? $defaults : array();
		$this->var_regex = $var_regex;
		$this->is_static = ($static_length === strlen($rule));
		$this->static    = ($this->is_static) ? $rule : substr($rule, 0, $static_length);
	}

	/**
	 * Tests if the route matches a given URI. A successful match will return
	 * all of the routed parameters as an array. A failed match will return
	 * boolean FALSE.
	 */
	protected function _match($path, array $values = array())
	{
		// Check required query parameters
		if (!empty($this->args))
		{
			if (empty($values)) return false;
			foreach ($this->args as $arg)
			{
				if (!isset($values[$arg])) return false;
				$var_regex = is_array($this->var_regex)
					? (isset($this->var_regex[$arg]) ? $this->var_regex[$arg] : null)
					: $this->var_regex;
				if (!empty($var_regex) && !preg_match('#^'.$var_regex.'$#uD', $values[$arg])) return false;
			}
		}

		// Check static prefix
		if ($this->is_static)
		{
			return ($path == $this->rule)
				? array_merge($values, $this->defaults)
				: false;
		}
		if (strpos($path, $this->static) !== 0) return false;

		// Check compiled regexp
		$this->_compile();
		if (!preg_match($this->compiled, $path, $matches)) return false;

		// Parse results
		$vars = array();
		foreach ($matches as $name => $value)
		{
			if (is_numeric($name) || $value === '') continue;
			list($key, $name) = explode('___', $name);
			if ($key === '')
			{
				// Regular placeholder
				$vars[$name] = $value;
			}
			else
			{
				// Array placeholder
				if (!isset($vars[$name]))
				{
					$vars[$name] = array();
				}
				$vars[$name][$key] = $value;
			}
		}
		return array_merge($values, $this->defaults, $vars);
	}

	protected function _compile()
	{
		if ($this->compiled !== '') return;
		$this->_normalize();
		// Instead of preg_quote, does not escape symbols ():
		$expression = preg_replace('#[-<>.\\+*?[^\\]${}=!|\\#]#', '\\\\$0', $this->rule);
		// Make optional parts of the URI non-capturing and optional
		$expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
		$def_arg_regex = (is_string($this->var_regex)) ? $this->var_regex : '[^/]+';
		$expression = preg_replace(
			'#\\\\{:([\w\d_]+)(?:\\\\\\[([\w\d_]*)\\\\\\])?\\\\}#',
			'(?P<${2}___${1}>' . $def_arg_regex . ')',
			$expression
		);
		if (is_array($this->var_regex))
		{
			// Replace the default regex with the user-specified regex
			$search = $replace = array();
			foreach ($this->var_regex as $key => $new_arg_regex)
			{
				$search[]  = "___{$key}>{$def_arg_regex}";
				$replace[] = "___{$key}>{$new_arg_regex}";
			}
			$expression = str_replace($search, $replace, $expression);
		}
		$this->compiled = '#^'.$expression.'$#uD';
	}

	protected function _normalize()
	{
		if ($this->normalized) return;
		$this->ph_indexes = $this->ph_used = array();
		$this->rule = preg_replace_callback(
			'#({)?:(?P<name>[\w\d_]+)(?:\[(?P<key>[\w\d_]*)\])?(?(1)})#',
			array($this,'_normalize_ph'),
			$this->rule
		);
		unset($this->ph_indexes);
		unset($this->ph_used);
		$this->normalized = true;
	}

	protected $ph_indexes;
	protected $ph_used;

	protected function _normalize_ph($ph)
	{
		$name = $ph[2];
		$key = isset($ph[3]) ? $ph[3] : false;
		if ($key === false)
		{
			$ph = '{:'.$name.'}';
		}
		else
		{
			if ($key === '')
			{
				$this->ph_indexes[$name] = isset($this->ph_indexes[$name]) ? ($this->ph_indexes[$name] + 1) : 0;
				$key = $this->ph_indexes[$name];
			}
			else if (is_numeric($key) && (!isset($this->ph_indexes[$name]) || $key > $this->ph_indexes[$name]))
			{
				$this->ph_indexes[$name] = $key;
			}
			$ph = '{:'.$name.'['.$key.']}';
		}
		if (isset($this->ph_used[$ph]))
		{
			throw new exception('Duplicate placeholders are not allowed: ' . $ph);
		}
		else
		{
			$this->ph_used[$ph] = true;
		}
		return $ph;
	}

	/**
	 * Generates a URI for the current route based on the parameters given.
	 */
	protected function _format(array $vars = array())
	{
		// Remove variables that are identical to the default values
		foreach ($vars as $arg => $value)
		{
			if (isset($this->defaults[$arg]) && $this->defaults[$arg] == $value)
			{
				unset($vars[$arg]);
			}
		}

		// Check required query parameters
		if (!empty($this->args))
		{
			foreach ($this->args as $arg)
			{
				if (isset($vars[$arg])) continue;
				if (isset($this->defaults[$arg]))
				{
					$vars[$arg] = $this->defaults[$arg];
				}
				else
				{
					throw new exception('Required query parameters not passed');
				}
			}
		}

		$this->_normalize();
		$uri = preg_replace('/[^\x00-\x7F]+/e', 'urlencode("$0")', $this->rule);
		if ($this->is_static)
		{
			// This is a static route, no need to replace anything
			return $uri . $this->build_query($vars);
		}

		if (strpos($uri, '(') !== false)
		{
			while (preg_match('#\([^()]++\)#', $uri, $match))
			{
				// Replace the group in the URI
				$replace = $this->_format_part(substr($match[0], 1, -1), $vars);
				$uri = str_replace($match[0], $replace, $uri);
			}
		}
		$uri = $this->_format_part($uri, $vars, true);
		$uri = str_replace('#', '', $uri);
		if ($uri === '')
		{
			throw new exception('Required route parameters not passed');
		}
		return $uri . $this->build_query($vars);
	}

	protected function build_query($vars)
	{
		ksort($vars);
		$query = str_ireplace(array('%5B', '%5D'), array('[', ']'), http_build_query($vars));
		return (empty($query)) ? '' : ('?' . $query);
	}

	protected function _format_part($part, &$vars_ref, $required = false)
	{
		$vars = $vars_ref;
		$required = ($required || strpos($part, '#') !== false);
		while (preg_match('#{:(?P<name>[\w\d_]+)(?:\[(?P<key>[\w\d_]*)\])?}#', $part, $match))
		{
			$placeholder = $match[0];
			$name = $match['name'];
			$key = isset($match['key']) ? $match['key'] : false;
			$value = null;
			$need_unset = false;

			if (isset($vars[$name]))
			{
				$value = $vars[$name];
				$required = true;
				$need_unset = true;
			}
			else if (isset($this->defaults[$name]))
			{
				$value = $this->defaults[$name];
			}

			if ($key === false)
			{
				if ($need_unset) unset($vars[$name]);
			}
			else
			{
				$value = (is_array($value) && isset($value[$key])) ? $value[$key] : null;
				if ($value !== null && $need_unset)
				{
					unset($vars[$name][$key]);
				}
			}

			$value = ($value === false) ? '0' : strval($value);
			if ($value !== '')
			{
				// Replace the placeholder with the parameter value
				$part = str_replace($placeholder, '#'.urlencode($value).'#', $part);
			}
			else
			{
				// This group has missing parameters
				return '';
			}
		}
		if ($required)
		{
			$vars_ref = $vars;
			return $part;
		}
		else
		{
			return '';
		}
	}
}

function url($name, array $vars = array())
{
	return route::format($name, $vars);
}
