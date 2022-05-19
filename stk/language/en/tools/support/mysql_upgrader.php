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
	$lang = array();
}

$lang = array_merge($lang, array(
	'MYSQL_UPGRADER'					=> 'MySQL Upgrader',
	'MYSQL_UPGRADER_BACKUP'				=> 'This tool is potentially dangerous; make sure that you make a backup of your database before proceding!',
	'MYSQL_UPGRADER_EXPLAIN'			=> 'This tool is designed to resolve certain issues that are caused when the MySQL database used by your phpBB installation is upgraded. This upgrade will cause the database schema to become incompatible with the new version <em>See also the “<a href="http://www.phpbb.com/kb/article/doesnt-have-a-default-value-errors">Doesn\'t have a default value errors</a>” KB article.</em>',
	'MYSQL_UPGRADER_DOWNLOAD'			=> 'Download',
	'MYSQL_UPGRADER_DOWNLOAD_EXPLAIN'	=> 'By checking this option the MySQL Upgrader will generate the queries and display the output to you, from there you can download the result file.',
	'MYSQL_UPGRADER_RESULT'				=> 'Below are the queries that must be run to update the database schema to the correct MySQL version. Click <a href="%s">here</a> to download the queries in a .sql file.',
	'MYSQL_UPGRADER_RUN'				=> 'Run',
	'MYSQL_UPGRADER_RUN_EXPLAIN'		=> 'By checking this option the MySQL Upgrader will generate the queries and automatically run the result on your database.<br />This might take some time, do <strong>not</strong> interrupt this process until the tool notifies you that it\'s ready.',
	'MYSQL_UPGRADER_SCRIPT'				=> 'MySQL upgrader script',
	'MYSQL_UPGRADER_SUCCESSFULL'		=> 'The MySQL Upgrader has been run successfully',

	'QUERY_FINISHED'	=> 'Finished running query %1$d of %2$d, continuing to the next step.',

	'TOOL_MYSQL_ONLY'	=> 'This tool is only available for users of the MySQL DBMS',
));
