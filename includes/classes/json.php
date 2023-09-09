<?php
/**
 * JSON encode/decode
 *
 * @copyright  (c) 2011 Evgeny Vrublevsky <veg@tut.by>
 */

class json_exception extends exception {}

class json
{
	static function encode($data)
	{
		return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	static function decode($json)
	{
		$result = json_decode($json, true);
		$last_error = json_last_error();
		switch ($last_error)
		{
			case JSON_ERROR_NONE:  break;
			case JSON_ERROR_DEPTH: throw new json_exception('Maximum JSON depth has been exceeded', JSON_ERROR_DEPTH);
			default:               throw new json_exception('Invalid JSON', $last_error);
		}
		return $result;
	}
}
