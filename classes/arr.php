<?php
/**
 * Useful tools for arrays.
 *
 * @copyright (c) 2011 Evgeny Vrublevsky <veg@tut.by>
 */
class arr
{
	/**
	 * Gets value from array and casts it to type of default value.
	 *
	 *     $name  = arr::get($array, 'name');
	 *     $flag  = arr::get($array, 'flag', false);
	 *     $flags = arr::get($array, array('flag1', 'flag2'), false);
	 *     $vars  = arr::get($array, array('flag', 'user_id' => 0), false);
	 *     $vars  = arr::get($array, array('flag' => false, 'user_id' => 0));
	 *
	 * @param   $array   source array
	 * @param   $key     key name
	 * @param   $default default value (and pattern for type cast)
	 * @return  mixed
	 */
	function get(&$array, $index = null, $default = null, $autocast = false)
	{
		// Called without arguments, return all variables
		if (is_null($index))
		{
			return $array;
		}

		// Called for array of variables
		if (is_array($index))
		{
			$result = array();
			foreach ($index as $key => $value)
			{
				if (is_int($key))
				{
					$result[$value] = self::get($array, $value, $default, $autocast);
				}
				else
				{
					$result[$key] = self::get($array, $key, $value, $autocast);
				}
			}
			return $result;
		}

		// Normal call
		$value = isset($array[$index]) ? $array[$index] : null;
		if (is_null($value)) return $default;
		if (!$autocast || is_null($default)) return $value;

		// Default value is scalar
		if (!is_array($default))
		{
			if (is_array($value))    $value = end($value);
			if (is_string($default)) return (string)$value;
			if (is_int($default))    return (int)$value;
			if (is_bool($default))   return (bool)$value;
			if (is_float($default))  return (float)$value;
			return $value;
		}

		// Default value is array
		if (!is_array($value)) $value = array($value); // explode(',', $value);
		if (count($default) == 0) return $value;
		reset($default);
		list($key, $item) = each($default);
		$item_type = gettype($item);
		$key_type = gettype($key);

		// Default value is array of arrays
		// if ($item_type == 'array')
		// {
			// reset($item);
			// list($subkey, $subitem) = each($item);
			// $subitem_type = gettype($subitem);
			// $subitem_type = ($subitem_type == 'array') ? 'NULL' : $subitem_type;
			// $subkey_type = gettype($subkey);
		// }

		// Cast value to pattern
		$result = array();
		foreach ($value as $key => $item)
		{
			settype($item, $item_type);
			settype($key, $key_type);
			$result[$key] = $item;
		}
		return $result;
	}

	/**
	 * Tests if an array is associative or not.
	 */
	public static function is_assoc(array $array)
	{
		$keys = array_keys($array);
		// If the array keys of the keys match the keys, then the array must
		// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys($keys) !== $keys;
	}

	/**
	 * Tests if an array is vector or not.
	 */
	static function is_vector(array $array)
	{
		return count(array_diff_key($array, range(0, count($array) - 1))) == 0;
	}
}
