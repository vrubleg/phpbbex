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
	'ACP_IMAGESETS_EXPLAIN' => 'Imagesets comprise all the button, forum, folder, etc. and other non-style specific images used by the board. Here you can delete existing imagesets and import or activate new sets.',
	'ACP_STYLES_EXPLAIN'    => 'Here you can manage the available styles on your board. A style consists of a template, theme and imageset. You may alter existing styles, delete, deactivate, reactivate, create or import new ones. You can also see what a style will look like using the preview function. The current default style is noted by the presence of an asterisk (*). Also listed is the total user count for each style, note that overriding user styles will not be reflected here.',
	'ACP_TEMPLATES_EXPLAIN' => 'A template set comprises all the markup used to generate the layout of your board. Here you can edit existing template sets, delete, export, import and preview sets. You can also modify the templating code used to generate BBCode.',
	'ACP_THEMES_EXPLAIN'    => 'From here you can create, install, edit, delete and export themes. A theme is the combination of colours and images that are applied to your templates to define the basic look of your board. The range of options open to you depends on the configuration of your server and phpBBex installation, see the manual for further details. Please note that when creating new themes the use of an existing theme as a basis is optional.',
	'ADD_STYLE'             => 'Create style',
	'ADD_STYLE_EXPLAIN'     => 'Here you can create a new style. Depending on your server configuration and file permissions you may have additional options. For example you may be able to base this style on an existing one. You may also be able to upload or import (from the store directory) a style archive. If you upload or import an archive the style name will be determined automatically.',


	'CONFIRM_IMAGESET_REFRESH'      => 'Are you sure you wish to refresh cached imageset data?',
	'CONFIRM_TEMPLATE_CLEAR_CACHE'  => 'Are you sure you wish to clear all cached versions of your template files?',
	'CONFIRM_THEME_REFRESH'         => 'Are you sure you wish to refresh the theme stylesheet version? This will force browsers to reload the parsed stylesheet.',
	'COPYRIGHT'                     => 'Copyright',
	'CREATE_STYLE'                  => 'Create new style',

	'DEACTIVATE_DEFAULT'        => 'You cannot deactivate the default style.',
	'DELETE_IMAGESET'           => 'Delete imageset',
	'DELETE_IMAGESET_EXPLAIN'   => 'Here you can remove the selected imageset from the database. Please note that there is no undo capability.',
	'DELETE_STYLE'              => 'Delete style',
	'DELETE_STYLE_EXPLAIN'      => 'Here you can remove the selected style. Take care in deleting styles, there is no undo capability.',
	'DELETE_TEMPLATE'           => 'Delete template',
	'DELETE_TEMPLATE_EXPLAIN'   => 'Here you can remove the selected template set from the database. Please note that there is no undo capability. It is recommended that you first export your set for possible future use.',
	'DELETE_THEME'              => 'Delete theme',
	'DELETE_THEME_EXPLAIN'      => 'Here you can remove the selected theme from the database. Please note that there is no undo capability. It is recommended that you first export your theme for possible future use.',
	'DETAILS'                   => 'Details',


	'EDIT_DETAILS_STYLE'                => 'Edit style',
	'EDIT_DETAILS_STYLE_EXPLAIN'        => 'Using the form below you can modify this existing style. You may alter the combination of template, theme and imageset which define the style itself. You may also make the style the default one.',


	'IMAGESET_ADDED'            => 'New imageset added on filesystem.',
	'IMAGESET_DELETED'          => 'Imageset deleted successfully.',
	'IMAGESET_DELETED_FS'       => 'Imageset removed from database but some files may remain on the filesystem.',
	'IMAGESET_ERR_NAME_EXIST'   => 'An imageset with that directory already exists.',
	'IMAGESET_ERR_NAME_LONG'    => 'The imageset directory can be no longer than 100 characters.',
	'IMAGESET_ERR_NOT_IMAGESET' => 'The archive you specified does not contain a valid imageset.',
	'IMAGESET_ERR_STYLE_NAME'   => 'You must supply a directory for this imageset.',
	'IMAGESET_ERR_VERSION'      => 'The imageset version must match phpBBex %1$s. Found version: %2$s.',
	'IMAGESET_NAME'             => 'Imageset directory',
	'IMAGESET_REFRESHED'        => 'Imageset refreshed successfully.',


	'INACTIVE_STYLES'           => 'Inactive styles',
	'INHERITING_FROM'           => 'Inherits from',
	'INSTALL_IMAGESET'          => 'Install imageset',
	'INSTALL_IMAGESET_EXPLAIN'  => 'Here you can install your selected imageset. You can edit certain details if you wish or use the installation defaults.',
	'INSTALL_STYLE'             => 'Install style',
	'INSTALL_STYLE_EXPLAIN'     => 'Here you can install a new style and if appropriate the corresponding style elements. If you already have the relevant style elements installed they will not be overwritten. Some styles require existing style elements to already be installed. If you try installing such a style and do not have the required elements you will be notified.',
	'INSTALL_TEMPLATE'          => 'Install Template',
	'INSTALL_TEMPLATE_EXPLAIN'  => 'Here you can install a new template set. Depending on your server configuration you may have a number of options here.',
	'INSTALL_THEME'             => 'Install theme',
	'INSTALL_THEME_EXPLAIN'     => 'Here you can install your selected theme. You can edit certain details if you wish or use the installation defaults.',
	'INSTALLED_IMAGESET'        => 'Installed imagesets',
	'INSTALLED_STYLE'           => 'Installed styles',
	'INSTALLED_TEMPLATE'        => 'Installed templates',
	'INSTALLED_THEME'           => 'Installed themes',

	'KEEP_IMAGESET'             => 'Keep “%s” imageset',
	'KEEP_TEMPLATE'             => 'Keep “%s” template',
	'KEEP_THEME'                => 'Keep “%s” theme',


	'NO_IMAGESET'               => 'Cannot find imageset on filesystem.',
	'NO_STYLE'                  => 'Cannot find style on filesystem.',
	'NO_TEMPLATE'               => 'Cannot find template on filesystem.',
	'NO_THEME'                  => 'Cannot find theme on filesystem.',
	'NO_UNINSTALLED_IMAGESET'   => 'No uninstalled imagesets detected.',
	'NO_UNINSTALLED_STYLE'      => 'No uninstalled styles detected.',
	'NO_UNINSTALLED_TEMPLATE'   => 'No uninstalled templates detected.',
	'NO_UNINSTALLED_THEME'      => 'No uninstalled themes detected.',

	'ONLY_IMAGESET'         => 'This is the only remaining imageset, you cannot delete it.',
	'ONLY_STYLE'            => 'This is the only remaining style, you cannot delete it.',
	'ONLY_TEMPLATE'         => 'This is the only remaining template set, you cannot delete it.',
	'ONLY_THEME'            => 'This is the only remaining theme, you cannot delete it.',
	'OPTIONAL_BASIS'        => 'Optional basis',

	'REFRESH'                   => 'Refresh',
	'REPLACE_IMAGESET'          => 'Replace imageset with',
	'REPLACE_IMAGESET_EXPLAIN'  => 'This imageset will replace the one you are deleting in any styles that use it.',
	'REPLACE_STYLE'             => 'Replace style with',
	'REPLACE_STYLE_EXPLAIN'     => 'This style will replace the one being deleted for members that use it.',
	'REPLACE_TEMPLATE'          => 'Replace template with',
	'REPLACE_TEMPLATE_EXPLAIN'  => 'This template set will replace the one you are deleting in any styles that use it.',
	'REPLACE_THEME'             => 'Replace theme with',
	'REPLACE_THEME_EXPLAIN'     => 'This theme will replace the one you are deleting in any styles that use it.',
	'REPLACE_WITH_OPTION'       => 'Replace with “%s”',
	'REQUIRES_IMAGESET'         => 'This style requires the %s imageset to be installed.',
	'REQUIRES_TEMPLATE'         => 'This style requires the %s template set to be installed.',
	'REQUIRES_THEME'            => 'This style requires the %s theme to be installed.',

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

	'TEMPLATE_ADDED'            => 'Template set added and stored on filesystem.',
	'TEMPLATE_CACHE_CLEARED'    => 'Template cache cleared successfully.',
	'TEMPLATE_DELETED'          => 'Template set deleted successfully.',
	'TEMPLATE_DELETE_DEPENDENT' => 'The template set cannot be deleted as there are one or more other template sets inheriting from it:',
	'TEMPLATE_DELETED_FS'       => 'Template set removed from database but some files may remain on the filesystem.',
	'TEMPLATE_ERR_CACHE_READ'   => 'The cache directory used to store cached versions of template files could not be opened.',
	'TEMPLATE_ERR_NAME_EXIST'   => 'A template set with that directory already exists.',
	'TEMPLATE_ERR_NAME_LONG'    => 'The template directory can be no longer than 100 characters.',
	'TEMPLATE_ERR_NOT_TEMPLATE' => 'The archive you specified does not contain a valid template set.',
	'TEMPLATE_ERR_REQUIRED_OR_INCOMPLETE' => 'The new template set requires the template %s to be installed and not inheriting itself.',
	'TEMPLATE_ERR_STYLE_NAME'   => 'You must supply a directory for this template.',
	'TEMPLATE_ERR_VERSION'      => 'The template version must match phpBBex %1$s. Found version: %2$s.',
	'TEMPLATE_INHERITS'         => 'This template sets inherits from %s and thus cannot have a different storage setting than its super template.',
	'TEMPLATE_NAME'             => 'Template directory',

	'THEME_ADDED'               => 'New theme added on filesystem.',
	'THEME_DELETED'             => 'Theme deleted successfully.',
	'THEME_DELETED_FS'          => 'Theme removed from database but files remain on the filesystem.',
	'THEME_ERR_NAME_EXIST'      => 'A theme with that directory already exists.',
	'THEME_ERR_NAME_LONG'       => 'The theme directory can be no longer than 100 characters.',
	'THEME_ERR_NOT_THEME'       => 'The archive you specified does not contain a valid theme.',
	'THEME_ERR_STYLE_NAME'      => 'You must supply a directory for this theme.',
	'THEME_ERR_VERSION'         => 'The theme version must match phpBBex %1$s. Found version: %2$s.',
	'THEME_NAME'                => 'Theme directory',
	'THEME_REFRESHED'           => 'Theme stylesheet version refreshed successfully.',

	'UNINSTALLED_IMAGESET'  => 'Uninstalled imagesets',
	'UNINSTALLED_STYLE'     => 'Uninstalled styles',
	'UNINSTALLED_TEMPLATE'  => 'Uninstalled templates',
	'UNINSTALLED_THEME'     => 'Uninstalled themes',

]);
