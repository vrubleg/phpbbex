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
* Sending password reminders
*/
class ucp_remind
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $config;

		$username	= request_var('username', '', true);
		$email		= strtolower(request_var('email', ''));
		$submit		= (isset($_POST['submit'])) ? true : false;

		if ($submit)
		{
			$sql = 'SELECT user_id, username, user_permissions, user_email, user_jabber, user_notify_type, user_type, user_lang, user_inactive_reason
				FROM ' . USERS_TABLE . "
				WHERE user_email_hash = '" . $db->sql_escape(phpbb_email_hash($email)) . "'
					AND username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$user_row)
			{
				trigger_error('NO_EMAIL_USER');
			}

			if ($user_row['user_type'] == USER_IGNORE)
			{
				trigger_error('NO_USER');
			}

			if ($user_row['user_type'] == USER_INACTIVE)
			{
				if ($user_row['user_inactive_reason'] == INACTIVE_MANUAL)
				{
					trigger_error('ACCOUNT_DEACTIVATED');
				}
				else
				{
					trigger_error('ACCOUNT_NOT_ACTIVATED');
				}
			}

			// Check users permissions
			$auth2 = new phpbb_auth();
			$auth2->acl($user_row);

			if (!$auth2->acl_get('u_chgpasswd'))
			{
				trigger_error('NO_AUTH_PASSWORD_REMINDER');
			}

			$server_url = generate_board_url();

			// Make password at least 8 characters long, make it longer if admin wants to.
			// gen_rand_string() however has a limit of 12 or 13.
			$user_password = gen_rand_string_friendly(max(8, mt_rand((int) $config['min_pass_chars'], (int) $config['max_pass_chars'])));

			// For the activation key a random length between 6 and 10 will do.
			$user_actkey = gen_rand_string(mt_rand(6, 10));

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_newpasswd = '" . $db->sql_escape(phpbb_hash($user_password)) . "', user_actkey = '" . $db->sql_escape($user_actkey) . "'
				WHERE user_id = " . $user_row['user_id'];
			$db->sql_query($sql);

			require_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.php');

			$messenger = new messenger(false);

			$messenger->template('user_activate_passwd', $user_row['user_lang']);

			$messenger->to($user_row['user_email'], $user_row['username']);
			$messenger->im($user_row['user_jabber'], $user_row['username']);

			$messenger->anti_abuse_headers($config, $user);

			$messenger->assign_vars(array(
				'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
				'PASSWORD'		=> htmlspecialchars_decode($user_password),
				'U_ACTIVATE'	=> "$server_url/ucp.php?mode=activate&u={$user_row['user_id']}&k=$user_actkey")
			);

			$messenger->send($user_row['user_notify_type']);

			meta_refresh(3, append_sid(PHPBB_ROOT_PATH . 'index.php'));

			$message = $user->lang['PASSWORD_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid(PHPBB_ROOT_PATH . 'index.php') . '">', '</a>');
			trigger_error($message);
		}

		$template->assign_vars(array(
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'mode=sendpassword'))
		);

		$this->tpl_name = 'ucp_remind';
		$this->page_title = 'UCP_REMIND';
	}
}
