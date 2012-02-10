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

	// HTTP status codes and messages
	protected static $_messages = array(
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',

		// Success 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		// 306 is deprecated but reserved
		307 => 'Temporary Redirect',

		// Client Error 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',

		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	);

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
			self::header('Content-Type', $type.($charset ? "; charset={$charset}" : ''));
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
		header($_SERVER["SERVER_PROTOCOL"] . ' ' . $status . ' ' . self::$_messages[$status]);
		header('Status: ' . $status . ' ' . self::$_messages[$status]);
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
}
