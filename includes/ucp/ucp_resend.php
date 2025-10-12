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
* Resending activation emails
*/
class ucp_resend
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
		$submit		= isset($_POST['submit']);

		add_form_key('ucp_resend');

		if ($submit)
		{
			if (!check_form_key('ucp_resend'))
			{
				trigger_error('FORM_INVALID');
			}

			$sql = 'SELECT user_id, group_id, username, user_email, user_type, user_lang, user_actkey, user_inactive_reason
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

			if (!$user_row['user_actkey'] && $user_row['user_type'] != USER_INACTIVE)
			{
				trigger_error('ACCOUNT_ALREADY_ACTIVATED');
			}

			if (!$user_row['user_actkey'] || ($user_row['user_type'] == USER_INACTIVE && $user_row['user_inactive_reason'] == INACTIVE_MANUAL))
			{
				trigger_error('ACCOUNT_DEACTIVATED');
			}

			require_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.php');
			$messenger = new messenger(false);

			if ($config['require_activation'] == USER_ACTIVATION_SELF)
			{
				$messenger->template('user_resend_inactive', $user_row['user_lang']);
				$messenger->to($user_row['user_email'], $user_row['username']);

				$messenger->anti_abuse_headers($config, $user);

				$messenger->assign_vars([
					'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename'])),
					'USERNAME'		=> htmlspecialchars_decode($user_row['username']),
					'U_ACTIVATE'	=> generate_board_url() . "/ucp.php?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}"]
				);

				$messenger->send(NOTIFY_EMAIL);
			}

			if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				// Grab an array of user_id's with a_user permissions ... these users can activate a user
				$admin_ary = $auth->acl_get_list(false, 'a_user', false);

				$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
					FROM ' . USERS_TABLE . '
					WHERE ' . $db->sql_in_set('user_id', $admin_ary[0]['a_user']);
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$messenger->template('admin_activate', $row['user_lang']);
					$messenger->to($row['user_email'], $row['username']);
					$messenger->im($row['user_jabber'], $row['username']);

					$messenger->anti_abuse_headers($config, $user);

					$messenger->assign_vars([
						'USERNAME'			=> htmlspecialchars_decode($user_row['username']),
						'U_USER_DETAILS'	=> generate_board_url() . "/memberlist.php?mode=viewprofile&u={$user_row['user_id']}",
						'U_ACTIVATE'		=> generate_board_url() . "/ucp.php?mode=activate&u={$user_row['user_id']}&k={$user_row['user_actkey']}"]
					);

					$messenger->send($row['user_notify_type']);
				}
				$db->sql_freeresult($result);
			}

			meta_refresh(3, append_sid(PHPBB_ROOT_PATH . 'index.php'));

			$message = ($config['require_activation'] == USER_ACTIVATION_ADMIN) ? $user->lang['ACTIVATION_EMAIL_SENT_ADMIN'] : $user->lang['ACTIVATION_EMAIL_SENT'];
			$message .= '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid(PHPBB_ROOT_PATH . 'index.php') . '">', '</a>');
			trigger_error($message);
		}

		$template->assign_vars([
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'mode=resend_act')]
		);

		$this->tpl_name = 'ucp_resend';
		$this->page_title = 'UCP_RESEND';
	}
}
