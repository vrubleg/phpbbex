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

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'ERROR_QUERY'					=> 'Query containing the error',

	'NO_RESULTS'					=> 'No Results',
	'NO_SQL_QUERY'					=> 'You must enter a query to run.',

	'QUERY_RESULT'					=> 'Query results',

	'SHOW_RESULTS'					=> 'Show Results',
	'SQL_QUERY'						=> 'Run SQL Query',
	'SQL_QUERY_EXPLAIN'				=> 'Enter the SQL query you wish to run. The tool will substitute "phpbb_" with your table prefix.<br />If the "Show Results" checkbox is checked the tool will display the results <em>(if any)</em> of the query.',

	'SQL_QUERY_LEGEND'				=> 'SQL Query',
	'SQL_QUERY_SUCCESS'				=> 'The SQL query has been run successfully.',
]);
