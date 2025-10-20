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
* Board registration
*/
class ucp_register
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template;

		if ($config['require_activation'] == USER_ACTIVATION_DISABLE)
		{
			trigger_error('UCP_REGISTER_DISABLE');
		}

		if (isset($_POST['disagree']))
		{
			redirect(append_sid(PHPBB_ROOT_PATH . 'index.php'));
		}

		$submit			= isset($_POST['agree']);
		$change_lang	= request_var('change_lang', '');
		$user_lang		= request_var('lang', $user->lang_name);

		add_form_key('ucp_register');

		if ($change_lang || $user_lang != $config['default_lang'])
		{
			$use_lang = ($change_lang) ? basename($change_lang) : basename($user_lang);

			if (!validate_language_iso_name($use_lang))
			{
				if ($change_lang)
				{
					$submit = false;
				}

				$user->lang_name = $user_lang = $use_lang;
				$user->lang = [];
				$user->data['user_lang'] = $user->lang_name;
				$user->add_lang(['common', 'ucp']);
			}
			else
			{
				$change_lang = '';
				$user_lang = $user->lang_name;
			}
		}

		require_once(PHPBB_ROOT_PATH . 'includes/functions_profile_fields.php');
		$cp = new custom_profile();
		$error = $cp_data = $cp_error = [];

		// The CAPTCHA kicks in here. We can't help that the information gets lost on language change.
		if ($config['enable_confirm'])
		{
			require_once(PHPBB_ROOT_PATH . 'includes/captcha/captcha_factory.php');
			$captcha = phpbb_captcha_factory::get_instance($config['captcha_plugin']);
			$captcha->init(CONFIRM_REG);
		}

		$is_dst = $config['board_dst'];
		$timezone = $config['board_timezone'];

		$data = [
			'username'			=> utf8_normalize_nfc(request_var('username', '', true)),
			'new_password'		=> request_var('new_password', '', true),
			'password_confirm'	=> request_var('password_confirm', '', true),
			'email'				=> strtolower(request_var('email', '')),
			'lang'				=> basename(request_var('lang', $user->lang_name)),
			'tz'				=> request_var('tz', (float) $timezone),
		];

		// Check and initialize some variables if needed
		if ($submit)
		{
			$data['lang']		= ($config['override_user_lang']) ? $config['default_lang'] : $data['lang'];
			$data['tz']			= ($config['override_user_timezone']) ? $config['board_timezone'] : $data['tz'];
			$is_dst				= ($config['override_user_timezone']) ? $config['board_dst'] : $is_dst;

			$error_type = [
				'generic' => false,
				'token' => false,
				'captcha' => false,
				'attempts' => false,
				'dnsbl' => false,
			];

			$error = validate_data($data, [
				'username'			=> [
					['string', false, $config['min_name_chars'], $config['max_name_chars']],
					['username', '']],
				'new_password'		=> [
					['string', false, $config['min_pass_chars'], $config['max_pass_chars']],
					['password']],
				'password_confirm'	=> ['string', false, $config['min_pass_chars'], $config['max_pass_chars']],
				'email'				=> [
					['string', false, 6, 60],
					['email']],
				'tz'				=> ['num', false, -14, 14],
				'lang'				=> ['language_iso_name'],
			]);

			$error_type['generic'] = !!sizeof($error);

			if (!check_form_key('ucp_register'))
			{
				$error[] = $user->lang['FORM_INVALID'];
				$error_type['token'] = true;
			}

			// Replace "error" strings with their real, localised form
			$error = preg_replace_callback('#^([A-Z_]+)$#', function ($m) use ($user) { return $user->lang[$m[1]] ?? $m[1]; }, $error);

			if ($config['enable_confirm'])
			{
				$vc_response = $captcha->validate($data);
				if ($vc_response !== false)
				{
					$error[] = $vc_response;
					$error_type['captcha'] = true;
				}

				if ($config['max_reg_attempts'] && $captcha->get_attempt_count() > $config['max_reg_attempts'])
				{
					$error[] = $user->lang['TOO_MANY_REGISTERS'];
					$error_type['attempts'] = true;
				}
			}

			// DNSBL check
			if ($config['check_dnsbl'])
			{
				if (($dnsbl = $user->check_dnsbl('register')) !== false)
				{
					$error[] = sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]);
					$error_type['dnsbl'] = true;
				}
			}

			// validate custom profile fields
			$cp->submit_cp_field('register', $user->get_iso_lang_id(), $cp_data, $error);

			if (!sizeof($error))
			{
				if ($data['new_password'] != $data['password_confirm'])
				{
					$error[] = $user->lang['NEW_PASSWORD_ERROR'];
				}
			}

			// Get browser id
			$browser_id = get_cookie('bid', '');
			$sql = "SELECT * FROM " . USER_BROWSER_IDS_TABLE . " WHERE browser_id='" . $db->sql_escape($browser_id) . "'";
			$result = $db->sql_query($sql);
			$browser = $db->sql_fetchrow($result);
			if (!$browser)
			{
				$browser = [
					'browser_id' => 'none',
					'created' => time(),
					'last_visit' => time(),
					'visits' => 0,
					'agent' => trim(substr(!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', 0, 249)),
					'last_ip' => (!empty($_SERVER['REMOTE_ADDR'])) ? (string) $_SERVER['REMOTE_ADDR'] : '',
				];
			}
			$db->sql_freeresult($result);

			if (!sizeof($error))
			{
				$server_url = generate_board_url();

				// Which group by default?
				$group_name = 'REGISTERED';

				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . "
					WHERE group_name = '" . $db->sql_escape($group_name) . "'
						AND group_type = " . GROUP_SPECIAL;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error('NO_GROUP');
				}

				$group_id = $row['group_id'];

				if (($config['require_activation'] == USER_ACTIVATION_SELF || $config['require_activation'] == USER_ACTIVATION_ADMIN) && $config['email_enable'])
				{
					$user_actkey = gen_rand_string(mt_rand(6, 10));
					$user_type = USER_INACTIVE;
					$user_inactive_reason = INACTIVE_REGISTER;
					$user_inactive_time = time();
				}
				else
				{
					$user_type = USER_NORMAL;
					$user_actkey = '';
					$user_inactive_reason = 0;
					$user_inactive_time = 0;
				}

				$user_row = [
					'username'				=> $data['username'],
					'user_password'			=> phpbb_hash($data['new_password']),
					'user_email'			=> $data['email'],
					'group_id'				=> (int) $group_id,
					'user_timezone'			=> (float) $data['tz'],
					'user_dst'				=> $is_dst,
					'user_lang'				=> $data['lang'],
					'user_type'				=> $user_type,
					'user_actkey'			=> $user_actkey,
					'user_ip'				=> $user->ip,
					'user_regdate'			=> time(),
					'user_inactive_reason'	=> $user_inactive_reason,
					'user_inactive_time'	=> $user_inactive_time,
				];

				if ($config['new_member_post_limit'])
				{
					$user_row['user_new'] = 1;
				}

				// Register user...
				$user_id = user_add($user_row, $cp_data);

				// This should not happen, because the required variables are listed above...
				if ($user_id === false)
				{
					trigger_error('NO_USER', E_USER_ERROR);
				}

				// Log registration
				$user_id_orig = $user->data['user_id'];
				$user->data['user_id'] = $user_id;
				add_log('register', 'LOG_REGISTER_OK', $data['username'], $data['email'], '', $browser['browser_id'], $browser['agent'], time() - $browser['created'], $browser['visits']);
				$user->data['user_id'] = $user_id_orig;

				// Okay, captcha, your job is done.
				if ($config['enable_confirm'] && isset($captcha))
				{
					$captcha->reset();
				}

				if ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable'])
				{
					$message = $user->lang['ACCOUNT_INACTIVE'];
					$email_template = 'user_welcome_inactive';
				}
				else if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $config['email_enable'])
				{
					$message = $user->lang['ACCOUNT_INACTIVE_ADMIN'];
					$email_template = 'admin_welcome_inactive';
				}
				else
				{
					$message = $user->lang['ACCOUNT_ADDED'];
					$email_template = 'user_welcome';
				}

				if ($config['email_enable'])
				{
					require_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.php');

					$messenger = new messenger(false);

					$messenger->template($email_template, $data['lang']);

					$messenger->to($data['email'], $data['username']);

					$messenger->anti_abuse_headers($config, $user);

					$messenger->assign_vars([
						'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename'])),
						'USERNAME'		=> htmlspecialchars_decode($data['username']),
						'PASSWORD'		=> htmlspecialchars_decode($data['new_password']),
						'U_ACTIVATE'	=> "$server_url/ucp.php?mode=activate&u=$user_id&k=$user_actkey"]
					);

					$messenger->send(NOTIFY_EMAIL);

					if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
					{
						// Grab an array of user_id's with a_user permissions ... these users can activate a user
						$admin_ary = $auth->acl_get_list(false, 'a_user', false);
						$admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : [];

						// Also include founders
						$where_sql = ' WHERE user_type = ' . USER_FOUNDER;

						if (sizeof($admin_ary))
						{
							$where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
						}

						$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
							FROM ' . USERS_TABLE . ' ' .
							$where_sql;
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							$messenger->template('admin_activate', $row['user_lang']);
							$messenger->to($row['user_email'], $row['username']);
							$messenger->im($row['user_jabber'], $row['username']);

							$messenger->assign_vars([
								'USERNAME'			=> htmlspecialchars_decode($data['username']),
								'U_USER_DETAILS'	=> "$server_url/memberlist.php?mode=viewprofile&u=$user_id",
								'U_ACTIVATE'		=> "$server_url/ucp.php?mode=activate&u=$user_id&k=$user_actkey"]
							);

							$messenger->send($row['user_notify_type']);
						}
						$db->sql_freeresult($result);
					}
				}

				$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid(PHPBB_ROOT_PATH . 'index.php') . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				// Log registration
				add_log('register', 'LOG_REGISTER_REJECTED_' . ($error_type['token'] ? 'BOT' : 'USER'), $data['username'], $data['email'], implode("\n", $error), $browser['browser_id'], $browser['agent'], time() - $browser['created'], $browser['visits']);

				// Display one error if user provided invalid token
				if ($error_type['token'])
				{
					$error = [$user->lang['FORM_INVALID_TOKEN']];
				}
			}
		}

		$s_hidden_fields = [
			'change_lang'	=> 0,
		];

		if ($config['enable_confirm'])
		{
			$s_hidden_fields = array_merge($s_hidden_fields, $captcha->get_hidden_fields());
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);
		$confirm_image = '';

		// Visual Confirmation - Show images
		if ($config['enable_confirm'])
		{
			$template->assign_vars([
				'CAPTCHA_TEMPLATE'		=> $captcha->get_template(),
			]);
		}

		//
		$l_reg_cond = '';
		switch ($config['require_activation'])
		{
			case USER_ACTIVATION_SELF:
				$l_reg_cond = $user->lang['UCP_EMAIL_ACTIVATE'];
			break;

			case USER_ACTIVATION_ADMIN:
				$l_reg_cond = $user->lang['UCP_ADMIN_ACTIVATE'];
			break;
		}

		$template->assign_vars([
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'USERNAME'			=> $data['username'],
			'PASSWORD'			=> $data['new_password'],
			'PASSWORD_CONFIRM'	=> $data['password_confirm'],
			'EMAIL'				=> $data['email'],

			'L_REG_COND'				=> $l_reg_cond,
			'L_USERNAME_EXPLAIN'		=> sprintf($user->lang[$config['allow_name_chars'] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']),
			'L_PASSWORD_EXPLAIN'		=> sprintf($user->lang[$config['pass_complex'] . '_EXPLAIN'], $config['min_pass_chars'], $config['max_pass_chars']),
			'L_TERMS_OF_USE_CONTENT'	=> sprintf($user->lang['TERMS_OF_USE_CONTENT'], $config['sitename'], generate_board_url()),

			'S_LANG_OPTIONS'	=> ($config['override_user_lang']) ? '' : language_select($data['lang']),
			'S_TZ_OPTIONS'		=> ($config['override_user_timezone']) ? '' : tz_select($data['tz']),
			'S_CONFIRM_REFRESH'	=> ($config['enable_confirm'] && $config['confirm_refresh']),
			'S_REGISTRATION'	=> true,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'mode=register'),
		]);

		//
		$user->profile_fields = [];

		// Generate profile fields -> Template Block Variable profile_fields
		$cp->generate_profile_fields('register', $user->get_iso_lang_id());

		//
		$this->tpl_name = 'ucp_register';
		$this->page_title = 'UCP_REGISTRATION';
	}
}
