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

function safe_strval(&$m)
{
	return isset($m) ? strval($m) : '';
}

/**
* BBCode class
*/
class bbcode
{
	var $bbcode_uid = '';
	var $bbcode_bitfield = '';
	var $bbcode_cache = array();
	var $bbcode_template = array();
	var $post_time = 0;

	var $bbcodes = array();

	var $template_bitfield;
	var $template_filename = '';

	/**
	* Constructor
	* Init bbcode cache entries if bitfield is specified
	*/
	function __construct($bitfield = '')
	{
		$this->set_bitfield($bitfield);
	}

	function set_bitfield($bitfield = '')
	{
		if ($bitfield)
		{
			$this->bbcode_bitfield = $bitfield;
			$this->bbcode_cache_init();
		}
	}

	/**
	* Second pass bbcodes
	*/
	function bbcode_second_pass(&$message, $bbcode_uid = '', $bbcode_bitfield = false, $post_time = 0)
	{
		if ($bbcode_uid)
		{
			$this->bbcode_uid = $bbcode_uid;
		}

		if ($bbcode_bitfield !== false)
		{
			$this->bbcode_bitfield = $bbcode_bitfield;

			// Init those added with a new bbcode_bitfield (already stored codes will not get parsed again)
			$this->bbcode_cache_init();
		}

		if (!$this->bbcode_bitfield)
		{
			// Remove the uid from tags that have not been transformed into HTML
			if ($this->bbcode_uid)
			{
				$message = str_replace(':' . $this->bbcode_uid, '', $message);
			}

			return;
		}

		// Post time for the [upd] bbcode
		$this->post_time = (int) $post_time;

		$str = array('search' => array(), 'replace' => array());
		$preg = array('search' => array(), 'replace' => array());

		$bitfield = new bitfield($this->bbcode_bitfield);
		$bbcodes_set = $bitfield->get_all_set();

		$undid_bbcode_specialchars = false;
		foreach ($bbcodes_set as $bbcode_id)
		{
			if (!empty($this->bbcode_cache[$bbcode_id]))
			{
				foreach ($this->bbcode_cache[$bbcode_id] as $type => $array)
				{
					foreach ($array as $search => $replace)
					{
						${$type}['search'][] = str_replace('$uid', $this->bbcode_uid, $search);
						${$type}['replace'][] = $replace;
					}

					if (sizeof($str['search']))
					{
						$message = str_replace($str['search'], $str['replace'], $message);
						$str = array('search' => array(), 'replace' => array());
					}

					if (sizeof($preg['search']))
					{
						// we need to turn the entities back into their original form to allow the
						// search patterns to work properly
						if (!$undid_bbcode_specialchars)
						{
							$message = str_replace(array('&#58;', '&#46;'), array(':', '.'), $message);
							$undid_bbcode_specialchars = true;
						}

						for ($i = 0; $i < count($preg['search']); $i++)
						{
							if (is_callable($preg['replace'][$i]))
							{
								$message = preg_replace_callback($preg['search'][$i], $preg['replace'][$i], $message);
							}
							else
							{
								$message = preg_replace($preg['search'][$i], $preg['replace'][$i], $message);
							}
						}

						$preg = array('search' => array(), 'replace' => array());
					}
				}
			}
		}

		// Remove the uid from tags that have not been transformed into HTML
		$message = str_replace(':' . $this->bbcode_uid, '', $message);
	}

	/**
	* Init bbcode cache
	*
	* requires: $this->bbcode_bitfield
	* sets: $this->bbcode_cache with bbcode templates needed for bbcode_bitfield
	*/
	function bbcode_cache_init()
	{
		global $phpbb_root_path, $template, $user;

		if (empty($this->template_filename))
		{
			$this->template_bitfield = new bitfield($user->theme['bbcode_bitfield']);
			$this->template_filename = $phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/template/bbcode.html';

			if (empty($user->theme['template_inherits_id']) && !empty($template->orig_tpl_inherits_id))
			{
				$user->theme['template_inherits_id'] = $template->orig_tpl_inherits_id;
			}

			if (!@file_exists($this->template_filename))
			{
				if (isset($user->theme['template_inherits_id']) && $user->theme['template_inherits_id'])
				{
					$this->template_filename = $phpbb_root_path . 'styles/' . $user->theme['template_inherit_path'] . '/template/bbcode.html';
					if (!@file_exists($this->template_filename))
					{
						trigger_error('The file ' . $this->template_filename . ' is missing.', E_USER_ERROR);
					}
				}
				else
				{
					trigger_error('The file ' . $this->template_filename . ' is missing.', E_USER_ERROR);
				}
			}
		}

		$bbcode_ids = $rowset = $sql = array();

		$bitfield = new bitfield($this->bbcode_bitfield);
		$bbcodes_set = $bitfield->get_all_set();

		foreach ($bbcodes_set as $bbcode_id)
		{
			if (isset($this->bbcode_cache[$bbcode_id]))
			{
				// do not try to re-cache it if it's already in
				continue;
			}
			$bbcode_ids[] = $bbcode_id;

			if ($bbcode_id > NUM_CORE_BBCODES)
			{
				$sql[] = $bbcode_id;
			}
		}

		if (sizeof($sql))
		{
			global $db;

			$sql = 'SELECT *
				FROM ' . BBCODES_TABLE . '
				WHERE ' . $db->sql_in_set('bbcode_id', $sql);
			$result = $db->sql_query($sql, 3600);

			while ($row = $db->sql_fetchrow($result))
			{
				// To circumvent replacing newlines with <br /> for the generated html,
				// we use carriage returns here. They are later changed back to newlines
				$row['bbcode_tpl'] = str_replace("\n", "\r", $row['bbcode_tpl']);
				$row['second_pass_replace'] = str_replace("\n", "\r", $row['second_pass_replace']);

				$rowset[$row['bbcode_id']] = $row;
			}
			$db->sql_freeresult($result);
		}

		foreach ($bbcode_ids as $bbcode_id)
		{
			switch ($bbcode_id)
			{
				case 0:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							"[/quote:\$uid]\n"	=> $this->bbcode_tpl('quote_close', $bbcode_id),
							'[/quote:$uid]'	=> $this->bbcode_tpl('quote_close', $bbcode_id)
						),
						'preg' => array(
							'#\[quote(?:=&quot;(.*?)&quot;)?:$uid\]((?!\[quote(?:=&quot;.*?&quot;)?:$uid\]).)?#is' => function ($m) { return $this->bbcode_second_pass_quote(safe_strval($m[1]), safe_strval($m[2])); },
						)
					);
				break;

				case 1:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[b:$uid]'	=> $this->bbcode_tpl('b_open', $bbcode_id),
							'[/b:$uid]'	=> $this->bbcode_tpl('b_close', $bbcode_id),
						)
					);
				break;

				case 2:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[i:$uid]'	=> $this->bbcode_tpl('i_open', $bbcode_id),
							'[/i:$uid]'	=> $this->bbcode_tpl('i_close', $bbcode_id),
						)
					);
				break;

				case 3:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[url:$uid\]((.*?))\[/url:$uid\]#s'           => function ($m) { return $this->bbcode_second_pass_url($m[1], $m[2]); },
							'#\[url=([^\[]+?):$uid\](.*?)\[/url:$uid\]#s'   => function ($m) { return $this->bbcode_second_pass_url($m[1], $m[2]); },
						)
					);
				break;

				case 4:
					if ($user->optionget('viewimg'))
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[img:$uid\](.*?)\[/img:$uid\]#s'		=> $this->bbcode_tpl('img', $bbcode_id),
							)
						);
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[img:$uid\](.*?)\[/img:$uid\]#s' => function ($m) { return $this->bbcode_second_pass_url($m[1], '[ img ]'); },
							)
						);
					}
				break;

				case 5:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[size=([\-\+]?\d+):$uid\](.*?)\[/size:$uid\]#s'	=> $this->bbcode_tpl('size', $bbcode_id),
						)
					);
				break;

				case 6:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'!\[color=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+):$uid\](.*?)\[/color:$uid\]!is'	=> $this->bbcode_tpl('color', $bbcode_id),
						)
					);
				break;

				case 7:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[u:$uid]'	=> $this->bbcode_tpl('u_open', $bbcode_id),
							'[/u:$uid]'	=> $this->bbcode_tpl('u_close', $bbcode_id),
						)
					);
				break;

				case 8:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[code(?:=([a-z]+))?:$uid\](.*?)\[/code:$uid\][\n]?#is' => function ($m) { return $this->bbcode_second_pass_code($m[1], $m[2]); },
						)
					);
				break;

				case 9:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#(\[\/?(list|\*):[mou]?:?$uid\])[\n]{1}#'	=> "\$1",
							'#(\[list=([^\[]+):$uid\])[\n]{1}#'			=> "\$1",
							'#\[list=([^\[]+):$uid\]#'					=> function ($m) { return $this->bbcode_list($m[1]); },
						),
						'str' => array(
							'[list:$uid]'		=> $this->bbcode_tpl('ulist_open_default', $bbcode_id),
							'[/list:u:$uid]'	=> $this->bbcode_tpl('ulist_close', $bbcode_id),
							'[/list:o:$uid]'	=> $this->bbcode_tpl('olist_close', $bbcode_id),
							'[*:$uid]'			=> $this->bbcode_tpl('listitem', $bbcode_id),
							'[/*:$uid]'			=> $this->bbcode_tpl('listitem_close', $bbcode_id),
							'[/*:m:$uid]'		=> $this->bbcode_tpl('listitem_close', $bbcode_id)
						),
					);
				break;

				case 10:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[email:$uid\]((.*?))\[/email:$uid\]#is'			=> $this->bbcode_tpl('email', $bbcode_id),
							'#\[email=([^\[]+):$uid\](.*?)\[/email:$uid\]#is'	=> $this->bbcode_tpl('email', $bbcode_id)
						)
					);
				break;

				case 11:
					if ($user->optionget('viewflash'))
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[flash=([0-9]+),([0-9]+):$uid\](.*?)\[/flash:$uid\]#'	=> $this->bbcode_tpl('flash', $bbcode_id),
							)
						);
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = array(
							'preg' => array(
								'#\[flash=([0-9]+),([0-9]+):$uid\](.*?)\[/flash:$uid\]#'	=> function ($m) { return $this->bbcode_second_pass_url($m[3], '[ flash ]'); },
							)
						);
					}
				break;

				case 12:
					$this->bbcode_cache[$bbcode_id] = array(
						'str'	=> array(
							'[/attachment:$uid]'	=> $this->bbcode_tpl('inline_attachment_close', $bbcode_id)
						),
						'preg'	=> array(
							'#\[attachment=([0-9]+):$uid\]#'	=> $this->bbcode_tpl('inline_attachment_open', $bbcode_id)
						)
					);
				break;

				case 13:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[s:$uid]'	=> $this->bbcode_tpl('s_open', $bbcode_id),
							'[/s:$uid]'	=> $this->bbcode_tpl('s_close', $bbcode_id),
						)
					);
				break;

				case 14:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							'[tt:$uid]'	=> $this->bbcode_tpl('tt_open', $bbcode_id),
							'[/tt:$uid]'	=> $this->bbcode_tpl('tt_close', $bbcode_id),
						)
					);
				break;

				case 15:
					$this->bbcode_cache[$bbcode_id] = array(
						'preg' => array(
							'#\[upd=([\d]{9,10}|[+]\d+(?:[:]\d+){0,3}):$uid\](.*?)\[/upd:$uid\]#' => function ($m) { return $this->bbcode_second_pass_upd($m[1], $m[2]); },
						)
					);
				break;

				case 16:
					$this->bbcode_cache[$bbcode_id] = array(
						'str' => array(
							"[/spoiler:\$uid]\n"	=> $this->bbcode_tpl('spoiler_close', $bbcode_id),
							'[/spoiler:$uid]'		=> $this->bbcode_tpl('spoiler_close', $bbcode_id)
						),
						'preg' => array(
							'#\[spoiler(?:=&quot;(.*?)&quot;)?:$uid\]((?!\[spoiler(?:=&quot;.*?&quot;)?:$uid\]).)?#is'	=> function ($m) { return $this->bbcode_second_pass_spoiler(safe_strval($m[1]), safe_strval($m[2])); }
						)
					);
				break;

				default:
					if (isset($rowset[$bbcode_id]))
					{
						if ($this->template_bitfield->get($bbcode_id))
						{
							// The bbcode requires a custom template to be loaded
							if (!$bbcode_tpl = $this->bbcode_tpl($rowset[$bbcode_id]['bbcode_tag'], $bbcode_id))
							{
								// For some reason, the required template seems not to be available, use the default template
								$bbcode_tpl = (!empty($rowset[$bbcode_id]['second_pass_replace'])) ? $rowset[$bbcode_id]['second_pass_replace'] : $rowset[$bbcode_id]['bbcode_tpl'];
							}
							else
							{
								// In order to use templates with custom bbcodes we need
								// to replace all {VARS} to corresponding backreferences
								// Note that backreferences are numbered from bbcode_match
								if (preg_match_all('/\{(URL|LOCAL_URL|EMAIL|TEXT|SIMPLETEXT|INTTEXT|IDENTIFIER|COLOR|NUMBER)[0-9]*\}/', $rowset[$bbcode_id]['bbcode_match'], $m))
								{
									foreach ($m[0] as $i => $tok)
									{
										$bbcode_tpl = str_replace($tok, '$' . ($i + 1), $bbcode_tpl);
									}
								}
							}
						}
						else
						{
							// Default template
							$bbcode_tpl = (!empty($rowset[$bbcode_id]['second_pass_replace'])) ? $rowset[$bbcode_id]['second_pass_replace'] : $rowset[$bbcode_id]['bbcode_tpl'];
						}

						// Replace {L_*} lang strings
						$bbcode_tpl = preg_replace_callback('/{L_([A-Z0-9_]+)}/', function ($m) use ($user) { return (!empty($user->lang[$m[1]])) ? $user->lang[$m[1]] : ucwords(strtolower(str_replace('_', ' ', $m[1]))); }, $bbcode_tpl);

						if (!empty($rowset[$bbcode_id]['second_pass_replace']))
						{
							// The custom BBCode requires second-pass pattern replacements
							$this->bbcode_cache[$bbcode_id] = array(
								'preg' => array($rowset[$bbcode_id]['second_pass_match'] => $bbcode_tpl)
							);
						}
						else
						{
							$this->bbcode_cache[$bbcode_id] = array(
								'str' => array($rowset[$bbcode_id]['second_pass_match'] => $bbcode_tpl)
							);
						}
					}
					else
					{
						$this->bbcode_cache[$bbcode_id] = false;
					}
				break;
			}
		}
	}

	/**
	* Return bbcode template
	*/
	function bbcode_tpl($tpl_name, $bbcode_id = -1, $skip_bitfield_check = false)
	{
		static $bbcode_hardtpl = array();
		if (empty($bbcode_hardtpl))
		{
			global $user;

			$bbcode_hardtpl = array(
				'b_open'	=> '<span style="font-weight: bold">',
				'b_close'	=> '</span>',
				'i_open'	=> '<span style="font-style: italic">',
				'i_close'	=> '</span>',
				'u_open'	=> '<span style="text-decoration: underline">',
				'u_close'	=> '</span>',
				's_open'	=> '<span style="text-decoration: line-through">',
				's_close'	=> '</span>',
				'tt_open'	=> '<code>',
				'tt_close'	=> '</code>',
				'img'		=> '<img src="$1" alt="' . $user->lang['IMAGE'] . '" />',
				'size'		=> '<span style="font-size: $1%; line-height: normal">$2</span>',
				'color'		=> '<span style="color: $1">$2</span>',
				'email'		=> '<a href="mailto:$1" class="postlink">$2</a>',
				// Fallbacks for old templates
				'upd_merged'			=> '<span style="font-size: 85%; line-height: normal; color: gray;">$1</span>',
				'upd_subject'			=> '<br /><span style="font-weight: bold">$1</span>',
				'spoiler_title_open'	=> '<div><span style="font-weight: bold">$1:</span> ',
				'spoiler_open'			=> '<div>',
				'spoiler_close'			=> '</div>',
			);
		}

		if ($bbcode_id != -1 && !$skip_bitfield_check && !$this->template_bitfield->get($bbcode_id))
		{
			return (isset($bbcode_hardtpl[$tpl_name])) ? $bbcode_hardtpl[$tpl_name] : false;
		}

		if (empty($this->bbcode_template))
		{
			if (($tpl = file_get_contents($this->template_filename)) === false)
			{
				trigger_error('Could not load bbcode template', E_USER_ERROR);
			}

			// replace \ with \\ and then ' with \'.
			$tpl = str_replace('\\', '\\\\', $tpl);
			$tpl = str_replace("'", "\'", $tpl);

			// strip newlines and indent
			$tpl = preg_replace("/\n[\n\r\s\t]*/", '', $tpl);

			// Turn template blocks into PHP assignment statements for the values of $bbcode_tpl..
			$this->bbcode_template = array();

			$matches = preg_match_all('#<!-- BEGIN (.*?) -->(.*?)<!-- END (?:.*?) -->#', $tpl, $match);

			for ($i = 0; $i < $matches; $i++)
			{
				if (empty($match[1][$i]))
				{
					continue;
				}

				$this->bbcode_template[$match[1][$i]] = $this->bbcode_tpl_replace($match[1][$i], $match[2][$i]);
			}
		}

		return (isset($this->bbcode_template[$tpl_name])) ? $this->bbcode_template[$tpl_name] : ((isset($bbcode_hardtpl[$tpl_name])) ? $bbcode_hardtpl[$tpl_name] : false);
	}

	/**
	* Return bbcode template replacement
	*/
	function bbcode_tpl_replace($tpl_name, $tpl)
	{
		global $user;

		static $replacements = array(
			'quote_username_open'	=> array('{USERNAME}'	=> '$1'),
			'spoiler_title_open'	=> array('{TITLE}'		=> '$1'),
			'color'					=> array('{COLOR}'		=> '$1', '{TEXT}'			=> '$2'),
			'size'					=> array('{SIZE}'		=> '$1', '{TEXT}'			=> '$2'),
			'img'					=> array('{URL}'		=> '$1'),
			'flash'					=> array('{WIDTH}'		=> '$1', '{HEIGHT}'			=> '$2', '{URL}'	=> '$3'),
			'url'					=> array('{URL}'		=> '$1', '{DESCRIPTION}'	=> '$2'),
			'email'					=> array('{EMAIL}'		=> '$1', '{DESCRIPTION}'	=> '$2'),
			'upd_merged'			=> array('{MERGED}'		=> '$1'),
			'upd_subject'			=> array('{SUBJECT}'	=> '$1'),
		);

		$tpl = preg_replace_callback('/{L_([A-Z0-9_]+)}/', function ($m) use ($user) { return (!empty($user->lang[$m[1]])) ? $user->lang[$m[1]] : ucwords(strtolower(str_replace('_', ' ', $m[1]))); }, $tpl);

		if (!empty($replacements[$tpl_name]))
		{
			$tpl = strtr($tpl, $replacements[$tpl_name]);
		}

		return trim($tpl);
	}

	/**
	* Second parse list bbcode
	*/
	function bbcode_list($type)
	{
		$start = 1;
		if ($type == '')
		{
			$tpl = 'ulist_open_default';
			$type = 'default';
		}
		else if ($type == 'i')
		{
			$tpl = 'olist_open';
			$type = 'lower-roman';
		}
		else if ($type == 'I')
		{
			$tpl = 'olist_open';
			$type = 'upper-roman';
		}
		else if (preg_match('#^(disc|circle|square)$#i', $type))
		{
			$tpl = 'ulist_open';
			$type = strtolower($type);
		}
		else if (preg_match('#^[a-z]$#', $type))
		{
			$start = (int)(ord($type) - ord('a') + 1);
			$tpl = 'olist_open';
			$type = 'lower-alpha';
		}
		else if (preg_match('#[A-Z]#', $type))
		{
			$start = (int)(ord($type) - ord('A') + 1);
			$tpl = 'olist_open';
			$type = 'upper-alpha';
		}
		else if (is_numeric($type))
		{
			$start = (int) $type;
			$tpl = 'olist_open';
			$type = 'decimal';
		}
		else
		{
			$tpl = 'olist_open';
			$type = 'decimal';
		}

		return strtr($this->bbcode_tpl($tpl), array('{LIST_TYPE}' => $type, '{LIST_START}' => $start));
	}

	/**
	* Second parse url tag
	*/
	function bbcode_second_pass_url($href, $text)
	{
		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$href = str_replace('\"', '"', $href);
		$text = str_replace('\"', '"', $text);
		$external = stripos(preg_replace('#^https?://#i', '', $href), preg_replace('#^https?://#i', '', generate_board_url(true))) !== 0 && $href[0] !== '.' && $href[0] !== '/';
		$attrs = $external ? (' class="postlink"' . get_attrs_for_external_link($href)) : ' class="postlink local"';
		return '<a href="'.$href.'"'.$attrs.'>'.$text.'</a>';
	}

	/**
	* Second parse upd tag
	*/
	function bbcode_second_pass_upd($time, $subj)
	{
		global $user;

		static $tpls = array();
		if (empty($tpls))
		{
			$tpls = array(
				'upd_merged'	=> $this->bbcode_tpl('upd_merged', 15),
				'upd_subject'	=> $this->bbcode_tpl('upd_subject', 15),
			);
		}

		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$time = str_replace('\"', '"', $time);
		$subj = str_replace('\"', '"', $subj);
		$result = '';

		if ($time[0] !== '+')
		{
			$time = (int) $time;
			if (!$this->post_time || $time - $this->post_time < 0)
			{
				$result = $user->format_date($time, false, true);
				$result = str_replace('$1', sprintf($user->lang['UPD_MERGED'], $result), $tpls['upd_merged']);
			}
			else
			{
				$result = time_delta::get_verbal($this->post_time, $time, false, 2);
				$result = str_replace('$1', sprintf($user->lang['UPD_MERGED_AFTER'], $result), $tpls['upd_merged']);
			}
			$this->post_time = max($time, $this->post_time);
		}
		else
		{
			$parts = explode(':', $time);
			$seconds = (int) array_pop($parts);
			$seconds += array_pop($parts) * 60;
			$seconds += array_pop($parts) * 3600;
			$seconds += array_pop($parts) * 86400;

			if ($this->post_time)
			{
				$this->post_time += $seconds;
			}

			$result = time_delta::get_verbal(0, $seconds, false, 2);
			$result = str_replace('$1', sprintf($user->lang['UPD_MERGED_AFTER'], $result), $tpls['upd_merged']);
		}

		if (trim($subj))
		{
			$result .= str_replace('$1', $subj, $tpls['upd_subject']);
		}

		return $result;
	}

	/**
	* Second parse quote tag
	*/
	function bbcode_second_pass_quote($username, $quote)
	{
		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$quote = str_replace('\"', '"', $quote);
		$username = str_replace('\"', '"', $username);

		// remove newline at the beginning
		if ($quote == "\n")
		{
			$quote = '';
		}

		$quote = (($username) ? str_replace('$1', $username, $this->bbcode_tpl('quote_username_open')) : $this->bbcode_tpl('quote_open')) . $quote;

		return $quote;
	}

	/**
	* Second parse spoiler tag
	*/
	function bbcode_second_pass_spoiler($title, $text)
	{
		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$text = str_replace('\"', '"', $text);
		$title = str_replace('\"', '"', $title);

		// remove newline at the beginning
		if ($text == "\n")
		{
			$text = '';
		}

		$text = (($title) ? str_replace('$1', $title, $this->bbcode_tpl('spoiler_title_open', 16)) : $this->bbcode_tpl('spoiler_open', 16)) . $text;

		return $text;
	}

	/**
	* Second parse code tag
	*/
	function bbcode_second_pass_code($type, $code)
	{
		// when using the /e modifier, preg_replace slashes double-quotes but does not
		// seem to slash anything else
		$code = str_replace('\"', '"', $code);

		switch ($type)
		{
			case 'php':
				// Not the english way, but valid because of hardcoded syntax highlighting
				if (strpos($code, '<span class="syntaxdefault"><br /></span>') === 0)
				{
					$code = substr($code, 41);
				}

			// no break;

			default:
				$code = str_replace("\t", '&nbsp; &nbsp;', $code);
				$code = str_replace('  ', '&nbsp; ', $code);
				$code = str_replace('  ', ' &nbsp;', $code);
				$code = str_replace("\n ", "\n&nbsp;", $code);

				// keep space at the beginning
				if (!empty($code) && $code[0] == ' ')
				{
					$code = '&nbsp;' . substr($code, 1);
				}

				// remove newline at the beginning
				if (!empty($code) && $code[0] == "\n")
				{
					$code = substr($code, 1);
				}
			break;
		}

		$code = $this->bbcode_tpl('code_open') . $code . $this->bbcode_tpl('code_close');

		return $code;
	}
}
