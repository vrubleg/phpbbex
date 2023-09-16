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
	* Current sql layer
	*/
	var $sql_layer = '';

	/**
	* @var object DB object
	*/
	var $db = NULL;

	/**
	* The Column types for every database we support
	* @var array
	*/
	var $dbms_type_map = array(
		'mysql'	=> array(
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
		),

		'sqlite'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'INTEGER UNSIGNED', //'mediumint(8) UNSIGNED',
			'UINT:'		=> 'INTEGER UNSIGNED', // 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'INTEGER UNSIGNED', //'mediumint(4) UNSIGNED',
			'BOOL'		=> 'INTEGER UNSIGNED', //'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'text(65535)',
			'STEXT'		=> 'text(65535)',
			'TEXT'		=> 'text(65535)',
			'MTEXT'		=> 'mediumtext(16777215)',
			'XSTEXT_UNI'=> 'text(65535)',
			'STEXT_UNI'	=> 'text(65535)',
			'TEXT_UNI'	=> 'text(65535)',
			'MTEXT_UNI'	=> 'mediumtext(16777215)',
			'TIMESTAMP'	=> 'INTEGER UNSIGNED', //'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar(255)',
			'VARBINARY'	=> 'blob',
		),

		'postgres'	=> array(
			'INT:'		=> 'INT4',
			'BINT'		=> 'INT8',
			'UINT'		=> 'INT4', // unsigned
			'UINT:'		=> 'INT4', // unsigned
			'USINT'		=> 'INT2', // unsigned
			'BOOL'		=> 'INT2', // unsigned
			'TINT:'		=> 'INT2',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'varchar(1000)',
			'STEXT'		=> 'varchar(3000)',
			'TEXT'		=> 'varchar(8000)',
			'MTEXT'		=> 'TEXT',
			'XSTEXT_UNI'=> 'varchar(100)',
			'STEXT_UNI'	=> 'varchar(255)',
			'TEXT_UNI'	=> 'varchar(4000)',
			'MTEXT_UNI'	=> 'TEXT',
			'TIMESTAMP'	=> 'INT4', // unsigned
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar_ci',
			'VARBINARY'	=> 'bytea',
		),
	);

	/**
	* A list of types being unsigned for better reference in some db's
	* @var array
	*/
	var $unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');

	/**
	* A list of supported DBMS. We change this class to support more DBMS, the DBMS itself only need to follow some rules.
	* @var array
	*/
	var $supported_dbms = array('mysql', 'postgres', 'sqlite');

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
		$this->sql_layer = $this->db->sql_layer;
	}

	/**
	* Gets a list of tables in the database.
	*
	* @return array		Array of table names  (all lower case)
	*/
	function sql_list_tables()
	{
		switch ($this->db->sql_layer)
		{
			case 'mysql':
				$sql = 'SHOW TABLES';
			break;

			case 'sqlite':
				$sql = 'SELECT name
					FROM sqlite_master
					WHERE type = "table"';
			break;

			case 'postgres':
				$sql = 'SELECT relname
					FROM pg_stat_user_tables';
			break;
		}

		$result = $this->db->sql_query($sql);

		$tables = array();
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
		$columns = $statements = array();

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
					$table_data['PRIMARY_KEY'] = array($table_data['PRIMARY_KEY']);
				}

				$table_sql .= ",\n\t PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ')';
			}
		}

		// close the table
		switch ($this->sql_layer)
		{
			case 'mysql':
				// make sure the table is in UTF-8 mode
				$table_sql .= "\n) CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`;";
				$statements[] = $table_sql;
			break;

			case 'sqlite':
				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;

			case 'postgres':
				// do we need to add a sequence for auto incrementing columns?
				if ($create_sequence)
				{
					$statements[] = "CREATE SEQUENCE {$table_name}_seq;";
				}

				$table_sql .= "\n);";
				$statements[] = $table_sql;
			break;
		}

		// Write Keys
		if (isset($table_data['KEYS']))
		{
			foreach ($table_data['KEYS'] as $key_name => $key_data)
			{
				if (!is_array($key_data[1]))
				{
					$key_data[1] = array($key_data[1]);
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

		$statements = array();
		$sqlite = false;

		// For SQLite we need to perform the schema changes in a much more different way
		if ($this->db->sql_layer == 'sqlite' && $this->return_statements)
		{
			$sqlite_data = array();
			$sqlite = true;
		}

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

					if ($sqlite)
					{
						if ($column_exists)
						{
							$sqlite_data[$table]['change_columns'][] = $result;
						}
						else
						{
							$sqlite_data[$table]['add_columns'][] = $result;
						}
					}
					else if ($this->return_statements)
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

					if ($sqlite)
					{
						if ($column_exists)
						{
							continue;
//							$sqlite_data[$table]['change_columns'][] = $result;
						}
						else
						{
							$sqlite_data[$table]['add_columns'][] = $result;
						}
					}
					else if ($this->return_statements)
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

						if ($sqlite)
						{
							$sqlite_data[$table]['drop_columns'][] = $result;
						}
						else if ($this->return_statements)
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

				if ($sqlite)
				{
					$sqlite_data[$table]['primary_key'] = $result;
				}
				else if ($this->return_statements)
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

		if ($sqlite)
		{
			foreach ($sqlite_data as $table_name => $sql_schema_changes)
			{
				// Create temporary table with original data
				$statements[] = 'begin';

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					continue;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				// Create a backup table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				// Get the columns...
				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$plain_table_cols = trim($matches[1]);
				$new_table_cols = preg_split('/,(?![\s\w]+\))/m', $plain_table_cols);
				$column_list = array();

				foreach ($new_table_cols as $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						continue;
					}
					$column_list[] = $entities[0];
				}

				// note down the primary key notation because sqlite only supports adding it to the end for the new table
				$primary_key = false;
				$_new_cols = array();

				foreach ($new_table_cols as $key => $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						$primary_key = $declaration;
						continue;
					}
					$_new_cols[] = $declaration;
				}

				$new_table_cols = $_new_cols;

				// First of all... change columns
				if (!empty($sql_schema_changes['change_columns']))
				{
					foreach ($sql_schema_changes['change_columns'] as $column_sql)
					{
						foreach ($new_table_cols as $key => $declaration)
						{
							$entities = preg_split('#\s+#', trim($declaration));
							if (strpos($column_sql, $entities[0] . ' ') === 0)
							{
								$new_table_cols[$key] = $column_sql;
							}
						}
					}
				}

				if (!empty($sql_schema_changes['add_columns']))
				{
					foreach ($sql_schema_changes['add_columns'] as $column_sql)
					{
						$new_table_cols[] = $column_sql;
					}
				}

				// Now drop them...
				if (!empty($sql_schema_changes['drop_columns']))
				{
					foreach ($sql_schema_changes['drop_columns'] as $column_name)
					{
						// Remove from column list...
						$new_column_list = array();
						foreach ($column_list as $key => $value)
						{
							if ($value === $column_name)
							{
								continue;
							}

							$new_column_list[] = $value;
						}

						$column_list = $new_column_list;

						// Remove from table...
						$_new_cols = array();
						foreach ($new_table_cols as $key => $declaration)
						{
							$entities = preg_split('#\s+#', trim($declaration));
							if (strpos($column_name . ' ', $entities[0] . ' ') === 0)
							{
								continue;
							}
							$_new_cols[] = $declaration;
						}
						$new_table_cols = $_new_cols;
					}
				}

				// Primary key...
				if (!empty($sql_schema_changes['primary_key']))
				{
					$new_table_cols[] = 'PRIMARY KEY (' . implode(', ', $sql_schema_changes['primary_key']) . ')';
				}
				// Add a new one or the old primary key
				else if ($primary_key !== false)
				{
					$new_table_cols[] = $primary_key;
				}

				$columns = implode(',', $column_list);

				// create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . implode(',', $new_table_cols) . ');';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';
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
		$columns = array();

		switch ($this->sql_layer)
		{
			case 'mysql':
				$sql = "SHOW COLUMNS FROM $table";
			break;

			// PostgreSQL has a way of doing this in a much simpler way but would
			// not allow us to support all versions of PostgreSQL
			case 'postgres':
				$sql = "SELECT a.attname
					FROM pg_class c, pg_attribute a
					WHERE c.relname = '{$table}'
						AND a.attnum > 0
						AND a.attrelid = c.oid";
			break;

			case 'sqlite':
				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table}'";

				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					return false;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$cols = trim($matches[1]);
				$col_array = preg_split('/,(?![\s\w]+\))/m', $cols);

				foreach ($col_array as $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						continue;
					}

					$column = strtolower($entities[0]);
					$columns[$column] = $column;
				}

				return $columns;
			break;
		}

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
		switch ($this->sql_layer)
		{
			case 'postgres':
				$sql = "SELECT ic.relname as index_name
					FROM pg_class bc, pg_class ic, pg_index i
					WHERE (bc.oid = i.indrelid)
						AND (ic.oid = i.indexrelid)
						AND (bc.relname = '" . $table_name . "')
						AND (i.indisunique != 't')
						AND (i.indisprimary != 't')";
				$col = 'index_name';
			break;

			case 'mysql':
				$sql = 'SHOW KEYS
					FROM ' . $table_name;
				$col = 'Key_name';
			break;

			case 'sqlite':
				$sql = "PRAGMA index_list('" . $table_name . "');";
				$col = 'name';
			break;
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($this->sql_layer == 'mysql' && !$row['Non_unique'])
			{
				continue;
			}

			// These DBMS prefix index name with the table name
			switch ($this->sql_layer)
			{
				case 'postgres':
				case 'sqlite':
					$row[$col] = substr($row[$col], strlen($table_name) + 1);
				break;
			}

			if (strtolower($row[$col]) == strtolower($index_name))
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
		switch ($this->sql_layer)
		{
			case 'postgres':
				$sql = "SELECT ic.relname as index_name, i.indisunique
					FROM pg_class bc, pg_class ic, pg_index i
					WHERE (bc.oid = i.indrelid)
						AND (ic.oid = i.indexrelid)
						AND (bc.relname = '" . $table_name . "')
						AND (i.indisprimary != 't')";
				$col = 'index_name';
			break;

			case 'mysql':
				$sql = 'SHOW KEYS
					FROM ' . $table_name;
				$col = 'Key_name';
			break;

			case 'sqlite':
				$sql = "PRAGMA index_list('" . $table_name . "');";
				$col = 'name';
			break;
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($this->sql_layer == 'mysql' && ($row['Non_unique'] || $row[$col] == 'PRIMARY'))
			{
				continue;
			}

			if ($this->sql_layer == 'sqlite' && !$row['unique'])
			{
				continue;
			}

			if ($this->sql_layer == 'postgres' && $row['indisunique'] != 't')
			{
				continue;
			}

			// These DBMS prefix index name with the table name
			switch ($this->sql_layer)
			{
				case 'postgres':
				case 'sqlite':
					$row[$col] = substr($row[$col], strlen($table_name) + 1);
				break;
			}

			if (strtolower($row[$col]) == strtolower($index_name))
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
			if (!is_array($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']))
			{
				$column_type = sprintf($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':'], $column_length);
			}
			else
			{
				if (isset($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['rule']))
				{
					switch ($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['rule'][0])
					{
						case 'div':
							$column_length /= $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['rule'][1];
							$column_length = ceil($column_length);
							$column_type = sprintf($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':'][0], $column_length);
						break;
					}
				}

				if (isset($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit']))
				{
					switch ($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][0])
					{
						case 'mult':
							$column_length *= $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][1];
							if ($column_length > $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][2])
							{
								$column_type = $this->dbms_type_map[$this->sql_layer][$orig_column_type . ':']['limit'][3];
							}
							else
							{
								$column_type = sprintf($this->dbms_type_map[$this->sql_layer][$orig_column_type . ':'][0], $column_length);
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
			$column_type = $this->dbms_type_map[$this->sql_layer][$column_data[0]];
		}

		// Adjust default value if db-dependant specified
		if (is_array($column_data[1]))
		{
			$column_data[1] = (isset($column_data[1][$this->sql_layer])) ? $column_data[1][$this->sql_layer] : $column_data[1]['default'];
		}

		$sql = '';

		$return_array = array();

		switch ($this->sql_layer)
		{
			case 'mysql':
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
					else if ($this->sql_layer === 'mysql' && $column_data[2] == 'true_sort')
					{
						$sql .= ' COLLATE utf8mb4_unicode_ci';
					}
				}

			break;

			case 'postgres':
				$return_array['column_type'] = $column_type;

				$sql .= " {$column_type} ";

				$return_array['auto_increment'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$default_val = "nextval('{$table_name}_seq')";
					$return_array['auto_increment'] = true;
				}
				else if (!is_null($column_data[1]))
				{
					$default_val = "'" . $column_data[1] . "'";
					$return_array['null'] = 'NOT NULL';
					$sql .= 'NOT NULL ';
				}

				$return_array['default'] = $default_val;

				$sql .= "DEFAULT {$default_val}";

				// Unsigned? Then add a CHECK contraint
				if (in_array($orig_column_type, $this->unsigned_types))
				{
					$return_array['constraint'] = "CHECK ({$column_name} >= 0)";
					$sql .= " CHECK ({$column_name} >= 0)";
				}

			break;

			case 'sqlite':
				$return_array['primary_key_set'] = false;
				if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
				{
					$sql .= ' INTEGER PRIMARY KEY';
					$return_array['primary_key_set'] = true;
				}
				else
				{
					$sql .= ' ' . $column_type;
				}

				$sql .= ' NOT NULL ';
				$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}'" : '';

			break;
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
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'mysql':
				$after = (!empty($column_data['after'])) ? ' AFTER ' . $column_data['after'] : '';
				$statements[] = 'ALTER TABLE `' . $table_name . '` ADD COLUMN `' . $column_name . '` ' . $column_data['column_type_sql'] . $after;
			break;

			case 'postgres':
				// Does not support AFTER, only through temporary table
				if (version_compare($this->db->sql_server_info(true), '8.0', '>='))
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' ADD COLUMN "' . $column_name . '" ' . $column_data['column_type_sql'];
				}
				else
				{
					// old versions cannot add columns with default and null information
					$statements[] = 'ALTER TABLE ' . $table_name . ' ADD COLUMN "' . $column_name . '" ' . $column_data['column_type'] . ' ' . $column_data['constraint'];

					if (isset($column_data['null']))
					{
						if ($column_data['null'] == 'NOT NULL')
						{
							$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN ' . $column_name . ' SET NOT NULL';
						}
					}

					if (isset($column_data['default']))
					{
						$statements[] = 'ALTER TABLE ' . $table_name . ' ALTER COLUMN ' . $column_name . ' SET DEFAULT ' . $column_data['default'];
					}
				}

			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column_name . ' ' . $column_data['column_type_sql'];
				}

				if (version_compare(sqlite_libversion(), '3.0') == -1)
				{
					$sql = "SELECT sql
						FROM sqlite_master
						WHERE type = 'table'
							AND name = '{$table_name}'
						ORDER BY type DESC, name;";
					$result = $this->db->sql_query($sql);

					if (!$result)
					{
						break;
					}

					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$statements[] = 'begin';

					// Create a backup table and populate it, destroy the existing one
					$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
					$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
					$statements[] = 'DROP TABLE ' . $table_name;

					preg_match('#\((.*)\)#s', $row['sql'], $matches);

					$new_table_cols = trim($matches[1]);
					$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
					$column_list = array();

					foreach ($old_table_cols as $declaration)
					{
						$entities = preg_split('#\s+#', trim($declaration));
						if ($entities[0] == 'PRIMARY')
						{
							continue;
						}
						$column_list[] = $entities[0];
					}

					$columns = implode(',', $column_list);

					$new_table_cols = $column_name . ' ' . $column_data['column_type_sql'] . ',' . $new_table_cols;

					// create a new table and fill it up. destroy the temp one
					$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');';
					$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
					$statements[] = 'DROP TABLE ' . $table_name . '_temp';

					$statements[] = 'commit';
				}
				else
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' ADD ' . $column_name . ' [' . $column_data['column_type_sql'] . ']';
				}
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop column
	*/
	function sql_column_remove($table_name, $column_name, $inline = false)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'mysql':
				$statements[] = 'ALTER TABLE `' . $table_name . '` DROP COLUMN `' . $column_name . '`';
			break;

			case 'postgres':
				$statements[] = 'ALTER TABLE ' . $table_name . ' DROP COLUMN "' . $column_name . '"';
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column_name;
				}

				if (version_compare(sqlite_libversion(), '3.0') == -1)
				{
					$sql = "SELECT sql
						FROM sqlite_master
						WHERE type = 'table'
							AND name = '{$table_name}'
						ORDER BY type DESC, name;";
					$result = $this->db->sql_query($sql);

					if (!$result)
					{
						break;
					}

					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$statements[] = 'begin';

					// Create a backup table and populate it, destroy the existing one
					$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
					$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
					$statements[] = 'DROP TABLE ' . $table_name;

					preg_match('#\((.*)\)#s', $row['sql'], $matches);

					$new_table_cols = trim($matches[1]);
					$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
					$column_list = array();

					foreach ($old_table_cols as $declaration)
					{
						$entities = preg_split('#\s+#', trim($declaration));
						if ($entities[0] == 'PRIMARY' || $entities[0] === $column_name)
						{
							continue;
						}
						$column_list[] = $entities[0];
					}

					$columns = implode(',', $column_list);

					$new_table_cols = preg_replace('/' . $column_name . '[^,]+(?:,|$)/m', '', $new_table_cols);

					// create a new table and fill it up. destroy the temp one
					$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ');';
					$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
					$statements[] = 'DROP TABLE ' . $table_name . '_temp';

					$statements[] = 'commit';
				}
				else
				{
					$statements[] = 'ALTER TABLE ' . $table_name . ' DROP COLUMN ' . $column_name;
				}
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop Index
	*/
	function sql_index_drop($table_name, $index_name)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'mysql':
				$statements[] = 'DROP INDEX ' . $index_name . ' ON ' . $table_name;
			break;

			case 'postgres':
			case 'sqlite':
				$statements[] = 'DROP INDEX ' . $table_name . '_' . $index_name;
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Drop Table
	*/
	function sql_table_drop($table_name)
	{
		$statements = array();

		if (!$this->sql_table_exists($table_name))
		{
			return $this->_sql_run_sql($statements);
		}

		// the most basic operation, get rid of the table
		$statements[] = 'DROP TABLE ' . $table_name;

		switch ($this->sql_layer)
		{
			case 'postgres':
				// PGSQL does not "tightly" bind sequences and tables, we must guess...
				$sql = "SELECT relname
					FROM pg_class
					WHERE relkind = 'S'
						AND relname = '{$table_name}_seq'";
				$result = $this->db->sql_query($sql);

				// We don't even care about storing the results. We already know the answer if we get rows back.
				if ($this->db->sql_fetchrow($result))
				{
					$statements[] =  "DROP SEQUENCE {$table_name}_seq;\n";
				}
				$this->db->sql_freeresult($result);
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add primary key
	*/
	function sql_create_primary_key($table_name, $column, $inline = false)
	{
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'postgres':
			case 'mysql':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(', ', $column) . ')';
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column;
				}

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$statements[] = 'begin';

				// Create a backup table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$new_table_cols = trim($matches[1]);
				$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
				$column_list = array();

				foreach ($old_table_cols as $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					if ($entities[0] == 'PRIMARY')
					{
						continue;
					}
					$column_list[] = $entities[0];
				}

				$columns = implode(',', $column_list);

				// create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . $new_table_cols . ', PRIMARY KEY (' . implode(', ', $column) . '));';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add unique index
	*/
	function sql_create_unique_index($table_name, $index_name, $column)
	{
		$statements = array();

		$table_prefix = substr(CONFIG_TABLE, 0, -6); // strlen(config)
		if (strlen($table_name . $index_name) - strlen($table_prefix) > 24)
		{
			$max_length = strlen($table_prefix) + 24;
			trigger_error("Index name '{$table_name}_$index_name' on table '$table_name' is too long. The maximum is $max_length characters.", E_USER_ERROR);
		}

		switch ($this->sql_layer)
		{
			case 'postgres':
			case 'sqlite':
				$statements[] = 'CREATE UNIQUE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mysql':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD UNIQUE INDEX ' . $index_name . '(' . implode(', ', $column) . ')';
			break;
		}

		return $this->_sql_run_sql($statements);
	}

	/**
	* Add index
	*/
	function sql_create_index($table_name, $index_name, $column)
	{
		$statements = array();

		$table_prefix = substr(CONFIG_TABLE, 0, -6); // strlen(config)
		if (strlen($table_name . $index_name) - strlen($table_prefix) > 24)
		{
			$max_length = strlen($table_prefix) + 24;
			trigger_error("Index name '{$table_name}_$index_name' on table '$table_name' is too long. The maximum is $max_length characters.", E_USER_ERROR);
		}

		// remove index length
		$column = preg_replace('#:.*$#', '', $column);

		switch ($this->sql_layer)
		{
			case 'postgres':
			case 'sqlite':
				$statements[] = 'CREATE INDEX ' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . implode(', ', $column) . ')';
			break;

			case 'mysql':
				$statements[] = 'ALTER TABLE ' . $table_name . ' ADD INDEX ' . $index_name . '(' . implode(', ', $column) . ')';
			break;
		}

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
		$index_array = array();

		switch ($this->sql_layer)
		{
			case 'postgres':
				$sql = "SELECT ic.relname as index_name
					FROM pg_class bc, pg_class ic, pg_index i
					WHERE (bc.oid = i.indrelid)
						AND (ic.oid = i.indexrelid)
						AND (bc.relname = '" . $table_name . "')
						AND (i.indisunique != 't')
						AND (i.indisprimary != 't')";
				$col = 'index_name';
			break;

			case 'mysql':
				$sql = 'SHOW KEYS
					FROM ' . $table_name;
				$col = 'Key_name';
			break;

			case 'sqlite':
				$sql = "PRAGMA index_info('" . $table_name . "');";
				$col = 'name';
			break;
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($this->sql_layer == 'mysql' && !$row['Non_unique'])
			{
				continue;
			}

			switch ($this->sql_layer)
			{
				case 'postgres':
				case 'sqlite':
					$row[$col] = substr($row[$col], strlen($table_name) + 1);
				break;
			}

			$index_array[] = $row[$col];
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
		$statements = array();

		switch ($this->sql_layer)
		{
			case 'mysql':
				$statements[] = 'ALTER TABLE `' . $table_name . '` CHANGE `' . $column_name . '` `' . $column_name . '` ' . $column_data['column_type_sql'];
			break;

			case 'postgres':
				$sql = 'ALTER TABLE ' . $table_name . ' ';

				$sql_array = array();
				$sql_array[] = 'ALTER COLUMN ' . $column_name . ' TYPE ' . $column_data['column_type'];

				if (isset($column_data['null']))
				{
					if ($column_data['null'] == 'NOT NULL')
					{
						$sql_array[] = 'ALTER COLUMN ' . $column_name . ' SET NOT NULL';
					}
					else if ($column_data['null'] == 'NULL')
					{
						$sql_array[] = 'ALTER COLUMN ' . $column_name . ' DROP NOT NULL';
					}
				}

				if (isset($column_data['default']))
				{
					$sql_array[] = 'ALTER COLUMN ' . $column_name . ' SET DEFAULT ' . $column_data['default'];
				}

				// we don't want to double up on constraints if we change different number data types
				if (isset($column_data['constraint']))
				{
					$constraint_sql = "SELECT consrc as constraint_data
								FROM pg_constraint, pg_class bc
								WHERE conrelid = bc.oid
									AND bc.relname = '{$table_name}'
									AND NOT EXISTS (
										SELECT *
											FROM pg_constraint as c, pg_inherits as i
											WHERE i.inhrelid = pg_constraint.conrelid
												AND c.conname = pg_constraint.conname
												AND c.consrc = pg_constraint.consrc
												AND c.conrelid = i.inhparent
									)";

					$constraint_exists = false;

					$result = $this->db->sql_query($constraint_sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						if (trim($row['constraint_data']) == trim($column_data['constraint']))
						{
							$constraint_exists = true;
							break;
						}
					}
					$this->db->sql_freeresult($result);

					if (!$constraint_exists)
					{
						$sql_array[] = 'ADD ' . $column_data['constraint'];
					}
				}

				$sql .= implode(', ', $sql_array);

				$statements[] = $sql;
			break;

			case 'sqlite':

				if ($inline && $this->return_statements)
				{
					return $column_name . ' ' . $column_data['column_type_sql'];
				}

				$sql = "SELECT sql
					FROM sqlite_master
					WHERE type = 'table'
						AND name = '{$table_name}'
					ORDER BY type DESC, name;";
				$result = $this->db->sql_query($sql);

				if (!$result)
				{
					break;
				}

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$statements[] = 'begin';

				// Create a temp table and populate it, destroy the existing one
				$statements[] = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', 'CREATE TEMPORARY TABLE ' . $table_name . '_temp', $row['sql']);
				$statements[] = 'INSERT INTO ' . $table_name . '_temp SELECT * FROM ' . $table_name;
				$statements[] = 'DROP TABLE ' . $table_name;

				preg_match('#\((.*)\)#s', $row['sql'], $matches);

				$new_table_cols = trim($matches[1]);
				$old_table_cols = preg_split('/,(?![\s\w]+\))/m', $new_table_cols);
				$column_list = array();

				foreach ($old_table_cols as $key => $declaration)
				{
					$entities = preg_split('#\s+#', trim($declaration));
					$column_list[] = $entities[0];
					if ($entities[0] == $column_name)
					{
						$old_table_cols[$key] = $column_name . ' ' . $column_data['column_type_sql'];
					}
				}

				$columns = implode(',', $column_list);

				// create a new table and fill it up. destroy the temp one
				$statements[] = 'CREATE TABLE ' . $table_name . ' (' . implode(',', $old_table_cols) . ');';
				$statements[] = 'INSERT INTO ' . $table_name . ' (' . $columns . ') SELECT ' . $columns . ' FROM ' . $table_name . '_temp;';
				$statements[] = 'DROP TABLE ' . $table_name . '_temp';

				$statements[] = 'commit';

			break;
		}

		return $this->_sql_run_sql($statements);
	}
}
