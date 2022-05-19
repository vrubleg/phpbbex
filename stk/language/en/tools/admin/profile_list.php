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
	'ALL'					=> 'All',

	'CLICK_TO_DELETE'		=> 'Delete all selected users by clicking on this button. <em>(Can’t be undone!)</em>',

	'FILTER'				=> 'Filter',

	'LIMIT'					=> 'Limit',

	'ONLY_NON_EMPTY'		=> 'Only Non-Empty',
	'ORDER_BY'				=> 'Order By',

	'PROFILE_LIST'			=> 'Profile List',
	'PROFILE_LIST_EXPLAIN'	=> 'This tool displays profile information for multiple users. It may also be used to aid in identifying spam accounts.',

	'USERS_DELETE'				=> 'Delete selected users',
	'USERS_DELETE_CONFIRM'		=> 'Are you sure that you want to delete the selected users? Deleting users through this tool <strong>will</strong> remove all their posts as well!',
	'USERS_DELETE_SUCCESSFULL'	=> 'All selected users where deleted successfully!',
));
