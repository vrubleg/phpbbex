<?php
/**
 * JSON encode/decode
 *
 * @copyright  (c) 2011 Evgeny Vrublevsky <veg@tut.by>
 */

class json_exception extends exception {}

class json
{
	function encode($data)
	{
		if (is_null($data))   return 'null';
		if (is_int($data))    return $data;
		if (is_float($data))  return str_replace(',', '.', (string)$data);
		if (is_bool($data))   return ($data) ? 'true' : 'false';
		if (is_scalar($data)) return '"' . addcslashes($data, "\n\r\t\v\f\"\\") . '"';
		if (is_object($data)) $data = get_object_vars($data);
		$result = array();
		if (arr::is_vector($data))
		{
			foreach ($data as $v) $result[] = json::encode($v);
			return '[' . join(',', $result) . ']';
		}
		else
		{
			foreach ($data as $k => $v) $result[] = json::encode($k).':'.json::encode($v);
			return '{' . join(',', $result) . '}';
		}
	}

	function decode($json)
	{
		$result = json_decode($json, true);
		if (!function_exists('json_last_error'))
		{
			if (!empty($json) && is_null($result) && $json != 'null')
			{
				throw new json_exception('Invalid or malformed JSON');
			}
		}
		else
		{
			// PHP 5.3.3 version
			switch(json_last_error())
			{
				case JSON_ERROR_DEPTH:          throw new json_exception('The maximum stack depth has been exceeded', JSON_ERROR_DEPTH);
				case JSON_ERROR_STATE_MISMATCH: throw new json_exception('Invalid or malformed JSON', JSON_ERROR_STATE_MISMATCH);
				case JSON_ERROR_CTRL_CHAR:      throw new json_exception('Control character error, possibly incorrectly encoded', JSON_ERROR_CTRL_CHAR);
				case JSON_ERROR_SYNTAX:         throw new json_exception('Syntax error, malformed JSON', JSON_ERROR_SYNTAX);
				case JSON_ERROR_UTF8:           throw new json_exception('Malformed UTF-8 characters, possibly incorrectly encoded', JSON_ERROR_UTF8);
			}
		}
		return $result;
	}
}
