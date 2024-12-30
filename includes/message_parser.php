<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

require_once($phpbb_root_path . 'includes/bbcode.php');

/**
* BBCODE FIRSTPASS
* BBCODE first pass class (functions for parsing messages for db storage)
*/
class bbcode_firstpass extends bbcode
{
	var $message = '';
	var $warn_msg = array();
	var $parsed_items = array();

	/**
	* Parse BBCode
	*/
	function parse_bbcode()
	{
		if (!$this->bbcodes)
		{
			$this->bbcode_init();
		}

		global $user;

		$this->bbcode_bitfield = '';
		$bitfield = new bitfield();

		foreach ($this->bbcodes as $bbcode_name => $bbcode_data)
		{
			if (isset($bbcode_data['disabled']) && $bbcode_data['disabled'])
			{
				foreach ($bbcode_data['regexp'] as $regexp => $replacement)
				{
					if (preg_match($regexp, $this->message))
					{
						$this->warn_msg[] = sprintf($user->lang['UNAUTHORISED_BBCODE'] , '[' . $bbcode_name . ']');
						continue;
					}
				}
			}
			else
			{
				foreach ($bbcode_data['regexp'] as $regexp => $replacement)
				{
					// The pattern gets compiled and cached by the PCRE extension,
					// it should not demand recompilation
					if (preg_match($regexp, $this->message))
					{
						if (is_callable($replacement))
						{
							$this->message = preg_replace_callback($regexp, $replacement, $this->message);
						}
						else
						{
							$this->message = preg_replace($regexp, $replacement, $this->message);
						}
						$bitfield->set($bbcode_data['bbcode_id']);
					}
				}
			}
		}

		$this->bbcode_bitfield = $bitfield->get_base64();
	}

	/**
	* Prepare some bbcodes for better parsing
	*/
	function prepare_bbcodes()
	{
		// Ok, seems like users instead want the no-parsing of urls, smilies, etc. after and before and within quote tags being tagged as "not a bug".
		// Fine by me ;) Will ease our live... but do not come back and cry at us, we won't hear you.

		/* Add newline at the end and in front of each quote block to prevent parsing errors (urls, smilies, etc.)
		if (strpos($this->message, '[quote') !== false && strpos($this->message, '[/quote]') !== false)
		{
			$this->message = str_replace("\r\n", "\n", $this->message);

			// We strip newlines and spaces after and before quotes in quotes (trimming) and then add exactly one newline
			$this->message = preg_replace('#\[quote(=&quot;.*?&quot;)?\]\s*(.*?)\s*\[/quote\]#siu', '[quote\1]' . "\n" . '\2' ."\n[/quote]", $this->message);
		}
		*/

		// Add other checks which needs to be placed before actually parsing anything (be it bbcodes, smilies, urls...)
	}

	/**
	* Init bbcode data for later parsing
	*/
	function bbcode_init($allow_custom_bbcode = true)
	{
		static $rowset;

		// This array holds all bbcode data. BBCodes will be processed in this
		// order, so it is important to keep [code] in first position and
		// [quote] in second position.
		// To parse multiline URL we enable dotall option setting only for URL text
		// but not for link itself, thus [url][/url] is not affected.
		$this->bbcodes = array(
			'tt'			=> array('bbcode_id' => 14,	'regexp' => array('#\[tt\](.*?)\[/tt\]#uis' => function ($m) { return $this->bbcode_teletype($m[1]); })),
			'code'			=> array('bbcode_id' => 8,	'regexp' => array('#\[code(?:=([a-z]+))?\](.+\[/code\])#uis' => function ($m) { return $this->bbcode_code($m[1], $m[2]); })),
			'quote'			=> array('bbcode_id' => 0,	'regexp' => array('#\[quote(?:=&quot;(.*?)&quot;)?\](.*)\[/quote\]#uis' => function ($m) { return $this->bbcode_quote($m[0], 'quote'); })),
			'spoiler'		=> array('bbcode_id' => 16,	'regexp' => array('#\[spoiler(?:=&quot;(.*?)&quot;)?\](.*)\[/spoiler\]#uis' => function ($m) { return $this->bbcode_quote($m[0], 'spoiler'); })),
			'upd'			=> array('bbcode_id' => 15,	'regexp' => array('#\[upd(?:=([\d]{9,10}|[+]\d+(?:[:]\d+){0,3}))?\](?:(.*?)\[/upd\])?#ui' => function ($m) { return $this->bbcode_upd($m[1], $m[2]); })),
			'attachment'	=> array('bbcode_id' => 12,	'regexp' => array('#\[attachment=([0-9]+)\](.*?)\[/attachment\]#uis' => function ($m) { return $this->bbcode_attachment($m[1], $m[2]); })),
			'b'				=> array('bbcode_id' => 1,	'regexp' => array('#\[b\](.*?)\[/b\]#uis' => function ($m) { return $this->bbcode_strong($m[1]); })),
			'i'				=> array('bbcode_id' => 2,	'regexp' => array('#\[i\](.*?)\[/i\]#uis' => function ($m) { return $this->bbcode_italic($m[1]); })),
			'url'			=> array('bbcode_id' => 3,	'regexp' => array('#\[url(=(.*))?\](?(1)((?s).*(?-s))|(.*))\[/url\]#uiU' => function ($m) { return $this->validate_url($m[2], !empty($m[3]) ? $m[3] : safe_strval($m[4])); })),
			'img'			=> array('bbcode_id' => 4,	'regexp' => array('#\[img\](.*)\[/img\]#uiU' => function ($m) { return $this->bbcode_img($m[1]); })),
			'size'			=> array('bbcode_id' => 5,	'regexp' => array('#\[size=([\-\+]?\d+)\](.*?)\[/size\]#uis' => function ($m) { return $this->bbcode_size($m[1], $m[2]); })),
			'color'			=> array('bbcode_id' => 6,	'regexp' => array('!\[color=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)\](.*?)\[/color\]!uis' => function ($m) { return $this->bbcode_color($m[1], $m[2]); })),
			'u'				=> array('bbcode_id' => 7,	'regexp' => array('#\[u\](.*?)\[/u\]#uis' => function ($m) { return $this->bbcode_underline($m[1]); })),
			's'				=> array('bbcode_id' => 13,	'regexp' => array('#\[s\](.*?)\[/s\]#uis' => function ($m) { return $this->bbcode_strikethrough($m[1]); })),
			'list'			=> array('bbcode_id' => 9,	'regexp' => array('#\[list(?:=(?:[a-z0-9]|disc|circle|square))?].*\[/list]#uis' => function ($m) { return $this->bbcode_parse_list($m[0]); })),
			'email'			=> array('bbcode_id' => 10,	'regexp' => array('#\[email=?(.*?)?\](.*?)\[/email\]#uis' => function ($m) { return $this->validate_email($m[1], $m[2]); })),
			'flash'			=> array('bbcode_id' => 11,	'regexp' => array('#\[flash=([0-9]+),([0-9]+)\](.*?)\[/flash\]#ui' => function ($m) { return $this->bbcode_flash($m[1], $m[2], $m[3]); })),
		);

		// Zero the parsed items array
		$this->parsed_items = array();

		foreach ($this->bbcodes as $tag => $bbcode_data)
		{
			$this->parsed_items[$tag] = 0;
		}

		if (!$allow_custom_bbcode)
		{
			return;
		}

		if (!is_array($rowset))
		{
			global $db;
			$rowset = array();

			$sql = 'SELECT *
				FROM ' . BBCODES_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$rowset[] = $row;
			}
			$db->sql_freeresult($result);
		}

		foreach ($rowset as $row)
		{
			$match = $row['first_pass_match'];
			$replace = str_replace('$uid', $this->bbcode_uid, $row['first_pass_replace']);

			// Convert to a callback if the match has e modifier.
			if (preg_match('/^!(.*)!([a-zA-Z]*e[a-zA-Z]*)$/', $match, $parts))
			{
				$match = '!' . $parts[1] . '!' . str_replace('e', '', $parts[2]);
				$replace = preg_replace('#\$\{(\d+)\}#', '\'.$m[${1}].\'', $replace);
				$replace = eval('return function ($m) { return ' . $replace . '; };');
			}

			$this->bbcodes[$row['bbcode_tag']] = array(
				'bbcode_id'	=> (int) $row['bbcode_id'],
				'regexp'	=> array($match => $replace)
			);
		}
	}

	/**
	* Making some pre-checks for bbcodes as well as increasing the number of parsed items
	*/
	function check_bbcode($bbcode, &$in)
	{
		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$in = str_replace("\r\n", "\n", str_replace('\"', '"', $in));

		// Trimming here to make sure no empty bbcodes are parsed accidently
		if (trim($in) == '')
		{
			return false;
		}

		$this->parsed_items[$bbcode]++;

		return true;
	}

	/**
	* Transform some characters in valid bbcodes
	*/
	function bbcode_specialchars($text)
	{
		$str_from = array('<', '>', '[', ']', '.', ':');
		$str_to = array('&lt;', '&gt;', '&#91;', '&#93;', '&#46;', '&#58;');

		return str_replace($str_from, $str_to, $text);
	}

	/**
	* Parse size tag
	*/
	function bbcode_size($stx, $in)
	{
		global $user, $config;

		if (!$this->check_bbcode('size', $in))
		{
			return $in;
		}

		if ($config['max_' . $this->mode . '_font_size'] && $config['max_' . $this->mode . '_font_size'] < $stx)
		{
			$this->warn_msg[] = sprintf($user->lang['MAX_FONT_SIZE_EXCEEDED'], $config['max_' . $this->mode . '_font_size']);

			return '[size=' . $stx . ']' . $in . '[/size]';
		}

		if ($config['min_' . $this->mode . '_font_size'] && $config['min_' . $this->mode . '_font_size'] > $stx)
		{
			$this->warn_msg[] = sprintf($user->lang['MIN_FONT_SIZE_EXCEEDED'], $config['min_' . $this->mode . '_font_size']);

			return '[size=' . $stx . ']' . $in . '[/size]';
		}

		// Do not allow size=0
		if ($stx <= 0)
		{
			return '[size=' . $stx . ']' . $in . '[/size]';
		}

		return '[size=' . $stx . ':' . $this->bbcode_uid . ']' . $in . '[/size:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse color tag
	*/
	function bbcode_color($stx, $in)
	{
		if (!$this->check_bbcode('color', $in))
		{
			return $in;
		}

		return '[color=' . $stx . ':' . $this->bbcode_uid . ']' . $in . '[/color:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse u tag
	*/
	function bbcode_underline($in)
	{
		if (!$this->check_bbcode('u', $in))
		{
			return $in;
		}

		return '[u:' . $this->bbcode_uid . ']' . $in . '[/u:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse s tag
	*/
	function bbcode_strikethrough($in)
	{
		if (!$this->check_bbcode('s', $in))
		{
			return $in;
		}

		return '[s:' . $this->bbcode_uid . ']' . $in . '[/s:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse b tag
	*/
	function bbcode_strong($in)
	{
		if (!$this->check_bbcode('b', $in))
		{
			return $in;
		}

		return '[b:' . $this->bbcode_uid . ']' . $in . '[/b:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse i tag
	*/
	function bbcode_italic($in)
	{
		if (!$this->check_bbcode('i', $in))
		{
			return $in;
		}

		return '[i:' . $this->bbcode_uid . ']' . $in . '[/i:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse tt tag
	*/
	function bbcode_teletype($in)
	{
		if (!$this->check_bbcode('tt', $in))
		{
			return $in;
		}

		// This ugly hardcode taken from the bbcode_code
		$htm_match = get_preg_expression('bbcode_htm');
		unset($htm_match[4], $htm_match[5]);
		$htm_replace = array('\1', '\1', '\2', '\1');
		$in = preg_replace($htm_match, $htm_replace, $in);

		return '[tt:' . $this->bbcode_uid . ']' . str_replace(array('[', ']', ' ', "\t"), array('&#91;', '&#93;', '&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;'), $in) . '[/tt:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse upd tag
	*/
	function bbcode_upd($time, $subj)
	{
		if (empty($time))
		{
			$time = time();
		}
		else if ($time[0] === '+')
		{
			$parts = explode(':', $time);
			$seconds = (int) array_pop($parts);
			$seconds += array_pop($parts) * 60;
			$seconds += array_pop($parts) * 3600;
			$seconds += array_pop($parts) * 86400;
			// Do not allow very big values
			if ($seconds > 86400 * 365 * 15 || $seconds < 0)
			{
				return '[upd=' . $time . ']' . $subj . '[/upd]';
			}
		}
		else
		{
			$time = (int) $time;
			if ($time < 1000000000 || $time > time())
			{
				$time = time();
			}
		}

		// This ugly hardcode taken from the bbcode_code
		$htm_match = get_preg_expression('bbcode_htm');
		unset($htm_match[4], $htm_match[5]);
		$htm_replace = array('\1', '\1', '\2', '\1');
		$subj = preg_replace($htm_match, $htm_replace, $subj);

		return '[upd=' . $time . ':' . $this->bbcode_uid . ']' . str_replace(array('[', ']'), array('&#91;', '&#93;'), $subj) . '[/upd:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse img tag
	*/
	function bbcode_img($in)
	{
		global $user, $config;

		if (!$this->check_bbcode('img', $in))
		{
			return $in;
		}

		$error = false;
		$in = $this->fix_url($in);

		// Checking urls
		$is_relative = $this->is_relative_url($in);
		if (!$is_relative && !preg_match('#^' . get_preg_expression('url') . '$#iu', $in))
		{
			return '[img]' . $in . '[/img]';
		}

		$full = $this->to_absolute_url($in);

		if ($config['max_' . $this->mode . '_img_height'] || $config['max_' . $this->mode . '_img_width'])
		{
			$stats = @getimagesize(htmlspecialchars_decode($full));

			if ($stats === false)
			{
				$error = true;
				$this->warn_msg[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
			}
			else
			{
				if ($config['max_' . $this->mode . '_img_height'] && $config['max_' . $this->mode . '_img_height'] < $stats[1])
				{
					$error = true;
					$this->warn_msg[] = sprintf($user->lang['MAX_IMG_HEIGHT_EXCEEDED'], $config['max_' . $this->mode . '_img_height']);
				}

				if ($config['max_' . $this->mode . '_img_width'] && $config['max_' . $this->mode . '_img_width'] < $stats[0])
				{
					$error = true;
					$this->warn_msg[] = sprintf($user->lang['MAX_IMG_WIDTH_EXCEEDED'], $config['max_' . $this->mode . '_img_width']);
				}
			}
		}

		if ($error || $this->path_in_domain($full))
		{
			return '[img]' . $in . '[/img]';
		}

		return '[img:' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($in) . '[/img:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse flash tag
	*/
	function bbcode_flash($width, $height, $in)
	{
		global $user, $config;

		if (!$this->check_bbcode('flash', $in))
		{
			return $in;
		}

		$in = trim($in);
		$error = false;

		// Do not allow 0-sizes generally being entered
		if ($width <= 0 || $height <= 0)
		{
			return '[flash=' . $width . ',' . $height . ']' . $in . '[/flash]';
		}

		$in = str_replace(' ', '%20', $in);

		// Make sure $in is a URL.
		if (!preg_match('#^' . get_preg_expression('url') . '$#i', $in) &&
			!preg_match('#^' . get_preg_expression('www_url') . '$#i', $in))
		{
			return '[flash=' . $width . ',' . $height . ']' . $in . '[/flash]';
		}

		// Apply the same size checks on flash files as on images
		if ($config['max_' . $this->mode . '_img_height'] || $config['max_' . $this->mode . '_img_width'])
		{
			if ($config['max_' . $this->mode . '_img_height'] && $config['max_' . $this->mode . '_img_height'] < $height)
			{
				$error = true;
				$this->warn_msg[] = sprintf($user->lang['MAX_FLASH_HEIGHT_EXCEEDED'], $config['max_' . $this->mode . '_img_height']);
			}

			if ($config['max_' . $this->mode . '_img_width'] && $config['max_' . $this->mode . '_img_width'] < $width)
			{
				$error = true;
				$this->warn_msg[] = sprintf($user->lang['MAX_FLASH_WIDTH_EXCEEDED'], $config['max_' . $this->mode . '_img_width']);
			}
		}

		if ($error || $this->path_in_domain($in))
		{
			return '[flash=' . $width . ',' . $height . ']' . $in . '[/flash]';
		}

		return '[flash=' . $width . ',' . $height . ':' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($in) . '[/flash:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse inline attachments [ia]
	*/
	function bbcode_attachment($stx, $in)
	{
		if (!$this->check_bbcode('attachment', $in))
		{
			return $in;
		}

		return '[attachment=' . $stx . ':' . $this->bbcode_uid . ']<!-- ia' . $stx . ' -->' . trim($in) . '<!-- ia' . $stx . ' -->[/attachment:' . $this->bbcode_uid . ']';
	}

	/**
	* Parse code text from code tag
	* @access private
	*/
	function bbcode_parse_code($stx, &$code)
	{
		switch (strtolower($stx))
		{
			case 'php':

				// This piece of code is a stinky ugly hack =(

				$remove_tags = false;

				$str_from = array('&lt;', '&gt;', '&#91;', '&#93;', '&#46;', '&#58;', '&#058;');
				$str_to = array('<', '>', '[', ']', '.', ':', ':');
				$code = str_replace($str_from, $str_to, $code);

				if (strpos($code, '<?php') === false && strpos($code, '<?=') === false)
				{
					$remove_tags = true;
					$code = "<?php {$code}";
				}

				$conf = array('highlight.bg', 'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string');
				foreach ($conf as $ini_var)
				{
					@ini_set($ini_var, str_replace('highlight.', 'syntax', $ini_var));
				}

				// Because highlight_string is specialcharing the text (but we already did this before), we have to reverse this in order to get correct results
				$code = htmlspecialchars_decode($code);
				$code = highlight_string($code, true);

				// Starting from PHP 8.3, the output format was updated. An ugly hack to fix it.
				if (strpos($code, '<pre><code ') === 0)
				{
					$code = str_replace(['<pre><code ', '</code></pre>', ''], ['<span ', '</span>'], $code);
				}

				$str_from = array('<span style="color: ', '<font color="syntax', '</font>', '<code>', '</code>','[', ']', '.', ':');
				$str_to = array('<span class="', '<span class="syntax', '</span>', '', '', '&#91;', '&#93;', '&#46;', '&#58;');

				if ($remove_tags)
				{
					$str_from[] = '<span class="syntaxdefault">&lt;?php </span>';
					$str_to[] = '';
					$str_from[] = '<span class="syntaxdefault">&lt;?php&nbsp;';
					$str_to[] = '<span class="syntaxdefault">';
				}

				$code = str_replace($str_from, $str_to, $code);
				$code = preg_replace('#^(<span class="[a-z_]+">)\n?(.*?)\n?(</span>)$#is', '$1$2$3', $code);
				$code = preg_replace('#^<span class="[a-z]+"><span class="([a-z]+)">(.*)</span></span>#s', '<span class="$1">$2</span>', $code);

				// remove newline at the end
				if (!empty($code) && substr($code, -1) == "\n")
				{
					$code = substr($code, 0, -1);
				}

				return "[code=$stx:" . $this->bbcode_uid . ']' . $code . '[/code:' . $this->bbcode_uid . ']';
			break;

			default:
				return '[code:' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($code) . '[/code:' . $this->bbcode_uid . ']';
			break;
		}
	}

	/**
	* Parse code tag
	* Expects the argument to start right after the opening [code] tag and to end with [/code]
	*/
	function bbcode_code($stx, $in)
	{
		if (!$this->check_bbcode('code', $in))
		{
			return $in;
		}

		// We remove the hardcoded elements from the code block here because it is not used in code blocks
		// Having it here saves us one preg_replace per message containing [code] blocks
		// Additionally, magic url parsing should go after parsing bbcodes, but for safety those are stripped out too...
		$htm_match = get_preg_expression('bbcode_htm');
		unset($htm_match[4], $htm_match[5]);
		$htm_replace = array('\1', '\1', '\2', '\1');

		$out = $code_block = '';
		$open = 1;

		while ($in)
		{
			// Determine position and tag length of next code block
			preg_match('#(.*?)(\[code(?:=([a-z]+))?\])(.+)#is', $in, $buffer);
			$pos = (isset($buffer[1])) ? strlen($buffer[1]) : false;
			$tag_length = (isset($buffer[2])) ? strlen($buffer[2]) : false;

			// Determine position of ending code tag
			$pos2 = stripos($in, '[/code]');

			// Which is the next block, ending code or code block
			if ($pos !== false && $pos < $pos2)
			{
				// Open new block
				if (!$open)
				{
					$out .= substr($in, 0, $pos);
					$in = substr($in, $pos);
					$stx = (isset($buffer[3])) ? $buffer[3] : '';
					$code_block = '';
				}
				else
				{
					// Already opened block, just append to the current block
					$code_block .= substr($in, 0, $pos) . ((isset($buffer[2])) ? $buffer[2] : '');
					$in = substr($in, $pos);
				}

				$in = substr($in, $tag_length);
				$open++;
			}
			else
			{
				// Close the block
				if ($open == 1)
				{
					$code_block .= substr($in, 0, $pos2);
					$code_block = preg_replace($htm_match, $htm_replace, $code_block);

					// Parse this code block
					$out .= $this->bbcode_parse_code($stx, $code_block);
					$code_block = '';
					$open--;
				}
				else if ($open)
				{
					// Close one open tag... add to the current code block
					$code_block .= substr($in, 0, $pos2 + 7);
					$open--;
				}
				else
				{
					// end code without opening code... will be always outside code block
					$out .= substr($in, 0, $pos2 + 7);
				}

				$in = substr($in, $pos2 + 7);
			}
		}

		// if now $code_block has contents we need to parse the remaining code while removing the last closing tag to match up.
		if ($code_block)
		{
			$code_block = substr($code_block, 0, -7);
			$code_block = preg_replace($htm_match, $htm_replace, $code_block);

			$out .= $this->bbcode_parse_code($stx, $code_block);
		}

		return $out;
	}

	/**
	* Parse list bbcode
	* Expects the argument to start with a tag
	*/
	function bbcode_parse_list($in)
	{
		if (!$this->check_bbcode('list', $in))
		{
			return $in;
		}

		// $tok holds characters to stop at. Since the string starts with a '[' we'll get everything up to the first ']' which should be the opening [list] tag
		$tok = ']';
		$out = '[';

		// First character is [
		$in = substr($in, 1);
		$list_end_tags = $item_end_tags = array();

		do
		{
			$pos = strlen($in);

			for ($i = 0, $tok_len = strlen($tok); $i < $tok_len; ++$i)
			{
				$tmp_pos = strpos($in, $tok[$i]);

				if ($tmp_pos !== false && $tmp_pos < $pos)
				{
					$pos = $tmp_pos;
				}
			}

			$buffer = substr($in, 0, $pos);
			$tok = $in[$pos];

			$in = substr($in, $pos + 1);

			if ($tok == ']')
			{
				// if $tok is ']' the buffer holds a tag
				if (strtolower($buffer) == '/list' && sizeof($list_end_tags))
				{
					// valid [/list] tag, check nesting so that we don't hit false positives
					if (sizeof($item_end_tags) && sizeof($item_end_tags) >= sizeof($list_end_tags))
					{
						// current li tag has not been closed
						if (preg_match('/\n\[$/', $out, $m))
						{
							$out = preg_replace('/\n?\[$/', '[', $out) . array_pop($item_end_tags) . "]\n[";
						}
						else
						{
							$out .= array_pop($item_end_tags) . '][';
						}
					}

					$out .= array_pop($list_end_tags) . ']';
					$tok = '[';
				}
				else if (preg_match('#^list(=[0-9a-z]+)?$#i', $buffer, $m))
				{
					// sub-list, add a closing tag
					if (empty($m[1]) || preg_match('/^=(?:disc|square|circle)$/i', $m[1]))
					{
						array_push($list_end_tags, '/list:u:' . $this->bbcode_uid);
					}
					else
					{
						array_push($list_end_tags, '/list:o:' . $this->bbcode_uid);
					}
					$out .= 'list' . substr($buffer, 4) . ':' . $this->bbcode_uid . ']';
					$tok = '[';
				}
				else
				{
					if (($buffer == '*' || substr($buffer, -2) == '[*') && sizeof($list_end_tags))
					{
						// the buffer holds a bullet tag and we have a [list] tag open
						if (sizeof($item_end_tags) >= sizeof($list_end_tags))
						{
							if (substr($buffer, -2) == '[*')
							{
								$out .= substr($buffer, 0, -2) . '[';
							}
							// current li tag has not been closed
							if (preg_match('/\n\[$/', $out, $m))
							{
								$out = preg_replace('/\n\[$/', '[', $out);
								$buffer = array_pop($item_end_tags) . "]\n[*:" . $this->bbcode_uid;
							}
							else
							{
								$buffer = array_pop($item_end_tags) . '][*:' . $this->bbcode_uid;
							}
						}
						else
						{
							$buffer = '*:' . $this->bbcode_uid;
						}

						$item_end_tags[] = '/*:m:' . $this->bbcode_uid;
					}
					else if ($buffer == '/*')
					{
						array_pop($item_end_tags);
						$buffer = '/*:' . $this->bbcode_uid;
					}

					$out .= $buffer . $tok;
					$tok = '[]';
				}
			}
			else
			{
				// Not within a tag, just add buffer to the return string
				$out .= $buffer . $tok;
				$tok = ($tok == '[') ? ']' : '[]';
			}
		}
		while ($in);

		// do we have some tags open? close them now
		if (sizeof($item_end_tags))
		{
			$out .= '[' . implode('][', $item_end_tags) . ']';
		}
		if (sizeof($list_end_tags))
		{
			$out .= '[' . implode('][', $list_end_tags) . ']';
		}

		return $out;
	}

	/**
	* Parse quote and spoiler bbcodes
	* Expects the argument to start with a tag
	*/
	function bbcode_quote($in, $type = 'quote')
	{
		global $config, $user;

		/**
		* If you change this code, make sure the cases described within the following reports are still working:
		* #3572 - [quote="[test]test"]test [ test[/quote] - (correct: parsed)
		* #14667 - [quote]test[/quote] test ] and [ test [quote]test[/quote] (correct: parsed)
		* #14770 - [quote="["]test[/quote] (correct: parsed)
		* [quote="[i]test[/i]"]test[/quote] (correct: parsed)
		* [quote="[quote]test[/quote]"]test[/quote] (correct: parsed - Username displayed as [quote]test[/quote])
		* #20735 - [quote]test[/[/b]quote] test [/quote][/quote] test - (correct: quoted: "test[/[/b]quote] test" / non-quoted: "[/quote] test" - also failed if layout distorted)
		* #40565 - [quote="a"]a[/quote][quote="a]a[/quote] (correct: first quote tag parsed, second quote tag unparsed)
		*/

		$in = str_replace("\r\n", "\n", str_replace('\"', '"', trim($in)));

		if (!isset($config['max_'.$type.'_depth']) || $config['max_'.$type.'_depth'] < 0)
		{
			return $in;
		}

		if (!$in)
		{
			return '';
		}

		// To let the parser not catch tokens within quote_username quotes we encode them before we start this...
		$in = preg_replace_callback('#'.$type.'=&quot;(.*?)&quot;\]#i', function ($m) use ($type) { return $type.'=&quot;' . str_replace(array('[', ']', '\"'), array('&#91;', '&#93;', '\"'), $m[1]) . '&quot;]'; }, $in);

		$tok = ']';
		$out = '[';

		$in = substr($in, 1);
		$close_tags = $error_ary = array();
		$buffer = '';

		do
		{
			$pos = strlen($in);
			for ($i = 0, $tok_len = strlen($tok); $i < $tok_len; ++$i)
			{
				$tmp_pos = strpos($in, $tok[$i]);
				if ($tmp_pos !== false && $tmp_pos < $pos)
				{
					$pos = $tmp_pos;
				}
			}

			$buffer .= substr($in, 0, $pos);
			$tok = $in[$pos];
			$in = substr($in, $pos + 1);

			if ($tok == ']')
			{
				if (strtolower($buffer) == '/'.$type && sizeof($close_tags) && substr($out, -1, 1) == '[')
				{
					// we have found a closing tag
					$out .= array_pop($close_tags) . ']';
					$tok = '[';
					$buffer = '';

					/* Add space at the end of the closing tag if not happened before to allow following urls/smilies to be parsed correctly
					* Do not try to think for the user. :/ Do not parse urls/smilies if there is no space - is the same as with other bbcodes too.
					* Also, we won't have any spaces within $in anyway, only adding up spaces -> #10982
					if (!$in || $in[0] !== ' ')
					{
						$out .= ' ';
					}*/
				}
				else if (preg_match('#^'.$type.'(?:=&quot;(.*?)&quot;)?$#is', $buffer, $m) && substr($out, -1, 1) == '[')
				{
					$this->parsed_items[$type]++;

					// the buffer holds a valid opening tag
					if (!empty($config['max_'.$type.'_depth']) && sizeof($close_tags) >= $config['max_'.$type.'_depth'])
					{
						// there are too many nested quotes
						$error_ary[$type.'_depth'] = sprintf($user->lang[strtoupper($type).'_DEPTH_EXCEEDED'], $config['max_'.$type.'_depth']);

						$out .= $buffer . $tok;
						$tok = '[]';
						$buffer = '';

						continue;
					}

					array_push($close_tags, '/'.$type.':' . $this->bbcode_uid);

					if (isset($m[1]) && $m[1])
					{
						$username = str_replace(array('&#91;', '&#93;'), array('[', ']'), $m[1]);
						$username = preg_replace('#\[(?!b|i|u|color|url|email|/b|/i|/u|/color|/url|/email)#iU', '&#91;$1', $username);

						$end_tags = array();
						$error = false;

						preg_match_all('#\[((?:/)?(?:[a-z]+))#i', $username, $tags);
						foreach ($tags[1] as $tag)
						{
							if ($tag[0] != '/')
							{
								$end_tags[] = '/' . $tag;
							}
							else
							{
								$end_tag = array_pop($end_tags);
								$error = ($end_tag != $tag) ? true : false;
							}
						}

						if ($error)
						{
							$username = $m[1];
						}

						$out .= $type.'=&quot;' . $username . '&quot;:' . $this->bbcode_uid . ']';
					}
					else
					{
						$out .= $type.':' . $this->bbcode_uid . ']';
					}

					$tok = '[';
					$buffer = '';
				}
				else if (preg_match('#^'.$type.'=&quot;(.*?)#is', $buffer, $m))
				{
					// the buffer holds an invalid opening tag
					$buffer .= ']';
				}
				else
				{
					$out .= $buffer . $tok;
					$tok = '[]';
					$buffer = '';
				}
			}
			else
			{
/**
*				Old quote code working fine, but having errors listed in bug #3572
*
*				$out .= $buffer . $tok;
*				$tok = ($tok == '[') ? ']' : '[]';
*				$buffer = '';
*/

				$out .= $buffer . $tok;

				if ($tok == '[')
				{
					// Search the text for the next tok... if an ending quote comes first, then change tok to []
					$pos1 = stripos($in, '[/'.$type);
					// If the token ] comes first, we change it to ]
					$pos2 = strpos($in, ']');
					// If the token [ comes first, we change it to [
					$pos3 = strpos($in, '[');

					if ($pos1 !== false && ($pos2 === false || $pos1 < $pos2) && ($pos3 === false || $pos1 < $pos3))
					{
						$tok = '[]';
					}
					else if ($pos3 !== false && ($pos2 === false || $pos3 < $pos2))
					{
						$tok = '[';
					}
					else
					{
						$tok = ']';
					}
				}
				else
				{
					$tok = '[]';
				}
				$buffer = '';
			}
		}
		while ($in);

		$out .= $buffer;

		if (sizeof($close_tags))
		{
			$out .= '[' . implode('][', $close_tags) . ']';
		}

		foreach ($error_ary as $error_msg)
		{
			$this->warn_msg[] = $error_msg;
		}

		return $out;
	}

	/**
	* Validate email
	*/
	function validate_email($var1, $var2)
	{
		$var1 = str_replace("\r\n", "\n", str_replace('\"', '"', trim($var1)));
		$var2 = str_replace("\r\n", "\n", str_replace('\"', '"', trim($var2)));

		$txt = $var2;
		$email = ($var1) ? $var1 : $var2;

		$validated = true;

		if (!preg_match('/^' . get_preg_expression('email') . '$/i', $email))
		{
			$validated = false;
		}

		if (!$validated)
		{
			return '[email' . (($var1) ? "=$var1" : '') . ']' . $var2 . '[/email]';
		}

		$this->parsed_items['email']++;

		if ($var1)
		{
			$retval = '[email=' . $this->bbcode_specialchars($email) . ':' . $this->bbcode_uid . ']' . $txt . '[/email:' . $this->bbcode_uid . ']';
		}
		else
		{
			$retval = '[email:' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($email) . '[/email:' . $this->bbcode_uid . ']';
		}

		return $retval;
	}

	function fix_url($url)
	{
		$url = str_replace(' ', '%20', trim($url));
		if ($url[0] === '/' && $url[1] === '/')
		{
			$url = 'http:' . $url;
		}

		// Try to cope with a common user error... not specifying a protocol but only a subdomain
		if (!preg_match('#^[a-z][a-z\d+\-]*:#i', $url) && !preg_match('#^([.]/|/|[a-z]*script|about|applet|activex|chrome)#', $url))
		{
			$url = 'http://' . $url;
		}

		return $url;
	}

	function is_relative_url($url)
	{
		return preg_match('#^[.]?/' . get_preg_expression('relative_url') . '$#iu', $url) > 0;
	}

	function to_absolute_url($url)
	{
		if ($url[0] === '/') return generate_board_url(true) . $url;
		if ($url[0] === '.') return generate_board_url() . substr($url, 1);
		return $url;
	}

	/**
	* Validate url
	*
	* @param string $args optional url parameter for url bbcode: [url(=$args)]$text[/url]
	* @param string $text url bbcode content: [url(=$args)]$text[/url]
	*/
	function validate_url($args, $text)
	{
		global $config;

		$args = str_replace("\r\n", "\n", str_replace('\"', '"', trim($args)));
		$text = str_replace("\r\n", "\n", str_replace('\"', '"', trim($text)));

		$url = ($args) ? $args : $text;
		if (!$url)
		{
			return '[url' . (($args) ? '=' . $args : '') . ']' . $text . '[/url]';
		}

		// Some transformations
		$url = $this->fix_url($url);

		// Set text if not presented
		if ($args && !$text)
		{
			$text = preg_match('#^https?://[^/]+/?$#iu', $url) ? preg_replace('#(https?:|/)#iu', '', $url) : $url;
		}

		$is_relative = $this->is_relative_url($url);
		$is_file_url = false;
		$is_special = false;
		if (!$is_relative)
		{
			$is_file_url = preg_match('#^(?:https?|s?ftp)://[\w\d]#iu', $url) && preg_match('#^' . get_preg_expression('url') . '$#iu', $url);
			$is_special = preg_match('#^(?:magnet|ed2k|skype|xmpp|irc|ircs|mailto|callto|steam):#iu', $url);
		}

		// Checking urls
		if ($is_file_url || $is_relative || $is_special)
		{
			$this->parsed_items['url']++;

			// Is this a link to somewhere inside this board? If so then remove the session id from the url
			if (($is_relative || stripos($url, generate_board_url(true)) === 0) && strpos($url, 'sid=') !== false)
			{
				$url = preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}&amp;/', '\1', $url);
				$url = preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}$/', '', $url);
				$text = preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}&amp;/', '\1', $text);
				$text = preg_replace('/(&amp;|\?)sid=[0-9a-f]{32}$/', '', $text);
			}

			return ($url != $text) ? '[url=' . $this->bbcode_specialchars($url) . ':' . $this->bbcode_uid . ']' . $text . '[/url:' . $this->bbcode_uid . ']' : '[url:' . $this->bbcode_uid . ']' . $this->bbcode_specialchars($url) . '[/url:' . $this->bbcode_uid . ']';
		}

		return '[url' . (($args) ? '=' . $args : '') . ']' . $text . '[/url]';
	}

	/**
	* Check if url is pointing to this domain/script_path/php-file
	*
	* @param string $url the url to check
	* @return true if the url is pointing to this domain/script_path/php-file, false if not
	*
	* @access private
	*/
	function path_in_domain($url)
	{
		global $config, $user;

		$check_path = ($user->page['root_script_path'] != '/') ? substr($user->page['root_script_path'], 0, -1) : '/';

		// Is the user trying to link to a php file in this domain and script path?
		if (strpos($url, '.php') !== false && strpos($url, $check_path) !== false)
		{
			// Check again in correct order...
			$pos_ext = strpos($url, '.php');
			$pos_path = strpos($url, $check_path);
			$pos_domain = strpos($url, $user->host);

			if ($pos_domain !== false && $pos_path >= $pos_domain && $pos_ext >= $pos_path)
			{
				// Ok, actually we allow linking to some files (this may be able to be extended in some way later...)
				if (strpos($url, '/' . $check_path . '/file.php') !== 0)
				{
					return false;
				}

				return true;
			}
		}

		return false;
	}
}

/**
* Main message parser for posting, pm, etc. takes raw message
* and parses it for attachments, bbcode and smilies
* @package phpBB3
*/
class parse_message extends bbcode_firstpass
{
	var $attachment_data = array();
	var $filename_data = array();

	// Helps ironing out user error
	var $message_status = '';

	var $allow_img_bbcode = true;
	var $allow_flash_bbcode = true;
	var $allow_quote_bbcode = true;
	var $allow_url_bbcode = true;

	var $mode;

	/**
	* Init - give message here or manually
	*/
	function __construct($message = '')
	{
		// Init BBCode UID
		$this->bbcode_uid = substr(base_convert(unique_id(), 16, 36), 0, BBCODE_UID_LEN);
		$this->message = $message;
	}

	/**
	* Parse Message
	*/
	function parse($allow_bbcode, $allow_magic_url, $allow_smilies, $allow_img_bbcode = true, $allow_flash_bbcode = true, $allow_quote_bbcode = true, $allow_url_bbcode = true, $update_this_message = true, $mode = 'post')
	{
		global $config, $db, $user;

		$this->mode = $mode;

		foreach (array('chars', 'smilies', 'urls', 'imgs', 'font_size', 'img_height', 'img_width') as $key)
		{
			if (!isset($config['max_' . $mode . '_' . $key]))
			{
				$config['max_' . $mode . '_' . $key] = 0;
			}
		}

		foreach (array('chars', 'font_size') as $key)
		{
			if (!isset($config['min_' . $mode . '_' . $key]))
			{
				$config['min_' . $mode . '_' . $key] = 0;
			}
		}

		$this->allow_img_bbcode = $allow_img_bbcode;
		$this->allow_flash_bbcode = $allow_flash_bbcode;
		$this->allow_quote_bbcode = $allow_quote_bbcode;
		$this->allow_url_bbcode = $allow_url_bbcode;

		// If false, then $this->message won't be altered, the text will be returned instead.
		if (!$update_this_message)
		{
			$tmp_message = $this->message;
			$return_message = &$this->message;
		}

		if ($this->message_status == 'display')
		{
			$this->decode_message();
		}

		// Do some general 'cleanup' first before processing message,
		// e.g. remove excessive newlines(?), smilies(?)
		$match = array('#(script|about|applet|activex|chrome):#i');
		$replace = array("\\1&#058;");
		$this->message = preg_replace($match, $replace, trim($this->message));

		// Store message length...
		$message_length = ($mode == 'post') ? utf8_strlen($this->message) : utf8_strlen(preg_replace('#\[\/?[a-z\*\+\-]+(=[\S]+)?\]#ius', ' ', $this->message));

		// Maximum message length check. 0 disables this check completely.
		if ((int) $config['max_' . $mode . '_chars'] > 0 && $message_length > (int) $config['max_' . $mode . '_chars'])
		{
			$this->warn_msg[] = sprintf($user->lang['TOO_MANY_CHARS_' . strtoupper($mode)], $message_length, (int) $config['max_' . $mode . '_chars']);
			return (!$update_this_message) ? $return_message : $this->warn_msg;
		}

		// Minimum message length check for post only
		if ($mode === 'post')
		{
			if (!$message_length || $message_length < (int) $config['min_post_chars'])
			{
				$this->warn_msg[] = (!$message_length) ? $user->lang['TOO_FEW_CHARS'] : sprintf($user->lang['TOO_FEW_CHARS_LIMIT'], $message_length, (int) $config['min_post_chars']);
				return (!$update_this_message) ? $return_message : $this->warn_msg;
			}
		}

		// Prepare BBcode (just prepares some tags for better parsing)
		if ($allow_bbcode && strpos($this->message, '[') !== false)
		{
			$this->bbcode_init();
			$disallow = array('img', 'flash', 'quote', 'url');
			foreach ($disallow as $bool)
			{
				if (!${'allow_' . $bool . '_bbcode'})
				{
					$this->bbcodes[$bool]['disabled'] = true;
				}
			}

			$this->prepare_bbcodes();
		}

		// Parse smilies
		if ($allow_smilies)
		{
			$this->smilies($config['max_' . $mode . '_smilies']);
		}

		$num_urls = 0;

		// Parse BBCode
		if ($allow_bbcode && strpos($this->message, '[') !== false)
		{
			$this->parse_bbcode();
			$num_urls += $this->parsed_items['url'];
		}

		// Parse URL's
		if ($allow_magic_url)
		{
			$this->magic_url(generate_board_url());

			if ($config['max_' . $mode . '_urls'])
			{
				$num_urls += preg_match_all('#\<!-- ([lmwe]) --\>.*?\<!-- \1 --\>#', $this->message, $matches);
			}
		}

		// Check for "empty" message. We do not check here for maximum length, because bbcode, smilies, etc. can add to the length.
		// The maximum length check happened before any parsings.
		if ($mode === 'post' && utf8_clean_string($this->message) === '')
		{
			$this->warn_msg[] = $user->lang['TOO_FEW_CHARS'];
			return (!$update_this_message) ? $return_message : $this->warn_msg;
		}

		// Check number of links
		if ($config['max_' . $mode . '_urls'] && $num_urls > $config['max_' . $mode . '_urls'])
		{
			$this->warn_msg[] = sprintf($user->lang['TOO_MANY_URLS'], $config['max_' . $mode . '_urls']);
			return (!$update_this_message) ? $return_message : $this->warn_msg;
		}

		// Check number of images
		$num_imgs = ($allow_bbcode && stripos($this->message, '[img') !== false) ? $this->parsed_items['img'] : 0;
		if ($config['max_' . $mode . '_imgs'] && $num_imgs > $config['max_' . $mode . '_imgs'])
		{
			$this->warn_msg[] = sprintf($user->lang['TOO_MANY_IMGS'], $config['max_' . $mode . '_imgs']);
			return (!$update_this_message) ? $return_message : $this->warn_msg;
		}

		if (!$update_this_message)
		{
			unset($this->message);
			$this->message = $tmp_message;
			return $return_message;
		}

		$this->message_status = 'parsed';
		return false;
	}

	/**
	* Formatting text for display
	*/
	function format_display($allow_bbcode, $allow_magic_url, $allow_smilies, $update_this_message = true)
	{
		// If false, then the parsed message get returned but internal message not processed.
		if (!$update_this_message)
		{
			$tmp_message = $this->message;
			$return_message = &$this->message;
		}

		if ($this->message_status == 'plain')
		{
			// Force updating message - of course.
			$this->parse($allow_bbcode, $allow_magic_url, $allow_smilies, $this->allow_img_bbcode, $this->allow_flash_bbcode, $this->allow_quote_bbcode, $this->allow_url_bbcode, true);
		}

		// Replace naughty words such as farty pants
		$this->message = censor_text($this->message);

		// Parse BBcode
		if ($allow_bbcode)
		{
			$this->bbcode_cache_init();

			// We are giving those parameters to be able to use the bbcode class on its own
			$this->bbcode_second_pass($this->message, $this->bbcode_uid);
		}

		$this->message = bbcode_nl2br($this->message);
		$this->message = smiley_text($this->message, !$allow_smilies);

		if (!$update_this_message)
		{
			unset($this->message);
			$this->message = $tmp_message;
			return $return_message;
		}

		$this->message_status = 'display';
		return false;
	}

	/**
	* Decode message to be placed back into form box
	*/
	function decode_message($custom_bbcode_uid = '', $update_this_message = true)
	{
		// If false, then the parsed message get returned but internal message not processed.
		if (!$update_this_message)
		{
			$tmp_message = $this->message;
			$return_message = &$this->message;
		}

		($custom_bbcode_uid) ? decode_message($this->message, $custom_bbcode_uid) : decode_message($this->message, $this->bbcode_uid);

		if (!$update_this_message)
		{
			unset($this->message);
			$this->message = $tmp_message;
			return $return_message;
		}

		$this->message_status = 'plain';
		return false;
	}

	/**
	* Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
	* Cuts down displayed size of link if over 50 chars, turns absolute links
	* into relative versions when the server/script path matches the link
	*/
	function magic_url($server_url)
	{
		// We use the global make_clickable function
		$this->message = make_clickable($this->message, $server_url);
	}

	/**
	* Parse Smilies
	*/
	function smilies($max_smilies = 0)
	{
		global $db, $user;
		static $match;
		static $replace;

		// See if the static arrays have already been filled on an earlier invocation
		if (!is_array($match))
		{
			$match = $replace = array();

			// NOTE: obtain_* function? chaching the table contents?

			// For now setting the ttl to 10 minutes
			$sql = 'SELECT *
				FROM ' . SMILIES_TABLE . '
				ORDER BY LENGTH(code) DESC';
			$result = $db->sql_query($sql, 600);

			while ($row = $db->sql_fetchrow($result))
			{
				if (empty($row['code']))
				{
					continue;
				}

				// (assertion)
				$match[] = preg_quote($row['code'], '#');
				$replace[] = '<!-- s' . $row['code'] . ' --><img src="{SMILIES_PATH}/' . $row['smiley_url'] . '" alt="' . $row['code'] . '" title="' . (isset($user->lang[$row['emotion']]) ? $user->lang[$row['emotion']] : $row['emotion']) . '" /><!-- s' . $row['code'] . ' -->';
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($match))
		{
			if ($max_smilies)
			{
				// 'u' modifier has been added to correctly parse smilies within unicode strings
				// For details: http://tracker.phpbb.com/browse/PHPBB3-10117
				$num_matches = preg_match_all('#(?<=^|[\n .])(?:' . implode('|', $match) . ')(?![^<>]*>)#u', $this->message, $matches);
				unset($matches);

				if ($num_matches !== false && $num_matches > $max_smilies)
				{
					$this->warn_msg[] = sprintf($user->lang['TOO_MANY_SMILIES'], $max_smilies);
					return;
				}
			}

			// Make sure the delimiter # is added in front and at the end of every element within $match
			// 'u' modifier has been added to correctly parse smilies within unicode strings
			// For details: http://tracker.phpbb.com/browse/PHPBB3-10117

			$this->message = trim(preg_replace(explode(chr(0), '#(?<=^|[\n .])' . implode('(?![^<>]*>)#u' . chr(0) . '#(?<=^|[\n .])', $match) . '(?![^<>]*>)#u'), $replace, $this->message));
		}
	}

	/**
	* Parse Attachments
	*/
	function parse_attachments($form_name, $mode, $forum_id, $submit, $preview, $refresh, $is_message = false)
	{
		global $config, $auth, $user, $phpbb_root_path, $db;

		$error = array();

		$num_attachments = sizeof($this->attachment_data);
		$this->filename_data['filecomment'] = utf8_normalize_nfc(request_var('filecomment', '', true));
		$upload_file = (isset($_FILES[$form_name]) && $_FILES[$form_name]['name'] != 'none' && trim($_FILES[$form_name]['name'])) ? true : false;

		$add_file		= (isset($_POST['add_file'])) ? true : false;
		$delete_file	= (isset($_POST['delete_file'])) ? true : false;
		$update_file	= (isset($_POST['update_file'])) ? true : false;

		// First of all adjust comments if changed
		$actual_comment_list = utf8_normalize_nfc(request_var('comment_list', array(''), true));

		foreach ($actual_comment_list as $comment_key => $comment)
		{
			if (!isset($this->attachment_data[$comment_key]))
			{
				continue;
			}

			if ($this->attachment_data[$comment_key]['attach_comment'] != $actual_comment_list[$comment_key])
			{
				$this->attachment_data[$comment_key]['attach_comment'] = $actual_comment_list[$comment_key];
			}
		}

		$cfg = array();
		$cfg['max_attachments'] = ($is_message) ? $config['max_attachments_pm'] : $config['max_attachments'];
		$forum_id = ($is_message) ? 0 : $forum_id;

		if ($submit && in_array($mode, array('post', 'reply', 'quote', 'edit')) && $upload_file)
		{
			if ($num_attachments < $cfg['max_attachments'] || $auth->acl_get('a_') || $auth->acl_get('m_', $forum_id))
			{
				$filedata = upload_attachment($form_name, $forum_id, false, '', $is_message);
				$error = $filedata['error'];

				if ($filedata['post_attach'] && !sizeof($error))
				{
					$sql_ary = array(
						'physical_filename'	=> $filedata['physical_filename'],
						'attach_comment'	=> $this->filename_data['filecomment'],
						'real_filename'		=> $filedata['real_filename'],
						'extension'			=> $filedata['extension'],
						'mimetype'			=> $filedata['mimetype'],
						'filesize'			=> $filedata['filesize'],
						'filetime'			=> $filedata['filetime'],
						'thumbnail'			=> $filedata['thumbnail'],
						'is_orphan'			=> 1,
						'in_message'		=> ($is_message) ? 1 : 0,
						'poster_id'			=> $user->data['user_id'],
					);

					$db->sql_query('INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

					$new_entry = array(
						'attach_id'		=> $db->sql_nextid(),
						'is_orphan'		=> 1,
						'real_filename'	=> $filedata['real_filename'],
						'attach_comment'=> $this->filename_data['filecomment'],
					);

					$this->attachment_data = array_merge(array(0 => $new_entry), $this->attachment_data);
					$this->message = preg_replace_callback('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#', function ($m) {
						return '[attachment=' . ($m[1] + 1) . ']' . $m[2] .'[/attachment]';
					}, $this->message);
					$this->filename_data['filecomment'] = '';

					// This Variable is set to false here, because Attachments are entered into the
					// Database in two modes, one if the id_list is 0 and the second one if post_attach is true
					// Since post_attach is automatically switched to true if an Attachment got added to the filesystem,
					// but we are assigning an id of 0 here, we have to reset the post_attach variable to false.
					//
					// This is very relevant, because it could happen that the post got not submitted, but we do not
					// know this circumstance here. We could be at the posting page or we could be redirected to the entered
					// post. :)
					$filedata['post_attach'] = false;
				}
			}
			else
			{
				$error[] = sprintf($user->lang['TOO_MANY_ATTACHMENTS'], $cfg['max_attachments']);
			}
		}

		if ($preview || $refresh || sizeof($error))
		{
			// Perform actions on temporary attachments
			if ($delete_file)
			{
				require_once($phpbb_root_path . 'includes/functions_admin.php');

				$index = array_keys(request_var('delete_file', array(0 => 0)));
				$index = (!empty($index)) ? $index[0] : false;

				if ($index !== false && !empty($this->attachment_data[$index]))
				{
					// delete selected attachment
					if ($this->attachment_data[$index]['is_orphan'])
					{
						$sql = 'SELECT attach_id, physical_filename, thumbnail
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE attach_id = ' . (int) $this->attachment_data[$index]['attach_id'] . '
								AND is_orphan = 1
								AND poster_id = ' . $user->data['user_id'];
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if ($row)
						{
							phpbb_unlink($row['physical_filename'], 'file');

							if ($row['thumbnail'])
							{
								phpbb_unlink($row['physical_filename'], 'thumbnail');
							}

							$db->sql_query('DELETE FROM ' . ATTACHMENTS_TABLE . ' WHERE attach_id = ' . (int) $this->attachment_data[$index]['attach_id']);
						}
					}
					else
					{
						delete_attachments('attach', array(intval($this->attachment_data[$index]['attach_id'])));
					}

					unset($this->attachment_data[$index]);
					$this->message = preg_replace_callback('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#', function ($m) use ($index) {
						return ($m[1] == $index) ? '' : (($m[1] > $index) ? '[attachment=' . ($m[1] - 1) . ']' . $m[2] .'[/attachment]' : $m[0]);
					}, $this->message);

					// Reindex Array
					$this->attachment_data = array_values($this->attachment_data);
				}
			}
			else if (($add_file || $preview) && $upload_file)
			{
				if ($num_attachments < $cfg['max_attachments'] || $auth->acl_gets('m_', 'a_', $forum_id))
				{
					$filedata = upload_attachment($form_name, $forum_id, false, '', $is_message);
					$error = array_merge($error, $filedata['error']);

					if (!sizeof($error))
					{
						$sql_ary = array(
							'physical_filename'	=> $filedata['physical_filename'],
							'attach_comment'	=> $this->filename_data['filecomment'],
							'real_filename'		=> $filedata['real_filename'],
							'extension'			=> $filedata['extension'],
							'mimetype'			=> $filedata['mimetype'],
							'filesize'			=> $filedata['filesize'],
							'filetime'			=> $filedata['filetime'],
							'thumbnail'			=> $filedata['thumbnail'],
							'is_orphan'			=> 1,
							'in_message'		=> ($is_message) ? 1 : 0,
							'poster_id'			=> $user->data['user_id'],
						);

						$db->sql_query('INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

						$new_entry = array(
							'attach_id'		=> $db->sql_nextid(),
							'is_orphan'		=> 1,
							'real_filename'	=> $filedata['real_filename'],
							'attach_comment'=> $this->filename_data['filecomment'],
						);

						$this->attachment_data = array_merge(array(0 => $new_entry), $this->attachment_data);
						$this->message = preg_replace_callback('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#', function ($m) {
							return '[attachment=' . ($m[1] + 1) . ']' . $m[2] .'[/attachment]';
						}, $this->message);
						$this->filename_data['filecomment'] = '';
					}
				}
				else
				{
					$error[] = sprintf($user->lang['TOO_MANY_ATTACHMENTS'], $cfg['max_attachments']);
				}
			}
			else if ($update_file && $upload_file)
			{
				require_once($phpbb_root_path . 'includes/functions_admin.php');

				$filedata = upload_attachment($form_name, $forum_id, false, '', $is_message);
				$error = array_merge($error, $filedata['error']);

				$index = array_keys(request_var('update_file', array(0 => 0)));
				$index = (!empty($index)) ? $index[0] : false;
				$filename = $filedata['real_filename'];

				if ($index !== false && !empty($this->attachment_data[$index]))
				{
					$sql = 'SELECT attach_id, is_orphan, filesize, physical_filename, thumbnail
						FROM ' . ATTACHMENTS_TABLE . '
						WHERE attach_id = ' . (int) $this->attachment_data[$index]['attach_id']; // . ' AND poster_id = ' . $user->data['user_id'];
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!sizeof($error))
					{
						$sql_ary = array(
							'physical_filename'	=> $filedata['physical_filename'],
							'attach_comment'	=> $this->filename_data['filecomment'],
							'real_filename'		=> $filedata['real_filename'],
							'extension'			=> $filedata['extension'],
							'mimetype'			=> $filedata['mimetype'],
							'filesize'			=> $filedata['filesize'],
							'filetime'			=> $filedata['filetime'],
							'thumbnail'			=> $filedata['thumbnail'],
							'in_message'		=> ($is_message) ? 1 : 0,
							// 'poster_id'			=> $user->data['user_id'],
						);

						$db->sql_query('UPDATE ' . ATTACHMENTS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE attach_id = ' . (int) $this->attachment_data[$index]['attach_id'] );

						// Delete old file
						phpbb_unlink($row['physical_filename'], 'file');

						if ($row['thumbnail'])
						{
							phpbb_unlink($row['physical_filename'], 'thumbnail');
						}

						if (!$row['is_orphan'])
						{
							set_config('upload_dir_size', $config['upload_dir_size'] - $row['filesize'] + $filedata['filesize'], true);
						}

						// Refresh attachment data
						$this->attachment_data[$index]['real_filename'] = $filedata['real_filename'];
						$this->attachment_data[$index]['attach_comment'] = ($this->filename_data['filecomment']) ? $this->filename_data['filecomment'] : $this->attachment_data[$index]['attach_comment'];
						$this->message = preg_replace("#\[attachment=$index\](.*?)\[\/attachment\]#e", "'[attachment=$index]' . \$filename . '[/attachment]'", $this->message);
						$this->filename_data['filecomment'] = '';
					}
				}
			}
		}

		foreach ($error as $error_msg)
		{
			$this->warn_msg[] = $error_msg;
		}
	}

	/**
	* Get Attachment Data
	*/
	function get_submitted_attachment_data($check_user_id = false)
	{
		global $user, $db, $phpbb_root_path, $config;

		$this->filename_data['filecomment'] = utf8_normalize_nfc(request_var('filecomment', '', true));
		$attachment_data = (isset($_POST['attachment_data'])) ? $_POST['attachment_data'] : array();
		$this->attachment_data = array();

		$check_user_id = ($check_user_id === false) ? $user->data['user_id'] : $check_user_id;

		if (!sizeof($attachment_data))
		{
			return;
		}

		$not_orphan = $orphan = array();

		foreach ($attachment_data as $pos => $var_ary)
		{
			if ($var_ary['is_orphan'])
			{
				$orphan[(int) $var_ary['attach_id']] = $pos;
			}
			else
			{
				$not_orphan[(int) $var_ary['attach_id']] = $pos;
			}
		}

		// Regenerate already posted attachments
		if (sizeof($not_orphan))
		{
			// Get the attachment data, based on the poster id...
			$sql = 'SELECT attach_id, is_orphan, real_filename, attach_comment
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('attach_id', array_keys($not_orphan)) . '
					AND poster_id = ' . $check_user_id;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$pos = $not_orphan[$row['attach_id']];
				$this->attachment_data[$pos] = $row;
				set_var($this->attachment_data[$pos]['attach_comment'], $_POST['attachment_data'][$pos]['attach_comment'], 'string', true);

				unset($not_orphan[$row['attach_id']]);
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($not_orphan))
		{
			trigger_error('NO_ACCESS_ATTACHMENT', E_USER_ERROR);
		}

		// Regenerate newly uploaded attachments
		if (sizeof($orphan))
		{
			$sql = 'SELECT attach_id, is_orphan, real_filename, attach_comment
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('attach_id', array_keys($orphan)) . '
					AND poster_id = ' . $user->data['user_id'] . '
					AND is_orphan = 1';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$pos = $orphan[$row['attach_id']];
				$this->attachment_data[$pos] = $row;
				set_var($this->attachment_data[$pos]['attach_comment'], $_POST['attachment_data'][$pos]['attach_comment'], 'string', true);

				unset($orphan[$row['attach_id']]);
			}
			$db->sql_freeresult($result);
		}

		if (sizeof($orphan))
		{
			trigger_error('NO_ACCESS_ATTACHMENT', E_USER_ERROR);
		}

		ksort($this->attachment_data);
	}

	/**
	* Parse Poll
	*/
	function parse_poll(&$poll)
	{
		global $auth, $user, $config;

		$poll_max_options = $poll['poll_max_options'];

		// Parse Poll Option text ;)
		$tmp_message = $this->message;
		$this->message = $poll['poll_option_text'];
		$bbcode_bitfield = $this->bbcode_bitfield;

		$poll['poll_option_text'] = $this->parse($poll['enable_bbcode'], ($config['allow_post_links']) ? $poll['enable_urls'] : false, $poll['enable_smilies'], $poll['img_status'], false, false, $config['allow_post_links'], false, 'poll');

		$bbcode_bitfield = base64_encode(base64_decode($bbcode_bitfield) | base64_decode($this->bbcode_bitfield));
		$this->message = $tmp_message;

		// Parse Poll Title
		$tmp_message = $this->message;
		$this->message = $poll['poll_title'];
		$this->bbcode_bitfield = $bbcode_bitfield;

		$poll['poll_options'] = explode("\n", trim($poll['poll_option_text']));
		$poll['poll_options_size'] = sizeof($poll['poll_options']);

		if (!$poll['poll_title'] && $poll['poll_options_size'])
		{
			$this->warn_msg[] = $user->lang['NO_POLL_TITLE'];
		}
		else
		{
			if (utf8_strlen(preg_replace('#\[\/?[a-z\*\+\-]+(=[\S]+)?\]#ius', ' ', $this->message)) > 100)
			{
				$this->warn_msg[] = $user->lang['POLL_TITLE_TOO_LONG'];
			}
			$poll['poll_title'] = $this->parse($poll['enable_bbcode'], ($config['allow_post_links']) ? $poll['enable_urls'] : false, $poll['enable_smilies'], $poll['img_status'], false, false, $config['allow_post_links'], false, 'poll');
			if (strlen($poll['poll_title']) > 255)
			{
				$this->warn_msg[] = $user->lang['POLL_TITLE_COMP_TOO_LONG'];
			}
		}

		$this->bbcode_bitfield = base64_encode(base64_decode($bbcode_bitfield) | base64_decode($this->bbcode_bitfield));
		$this->message = $tmp_message;
		unset($tmp_message);

		if (sizeof($poll['poll_options']) == 1)
		{
			$this->warn_msg[] = $user->lang['TOO_FEW_POLL_OPTIONS'];
		}
		else if ($poll['poll_options_size'] > (int) $config['max_poll_options'])
		{
			$this->warn_msg[] = $user->lang['TOO_MANY_POLL_OPTIONS'];
		}
		else if ($poll_max_options > $poll['poll_options_size'])
		{
			$this->warn_msg[] = $user->lang['TOO_MANY_USER_OPTIONS'];
		}

		$poll['poll_max_options'] = ($poll['poll_max_options'] < 1) ? 1 : (($poll['poll_max_options'] > $config['max_poll_options']) ? $config['max_poll_options'] : $poll['poll_max_options']);
	}
}
