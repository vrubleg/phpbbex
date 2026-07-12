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
	'ACP_STYLES_EXPLAIN'    => 'Here you can manage the available styles on your board. A style consists of a template, theme and imageset. The current default style is noted by the presence of an asterisk (*). Also listed is the total user count for each style. Note that overriding user styles will not be reflected here.',
	'ADD_STYLE'             => 'Create style',
	'ADD_STYLE_EXPLAIN'     => 'Here you can create a new style based on components of existing styles in the styles directory.',

	'COPYRIGHT'                     => 'Copyright',
	'CREATE_STYLE'                  => 'Create new style',

	'DEACTIVATE_DEFAULT'        => 'You cannot deactivate the default style.',
	'DELETE_STYLE'              => 'Delete style',
	'DELETE_STYLE_EXPLAIN'      => 'Here you can remove the selected style. Take care in deleting styles, there is no undo capability.',
	'DETAILS'                   => 'Details',

	'EDIT_DETAILS_STYLE'                => 'Edit style',
	'EDIT_DETAILS_STYLE_EXPLAIN'        => 'Using the form below you can modify this existing style. You may alter the combination of template, theme and imageset which define the style itself. You may also make the style the default one.',

	'INACTIVE_STYLES'           => 'Inactive styles',
	'INHERITING_FROM'           => 'Inherits from',
	'INSTALL_STYLE'             => 'Install style',
	'INSTALL_STYLE_EXPLAIN'     => 'Here you can install a new style and if appropriate the corresponding style elements. If you already have the relevant style elements installed they will not be overwritten. Some styles require existing style elements to already be installed. If you try installing such a style and do not have the required elements you will be notified.',
	'INSTALLED_STYLE'           => 'Installed styles',

	'NO_STYLE'                  => 'Cannot find style on filesystem.',
	'NO_UNINSTALLED_STYLE'      => 'No uninstalled styles detected.',

	'ONLY_STYLE'            => 'This is the only remaining style, you cannot delete it.',

	'REPLACE_STYLE'             => 'Replace style with',
	'REPLACE_STYLE_EXPLAIN'     => 'This style will replace the one being deleted for members that use it.',
	'REPLACE_WITH_OPTION'       => 'Replace with “%s”',

	'STYLE_ACTIVATE'            => 'Activate',
	'STYLE_ACTIVE'              => 'Active',
	'STYLE_ADDED'               => 'Style added successfully.',
	'STYLE_DEACTIVATE'          => 'Deactivate',
	'STYLE_DEFAULT'             => 'Make default style',
	'STYLE_DELETED'             => 'Style deleted successfully.',
	'STYLE_DETAILS_UPDATED'     => 'Style edited successfully.',
	'STYLE_ERR_NAME_EXIST'      => 'A style with that name already exists.',
	'STYLE_ERR_NAME_LONG'       => 'The style name can be no longer than 30 characters.',
	'STYLE_ERR_NO_IDS'          => 'You must select a template, theme and imageset for this style.',
	'STYLE_ERR_NOT_STYLE'       => 'The imported or uploaded file did not contain a valid style archive.',
	'STYLE_ERR_STYLE_NAME'      => 'You must supply a name for this style.',
	'STYLE_ERR_VERSION'         => 'The style version must match phpBBex %1$s. Found version: %2$s.',
	'STYLE_IMAGESET'            => 'Imageset',
	'STYLE_NAME'                => 'Style name',
	'STYLE_TEMPLATE'            => 'Template',
	'STYLE_THEME'               => 'Theme',
	'STYLE_USED_BY'             => 'Used by',

	'TEMPLATE_ERR_REQUIRED_OR_INCOMPLETE' => 'The new template set requires the template %s to be installed and not inheriting itself.',

	'UNINSTALLED_STYLE'     => 'Uninstalled styles',
]);
