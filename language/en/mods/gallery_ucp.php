<?php
/**
*
* gallery_ucp [English]
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
**/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'ALBUMS'                        => 'Albums',
	'ALBUM_DESC'                    => 'Album Description',

	'CREATE_PERSONAL_ALBUM'         => 'Create personal album',

	'DELETE_ALBUM'                  => 'Delete Album',
	'DELETE_ALBUM_CONFIRM'          => 'Delete your personal album and all its images?',
	'DELETED_ALBUMS'                => 'Album successfully deleted',

	'EDIT_ALBUM'                    => 'Edit album',
	'EDIT_PERSONAL_ALBUM'           => 'Edit personal album',
	'EDIT_PERSONAL_ALBUM_EXP'       => 'You can edit your personal album description here.',
	'EDITED_PERSONAL_ALBUM'         => 'Personal album successfully edited',



	'NEED_INITIALISE'               => 'You don’t have a personal album yet.',
	'NO_ALBUM_STEALING'             => 'You are not allowed to manage the Album of other users.',
	'NO_FAVORITES'                  => 'You don’t have any favorites.',
	'NO_PERSALBUM_ALLOWED'          => 'You don’t have the permissions create your personal album',
	'NO_PERSONAL_ALBUM'             => 'You don’t have a personal album yet. You can create one here.<br />Only the owner can upload images to a personal album.',
	'NO_SUBSCRIPTIONS'              => 'You didn’t subscribe to any image.',

	'PARSE_BBCODE'                  => 'Parse BBCode',
	'PARSE_SMILIES'                 => 'Parse smilies',
	'PARSE_URLS'                    => 'Parse links',
	'PERSONAL_ALBUM'                => 'Personal album',

	'REMOVE_FROM_FAVORITES'         => 'Remove from favorites',

	'UNSUBSCRIBE'                   => 'stop watching',
	'USER_ALLOW_COMMENTS'           => 'Allow users to comment your images',

	'YOUR_FAVORITE_IMAGES'          => 'Here you can see your favorite-images. You may remove them, if you don’t like them anymore.',
	'YOUR_SUBSCRIPTIONS'            => 'Here you see albums and images you get notified on.',

	'VIEWEXIFS_DEFAULT'             => 'View Exif-Data by default',

	'WATCH_CHANGED'                 => 'Settings stored',
	'WATCH_COM'                     => 'Subscribe commented images by default',
	'WATCH_FAVO'                    => 'Subscribe favorite images by default',
	'WATCH_NOTE'                    => 'This option only affects on new images. All other images need to be added by the “subscribe image“ option.',
	'WATCH_OWN'                     => 'Subscribe own images by default',
]);
