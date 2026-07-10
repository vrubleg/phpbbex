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
* Changing user preferences
*/
class ucp_prefs
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template;

		$submit = isset($_POST['submit']);
		$error = $data = [];
		$s_hidden_fields = '';

		switch ($mode)
		{
			case 'personal':
				add_form_key('ucp_prefs_personal');
				$data = [
					'notifymethod'  => request_var('notifymethod', $user->data['user_notify_type']),
					'lang'          => basename(request_var('lang', $user->data['user_lang_code'])),
					'style'         => request_var('style', (int) $user->data['user_style']),
					'tz'            => request_var('tz', (float) $user->data['user_timezone']),
					'dst'           => request_var('dst', (bool) $user->data['user_dst']),
					'massemail'     => request_var('massemail', (bool) $user->data['user_allow_massemail']),
					'hideonline'    => request_var('hideonline', (bool) !$user->data['user_allow_viewonline']),
					'notifypm'      => request_var('notifypm', (bool) $user->data['user_notify_pm']),
					'popuppm'       => request_var('popuppm', (bool) $user->optionget('popuppm')),
					'allowpm'       => request_var('allowpm', (bool) $user->data['user_allow_pm']),
				];

				if ($data['notifymethod'] == NOTIFY_IM && (!$config['jab_enable'] || !$user->data['user_jabber'] || !@extension_loaded('xml')))
				{
					// Jabber isnt enabled, or no jabber field filled in. Update the users table to be sure its correct.
					$data['notifymethod'] = NOTIFY_BOTH;
				}

				if ($submit)
				{
					if ($config['override_user_style'])
					{
						$data['style'] = (int) $config['default_style'];
					}
					else if (!phpbb_style_is_active($data['style']))
					{
						$data['style'] = (int) $user->data['user_style'];
					}

					$data['lang']       = ($config['override_user_lang']) ? $config['default_lang_code'] : $data['lang'];
					$data['tz']         = ($config['override_user_timezone']) ? $config['board_timezone'] : $data['tz'];
					$data['dst']        = ($config['override_user_timezone']) ? $config['board_dst'] : $data['dst'];

					$error = validate_data($data, [
						'lang'          => ['lang_code'],
						'tz'            => ['num', false, -14, 14],
					]);

					if (!check_form_key('ucp_prefs_personal'))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!sizeof($error))
					{
						$user->optionset('popuppm', $data['popuppm']);

						$sql_ary = [
							'user_allow_pm'         => $data['allowpm'],
							'user_allow_massemail'  => $data['massemail'],
							'user_allow_viewonline' => ($auth->acl_get('u_hideonline')) ? !$data['hideonline'] : $user->data['user_allow_viewonline'],
							'user_notify_type'      => $data['notifymethod'],
							'user_notify_pm'        => $data['notifypm'],
							'user_options'          => $user->data['user_options'],

							'user_dst'              => $data['dst'],
							'user_lang_code'        => $data['lang'],
							'user_timezone'         => $data['tz'],
							'user_style'            => $data['style'],
						];

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, $this->u_action);
						$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = preg_replace_callback('#^([A-Z_]+)$#', function ($m) use ($user) { return $user->lang[$m[1]] ?? $m[1]; }, $error);
				}

				// check if there are any user-selectable languages
				$sql = 'SELECT COUNT(lang_code) as languages_count
								FROM ' . LANG_TABLE;
				$result = $db->sql_query($sql);
				if ($db->sql_fetchfield('languages_count') > 1)
				{
					$s_more_languages = true;
				}
				else
				{
					$s_more_languages = false;
				}
				$db->sql_freeresult($result);

				// check if there are any user-selectable styles
				$sql = 'SELECT COUNT(style_id) as styles_count
								FROM ' . STYLES_TABLE . '
								WHERE style_active = 1';
				$result = $db->sql_query($sql);
				if ($db->sql_fetchfield('styles_count') > 1)
				{
					$s_more_styles = true;
				}
				else
				{
					$s_more_styles = false;
				}
				$db->sql_freeresult($result);

				$template->assign_vars([
					'ERROR'             => (sizeof($error)) ? implode('<br />', $error) : '',

					'S_NOTIFY_EMAIL'    => ($data['notifymethod'] == NOTIFY_EMAIL),
					'S_NOTIFY_IM'       => ($data['notifymethod'] == NOTIFY_IM),
					'S_NOTIFY_BOTH'     => ($data['notifymethod'] == NOTIFY_BOTH),
					'S_MASS_EMAIL'      => $data['massemail'],
					'S_ALLOW_PM'        => $data['allowpm'],
					'S_HIDE_ONLINE'     => $data['hideonline'],
					'S_NOTIFY_PM'       => $data['notifypm'],
					'S_POPUP_PM'        => $data['popuppm'],
					'S_DST'             => $data['dst'],

					'S_MORE_LANGUAGES'  => $s_more_languages,
					'S_MORE_STYLES'         => $s_more_styles,

					'S_LANG_OPTIONS'        => ($config['override_user_lang']) ? '' : language_select($data['lang']),
					'S_STYLE_OPTIONS'       => ($config['override_user_style']) ? '' : style_select($data['style']),
					'S_TZ_OPTIONS'          => ($config['override_user_timezone']) ? '' : tz_select($data['tz']),
					'S_CAN_HIDE_ONLINE'     => (bool) $auth->acl_get('u_hideonline'),
					'S_SELECT_NOTIFY'       => ($config['jab_enable'] && $user->data['user_jabber'] && @extension_loaded('xml')),
				]);

			break;

			case 'view':

				add_form_key('ucp_prefs_view');

				$data = [
					'images'        => request_var('images', (bool) $user->optionget('viewimg')),
					'flash'         => request_var('flash', (bool) $user->optionget('viewflash')),
					'smilies'       => request_var('smilies', (bool) $user->optionget('viewsmilies')),
					'sigs'          => request_var('sigs', (bool) $user->optionget('viewsigs')),
					'avatars'       => request_var('avatars', (bool) $user->optionget('viewavatars')),
					'wordcensor'    => request_var('wordcensor', (bool) $user->optionget('viewcensors')),

					'quickreply'    => request_var('quickreply', (bool) $user->optionget('viewquickreply')),
					'quickpost'     => request_var('quickpost', (bool) $user->optionget('viewquickpost')),
				];

				if ($submit)
				{
					$error = [];

					if (!check_form_key('ucp_prefs_view'))
					{
						$error[] = 'FORM_INVALID';
					}

					if (!sizeof($error))
					{
						$user->optionset('viewimg', $data['images']);
						$user->optionset('viewflash', $data['flash']);
						$user->optionset('viewsmilies', $data['smilies']);
						$user->optionset('viewsigs', $data['sigs']);
						$user->optionset('viewavatars', $data['avatars']);
						$user->optionset('viewquickreply', $data['quickreply']);
						$user->optionset('viewquickpost', $data['quickpost']);

						if ($auth->acl_get('u_chgcensors'))
						{
							$user->optionset('viewcensors', $data['wordcensor']);
						}

						$sql_ary = [
							'user_options'              => $user->data['user_options'],
						];

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						meta_refresh(3, $this->u_action);
						$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}

					// Replace "error" strings with their real, localised form
					$error = preg_replace_callback('#^([A-Z_]+)$#', function ($m) use ($user) { return $user->lang[$m[1]] ?? $m[1]; }, $error);
				}

				$template->assign_vars([
					'ERROR'             => (sizeof($error)) ? implode('<br />', $error) : '',

					'S_IMAGES'          => $data['images'],
					'S_FLASH'           => $data['flash'],
					'S_SMILIES'         => $data['smilies'],
					'S_SIGS'            => $data['sigs'],
					'S_AVATARS'         => $data['avatars'],
					'S_DISABLE_CENSORS' => $data['wordcensor'],

					'S_QUICKREPLY'      => $data['quickreply'],
					'QUICK_REPLY'       => (bool) $config['allow_quick_reply'],
					'S_QUICKPOST'       => $data['quickpost'],
					'QUICK_POST'        => (bool) $config['allow_quick_post'],

					'S_CHANGE_CENSORS'      => ($auth->acl_get('u_chgcensors') && $config['allow_nocensors']),
				]);

			break;

			case 'post':

				$data = [
					'bbcode'    => request_var('bbcode', $user->optionget('bbcode')),
					'smilies'   => request_var('smilies', $user->optionget('smilies')),
					'sig'       => request_var('sig', $user->optionget('attachsig')),
					'notify'    => request_var('notify', (bool) $user->data['user_notify']),
				];
				add_form_key('ucp_prefs_post');

				if ($submit)
				{
					if (check_form_key('ucp_prefs_post'))
					{
						$user->optionset('bbcode', $data['bbcode']);
						$user->optionset('smilies', $data['smilies']);
						$user->optionset('attachsig', $data['sig']);

						$sql_ary = [
							'user_options'  => $user->data['user_options'],
							'user_notify'   => $data['notify'],
						];

						$sql = 'UPDATE ' . USERS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . $user->data['user_id'];
						$db->sql_query($sql);

						$msg = $user->lang['PREFERENCES_UPDATED'];
					}
					else
					{
						$msg = $user->lang['FORM_INVALID'];
					}
					meta_refresh(3, $this->u_action);
					$message = $msg . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
					trigger_error($message);
				}

				$template->assign_vars([
					'S_BBCODE'  => $data['bbcode'],
					'S_SMILIES' => $data['smilies'],
					'S_SIG'     => $data['sig'],
					'S_NOTIFY'  => $data['notify']]
				);
			break;
		}

		$template->assign_vars([
			'L_TITLE'           => $user->lang['UCP_PREFS_' . strtoupper($mode)],

			'S_HIDDEN_FIELDS'   => $s_hidden_fields,
			'S_UCP_ACTION'      => $this->u_action]
		);

		$this->tpl_name = 'ucp_prefs_' . $mode;
		$this->page_title = 'UCP_PREFS_' . strtoupper($mode);
	}
}
