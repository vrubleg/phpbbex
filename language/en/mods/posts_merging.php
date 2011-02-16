<?php
/**
*
* posts_merging [English]
*
* @package language
* @version $Id: posts_merging.php,v 1.01 2007/10/16 17:19:50 rxu Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(

	'MERGE_SEPARATOR'		=> "\n\n[size=85][color=gray]%s after %s:[/color][/size]\n",
	'MERGE_SUBJECT'			=> "[size=85][color=gray]%s[/color][/size]\n",

// Time delta
	'D_SECONDS'  => array('second', 'seconds', 'seconds'),
	'D_MINUTES'  => array('minute', 'minutes', 'minutes'),
	'D_HOURS'    => array('hour', 'hours', 'hours'),
	'D_MDAY'     => array('day', 'days', 'days'),
	'D_MON'      => array('month', 'months', 'months'),
	'D_YEAR'     => array('year', 'yaers', 'years'),
// ACP block
	'MERGE_INTERVAL'				=> 'Merging posts interval',
	'MERGE_INTERVAL_EXPLAIN'		=> 'Number of hours a messages from the user will be merged with his topic last message. Leave empty or 0 to disable merging.',
	'MERGE_NO_TOPICS'				=> 'Topics without merging',
	'MERGE_NO_TOPICS_EXPLAIN'		=> 'Set comma separated list of topics\'IDs where posts merging will be disabled.',
	'MERGE_NO_FORUMS'				=> 'Forums without merging',
	'MERGE_NO_FORUMS_EXPLAIN'		=> 'Set comma separated list of forums\'IDs where posts merging will be disabled.',
));
?>