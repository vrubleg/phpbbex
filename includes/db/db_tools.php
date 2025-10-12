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
* Database Tools for handling cross-db actions such as altering columns, etc.
* Currently not supported is returning SQL for creating tables.
*
* @package dbal
* @note currently not used within phpBB3, but may be utilized later.
*/
class phpbb_db_tools
{
	/**
	* @var object DB object
	*/
	var $db = NULL;

	/**
	* The Column types for every database we support
	* @var array
	*/
	var $dbms_type_map = [
		'INT:'		=> 'int(%d)',
		'BINT'		=> 'bigint(20)',
		'UINT'		=> 'mediumint(8) UNSIGNED',
		'UINT:'		=> 'int(%d) UNSIGNED',
		'TINT:'		=> 'tinyint(%d)',
		'USINT'		=> 'smallint(4) UNSIGNED',
		'BOOL'		=> 'tinyint(1) UNSIGNED',
		'VCHAR'		=> 'varchar(255)',
		'VCHAR:'	=> 'varchar(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'text',
		'XSTEXT_UNI'=> 'varchar(100)',
		'STEXT'		=> 'text',
		'STEXT_UNI'	=> 'varchar(255)',
		'TEXT'		=> 'text',
		'TEXT_UNI'	=> 'text',
		'MTEXT'		=> 'mediumtext',
		'MTEXT_UNI'	=> 'mediumtext',
		'TIMESTAMP'	=> 'int(11) UNSIGNED',
		'DECIMAL'	=> 'decimal(5,2)',
		'DECIMAL:'	=> 'decimal(%d,2)',
		'PDECIMAL'	=> 'decimal(6,3)',
		'PDECIMAL:'	=> 'decimal(%d,3)',
		'VCHAR_UNI'	=> 'varchar(255)',
		'VCHAR_UNI:'=> 'varchar(%d)',
		'VCHAR_CI'	=> 'varchar(255)',
		'VARBINARY'	=> 'varbinary(255)',
	];

	/**
	* A list of types being unsigned for better reference in some db's
	* @var array
	*/
	var $unsigned_types = ['UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP'];

	/**
	* A list of supported DBMS. We change this class to support more DBMS, the DBMS itself only need to follow some rules.
	* @var array
	*/
	var $supported_dbms = ['mysql'];

	/**
	* This is set to true if user only wants to return the 'to-be-executed' SQL statement(s) (as an array).
	* This mode has no effect on some methods (inserting of data for example). This is expressed within the methods command.
	*/
	var $return_statements = false;

	/**
	* Constructor. Set DB Object and set {@link $return_statements return_statements}.
	*
	* @param phpbb_dbal	$db					DBAL object
	* @param bool		$return_statements	True if only statements should be returned and no SQL being executed
	*/
	function __construct(&$db, $return_statements = false)
	{
		$this->db = $db;
		$this->return_statements = $return_statements;
	}

	/**
	* Gets a list of tables in the database.
	*
	* @return array		Array of table names  (all lower case)
	*/
	function sql_list_tables()
	{
		$sql = 'SHOW TABLES';

		$result = $this->db->sql_query($sql);

		$tables = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$name = current($row);
			$tables[$name] = $name;
		}
		$this->db->sql_freeresult($result);

		return $tables;
	}

	/**
	* Check if table exists
	*
	*
	* @param string	$table_name	The table name to check for
	* @return bool true if table exists, else false
	*/
	function sql_table_exists($table_name)
	{
		$this->db->sql_return_on_error(true);
		$result = $this->db->sql_query_limit('SELECT * FROM ' . $table_name, 1);
		$this->db->sql_return_on_error(false);

		if ($result)
		{
			$this->db->sql_freeresult($result);
			return true;
		}

		return false;
	}

	/**
	* Create SQL Table
	*
	* @param string	$table_name	The table name to create
	* @param array	$table_data	Array containing table data.
	* @return array	Statements if $return_statements is true.
	*/
	function sql_create_table($table_name, $table_data)
	{
		// holds the DDL for a column
		$columns = $statements = [];

		if ($this->sql_table_exists($table_name))
		{
			return $this->_sql_run_sql($statements);
		}

		// Begin transaction
		$statements[] = 'begin';

		// Determine if we have created a PRIMARY KEY in the earliest
		$primary_key_gen = false;

		// Determine if the table requires a sequence
		$create_sequence = false;

		// Begin table sql statement
		$table_sql = 'CREATE TABLE ' . $table_name . ' (' . "\n";

		// Iterate through the columns to create a table
		foreach ($table_data['COLUMNS'] as $column_name => $column_data)
		{
			// here lies an array, filled with information compiled on the column's data
			$prepared_column = $this->sql_prepare_column_data($table_name, $column_name, $column_data);

			if (isset($prepared_column['auto_increment']) && $prepared_column['auto_increment'] && strlen($column_name) > 26) // "{$column_name}_gen"
			{
				trigger_error("Index name '{$column_name}_gen' on table '$table_name' is too long. The maximum auto increment column length is 26 characters.", E_USER_ERROR);
			}

			// here we add the definition of the new column to the list of columns
			$columns[] = "\t {$column_name} " . $prepared_column['column_type_sql'];

			// see if we have found a primary key set due to a column definition if we have found it, we can stop looking
			if (!$primary_key_gen)
			{
				$primary_key_gen = isset($prepared_column['primary_key_set']) && $prepared_column['primary_key_set'];
			}

			// create sequence DDL based off of the existance of auto incrementing columns
			if (!$create_sequence && isset($prepared_column['auto_increment']) && $prepared_column['auto_increment'])
			{
				$create_sequence = $column_name;
			}
		}

		// this makes up all the columns in the create table statement
		$table_sql .= implode(",\n", $columns);

		// we have yet to create a primary key for this table,
		// this means that we can add the one we really wanted instead
		if (!$primary_key_gen)
		{
			// Write primary key
			if (isset($table_data['PRIMARY_KEY']))
			{
				if (!is_array($table_data['PRIMARY_KEY']))
				{
					$table_data['PRIMARY_KEY'] = [$table_data['PRIMARY_KEY']];
				}

				$table_sql .= ",\n\t PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
			}
		}

		// close the table
		// make sure the table is in UTF-8 mode
		$table_sql .= "\n) CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`;";
		$statements[] = $table_sql;

		// Write Keys
		if (isset($table_data['KEYS']))
		{
			foreach ($table_data['KEYS'] as $key_name => $key_data)
			{
				if (!is_array($key_data[1]))
				{
					$key_data[1] = [$key_data[1]];
				}

				$old_return_statements = $this->return_statements;
				$this->return_statements = true;

				$key_stmts = ($key_data[0] == 'UNIQUE') ? $this->sql_create_unique_index($table_name, $key_name, $key_data[1]) : $this->sql_create_index($table_name, $key_name, $key_data[1]);

				foreach ($key_stmts as $key_stmt)
				{
					$statements[] = $key_stmt;
				}

				$this->return_statements = $old_return_statements;
			}
		}

		// Commit Transaction
		$statements[] = 'commit';

		return $this->_sql_run_sql($statements);
	}

	/**
	* Handle passed database update array.
	* Expected structure...
	* Key being one of the following
	*	change_columns: Column changes (only type, not name)
	*	add_columns: Add columns to a table
	*	drop_keys: Dropping keys
	*	drop_columns: Removing/Dropping columns
	*	add_primary_keys: adding primary keys
	*	add_unique_index: adding an unique index
	*	add_index: adding an index (can be column:index_size if you need to provide size)
	*
	* The values are in this format:
	*		{TABLE NAME}		=> array(
	*			{COLUMN NAME}		=> array({COLUMN TYPE}, {DEFAULT VALUE}, {OPTIONAL VARIABLES}),
	*			{KEY/INDEX NAME}	=> array({COLUMN NAMES}),
	*		)
	*
	* For more information have a look at /develop/create_schema_files.php (only available through SVN)
	*/
	function perform_schema_changes($schema_changes)
	{
		if (empty($schema_changes))
		{
			return;
		}

		$statements = [];

		// Drop tables?
		if (!empty($schema_changes['drop_tables']))
		{
			foreach ($schema_changes['drop_tables'] as $table)
			{
				// only drop table if it exists
				if ($this->sql_table_exists($table))
				{
					$result = $this->sql_table_drop($table);
					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Add tables?
		if (!empty($schema_changes['add_tables']))
		{
			foreach ($schema_changes['add_tables'] as $table => $table_data)
			{
				$result = $this->sql_create_table($table, $table_data);
				if ($this->return_statements)
				{
					$statements = array_merge($statements, $result);
				}
			}
		}

		// Change columns?
		if (!empty($schema_changes['change_columns']))
		{
			foreach ($schema_changes['change_columns'] as $table => $columns)
			{
				foreach ($columns as $column_name => $column_data)
				{
					// If the column exists we change it, else we add it ;)
					if ($column_exists = $this->sql_column_exists($table, $column_name))
					{
						$result = $this->sql_column_change($table, $column_name, $column_data, true);
					}
					else
					{
						$result = $this->sql_column_add($table, $column_name, $column_data, true);
					}

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Add columns?
		if (!empty($schema_changes['add_columns']))
		{
			foreach ($schema_changes['add_columns'] as $table => $columns)
			{
				foreach ($columns as $column_name => $column_data)
				{
					// Only add the column if it does not exist yet
					if ($column_exists = $this->sql_column_exists($table, $column_name))
					{
						continue;
						// This is commented out here because it can take tremendous time on updates
//						$result = $this->sql_column_change($table, $column_name, $column_data, true);
					}
					else
					{
						$result = $this->sql_column_add($table, $column_name, $column_data, true);
					}

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Remove keys?
		if (!empty($schema_changes['drop_keys']))
		{
			foreach ($schema_changes['drop_keys'] as $table => $indexes)
			{
				foreach ($indexes as $index_name)
				{
					if (!$this->sql_index_exists($table, $index_name))
					{
						continue;
					}

					$result = $this->sql_index_drop($table, $index_name);

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Drop columns?
		if (!empty($schema_changes['drop_columns']))
		{
			foreach ($schema_changes['drop_columns'] as $table => $columns)
			{
				foreach ($columns as $column)
				{
					// Only remove the column if it exists...
					if ($this->sql_column_exists($table, $column))
					{
						$result = $this->sql_column_remove($table, $column, true);

						if ($this->return_statements)
						{
							$statements = array_merge($statements, $result);
						}
					}
				}
			}
		}

		// Add primary keys?
		if (!empty($schema_changes['add_primary_keys']))
		{
			foreach ($schema_changes['add_primary_keys'] as $table => $columns)
			{
				$result = $this->sql_create_primary_key($table, $columns, true);

				if ($this->return_statements)
				{
					$statements = array_merge($statements, $result);
				}
			}
		}

		// Add unique indexes?
		if (!empty($schema_changes['add_unique_index']))
		{
			foreach ($schema_changes['add_unique_index'] as $table => $index_array)
			{
				foreach ($index_array as $index_name => $column)
				{
					if ($this->sql_unique_index_exists($table, $index_name))
					{
						continue;
					}

					$result = $this->sql_create_unique_index($table, $index_name, $column);

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		// Add indexes?
		if (!empty($schema_changes['add_index']))
		{
			foreach ($schema_changes['add_index'] as $table => $index_array)
			{
				foreach ($index_array as $index_name => $column)
				{
					if ($this->sql_index_exists($table, $index_name))
					{
						continue;
					}

					$result = $this->sql_create_index($table, $index_name, $column);

					if ($this->return_statements)
					{
						$statements = array_merge($statements, $result);
					}
				}
			}
		}

		if ($this->return_statements)
		{
			return $statements;
		}
	}

	/**
	* Gets a list of columns of a table.
	*
	* @param string $table		Table name
	*
	* @return array				Array of column names (all lower case)
	*/
	function sql_list_columns($table)
	{
		$columns = [];

		$sql = "SHOW COLUMNS FROM $table";

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$column = strtolower(current($row));
			$columns[$column] = $column;
		}
		$this->db->sql_freeresult($result);

		return $columns;
	}

	/**
	* Check whether a specified column exist in a table
	*
	* @param string	$table			Table to check
	* @param string	$column_name	Column to check
	*
	* @return bool		True if column exists, false otherwise
	*/
	function sql_column_exists($table, $column_name)
	{
		$columns = $this->sql_list_columns($table);

		return isset($columns[$column_name]);
	}

	/**
	* Check if a specified index exists in table. Does not return PRIMARY KEY and UNIQUE indexes.
	*
	* @param string	$table_name		Table to check the index at
	* @param string	$index_name		The index name to check
	*
	* @return bool True if index exists, else false
	*/
	function sql_index_exists($table_name, $index_name)
	{
		$sql = 'SHOW KEYS FROM ' . $table_name;

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$row['Non_unique'])
			{
				continue;
			}

			if (strtolower($row['Key_name']) == strtolower($index_name))
			{
				$this->db->sql_freeresult($result);
				return true;
			}
		}
		$this->db->sql_freeresult($result);

		return false;
	}

	/**
	* Check if a specified index exists in table. Does not return PRIMARY KEY indexes.
	*
	* @param string	$table_name		Table to check the index at
	* @param string	$index_name		The index name to check
	*
	* @return bool True if index exists, else false
	*/
	function sql_unique_index_exists($table_name, $index_name)
	{
		$sql = 'SHOW KEYS FROM ' . $table_name;

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['Non_unique'] || $row['Key_name'] == 'PRIMARY')
			{
				continue;
			}

			if (strtolower($row['Key_name']) == strtolower($index_name))
			{
				$this->db->sql_freeresult($result);
				return true;
			}
		}
		$this->db->sql_freeresult($result);

		return false;
	}

	/**
	* Private method for performing sql statements (either execute them or return them)
	* @access private
	*/
	function _sql_run_sql($statements)
	{
		if ($this->return_statements)
		{
			return $statements;
		}

		// We could add error handling here...
		foreach ($statements as $sql)
		{
			if ($sql === 'begin')
			{
				$this->db->sql_transaction('begin');
			}
			else if ($sql === 'commit')
			{
				$this->db->sql_transaction('commit');
			}
			else
			{
				$this->db->sql_query($sql);
			}
		}

		return true;
	}

	/**
	* Function to prepare some column information for better usage
	* @access private
	*/
	function sql_prepare_column_data($table_name, $column_name, $column_data)
	{
		if (strlen($column_name) > 30)
		{
			trigger_error("Column name '$column_name' on table '$table_name' is too long. The maximum is 30 characters.", E_USER_ERROR);
		}

		// Get type
		if (strpos($column_data[0], ':') !== false)
		{
			list($orig_column_type, $column_length) = explode(':', $column_data[0]);
			if (!is_array($this->dbms_type_map[$orig_column_type . ':']))
			{
				$column_type = sprintf($this->dbms_type_map[$orig_column_type . ':'], $column_length);
			}
			else
			{
				if (isset($this->dbms_type_map[$orig_column_type . ':']['rule']))
				{
					switch ($this->dbms_type_map[$orig_column_type . ':']['rule'][0])
					{
						case 'div':
							$column_length /= $this->dbms_type_map[$orig_column_type . ':']['rule'][1];
							$column_length = ceil($column_length);
							$column_type = sprintf($this->dbms_type_map[$orig_column_type . ':'][0], $column_length);
						break;
					}
				}

				if (isset($this->dbms_type_map[$orig_column_type . ':']['limit']))
				{
					switch ($this->dbms_type_map[$orig_column_type . ':']['limit'][0])
					{
						case 'mult':
							$column_length *= $this->dbms_type_map[$orig_column_type . ':']['limit'][1];
							if ($column_length > $this->dbms_type_map[$orig_column_type . ':']['limit'][2])
							{
								$column_type = $this->dbms_type_map[$orig_column_type . ':']['limit'][3];
							}
							else
							{
								$column_type = sprintf($this->dbms_type_map[$orig_column_type . ':'][0], $column_length);
							}
						break;
					}
				}
			}
			$orig_column_type .= ':';
		}
		else
		{
			$orig_column_type = $column_data[0];
			$column_type = $this->dbms_type_map[$column_data[0]];
		}

		$sql = '';

		$return_array = [];

		$sql .= " {$column_type} ";

		// For hexadecimal values do not use single quotes
		if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
		{
			$sql .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
		}
		$sql .= 'NOT NULL';

		if (isset($column_data[2]))
		{
			if ($column_data[2] == 'auto_increment')
			{
				$sql .= ' auto_increment';
			}
			else if ($column_data[2] == 'true_sort')
			{
				$sql .= ' COLLATE utf8mb4_unicode_ci';
			}
		}

		$return_array['column_type_sql'] = $sql;

		return $return_array;
	}

	/**
	* Add new column
	*/
	function sql_column_add($table_name, $column_name, $column_data, $inline = false)
	{
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = [];

		$after = (!empty($column_data['after'])) ? ' AFTER ' . $column_data['after'] : '';
		$statements[] = 'ALTER TABLE `' . $table_name . '` ADD COLUMN `' . $column_name . '` ' . $column_data['column_type_sql'] . $after;

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop column
	*/
	function sql_column_remove($table_name, $column_name, $inline = false)
	{
		$statements = [];

		$statements[] = 'ALTER TABLE `' . $table_name . '` DROP COLUMN `' . $column_name . '`';

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop Index
	*/
	function sql_index_drop($table_name, $index_name)
	{
		$statements = [];

		$statements[] = 'DROP INDEX ' . $index_name . ' ON ' . $table_name;

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop Table
	*/
	function sql_table_drop($table_name)
	{
		$statements = [];

		if (!$this->sql_table_exists($table_name))
		{
			return $this->_sql_run_sql($statements);
		}

		$statements[] = 'DROP TABLE ' . $table_name;

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add primary key
	*/
	function sql_create_primary_key($table_name, $column, $inline = false)
	{
		$statements = [];

		$statements[] = 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(', ', $column) . ')';

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add unique index
	*/
	function sql_create_unique_index($table_name, $index_name, $column)
	{
		$statements = [];

		$table_prefix = substr(CONFIG_TABLE, 0, -6); // strlen(config)
		if (strlen($table_name . $index_name) - strlen($table_prefix) > 24)
		{
			$max_length = strlen($table_prefix) + 24;
			trigger_error("Index name '{$table_name}_$index_name' on table '$table_name' is too long. The maximum is $max_length characters.", E_USER_ERROR);
		}

		$statements[] = 'ALTER TABLE ' . $table_name . ' ADD UNIQUE INDEX ' . $index_name . '(' . implode(', ', $column) . ')';

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add index
	*/
	function sql_create_index($table_name, $index_name, $column)
	{
		$statements = [];

		$table_prefix = substr(CONFIG_TABLE, 0, -6); // strlen(config)
		if (strlen($table_name . $index_name) - strlen($table_prefix) > 24)
		{
			$max_length = strlen($table_prefix) + 24;
			trigger_error("Index name '{$table_name}_$index_name' on table '$table_name' is too long. The maximum is $max_length characters.", E_USER_ERROR);
		}

		// remove index length
		$column = preg_replace('#:.*$#', '', $column);

		$statements[] = 'ALTER TABLE ' . $table_name . ' ADD INDEX ' . $index_name . '(' . implode(', ', $column) . ')';

		return $this->_sql_run_sql($statements);
	}

	/**
	* List all of the indices that belong to a table,
	* does not count:
	* * UNIQUE indices
	* * PRIMARY keys
	*/
	function sql_list_index($table_name)
	{
		$index_array = [];

		$sql = 'SHOW KEYS FROM ' . $table_name;

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$row['Non_unique'])
			{
				continue;
			}

			$index_array[] = $row['Key_name'];
		}
		$this->db->sql_freeresult($result);

		return array_map('strtolower', $index_array);
	}

	/**
	* Change column type (not name!)
	*/
	function sql_column_change($table_name, $column_name, $column_data, $inline = false)
	{
		$column_data = $this->sql_prepare_column_data($table_name, $column_name, $column_data);
		$statements = [];

		$statements[] = 'ALTER TABLE `' . $table_name . '` CHANGE `' . $column_name . '` `' . $column_name . '` ' . $column_data['column_type_sql'];

		return $this->_sql_run_sql($statements);
	}
}
