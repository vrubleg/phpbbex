<?php
/**
 * Provides multi-byte aware replacement string functions.
 *
 * Requirements:
 * - PCRE needs to be compiled with UTF-8 support (--enable-utf8)
 * - The [iconv extension](http://php.net/iconv) is loaded
 * - The [mbstring extension](http://php.net/mbstring)
 *
 * @copyright  (c) 2005 Harry Fuecks <hfuecks@gmail.com>
 * @copyright  (c) 2011 Evgeny Vrublevsky <veg@tut.by>
 */
class str
{
	// Constants for pad
	const left = STR_PAD_LEFT;
	const right = STR_PAD_RIGHT;
	const both = STR_PAD_BOTH;

	// Constants for charcase
	const lower = -1;
	const none = 0;
	const upper = 1;

	/**
	 * Recursively cleans arrays, objects, and strings. Removes ASCII control
	 * codes and converts to the requested charset while silently discarding
	 * incompatible characters.
	 *
	 *     str::clean($_GET); // Clean GET data
	 *
	 * @param   mixed   variable to clean
	 * @return  mixed
	 */
	public static function clean($var)
	{
		if (is_array($var) || is_object($var))
		{
			foreach ($var as $key => $val)
			{
				$var[self::clean($key)] = self::clean($val);
			}
		}
		elseif (is_string($var) && $var !== '')
		{
			// Remove control characters
			$var = self::strip_ascii_ctrl($var);
			if ( ! self::is_ascii($var))
			{
				$error_reporting = error_reporting(~E_NOTICE);
				// iconv is expensive, so it is only used when needed
				$var = iconv('utf-8', 'utf-8//IGNORE', $var);
				error_reporting($error_reporting);
			}
		}
		return $var;
	}

	/**
	 * Tests whether a string contains only 7-bit ASCII bytes. This is used to
	 * determine when to use native functions or UTF-8 functions.
	 *
	 *     $is_ascii = str::is_ascii($str);
	 *
	 * @param   mixed    string or array of strings to check
	 * @return  boolean
	 */
	public static function is_ascii($str)
	{
		if (is_array($str))
		{
			$str = implode($str);
		}
		return ! preg_match('/[^\x00-\x7F]/S', $str);
	}

	/**
	 * Strips out device control codes in the ASCII range.
	 *
	 *     $str = str::strip_ascii_ctrl($str);
	 *
	 * @param   string  string to clean
	 * @return  string
	 */
	public static function strip_ascii_ctrl($str)
	{
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
	}

	/**
	 * Strips out all non-7bit ASCII bytes.
	 *
	 *     $str = str::strip_non_ascii($str);
	 *
	 * @param   string  string to clean
	 * @return  string
	 */
	public static function strip_non_ascii($str)
	{
		return preg_replace('/[^\x00-\x7F]+/S', '', $str);
	}

	/**
	 * Returns the length of the given string. This is a UTF8-aware version
	 * of [strlen](http://php.net/strlen).
	 *
	 *     $length = str::length($str);
	 *
	 * @param   string   string being measured for length
	 * @return  integer
	 */
	public static function length($str)
	{
		return mb_strlen($str, 'utf-8');
	}

	/**
	 * Returns part of a UTF-8 string. This is a UTF8-aware version
	 * of [substr](http://php.net/substr).
	 *
	 *     $sub = str::substr($str, $offset);
	 *
	 * @param   string   input string
	 * @param   integer  offset
	 * @param   integer  length limit
	 * @return  string
	 */
	public static function substr($str, $offset, $length = null)
	{
		return ($length === null)
			? mb_substr($str, $offset, mb_strlen($str), 'utf-8')
			: mb_substr($str, $offset, $length, 'utf-8');
	}

	/**
	 * Pads a UTF-8 string to a certain length with another string. This is a
	 * UTF8-aware version of [str_pad](http://php.net/str_pad).
	 *
	 *     $str = str::pad($str, $length);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   input string
	 * @param   integer  desired string length after padding
	 * @param   string   string to use as padding
	 * @param   string   padding type: str::right, str::left or str::both
	 * @return  string
	 */
	public static function pad($str, $final_str_length, $pad_str = ' ', $pad_type = str::right)
	{
		if (str::is_ascii($str) && str::is_ascii($pad_str))
			return str_pad($str, $final_str_length, $pad_str, $pad_type);

		$str_length = str::length($str);

		if ($final_str_length <= 0 || $final_str_length <= $str_length)
			return $str;

		$pad_str_length = str::length($pad_str);
		$pad_length = $final_str_length - $str_length;

		if ($pad_type == str::right)
		{
			$repeat = ceil($pad_length / $pad_str_length);
			return str::substr($str.str_repeat($pad_str, $repeat), 0, $final_str_length);
		}

		if ($pad_type == str::left)
		{
			$repeat = ceil($pad_length / $pad_str_length);
			return str::substr(str_repeat($pad_str, $repeat), 0, floor($pad_length)).$str;
		}

		if ($pad_type == str::both)
		{
			$pad_length /= 2;
			$pad_length_left = floor($pad_length);
			$pad_length_right = ceil($pad_length);
			$repeat_left = ceil($pad_length_left / $pad_str_length);
			$repeat_right = ceil($pad_length_right / $pad_str_length);

			$pad_left = str::substr(str_repeat($pad_str, $repeat_left), 0, $pad_length_left);
			$pad_right = str::substr(str_repeat($pad_str, $repeat_right), 0, $pad_length_right);
			return $pad_left.$str.$pad_right;
		}

		trigger_error('str::pad: Unknown padding type ('.$pad_type.')', E_USER_ERROR);
	}

	/**
	 * Replaces special/accented UTF-8 characters by ASCII-7 "equivalents".
	 *
	 *     $ascii = str::transliterate($utf8);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @author  Evgeny Vrublevsky <veg@tut.by>
	 * @param   string   string to transliterate
	 * @return  string
	 */
	public static function transliterate($str)
	{
		$replace_pairs = array(
			// Upper accents
			'À' => 'A',  'Ô' => 'O',  'Ď' => 'D',  'Ḟ' => 'F',  'Ë' => 'E',  'Š' => 'S',  'Ơ' => 'O',
			'Ă' => 'A',  'Ř' => 'R',  'Ț' => 'T',  'Ň' => 'N',  'Ā' => 'A',  'Ķ' => 'K',  'Ĕ' => 'E',
			'Ŝ' => 'S',  'Ỳ' => 'Y',  'Ņ' => 'N',  'Ĺ' => 'L',  'Ħ' => 'H',  'Ṗ' => 'P',  'Ó' => 'O',
			'Ú' => 'U',  'Ě' => 'E',  'É' => 'E',  'Ç' => 'C',  'Ẁ' => 'W',  'Ċ' => 'C',  'Õ' => 'O',
			'Ṡ' => 'S',  'Ø' => 'O',  'Ģ' => 'G',  'Ŧ' => 'T',  'Ș' => 'S',  'Ė' => 'E',  'Ĉ' => 'C',
			'Ś' => 'S',  'Î' => 'I',  'Ű' => 'U',  'Ć' => 'C',  'Ę' => 'E',  'Ŵ' => 'W',  'Ṫ' => 'T',
			'Ū' => 'U',  'Č' => 'C',  'Ö' => 'O',  'È' => 'E',  'Ŷ' => 'Y',  'Ą' => 'A',  'Ł' => 'L',
			'Ų' => 'U',  'Ů' => 'U',  'Ş' => 'S',  'Ğ' => 'G',  'Ļ' => 'L',  'Ƒ' => 'F',  'Ž' => 'Z',
			'Ẃ' => 'W',  'Ḃ' => 'B',  'Å' => 'A',  'Ì' => 'I',  'Ï' => 'I',  'Ḋ' => 'D',  'Ť' => 'T',
			'Ŗ' => 'R',  'Ä' => 'A',  'Í' => 'I',  'Ŕ' => 'R',  'Ê' => 'E',  'Ü' => 'U',  'Ò' => 'O',
			'Ē' => 'E',  'Ñ' => 'N',  'Ń' => 'N',  'Ĥ' => 'H',  'Ĝ' => 'G',  'Đ' => 'D',  'Ĵ' => 'J',
			'Ÿ' => 'Y',  'Ũ' => 'U',  'Ŭ' => 'U',  'Ư' => 'U',  'Ţ' => 'T',  'Ý' => 'Y',  'Ő' => 'O',
			'Â' => 'A',  'Ľ' => 'L',  'Ẅ' => 'W',  'Ż' => 'Z',  'Ī' => 'I',  'Ã' => 'A',  'Ġ' => 'G',
			'Ṁ' => 'M',  'Ō' => 'O',  'Ĩ' => 'I',  'Ù' => 'U',  'Į' => 'I',  'Ź' => 'Z',  'Á' => 'A',
			'Û' => 'U',  'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae', 'İ' => 'I',
			// Lower accents
			'à' => 'a',  'ô' => 'o',  'ď' => 'd',  'ḟ' => 'f',  'ë' => 'e',  'š' => 's',  'ơ' => 'o',
			'ß' => 'ss', 'ă' => 'a',  'ř' => 'r',  'ț' => 't',  'ň' => 'n',  'ā' => 'a',  'ķ' => 'k',
			'ŝ' => 's',  'ỳ' => 'y',  'ņ' => 'n',  'ĺ' => 'l',  'ħ' => 'h',  'ṗ' => 'p',  'ó' => 'o',
			'ú' => 'u',  'ě' => 'e',  'é' => 'e',  'ç' => 'c',  'ẁ' => 'w',  'ċ' => 'c',  'õ' => 'o',
			'ṡ' => 's',  'ø' => 'o',  'ģ' => 'g',  'ŧ' => 't',  'ș' => 's',  'ė' => 'e',  'ĉ' => 'c',
			'ś' => 's',  'î' => 'i',  'ű' => 'u',  'ć' => 'c',  'ę' => 'e',  'ŵ' => 'w',  'ṫ' => 't',
			'ū' => 'u',  'č' => 'c',  'ö' => 'o',  'è' => 'e',  'ŷ' => 'y',  'ą' => 'a',  'ł' => 'l',
			'ų' => 'u',  'ů' => 'u',  'ş' => 's',  'ğ' => 'g',  'ļ' => 'l',  'ƒ' => 'f',  'ž' => 'z',
			'ẃ' => 'w',  'ḃ' => 'b',  'å' => 'a',  'ì' => 'i',  'ï' => 'i',  'ḋ' => 'd',  'ť' => 't',
			'ŗ' => 'r',  'ä' => 'a',  'í' => 'i',  'ŕ' => 'r',  'ê' => 'e',  'ü' => 'u',  'ò' => 'o',
			'ē' => 'e',  'ñ' => 'n',  'ń' => 'n',  'ĥ' => 'h',  'ĝ' => 'g',  'đ' => 'd',  'ĵ' => 'j',
			'ÿ' => 'y',  'ũ' => 'u',  'ŭ' => 'u',  'ư' => 'u',  'ţ' => 't',  'ý' => 'y',  'ő' => 'o',
			'â' => 'a',  'ľ' => 'l',  'ẅ' => 'w',  'ż' => 'z',  'ī' => 'i',  'ã' => 'a',  'ġ' => 'g',
			'ṁ' => 'm',  'ō' => 'o',  'ĩ' => 'i',  'ù' => 'u',  'į' => 'i',  'ź' => 'z',  'á' => 'a',
			'û' => 'u',  'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u',  'ĕ' => 'e',  'ı' => 'i',
			// Upper cyrillic symbols
			'А' => 'A',  'Б' => 'B',  'В' => 'V',  'Г' => 'G',  'Д' => 'D',  'Е' => 'E',  'Ж' => 'Zh',
			'З' => 'Z',  'И' => 'I',  'Й' => 'Y',  'К' => 'K',  'Л' => 'L',  'М' => 'M',  'Н' => 'N',
			'О' => 'O',  'П' => 'P',  'Р' => 'R',  'С' => 'S',  'Т' => 'T',  'У' => 'U',  'Ф' => 'F',
			'Х' => 'H',  'Ц' => 'C',  'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch','Ъ' => "'",  'Ы' => 'Y',
			'Ь' => '',   'Э' => 'E',  'Ю' => 'Yu', 'Я' => 'Ya',	'Ґ' => 'G',  'Ё' => 'Yo', 'Є' => 'E',
			'Ї' => 'Yi', 'І' => 'I',
			// Lower cyrillic symbols
			'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g',  'д' => 'd',  'е' => 'e',  'ж' => 'zh',
			'з' => 'z',  'и' => 'i',  'й' => 'y',  'к' => 'k',  'л' => 'l',  'м' => 'm',  'н' => 'n',
			'о' => 'o',  'п' => 'p',  'р' => 'r',  'с' => 's',  'т' => 't',  'у' => 'u',  'ф' => 'f',
			'х' => 'h',  'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch','ъ' => "'",  'ы' => 'y',
			'ь' => '',   'э' => 'e',  'ю' => 'yu', 'я' => 'ya', 'ґ' => 'g',  'ё' => 'yo', 'є' => 'e',
			'ї' => 'yi', 'і' => 'i',
			// Other symbols
			'№' => 'N',  '€' => 'E',
		);
		return strtr($str, $replace_pairs);
	}

	/**
	 * Generates identifier from string.
	 *
	 *     $ascii = str::symbolize($utf8);
	 *
	 * @author  Evgeny Vrublevsky <veg@tut.by>
	 * @param   string   string to symbolize
	 * @param   string   separator
	 * @param   string   char case: str::upper, str::lower or str::none
	 * @return  string
	 */
	public static function symbolize($str, $separator = '_', $case = str::lower)
	{
		$identifier = str::transliterate($str);
		$identifier = preg_replace("/[^a-z0-9{$separator}]/i", $separator, $identifier);
		$identifier = preg_replace("/[{$separator}]+/", $separator, $identifier);
		$identifier = str::trim($identifier, $separator);
		switch ($case)
		{
			case str::lower:
				$identifier = str::lowercase($identifier);
			break;
			case str::upper:
				$identifier = str::uppercase($identifier);
			break;
		}
		return $identifier;
	}

	/**
	 * Makes a UTF-8 string lowercase. This is a UTF8-aware version
	 * of [strtolower](http://php.net/strtolower).
	 *
	 *     $str = str::lowercase($str);
	 *
	 * @param   string   mixed case string
	 * @return  string
	 */
	public static function lowercase($str)
	{
		return mb_strtolower($str, 'utf-8');
	}

	/**
	 * Makes a UTF-8 string uppercase. This is a UTF8-aware version
	 * of [strtoupper](http://php.net/strtoupper).
	 *
	 * @param   string   mixed case string
	 * @return  string
	 */
	public static function uppercase($str)
	{
		return mb_strtoupper($str, 'utf-8');
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the beginning and
	 * end of a string. This is a UTF8-aware version of [trim](http://php.net/trim).
	 *
	 *     $str = str::trim($str);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @author  Evgeny Vrublevsky <veg@tut.by>
	 * @param   string   input string
	 * @param   string   string of characters to remove, null - all spacers
	 * @param   string   trim type: str::right, str::left or str::both
	 * @return  string
	 */
	public static function trim($str, $charlist = null, $trim_type = str::both)
	{
		switch ($trim_type)
		{
			case str::left:
				if ($charlist === null)
					return ltrim($str);
				if (str::is_ascii($charlist))
					return ltrim($str, $charlist);
			break;
			case str::right:
				if ($charlist === null)
					return rtrim($str);
				if (str::is_ascii($charlist))
					return rtrim($str, $charlist);
			break;
			case str::both:
				if ($charlist === null)
					return trim($str);
				if (str::is_ascii($charlist))
					return trim($str, $charlist);
			break;
		}

		$charlist = preg_replace('#[-\[\]:\\\\^/]#', '\\\\$0', $charlist);
		if ($trim_type == str::both || $trim_type == str::left)
		{
			$str = preg_replace('/^['.$charlist.']+/u', '', $str);
		}
		if ($trim_type == str::both || $trim_type == str::right)
		{
			$str = preg_replace('/['.$charlist.']++$/uD', '', $str);
		}

		return $str;
	}

	/**
	 * Generates unique HEX id
	 *
	 *     $id = str::uniqid();
	 *
	 * @author  Evgeny Vrublevsky <veg@tut.by>
	 * @param   integer  unique id length (up to 40 symbols)
	 * @return  string
	 */
	public static function uniqid($length = 32)
	{
		return substr(sha1(uniqid('trah-tibidoh', true)), 0, $length);
	}
}
