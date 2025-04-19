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

class erk_config_repair
{
	function run()
	{
		if (!file_exists(PHPBB_ROOT_PATH . 'config.php'))
		{
			$this->repair();
			header('Location: ' . STK_INDEX);
			exit;
		}
		return true;
	}
	function repair()
	{
		require_once(PHPBB_ROOT_PATH . 'includes/functions.php');
		require_once(PHPBB_ROOT_PATH . 'includes/functions_install.php');

		$available_dbms = get_available_dbms();

		$error = array();
		$data = array(
			'dbms'			=> (isset($_POST['dbms'])) ? $_POST['dbms'] : '',
			'dbhost'		=> (isset($_POST['dbhost'])) ? $_POST['dbhost'] : '',
			'dbport'		=> (isset($_POST['dbport'])) ? $_POST['dbport'] : '',
			'dbname'		=> (isset($_POST['dbname'])) ? $_POST['dbname'] : '',
			'dbuser'		=> (isset($_POST['dbuser'])) ? $_POST['dbuser'] : '',
			'dbpasswd'		=> (isset($_POST['dbpasswd'])) ? $_POST['dbpasswd'] : '',
			'table_prefix'	=> (isset($_POST['table_prefix'])) ? $_POST['table_prefix'] : 'phpbb_',
		);

		if (isset($_POST['submit']))
		{
			if (!isset($available_dbms['mysql']))
			{
				$error[] = 'Database Connection not available.';
			}
			else
			{
				$connect_test = $this->critical_connect_check_db(true, $error, $available_dbms['mysql'], $data['table_prefix'], $data['dbhost'], $data['dbuser'], htmlspecialchars_decode($data['dbpasswd']), $data['dbname'], $data['dbport']);
				if (!$connect_test)
				{
					$error[] = 'Database Connection failed.';
				}
			}
		}

		if (isset($_POST['submit']) && empty($error))
		{
			// Time to convert the data provided into a config file
			$config_data = "<?php\n\n";

			$config_data_array = array(
				'dbhost'		=> $data['dbhost'],
				'dbport'		=> $data['dbport'],
				'dbname'		=> $data['dbname'],
				'dbuser'		=> $data['dbuser'],
				'dbpasswd'		=> htmlspecialchars_decode($data['dbpasswd']),
				'table_prefix'	=> $data['table_prefix'],
				'acm_type'		=> 'file',
			);

			foreach ($config_data_array as $key => $value)
			{
				$config_data .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
			}
			unset($config_data_array);

			$config_data .= "\n@define('PHPBB_INSTALLED', true);\n";
			$config_data .= "// @define('DEBUG', true);\n";
			$config_data .= "// @define('DEBUG_EXTRA', true);\n";

			// Assume it will work ... if nothing goes wrong below
			$written = true;

			if (!($fp = @fopen(PHPBB_ROOT_PATH . 'config.php', 'w')))
			{
				// Something went wrong ... so let's try another method
				$written = false;
			}

			if (!(@fwrite($fp, $config_data)))
			{
				// Something went wrong ... so let's try another method
				$written = false;
			}

			@fclose($fp);

			if ($written)
			{
				// We may revert back to chmod() if we see problems with users not able to change their config.php file directly
				phpbb_chmod(PHPBB_ROOT_PATH . 'config.php', CHMOD_READ);
			}
			else
			{
				header('Content-type: text/html; charset=UTF-8');
				echo 'ERROR: Could not write config file.  Please copy the text below, put it in a file named config.php, and place it in the root directory of your forum.<br /><br />';
				echo nl2br(htmlspecialchars($config_data));
				exit;
			}
		}
		else
		{
			header('Content-type: text/html; charset=UTF-8');
			?>
<!DOCTYPE html>
<html dir="ltr">
	<head>
		<meta charset="utf-8" />
		<title>Config Repair - Support Toolkit</title>
		<link href="<?php echo STK_ROOT_PATH; ?>style/style.css" rel="stylesheet" media="screen" />
	</head>
	<body id="errorpage">
		<div id="wrap">
			<div id="page-header">
				<h1>Emergency Repair Kit</h1>
				<p>
					<a href="<?php echo STK_ROOT_PATH; ?>">Support Toolkit index</a> &bull;
					<a href="<?php echo PHPBB_ROOT_PATH; ?>">Board index</a>
				</p>
			</div>
			<div id="page-body">
				<div id="acp">
					<div class="panel">
						<div id="content">
							<h1>Config Repair</h1>
							<br />
							<p>
								Through this tool you can regenerate your configuration file.
							</p>
							<form id="stk" method="post" action="<?php echo STK_ROOT_PATH . 'erk.php'; ?>" name="support_tool_kit">
								<fieldset>
									<?php if (!empty($error)) {?>
										<div class="errorbox">
											<h3>Error</h3>
											<p><?php echo implode('<br />', $error); ?></p>
										</div>
									<?php } ?>
									<dl>
										<dt><label for="dbhost">Database server hostname:</label></dt>
										<dd><input id="dbhost" type="text" value="<?php echo $data['dbhost']; ?>" name="dbhost" maxlength="100" size="25"/></dd>
									</dl>
									<dl>
										<dt><label for="dbport">Database server port:</label><br /><span class="explain">Leave this blank unless you know the server operates on a non-standard port.</span></dt>
										<dd><input id="dbport" type="text" value="<?php echo $data['dbport']; ?>" name="dbport" maxlength="100" size="25"/></dd>
									</dl>
									<dl>
										<dt><label for="dbname">Database name:</label></dt>
										<dd><input id="dbname" type="text" value="<?php echo $data['dbname']; ?>" name="dbname" maxlength="100" size="25"/></dd>
									</dl>
									<dl>
										<dt><label for="dbuser">Database username:</label></dt>
										<dd><input id="dbuser" type="text" value="<?php echo $data['dbuser']; ?>" name="dbuser" maxlength="100" size="25"/></dd>
									</dl>
									<dl>
										<dt><label for="dbpasswd">Database password:</label></dt>
										<dd><input id="dbpasswd" type="password" value="" name="dbpasswd" maxlength="100" size="25"/></dd>
									</dl>
									<dl>
										<dt><label for="table_prefix">Prefix for tables in database:</label></dt>
										<dd><input id="table_prefix" type="text" value="<?php echo $data['table_prefix']; ?>" name="table_prefix" maxlength="100" size="25"/></dd>
									</dl>
									<p class="submit-buttons">
										<input class="button1" type="submit" id="submit" name="submit" value="Submit" />
									</p>
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div id="page-footer">
				Powered by <a href="//phpbbex.com/">phpBBex</a>
			</div>
		</div>
	</body>
</html>
			<?php
			exit;
		}
	}
	/**
	* Used to test whether we are able to connect to the database the user has specified
	* and identify any problems (eg there are already tables with the names we want to use
	* @param	array	$dbms should be of the format of an element of the array returned by {@link get_available_dbms get_available_dbms()}
	*					necessary extensions should be loaded already
	*/
	function critical_connect_check_db($error_connect, &$error, $dbms_details, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport, $prefix_may_exist = false, $load_dbal = true, $unicode_check = true)
	{
		if ($dbms_details['MODULE'] != 'mysqli')
		{
			$error[] = 'MySQL 5.5 and newer is required.';
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
			$error[] = 'No database name specified.';
			return false;
		}

		// Check the prefix length to ensure that index names are not too long and does not contain invalid characters
		if (strspn($table_prefix, '-./\\') !== 0)
		{
			$error[] = 'The table prefix you have specified is invalid for your database.';
			return false;
		}

		if (strlen($table_prefix) > 36)
		{
			$error[] = 'The table prefix you have specified is invalid for your database.';
			return false;
		}

		// Try and connect ...
		if (is_array($db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false)))
		{
			$db_error = $db->sql_error();
			$error[] = 'Could not connect to the database, see error message below.' . '<br />' . (($db_error['message']) ? $db_error['message'] : 'No error message given.');
		}
		else
		{
			// Make sure that the user has selected a sensible DBAL for the DBMS actually installed
			if (version_compare(mysqli_get_server_info($db->db_connect_id), '5.5.0', '<'))
			{
				$error[] = 'MySQL 5.5 and newer is required.';
			}

			$tables = get_tables($db);
			if (!in_array($table_prefix . 'acl_options', $tables) || !in_array($table_prefix . 'config', $tables) || !in_array($table_prefix . 'forums', $tables))
			{
				$error[] = 'phpBB3 tables could not be found on this database with this table prefix.';
			}
		}

		if ($error_connect && empty($error))
		{
			return true;
		}
		return false;
	}
}
