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

/**
* Generate sort selection fields
*/
function gen_sort_selects(&$limit_days, &$sort_by_text, &$sort_days, &$sort_key, &$sort_dir, &$s_limit_days, &$s_sort_key, &$s_sort_dir, &$u_sort_param, $def_st = false, $def_sk = false, $def_sd = false)
{
	global $user;

	$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

	$sorts = array(
		'st'	=> array(
			'key'		=> 'sort_days',
			'default'	=> $def_st,
			'options'	=> $limit_days,
			'output'	=> &$s_limit_days,
		),

		'sk'	=> array(
			'key'		=> 'sort_key',
			'default'	=> $def_sk,
			'options'	=> $sort_by_text,
			'output'	=> &$s_sort_key,
		),

		'sd'	=> array(
			'key'		=> 'sort_dir',
			'default'	=> $def_sd,
			'options'	=> $sort_dir_text,
			'output'	=> &$s_sort_dir,
		),
	);
	$u_sort_param  = '';

	foreach ($sorts as $name => $sort_ary)
	{
		$key = $sort_ary['key'];
		$selected = ${$sort_ary['key']};;

		// Check if the key is selectable. If not, we reset to the default or first key found.
		// This ensures the values are always valid. We also set $sort_dir/sort_key/etc. to the
		// correct value, else the protection is void. ;)
		if (!isset($sort_ary['options'][$selected]))
		{
			if ($sort_ary['default'] !== false)
			{
				$selected = ${$key} = $sort_ary['default'];
			}
			else
			{
				@reset($sort_ary['options']);
				$selected = ${$key} = key($sort_ary['options']);
			}
		}

		$sort_ary['output'] = '<select name="' . $name . '" id="' . $name . '">';
		foreach ($sort_ary['options'] as $option => $text)
		{
			$sort_ary['output'] .= '<option value="' . $option . '"' . (($selected == $option) ? ' selected="selected"' : '') . '>' . $text . '</option>';
		}
		$sort_ary['output'] .= '</select>';

		$u_sort_param .= ($selected !== $sort_ary['default']) ? ((strlen($u_sort_param)) ? '&amp;' : '') . "{$name}={$selected}" : '';
	}

	return;
}

/**
* Generate Jumpbox
*/
function make_jumpbox($action, $forum_id = false, $select_all = false, $acl_list = false, $force_display = false)
{
	global $config, $auth, $template, $user, $db;

	// We only return if the jumpbox is not forced to be displayed (in case it is needed for functionality)
	if (!$config['load_jumpbox'] && $force_display === false)
	{
		return;
	}

	$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
		FROM ' . FORUMS_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql, 600);

	$right = $padding = 0;
	$padding_store = array('0' => 0);
	$display_jumpbox = false;
	$iteration = 0;

	// Sometimes it could happen that forums will be displayed here not be displayed within the index page
	// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
	// If this happens, the padding could be "broken"

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['left_id'] < $right)
		{
			$padding++;
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			// Ok, if the $padding_store for this parent is empty there is something wrong. For now we will skip over it.
			// @todo digging deep to find out "how" this can happen.
			$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : $padding;
		}

		$right = $row['right_id'];

		if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
		{
			// Non-postable forum with no subforums, don't display
			continue;
		}

		if (!$auth->acl_get('f_list', $row['forum_id']))
		{
			// if the user does not have permissions to list this forum skip
			continue;
		}

		if ($acl_list && !$auth->acl_gets($acl_list, $row['forum_id']))
		{
			continue;
		}

		if (!$display_jumpbox)
		{
			$template->assign_block_vars('jumpbox_forums', array(
				'FORUM_ID'		=> ($select_all) ? 0 : -1,
				'FORUM_NAME'	=> ($select_all) ? $user->lang['ALL_FORUMS'] : $user->lang['SELECT_FORUM'],
				'S_FORUM_COUNT'	=> $iteration)
			);

			$iteration++;
			$display_jumpbox = true;
		}

		$template->assign_block_vars('jumpbox_forums', array(
			'FORUM_ID'		=> $row['forum_id'],
			'FORUM_NAME'	=> $row['forum_name'],
			'SELECTED'		=> ($row['forum_id'] == $forum_id) ? ' selected="selected"' : '',
			'S_FORUM_COUNT'	=> $iteration,
			'S_IS_CAT'		=> ($row['forum_type'] == FORUM_CAT) ? true : false,
			'S_IS_LINK'		=> ($row['forum_type'] == FORUM_LINK) ? true : false,
			'S_IS_POST'		=> ($row['forum_type'] == FORUM_POST) ? true : false)
		);

		for ($i = 0; $i < $padding; $i++)
		{
			$template->assign_block_vars('jumpbox_forums.level', array());
		}
		$iteration++;
	}
	$db->sql_freeresult($result);
	unset($padding_store);

	$template->assign_vars(array(
		'S_DISPLAY_JUMPBOX'	=> $display_jumpbox,
		'S_JUMPBOX_ACTION'	=> $action)
	);

	return;
}

/**
* Bump Topic Check - used by posting and viewtopic
*/
function bump_topic_allowed($forum_id, $topic_bumped, $last_post_time, $topic_poster, $last_topic_poster)
{
	global $config, $auth, $user;

	// Check permission and make sure the last post was not already bumped
	if (!$auth->acl_get('f_bump', $forum_id))
	{
		return false;
	}

	// Check bump time range, is the user really allowed to bump the topic at this time?
	$bump_time = ($config['bump_type'] == 'm') ? $config['bump_interval'] * 60 : (($config['bump_type'] == 'h') ? $config['bump_interval'] * 3600 : $config['bump_interval'] * 86400);

	// Check bump time
	if ($last_post_time + $bump_time > time())
	{
		return false;
	}

	// Check bumper, only topic poster and last poster are allowed to bump
	if ($topic_poster != $user->data['user_id'] && $last_topic_poster != $user->data['user_id'])
	{
		return false;
	}

	// A bump time of 0 will completely disable the bump feature... not intended but might be useful.
	return $bump_time;
}

/**
* Generates a text with approx. the specified length which contains the specified words and their context
*
* @param	string	$text	The full text from which context shall be extracted
* @param	string	$words	An array of words which should be contained in the result, has to be a valid part of a PCRE pattern (escape with preg_quote!)
* @param	int		$length	The desired length of the resulting text, however the result might be shorter or longer than this value
*
* @return	string			Context of the specified words separated by "..."
*/
function get_context($text, $words, $length = 400)
{
	// first replace all whitespaces with single spaces
	$text = preg_replace('/ +/', ' ', strtr($text, "\t\n\r\x0C ", '     '));

	// we need to turn the entities back into their original form, to not cut the message in between them
	$entities = array('&lt;', '&gt;', '&#91;', '&#93;', '&#46;', '&#58;', '&#058;');
	$characters = array('<', '>', '[', ']', '.', ':', ':');
	$text = str_replace($entities, $characters, $text);

	$word_indizes = array();
	if (sizeof($words))
	{
		$match = '';
		// find the starting indizes of all words
		foreach ($words as $word)
		{
			if ($word)
			{
				if (preg_match('#(?:[^\w]|^)(' . $word . ')(?:[^\w]|$)#i', $text, $match))
				{
					if (empty($match[1]))
					{
						continue;
					}

					$pos = utf8_strpos($text, $match[1]);
					if ($pos !== false)
					{
						$word_indizes[] = $pos;
					}
				}
			}
		}
		unset($match);

		if (sizeof($word_indizes))
		{
			$word_indizes = array_unique($word_indizes);
			sort($word_indizes);

			$wordnum = sizeof($word_indizes);
			// number of characters on the right and left side of each word
			$sequence_length = (int) ($length / (2 * $wordnum)) - 2;
			$final_text = '';
			$word = $j = 0;
			$final_text_index = -1;

			// cycle through every character in the original text
			for ($i = $word_indizes[$word], $n = utf8_strlen($text); $i < $n; $i++)
			{
				// if the current position is the start of one of the words then append $sequence_length characters to the final text
				if (isset($word_indizes[$word]) && ($i == $word_indizes[$word]))
				{
					if ($final_text_index < $i - $sequence_length - 1)
					{
						$final_text .= '... ' . preg_replace('#^([^ ]*)#', '', utf8_substr($text, $i - $sequence_length, $sequence_length));
					}
					else
					{
						// if the final text is already nearer to the current word than $sequence_length we only append the text
						// from its current index on and distribute the unused length to all other sequenes
						$sequence_length += (int) (($final_text_index - $i + $sequence_length + 1) / (2 * $wordnum));
						$final_text .= utf8_substr($text, $final_text_index + 1, $i - $final_text_index - 1);
					}
					$final_text_index = $i - 1;

					// add the following characters to the final text (see below)
					$word++;
					$j = 1;
				}

				if ($j > 0)
				{
					// add the character to the final text and increment the sequence counter
					$final_text .= utf8_substr($text, $i, 1);
					$final_text_index++;
					$j++;

					// if this is a whitespace then check whether we are done with this sequence
					if (utf8_substr($text, $i, 1) == ' ')
					{
						// only check whether we have to exit the context generation completely if we haven't already reached the end anyway
						if ($i + 4 < $n)
						{
							if (($j > $sequence_length && $word >= $wordnum) || utf8_strlen($final_text) > $length)
							{
								$final_text .= ' ...';
								break;
							}
						}
						else
						{
							// make sure the text really reaches the end
							$j -= 4;
						}

						// stop context generation and wait for the next word
						if ($j > $sequence_length)
						{
							$j = 0;
						}
					}
				}
			}
			return str_replace($characters, $entities, $final_text);
		}
	}

	if (!sizeof($words) || !sizeof($word_indizes))
	{
		return str_replace($characters, $entities, ((utf8_strlen($text) >= $length + 3) ? utf8_substr($text, 0, $length) . '...' : $text));
	}
}

/**
* Cleans a search string by removing single wildcards from it and replacing multiple spaces with a single one.
*
* @param string $search_string The full search string which should be cleaned.
*
* @return string The cleaned search string without any wildcards and multiple spaces.
*/
function phpbb_clean_search_string($search_string)
{
	// This regular expressions matches every single wildcard.
	// That means one after a whitespace or the beginning of the string or one before a whitespace or the end of the string.
	$search_string = preg_replace('#(?<=^|\s)\*+(?=\s|$)#', '', $search_string);
	$search_string = trim($search_string);
	$search_string = preg_replace(array('#\s+#u', '#\*+#u'), array(' ', '*'), $search_string);
	return $search_string;
}

/**
* Decode text whereby text is coming from the db and expected to be pre-parsed content
* We are placing this outside of the message parser because we are often in need of it...
*/
function decode_message(&$message, $bbcode_uid = '')
{
	global $config;

	if ($bbcode_uid)
	{
		$match = array('<br />', "[/*:m:$bbcode_uid]", ":u:$bbcode_uid", ":o:$bbcode_uid", ":$bbcode_uid");
		$replace = array("\n", '', '', '', '');
	}
	else
	{
		$match = array('<br />');
		$replace = array("\n");
	}

	$message = str_replace($match, $replace, $message);

	$match = get_preg_expression('bbcode_htm');
	$replace = array('\1', '\1', '\2', '\1', '', '');

	$message = preg_replace($match, $replace, $message);
}

/**
* Strips all bbcode from a text and returns the plain content
*/
function strip_bbcode(&$text, $uid = '')
{
	if (!$uid)
	{
		$uid = '[0-9a-z]{5,}';
	}

	$text = preg_replace("#\[\/?[a-z0-9\*\+\-]+(?:=(?:&quot;.*&quot;|[^\]]*))?(?::[a-z])?(\:$uid)\]#", ' ', $text);

	$match = get_preg_expression('bbcode_htm');
	$replace = array('\1', '\1', '\2', '\1', '', '');

	$text = preg_replace($match, $replace, $text);
}

/**
* For display of custom parsed text on user-facing pages
* Expects $text to be the value directly from the database (stored value)
*/
function generate_text_for_display($text, $uid, $bitfield, $flags)
{
	static $bbcode;

	if ($text === '')
	{
		return '';
	}

	$text = censor_text($text);

	// Parse bbcode if bbcode uid stored and bbcode enabled
	if ($uid && ($flags & OPTION_FLAG_BBCODE))
	{
		if (!class_exists('bbcode'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
		}

		if (empty($bbcode))
		{
			$bbcode = new bbcode();
		}

		$bbcode->set_bitfield($bitfield);
		$bbcode->bbcode_second_pass($text, $uid);
	}

	$text = bbcode_nl2br($text);
	$text = smiley_text($text, !($flags & OPTION_FLAG_SMILIES));

	return $text;
}

/**
* For parsing custom parsed text to be stored within the database.
* This function additionally returns the uid and bitfield that needs to be stored.
* Expects $text to be the value directly from request_var() and in it's non-parsed form
*/
function generate_text_for_storage(&$text, &$uid, &$bitfield, &$flags, $allow_bbcode = false, $allow_urls = false, $allow_smilies = false)
{
	global $phpbb_root_path, $phpEx;

	$uid = $bitfield = '';
	$flags = (($allow_bbcode) ? OPTION_FLAG_BBCODE : 0) + (($allow_smilies) ? OPTION_FLAG_SMILIES : 0) + (($allow_urls) ? OPTION_FLAG_LINKS : 0);

	if ($text === '')
	{
		return;
	}

	if (!class_exists('parse_message'))
	{
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
	}

	$message_parser = new parse_message($text);
	$message_parser->parse($allow_bbcode, $allow_urls, $allow_smilies);

	$text = $message_parser->message;
	$uid = $message_parser->bbcode_uid;

	// If the bbcode_bitfield is empty, there is no need for the uid to be stored.
	if (!$message_parser->bbcode_bitfield)
	{
		$uid = '';
	}

	$bitfield = $message_parser->bbcode_bitfield;

	return;
}

/**
* For decoding custom parsed text for edits as well as extracting the flags
* Expects $text to be the value directly from the database (pre-parsed content)
*/
function generate_text_for_edit($text, $uid, $flags)
{
	global $phpbb_root_path, $phpEx;

	decode_message($text, $uid);

	return array(
		'allow_bbcode'	=> ($flags & OPTION_FLAG_BBCODE) ? 1 : 0,
		'allow_smilies'	=> ($flags & OPTION_FLAG_SMILIES) ? 1 : 0,
		'allow_urls'	=> ($flags & OPTION_FLAG_LINKS) ? 1 : 0,
		'text'			=> $text
	);
}

/**
* Function for make_clickable_callback and bbcode::bbcode_second_pass_url
*/
function get_attrs_for_external_link($url)
{
	global $config;

	if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) return '';
	$maxpos = strpos($url, '/', 8);
	if (!$maxpos) $maxpos = strlen($url);

	$newwindow = !empty($config['external_links_newwindow']);
	if ($newwindow)
	{
		static $newwindow_exclude;
		if (!is_array($newwindow_exclude))
		{
			$newwindow_exclude = empty($config['external_links_newwindow_exclude']) ? array() : explode("\n", str_replace(array("\r\n", ','), "\n", $config['external_links_newwindow_exclude']));
			$newwindow_exclude = array_filter(array_map('trim', $newwindow_exclude));
		}

		foreach ($newwindow_exclude as $prefix)
		{
			$pos = stripos($url, $prefix);
			if ($pos !== false && $pos < $maxpos)
			{
				$newwindow = false;
				break;
			}
		}
	}

	$nofollow = !empty($config['external_links_nofollow']);
	if ($nofollow)
	{
		static $nofollow_exclude;
		if (!is_array($nofollow_exclude))
		{
			$nofollow_exclude = empty($config['external_links_nofollow_exclude']) ? array() : explode("\n", str_replace(array("\r\n", ','), "\n", $config['external_links_nofollow_exclude']));
			$nofollow_exclude = array_filter(array_map('trim', $nofollow_exclude));
		}

		foreach ($nofollow_exclude as $prefix)
		{
			$pos = stripos($url, $prefix);
			if ($pos !== false && $pos < $maxpos)
			{
				$nofollow = false;
				break;
			}
		}
	}

	return ($newwindow ? ' target="_blank"' : '') . ($nofollow ? ' rel="nofollow"' : '');
}

/**
* A subroutine of make_clickable used with preg_replace
* It places correct HTML around an url, shortens the displayed text
* and makes sure no entities are inside URLs
*/
function make_clickable_callback($type, $whitespace, $url, $server_url)
{
	$attrs			= '';
	$append			= '';
	$url			= htmlspecialchars_decode($url);

	// make sure no HTML entities were matched
	$split = strcspn($url, '<>"');

	if ($split !== strlen($url))
	{
		// an HTML entity was found, so the URL has to end before it
		$append			= substr($url, $split);
		$url			= substr($url, 0, $split);
	}

	// if the last character of the url is a punctuation mark, exclude it from the url
	$last_char = $url[strlen($url) - 1];

	switch ($last_char)
	{
		case '.':
		case '?':
		case '!':
		case ':':
		case ',':
			$append = $last_char;
			$url = substr($url, 0, -1);
		break;

		// set last_char to empty here, so the variable can be used later to
		// check whether a character was removed
		default:
			$last_char = '';
		break;
	}

	$text = urldecode($url);

	switch ($type)
	{
		case MAGIC_URL_WWW:
			$url	= 'http://' . $url;

		case MAGIC_URL_FULL:
			if (in_array(strtolower($url), array('http://', 'https://')))
			{
				return $whitespace . $url . $append;
			}
			$external = stripos(preg_replace('#^https?://#i', '', $url), preg_replace('#^https?://#i', '', $server_url)) !== 0;
			if ($external)
			{
				$tag		= ($type == MAGIC_URL_WWW) ? 'w' : 'm';
				$attrs		= ' class="postlink"' . get_attrs_for_external_link($url);
			}
			else
			{
				$tag		= ($type == MAGIC_URL_WWW) ? 'w' : 'l';
				$attrs		= ' class="postlink local"';
				$url		= preg_replace('/[&?]sid=[0-9a-f]{32}$/', '', preg_replace('/([&?])sid=[0-9a-f]{32}&/', '$1', $url));
				$text		= urldecode($url);
			}
		break;

		case MAGIC_URL_EMAIL:
			$tag	= 'e';
			$url	= 'mailto:' . $url;
			$attrs	= ' class="postlink"';
		break;
	}

	if (utf8_strlen($text) > 85) $text = utf8_substr($text, 0, 49) . ' ... ' . utf8_substr($text, -30);

	$url	= htmlspecialchars($url);
	$text	= htmlspecialchars($text);
	$append	= htmlspecialchars($append);

	$html	= "$whitespace<!-- $tag --><a$attrs href=\"$url\">$text</a><!-- $tag -->$append";

	return $html;
}

/**
* make_clickable function
*
* Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
* Cuts down displayed size of link if over 50 chars, turns absolute links
* into relative versions when the server/script path matches the link
*/
function make_clickable($text, $server_url = false, $class = 'postlink')
{
	if ($server_url === false)
	{
		$server_url = generate_board_url(true);
	}

	// matches a xxxx://aaaaa.bbb.cccc. ...
	$text = preg_replace_callback('#(^|[\n\t (>.])(' . get_preg_expression('url_inline') . ')#iu', function ($m) use ($server_url) { return make_clickable_callback(MAGIC_URL_FULL, $m[1], $m[2], $server_url); }, $text);
	// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	$text = preg_replace_callback('#(^|[\n\t (>])(' . get_preg_expression('www_url_inline') . ')#iu', function ($m) use ($server_url) { return make_clickable_callback(MAGIC_URL_WWW, $m[1], $m[2], $server_url); }, $text);
	// matches an email@domain type address at the start of a line, or after a space or after what might be a BBCode.
	$text = preg_replace_callback('/(^|[\n\t (>])(' . get_preg_expression('email') . ')/i', function ($m) use ($server_url) { return make_clickable_callback(MAGIC_URL_EMAIL, $m[1], $m[2], $server_url); }, $text);

	return $text;
}

/**
* Censoring
*/
function censor_text($text)
{
	static $censors;

	// Nothing to do?
	if ($text === '')
	{
		return '';
	}

	// We moved the word censor checks in here because we call this function quite often - and then only need to do the check once
	if (!isset($censors) || !is_array($censors))
	{
		global $config, $user, $auth, $cache;

		// We check here if the user is having viewing censors disabled (and also allowed to do so).
		if (!$user->optionget('viewcensors') && $config['allow_nocensors'] && $auth->acl_get('u_chgcensors'))
		{
			$censors = array();
		}
		else
		{
			$censors = $cache->obtain_word_list();
		}
	}

	if (sizeof($censors))
	{
		return preg_replace($censors['match'], $censors['replace'], $text);
	}

	return $text;
}

/**
* custom version of nl2br which takes custom BBCodes into account
*/
function bbcode_nl2br($text)
{
	// custom BBCodes might contain carriage returns so they
	// are not converted into <br /> so now revert that
	$text = str_replace(array("\n", "\r"), array('<br />', "\n"), $text);
	return $text;
}

/**
* Smiley processing
*/
function smiley_text($text, $force_option = false)
{
	global $config, $user, $phpbb_root_path;

	if ($force_option || !$config['allow_smilies'] || !$user->optionget('viewsmilies'))
	{
		return preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $text);
	}
	else
	{
		$root_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $phpbb_root_path;
		return preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/(.*?) \/><!\-\- s\1 \-\->#', '<img src="' . $root_path . SMILIES_PATH . '/\2 />', $text);
	}
}

function get_attachment_mime($category, $extension)
{
	if ($category == ATTACHMENT_CATEGORY_AUDIO)
	{
		switch ($extension)
		{
			case 'ogg':
			case 'oga':
				return 'audio/ogg';
			case 'mp4':
			case 'm4a':
				return 'audio/mp4';
			case 'webm':
			case 'webma':
				return 'audio/webm';
			case 'opus':
				return 'audio/opus';
			case 'flac':
				return 'audio/flac';
			case 'mp1':
			case 'mp2':
			case 'mp3':
			case 'mpg':
			case 'mpeg':
				return 'audio/mpeg';
			case 'wav':
				return 'audio/wav';
		}
	}

	if ($category == ATTACHMENT_CATEGORY_VIDEO)
	{
		switch ($extension)
		{
			case 'ogg':
			case 'ogv':
				return 'video/ogg';
			case 'mp4':
			case 'm4v':
				return 'video/mp4';
			case 'webm':
			case 'webmv':
				return 'video/webm';
		}
	}

	return false;
}

/**
* General attachment parsing
*
* @param mixed $forum_id The forum id the attachments are displayed in (false if in private message)
* @param string &$message The post/private message
* @param array &$attachments The attachments to parse for (inline) display. The attachments array will hold templated data after parsing.
* @param array &$update_count The attachment counts to be updated - will be filled
* @param bool $preview If set to true the attachments are parsed for preview. Within preview mode the comments are fetched from the given $attachments array and not fetched from the database.
*/
function parse_attachments($forum_id, &$message, &$attachments, &$update_count, $preview = false)
{
	if (!sizeof($attachments))
	{
		return;
	}

	global $template, $cache, $user;
	global $extensions, $config, $phpbb_root_path, $phpEx;

	//
	$compiled_attachments = array();

	if (!isset($template->filename['attachment_tpl']))
	{
		$template->set_filenames(array(
			'attachment_tpl'	=> 'attachment.html')
		);
	}

	if (empty($extensions) || !is_array($extensions))
	{
		$extensions = $cache->obtain_attach_extensions($forum_id);
	}

	// Look for missing attachment information...
	$attach_ids = array();
	foreach ($attachments as $pos => $attachment)
	{
		// If is_orphan is set, we need to retrieve the attachments again...
		if (!isset($attachment['extension']) && !isset($attachment['physical_filename']))
		{
			$attach_ids[(int) $attachment['attach_id']] = $pos;
		}
	}

	// Grab attachments (security precaution)
	if (sizeof($attach_ids))
	{
		global $db;

		$new_attachment_data = array();

		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('attach_id', array_keys($attach_ids));
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (!isset($attach_ids[$row['attach_id']]))
			{
				continue;
			}

			// If we preview attachments we will set some retrieved values here
			if ($preview)
			{
				$row['attach_comment'] = $attachments[$attach_ids[$row['attach_id']]]['attach_comment'];
			}

			$new_attachment_data[$attach_ids[$row['attach_id']]] = $row;
		}
		$db->sql_freeresult($result);

		$attachments = $new_attachment_data;
		unset($new_attachment_data);
	}

	// Sort correctly
	if ($config['display_order'])
	{
		// Ascending sort
		krsort($attachments);
	}
	else
	{
		// Descending sort
		ksort($attachments);
	}

	foreach ($attachments as $attachment)
	{
		if (!sizeof($attachment))
		{
			continue;
		}

		// We need to reset/empty the _file block var, because this function might be called more than once
		$template->destroy_block_vars('_file');

		$block_array = array();

		// Some basics...
		$attachment['extension'] = strtolower(trim($attachment['extension']));
		$filename = $phpbb_root_path . UPLOADS_PATH . '/' . utf8_basename($attachment['physical_filename']);
		$thumbnail_filename = $phpbb_root_path . UPLOADS_PATH . '/thumb_' . utf8_basename($attachment['physical_filename']);

		$upload_icon = '';
		if (isset($extensions[$attachment['extension']]) && $extensions[$attachment['extension']]['upload_icon'])
		{
			$upload_icon = '<img src="' . $phpbb_root_path . FILE_ICONS_PATH . '/' . trim($extensions[$attachment['extension']]['upload_icon']) . '" />';
		}

		$filesize = get_formatted_filesize($attachment['filesize'], false);

		$comment = bbcode_nl2br(censor_text($attachment['attach_comment']));

		$block_array += array(
			'UPLOAD_ICON'		=> $upload_icon,
			'FILESIZE'			=> $filesize['value'],
			'SIZE_LANG'			=> $filesize['unit'],
			'DOWNLOAD_NAME'		=> utf8_basename($attachment['real_filename']),
			'COMMENT'			=> $comment,
		);

		$denied = false;

		if (!extension_allowed($forum_id, $attachment['extension'], $extensions))
		{
			$denied = true;

			$block_array += array(
				'S_DENIED'			=> true,
				'DENIED_MESSAGE'	=> sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension'])
			);
		}

		if (!$denied)
		{
			$l_downloaded_viewed = $download_link = '';
			$display_cat = $extensions[$attachment['extension']]['display_cat'];
			if ($display_cat >= ATTACHMENT_CATEGORY_COUNT) { $display_cat = ATTACHMENT_CATEGORY_NONE; }

			if ($display_cat == ATTACHMENT_CATEGORY_IMAGE)
			{
				if ($attachment['thumbnail'])
				{
					$display_cat = ATTACHMENT_CATEGORY_THUMB;
				}
				else
				{
					if ($config['img_display_inlined'])
					{
						if ($config['img_link_width'] || $config['img_link_height'])
						{
							$dimension = @getimagesize($filename);

							// If the dimensions could not be determined or the image being 0x0 we display it as a link for safety purposes
							if ($dimension === false || empty($dimension[0]) || empty($dimension[1]))
							{
								$display_cat = ATTACHMENT_CATEGORY_NONE;
							}
							else
							{
								$display_cat = ($dimension[0] <= $config['img_link_width'] && $dimension[1] <= $config['img_link_height']) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE;
							}
						}
					}
					else
					{
						$display_cat = ATTACHMENT_CATEGORY_NONE;
					}
				}
			}

			// Make some descisions based on user options being set.
			if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !$user->optionget('viewimg'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			$download_link = append_sid("{$phpbb_root_path}file.$phpEx", 'id=' . $attachment['attach_id'] . '&amp;filename=' . urlencode(utf8_basename($attachment['real_filename'])));

			switch ($display_cat)
			{
				// Images
				case ATTACHMENT_CATEGORY_IMAGE:
					$l_downloaded_viewed = 'VIEWED_COUNT';
					$inline_link = append_sid("{$phpbb_root_path}file.$phpEx", 'id=' . $attachment['attach_id'] . '&amp;filename=' . urlencode(utf8_basename($attachment['real_filename'])));
					$download_link .= '&amp;mode=view';

					$block_array += array(
						'S_IMAGE'		=> true,
						'U_INLINE_LINK'		=> $inline_link,
					);

					$update_count[] = $attachment['attach_id'];
				break;

				// Images, but display Thumbnail
				case ATTACHMENT_CATEGORY_THUMB:
					$l_downloaded_viewed = 'VIEWED_COUNT';
					$thumbnail_link = append_sid("{$phpbb_root_path}file.$phpEx", 'id=' . $attachment['attach_id'] . '&amp;t=1&amp;filename=' . urlencode(utf8_basename($attachment['real_filename'])));
					$download_link .= '&amp;mode=view';

					$block_array += array(
						'S_THUMBNAIL'		=> true,
						'THUMB_IMAGE'		=> $thumbnail_link,
					);

					$update_count[] = $attachment['attach_id'];
				break;

				// HTML5 <video> and <audio>
				case ATTACHMENT_CATEGORY_VIDEO:
				case ATTACHMENT_CATEGORY_AUDIO:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$block_array += array(
						'S_VIDEO_FILE'	=> ($display_cat == ATTACHMENT_CATEGORY_VIDEO) ? true : false,
						'S_AUDIO_FILE'	=> ($display_cat == ATTACHMENT_CATEGORY_AUDIO) ? true : false,
						'U_FORUM'		=> generate_board_url(),
						'ATTACH_ID'		=> $attachment['attach_id'],
						'MIME'			=> get_attachment_mime($display_cat, $attachment['extension']),
					);
				break;

				default:
					$l_downloaded_viewed = 'DOWNLOAD_COUNT';

					$block_array += array(
						'S_FILE'		=> true,
					);
				break;
			}

			$l_download_count = (!isset($attachment['download_count']) || $attachment['download_count'] == 0) ? $user->lang[$l_downloaded_viewed . '_NONE'] : (($attachment['download_count'] == 1) ? sprintf($user->lang[$l_downloaded_viewed], $attachment['download_count']) : sprintf($user->lang[$l_downloaded_viewed . 'S'], $attachment['download_count']));

			$block_array += array(
				'U_DOWNLOAD_LINK'		=> $download_link,
				'L_DOWNLOAD_COUNT'		=> $l_download_count
			);
		}

		$template->assign_var('ROOT_PATH', $phpbb_root_path);
		$template->assign_block_vars('_file', $block_array);

		$compiled_attachments[] = $template->assign_display('attachment_tpl');
	}

	$attachments = $compiled_attachments;
	unset($compiled_attachments);

	$tpl_size = sizeof($attachments);

	$unset_tpl = array();

	preg_match_all('#<!\-\- ia([0-9]+) \-\->(.*?)<!\-\- ia\1 \-\->#', $message, $matches, PREG_PATTERN_ORDER);

	$replace = array();
	foreach ($matches[0] as $num => $capture)
	{
		// Flip index if we are displaying the reverse way
		$index = ($config['display_order']) ? ($tpl_size-($matches[1][$num] + 1)) : $matches[1][$num];

		$replace['from'][] = $matches[0][$num];
		$replace['to'][] = (isset($attachments[$index])) ? $attachments[$index] : sprintf($user->lang['MISSING_INLINE_ATTACHMENT'], $matches[2][array_search($index, $matches[1])]);

		$unset_tpl[] = $index;
	}

	if (isset($replace['from']))
	{
		$message = str_replace($replace['from'], $replace['to'], $message);
	}

	$unset_tpl = array_unique($unset_tpl);

	// Needed to let not display the inlined attachments at the end of the post again
	foreach ($unset_tpl as $index)
	{
		unset($attachments[$index]);
	}
}

/**
* Check if extension is allowed to be posted.
*
* @param mixed $forum_id The forum id to check or false if private message
* @param string $extension The extension to check, for example zip.
* @param array &$extensions The extension array holding the information from the cache (will be obtained if empty)
*
* @return bool False if the extension is not allowed to be posted, else true.
*/
function extension_allowed($forum_id, $extension, &$extensions)
{
	if (empty($extensions))
	{
		global $cache;
		$extensions = $cache->obtain_attach_extensions($forum_id);
	}

	return (!isset($extensions['_allowed_'][$extension])) ? false : true;
}

/**
* Truncates string while retaining special characters if going over the max length
* The default max length is 60 at the moment
* The maximum storage length is there to fit the string within the given length. The string may be further truncated due to html entities.
* For example: string given is 'a "quote"' (length: 9), would be a stored as 'a &quot;quote&quot;' (length: 19)
*
* @param string $string The text to truncate to the given length. String is specialchared.
* @param int $max_length Maximum length of string (multibyte character count as 1 char / Html entity count as 1 char)
* @param int $max_store_length Maximum character length of string (multibyte character count as 1 char / Html entity count as entity chars).
* @param bool $allow_reply Allow Re: in front of string
* 	NOTE: This parameter can cause undesired behavior (returning strings longer than $max_store_length) and is deprecated.
* @param string $append String to be appended
*/
function truncate_string($string, $max_length = 60, $max_store_length = 255, $allow_reply = false, $append = '')
{
	$chars = array();

	$strip_reply = false;
	$stripped = false;
	if ($allow_reply && strpos($string, 'Re: ') === 0)
	{
		$strip_reply = true;
		$string = substr($string, 4);
	}

	$_chars = utf8_str_split(htmlspecialchars_decode($string));
	$chars = array_map('utf8_htmlspecialchars', $_chars);

	// Now check the length ;)
	if (sizeof($chars) > $max_length)
	{
		// Cut off the last elements from the array
		$string = implode('', array_slice($chars, 0, $max_length - utf8_strlen($append)));
		$stripped = true;
	}

	// Due to specialchars, we may not be able to store the string...
	if (utf8_strlen($string) > $max_store_length)
	{
		// let's split again, we do not want half-baked strings where entities are split
		$_chars = utf8_str_split(htmlspecialchars_decode($string));
		$chars = array_map('utf8_htmlspecialchars', $_chars);

		do
		{
			array_pop($chars);
			$string = implode('', $chars);
		}
		while (!empty($chars) && utf8_strlen($string) > $max_store_length);
	}

	if ($strip_reply)
	{
		$string = 'Re: ' . $string;
	}

	if ($append != '' && $stripped)
	{
		$string = $string . $append;
	}

	return $string;
}

/**
* Get username details for placing into templates.
* This function caches all modes on first call, except for no_profile and anonymous user - determined by $user_id.
*
* @param string $mode Can be profile (for getting an url to the profile), username (for obtaining the username), colour (for obtaining the user colour), full (for obtaining a html string representing a coloured link to the users profile) or no_profile (the same as full but forcing no profile link)
* @param int $user_id The users id
* @param string $username The users name
* @param string $username_colour The users colour
* @param string $guest_username optional parameter to specify the guest username. It will be used in favor of the GUEST language variable then.
* @param string $custom_profile_url optional parameter to specify a profile url. The user id get appended to this url as &amp;u={user_id}
*
* @return string A string consisting of what is wanted based on $mode.
* @author BartVB, Acyd Burn
*/
function get_username_string($mode, $user_id, $username, $username_colour = '', $guest_username = false, $custom_profile_url = false, $title = '')
{
	static $_profile_cache;

	// We cache some common variables we need within this function
	if (empty($_profile_cache))
	{
		global $phpbb_root_path, $phpEx;

		$_profile_cache['base_url'] = append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&amp;u={USER_ID}');
		$_profile_cache['tpl_noprofile'] = '{USERNAME}';
		$_profile_cache['tpl_noprofile_colour'] = '<span style="color: {USERNAME_COLOUR};" class="username-coloured">{USERNAME}</span>';
		$_profile_cache['tpl_profile'] = '<a rel="nofollow" href="{PROFILE_URL}">{USERNAME}</a>';
		$_profile_cache['tpl_profile_colour'] = '<a rel="nofollow" href="{PROFILE_URL}" style="color: {USERNAME_COLOUR};" class="username-coloured">{USERNAME}</a>';
		$_profile_cache['tpl_noprofile_title'] = '<span title="{TITLE}">{USERNAME}</span>';
		$_profile_cache['tpl_noprofile_colour_title'] = '<span title="{TITLE}" style="color: {USERNAME_COLOUR};" class="username-coloured">{USERNAME}</span>';
		$_profile_cache['tpl_profile_title'] = '<a rel="nofollow" href="{PROFILE_URL}" title="{TITLE}">{USERNAME}</a>';
		$_profile_cache['tpl_profile_colour_title'] = '<a rel="nofollow" href="{PROFILE_URL}" title="{TITLE}" style="color: {USERNAME_COLOUR};" class="username-coloured">{USERNAME}</a>';
	}

	global $user, $auth;

	// This switch makes sure we only run code required for the mode
	switch ($mode)
	{
		case 'full':
		case 'no_profile':
		case 'colour':

			// Build correct username colour
			$username_colour = ($username_colour) ? '#' . $username_colour : '';

			// Return colour
			if ($mode == 'colour')
			{
				return $username_colour;
			}

		// no break;

		case 'username':

			// Build correct username
			if ($guest_username === false)
			{
				$username = ($username) ? $username : $user->lang['GUEST'];
			}
			else
			{
				$username = ($user_id && $user_id != ANONYMOUS) ? $username : ((!empty($guest_username)) ? $guest_username : $user->lang['GUEST']);
			}

			// Return username
			if ($mode == 'username')
			{
				return $username;
			}

		// no break;

		case 'profile':

			// Build correct profile url - only show if not anonymous and permission to view profile if registered user
			// For anonymous the link leads to a login page.
			if ($user_id && $user_id != ANONYMOUS && ($user->data['user_id'] == ANONYMOUS || $auth->acl_get('u_viewprofile')))
			{
				$profile_url = ($custom_profile_url !== false) ? $custom_profile_url . '&amp;u=' . (int) $user_id : str_replace(array('={USER_ID}', '=%7BUSER_ID%7D'), '=' . (int) $user_id, $_profile_cache['base_url']);
			}
			else
			{
				$profile_url = '';
			}

			// Return profile
			if ($mode == 'profile')
			{
				return $profile_url;
			}

		// no break;
	}

	$postfix = empty($title) ? '' : '_title';

	if (($mode == 'full' && !$profile_url) || $mode == 'no_profile')
	{
		return str_replace(array('{USERNAME_COLOUR}', '{USERNAME}', '{TITLE}'), array($username_colour, $username, $title), (!$username_colour) ? $_profile_cache['tpl_noprofile'.$postfix] : $_profile_cache['tpl_noprofile_colour'.$postfix]);
	}

	return str_replace(array('{PROFILE_URL}', '{USERNAME_COLOUR}', '{USERNAME}', '{TITLE}'), array($profile_url, $username_colour, $username, $title), (!$username_colour) ? $_profile_cache['tpl_profile'.$postfix] : $_profile_cache['tpl_profile_colour'.$postfix]);
}

/**
* Get associative array with time delta.
*/
function get_verbal_time_delta_values($first_time, $last_time)
{
	if ($last_time < $first_time) { return false; }

	// Solve H:M:S part.
	$hms = ($last_time - $first_time) % (3600 * 24);
	$delta['seconds'] = $hms % 60;
	$delta['minutes'] = floor($hms/60) % 60;
	$delta['hours']   = floor($hms/3600) % 60;

	// Now work only with date, delta time = 0.
	$last_time -= $hms;
	$f = getdate($first_time);
	$l = getdate($last_time); // the same daytime as $first_time!

	$d_year = $d_mon = $d_day = 0;

	// Delta day. Is negative, month overlapping.
	$d_day += $l['mday'] - $f['mday'];
	if ($d_day < 0)
	{
		$mon_length = (int) date('t', $first_time);
		$d_day += $mon_length;
		$d_mon--;
	}
	$delta['mday'] = $d_day;

	// Delta month. If negative, year overlapping.
	$d_mon += $l['mon'] - $f['mon'];
	if ($d_mon < 0)
	{
		$d_mon += 12;
		$d_year--;
	}
	$delta['mon'] = $d_mon;

	// Delta year.
	$d_year += $l['year'] - $f['year'];
	$delta['year'] = $d_year;

	return $delta;
}

/**
* Spell result in appropriate form depending on integer value, i.e.: "1 answer", "2 answers", "13 answers", et cetera.
*/
function get_verbal_time_delta_declension($int, $expressions)
{
	$count = $int % 100;
	if ($count >= 5 && $count <= 20)
	{
		$result = $int . ' ' . $expressions[2];
	}
	else
	{
		$count = $count % 10;
		if ($count == 1)
		{
			$result = $int . ' ' . $expressions[0];
		}
		else if ($count >= 2 && $count <= 4)
		{
			$result = $int . ' ' . $expressions[1];
		}
		else
		{
			$result = $int . ' ' . $expressions[2];
		}
	}
	return $result;
}

/**
* Make a spellable phrase with time delta.
*/
function get_verbal_time_delta($first_time, $last_time, $accuracy = false, $max_parts = false, $keep_zeros = false)
{
	global $user;

	if ($first_time - $last_time === 0)
	{
		return get_verbal_time_delta_declension(0, $user->lang['D_SECONDS']);
	}

	$delta = get_verbal_time_delta_values($first_time, $last_time);
	if (!$delta) { return false; }

	$parts = array();
	$parts_count = 0;
	foreach (array_reverse($delta) as $measure => $value)
	{
		if ($max_parts && $max_parts <= $parts_count)
		{
			break;
		}
		if (!$value && (!$keep_zeros || !$parts_count))
		{
			if ($measure !== $accuracy)
			{
				if ($parts_count) $parts_count++;
				continue;
			}
			else if (count($parts))
			{
				break;
			}
		}
		$parts_count++;
		$parts[] = get_verbal_time_delta_declension($value, $user->lang['D_' . strtoupper($measure)]);
		if ($measure === $accuracy)
		{
			break;
		}
	}
	return join(' ', $parts);
}

/**
* @package phpBB3
*/
class bitfield
{
	var $data;

	function __construct($bitfield = '')
	{
		$this->data = base64_decode($bitfield);
	}

	/**
	*/
	function get($n)
	{
		// Get the ($n / 8)th char
		$byte = $n >> 3;

		if (strlen($this->data) >= $byte + 1)
		{
			$c = $this->data[$byte];

			// Lookup the ($n % 8)th bit of the byte
			$bit = 7 - ($n & 7);
			return (bool) (ord($c) & (1 << $bit));
		}
		else
		{
			return false;
		}
	}

	function set($n)
	{
		$byte = $n >> 3;
		$bit = 7 - ($n & 7);

		if (strlen($this->data) >= $byte + 1)
		{
			$this->data[$byte] = $this->data[$byte] | chr(1 << $bit);
		}
		else
		{
			$this->data .= str_repeat("\0", $byte - strlen($this->data));
			$this->data .= chr(1 << $bit);
		}
	}

	function clear($n)
	{
		$byte = $n >> 3;

		if (strlen($this->data) >= $byte + 1)
		{
			$bit = 7 - ($n & 7);
			$this->data[$byte] = $this->data[$byte] &~ chr(1 << $bit);
		}
	}

	function get_blob()
	{
		return $this->data;
	}

	function get_base64()
	{
		return base64_encode($this->data);
	}

	function get_bin()
	{
		$bin = '';
		$len = strlen($this->data);

		for ($i = 0; $i < $len; ++$i)
		{
			$bin .= str_pad(decbin(ord($this->data[$i])), 8, '0', STR_PAD_LEFT);
		}

		return $bin;
	}

	function get_all_set()
	{
		return array_keys(array_filter(str_split($this->get_bin())));
	}

	function merge($bitfield)
	{
		$this->data = $this->data | $bitfield->get_blob();
	}
}
