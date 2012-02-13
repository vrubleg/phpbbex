<?php
class http_exception extends exception {}

class core
{
	protected static $_modules = array();

	static function init()
	{
		set_exception_handler('core::exception_handler');
		// set_error_handler('core::exception_error_handler');
		request::init();
		response::init(array('gzip' => true));
	}

	static function exception_error_handler($errno, $errstr, $file, $line)
	{
		if(error_reporting() & $errno) throw new ErrorException($errstr, 0, $errno, $file, $line);
	}

	static function exception_handler(exception $e)
	{
		if (!headers_sent())
		{
			header('HTTP/1.1 503 Service Unavailable');
			header('content-type: text/html; charset=utf-8');
		}
		echo '<h1>Uncaught Exception</h1>';
		echo '<b>File: </b>' . $e->getFile() . ' (' . $e->getLine() . ')<br>';
		if ($e->getCode()) echo '[' . $e->getCode() . '] ';
		echo '<b>Error: </b>' . $e->getMessage() . '<br>';
		echo nl2br($e->getTraceAsString()) . '<br>';
	}

	static function module($name)
	{
		if (isset(self::$_modules[$name])) return self::$_modules[$name];
		$class = 'module_' . $name;
		if (!autoloader::load($class)) throw new http_exception('Module Not Found', 404);
		self::$_modules[$name] = new $class();
		return self::$_modules[$name];
	}

	static function run()
	{
		try
		{
			$module = core::module(get('module', ''));
			$method = 'action_' . get('action', '');
			if (!method_exists($module, $method)) throw new http_exception('Action Not Found', 404);
			call_user_func(array($module, $method)); // $module->{$method}();
		}
		catch (http_exception $e)
		{
			$message = $e->getMessage();
			$code = $e->getCode() ? $e->getCode() : 500;
			response::status($code);
			echo('<h1>' . $code . ' ' . $message . '</h1>');
		}
	}
}
