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
	'ACP_LANGUAGE_PACKS_EXPLAIN'    => 'Here you are able to install/remove language packs. The default language pack is marked with an asterisk (*).',

	'INSTALLED_LANGUAGE_PACKS'  => 'Installed language packs',
	'INVALID_LANGUAGE_PACK'     => 'The selected language pack seems to be not valid. Please verify the language pack and upload it again if necessary.',

	'LANGUAGE_DETAILS_UPDATED'          => 'Language details successfully updated.',
	'LANGUAGE_PACK_ALREADY_INSTALLED'   => 'This language pack is already installed.',
	'LANGUAGE_PACK_DELETED'             => 'The language pack <strong>%s</strong> has been removed successfully. All users using this language have been reset to the boards default language.',
	'LANGUAGE_PACK_DETAILS'             => 'Language pack details',
	'LANGUAGE_PACK_INSTALLED'           => 'The language pack <strong>%s</strong> has been successfully installed.',
	'LANGUAGE_PACK_CPF_UPDATE'          => 'The custom profile fields’ language strings were copied from the default language. Please change them if necessary.',
	'LANGUAGE_PACK_CODE'                => 'Language Code',
	'LANGUAGE_PACK_LOCAL_NAME'          => 'Localised Name',
	'LANGUAGE_PACK_ENGLISH_NAME'        => 'English Name',
	'LANGUAGE_PACK_NOT_EXIST'           => 'The selected language pack does not exist.',
	'LANGUAGE_PACK_USED_BY'             => 'Used by',

	'NO_LANG_CODE'                  => 'You haven’t specified a language pack.',
	'NO_REMOVE_DEFAULT_LANG'        => 'You are not able to remove the default language pack.<br />If you want to remove this language pack, change your boards default language first.',
	'NO_UNINSTALLED_LANGUAGE_PACKS' => 'No uninstalled language packs',

	'UNINSTALLED_LANGUAGE_PACKS'    => 'Uninstalled language packs',
]);
