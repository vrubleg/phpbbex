<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_INSTALL'))
{
	exit;
}

if (!empty($setmodules))
{
	// If phpBB is already installed we do not include this module.
	if (defined('PHPBB_INSTALLED') && !file_exists($phpbb_root_path . 'cache/install_lock'))
	{
		return;
	}

	$module[] = array(
		'module_type'		=> 'install',
		'module_title'		=> 'INSTALL',
		'module_filename'	=> substr(basename(__FILE__), 0, -4),
		'module_order'		=> 10,
		'module_subs'		=> '',
		'module_stages'		=> array('INTRO', 'REQUIREMENTS', 'DATABASE', 'ADMINISTRATOR', 'CREATE_TABLE', 'FINAL'),
		'module_reqs'		=> ''
	);
}

/**
* Installation
*/
class install_install extends module
{
	var $p_master;
	var $page_title;

	function __construct(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($mode, $sub)
	{
		global $lang, $template, $language, $phpbb_root_path, $cache;

		switch ($sub)
		{
			case 'intro':
				$cache->purge();

				$this->page_title = $lang['SUB_INTRO'];

				$template->assign_vars(array(
					'TITLE'			=> $lang['INSTALL_INTRO'],
					'BODY'			=> $lang['INSTALL_INTRO_BODY'],
					'L_SUBMIT'		=> $lang['NEXT_STEP'],
					'S_LANG_SELECT'	=> '<select id="language" name="language">' . $this->p_master->inst_language_select($language) . '</select>',
					'U_ACTION'		=> $this->p_master->module_url . "?mode=$mode&amp;sub=requirements&amp;language=$language",
				));

			break;

			case 'requirements':
				$this->check_server_requirements($mode, $sub);

			break;

			case 'database':
				$this->obtain_database_settings($mode, $sub);

			break;

			case 'administrator':
				$this->obtain_admin_settings($mode, $sub);

			break;

			case 'create_table':
				$this->page_title = $lang['STAGE_CREATE_TABLE'];

				$this->db_connect();
				$this->load_schema($mode, $sub);
				$this->fetch_config();
				$this->build_search_index($mode, $sub);
				$this->add_modules($mode, $sub);
				$this->add_language($mode, $sub);
				$this->add_bots($mode, $sub);

				$template->assign_vars(array(
					'BODY'		=> $lang['STAGE_CREATE_TABLE_EXPLAIN'],
					'L_SUBMIT'	=> $lang['NEXT_STEP'],
					'S_HIDDEN'	=> build_hidden_fields($this->get_submitted_data()),
					'U_ACTION'	=> $this->p_master->module_url . "?mode=$mode&amp;sub=final",
				));
			break;

			case 'final':
				$this->db_connect();
				$this->fetch_config();
				$this->create_config_file($mode, $sub);
			break;
		}

		$this->tpl_name = 'install_install';
	}

	/**
	* Checks that the server we are installing on meets the requirements for running phpBB
	*/
	function check_server_requirements($mode, $sub)
	{
		global $lang, $template, $phpbb_root_path, $language;

		$this->page_title = $lang['STAGE_REQUIREMENTS'];

		$template->assign_vars(array(
			'TITLE'		=> $lang['REQUIREMENTS_TITLE'],
			'BODY'		=> $lang['REQUIREMENTS_EXPLAIN'],
		));

		$passed = array('php' => false, 'db' => false, 'files' => false, 'pcre' => false, 'imagesize' => false,);

		// Test for basic PHP settings
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['PHP_SETTINGS'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_SETTINGS_EXPLAIN'],
		));

		// Test the minimum PHP version
		if (version_compare(PHP_VERSION, '5.6', '<'))
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}
		else
		{
			$passed['php'] = true;

			// We also give feedback on whether we're running in safe mode
			$result = '<strong style="color:green">' . $lang['YES'];
			if (@ini_get('safe_mode') == '1' || strtolower(@ini_get('safe_mode')) == 'on')
			{
				$result .= ', ' . $lang['PHP_SAFE_MODE'];
			}
			$result .= '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_VERSION_REQD'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));

		// Check for url_fopen
		if (@ini_get('allow_url_fopen') == '1' || strtolower(@ini_get('allow_url_fopen')) == 'on')
		{
			$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_URL_FOPEN_SUPPORT'],
			'TITLE_EXPLAIN'	=> $lang['PHP_URL_FOPEN_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));


		// Check for getimagesize
		if (@function_exists('getimagesize'))
		{
			$passed['imagesize'] = true;
			$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_GETIMAGESIZE_SUPPORT'],
			'TITLE_EXPLAIN'	=> $lang['PHP_GETIMAGESIZE_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Check for PCRE UTF-8 and "(?|(a)|(b))" construction support
		$pattern = '#(?|([\pL])|([\d]))#u';
		$match1 = $match2 = array();
		if (@preg_match('//u', '')
			&& @preg_match($pattern, 'Ъ', $match1) && isset($match1[1]) && $match1[1] === 'Ъ'
			&& @preg_match($pattern, '1', $match2) && isset($match2[1]) && $match2[1] === '1')
		{
			$passed['pcre'] = true;
			$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}

		$template->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PCRE_UTF_SUPPORT'],
			'TITLE_EXPLAIN'	=> $lang['PCRE_UTF_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		$passed['mbstring'] = true;
		if (@extension_loaded('mbstring'))
		{
			// Test for available database modules
			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $lang['MBSTRING_CHECK'],
				'LEGEND_EXPLAIN'	=> $lang['MBSTRING_CHECK_EXPLAIN'],
			));

			$checks = array(
				array('func_overload', '&', (defined('MB_OVERLOAD_MAIL') && defined('MB_OVERLOAD_STRING') ? MB_OVERLOAD_MAIL|MB_OVERLOAD_STRING : 0)),
				array('encoding_translation', '!=', 0),
				array('http_input', '!=', array('pass', '')),
				array('http_output', '!=', array('pass', ''))
			);

			foreach ($checks as $mb_checks)
			{
				$ini_val = @ini_get('mbstring.' . $mb_checks[0]);
				switch ($mb_checks[1])
				{
					case '&':
						if (intval($ini_val) & $mb_checks[2])
						{
							$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
							$passed['mbstring'] = false;
						}
						else
						{
							$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
						}
					break;

					case '!=':
						if (!is_array($mb_checks[2]) && $ini_val != $mb_checks[2] ||
							is_array($mb_checks[2]) && !in_array($ini_val, $mb_checks[2]))
						{
							$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
							$passed['mbstring'] = false;
						}
						else
						{
							$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
						}
					break;
				}
				$template->assign_block_vars('checks', array(
					'TITLE'			=> $lang['MBSTRING_' . strtoupper($mb_checks[0])],
					'TITLE_EXPLAIN'	=> $lang['MBSTRING_' . strtoupper($mb_checks[0]) . '_EXPLAIN'],
					'RESULT'		=> $result,

					'S_EXPLAIN'		=> true,
					'S_LEGEND'		=> false,
				));
			}
		}

		// Test for available database modules
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['PHP_SUPPORTED_DB'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_SUPPORTED_DB_EXPLAIN'],
		));

		$available_dbms = get_available_dbms(false, true);
		$passed['db'] = $available_dbms['ANY_DB_SUPPORT'];
		unset($available_dbms['ANY_DB_SUPPORT']);

		foreach ($available_dbms as $db_name => $db_ary)
		{
			if (!$db_ary['AVAILABLE'])
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DLL_' . strtoupper($db_name)],
					'RESULT'	=> '<span style="color:red">' . $lang['UNAVAILABLE'] . '</span>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DLL_' . strtoupper($db_name)],
					'RESULT'	=> '<strong style="color:green">' . $lang['AVAILABLE'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
		}

		// Test for other modules
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['PHP_OPTIONAL_MODULE'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_OPTIONAL_MODULE_EXPLAIN'],
		));

		foreach ($this->php_dlls_other as $dll)
		{
			if (!@extension_loaded($dll))
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DLL_' . strtoupper($dll)],
					'RESULT'	=> '<strong style="color:red">' . $lang['UNAVAILABLE'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
				continue;
			}

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $lang['DLL_' . strtoupper($dll)],
				'RESULT'	=> '<strong style="color:green">' . $lang['AVAILABLE'] . '</strong>',

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// Check permissions on files/directories we need access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['FILES_REQUIRED'],
			'LEGEND_EXPLAIN'	=> $lang['FILES_REQUIRED_EXPLAIN'],
		));

		$directories = array('cache/', 'files/', 'store/');

		umask(0);

		$passed['files'] = true;
		foreach ($directories as $dir)
		{
			$exists = $write = false;

			// Try to create the directory if it does not exist
			if (!file_exists($phpbb_root_path . $dir))
			{
				@mkdir($phpbb_root_path . $dir, 0777);
				phpbb_chmod($phpbb_root_path . $dir, CHMOD_READ | CHMOD_WRITE);
			}

			// Now really check
			if (file_exists($phpbb_root_path . $dir) && is_dir($phpbb_root_path . $dir))
			{
				phpbb_chmod($phpbb_root_path . $dir, CHMOD_READ | CHMOD_WRITE);
				$exists = true;
			}

			// Now check if it is writable by storing a simple file
			$fp = @fopen($phpbb_root_path . $dir . 'test_lock', 'wb');
			if ($fp !== false)
			{
				$write = true;
			}
			@fclose($fp);

			@unlink($phpbb_root_path . $dir . 'test_lock');

			$passed['files'] = ($exists && $write && $passed['files']) ? true : false;

			$exists = ($exists) ? '<strong style="color:green">' . $lang['FOUND'] . '</strong>' : '<strong style="color:red">' . $lang['NOT_FOUND'] . '</strong>';
			$write = ($write) ? ', <strong style="color:green">' . $lang['WRITABLE'] . '</strong>' : (($exists) ? ', <strong style="color:red">' . $lang['UNWRITABLE'] . '</strong>' : '');

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $exists . $write,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// Check permissions on files/directories it would be useful access to
		$template->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['FILES_OPTIONAL'],
			'LEGEND_EXPLAIN'	=> $lang['FILES_OPTIONAL_EXPLAIN'],
		));

		$directories = array('config.php', 'images/avatars/upload/');

		foreach ($directories as $dir)
		{
			$write = $exists = true;
			if (file_exists($phpbb_root_path . $dir))
			{
				if (!phpbb_is_writable($phpbb_root_path . $dir))
				{
					$write = false;
				}
			}
			else
			{
				$write = $exists = false;
			}

			$exists_str = ($exists) ? '<strong style="color:green">' . $lang['FOUND'] . '</strong>' : '<strong style="color:red">' . $lang['NOT_FOUND'] . '</strong>';
			$write_str = ($write) ? ', <strong style="color:green">' . $lang['WRITABLE'] . '</strong>' : (($exists) ? ', <strong style="color:red">' . $lang['UNWRITABLE'] . '</strong>' : '');

			$template->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $exists_str . $write_str,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = '';
		$url = (!in_array(false, $passed)) ? $this->p_master->module_url . "?mode=$mode&amp;sub=database&amp;language=$language" : $this->p_master->module_url . "?mode=$mode&amp;sub=requirements&amp;language=$language	";
		$submit = (!in_array(false, $passed)) ? $lang['INSTALL_START'] : $lang['INSTALL_TEST'];

		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Obtain the information required to connect to the database
	*/
	function obtain_database_settings($mode, $sub)
	{
		global $lang, $template;

		$this->page_title = $lang['STAGE_DATABASE'];

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		$connect_test = false;
		$error = array();
		$available_dbms = get_available_dbms(false, true);

		// Has the user opted to test the connection?
		if (isset($_POST['testdb']))
		{
			if (!isset($available_dbms[$data['dbms']]) || !$available_dbms[$data['dbms']]['AVAILABLE'])
			{
				$error[] = $lang['INST_ERR_NO_DB'];
				$connect_test = false;
			}
			else if (!preg_match(get_preg_expression('table_prefix'), $data['table_prefix']))
			{
				$error[] = $lang['INST_ERR_DB_INVALID_PREFIX'];
				$connect_test = false;
			}
			else
			{
				$connect_test = connect_check_db(true, $error, $available_dbms[$data['dbms']], $data['table_prefix'], $data['dbhost'], $data['dbuser'], htmlspecialchars_decode($data['dbpasswd']), $data['dbname'], $data['dbport']);
			}

			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $lang['DB_CONNECTION'],
				'LEGEND_EXPLAIN'	=> false,
			));

			if ($connect_test)
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DB_TEST'],
					'RESULT'	=> '<strong style="color:green">' . $lang['SUCCESSFUL_CONNECT'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DB_TEST'],
					'RESULT'	=> '<strong style="color:red">' . implode('<br />', $error) . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
		}

		if (!$connect_test)
		{
			// Update the list of available DBMS modules to only contain those which can be used
			$available_dbms_temp = array();

			unset($available_dbms['ANY_DB_SUPPORT']);
			foreach ($available_dbms as $type => $dbms_ary)
			{
				if (!$dbms_ary['AVAILABLE'])
				{
					continue;
				}

				$available_dbms_temp[$type] = $dbms_ary;
			}

			$available_dbms = &$available_dbms_temp;

			// And now for the main part of this page
			$data['table_prefix'] = (!empty($data['table_prefix']) ? $data['table_prefix'] : 'phpbb_');

			foreach ($this->db_config_options as $config_key => $vars)
			{
				if (!is_array($vars) && strpos($config_key, 'legend') === false)
				{
					continue;
				}

				if (strpos($config_key, 'legend') !== false)
				{
					$template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> $lang[$vars])
					);

					continue;
				}

				$options = isset($vars['options']) ? $vars['options'] : '';

				$template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> $lang[$vars['lang']],
					'S_EXPLAIN'		=> $vars['explain'],
					'S_LEGEND'		=> false,
					'TITLE_EXPLAIN'	=> ($vars['explain']) ? $lang[$vars['lang'] . '_EXPLAIN'] : '',
					'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
					)
				);
			}
		}

		// And finally where do we want to go next (well today is taken isn't it :P)
		$s_hidden_fields = '<input type="hidden" name="language" value="' . $data['language'] . '" />';
		if ($connect_test)
		{
			foreach ($this->db_config_options as $config_key => $vars)
			{
				if (!is_array($vars))
				{
					continue;
				}
				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
			}
		}

		$url = ($connect_test) ? $this->p_master->module_url . "?mode=$mode&amp;sub=administrator" : $this->p_master->module_url . "?mode=$mode&amp;sub=database";
		$s_hidden_fields .= ($connect_test) ? '' : '<input type="hidden" name="testdb" value="true" />';

		$submit = $lang['NEXT_STEP'];

		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Obtain the administrator's name, password and email address
	*/
	function obtain_admin_settings($mode, $sub)
	{
		global $lang, $template;

		$this->page_title = $lang['STAGE_ADMINISTRATOR'];

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index.php?mode=install");
		}

		$s_hidden_fields = '';
		$passed = false;

		$data['default_lang'] = ($data['default_lang'] !== '') ? $data['default_lang'] : $data['language'];

		if (isset($_POST['check']))
		{
			$error = array();

			// Check the entered email address and password
			if ($data['admin_name'] == '' || $data['admin_pass1'] == '' || $data['admin_pass2'] == '' || $data['board_email1'] == '' || $data['board_email2'] == '')
			{
				$error[] = $lang['INST_ERR_MISSING_DATA'];
			}

			if ($data['admin_pass1'] != $data['admin_pass2'] && $data['admin_pass1'] != '')
			{
				$error[] = $lang['INST_ERR_PASSWORD_MISMATCH'];
			}

			// Test against the default username rules
			if ($data['admin_name'] != '' && utf8_strlen($data['admin_name']) < 3)
			{
				$error[] = $lang['INST_ERR_USER_TOO_SHORT'];
			}

			if ($data['admin_name'] != '' && utf8_strlen($data['admin_name']) > 20)
			{
				$error[] = $lang['INST_ERR_USER_TOO_LONG'];
			}

			// Test against the default password rules
			if ($data['admin_pass1'] != '' && utf8_strlen($data['admin_pass1']) < 6)
			{
				$error[] = $lang['INST_ERR_PASSWORD_TOO_SHORT'];
			}

			if ($data['admin_pass1'] != '' && utf8_strlen($data['admin_pass1']) > 30)
			{
				$error[] = $lang['INST_ERR_PASSWORD_TOO_LONG'];
			}

			if ($data['board_email1'] != $data['board_email2'] && $data['board_email1'] != '')
			{
				$error[] = $lang['INST_ERR_EMAIL_MISMATCH'];
			}

			if ($data['board_email1'] != '' && !preg_match('/^' . get_preg_expression('email') . '$/i', $data['board_email1']))
			{
				$error[] = $lang['INST_ERR_EMAIL_INVALID'];
			}

			$template->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $lang['STAGE_ADMINISTRATOR'],
				'LEGEND_EXPLAIN'	=> false,
			));

			if (!sizeof($error))
			{
				$passed = true;
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['ADMIN_TEST'],
					'RESULT'	=> '<strong style="color:green">' . $lang['TESTS_PASSED'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$template->assign_block_vars('checks', array(
					'TITLE'		=> $lang['ADMIN_TEST'],
					'RESULT'	=> '<strong style="color:red">' . implode('<br />', $error) . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
		}

		if (!$passed)
		{
			foreach ($this->admin_config_options as $config_key => $vars)
			{
				if (!is_array($vars) && strpos($config_key, 'legend') === false)
				{
					continue;
				}

				if (strpos($config_key, 'legend') !== false)
				{
					$template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> $lang[$vars])
					);

					continue;
				}

				$options = isset($vars['options']) ? $vars['options'] : '';

				$template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> $lang[$vars['lang']],
					'S_EXPLAIN'		=> $vars['explain'],
					'S_LEGEND'		=> false,
					'TITLE_EXPLAIN'	=> ($vars['explain']) ? $lang[$vars['lang'] . '_EXPLAIN'] : '',
					'CONTENT'		=> $this->p_master->input_field($config_key, $vars['type'], $data[$config_key], $options),
					)
				);
			}
		}
		else
		{
			foreach ($this->admin_config_options as $config_key => $vars)
			{
				if (!is_array($vars))
				{
					continue;
				}
				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
			}
		}

		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $data['language'] . '" />';

		foreach ($this->db_config_options as $config_key => $vars)
		{
			if (!is_array($vars))
			{
				continue;
			}
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
		}

		$submit = $lang['NEXT_STEP'];

		$url = ($passed) ? $this->p_master->module_url . "?mode=$mode&amp;sub=create_table" : $this->p_master->module_url . "?mode=$mode&amp;sub=administrator";
		$s_hidden_fields .= ($passed) ? '' : '<input type="hidden" name="check" value="true" />';

		$template->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $url,
		));
	}

	/**
	* Prepare database connection.
	*/
	function db_connect()
	{
		global $phpbb_root_path, $lang, $db, $table_prefix;

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		if ($data['dbms'] != 'mysql')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			$this->p_master->redirect("index.php?mode=install");
		}

		// If we get here and the extension isn't loaded it should be safe to just go ahead and load it
		$available_dbms = get_available_dbms($data['dbms']);
		if (!isset($available_dbms[$data['dbms']]))
		{
			// Someone's been silly and tried providing a non-existant dbms
			$this->p_master->redirect("index.php?mode=install");
		}

		// Load the appropriate database class if not already loaded
		require_once($phpbb_root_path . 'includes/db/mysql.php');

		// Instantiate the database
		$db = new dbal_mysql();
		$db->sql_connect($data['dbhost'], $data['dbuser'], htmlspecialchars_decode($data['dbpasswd']), $data['dbname'], $data['dbport'], false, false);

		// NOTE: trigger_error does not work here.
		$db->sql_return_on_error(true);

		$table_prefix = $data['table_prefix'];
		require_once($phpbb_root_path . 'includes/constants.php');
	}

	/**
	* Fetch current config.
	*/
	function fetch_config()
	{
		global $db, $config;

		$sql = 'SELECT * FROM ' . CONFIG_TABLE;
		$result = $db->sql_query($sql);

		$config = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$config[$row['config_name']] = $row['config_value'];
		}

		$db->sql_freeresult($result);
	}

	/**
	* Load the contents of the schema into the database and then alter it based on what has been input during the installation
	*/
	function load_schema($mode, $sub)
	{
		global $phpbb_root_path, $lang, $db;

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		// Ok we have the db info go ahead and read in the relevant schema
		// and work on building the table
		$sql_query = file_get_contents('schemas/mysql_schema.sql');
		$sql_query = preg_replace('#phpbb_#i', $data['table_prefix'], $sql_query);
		$sql_query = phpbb_remove_comments($sql_query);
		$sql_query = split_sql_file($sql_query, ';');

		foreach ($sql_query as $sql)
		{
			//$sql = trim(str_replace('|', ';', $sql));
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}
		unset($sql_query);

		// Ok tables have been built, let's fill in the basic information
		$sql_query = file_get_contents('schemas/schema_data.sql');

		// Change prefix
		$sql_query = preg_replace('# phpbb_([^\s]*) #i', ' ' . $data['table_prefix'] . '\1 ', $sql_query);

		// Change language strings...
		$sql_query = preg_replace_callback('#\{L_([A-Z0-9\-_]*)\}#s', 'adjust_language_keys_callback', $sql_query);

		$sql_query = phpbb_remove_comments($sql_query);
		$sql_query = split_sql_file($sql_query, ';');

		foreach ($sql_query as $sql)
		{
			//$sql = trim(str_replace('|', ';', $sql));
			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}
		unset($sql_query);

		$current_time = time();

		$user_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';
		$user_ip = (stripos($user_ip, '::ffff:') === 0) ? substr($user_ip, 7) : $user_ip;

		// Set default config and post data, this applies to all DB's
		$sql_ary = array(
			'INSERT INTO ' . $data['table_prefix'] . "config (config_name, config_value)
				VALUES ('board_startdate', '$current_time')",

			'INSERT INTO ' . $data['table_prefix'] . "config (config_name, config_value)
				VALUES ('default_lang', '" . $db->sql_escape($data['default_lang']) . "')",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['board_email1']) . "'
				WHERE config_name = 'board_email'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['board_email1']) . "'
				WHERE config_name = 'board_contact'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($lang['default_dateformat']) . "'
				WHERE config_name = 'default_dateformat'",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($data['admin_name']) . "'
				WHERE config_name = 'newest_username'",

			'UPDATE ' . $data['table_prefix'] . "users
				SET username = '" . $db->sql_escape($data['admin_name']) . "', user_password='" . $db->sql_escape(md5($data['admin_pass1'])) . "', user_ip = '" . $db->sql_escape($user_ip) . "', user_lang = '" . $db->sql_escape($data['default_lang']) . "', user_email='" . $db->sql_escape($data['board_email1']) . "', user_dateformat='" . $db->sql_escape($lang['default_dateformat']) . "', user_email_hash = " . $db->sql_escape(phpbb_email_hash($data['board_email1'])) . ", username_clean = '" . $db->sql_escape(utf8_clean_string($data['admin_name'])) . "'
				WHERE username = 'Admin'",

			'UPDATE ' . $data['table_prefix'] . "moderator_cache
				SET username = '" . $db->sql_escape($data['admin_name']) . "'
				WHERE username = 'Admin'",

			'UPDATE ' . $data['table_prefix'] . "forums
				SET forum_last_poster_name = '" . $db->sql_escape($data['admin_name']) . "'
				WHERE forum_last_poster_name = 'Admin'",

			'UPDATE ' . $data['table_prefix'] . "topics
				SET topic_first_poster_name = '" . $db->sql_escape($data['admin_name']) . "', topic_last_poster_name = '" . $db->sql_escape($data['admin_name']) . "'
				WHERE topic_first_poster_name = 'Admin'
					OR topic_last_poster_name = 'Admin'",

			'UPDATE ' . $data['table_prefix'] . "users
				SET user_regdate = $current_time",

			'UPDATE ' . $data['table_prefix'] . "posts
				SET post_time = $current_time, poster_ip = '" . $db->sql_escape($user_ip) . "'",

			'UPDATE ' . $data['table_prefix'] . "topics
				SET topic_time = $current_time, topic_last_post_time = $current_time",

			'UPDATE ' . $data['table_prefix'] . "forums
				SET forum_last_post_time = $current_time",

			'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '" . $db->sql_escape($db->sql_server_info(true)) . "'
				WHERE config_name = 'dbms_version'",
		);

		if (@extension_loaded('gd'))
		{
			$sql_ary[] = 'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = 'phpbb_captcha_gd'
				WHERE config_name = 'captcha_plugin'";

			$sql_ary[] = 'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '1'
				WHERE config_name = 'captcha_gd'";
		}

		// Disable avatars if avatar directory isn't writable.
		if (!phpbb_is_writable($phpbb_root_path . 'images/avatars/upload/'))
		{
			$sql_ary[] = 'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '0'
				WHERE config_name = 'allow_avatar'";

			$sql_ary[] = 'UPDATE ' . $data['table_prefix'] . "config
				SET config_value = '0'
				WHERE config_name = 'allow_avatar_upload'";
		}

		foreach ($sql_ary as $sql)
		{
			//$sql = trim(str_replace('|', ';', $sql));

			if (!$db->sql_query($sql))
			{
				$error = $db->sql_error();
				$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
			}
		}
	}

	/**
	* Build the search index...
	*/
	function build_search_index($mode, $sub)
	{
		global $phpbb_root_path, $db, $config, $lang;

		require_once($phpbb_root_path . 'includes/search/fulltext_native.php');

		$error = false;
		$search = new fulltext_native($error);

		$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
			FROM ' . POSTS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$search->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
		}
		$db->sql_freeresult($result);
	}

	/**
	* Populate the module tables
	*/
	function add_modules($mode, $sub)
	{
		global $db, $lang, $phpbb_root_path;

		include_once($phpbb_root_path . 'includes/acp/acp_modules.php');

		$_module = new acp_modules();
		$module_classes = array('acp', 'mcp', 'ucp');

		// Add categories
		foreach ($module_classes as $module_class)
		{
			$categories = array();

			// Set the module class
			$_module->module_class = $module_class;

			foreach ($this->module_categories[$module_class] as $cat_name => $subs)
			{
				$module_data = array(
					'module_basename'	=> '',
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'parent_id'			=> 0,
					'module_class'		=> $module_class,
					'module_langname'	=> $cat_name,
					'module_mode'		=> '',
					'module_auth'		=> '',
				);

				// Add category
				$_module->update_module_data($module_data, true);

				// Check for last sql error happened
				if ($db->sql_error_triggered)
				{
					$error = $db->sql_error($db->sql_error_sql);
					$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
				}

				$categories[$cat_name]['id'] = (int) $module_data['module_id'];
				$categories[$cat_name]['parent_id'] = 0;

				// Create sub-categories...
				if (is_array($subs))
				{
					foreach ($subs as $level2_name)
					{
						$module_data = array(
							'module_basename'	=> '',
							'module_enabled'	=> 1,
							'module_display'	=> 1,
							'parent_id'			=> (int) $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $level2_name,
							'module_mode'		=> '',
							'module_auth'		=> '',
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->sql_error_triggered)
						{
							$error = $db->sql_error($db->sql_error_sql);
							$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
						}

						$categories[$level2_name]['id'] = (int) $module_data['module_id'];
						$categories[$level2_name]['parent_id'] = (int) $categories[$cat_name]['id'];
					}
				}
			}

			// Get the modules we want to add... returned sorted by name
			$module_info = $_module->get_module_infos('', $module_class);

			foreach ($module_info as $module_basename => $fileinfo)
			{
				foreach ($fileinfo['modes'] as $module_mode => $row)
				{
					foreach ($row['cat'] as $cat_name)
					{
						if (!isset($categories[$cat_name]))
						{
							continue;
						}

						$module_data = array(
							'module_basename'	=> $module_basename,
							'module_enabled'	=> 1,
							'module_display'	=> (isset($row['display'])) ? (int) $row['display'] : 1,
							'parent_id'			=> (int) $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $row['title'],
							'module_mode'		=> $module_mode,
							'module_auth'		=> $row['auth'],
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->sql_error_triggered)
						{
							$error = $db->sql_error($db->sql_error_sql);
							$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
						}
					}
				}
			}

			// Move some of the modules around since the code above will put them in the wrong place
			if ($module_class == 'acp')
			{
				// Move main module 4 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'main'
						AND module_class = 'acp'
						AND module_mode = 'main'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 4);

				// Move permissions intro screen module 4 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'permissions'
						AND module_class = 'acp'
						AND module_mode = 'intro'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 4);

				// Move manage users screen module 5 up...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'users'
						AND module_class = 'acp'
						AND module_mode = 'overview'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_up', 5);

				// Move attachments settings module 3 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'attachments'
						AND module_class = 'acp'
						AND module_mode = 'attach'
					ORDER BY module_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 3);
			}

			if ($module_class == 'mcp')
			{
				// Move pm report details module 3 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'pm_reports'
						AND module_class = 'mcp'
						AND module_mode = 'pm_report_details'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 3);

				// Move closed pm reports module 3 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'pm_reports'
						AND module_class = 'mcp'
						AND module_mode = 'pm_reports_closed'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 3);

				// Move open pm reports module 3 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'pm_reports'
						AND module_class = 'mcp'
						AND module_mode = 'pm_reports'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 3);
			}

			if ($module_class == 'ucp')
			{
				// Move attachment module 4 down...
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_basename = 'attachments'
						AND module_class = 'ucp'
						AND module_mode = 'attachments'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_module->move_module_by($row, 'move_down', 4);
			}

			// And now for the special ones
			// (these are modules which appear in multiple categories and thus get added manually to some for more control)
			if (isset($this->module_extras[$module_class]))
			{
				foreach ($this->module_extras[$module_class] as $cat_name => $mods)
				{
					$sql = 'SELECT module_id, left_id, right_id
						FROM ' . MODULES_TABLE . "
						WHERE module_langname = '" . $db->sql_escape($cat_name) . "'
							AND module_class = '" . $db->sql_escape($module_class) . "'";
					$result = $db->sql_query_limit($sql, 1);
					$row2 = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					foreach ($mods as $mod_name)
					{
						$sql = 'SELECT *
							FROM ' . MODULES_TABLE . "
							WHERE module_langname = '" . $db->sql_escape($mod_name) . "'
								AND module_class = '" . $db->sql_escape($module_class) . "'
								AND module_basename <> ''";
						$result = $db->sql_query_limit($sql, 1);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						$module_data = array(
							'module_basename'	=> $row['module_basename'],
							'module_enabled'	=> (int) $row['module_enabled'],
							'module_display'	=> (int) $row['module_display'],
							'parent_id'			=> (int) $row2['module_id'],
							'module_class'		=> $row['module_class'],
							'module_langname'	=> $row['module_langname'],
							'module_mode'		=> $row['module_mode'],
							'module_auth'		=> $row['module_auth'],
						);

						$_module->update_module_data($module_data, true);

						// Check for last sql error happened
						if ($db->sql_error_triggered)
						{
							$error = $db->sql_error($db->sql_error_sql);
							$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
						}
					}
				}
			}

			$_module->remove_cache_file();
		}
	}

	/**
	* Populate the language tables
	*/
	function add_language($mode, $sub)
	{
		global $db, $lang, $phpbb_root_path;

		$dir = @opendir($phpbb_root_path . 'language');

		if (!$dir)
		{
			$this->error('Unable to access the language directory', __LINE__, __FILE__);
		}

		while (($file = readdir($dir)) !== false)
		{
			$path = $phpbb_root_path . 'language/' . $file;

			if ($file == '.' || $file == '..' || is_link($path) || is_file($path) || $file == 'CVS')
			{
				continue;
			}

			if (is_dir($path) && file_exists($path . '/iso.txt'))
			{
				$lang_file = file("$path/iso.txt");

				$lang_pack = array(
					'lang_iso'			=> basename($path),
					'lang_dir'			=> basename($path),
					'lang_english_name'	=> trim(htmlspecialchars($lang_file[0])),
					'lang_local_name'	=> trim(htmlspecialchars($lang_file[1], ENT_COMPAT, 'UTF-8')),
					'lang_author'		=> trim(htmlspecialchars($lang_file[2], ENT_COMPAT, 'UTF-8')),
				);

				$db->sql_query('INSERT INTO ' . LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $lang_pack));

				if ($db->sql_error_triggered)
				{
					$error = $db->sql_error($db->sql_error_sql);
					$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
				}

				$valid_localized = array(
					'icon_back_top', 'icon_contact_email', 'icon_contact_icq', 'icon_contact_jabber', 'icon_contact_skype', 'icon_contact_telegram', 'icon_contact_pm', 'icon_contact_www', 'icon_post_delete', 'icon_post_edit', 'icon_post_info', 'icon_post_quote', 'icon_post_report', 'icon_user_online', 'icon_user_offline', 'icon_user_profile', 'icon_user_search', 'icon_user_warn', 'button_pm_forward', 'button_pm_new', 'button_pm_reply', 'button_topic_locked', 'button_topic_new', 'button_topic_reply',
				);

				$sql_ary = array();

				$sql = 'SELECT *
					FROM ' . STYLES_IMAGESET_TABLE;
				$result = $db->sql_query($sql);

				while ($imageset_row = $db->sql_fetchrow($result))
				{
					if (@file_exists("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$lang_pack['lang_iso']}/imageset.cfg"))
					{
						$cfg_data_imageset_data = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$lang_pack['lang_iso']}/imageset.cfg");
						foreach ($cfg_data_imageset_data as $image_name => $value)
						{
							if (strpos($value, '*') !== false)
							{
								if (substr($value, -1, 1) === '*')
								{
									list($image_filename, $image_height) = explode('*', $value);
									$image_width = 0;
								}
								else
								{
									list($image_filename, $image_height, $image_width) = explode('*', $value);
								}
							}
							else
							{
								$image_filename = $value;
								$image_height = $image_width = 0;
							}

							if (strpos($image_name, 'img_') === 0 && $image_filename)
							{
								$image_name = substr($image_name, 4);
								if (in_array($image_name, $valid_localized))
								{
									$sql_ary[] = array(
										'image_name'		=> (string) $image_name,
										'image_filename'	=> (string) $image_filename,
										'image_height'		=> (int) $image_height,
										'image_width'		=> (int) $image_width,
										'imageset_id'		=> (int) $imageset_row['imageset_id'],
										'image_lang'		=> (string) $lang_pack['lang_iso'],
									);
								}
							}
						}
					}
				}
				$db->sql_freeresult($result);

				if (sizeof($sql_ary))
				{
					$db->sql_multi_insert(STYLES_IMAGESET_DATA_TABLE, $sql_ary);

					if ($db->sql_error_triggered)
					{
						$error = $db->sql_error($db->sql_error_sql);
						$this->p_master->db_error($error['message'], $db->sql_error_sql, __LINE__, __FILE__);
					}
				}
			}
		}
		closedir($dir);
	}

	/**
	* Add search robots to the database
	*/
	function add_bots($mode, $sub)
	{
		global $db, $lang, $phpbb_root_path, $config;

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = 'BOTS'";
		$result = $db->sql_query($sql);
		$group_id = (int) $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);

		if (!$group_id)
		{
			// If we reach this point then something has gone very wrong
			$this->p_master->error($lang['NO_GROUP'], __LINE__, __FILE__);
		}

		if (!function_exists('user_add'))
		{
			include($phpbb_root_path . 'includes/functions_user.php');
		}

		foreach ($this->bot_list as $bot_name => $bot_agent)
		{
			if (empty($bot_agent)) { continue; }

			$user_row = array(
				'user_type'				=> USER_IGNORE,
				'group_id'				=> $group_id,
				'username'				=> $bot_name,
				'user_regdate'			=> time(),
				'user_password'			=> '',
				'user_colour'			=> '9E8DA7',
				'user_email'			=> '',
				'user_lang'				=> $data['default_lang'],
				'user_style'			=> 1,
				'user_timezone'			=> 0,
				'user_dateformat'		=> $lang['default_dateformat'],
				'user_allow_massemail'	=> 0,
				'user_allow_pm'			=> 0,
			);

			$user_id = user_add($user_row);

			if (!$user_id)
			{
				// If we can't insert this user then continue to the next one to avoid inconsistent data
				$this->p_master->db_error('Unable to insert bot into users table', $db->sql_error_sql, __LINE__, __FILE__, true);
				continue;
			}

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'bot_active'	=> 1,
				'bot_name'		=> (string) $bot_name,
				'user_id'		=> (int) $user_id,
				'bot_agent'		=> (string) $bot_agent,
				'bot_ip'		=> '',
			));

			$result = $db->sql_query($sql);
		}
	}

	/**
	* Writes the config file to disk, or if unable to do so offers alternative methods.
	* On success, sends the final email to the board administrator.
	*/
	function create_config_file($mode, $sub)
	{
		global $auth, $config, $db, $lang, $template, $user, $phpbb_root_path;

		$data = $this->get_submitted_data();
		$config_data = phpbb_create_config_file_data($data);

		if (isset($_POST['dlconfig']))
		{
			// They want a copy of the file to download, so send the relevant headers and dump out the data
			header("Content-Type: text/x-delimtext; name=\"config.php\"");
			header("Content-disposition: attachment; filename=config.php");
			echo $config_data;
			exit;
		}

		$config_path = $phpbb_root_path . 'config.php';
		$config_done = (file_exists($config_path) && strpos(file_get_contents($config_path), 'PHPBB_INSTALLED') !== false);

		if (!$config_done)
		{
			// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
			if ((file_exists($config_path) && phpbb_is_writable($config_path)) || phpbb_is_writable($phpbb_root_path))
			{
				$config_done = @file_put_contents($config_path, $config_data);
				if ($config_done) { phpbb_chmod($config_path, CHMOD_READ); }
			}
			$config_done = (file_exists($config_path) && strpos(file_get_contents($config_path), 'PHPBB_INSTALLED') !== false);
		}

		if (!$config_done)
		{
			// OK, so it didn't work, let's tell the user to download and copy config.php manually.

			$this->page_title = $lang['STAGE_CONFIG_FILE'];

			$s_hidden_fields = '<input type="hidden" name="language" value="' . $data['language'] . '" />';
			$config_options = array_merge($this->db_config_options, $this->admin_config_options);
			foreach ($config_options as $config_key => $vars)
			{
				if (!is_array($vars)) { continue; }
				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
			}

			$template->assign_vars(array(
				'TITLE'					=> $lang['STAGE_CONFIG_FILE'],
				'BODY'					=> $lang['CONFIG_FILE_UNABLE_WRITE'],
				'L_DL_CONFIG'			=> $lang['DL_CONFIG'],
				'L_DL_CONFIG_EXPLAIN'	=> $lang['DL_CONFIG_EXPLAIN'],
				'L_DL_DONE'				=> $lang['DONE'],
				'L_DL_DOWNLOAD'			=> $lang['DL_DOWNLOAD'],
				'S_HIDDEN'				=> $s_hidden_fields,
				'S_SHOW_DOWNLOAD'		=> true,
				'U_ACTION'				=> $this->p_master->module_url . "?mode=$mode&amp;sub=$sub",
			));

			// Create a lock file to indicate that there is an install in progress.
			// Otherwise we won't be able to show the final message after config.php is copied manually.
			if (@touch($phpbb_root_path . 'cache/install_lock'))
			{
				@chmod($phpbb_root_path . 'cache/install_lock', 0666);
			}
		}
		else
		{
			// OK, now that we've reached this point we can be confident that everything is installed and working...
			// So it's time to send an email to the administrator confirming the details they entered.

			$this->page_title = $lang['STAGE_FINAL'];

			$user->session_begin();
			$auth->login($data['admin_name'], $data['admin_pass1'], false, true, true);

			if ($config['email_enable'])
			{
				require_once($phpbb_root_path . 'includes/functions_messenger.php');
				$messenger = new messenger(false);
				$messenger->template('installed', $data['language']);
				$messenger->to($data['board_email1'], $data['admin_name']);
				$messenger->anti_abuse_headers($config, $user);
				$messenger->assign_vars(array(
					'USERNAME'		=> htmlspecialchars_decode($data['admin_name']),
					'PASSWORD'		=> htmlspecialchars_decode($data['admin_pass1']))
				);
				$messenger->send(NOTIFY_EMAIL);
			}

			// And finally, add a note to the log.
			add_log('admin', 'LOG_INSTALL_INSTALLED', $config['phpbbex_version']);

			$template->assign_vars(array(
				'TITLE'		=> $lang['INSTALL_CONGRATS'],
				'BODY'		=> sprintf($lang['INSTALL_CONGRATS_EXPLAIN'], $config['phpbbex_version']),
				'L_SUBMIT'	=> $lang['INSTALL_LOGIN'],
				'U_ACTION'	=> append_sid($phpbb_root_path . 'adm/index.php', false, true, true),
			));

			// Remove the lock file.
			if (file_exists($phpbb_root_path . 'cache/install_lock'))
			{
				@unlink($phpbb_root_path . 'cache/install_lock');
			}
		}
	}

	/**
	* Get submitted data
	*/
	function get_submitted_data()
	{
		return array(
			'language'		=> basename(request_var('language', '')),
			'dbms'			=> request_var('dbms', ''),
			'dbhost'		=> request_var('dbhost', ''),
			'dbport'		=> request_var('dbport', ''),
			'dbuser'		=> request_var('dbuser', ''),
			'dbpasswd'		=> request_var('dbpasswd', '', true),
			'dbname'		=> request_var('dbname', ''),
			'table_prefix'	=> request_var('table_prefix', ''),
			'default_lang'	=> basename(request_var('default_lang', '')),
			'admin_name'	=> utf8_normalize_nfc(request_var('admin_name', '', true)),
			'admin_pass1'	=> request_var('admin_pass1', '', true),
			'admin_pass2'	=> request_var('admin_pass2', '', true),
			'board_email1'	=> strtolower(request_var('board_email1', '')),
			'board_email2'	=> strtolower(request_var('board_email2', '')),
		);
	}

	/**
	* The information below will be used to build the input fields presented to the user
	*/
	var $db_config_options = array(
		'legend1'				=> 'DB_CONFIG',
		'dbms'					=> array('lang' => 'DBMS',			'type' => 'select', 'options' => 'dbms_select(\'{VALUE}\')', 'explain' => false),
		'dbhost'				=> array('lang' => 'DB_HOST',		'type' => 'text:25:100', 'explain' => true),
		'dbport'				=> array('lang' => 'DB_PORT',		'type' => 'text:25:100', 'explain' => true),
		'dbname'				=> array('lang' => 'DB_NAME',		'type' => 'text:25:100', 'explain' => false),
		'dbuser'				=> array('lang' => 'DB_USERNAME',	'type' => 'text:25:100', 'explain' => false),
		'dbpasswd'				=> array('lang' => 'DB_PASSWORD',	'type' => 'text:25:100', 'explain' => false),
		'table_prefix'			=> array('lang' => 'TABLE_PREFIX',	'type' => 'text:25:100', 'explain' => true),
	);
	var $admin_config_options = array(
		'legend1'				=> 'ADMIN_CONFIG',
		'default_lang'			=> array('lang' => 'DEFAULT_LANG',				'type' => 'select', 'options' => '$this->module->inst_language_select(\'{VALUE}\')', 'explain' => false),
		'admin_name'			=> array('lang' => 'ADMIN_USERNAME',			'type' => 'text:25:100', 'explain' => true),
		'admin_pass1'			=> array('lang' => 'ADMIN_PASSWORD',			'type' => 'password:25:100', 'explain' => true),
		'admin_pass2'			=> array('lang' => 'ADMIN_PASSWORD_CONFIRM',	'type' => 'password:25:100', 'explain' => false),
		'board_email1'			=> array('lang' => 'CONTACT_EMAIL',				'type' => 'text:25:100', 'explain' => false),
		'board_email2'			=> array('lang' => 'CONTACT_EMAIL_CONFIRM',		'type' => 'text:25:100', 'explain' => false),
	);

	/**
	* Specific PHP modules we may require for certain optional or extended features
	*/
	var $php_dlls_other = array('zlib', 'ftp', 'gd', 'xml');

	/**
	* A list of the web-crawlers/bots we recognise by default.
	*/
	var $bot_list = array(
		'Alexa [Bot]'				=> 'ia_archiver',
		'Ask Jeeves [Bot]'			=> 'Ask Jeeves',
		'Baidu [Spider]'			=> 'Baiduspider',
		'Bing [Bot]'				=> 'bingbot/',
		'Exabot [Bot]'				=> 'Exabot',
		'FAST WebCrawler [Crawler]'	=> 'FAST-WebCrawler/',
		'Gigabot [Bot]'				=> 'Gigabot/',
		'Google [Bot]'				=> 'Googlebot',
		'Google Ads [Bot]'			=> 'AdsBot-Google',
		'Google Adsense [Bot]'		=> 'Mediapartners-Google',
		'Majestic-12 [Bot]'			=> 'MJ12bot/',
		'Steeler [Crawler]'			=> 'http://www.tkl.iis.u-tokyo.ac.jp/~crawler/',
		'TurnitinBot [Bot]'			=> 'TurnitinBot/',
		'Voyager [Bot]'				=> 'voyager/',
		'W3C [Linkcheck]'			=> 'W3C-checklink/',
		'W3C [Validator]'			=> 'W3C_Validator',
		'YaCy [Bot]'				=> 'yacybot',
		'Yahoo [Bot]'				=> 'Yahoo! Slurp',
		'Ahrefs [Bot]'				=> 'AhrefsBot/',
		'Senti [Bot]'				=> 'SentiBot/',
		'Petal [Bot]'				=> 'PetalBot',
		'Barkrowler [Bot]'			=> 'Barkrowler/',
		'Ubermetrics [Bot]'			=> 'techinfo@ubermetrics-technologies.com',
		'Trendiction [Bot]'			=> 'trendiction.de/bot',
		'Seostar [Bot]'				=> 'https://seostar.co/robot/',
		'BLEX [Bot]'				=> 'BLEXBot/',
		'DuckDuck [Bot]'			=> 'duckduckgo.com',
		'Yandex [Bot]'				=> 'YandexBot/',
		'Yandex Images [Bot]'		=> 'YandexImages/',
		'Yandex Metrika [Bot]'		=> 'YandexMetrika/',
		'MailRu [Bot]'				=> 'Mail.Ru/',
		'Feedly [Bot]'				=> 'Feedly/',
		'Feedspot [Bot]'			=> 'Feedspot/',
	);

	/**
	* Define the module structure so that we can populate the database without
	* needing to hard-code module_id values
	*/
	var $module_categories = array(
		'acp'	=> array(
			'ACP_CAT_GENERAL'		=> array(
				'ACP_QUICK_ACCESS',
				'ACP_BOARD_CONFIGURATION',
				'ACP_CLIENT_COMMUNICATION',
				'ACP_SERVER_CONFIGURATION',
			),
			'ACP_CAT_FORUMS'		=> array(
				'ACP_MANAGE_FORUMS',
				'ACP_FORUM_BASED_PERMISSIONS',
			),
			'ACP_CAT_POSTING'		=> array(
				'ACP_MESSAGES',
				'ACP_ATTACHMENTS',
			),
			'ACP_CAT_USERGROUP'		=> array(
				'ACP_CAT_USERS',
				'ACP_GROUPS',
				'ACP_USER_SECURITY',
			),
			'ACP_CAT_PERMISSIONS'	=> array(
				'ACP_GLOBAL_PERMISSIONS',
				'ACP_FORUM_BASED_PERMISSIONS',
				'ACP_PERMISSION_ROLES',
				'ACP_PERMISSION_MASKS',
			),
			'ACP_CAT_STYLES'		=> array(
				'ACP_STYLE_MANAGEMENT',
				'ACP_STYLE_COMPONENTS',
			),
			'ACP_CAT_MAINTENANCE'	=> array(
				'ACP_FORUM_LOGS',
				'ACP_CAT_DATABASE',
			),
			'ACP_CAT_SYSTEM'		=> array(
				'ACP_AUTOMATION',
				'ACP_GENERAL_TASKS',
				'ACP_MODULE_MANAGEMENT',
			),
			'ACP_CAT_DOT_MODS'		=> null,
		),
		'mcp'	=> array(
			'MCP_MAIN'		=> null,
			'MCP_QUEUE'		=> null,
			'MCP_REPORTS'	=> null,
			'MCP_NOTES'		=> null,
			'MCP_WARN'		=> null,
			'MCP_LOGS'		=> null,
			'MCP_BAN'		=> null,
		),
		'ucp'	=> array(
			'UCP_MAIN'			=> null,
			'UCP_PROFILE'		=> null,
			'UCP_PREFS'			=> null,
			'UCP_PM'			=> null,
			'UCP_USERGROUPS'	=> null,
			'UCP_ZEBRA'			=> null,
		),
	);

	var $module_extras = array(
		'acp'	=> array(
			'ACP_QUICK_ACCESS' => array(
				'ACP_MANAGE_USERS',
				'ACP_GROUPS_MANAGE',
				'ACP_MANAGE_FORUMS',
				'ACP_MOD_LOGS',
				'ACP_BOTS',
				'ACP_PHP_INFO',
			),
			'ACP_FORUM_BASED_PERMISSIONS' => array(
				'ACP_FORUM_PERMISSIONS',
				'ACP_FORUM_PERMISSIONS_COPY',
				'ACP_FORUM_MODERATORS',
				'ACP_USERS_FORUM_PERMISSIONS',
				'ACP_GROUPS_FORUM_PERMISSIONS',
			),
		),
	);
}
