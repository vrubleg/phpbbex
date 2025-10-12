<?php
/**
* @package phpBBex Support Toolkit
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * The class that handles the resync of the registered users
 * group
 */
class resync_registered
{
	/**
	 * The `resync_user_groups` object
	 * @var resync_user_groups
	 */
	var $parent = null;

	/**
	 * Constructor
	 */
	function __construct($main_object)
	{
		$this->parent = $main_object;
	}

	/**
	 * Make sure that this process can/must be run
	 */
	function can_run()
	{
		return $this->_fetch_users(true);
	}

	/**
	 * Resync this group
	 */
	function resync()
	{
		global $config, $db;

		// Get the needed data
		$batch = $this->_fetch_users();
		$g = $this->_get_group_ids();

		$insert_reg = [];

		foreach ($batch as $row)
		{
			$insert_reg[] = [
				'group_id'		=> $g['REGISTERED'],
				'user_id'		=> $row['user_id'],
				'group_leader'	=> false,
				'user_pending'	=> false,
			];
		}

		$db->sql_multi_insert(USER_GROUP_TABLE, $insert_reg);
	}

	/**
	 * Grep the users that aren't in the groups
	 * @param  Boolean $missing If true this function will return whether there are users missing
	 */
	function _fetch_users($missing = false)
	{
		global $db;

		if (!function_exists('group_memberships'))
		{
			require_once(PHPBB_ROOT_PATH . 'includes/functions_user.php');
		}

		// Get teh group IDs
		$g = $this->_get_group_ids();

		// Now figure out whether there are users that aren't part in any of these
		$batch	= $users	= [];
		$data	= group_memberships($g);
		if (!empty($data))
		{
			foreach ($data as $user)
			{
				$users[] = (int) $user['user_id'];
			}

			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $users, true) . '
				AND user_type <> ' . USER_IGNORE;
			$result	= ($missing) ? $db->sql_query_limit($sql, 1, 0) : $db->sql_query($sql);
			$batch	= $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
		}

		// Return the correct stuff
		if ($missing)
		{
			return (empty($batch)) ? false : true;
		}
		return $batch;
	}

	/**
	 * Fetch the group IDs of the two groups
	 * @return void
	 */
	function _get_group_ids()
	{
		global $db;

		$g = [];
		$sql = 'SELECT group_id, group_name
			FROM ' . GROUPS_TABLE . '
			WHERE ' . $db->sql_in_set('group_name', ['REGISTERED']);
		$result	= $db->sql_query_limit($sql, 2, 0);
		while ($row = $db->sql_fetchrow($result))
		{
			$g[$row['group_name']] = $row['group_id'];
		}
		$db->sql_freeresult($result);

		return $g;
	}
}
