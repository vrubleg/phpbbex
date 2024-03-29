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
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_MODULE_MANAGEMENT_EXPLAIN'	=> 'Here you are able to manage all kind of modules. Please note that the ACP has a three-level menu structure (Category -> Category -> Module) whereby the others having a two-level menu structure (Category -> Module) which must be kept. Please also be aware that you may lock out yourself if you disable or delete the modules responsible for the module management itself.',
	'ADD_MODULE'					=> 'Add module',
	'ADD_MODULE_CONFIRM'			=> 'Are you sure you want to add the selected module with the selected mode?',
	'ADD_MODULE_TITLE'				=> 'Add module',

	'CANNOT_REMOVE_MODULE'	=> 'Unable to remove module, it has assigned children. Please remove or move all children before performing this action.',
	'CATEGORY'				=> 'Category',
	'CHOOSE_MODE'			=> 'Choose module mode',
	'CHOOSE_MODE_EXPLAIN'	=> 'Choose the modules mode being used.',
	'CHOOSE_MODULE'			=> 'Choose module',
	'CHOOSE_MODULE_EXPLAIN'	=> 'Choose the file being called by this module.',
	'CREATE_MODULE'			=> 'Create new module',

	'DEACTIVATED_MODULE'	=> 'Deactivated module',
	'DELETE_MODULE'			=> 'Delete module',
	'DELETE_MODULE_CONFIRM'	=> 'Are you sure you want to remove this module?',

	'EDIT_MODULE'			=> 'Edit module',
	'EDIT_MODULE_EXPLAIN'	=> 'Here you are able to enter module specific settings.',

	'HIDDEN_MODULE'			=> 'Hidden module',

	'MODULE'					=> 'Module',
	'MODULE_ADDED'				=> 'Module successfully added.',
	'MODULE_DELETED'			=> 'Module successfully removed.',
	'MODULE_DISPLAYED'			=> 'Module displayed',
	'MODULE_DISPLAYED_EXPLAIN'	=> 'If you do not wish to display this module, but want to use it, set this to no.',
	'MODULE_EDITED'				=> 'Module successfully edited.',
	'MODULE_ENABLED'			=> 'Module enabled',
	'MODULE_LANGNAME'			=> 'Module language name',
	'MODULE_LANGNAME_EXPLAIN'	=> 'Enter the displayed module name. Use language constant if name is served from language file.',
	'MODULE_TYPE'				=> 'Module type',

	'NO_CATEGORY_TO_MODULE'	=> 'Unable to turn category into module. Please remove/move all children before performing this action.',
	'NO_MODULE'				=> 'No module found.',
	'NO_MODULE_ID'			=> 'No module id specified.',
	'NO_MODULE_LANGNAME'	=> 'No module language name specified.',
	'NO_PARENT'				=> 'No Parent',

	'PARENT'				=> 'Parent',
	'PARENT_NO_EXIST'		=> 'Parent does not exist.',

	'SELECT_MODULE'			=> 'Select a module',
));
