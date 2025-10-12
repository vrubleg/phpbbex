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
* Returns an array of available DBMS with some data, if a DBMS is specified it will only
* return data for that DBMS and will load its extension if necessary.
*/
function get_available_dbms($dbms = false, $return_unavailable = false)
{
	global $lang;
	$available_dbms = [
		'mysql' => [
			'LABEL'			=> 'MySQLi',
			'MODULE'		=> 'mysqli',
			'AVAILABLE'		=> true,
		],
	];

	if ($dbms)
	{
		if (isset($available_dbms[$dbms]))
		{
			$available_dbms = [$dbms => $available_dbms[$dbms]];
		}
		else
		{
			return [];
		}
	}

	// now perform some checks whether they are really available
	foreach ($available_dbms as $db_name => $db_ary)
	{
		$dll = $db_ary['MODULE'];

		if (!@extension_loaded($dll))
		{
			if ($return_unavailable)
			{
				$available_dbms[$db_name]['AVAILABLE'] = false;
			}
			else
			{
				unset($available_dbms[$db_name]);
			}
			continue;
		}
		$any_db_support = true;
	}

	if ($return_unavailable)
	{
		$available_dbms['ANY_DB_SUPPORT'] = $any_db_support;
	}
	return $available_dbms;
}

/**
* Generate the drop down of available database options
*/
function dbms_select($default = '')
{
	global $lang;

	$available_dbms = get_available_dbms(false, false);
	$dbms_options = '';
	foreach ($available_dbms as $dbms_name => $details)
	{
		$selected = ($dbms_name == $default) ? ' selected="selected"' : '';
		$dbms_options .= '<option value="' . $dbms_name . '"' . $selected .'>' . $lang['DLL_' . strtoupper($dbms_name)] . '</option>';
	}
	return $dbms_options;
}

/**
* Get tables of a database
*
* @deprecated
*/
function get_tables(&$db)
{
	if (!class_exists('phpbb_db_tools'))
	{
		require_once(PHPBB_ROOT_PATH . 'includes/db/db_tools.php');
	}

	$db_tools = new phpbb_db_tools($db);

	return $db_tools->sql_list_tables();
}

/**
* Used to test whether we are able to connect to the database the user has specified
* and identify any problems (eg there are already tables with the names we want to use
* @param	array	$dbms should be of the format of an element of the array returned by {@link get_available_dbms get_available_dbms()}
*					necessary extensions should be loaded already
*/
function connect_check_db($error_connect, &$error, $dbms_details, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport, $prefix_may_exist = false, $load_dbal = true, $unicode_check = true)
{
	global $config, $lang;

	if ($dbms_details['MODULE'] != 'mysqli')
	{
		$error[] = $lang['INST_ERR_DB_NO_MYSQLI'];
		return false;
	}

	if ($load_dbal)
	{
		// Include the DB layer
		require_once(PHPBB_ROOT_PATH . 'includes/db/mysql.php');
	}

	// Instantiate it and set return on error true
	$db = new dbal_mysql();
	$db->sql_return_on_error(true);

	// Check that we actually have a database name before going any further.....
	if ($dbname === '')
	{
		$error[] = $lang['INST_ERR_DB_NO_NAME'];
		return false;
	}

	// Check the prefix length to ensure that index names are not too long and does not contain invalid characters
	if (strspn($table_prefix, '-./\\') !== 0)
	{
		$error[] = $lang['INST_ERR_PREFIX_INVALID'];
		return false;
	}

	if (strlen($table_prefix) > 36)
	{
		$error[] = sprintf($lang['INST_ERR_PREFIX_TOO_LONG'], 36);
		return false;
	}

	// Try and connect ...
	if (is_array($db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false)))
	{
		$db_error = $db->sql_error();
		$error[] = $lang['INST_ERR_DB_CONNECT'] . '<br />' . (($db_error['message']) ? utf8_convert_message($db_error['message']) : $lang['INST_ERR_DB_NO_ERROR']);
	}
	else
	{
		// Likely matches for an existing phpBB installation
		if (!$prefix_may_exist)
		{
			$temp_prefix = strtolower($table_prefix);
			$table_ary = [$temp_prefix . 'attachments', $temp_prefix . 'config', $temp_prefix . 'sessions', $temp_prefix . 'topics', $temp_prefix . 'users'];

			$tables = get_tables($db);
			$tables = array_map('strtolower', $tables);
			$table_intersect = array_intersect($tables, $table_ary);

			if (sizeof($table_intersect))
			{
				$error[] = $lang['INST_ERR_PREFIX'];
			}
		}

		// Make sure that the user has selected a sensible DBAL for the DBMS actually installed
		if (version_compare(mysqli_get_server_info($db->db_connect_id), '5.5.0', '<'))
		{
			$error[] = $lang['INST_ERR_DB_NO_MYSQLI'];
		}
	}

	if ($error_connect && (!isset($error) || !sizeof($error)))
	{
		return true;
	}
	return false;
}

/**
* Removes "/* style" as well as "# and -- style" comments.
*/
function sql_remove_comments($sql)
{
	// Remove /* */ comments (http://ostermiller.org/findcomment.html).
	$sql = preg_replace('#/\*(.|[\r\n])*?\*/#', "\n", $sql);

	// Remove # and -- style comments.
	$sql = preg_replace('/^\s*(#|--).*$/m', "\n", $sql);
	$sql = preg_replace('/\n{2,}/', "\n", $sql);

	return $sql;
}

/**
* Splits an SQL file into array of SQL statements.
*/
function sql_split_queries($sql)
{
	$sql = sql_remove_comments($sql);

	$sql = str_replace("\r" , '', $sql);
	$ary = preg_split('/;$/m', $sql);

	$ary = array_map('trim', $ary);
	$ary = array_filter($ary, 'strlen');

	return array_values($ary);
}

/**
* For replacing {L_*} strings with preg_replace_callback
*/
function adjust_language_keys_callback($matches)
{
	if (!empty($matches[1]))
	{
		global $lang, $db;

		return (!empty($lang[$matches[1]])) ? $db->sql_escape($lang[$matches[1]]) : $db->sql_escape($matches[1]);
	}
}

/**
* Creates the output to be stored in a phpBB config.php file
*
* @param	array	$data Array containing the database connection information
* @param	bool	$debug If the debug constants should be enabled by default or not
* @param	bool	$debug_test If the DEBUG_TEST constant should be added
*					NOTE: Only for use within the testing framework
*
* @return	string	The output to write to the file
*/
function phpbb_create_config_file_data($data, $debug = false, $debug_test = false)
{
	$config_data = "<?php\n\n";

	$config_data_array = [
		'dbhost'		=> $data['dbhost'],
		'dbport'		=> $data['dbport'],
		'dbname'		=> $data['dbname'],
		'dbuser'		=> $data['dbuser'],
		'dbpasswd'		=> htmlspecialchars_decode($data['dbpasswd']),
		'table_prefix'	=> $data['table_prefix'],
		'acm_type'		=> 'file',
	];

	foreach ($config_data_array as $key => $value)
	{
		$config_data .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
	}

	$config_data .= "\n@define('PHPBB_INSTALLED', true);\n";

	if ($debug)
	{
		$config_data .= "@define('DEBUG', true);\n";
		$config_data .= "@define('DEBUG_EXTRA', true);\n";
	}
	else
	{
		$config_data .= "// @define('DEBUG', true);\n";
		$config_data .= "// @define('DEBUG_EXTRA', true);\n";
	}

	if ($debug_test)
	{
		$config_data .= "@define('DEBUG_TEST', true);\n";
	}

	return $config_data;
}
