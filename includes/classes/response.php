<?php
/**
 * Response manager.
 *
 * @copyright (c) 2011 Evgeny Vrublevsky <veg@tut.by>
 */
class response
{
	protected static $_ready     = false;
	protected static $_stack     = array();

	/**
	 * Init response
	 */
	static function init($config = array())
	{
		if (self::$_ready) return;

		$type    = arr::get($config, 'type',    'text/html');
		$charset = arr::get($config, 'charset', 'utf-8');
		$gzip    = arr::get($config, 'gzip',    false);
		$buffer  = arr::get($config, 'buffer',  false);

		if ($type)
		{
			self::type($type, $charset);
		}

		if ($gzip && @extension_loaded('zlib') && !headers_sent() && ob_get_level() <= 1 && ob_get_length() == 0)
		{
			if (!ob_start('ob_gzhandler')) ob_start();
		}
		elseif ($buffer)
		{
			ob_start();
		}

		if (isset($config['expire']))
		{
			self::expire($config['expire']);
		}

		self::$_ready = true;
	}

	/**
	 * Start new output buffer
	 */
	static function push()
	{
		ob_start();
	}

	/**
	 * Get last output buffer contents
	 */
	static function pop()
	{
		return ob_get_clean();
	}

	/**
	 * Set HTTP status
	 */
	static function status($status)
	{
		http_response_code($status);
	}

	/**
	 * Set Content-Type header
	 */
	static function type($mime, $charset = 'utf-8')
	{
		self::header('Content-Type', $mime.($charset ? "; charset={$charset}" : ''));
	}

	/**
	 * Set HTTP header
	 */
	static function header($key, $value)
	{
		header($key . ': ' . $value);
	}

	/**
	 * Set caching headers
	 */
	static function expire($expire = 0)
	{
		if ($expire === 0 || $expire === false)
		{
			self::header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
			self::header('Pragma', 'no-cache');
			self::header('Expires', 'Sat, 24 Oct 1987 07:00:00 GMT');
		}
		elseif (is_int($expire))
		{
			self::header('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $expire));
		}
	}

	/**
	 * Set 301/302 redirect headers
	 */
	static function redirect($to, $status = 302)
	{
		self::status($status);
		self::header('Location', $to);
	}
}
