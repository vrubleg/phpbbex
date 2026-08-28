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

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'ACP_SEARCH_SETTINGS_EXPLAIN'           => 'Here you can configure search and the limits applied to search requests.',

	'CREATE_INDEX'                          => 'Create index',

	'DELETE_INDEX'                          => 'Delete index',

	'FULLTEXT_MYSQL_INDEX'                  => 'MySQL fulltext index',
	'FULLTEXT_MYSQL_INDEXED_POSTS'          => '%s posts',
	'FULLTEXT_MYSQL_NOT_SUPPORTED'          => 'MySQL 5.6.4 or later is required for a fulltext index on InnoDB tables.',
	'FULLTEXT_MYSQL_STATUS'                 => 'Status',
	'FULLTEXT_MYSQL_STATUS_EXPLAIN'         => 'Keyword search is unavailable when the index has not been created.',
	'FULLTEXT_MYSQL_STATUS_INDEXED'         => 'Indexed',
	'FULLTEXT_MYSQL_STATUS_MISSING'         => 'Missing',
	'FULLTEXT_MYSQL_MIN_SEARCH_CHARS_EXPLAIN'   => 'Words with at least this many characters will be indexed for searching. You or your host can only change this setting by changing the mysql configuration.',
	'FULLTEXT_MYSQL_MAX_SEARCH_CHARS_EXPLAIN'   => 'Words with no more than this many characters will be indexed for searching. You or your host can only change this setting by changing the mysql configuration.',

	'GENERAL_SEARCH_SETTINGS'               => 'General search settings',

	'MAX_SEARCH_CHARS'                      => 'Max characters indexed by search',
	'MAX_NUM_SEARCH_KEYWORDS'               => 'Maximum number of allowed keywords',
	'MAX_NUM_SEARCH_KEYWORDS_EXPLAIN'       => 'Maximum number of words the user is able to search for. A value of 0 allows an unlimited number of words.',
	'MIN_SEARCH_CHARS'                      => 'Min characters indexed by search',
	'MIN_SEARCH_AUTHOR_CHARS'               => 'Min author name characters',
	'MIN_SEARCH_AUTHOR_CHARS_EXPLAIN'       => 'Users have to enter at least this many characters of the name when performing a wildcard author search. If the author’s username is shorter than this number you can still search for the author’s posts by entering the complete username.',

	'SEARCH_GUEST_INTERVAL'                 => 'Guest search flood interval',
	'SEARCH_GUEST_INTERVAL_EXPLAIN'         => 'Number of seconds guests must wait between searches. If one guest searches all others have to wait until the time interval passed.',
	'SEARCH_INDEX_CREATED'                  => 'Successfully indexed all posts in the board database.',
	'SEARCH_INDEX_REMOVED'                  => 'Successfully deleted the search index.',
	'SEARCH_INTERVAL'                       => 'User search flood interval',
	'SEARCH_INTERVAL_EXPLAIN'               => 'Number of seconds users must wait between searches. This interval is checked independently for each user.',
	'SEARCH_STORE_RESULTS'                  => 'Search result cache length',
	'SEARCH_STORE_RESULTS_EXPLAIN'          => 'Cached search results will expire after this time, in seconds. Set to 0 if you want to disable search cache.',

	'YES_SEARCH'                            => 'Enable search facilities',
	'YES_SEARCH_EXPLAIN'                    => 'Enables user facing search functionality including member search.',

	'DEFAULT_SEARCH_TITLEONLY'              => 'Search only in topic titles by default',
	'DEFAULT_SEARCH_TITLEONLY_EXPLAIN'      => 'Affects the global search and the search for a specific forum.',

	'SEARCH_HIGHLIGHT_KEYWORDS'             => 'Highlight keywords',
	'SEARCH_HIGHLIGHT_KEYWORDS_EXPLAIN'     => 'Creates links to viewtopic.php with hilit argument for found topics.',
]);
