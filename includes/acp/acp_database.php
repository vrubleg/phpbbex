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

class acp_database
{
	var $db_tools;
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $cache, $db, $user, $auth, $template, $table_prefix;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		if (!class_exists('phpbb_db_tools'))
		{
			require($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}
		$this->db_tools = new phpbb_db_tools($db);

		$user->add_lang('acp/database');

		$this->tpl_name = 'acp_database';
		$this->page_title = 'ACP_DATABASE';

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$template->assign_vars(array(
			'MODE'	=> $mode
		));

		switch ($mode)
		{
			case 'backup':

				$this->page_title = 'ACP_BACKUP';

				switch ($action)
				{
					case 'download':
						$type	= request_var('type', '');
						$table	= array_intersect($this->db_tools->sql_list_tables(), request_var('table', array('')));
						$format	= request_var('method', '');
						$where	= request_var('where', '');

						if (!sizeof($table))
						{
							trigger_error($user->lang['TABLE_SELECT_ERROR'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$store = $download = $structure = $schema_data = false;

						if ($where == 'store_and_download' || $where == 'store')
						{
							$store = true;
						}

						if ($where == 'store_and_download' || $where == 'download')
						{
							$download = true;
						}

						if ($type == 'full' || $type == 'structure')
						{
							$structure = true;
						}

						if ($type == 'full' || $type == 'data')
						{
							$schema_data = true;
						}

						@set_time_limit(1200);
						@set_time_limit(0);

						$time = time();

						$filename = 'backup_' . $time . '_' . unique_id();
						$extractor = new mysql_extractor($download, $store, $format, $filename, $time);
						$extractor->write_start($table_prefix);

						foreach ($table as $table_name)
						{
							// Get the table structure
							if ($structure)
							{
								$extractor->write_table($table_name);
							}
							else
							{
								// We might wanna empty out all that junk :D
								$extractor->flush('TRUNCATE TABLE ' . $table_name . ";\n");
							}

							// Data
							if ($schema_data)
							{
								$extractor->write_data($table_name);
							}
						}

						$extractor->write_end();

						add_log('admin', 'LOG_DB_BACKUP');

						if ($download == true)
						{
							exit;
						}

						trigger_error($user->lang['BACKUP_SUCCESS'] . adm_back_link($this->u_action));
					break;

					default:
						$tables = $this->db_tools->sql_list_tables();
						asort($tables);
						foreach ($tables as $table_name)
						{
							if (strlen($table_prefix) === 0 || stripos($table_name, $table_prefix) === 0)
							{
								$template->assign_block_vars('tables', array(
									'TABLE'	=> $table_name
								));
							}
						}
						unset($tables);

						$template->assign_vars(array(
							'U_ACTION'	=> $this->u_action . '&amp;action=download'
						));

						$available_methods = array('gzip' => 'zlib', 'bzip2' => 'bz2');

						foreach ($available_methods as $type => $module)
						{
							if (!@extension_loaded($module))
							{
								continue;
							}

							$template->assign_block_vars('methods', array(
								'TYPE'	=> $type
							));
						}

						$template->assign_block_vars('methods', array(
							'TYPE'	=> 'text'
						));
					break;
				}
			break;

			case 'restore':

				$this->page_title = 'ACP_RESTORE';

				switch ($action)
				{
					case 'submit':
						$delete = request_var('delete', '');
						$file = request_var('file', '');
						$download = request_var('download', '');

						if (!preg_match('#^backup_\d{10,}_[a-z\d]{16}\.(sql(?:\.(?:gz|bz2))?)$#', $file, $matches))
						{
							trigger_error($user->lang['BACKUP_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$file_name = $phpbb_root_path . 'store/' . $matches[0];

						if (!file_exists($file_name) || !is_readable($file_name))
						{
							trigger_error($user->lang['BACKUP_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if ($delete)
						{
							if (confirm_box(true))
							{
								unlink($file_name);
								add_log('admin', 'LOG_DB_DELETE');
								trigger_error($user->lang['BACKUP_DELETE'] . adm_back_link($this->u_action));
							}
							else
							{
								confirm_box(false, $user->lang['DELETE_SELECTED_BACKUP'], build_hidden_fields(array('delete' => $delete, 'file' => $file)));
							}
						}
						else if ($download || confirm_box(true))
						{
							if ($download)
							{
								$name = $matches[0];

								switch ($matches[1])
								{
									case 'sql':
										$mimetype = 'text/x-sql';
									break;
									case 'sql.bz2':
										$mimetype = 'application/x-bzip2';
									break;
									case 'sql.gz':
										$mimetype = 'application/x-gzip';
									break;
								}

								header('Cache-Control: no-store');
								header("Content-Type: $mimetype; name=\"$name\"");
								header("Content-Disposition: attachment; filename=$name");

								@set_time_limit(0);

								$fp = @fopen($file_name, 'rb');

								if ($fp !== false)
								{
									while (!feof($fp))
									{
										echo fread($fp, 8192);
									}
									fclose($fp);
								}

								flush();
								exit;
							}

							switch ($matches[1])
							{
								case 'sql':
									$fp = fopen($file_name, 'rb');
									$read = 'fread';
									$seek = 'fseek';
									$eof = 'feof';
									$close = 'fclose';
									$fgetd = 'fgetd';
								break;

								case 'sql.bz2':
									$fp = bzopen($file_name, 'r');
									$read = 'bzread';
									$seek = '';
									$eof = 'feof';
									$close = 'bzclose';
									$fgetd = 'fgetd_seekless';
								break;

								case 'sql.gz':
									$fp = gzopen($file_name, 'rb');
									$read = 'gzread';
									$seek = 'gzseek';
									$eof = 'gzeof';
									$close = 'gzclose';
									$fgetd = 'fgetd';
								break;
							}

							while (($sql = $fgetd($fp, ";\n", $read, $seek, $eof)) !== false)
							{
								$db->sql_query($sql);
							}

							$close($fp);

							// Purge the cache due to updated data
							$cache->purge();

							add_log('admin', 'LOG_DB_RESTORE');
							trigger_error($user->lang['RESTORE_SUCCESS'] . adm_back_link($this->u_action));
							break;
						}
						else if (!$download)
						{
							confirm_box(false, $user->lang['RESTORE_SELECTED_BACKUP'], build_hidden_fields(array('file' => $file)));
						}

					default:
						$methods = array('sql');
						$available_methods = array('sql.gz' => 'zlib', 'sql.bz2' => 'bz2');

						foreach ($available_methods as $type => $module)
						{
							if (!@extension_loaded($module))
							{
								continue;
							}
							$methods[] = $type;
						}

						$dir = $phpbb_root_path . 'store/';
						$dh = @opendir($dir);

						$backup_files = array();

						if ($dh)
						{
							while (($file = readdir($dh)) !== false)
							{
								if (preg_match('#^backup_(\d{10,})_[a-z\d]{16}\.(sql(?:\.(?:gz|bz2))?)$#', $file, $matches))
								{
									if (in_array($matches[2], $methods))
									{
										$backup_files[(int) $matches[1]] = $file;
									}
								}
							}
							closedir($dh);
						}

						if (!empty($backup_files))
						{
							krsort($backup_files);

							foreach ($backup_files as $name => $file)
							{
								$template->assign_block_vars('files', array(
									'FILE'		=> $file,
									'NAME'		=> $user->format_date($name, 'd-m-Y H:i:s', true),
									'SUPPORTED'	=> true,
								));
							}
						}

						$template->assign_vars(array(
							'U_ACTION'	=> $this->u_action . '&amp;action=submit'
						));
					break;
				}
			break;
		}
	}
}

/**
* @package acp
*/
class base_extractor
{
	var $fh;
	var $fp;
	var $write;
	var $close;
	var $store;
	var $download;
	var $time;
	var $format;
	var $run_comp = false;

	function __construct($download, $store, $format, $filename, $time)
	{
		$this->download = $download;
		$this->store = $store;
		$this->time = $time;
		$this->format = $format;

		switch ($format)
		{
			case 'text':
				$ext = '.sql';
				$open = 'fopen';
				$this->write = 'fwrite';
				$this->close = 'fclose';
				$mimetype = 'text/x-sql';
			break;
			case 'bzip2':
				$ext = '.sql.bz2';
				$open = 'bzopen';
				$this->write = 'bzwrite';
				$this->close = 'bzclose';
				$mimetype = 'application/x-bzip2';
			break;
			case 'gzip':
				$ext = '.sql.gz';
				$open = 'gzopen';
				$this->write = 'gzwrite';
				$this->close = 'gzclose';
				$mimetype = 'application/x-gzip';
			break;
		}

		if ($download == true)
		{
			$name = $filename . $ext;
			header('Cache-Control: no-store');
			header("Content-Type: $mimetype; name=\"$name\"");
			header("Content-Disposition: attachment; filename=$name");

			switch ($format)
			{
				case 'bzip2':
					ob_start();
				break;

				case 'gzip':
					if ((isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'msie') === false)
					{
						ob_start('ob_gzhandler');
					}
					else
					{
						$this->run_comp = true;
					}
				break;
			}
		}

		if ($store == true)
		{
			global $phpbb_root_path;
			$file = $phpbb_root_path . 'store/' . $filename . $ext;

			$this->fp = $open($file, 'w');

			if (!$this->fp)
			{
				trigger_error('FILE_WRITE_FAIL', E_USER_ERROR);
			}
		}
	}

	function write_end()
	{
		static $close;

		if ($this->store)
		{
			if ($close === null)
			{
				$close = $this->close;
			}
			$close($this->fp);
		}

		// bzip2 must be written all the way at the end
		if ($this->download && $this->format === 'bzip2')
		{
			$c = ob_get_clean();
			echo bzcompress($c);
		}
	}

	function flush($data)
	{
		static $write;
		if ($this->store === true)
		{
			if ($write === null)
			{
				$write = $this->write;
			}
			$write($this->fp, $data);
		}

		if ($this->download === true)
		{
			if ($this->format === 'bzip2' || $this->format === 'text' || ($this->format === 'gzip' && !$this->run_comp))
			{
				echo $data;
			}

			// we can write the gzip data as soon as we get it
			if ($this->format === 'gzip')
			{
				if ($this->run_comp)
				{
					echo gzencode($data);
				}
				else
				{
					ob_flush();
					flush();
				}
			}
		}
	}
}

/**
* @package acp
*/
class mysql_extractor extends base_extractor
{
	function write_start($table_prefix)
	{
		$sql_data = "#\n";
		$sql_data .= "# phpBB Backup Script\n";
		$sql_data .= "# Dump of tables for $table_prefix\n";
		$sql_data .= "# DATE : " . gmdate("d-m-Y H:i:s", $this->time) . " GMT\n";
		$sql_data .= "#\n";
		$this->flush($sql_data);
	}

	function write_table($table_name)
	{
		global $db;

		$sql = 'SHOW CREATE TABLE ' . $table_name;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);

		$sql_data = '# Table: ' . $table_name . "\n";
		$sql_data .= "DROP TABLE IF EXISTS $table_name;\n";
		$this->flush($sql_data . $row['Create Table'] . ";\n\n");

		$db->sql_freeresult($result);
	}

	function write_data($table_name)
	{
		global $db;
		$sql = "SELECT *
			FROM $table_name";
		$result = mysqli_query($db->db_connect_id, $sql, MYSQLI_USE_RESULT);
		if ($result != false)
		{
			$fields_cnt = mysqli_num_fields($result);

			// Get field information
			$field = mysqli_fetch_fields($result);
			$field_set = array();

			for ($j = 0; $j < $fields_cnt; $j++)
			{
				$field_set[] = $field[$j]->name;
			}

			$search			= array("\\", "'", "\x00", "\x0a", "\x0d", "\x1a", '"');
			$replace		= array("\\\\", "\\'", '\0', '\n', '\r', '\Z', '\\"');
			$fields			= implode(', ', $field_set);
			$sql_data		= 'INSERT INTO ' . $table_name . ' (' . $fields . ') VALUES ';
			$first_set		= true;
			$query_len		= 0;
			$max_len		= get_usable_memory();

			while ($row = mysqli_fetch_row($result))
			{
				$values	= array();
				if ($first_set)
				{
					$query = $sql_data . '(';
				}
				else
				{
					$query  .= ',(';
				}

				for ($j = 0; $j < $fields_cnt; $j++)
				{
					if (!isset($row[$j]) || is_null($row[$j]))
					{
						$values[$j] = 'NULL';
					}
					else if (($field[$j]->flags & 32768) && !($field[$j]->flags & 1024))
					{
						$values[$j] = $row[$j];
					}
					else
					{
						$values[$j] = "'" . str_replace($search, $replace, $row[$j]) . "'";
					}
				}
				$query .= implode(', ', $values) . ')';

				$query_len += strlen($query);
				if ($query_len > $max_len)
				{
					$this->flush($query . ";\n\n");
					$query = '';
					$query_len = 0;
					$first_set = true;
				}
				else
				{
					$first_set = false;
				}
			}
			mysqli_free_result($result);

			// check to make sure we have nothing left to flush
			if (!$first_set && $query)
			{
				$this->flush($query . ";\n\n");
			}
		}
	}
}

// get how much space we allow for a chunk of data, very similar to phpMyAdmin's way of doing things ;-) (hey, we only do this for MySQL anyway :P)
function get_usable_memory()
{
	$val = trim(@ini_get('memory_limit'));

	if (preg_match('/(\\d+)([mkg]?)/i', $val, $regs))
	{
		$memory_limit = (int) $regs[1];
		switch ($regs[2])
		{

			case 'k':
			case 'K':
				$memory_limit *= 1024;
			break;

			case 'm':
			case 'M':
				$memory_limit *= 1048576;
			break;

			case 'g':
			case 'G':
				$memory_limit *= 1073741824;
			break;
		}

		// how much memory PHP requires at the start of export (it is really a little less)
		if ($memory_limit > 6100000)
		{
			$memory_limit -= 6100000;
		}

		// allow us to consume half of the total memory available
		$memory_limit /= 2;
	}
	else
	{
		// set the buffer to 1M if we have no clue how much memory PHP will give us :P
		$memory_limit = 1048576;
	}

	return $memory_limit;
}

// modified from PHP.net
function fgetd(&$fp, $delim, $read, $seek, $eof, $buffer = 8192)
{
	$record = '';
	$delim_len = strlen($delim);

	while (!$eof($fp))
	{
		$pos = strpos($record, $delim);
		if ($pos === false)
		{
			$record .= $read($fp, $buffer);
			if ($eof($fp) && ($pos = strpos($record, $delim)) !== false)
			{
				$seek($fp, $pos + $delim_len - strlen($record), SEEK_CUR);
				return substr($record, 0, $pos);
			}
		}
		else
		{
			$seek($fp, $pos + $delim_len - strlen($record), SEEK_CUR);
			return substr($record, 0, $pos);
		}
	}

	return false;
}

function fgetd_seekless(&$fp, $delim, $read, $seek, $eof, $buffer = 8192)
{
	static $array = array();
	static $record = '';

	if (!sizeof($array))
	{
		while (!$eof($fp))
		{
			if (strpos($record, $delim) !== false)
			{
				$array = explode($delim, $record);
				$record = array_pop($array);
				break;
			}
			else
			{
				$record .= $read($fp, $buffer);
			}
		}
		if ($eof($fp) && strpos($record, $delim) !== false)
		{
			$array = explode($delim, $record);
			$record = array_pop($array);
		}
	}

	if (sizeof($array))
	{
		return array_shift($array);
	}

	return false;
}
