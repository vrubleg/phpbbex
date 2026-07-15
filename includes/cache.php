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
* Class for grabbing/handling cached entries, extends acm_file or acm_db depending on the setup
*/
class phpbb_cache extends acm
{
	/**
	* Get config values
	*/
	function obtain_config()
	{
		global $db;

		if (($config = $this->get('config')) !== false)
		{
			$sql = 'SELECT config_name, config_value
				FROM ' . CONFIG_TABLE . '
				WHERE is_dynamic = 1';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$config[$row['config_name']] = $row['config_value'];
			}
			$db->sql_freeresult($result);
		}
		else
		{
			$config = $cached_config = [];

			$sql = 'SELECT config_name, config_value, is_dynamic
				FROM ' . CONFIG_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if (!$row['is_dynamic'])
				{
					$cached_config[$row['config_name']] = $row['config_value'];
				}

				$config[$row['config_name']] = $row['config_value'];
			}
			$db->sql_freeresult($result);

			$this->put('config', $cached_config);
		}

		return $config;
	}

	/**
	* Obtain list of naughty words and build preg style replacement arrays for use by the
	* calling script
	*/
	function obtain_word_list()
	{
		global $db;

		if (($censors = $this->get('_word_censors')) === false)
		{
			$sql = 'SELECT word, replacement
				FROM ' . WORDS_TABLE;
			$result = $db->sql_query($sql);

			$censors = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$censors['match'][] = get_censor_preg_expression($row['word']);
				$censors['replace'][] = $row['replacement'];
			}
			$db->sql_freeresult($result);

			$this->put('_word_censors', $censors);
		}

		return $censors;
	}

	/**
	* Obtain currently listed icons
	*/
	function obtain_icons()
	{
		if (($icons = $this->get('_icons')) === false)
		{
			global $db;

			// Topic icons
			$sql = 'SELECT *
				FROM ' . ICONS_TABLE . '
				ORDER BY icons_order';
			$result = $db->sql_query($sql);

			$icons = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$icons[$row['icons_id']]['img'] = $row['icons_url'];
				$icons[$row['icons_id']]['width'] = (int) $row['icons_width'];
				$icons[$row['icons_id']]['height'] = (int) $row['icons_height'];
				$icons[$row['icons_id']]['display'] = (bool) $row['display_on_posting'];
			}
			$db->sql_freeresult($result);

			$this->put('_icons', $icons);
		}

		return $icons;
	}

	/**
	* Obtain ranks
	*/
	function obtain_ranks()
	{
		if (($ranks = $this->get('_ranks')) === false)
		{
			global $db;

			$sql = 'SELECT *
				FROM ' . RANKS_TABLE . '
				ORDER BY rank_min DESC';
			$result = $db->sql_query($sql);

			$ranks = ['special' => [], 'normal'=> []];

			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['rank_special'])
				{
					$ranks['special'][$row['rank_id']] = [
						'rank_title'        => $row['rank_title'],
						'rank_hide_title'   => $row['rank_hide_title'],
						'rank_image'        => $row['rank_image']
					];
				}
				else
				{
					$ranks['normal'][] = [
						'rank_title'        => $row['rank_title'],
						'rank_hide_title'   => $row['rank_hide_title'],
						'rank_image'        => $row['rank_image'],
						'rank_min'          => $row['rank_min'],
					];
				}
			}
			$db->sql_freeresult($result);

			$this->put('_ranks', $ranks);
		}

		return $ranks;
	}

	/**
	* Obtain allowed extensions
	*
	* @param mixed $forum_id If false then check for private messaging, if int then check for forum id. If true, then only return extension informations.
	*
	* @return array allowed extensions array.
	*/
	function obtain_attach_extensions($forum_id)
	{
		if (($extensions = $this->get('_extensions')) === false)
		{
			global $db;

			$extensions = [
				'_allowed_post' => [],
				'_allowed_pm'   => [],
			];

			// The rule is to only allow those extensions defined. ;)
			$sql = 'SELECT e.extension, g.*
				FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
				WHERE e.group_id = g.group_id
					AND (g.allow_group = 1 OR g.allow_in_pm = 1)';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$extension = strtolower(trim($row['extension']));

				$extensions[$extension] = [
					'display_cat'   => ($row['cat_id'] < ATTACHMENT_CATEGORY_COUNT) ? intval($row['cat_id']) : ATTACHMENT_CATEGORY_NONE,
					'download_mode' => (int) $row['download_mode'],
					'upload_icon'   => trim($row['upload_icon']),
					'max_filesize'  => (int) $row['max_filesize'],
					'allow_group'   => $row['allow_group'],
					'allow_in_pm'   => $row['allow_in_pm'],
				];

				$allowed_forums = ($row['allowed_forums']) ? unserialize(trim($row['allowed_forums'])) : [];

				// Store allowed extensions forum wise
				if ($row['allow_group'])
				{
					$extensions['_allowed_post'][$extension] = (!sizeof($allowed_forums)) ? 0 : $allowed_forums;
				}

				if ($row['allow_in_pm'])
				{
					$extensions['_allowed_pm'][$extension] = 0;
				}
			}
			$db->sql_freeresult($result);

			$this->put('_extensions', $extensions);
		}

		// Forum post
		if ($forum_id === false)
		{
			// We are checking for private messages, therefore we only need to get the pm extensions...
			$return = ['_allowed_' => []];

			foreach ($extensions['_allowed_pm'] as $extension => $check)
			{
				$return['_allowed_'][$extension] = 0;
				$return[$extension] = $extensions[$extension];
			}

			$extensions = $return;
		}
		else if ($forum_id === true)
		{
			return $extensions;
		}
		else
		{
			$forum_id = (int) $forum_id;
			$return = ['_allowed_' => []];

			foreach ($extensions['_allowed_post'] as $extension => $check)
			{
				// Check for allowed forums
				if (is_array($check))
				{
					$allowed = in_array($forum_id, $check);
				}
				else
				{
					$allowed = true;
				}

				if ($allowed)
				{
					$return['_allowed_'][$extension] = 0;
					$return[$extension] = $extensions[$extension];
				}
			}

			$extensions = $return;
		}

		if (!isset($extensions['_allowed_']))
		{
			$extensions['_allowed_'] = [];
		}

		return $extensions;
	}

	/**
	* Obtain active bots
	*/
	function obtain_bots()
	{
		if (($bots = $this->get('_bots')) === false)
		{
			global $db;

			$sql = 'SELECT bot_id, bot_agent, bot_ip
				FROM ' . BOTS_TABLE . '
				WHERE bot_active = 1
			ORDER BY LENGTH(bot_agent) DESC';
			$result = $db->sql_query($sql);

			$bots = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$bots[] = $row;
			}
			$db->sql_freeresult($result);

			$this->put('_bots', $bots);
		}

		return $bots;
	}

	/**
	* Obtain style cfg file data
	*/
	function obtain_style_cfg($style_dir, $type, $lang = '')
	{
		global $config;

		$cache_key = '_style_' . $style_dir
			. (($type != 'style') ? '_' . $type : '')
			. ($lang ? '_' . $lang : '')
			. '_cfg';

		$cfg_data = $this->get($cache_key) ?: [];

		if (empty($cfg_data['mtime']) || !empty($config['cache_mtime_check']))
		{
			$cfg_file = PHPBB_ROOT_PATH . 'styles/' . $style_dir
				. (($type != 'style') ? '/' . $type : '')
				. ($lang ? '/' . $lang : '')
				. '/' . $type . '.cfg';

			$cfg_mtime = @filemtime($cfg_file);

			if ($cfg_mtime === false)
			{
				if ($cfg_data)
				{
					$this->destroy($cache_key);
				}
				return [];
			}

			if (empty($cfg_data['mtime']) || (!empty($config['cache_mtime_check']) && $cfg_mtime > $cfg_data['mtime']))
			{
				$cfg_data = parse_cfg_file($cfg_file);
				$cfg_data['mtime'] = $cfg_mtime;
				$this->put($cache_key, $cfg_data);
			}
		}

		return $cfg_data;
	}

	/**
	* Obtain imageset data from cfg files
	*/
	function obtain_style_imageset($style_dir, $lang = null)
	{
		global $config;

		$lang = $lang ?: $config['default_lang_code'];
		$cache_key = "_style_{$style_dir}_imageset_{$lang}";
		$data = $this->get($cache_key) ?: [];

		if (empty($data['mtime']) || !empty($config['cache_mtime_check']))
		{
			$base_cfg = $this->obtain_style_cfg($style_dir, 'imageset');

			if (!$base_cfg)
			{
				$this->destroy($cache_key);
				return [];
			}

			$mtime = $base_cfg['mtime'];
			unset($base_cfg['mtime']);

			$lang_cfg = $this->obtain_style_cfg($style_dir, 'imageset', $lang);

			if ($lang_cfg)
			{
				$mtime = max($mtime, $lang_cfg['mtime']);
				unset($lang_cfg['mtime']);
			}

			if (empty($data['mtime']) || (!empty($config['cache_mtime_check']) && $mtime > $data['mtime']))
			{
				$data = [];

				foreach (['' => $base_cfg, $lang => $lang_cfg] as $image_lang => $imageset_cfg)
				{
					foreach ($imageset_cfg as $image_name => $image_filename)
					{
						if (strpos($image_name, 'img_') !== 0)
						{
							continue;
						}
						$image_name = substr($image_name, 4);

						$image_height = $image_width = 0;
						if (strpos($image_filename, '*') !== false)
						{
							if ($image_filename[-1] === '*')
							{
								[$image_filename, $image_height] = explode('*', $image_filename);
							}
							else
							{
								[$image_filename, $image_height, $image_width] = explode('*', $image_filename);
							}
						}
						else if ($image_filename)
						{
							$image_path = PHPBB_ROOT_PATH . 'styles/' . $style_dir . '/imageset/' . ($image_lang ? $image_lang . '/' : '') . $image_filename;
							$image_size = @getimagesize($image_path);

							if ($image_size)
							{
								$image_width = (int) $image_size[0];
								$image_height = (int) $image_size[1];
							}
						}

						if ($image_filename)
						{
							$data[$image_name] = [
								'image_name'        => (string) $image_name,
								'image_filename'    => (string) $image_filename,
								'image_height'      => (int) $image_height,
								'image_width'       => (int) $image_width,
								'image_lang'        => (string) $image_lang,
							];
						}
					}
				}

				$data['mtime'] = $mtime;
				$this->put($cache_key, $data);
			}
		}

		unset($data['mtime']);
		return $data;
	}

	/**
	* Obtain disallowed usernames
	*/
	function obtain_disallowed_usernames()
	{
		if (($usernames = $this->get('_disallowed_usernames')) === false)
		{
			global $db;

			$sql = 'SELECT disallow_username
				FROM ' . DISALLOW_TABLE;
			$result = $db->sql_query($sql);

			$usernames = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$usernames[] = str_replace('%', '.*?', preg_quote(utf8_clean_string($row['disallow_username']), '#'));
			}
			$db->sql_freeresult($result);

			$this->put('_disallowed_usernames', $usernames);
		}

		return $usernames;
	}
}
