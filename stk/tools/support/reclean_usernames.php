<?php
/**
*
* @package Support Toolkit - Reclean Usernames
* @copyright (c) 2009 phpBB Group
* @license GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class reclean_usernames
{
	/**
	* Batch size.
	*/
	var $batch_size = 500;

	/**
	* Display Options.
	*/
	function display_options()
	{
		return 'RECLEAN_USERNAMES';
	}

	/**
	* Run Tool
	*
	* Does the actual stuff we want the tool to do after submission
	*/
	function run_tool()
	{
		global $db, $template;

		$step = request_var('step', 0);
		$i = 0;

		$sql = 'SELECT user_id, username, username_clean FROM ' . USERS_TABLE;
		$result = $db->sql_query_limit($sql, $this->batch_size, ($step * $this->batch_size));
		while ($row = $db->sql_fetchrow($result))
		{
			$i++;
			$username_clean = utf8_clean_string($row['username']);

			if ($username_clean != $row['username_clean'])
			{
				$db->sql_query('UPDATE ' . USERS_TABLE . "
					SET username_clean = '" . $db->sql_escape($username_clean) . "'
					WHERE user_id = {$row['user_id']}");
			}
		}
		$db->sql_freeresult($result);

		if ($i == $this->batch_size)
		{
			meta_refresh(0, append_sid(STK_INDEX, ['c' => 'support', 't' => 'reclean_usernames', 'submit' => true, 'step' => ++$step]));
			$template->assign_var('U_BACK_TOOL', false);

			trigger_error('RECLEAN_USERNAMES_NOT_COMPLETE');
		}
		else
		{
			trigger_error('RECLEAN_USERNAMES_COMPLETE');
		}
	}
}
