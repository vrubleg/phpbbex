<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
define('ADMIN_START', true);
define('NEED_SID', true);

if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './../'); }
require_once(PHPBB_ROOT_PATH . 'common.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_module.php');

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('acp/common');

// Have they authenticated (again) as an admin for this session?
if (!isset($user->data['session_admin']) || !$user->data['session_admin'])
{
	login_box('', $user->lang['LOGIN_ADMIN_CONFIRM'], $user->lang['LOGIN_ADMIN_SUCCESS'], true, false);
}

// Is user any type of admin? No, then stop here, each script needs to
// check specific permissions but this is a catchall
if (!$auth->acl_get('a_'))
{
	trigger_error('NO_ADMIN');
}

// We define the admin variables now, because the user is now able to use the admin related features...
define('IN_ADMIN', true);
if (!defined('PHPBB_ADMIN_PATH')) { define('PHPBB_ADMIN_PATH', './'); }

// Some oft used variables
$module_id		= request_var('i', '');
$mode			= request_var('mode', '');

// Set custom template for admin area
$template->set_custom_template(PHPBB_ADMIN_PATH . 'style', 'admin');
$template->assign_var('T_TEMPLATE_PATH', PHPBB_ADMIN_PATH . 'style');

// the acp template is never stored in the database
$user->theme['template_storedb'] = false;

// Instantiate new module
$module = new p_master();

// Instantiate module system and generate list of available modules
$module->list_modules('acp');

// Select the active module
$module->set_active($module_id, $mode);

// Assign data to the template engine for the list of modules
// We do this before loading the active module for correct menu display in trigger_error
$module->assign_tpl_vars(append_sid(PHPBB_ADMIN_PATH . 'index.php'));

// Load and execute the relevant module
$module->load_active();

// Generate the page
adm_page_header($module->get_page_title());

$template->set_filenames(array(
	'body' => $module->get_tpl_name(),
));

adm_page_footer();

/**
* Header for acp pages
*/
function adm_page_header($page_title)
{
	global $config, $db, $user, $template;

	if (defined('HEADER_INC'))
	{
		return;
	}

	define('HEADER_INC', true);

	// gzip_compression
	if ($config['gzip_compress'])
	{
		if (@extension_loaded('zlib') && !headers_sent())
		{
			ob_start('ob_gzhandler');
		}
	}

	$template->assign_vars(array(
		'PAGE_TITLE'			=> $page_title,
		'USERNAME'				=> $user->data['username'],

		'SESSION_ID'			=> $user->session_id,
		'ROOT_PATH'				=> PHPBB_ADMIN_PATH,
		'PHPBB_ROOT_PATH'		=> PHPBB_ROOT_PATH,

		'U_LOGOUT'				=> append_sid(PHPBB_ROOT_PATH . 'ucp.php', 'mode=logout'),
		'U_ADM_LOGOUT'			=> append_sid(PHPBB_ADMIN_PATH . 'index.php', 'action=admlogout'),
		'U_ADM_INDEX'			=> append_sid(PHPBB_ADMIN_PATH . 'index.php'),
		'U_INDEX'				=> append_sid(PHPBB_ROOT_PATH . 'index.php', false, true, false),

		'T_IMAGES_PATH'			=> PHPBB_ROOT_PATH . 'images/',

		'ICON_MOVE_UP'				=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_up.gif" alt="' . $user->lang['MOVE_UP'] . '" title="' . $user->lang['MOVE_UP'] . '" />',
		'ICON_MOVE_UP_DISABLED'		=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_up_disabled.gif" alt="' . $user->lang['MOVE_UP'] . '" title="' . $user->lang['MOVE_UP'] . '" />',
		'ICON_MOVE_DOWN'			=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_down.gif" alt="' . $user->lang['MOVE_DOWN'] . '" title="' . $user->lang['MOVE_DOWN'] . '" />',
		'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_down_disabled.gif" alt="' . $user->lang['MOVE_DOWN'] . '" title="' . $user->lang['MOVE_DOWN'] . '" />',
		'ICON_EDIT'					=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_edit.gif" alt="' . $user->lang['EDIT'] . '" title="' . $user->lang['EDIT'] . '" />',
		'ICON_EDIT_DISABLED'		=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_edit_disabled.gif" alt="' . $user->lang['EDIT'] . '" title="' . $user->lang['EDIT'] . '" />',
		'ICON_DELETE'				=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_delete.gif" alt="' . $user->lang['DELETE'] . '" title="' . $user->lang['DELETE'] . '" />',
		'ICON_DELETE_DISABLED'		=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_delete_disabled.gif" alt="' . $user->lang['DELETE'] . '" title="' . $user->lang['DELETE'] . '" />',
		'ICON_SYNC'					=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_sync.gif" alt="' . $user->lang['RESYNC'] . '" title="' . $user->lang['RESYNC'] . '" />',
		'ICON_SYNC_DISABLED'		=> '<img src="' . PHPBB_ADMIN_PATH . 'images/icon_sync_disabled.gif" alt="' . $user->lang['RESYNC'] . '" title="' . $user->lang['RESYNC'] . '" />',

		'S_USER_LANG'			=> $user->lang['USER_LANG'],
	));

	if (!headers_sent())
	{
		header('Content-Type: text/html; charset=UTF-8');
		header('Cache-Control: private, no-cache="set-cookie"');
	}
}

/**
* Page footer for acp pages
*/
function adm_page_footer($copyright_html = true)
{
	global $db, $config, $template, $user, $auth, $cache, $starttime;

	// Output page creation time
	if (defined('DEBUG'))
	{
		$totaltime = microtime(true) - $starttime;

		if (!empty($_REQUEST['explain']) && $auth->acl_get('a_') && defined('DEBUG_EXTRA') && method_exists($db, 'sql_report'))
		{
			$db->sql_report('display');
		}

		$debug_output = sprintf('Time : %.3fs | ' . $db->sql_num_queries() . ' Queries | GZIP : ' . (($config['gzip_compress']) ? 'On' : 'Off') . (($user->load) ? ' | Load : ' . $user->load : ''), $totaltime);

		if ($auth->acl_get('a_') && defined('DEBUG_EXTRA'))
		{
			if ($memory_usage = memory_get_usage())
			{
				global $base_memory_usage;
				$memory_usage -= $base_memory_usage;
				$memory_usage = get_formatted_filesize($memory_usage);

				$debug_output .= ' | Memory Usage: ' . $memory_usage;
			}

			$debug_output .= ' | <a href="' . build_url() . '&amp;explain=1">Explain</a>';
		}
	}

	$template->assign_vars(array(
		'DEBUG_OUTPUT'		=> (defined('DEBUG')) ? $debug_output : '',
		'L_POWERED_BY'		=> $copyright_html ? $user->lang('POWERED_BY', POWERED_BY) : '',
	));

	$template->display('body');

	garbage_collection();
	exit_handler();
}

/**
* Generate back link for acp pages
*/
function adm_back_link($u_action)
{
	global $user;
	return '<br /><br /><a href="' . $u_action . '">&laquo; ' . $user->lang['BACK_TO_PREV'] . '</a>';
}

/**
* Build select field options in acp pages
*/
function build_select($option_ary, $option_default = false)
{
	global $user;

	$html = '';
	foreach ($option_ary as $value => $title)
	{
		$selected = ($option_default !== false && $value == $option_default) ? ' selected="selected"' : '';
		$html .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$title] . '</option>';
	}

	return $html;
}

/**
* Build radio fields in acp pages
*/
function h_radio($name, $input_ary, $input_default = false, $id = false, $key = false, $separator = '')
{
	global $user;

	$html = '';
	$id_assigned = false;
	foreach ($input_ary as $value => $title)
	{
		$selected = ($input_default !== false && $value == $input_default) ? ' checked="checked"' : '';
		$html .= '<label><input type="radio" name="' . $name . '"' . (($id && !$id_assigned) ? ' id="' . $id . '"' : '') . ' value="' . $value . '"' . $selected . (($key) ? ' accesskey="' . $key . '"' : '') . ' class="radio" /> ' . $user->lang[$title] . '</label>' . $separator;
		$id_assigned = true;
	}

	return $html;
}

/**
* Build configuration template for acp configuration pages
*/
function build_cfg_template($tpl_type, $key, &$new, $config_key, $vars)
{
	global $user, $module;

	$tpl = '';
	$name = 'config[' . $config_key . ']';

	// Make sure there is no notice printed out for non-existent config options (we simply set them)
	if (!isset($new[$config_key]))
	{
		$new[$config_key] = '';
	}

	switch ($tpl_type[0])
	{
		case 'html':
			$new[$config_key] = htmlspecialchars($new[$config_key]);
		case 'text':
		case 'password':
			$size = (int) $tpl_type[1];
			$maxlength = (int) $tpl_type[2];

			$tpl = '<input id="' . $key . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '" value="' . $new[$config_key] . '"' . (($tpl_type[0] === 'password') ?  ' autocomplete="off"' : '') . ' />';
		break;

		case 'dimension':
			$size = (int) $tpl_type[1];
			$maxlength = (int) $tpl_type[2];

			$tpl = '<input id="' . $key . '" type="text"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="config[' . $config_key . '_width]" value="' . $new[$config_key . '_width'] . '" /> x <input type="text"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="config[' . $config_key . '_height]" value="' . $new[$config_key . '_height'] . '" />';
		break;

		case 'htmlarea':
			$new[$config_key] = htmlspecialchars($new[$config_key]);
		case 'textarea':
			$rows = (int) $tpl_type[1];
			$cols = (int) $tpl_type[2];

			$tpl = '<textarea id="' . $key . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $new[$config_key] . '</textarea>';
		break;

		case 'radio':
			$key_yes	= ($new[$config_key]) ? ' checked="checked"' : '';
			$key_no		= (!$new[$config_key]) ? ' checked="checked"' : '';

			$tpl_type_cond = explode('_', $tpl_type[1]);
			$type_no = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

			$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $key_no . ' class="radio" /> ' . (($type_no) ? $user->lang['NO'] : $user->lang['DISABLED']) . '</label>';
			$tpl_yes = '<label><input type="radio" id="' . $key . '" name="' . $name . '" value="1"' . $key_yes . ' class="radio" /> ' . (($type_no) ? $user->lang['YES'] : $user->lang['ENABLED']) . '</label>';

			$tpl = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . $tpl_no : $tpl_no . $tpl_yes;
		break;

		case 'select':
		case 'custom':

			$return = '';

			if (isset($vars['method']))
			{
				$call = array($module->module, $vars['method']);
			}
			else if (isset($vars['function']))
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
					if (is_string($value)) switch ($value)
					{
						case '{CONFIG_VALUE}':
							$value = $new[$config_key];
						break;

						case '{KEY}':
							$value = $key;
						break;
					}

					$args[] = $value;
				}
			}
			else
			{
				$args = array($new[$config_key], $key);
			}

			$return = call_user_func_array($call, $args);

			if ($tpl_type[0] == 'select')
			{
				$tpl = '<select id="' . $key . '" name="' . $name . '">' . $return . '</select>';
			}
			else
			{
				$tpl = $return;
			}

		break;

		default:
		break;
	}

	if (isset($vars['append']))
	{
		$tpl .= $vars['append'];
	}

	return $tpl;
}

/**
* Going through a config array and validate values, writing errors to $error. The validation method  accepts parameters separated by ':' for string and int.
* The first parameter defines the type to be used, the second the lower bound and the third the upper bound. Only the type is required.
*/
function validate_config_vars($config_vars, &$cfg_array, &$error)
{
	global $user;

	$type	= 0;
	$min	= 1;
	$max	= 2;

	foreach ($config_vars as $config_name => $config_definition)
	{
		if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
		{
			continue;
		}

		if (!isset($config_definition['validate']))
		{
			continue;
		}

		$validator = explode(':', $config_definition['validate']);

		// Validate a bit. ;) (0 = type, 1 = min, 2= max)
		switch ($validator[$type])
		{
			case 'string':
				$length = utf8_strlen($cfg_array[$config_name]);

				// the column is a VARCHAR
				$validator[$max] = (isset($validator[$max])) ? min(1000, $validator[$max]) : 1000;

				if (isset($validator[$min]) && $length < $validator[$min])
				{
					$error[] = sprintf($user->lang['SETTING_TOO_SHORT'], $user->lang[$config_definition['lang']], $validator[$min]);
				}
				else if (isset($validator[$max]) && $length > $validator[2])
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LONG'], $user->lang[$config_definition['lang']], $validator[$max]);
				}
			break;

			case 'bool':
				$cfg_array[$config_name] = ($cfg_array[$config_name]) ? 1 : 0;
			break;

			case 'int':
				$cfg_array[$config_name] = (int) $cfg_array[$config_name];

				if (isset($validator[$min]) && $cfg_array[$config_name] < $validator[$min])
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LOW'], $user->lang[$config_definition['lang']], $validator[$min]);
				}
				else if (isset($validator[$max]) && $cfg_array[$config_name] > $validator[$max])
				{
					$error[] = sprintf($user->lang['SETTING_TOO_BIG'], $user->lang[$config_definition['lang']], $validator[$max]);
				}

				if (strpos($config_name, '_max') !== false)
				{
					// Min/max pairs of settings should ensure that min <= max
					// Replace _max with _min to find the name of the minimum
					// corresponding configuration variable
					$min_name = str_replace('_max', '_min', $config_name);

					if (isset($cfg_array[$min_name]) && is_numeric($cfg_array[$min_name]) && $cfg_array[$config_name] && $cfg_array[$min_name] && $cfg_array[$config_name] < $cfg_array[$min_name])
					{
						// A minimum value exists and the maximum value is less than it
						$error[] = sprintf($user->lang['SETTING_TOO_LOW'], $user->lang[$config_definition['lang']], (int) $cfg_array[$min_name]);
					}
				}
			break;

			case 'email':
				if (!preg_match('/^' . get_preg_expression('email') . '$/i', $cfg_array[$config_name]))
				{
					$error[] = $user->lang['EMAIL_INVALID_EMAIL'];
				}
			break;

			// Absolute path
			case 'script_path':
				if (!$cfg_array[$config_name])
				{
					break;
				}

				$destination = str_replace('\\', '/', $cfg_array[$config_name]);

				if ($destination !== '/')
				{
					// Adjust destination path (no trailing slash)
					if (substr($destination, -1, 1) == '/')
					{
						$destination = substr($destination, 0, -1);
					}

					$destination = str_replace(array('../', './'), '', $destination);

					if ($destination[0] != '/')
					{
						$destination = '/' . $destination;
					}
				}

				$cfg_array[$config_name] = trim($destination);

			break;

			// Absolute path
			case 'lang':
				if (!$cfg_array[$config_name])
				{
					break;
				}

				$cfg_array[$config_name] = basename($cfg_array[$config_name]);

				if (!file_exists(PHPBB_ROOT_PATH . 'language/' . $cfg_array[$config_name] . '/'))
				{
					$error[] = $user->lang['WRONG_DATA_LANG'];
				}
			break;

			// Relative path (appended PHPBB_ROOT_PATH).
			case 'rpath':
			case 'rwpath':
				if (!$cfg_array[$config_name])
				{
					break;
				}

				$destination = $cfg_array[$config_name];

				// Adjust destination path (no trailing slash)
				if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
				{
					$destination = substr($destination, 0, -1);
				}

				$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
				if ($destination && ($destination[0] == '/' || $destination[0] == "\\"))
				{
					$destination = '';
				}

				$cfg_array[$config_name] = trim($destination);

			// Absolute file path
			case 'absolute_path':
			case 'absolute_path_writable':
			// Path being relative (still prefixed by PHPBB_ROOT_PATH), but with the ability to escape the root dir...
			case 'path':
			case 'wpath':

				if (!$cfg_array[$config_name])
				{
					break;
				}

				$cfg_array[$config_name] = trim($cfg_array[$config_name]);

				// Make sure no NUL byte is present...
				if (strpos($cfg_array[$config_name], "\0") !== false || strpos($cfg_array[$config_name], '%00') !== false)
				{
					$cfg_array[$config_name] = '';
					break;
				}

				$path = in_array($config_definition['validate'], array('wpath', 'path', 'rpath', 'rwpath')) ? PHPBB_ROOT_PATH . $cfg_array[$config_name] : $cfg_array[$config_name];

				if (!file_exists($path))
				{
					$error[] = sprintf($user->lang['DIRECTORY_DOES_NOT_EXIST'], $cfg_array[$config_name]);
				}

				if (file_exists($path) && !is_dir($path))
				{
					$error[] = sprintf($user->lang['DIRECTORY_NOT_DIR'], $cfg_array[$config_name]);
				}

				// Check if the path is writable
				if ($config_definition['validate'] == 'wpath' || $config_definition['validate'] == 'rwpath' || $config_definition['validate'] === 'absolute_path_writable')
				{
					if (file_exists($path) && !phpbb_is_writable($path))
					{
						$error[] = sprintf($user->lang['DIRECTORY_NOT_WRITABLE'], $cfg_array[$config_name]);
					}
				}

			break;
		}
	}

	return;
}

/**
* Checks whatever or not a variable is OK for use in the Database
* param mixed $value_ary An array of the form array(array('lang' => ..., 'value' => ..., 'column_type' =>))'
* param mixed $error The error array
*/
function validate_range($value_ary, &$error)
{
	global $user;

	$column_types = array(
		'BOOL'	=> array('php_type' => 'int', 		'min' => 0, 				'max' => 1),
		'USINT'	=> array('php_type' => 'int',		'min' => 0, 				'max' => 65535),
		'UINT'	=> array('php_type' => 'int', 		'min' => 0, 				'max' => (int) 0x7fffffff),
		// Do not use (int) 0x80000000 - it evaluates to different
		// values on 32-bit and 64-bit systems.
		// Apparently -2147483648 is a float on 32-bit systems,
		// despite fitting in an int, thus explicit cast is needed.
		'INT'	=> array('php_type' => 'int', 		'min' => (int) -2147483648,	'max' => (int) 0x7fffffff),
		'TINT'	=> array('php_type' => 'int',		'min' => -128,				'max' => 127),

		'VCHAR'	=> array('php_type' => 'string', 	'min' => 0, 				'max' => 255),
	);
	foreach ($value_ary as $value)
	{
		$column = explode(':', $value['column_type']);
		$max = $min = 0;
		$type = 0;
		if (!isset($column_types[$column[0]]))
		{
			continue;
		}
		else
		{
			$type = $column_types[$column[0]];
		}

		switch ($type['php_type'])
		{
			case 'string' :
				$max = (isset($column[1])) ? min($column[1],$type['max']) : $type['max'];
				if (utf8_strlen($value['value']) > $max)
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LONG'], $user->lang[$value['lang']], $max);
				}
			break;

			case 'int':
				$min = (isset($column[1])) ? max($column[1],$type['min']) : $type['min'];
				$max = (isset($column[2])) ? min($column[2],$type['max']) : $type['max'];
				if ($value['value'] < $min)
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LOW'], $user->lang[$value['lang']], $min);
				}
				else if ($value['value'] > $max)
				{
					$error[] = sprintf($user->lang['SETTING_TOO_BIG'], $user->lang[$value['lang']], $max);
				}
			break;
		}
	}
}
