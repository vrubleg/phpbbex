<?php
/**
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_gallery_misc
{
	static public function display_captcha($mode)
	{
		static $gallery_display_captcha;

		if (isset($gallery_display_captcha[$mode]))
		{
			return $gallery_display_captcha[$mode];
		}

		global $config, $user;

		$gallery_display_captcha[$mode] = ($user->data['user_id'] == ANONYMOUS) && phpbb_gallery_config::get('captcha_' . $mode);

		return $gallery_display_captcha[$mode];
	}

	static public function not_authorised($backlink, $loginlink = '', $login_explain = '')
	{
		global $user;

		if (!$user->data['is_registered'] && $loginlink)
		{
			if ($login_explain && isset($user->lang[$login_explain]))
			{
				$login_explain = $user->lang[$login_explain];
			}
			else
			{
				$login_explain = '';
			}
			login_box($loginlink, $login_explain);
		}
		else
		{
			meta_refresh(3, $backlink);
			trigger_error('NOT_AUTHORISED');
		}
	}

	/**
	* Mark albums as read
	*
	*/
	static public function mark_read_albums($album_ids)
	{
		global $db, $user;

		if ($user->data['user_id'] == ANONYMOUS || !$album_ids)
		{
			return;
		}

		$album_ids = array_map('intval', $album_ids);
		$mark_time = time();
		$user_id = (int) $user->data['user_id'];
		$values = [];
		foreach (array_unique($album_ids) as $album_id)
		{
			$values[] = "({$user_id}, {$album_id}, {$mark_time})";
		}

		$sql = 'INSERT INTO ' . GALLERY_ATRACK_TABLE . ' (user_id, album_id, mark_time)
			VALUES ' . implode(', ', $values) . '
			ON DUPLICATE KEY UPDATE mark_time = ' . $mark_time;
		$db->sql_query($sql);
	}

	/**
	* Mark every album as read for a user
	*/
	static public function mark_read_all($time = 0, $user_id = 0)
	{
		global $db, $user;

		$user_id = $user_id ? (int) $user_id : (int) $user->data['user_id'];
		if (!$user_id || $user_id == ANONYMOUS)
		{
			return;
		}
		$time = $time ? (int) $time : time();

		$sql = 'DELETE FROM ' . GALLERY_ATRACK_TABLE . '
			WHERE user_id = ' . $user_id;
		$db->sql_query($sql);

		$sql = 'UPDATE ' . GALLERY_USERS_TABLE . '
			SET user_mark_time = ' . $time . '
			WHERE user_id = ' . $user_id;
		$db->sql_query($sql);
	}

	/**
	* Mark gallery albums read up to the last visit when the user has no sessions.
	*/
	static public function auto_mark_read_all()
	{
		global $db, $config;

		$sql = 'SELECT gu.user_id, gu.user_last_visit
			FROM ' . GALLERY_USERS_TABLE . ' gu
			WHERE gu.user_id <> ' . ANONYMOUS . '
				AND gu.user_last_visit > gu.user_mark_time
				AND gu.user_last_visit <= ' . (time() - (int) $config['auto_mark_read_delay']) . '
				AND NOT EXISTS (
					SELECT 1 FROM ' . SESSIONS_TABLE . ' s
					WHERE s.session_user_id = gu.user_id
				)';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			self::mark_read_all((int) $row['user_last_visit'], (int) $row['user_id']);
		}
		$db->sql_freeresult($result);
	}
}
