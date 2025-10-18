<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
define('IN_INSTALL', true);
if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './../'); }
require_once(PHPBB_ROOT_PATH . 'includes/startup.php');

if (defined('PHPBB_INSTALLED') && !file_exists(PHPBB_ROOT_PATH . 'cache/install_lock'))
{
	header('Location: ' . PHPBB_ROOT_PATH);
	exit();
}

// Try to override some limits - maybe it helps some...
@set_time_limit(0);
$mem_limit = @ini_get('memory_limit');
if (!empty($mem_limit))
{
	$unit = strtolower(substr($mem_limit, -1, 1));
	$mem_limit = (int) $mem_limit;

	if ($unit == 'k')
	{
		$mem_limit = floor($mem_limit / 1024);
	}
	else if ($unit == 'g')
	{
		$mem_limit *= 1024;
	}
	else if (is_numeric($unit))
	{
		$mem_limit = floor((int) ($mem_limit . $unit) / 1048576);
	}
	$mem_limit = max(128, $mem_limit) . 'M';
}
else
{
	$mem_limit = '128M';
}
@ini_set('memory_limit', $mem_limit);

// Include essential scripts
require_once(PHPBB_ROOT_PATH . 'includes/functions.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_content.php');
require_once(PHPBB_ROOT_PATH . 'includes/auth.php');
require_once(PHPBB_ROOT_PATH . 'includes/session.php');
require_once(PHPBB_ROOT_PATH . 'includes/template.php');
require_once(PHPBB_ROOT_PATH . 'includes/acm/acm_file.php');
require_once(PHPBB_ROOT_PATH . 'includes/cache.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');
require_once(PHPBB_ROOT_PATH . 'includes/utf/utf_tools.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_install.php');

// Try and load an appropriate language if required
$language = basename(request_var('language', ''));

if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !$language)
{
	$accept_lang_ary = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
	foreach ($accept_lang_ary as $accept_lang)
	{
		// Set correct format ... guess full xx_yy form
		$accept_lang = substr($accept_lang, 0, 2) . '_' . substr($accept_lang, 3, 2);

		if (file_exists(PHPBB_ROOT_PATH . 'language/' . $accept_lang) && is_dir(PHPBB_ROOT_PATH . 'language/' . $accept_lang))
		{
			$language = $accept_lang;
			break;
		}
		else
		{
			// No match on xx_yy so try xx
			$accept_lang = substr($accept_lang, 0, 2);
			if (file_exists(PHPBB_ROOT_PATH . 'language/' . $accept_lang) && is_dir(PHPBB_ROOT_PATH . 'language/' . $accept_lang))
			{
				$language = $accept_lang;
				break;
			}
		}
	}
}

// No appropriate language found ... so let's use the first one in the language
// dir, this may or may not be English
if (!$language)
{
	$dir = @opendir(PHPBB_ROOT_PATH . 'language');

	if (!$dir)
	{
		die('Unable to access the language directory');
		exit;
	}

	while (($file = readdir($dir)) !== false)
	{
		$path = PHPBB_ROOT_PATH . 'language/' . $file;

		if (!is_file($path) && !is_link($path) && file_exists($path . '/iso.txt'))
		{
			$language = $file;
			break;
		}
	}
	closedir($dir);
}

if (!file_exists(PHPBB_ROOT_PATH . 'language/' . $language) || !is_dir(PHPBB_ROOT_PATH . 'language/' . $language))
{
	die('No language found!');
}

// And finally, load the relevant language files
require(PHPBB_ROOT_PATH . 'language/' . $language . '/common.php');
require(PHPBB_ROOT_PATH . 'language/' . $language . '/acp/common.php');
require(PHPBB_ROOT_PATH . 'language/' . $language . '/acp/board.php');
require(PHPBB_ROOT_PATH . 'language/' . $language . '/install.php');
require(PHPBB_ROOT_PATH . 'language/' . $language . '/posting.php');

// usually we would need every single constant here - and it would be consistent. For 3.0.x, use a dirty hack... :(

// Define needed constants
define('CHMOD_ALL', 7);
define('CHMOD_READ', 4);
define('CHMOD_WRITE', 2);
define('CHMOD_EXECUTE', 1);

$mode = request_var('mode', 'install');
$sub = request_var('sub', '');

// Set PHP error handler to ours
set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
	$msg_handler = defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler';
	$msg_handler($errno, $errstr, $errfile, $errline, array_slice(debug_backtrace(), 1));
});
set_exception_handler(function ($e)
{
	$msg_handler = defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler';
	$msg_handler(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
});

$user = new phpbb_user();
$auth = new phpbb_auth();
$cache = new phpbb_cache();
$template = new phpbb_template();

// Set some standard variables we want to force
$config = [
	'load_tplcompile'	=> '1'
];

$template->set_custom_template('../adm/style', 'admin');
$template->assign_var('T_TEMPLATE_PATH', '../adm/style');

// the acp template is never stored in the database
$user->theme['template_storedb'] = false;

$install = new module();

$install->create('install', 'index.php', $mode, $sub);
$install->load();

// Generate the page
$install->page_header();
$install->generate_navigation();

$template->set_filenames([
	'body' => $install->get_tpl_name()]
);

$install->page_footer();

class module
{
	var $id = 0;
	var $type = 'install';
	var $module_ary = [];
	var $filename;
	var $module_url = '';
	var $tpl_name = '';
	var $mode;
	var $sub;
	var $module;

	/**
	* Private methods, should not be overwritten
	*/
	function create($module_type, $module_url, $selected_mod = false, $selected_submod = false)
	{
		global $db, $config;

		$module = [];

		// Grab module information using Bart's "neat-o-module" system (tm)
		$dir = @opendir('.');

		if (!$dir)
		{
			$this->error('Unable to access the installation directory', __LINE__, __FILE__);
		}

		$setmodules = 1;
		while (($file = readdir($dir)) !== false)
		{
			if (preg_match('#^install_(.*?)\.php' . '$#', $file))
			{
				require_once($file);
			}
		}
		closedir($dir);

		unset($setmodules);

		if (!sizeof($module))
		{
			$this->error('No installation modules found', __LINE__, __FILE__);
		}

		// Order to use and count further if modules get assigned to the same position or not having an order
		$max_module_order = 1000;

		foreach ($module as $row)
		{
			// Module order not specified or module already assigned at this position?
			if (!isset($row['module_order']) || isset($this->module_ary[$row['module_order']]))
			{
				$row['module_order'] = $max_module_order;
				$max_module_order++;
			}

			$this->module_ary[$row['module_order']]['name'] = $row['module_title'];
			$this->module_ary[$row['module_order']]['filename'] = $row['module_filename'];
			$this->module_ary[$row['module_order']]['subs'] = $row['module_subs'];
			$this->module_ary[$row['module_order']]['stages'] = $row['module_stages'];

			if (strtolower($selected_mod) == strtolower($row['module_title']))
			{
				$this->id = (int) $row['module_order'];
				$this->filename = (string) $row['module_filename'];
				$this->module_url = (string) $module_url;
				$this->mode = (string) $selected_mod;
				// Check that the sub-mode specified is valid or set a default if not
				if (is_array($row['module_subs']))
				{
					$this->sub = strtolower((in_array(strtoupper($selected_submod), $row['module_subs'])) ? $selected_submod : $row['module_subs'][0]);
				}
				else if (is_array($row['module_stages']))
				{
					$this->sub = strtolower((in_array(strtoupper($selected_submod), $row['module_stages'])) ? $selected_submod : $row['module_stages'][0]);
				}
				else
				{
					$this->sub = '';
				}
			}
		} // END foreach
	} // END create

	/**
	* Load and run the relevant module if applicable
	*/
	function load($mode = false, $run = true)
	{
		if ($run)
		{
			if (!empty($mode))
			{
				$this->mode = $mode;
			}

			$module = $this->filename;
			if (!class_exists($module))
			{
				$this->error('Module "' . htmlspecialchars($module) . '" not accessible.', __LINE__, __FILE__);
			}
			$this->module = new $module($this);

			if (method_exists($this->module, 'main'))
			{
				$this->module->main($this->mode, $this->sub);
			}
		}
	}

	/**
	* Output the standard page header
	*/
	function page_header()
	{
		if (defined('HEADER_INC'))
		{
			return;
		}

		define('HEADER_INC', true);
		global $template, $lang, $stage;

		$template->assign_vars([
			'L_CHANGE'				=> $lang['CHANGE'],
			'L_INSTALL_PANEL'		=> $lang['INSTALL_PANEL'],
			'L_SELECT_LANG'			=> $lang['SELECT_LANG'],
			'L_SKIP'				=> $lang['SKIP'],
			'PAGE_TITLE'			=> $this->get_page_title(),
			'T_IMAGE_PATH'			=> PHPBB_ROOT_PATH . 'adm/images/',
			'L_POWERED_BY'			=> sprintf($lang['POWERED_BY'], POWERED_BY),

			'S_USER_LANG'			=> $lang['USER_LANG'],
			]
		);

		if (!headers_sent())
		{
			header('Content-Type: text/html; charset=UTF-8');
			header('Cache-Control: private, no-cache="set-cookie"');
		}
	}

	/**
	* Output the standard page footer
	*/
	function page_footer()
	{
		global $db, $template;

		$template->display('body');

		// Close our DB connection.
		if (!empty($db) && is_object($db))
		{
			$db->sql_close();
		}

		if (function_exists('exit_handler'))
		{
			exit_handler();
		}
	}

	/**
	* Returns desired template name
	*/
	function get_tpl_name()
	{
		return $this->module->tpl_name . '.html';
	}

	/**
	* Returns the desired page title
	*/
	function get_page_title()
	{
		global $lang;

		if (!isset($this->module->page_title))
		{
			return '';
		}

		return $lang[$this->module->page_title] ?? $this->module->page_title;
	}

	/**
	* Generate an HTTP/1.1 header to redirect the user to another page
	* This is used during the installation when we do not have a database available to call the normal redirect function
	* @param string $page The page to redirect to relative to the installer root path
	*/
	function redirect($page)
	{
		$url = (HTTP_SECURE ? 'https://' : 'http://') . HTTP_HOST . (HTTP_PORT ? ':' . HTTP_PORT : '') . HTTP_ROOT . $page;
		header('Location: ' . $url);
		exit;
	}

	/**
	* Generate the navigation tabs
	*/
	function generate_navigation()
	{
		global $lang, $template, $language;

		if (is_array($this->module_ary))
		{
			@ksort($this->module_ary);
			foreach ($this->module_ary as $cat_ary)
			{
				$cat = $cat_ary['name'];
				$l_cat = (!empty($lang['CAT_' . $cat])) ? $lang['CAT_' . $cat] : preg_replace('#_#', ' ', $cat);
				$cat = strtolower($cat);
				$url = $this->module_url . "?mode=$cat&amp;language=$language";

				if ($this->mode == $cat)
				{
					$template->assign_block_vars('t_block1', [
						'L_TITLE'		=> $l_cat,
						'S_SELECTED'	=> true,
						'U_TITLE'		=> $url,
					]);

					if (is_array($this->module_ary[$this->id]['subs']))
					{
						$subs = $this->module_ary[$this->id]['subs'];
						foreach ($subs as $option)
						{
							$l_option = (!empty($lang['SUB_' . $option])) ? $lang['SUB_' . $option] : preg_replace('#_#', ' ', $option);
							$option = strtolower($option);
							$url = $this->module_url . '?mode=' . $this->mode . "&amp;sub=$option&amp;language=$language";

							$template->assign_block_vars('l_block1', [
								'L_TITLE'		=> $l_option,
								'S_SELECTED'	=> ($this->sub == $option),
								'U_TITLE'		=> $url,
							]);
						}
					}

					if (is_array($this->module_ary[$this->id]['stages']))
					{
						$subs = $this->module_ary[$this->id]['stages'];
						$matched = false;
						foreach ($subs as $option)
						{
							$l_option = (!empty($lang['STAGE_' . $option])) ? $lang['STAGE_' . $option] : preg_replace('#_#', ' ', $option);
							$option = strtolower($option);
							$matched = ($this->sub == $option) ? true : $matched;

							$template->assign_block_vars('l_block2', [
								'L_TITLE'		=> $l_option,
								'S_SELECTED'	=> ($this->sub == $option),
								'S_COMPLETE'	=> !$matched,
							]);
						}
					}
				}
				else
				{
					$template->assign_block_vars('t_block1', [
						'L_TITLE'		=> $l_cat,
						'S_SELECTED'	=> false,
						'U_TITLE'		=> $url,
					]);
				}
			}
		}
	}

	/**
	* Output an error message
	* If skip is true, return and continue execution, else exit
	*/
	function error($error, $line, $file, $skip = false)
	{
		global $lang, $db, $template;

		if ($skip)
		{
			$template->assign_block_vars('checks', [
				'S_LEGEND'	=> true,
				'LEGEND'	=> $lang['INST_ERR'],
			]);

			$template->assign_block_vars('checks', [
				'TITLE'		=> basename($file) . ' [ ' . $line . ' ]',
				'RESULT'	=> '<b style="color:red">' . $error . '</b>',
			]);

			return;
		}

		echo '<!DOCTYPE html>';
		echo '<html dir="ltr">';
		echo '<head>';
		echo '<meta charset="utf-8" />';
		echo '<title>' . $lang['INST_ERR_FATAL'] . '</title>';
		echo '<link href="../adm/style/admin.css" rel="stylesheet" media="screen" />';
		echo '</head>';
		echo '<body id="errorpage">';
		echo '<div id="wrap">';
		echo '	<div id="page-header">';
		echo '	</div>';
		echo '	<div id="page-body">';
		echo '		<div id="acp">';
		echo '		<div class="panel">';
		echo '			<div id="content">';
		echo '				<h1>' . $lang['INST_ERR_FATAL'] . '</h1>';
		echo '		<p>' . $lang['INST_ERR_FATAL'] . "</p>\n";
		echo '		<p>' . basename($file) . ' [ ' . $line . " ]</p>\n";
		echo '		<p><b>' . $error . "</b></p>\n";
		echo '			</div>';
		echo '		</div>';
		echo '		</div>';
		echo '	</div>';
		echo '	<div id="page-footer">';
		echo '		Powered by ' . POWERED_BY;
		echo '	</div>';
		echo '</div>';
		echo '</body>';
		echo '</html>';

		if (!empty($db) && is_object($db))
		{
			$db->sql_close();
		}

		exit_handler();
	}

	/**
	* Output an error message for a database related problem
	* If skip is true, return and continue execution, else exit
	*/
	function db_error($error, $sql, $line, $file, $skip = false)
	{
		global $lang, $db, $template;

		if ($skip)
		{
			$template->assign_block_vars('checks', [
				'S_LEGEND'	=> true,
				'LEGEND'	=> $lang['INST_ERR_FATAL'],
			]);

			$template->assign_block_vars('checks', [
				'TITLE'		=> basename($file) . ' [ ' . $line . ' ]',
				'RESULT'	=> '<b style="color:red">' . $error . '</b><br />&#187; SQL:' . $sql,
			]);

			return;
		}

		$template->set_filenames([
			'body' => 'install_error.html']
		);
		$this->page_header();
		$this->generate_navigation();

		$template->assign_vars([
			'MESSAGE_TITLE'		=> $lang['INST_ERR_FATAL_DB'],
			'MESSAGE_TEXT'		=> '<p>' . basename($file) . ' [ ' . $line . ' ]</p><p>SQL : ' . $sql . '</p><p><b>' . $error . '</b></p>',
		]);

		// Rollback if in transaction
		if ($db->transaction)
		{
			$db->sql_transaction('rollback');
		}

		$this->page_footer();
	}

	/**
	* Generate the relevant HTML for an input field and the associated label and explanatory text
	*/
	function input_field($name, $type, $value='', $options='')
	{
		global $lang;
		$tpl_type = explode(':', $type);
		$tpl = '';

		switch ($tpl_type[0])
		{
			case 'text':
			case 'password':
				$size = (int) $tpl_type[1];
				$maxlength = (int) $tpl_type[2];
				$autocomplete = (isset($options['autocomplete']) && $options['autocomplete'] == 'off') ? ' autocomplete="off"' : '';

				$tpl = '<input id="' . $name . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '"' . $autocomplete . ' value="' . $value . '" />';
			break;

			case 'textarea':
				$rows = (int) $tpl_type[1];
				$cols = (int) $tpl_type[2];

				$tpl = '<textarea id="' . $name . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $value . '</textarea>';
			break;

			case 'radio':
				$key_yes	= ($value) ? ' checked="checked" id="' . $name . '"' : '';
				$key_no		= (!$value) ? ' checked="checked" id="' . $name . '"' : '';

				$tpl_type_cond = explode('_', $tpl_type[1]);
				$type_no = ($tpl_type_cond[0] != 'disabled' && $tpl_type_cond[0] != 'enabled');

				$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $key_no . ' class="radio" /> ' . (($type_no) ? $lang['NO'] : $lang['DISABLED']) . '</label>';
				$tpl_yes = '<label><input type="radio" name="' . $name . '" value="1"' . $key_yes . ' class="radio" /> ' . (($type_no) ? $lang['YES'] : $lang['ENABLED']) . '</label>';

				$tpl = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . '&nbsp;&nbsp;' . $tpl_no : $tpl_no . '&nbsp;&nbsp;' . $tpl_yes;
			break;

			case 'select':
				eval('$s_options = ' . str_replace('{VALUE}', $value, $options) . ';');
				$tpl = '<select id="' . $name . '" name="' . $name . '">' . $s_options . '</select>';
			break;

			case 'custom':
				eval('$tpl = ' . str_replace('{VALUE}', $value, $options) . ';');
			break;

			default:
			break;
		}

		return $tpl;
	}

	/**
	* Generate the drop down of available language packs
	*/
	function inst_language_select($default = '')
	{
		$dir = @opendir(PHPBB_ROOT_PATH . 'language');

		if (!$dir)
		{
			$this->error('Unable to access the language directory', __LINE__, __FILE__);
		}

		while ($file = readdir($dir))
		{
			$path = PHPBB_ROOT_PATH . 'language/' . $file;

			if ($file == '.' || $file == '..' || is_link($path) || is_file($path) || $file == 'CVS')
			{
				continue;
			}

			if (file_exists($path . '/iso.txt'))
			{
				[$displayname, $localname] = @file($path . '/iso.txt');
				$lang[$localname] = $file;
			}
		}
		closedir($dir);

		@asort($lang);
		@reset($lang);

		$user_select = '';
		foreach ($lang as $displayname => $filename)
		{
			$selected = (strtolower($default) == strtolower($filename)) ? ' selected="selected"' : '';
			$user_select .= '<option value="' . $filename . '"' . $selected . '>' . ucwords($displayname) . '</option>';
		}

		return $user_select;
	}
}
