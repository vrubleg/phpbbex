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
	'ACP_FILES'						=> 'Admin language files',
	'ACP_LANGUAGE_PACKS_EXPLAIN'	=> 'Here you are able to install/remove language packs. The default language pack is marked with an asterisk (*).',

	'EMAIL_FILES'			=> 'Email templates',

	'FILE_CONTENTS'				=> 'File contents',
	'FILE_FROM_STORAGE'			=> 'File from storage folder',

	'HELP_FILES'				=> 'Help files',

	'INSTALLED_LANGUAGE_PACKS'	=> 'Installed language packs',
	'INVALID_LANGUAGE_PACK'		=> 'The selected language pack seems to be not valid. Please verify the language pack and upload it again if necessary.',

	'LANGUAGE_DETAILS_UPDATED'			=> 'Language details successfully updated.',
	'LANGUAGE_ENTRIES'					=> 'Language entries',
	'LANGUAGE_ENTRIES_EXPLAIN'			=> 'Here you are able to change existing language pack entries or not already translated ones.<br /><strong>Note:</strong> Once you changed a language file, the changes will be stored within a separate folder for you to download. The changes will not be seen by your users until you replace the original language files at your webspace (by uploading them).',
	'LANGUAGE_FILES'					=> 'Language files',
	'LANGUAGE_KEY'						=> 'Language key',
	'LANGUAGE_PACK_ALREADY_INSTALLED'	=> 'This language pack is already installed.',
	'LANGUAGE_PACK_DELETED'				=> 'The language pack <strong>%s</strong> has been removed successfully. All users using this language have been reset to the boards default language.',
	'LANGUAGE_PACK_DETAILS'				=> 'Language pack details',
	'LANGUAGE_PACK_INSTALLED'			=> 'The language pack <strong>%s</strong> has been successfully installed.',
	'LANGUAGE_PACK_CPF_UPDATE'			=> 'The custom profile fields’ language strings were copied from the default language. Please change them if necessary.',
	'LANGUAGE_PACK_ISO'					=> 'ISO',
	'LANGUAGE_PACK_LOCALNAME'			=> 'Local name',
	'LANGUAGE_PACK_NAME'				=> 'Name',
	'LANGUAGE_PACK_NOT_EXIST'			=> 'The selected language pack does not exist.',
	'LANGUAGE_PACK_USED_BY'				=> 'Used by (including robots)',
	'LANGUAGE_VARIABLE'					=> 'Language variable',
	'LANG_AUTHOR'						=> 'Language pack author',
	'LANG_ENGLISH_NAME'					=> 'English name',
	'LANG_ISO_CODE'						=> 'ISO code',
	'LANG_LOCAL_NAME'					=> 'Local name',

	'MISSING_LANGUAGE_FILE'		=> 'Missing language file: <strong style="color:red">%s</strong>',
	'MISSING_LANG_VARIABLES'	=> 'Missing language variables',
	'MODS_FILES'				=> 'MODs language files',

	'NO_FILE_SELECTED'				=> 'You haven’t specified a language file.',
	'NO_LANG_ID'					=> 'You haven’t specified a language pack.',
	'NO_REMOVE_DEFAULT_LANG'		=> 'You are not able to remove the default language pack.<br />If you want to remove this language pack, change your boards default language first.',
	'NO_UNINSTALLED_LANGUAGE_PACKS'	=> 'No uninstalled language packs',

	'REMOVE_FROM_STORAGE_FOLDER'		=> 'Remove from storage folder',

	'SELECT_DOWNLOAD_FORMAT'	=> 'Select download format',
	'SUBMIT_AND_DOWNLOAD'		=> 'Submit and download file',

	'THOSE_MISSING_LANG_FILES'			=> 'The following language files are missing from the %s language folder',
	'THOSE_MISSING_LANG_VARIABLES'		=> 'The following language variables are missing from the <strong>%s</strong> language pack',

	'UNINSTALLED_LANGUAGE_PACKS'	=> 'Uninstalled language packs',

	'UNABLE_TO_WRITE_FILE'		=> 'The file could not be written to %s.',

	'WRONG_LANGUAGE_FILE'		=> 'Selected language file is invalid.',
]);
