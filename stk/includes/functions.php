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
* Build configuration template for acp configuration pages
*
* Slightly modified from adm/index.php
*/
function build_cfg_template($tpl_type, $name, $vars)
{
	global $user;

	$tpl = array();

	// Give the option to not do a request_var here and never do it for password fields.
	if ((!isset($vars['no_request_var']) || !$vars['no_request_var']) && $tpl_type[0] != 'password')
	{
		$default = (isset($vars['default'])) ? request_var($name, $vars['default']) : request_var($name, '');
	}
	else
	{
		$default = (isset($vars['default'])) ? $vars['default'] : '';
	}

	switch ($tpl_type[0])
	{
		case 'text':
			// If requested set some vars so that we later can display the link correct
			if (isset($vars['select_user']) && $vars['select_user'] === true)
			{
				$tpl['find_user']		= true;
				$tpl['find_user_field']	= $name;
			}
		case 'password':
			$size = (int) $tpl_type[1];
			$maxlength = (int) $tpl_type[2];

			$tpl['tpl'] = '<input id="' . $name . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '" value="' . $default . '" />';
		break;

		case 'textarea':
			$rows = (int) $tpl_type[1];
			$cols = (int) $tpl_type[2];

			$tpl['tpl'] = '<textarea id="' . $name . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $default . '</textarea>';
		break;

		case 'radio':
			$name_yes	= ($default) ? ' checked="checked"' : '';
			$name_no	= (!$default) ? ' checked="checked"' : '';

			$tpl_type_cond = explode('_', $tpl_type[1]);
			$type_no = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

			$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $name_no . ' class="radio" /> ' . (($type_no) ? $user->lang['NO'] : $user->lang['DISABLED']) . '</label>';
			$tpl_yes = '<label><input type="radio" id="' . $name . '" name="' . $name . '" value="1"' . $name_yes . ' class="radio" /> ' . (($type_no) ? $user->lang['YES'] : $user->lang['ENABLED']) . '</label>';

			$tpl['tpl'] = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . $tpl_no : $tpl_no . $tpl_yes;
		break;

		case 'checkbox':
			$checked	= ($default) ? ' checked="checked"' : '';

			if (empty($tpl_type[1]))
			{
				$tpl['tpl'] = '<input type="checkbox" id="' . $name . '" name="' . $name . '"' . $checked . ' />';
			}
			else
			{
				$tpl['tpl'] = '<input type="radio" id="' . $name . '" name="' . $tpl_type[1] . '" value="' . $name . '"' . $checked . ' />';
			}
		break;

		case 'select':
		case 'select_multiple' :
		case 'custom':

			$return = '';

			if (isset($vars['function']))
			{
				$call = $vars['function'];
			}
			else
			{
				break;
			}

			if (isset($vars['params']))
			{
				$args = array();
				foreach ($vars['params'] as $value)
				{
					switch ($value)
					{
						case '{CONFIG_VALUE}':
							$value = $default;
						break;

						case '{KEY}':
							$value = $name;
						break;
					}

					$args[] = $value;
				}
			}
			else
			{
				$args = array($default, $name);
			}

			$return = call_user_func_array($call, $args);

			if ($tpl_type[0] == 'select')
			{
				$tpl['tpl'] = '<select id="' . $name . '" name="' . $name . '">' . $return . '</select>';
			}
			else if ($tpl_type[0] == 'select_multiple')
			{
				$tpl['tpl'] = '<select id="' . $name . '" name="' . $name . '[]" multiple="multiple">' . $return . '</select>';
			}
			else
			{
				$tpl['tpl'] = $return;
			}

		break;

		default:
		break;
	}

	if (isset($vars['append']))
	{
		$tpl['tpl'] .= $vars['append'];
	}

	return $tpl;
}

/**
* Use Lang
*
* A function for checking if a language key exists and changing the inputted var to the language value if it does.
* Build for the array_walk used on $error
*/
function use_lang(&$lang_key)
{
	global $user;

	$lang_key = user_lang($lang_key);
}

/**
* A wrapper function for the phpBB $user->lang() call. This method was introduced
* in phpBB 3.0.3. In all versions ≥ 3.0.3 this function will simply call the method
* for the other versions this method will imitate the method as seen in 3.0.3.
*
* More advanced language substitution
* Function to mimic sprintf() with the possibility of using phpBB's language system to substitute nullar/singular/plural forms.
* Params are the language key and the parameters to be substituted.
* This function/functionality is inspired by SHS` and Ashe.
*
* Example call: <samp>$user->lang('NUM_POSTS_IN_QUEUE', 1);</samp>
*/
function user_lang()
{
	global $user;

	$args = func_get_args();

	if (method_exists($user, 'lang'))
	{
		return call_user_func_array(array($user, 'lang'), $args);
	}
	else
	{
		$key = $args[0];

		// Return if language string does not exist
		if (!isset($user->lang[$key]) || (!is_string($user->lang[$key]) && !is_array($user->lang[$key])))
		{
			return $key;
		}

		// If the language entry is a string, we simply mimic sprintf() behaviour
		if (is_string($user->lang[$key]))
		{
			if (sizeof($args) == 1)
			{
				return $user->lang[$key];
			}

			// Replace key with language entry and simply pass along...
			$args[0] = $user->lang[$key];
			return call_user_func_array('sprintf', $args);
		}

		// It is an array... now handle different nullar/singular/plural forms
		$key_found = false;

		// We now get the first number passed and will select the key based upon this number
		for ($i = 1, $num_args = sizeof($args); $i < $num_args; $i++)
		{
			if (is_int($args[$i]))
			{
				$numbers = array_keys($user->lang[$key]);

				foreach ($numbers as $num)
				{
					if ($num > $args[$i])
					{
						break;
					}

					$key_found = $num;
				}
			}
		}

		// Ok, let's check if the key was found, else use the last entry (because it is mostly the plural form)
		if ($key_found === false)
		{
			$numbers = array_keys($user->lang[$key]);
			$key_found = end($numbers);
		}

		// Use the language string we determined and pass it to sprintf()
		$args[0] = $user->lang[$key][$key_found];
		return call_user_func_array('sprintf', $args);
	}
}

/**
* Stk add lang
*
* A wrapper for the $user->add_lang method that will use the custom language path that is used
* in this tool kit.
* The function shall first try to include the file in the users language, if that fails it will
* take the boards default language, if that also fails it will fall back to English
*
* @param	String	$lang_file	the name of the language file
* @param	mixed	$force_lang	If this parameter contains an ISO code this language
*								is used for the file. If set to "false" the users default
*								langauge will be used
*/
function stk_add_lang($lang_file, $fore_lang = false)
{
	global $config, $user;

	// Internally cache some data
	static $lang_data	= array();
	static $lang_dirs	= array();

	// Store current phpBB data
	if (empty($lang_data))
	{
		$lang_data = array(
			'lang_path'	=> $user->lang_path,
			'lang_name'	=> $user->lang_name,
		);
	}

	// Empty the lang_name
	$user->lang_name = '';

	// Find out what languages we could use
	if (empty($lang_dirs))
	{
		$lang_dirs = array(
			$user->data['user_lang'],			// User default
			basename($config['default_lang']),	// Board default
			'en',								// System default
		);

		// Only unique dirs
		$lang_dirs = array_unique($lang_dirs);
	}

	// Switch to the STK language dir
	$user->lang_path = STK_ROOT_PATH . 'language/';

	// Test all languages
	foreach ($lang_dirs as $dir)
	{
		// When forced skip all others
		if ($fore_lang !== false && $dir != $fore_lang)
		{
			continue;
		}

		if (file_exists($user->lang_path . $dir . "/{$lang_file}.php"))
		{
			$user->lang_name = $dir;
			break;
		}
	}

	// No language file :/
	if (empty($user->lang_name))
	{
		trigger_error("Language file: {$lang_file}.php" . ' missing!', E_USER_ERROR);
	}

	// Add the file
	$user->add_lang($lang_file);

	// Now reset the paths so phpBB can continue to operate as usual
	$user->lang_path = $lang_data['lang_path'];
	$user->lang_name = $lang_data['lang_name'];
}

/**
 * Perform all quick tasks that has to be ran before we authenticate
 *
 * @param	String	$action	The action to perform
 * @param   bool    $submit The form has been submitted
 */
function perform_unauthed_quick_tasks($action, $submit = false)
{
	global $template, $user;

	switch ($action)
	{
		// If the user wants to destroy their STK login cookie
		case 'stklogout' :
			setcookie('stk_token', '', (time() - 31536000));
			$user->unset_admin();
			meta_refresh(3, append_sid(PHPBB_ROOT_PATH . 'index.php'));
			trigger_error('STK_LOGOUT_SUCCESS');
		break;

		// Generate the passwd file
		case 'genpasswdfile' :
			// Create a 25 character alphanumeric password (easier to select with a browser and won't cause confusion like it could if it ends in "." or something).
			$_pass_string = substr(preg_replace(array('#([^a-zA-Z0-9])#', '#0#', '#O#'), array('', 'Z', 'Y'), phpbb_hash(unique_id())), 2, 25);

			// The password is usable for 6 hours from now
			$_pass_exprire = time() + 21600;

			// Print a message and tell the user what to do and where to download this page
			page_header($user->lang['GEN_PASS_FILE'], false);

			$template->assign_vars(array(
				'PASS_GENERATED'			=> sprintf($user->lang['PASS_GENERATED'], $_pass_string, $user->format_date($_pass_exprire, false, true)),
				'PASS_GENERATED_REDIRECT'	=> sprintf($user->lang['PASS_GENERATED_REDIRECT'], append_sid(STK_ROOT_PATH . 'index.php')),
				'S_HIDDEN_FIELDS'			=> build_hidden_fields(array('pass_string' => $_pass_string, 'pass_exp' => $_pass_exprire)),
				'U_ACTION'					=> append_sid(STK_INDEX, array('action' => 'downpasswdfile')),
			));

			$template->set_filenames(array(
				'body'	=> 'gen_password.html',
			));
			page_footer(false);
		break;

		// Download the passwd file
		case 'downpasswdfile' :
			$_pass_string	= request_var('pass_string', '', true);
			$_pass_exprire	= request_var('pass_exp', 0);

			// Something went wrong, stop execution
			if (!isset($_POST['download_passwd']) || empty($_pass_string) || $_pass_exprire <= 0)
			{
				trigger_error($user->lang['GEN_PASS_FAILED'], E_USER_ERROR);
			}

			// Create the file and let the user download it
			header('Content-Type: text/x-delimtext; name="passwd.php' . '"');
			header('Content-disposition: attachment; filename=passwd.php');

			print ("<?php
/**
* Support Toolkit emergency password.
* The file was generated on: " . $user->format_date($_pass_exprire - 21600, 'd/M/Y H:i.s', true)) . " and will expire on: " . $user->format_date($_pass_exprire, 'd/M/Y H:i.s', true) . ".
*/

// This file can only be from inside the Support Toolkit
if (!defined('IN_PHPBB') || !defined('STK_VERSION'))
{
	exit;
}

\$stk_passwd\t\t\t\t= '{$_pass_string}';
\$stk_passwd_expiration\t= {$_pass_exprire};
";
			exit_handler();
		break;
	}
}

/**
 * Perform all quick tasks that require the user to be authenticated
 *
 * @param	String	$action	The action we'll be performing
 */
function perform_authed_quick_tasks($action)
{
	global $user;

	$logout = false;

	switch ($action)
	{
		// User wants to logout and remove the password file
		case 'delpasswdfilelogout' :
			$logout = true;

			// No Break;

		// If the user wants to distroy the passwd file
		case 'delpasswdfile' :
			if (file_exists(STK_ROOT_PATH . 'passwd.php') && false === @unlink(STK_ROOT_PATH . 'passwd.php'))
			{
				// Shouldn't happen. Kill the script
				trigger_error($user->lang['FAIL_REMOVE_PASSWD'], E_USER_ERROR);
			}

			// Log him out
			if ($logout)
			{
				perform_unauthed_quick_tasks('stklogout');
			}
		break;
	}
}

/**
 * Support Toolkit Error handler
 *
 * A wrapper for the phpBB `msg_handler` function, which is mainly used
 * to update variables before calling the actual msg_handler and is able
 * to handle various special cases.
 */
function stk_msg_handler($errno, $msg_text, $errfile, $errline, $backtrace = [])
{
	// First and foremost handle the case where phpBB calls trigger error
	// but the STK really needs to continue.
	global $critical_repair, $stk_no_error;
	if ($stk_no_error === true)
	{
		return true;
	}

	// Do not display notices if we suppress them via @
	if (error_reporting() == 0 && $errno != E_USER_ERROR && $errno != E_USER_WARNING && $errno != E_USER_NOTICE)
	{
		return;
	}

	// We encounter an error while in the ERK, this need some special treatment
	if (defined('IN_ERK'))
	{
		$critical_repair->trigger_error($msg_text, ($errno == E_USER_ERROR ? false : true));
	}
	else if (!defined('IN_STK'))
	{
		// We're encountering an error before the STK is fully loaded
		// Set out own message if needed
		if ($errno == E_USER_ERROR)
		{
			$msg_text = 'The Support Toolkit encountered a fatal error.<br /><br />
						 The Support Toolkit includes an Emergency Repair Kit (ERK), a tool designed to resolve certain errors that prevent phpBB from functioning.
						 It is advised that you run the ERK now so it can attempt to repair the error it has detected.<br />
						 To run the ERK, click <a href="' . STK_ROOT_PATH . 'erk.php">here</a>.';
		}

		if (!isset($critical_repair))
		{
			$critical_repair = new critical_repair();
		}

		$critical_repair->trigger_error($msg_text, ($errno == E_USER_ERROR ? false : true));
	}

	//-- Normal phpBB msg_handler

	global $cache, $db, $auth, $template, $config, $user;
	global $phpbb_root_path, $msg_title, $msg_long_text;

	// Message handler is stripping text. In case we need it, we are possible to define long text...
	if (isset($msg_long_text) && $msg_long_text && !$msg_text)
	{
		$msg_text = $msg_long_text;
	}

	if (version_compare(PHP_VERSION, '8.4', '<') && $errno == E_STRICT) { $errno = E_WARNING; }

	switch ($errno)
	{
		case E_ERROR:
		case E_NOTICE:
		case E_WARNING:
		case E_DEPRECATED:

			// Check the error reporting level and return if the error level does not match
			// If DEBUG is defined the default level is E_ALL
			if (($errno & ((defined('DEBUG')) ? E_ALL : error_reporting())) == 0)
			{
				return;
			}

			// Template engine generates a lot of notices =(
			if ($errno == E_NOTICE && (strpos($errfile, 'cache') !== false || strpos($errfile, 'template.') !== false))
			{
				return;
			}

			$err_types = [E_ERROR => 'Error', E_NOTICE => 'Notice', E_WARNING => 'Warning', E_DEPRECATED => 'Deprecated'];
			$errfile = stk_filter_root_path($errfile);
			$msg_text = stk_filter_root_path($msg_text);
			$backtrace = format_backtrace($backtrace);

			if (defined('IN_INSTALL') || defined('DEBUG') || isset($auth) && $auth->acl_get('a_'))
			{
				echo '<b>[PHP ' . $err_types[$errno] . ']</b> in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: ' . $msg_text;
				if (defined('DEBUG_EXTRA') && $backtrace) { echo '<br><br><b>BACKTRACE</b><br><br><div style="font-family: monospace;">' . $backtrace . '</div>'; }
				echo '<br>' . "\n";
			}

			if (isset($db))
			{
				$log_text = "<b>FILE:</b> {$errfile}<br><b>LINE:</b> {$errline}<br><b>TEXT:</b> {$msg_text}";
				if (!empty($_SERVER['REQUEST_URI'])) { $log_text .= '<br><b>PAGE:</b> ' . htmlspecialchars($_SERVER['REQUEST_URI']); }
				if ($backtrace) { $log_text .= '<br><br><b>BACKTRACE</b><br><br>' . $backtrace; }

				add_log('critical', 'LOG_ERROR_GENERAL', 'PHP ' . $err_types[$errno], $log_text);
			}

			return;

		break;

		case E_USER_ERROR:

			if (!empty($user) && !empty($user->lang))
			{
				$msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
				$msg_title = (!isset($msg_title)) ? $user->lang['GENERAL_ERROR'] : ((!empty($user->lang[$msg_title])) ? $user->lang[$msg_title] : $msg_title);

				$l_notify = '';

				if (!empty($config['board_contact']))
				{
					$l_notify = '<p>' . sprintf($user->lang['NOTIFY_ADMIN_EMAIL'], $config['board_contact']) . '</p>';
				}
			}
			else
			{
				$msg_title = 'General Error';
				$l_notify = '';

				if (!empty($config['board_contact']))
				{
					$l_notify = '<p>Please notify the board administrator or webmaster: <a href="mailto:' . $config['board_contact'] . '">' . $config['board_contact'] . '</a></p>';
				}
			}

			$backtrace = format_backtrace($backtrace);

			if ((defined('DEBUG') || defined('IN_CRON') || defined('IMAGE_OUTPUT')) && isset($db))
			{
				$log_text = $msg_text;
				if (!empty($_SERVER['REQUEST_URI'])) { $log_text .= '<br><br><b>PAGE:</b> ' . htmlspecialchars($_SERVER['REQUEST_URI']); }
				if ($backtrace) { $log_text .= '<br><br><b>BACKTRACE</b><br><br>' . $backtrace; }

				// let's avoid loops
				$db->sql_return_on_error(true);
				add_log('critical', 'LOG_ERROR_GENERAL', $msg_title, $log_text);
				$db->sql_return_on_error(false);
			}

			// Do not send 200 OK, but service unavailable on errors
			http_response_code(503);

			garbage_collection();

			// Try to not call the adm page data...

			echo '<!DOCTYPE html>';
			echo '<html dir="ltr">';
			echo '<head>';
			echo '<meta charset="UTF-8" />';
			echo '<title>' . $msg_title . '</title>';
			echo '<style>' . "\n";
			echo '* { margin: 0; padding: 0; } html { font-size: 100%; height: 100%; overflow-y: scroll; margin-bottom: 1px; background-color: #E4EDF0; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #536482; background: #E4EDF0; font-size: 62.5%; margin: 0; } ';
			echo 'a, a:active, a:visited { color: #006699; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } ';
			echo '#wrap { padding: 20px; min-width: 615px; } #page-footer { clear: both; font-size: 1em; text-align: center; } ';
			echo '.panel { margin: 4px 0; background-color: #FFFFFF; border: solid 1px  #A9B8C2; } ';
			echo '#errorpage #content { padding: 10px; } #errorpage #content h1 { line-height: 1.2em; margin-bottom: 0; color: #DF075C; } ';
			echo '#errorpage #content div { margin-top: 10px; color: #333333; font: 1.5em monospace; text-decoration: none; line-height: 120%; text-align: left; }';
			echo "\n";
			echo '</style>';
			echo '</head>';
			echo '<body id="errorpage">';
			echo '<div id="wrap">';
			echo '	<div class="panel">';
			echo '		<div id="content">';
			echo '			<h1>' . $msg_title . '</h1>';
			echo '			<div>' . $msg_text . (($backtrace && defined('DEBUG_EXTRA')) ? '<br><br><b>BACKTRACE</b><br><br>' . $backtrace : '') . '</div>';
			echo '		</div>';
			echo '	</div>';
			echo '	<div id="page-footer">' . $l_notify . 'Powered by <a href="//phpbbex.com/">phpBBex</a></div>';
			echo '</div>';
			echo '</body>';
			echo '</html>';

			exit_handler();

			// On a fatal error (and E_USER_ERROR *is* fatal) we never want other scripts to continue and force an exit here.
			exit;
		break;

		case E_USER_WARNING:
		case E_USER_NOTICE:

			define('IN_ERROR_HANDLER', true);

			if (empty($user->data))
			{
				$user->session_begin();
			}

			// We re-init the auth array to get correct results on login/logout
			$auth->acl($user->data);

			if (empty($user->lang))
			{
				$user->setup();
			}

			if ($msg_text == 'ERROR_NO_ATTACHMENT' || $msg_text == 'NO_FORUM' || $msg_text == 'NO_TOPIC' || $msg_text == 'NO_USER')
			{
				http_response_code(404);
			}

			$msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
			$msg_title = (!isset($msg_title)) ? $user->lang['INFORMATION'] : ((!empty($user->lang[$msg_title])) ? $user->lang[$msg_title] : $msg_title);

			if (!defined('HEADER_INC'))
			{
				if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
				{
					adm_page_header($msg_title);
				}
				else
				{
					page_header($msg_title, false);
				}
			}

			$template->set_filenames(array(
				'body' => 'message_body.html')
			);

			$template->assign_vars(array(
				'MESSAGE_TITLE'		=> $msg_title,
				'MESSAGE_TEXT'		=> $msg_text,
				'S_USER_WARNING'	=> ($errno == E_USER_WARNING) ? true : false,
				'S_USER_NOTICE'		=> ($errno == E_USER_NOTICE) ? true : false)
			);

			// We do not want the cron script to be called on error messages
			define('IN_CRON', true);

			if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
			{
				adm_page_footer();
			}
			else
			{
				page_footer();
			}

			exit_handler();
		break;
	}

	// If we notice an error not handled here we pass this back to PHP by returning false
	// This may not work for all php versions
	return false;
}

if (!function_exists('adm_back_link'))
{
	/**
	* Generate back link for acp pages
	*/
	function adm_back_link($u_action)
	{
		return '<br /><br /><a href="' . $u_action . '">&laquo; ' . user_lang('BACK_TO_PREV') . '</a>';
	}
}

/**
* Removes absolute path to phpBB root directory from error messages
* and converts backslashes to forward slashes.
*
* @param string $errfile	Absolute file path
*							(e.g. /var/www/phpbb3/phpBB/includes/functions.php)
*							Please note that if $errfile is outside of the phpBB root,
*							the root path will not be found and can not be filtered.
* @return string			Relative file path
*							(e.g. /includes/functions.php)
*/
function stk_filter_root_path($errfile)
{
	static $root_path;

	if (empty($root_path))
	{
		$root_path = phpbb_realpath(__DIR__ . '/../');
	}

	return str_replace(array($root_path, '\\'), array('[ROOT]', '/'), $errfile);
}

/**
 * A function that behaves like `array_walk` but instead
 * of walking over the values this function walks
 * over the keys
 */
function stk_array_walk_keys(&$array, $callback)
{
	if (!is_callable($callback))
	{
		return;
	}

	$tmp_array = array();
	foreach ($array as $key => $null)
	{
		$walked_key = call_user_func($callback, $key);
		$tmp_array[$walked_key] = $array[$key];
		unset($array[$key]);
	}
	$array = $tmp_array;
}
