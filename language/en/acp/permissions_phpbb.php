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

/**
*	MODDERS PLEASE NOTE
*
*	You are able to put your permission sets into a separate file too by
*	prefixing the new file with permissions_ and putting it into the acp
*	language folder.
*
*	An example of how the file could look like:
*
*	<code>
*
*	if (empty($lang) || !is_array($lang))
*	{
*		$lang = array();
*	}
*
*	// Adding new category
*	$lang['permission_cat']['bugs'] = 'Bugs';
*
*	// Adding new permission set
*	$lang['permission_type']['bug_'] = 'Bug Permissions';
*
*	// Adding the permissions
*	$lang = array_merge($lang, array(
*		'acl_bug_view'		=> array('lang' => 'Can view bug reports', 'cat' => 'bugs'),
*		'acl_bug_post'		=> array('lang' => 'Can post bugs', 'cat' => 'post'), // Using a phpBB category here
*	));
*
*	</code>
*/

// Define categories and permission types
$lang = array_merge($lang, [
	'permission_cat'	=> [
		'actions'		=> 'Actions',
		'content'		=> 'Content',
		'forums'		=> 'Forums',
		'misc'			=> 'Misc',
		'permissions'	=> 'Permissions',
		'pm'			=> 'Private messages',
		'polls'			=> 'Polls',
		'post'			=> 'Post',
		'post_actions'	=> 'Post actions',
		'posting'		=> 'Posting',
		'profile'		=> 'Profile',
		'settings'		=> 'Settings',
		'topic_actions'	=> 'Topic actions',
		'user_group'	=> 'Users &amp; Groups',
	],

	// With defining 'global' here we are able to specify what is printed out if the permission is within the global scope.
	'permission_type'	=> [
		'u_'			=> 'User permissions',
		'a_'			=> 'Admin permissions',
		'm_'			=> 'Moderator permissions',
		'f_'			=> 'Forum permissions',
		'global'		=> [
			'm_'			=> 'Global moderator permissions',
		],
	],
]);

// User Permissions
$lang = array_merge($lang, [
	'acl_u_viewprofile'	=> ['lang' => 'Can view profiles, memberlist and online list', 'cat' => 'profile'],
	'acl_u_chgname'		=> ['lang' => 'Can change username', 'cat' => 'profile'],
	'acl_u_chgpasswd'	=> ['lang' => 'Can change password', 'cat' => 'profile'],
	'acl_u_chgemail'	=> ['lang' => 'Can change email address', 'cat' => 'profile'],
	'acl_u_chgavatar'	=> ['lang' => 'Can change avatar', 'cat' => 'profile'],
	'acl_u_chggrp'		=> ['lang' => 'Can change default usergroup', 'cat' => 'profile'],

	'acl_u_attach'		=> ['lang' => 'Can attach files', 'cat' => 'post'],
	'acl_u_download'	=> ['lang' => 'Can download files', 'cat' => 'post'],
	'acl_u_chgcensors'	=> ['lang' => 'Can disable word censors', 'cat' => 'post'],
	'acl_u_sig'			=> ['lang' => 'Can use signature', 'cat' => 'post'],
	'acl_u_ignorefpedittime'	=> ['lang' => 'Can ignore edit time limit for first posts', 'cat' => 'post'],
	'acl_u_ignoreedittime'		=> ['lang' => 'Can ignore edit time limit', 'cat' => 'post'],

	'acl_u_sendpm'		=> ['lang' => 'Can send private messages', 'cat' => 'pm'],
	'acl_u_masspm'		=> ['lang' => 'Can send messages to multiple users', 'cat' => 'pm'],
	'acl_u_masspm_group'=> ['lang' => 'Can send messages to groups', 'cat' => 'pm'],
	'acl_u_readpm'		=> ['lang' => 'Can read private messages', 'cat' => 'pm'],
	'acl_u_pm_edit'		=> ['lang' => 'Can edit own private messages', 'cat' => 'pm'],
	'acl_u_pm_attach'	=> ['lang' => 'Can attach files in private messages', 'cat' => 'pm'],
	'acl_u_pm_download'	=> ['lang' => 'Can download files in private messages', 'cat' => 'pm'],
	'acl_u_pm_bbcode'	=> ['lang' => 'Can use BBCode in private messages', 'cat' => 'pm'],
	'acl_u_pm_smilies'	=> ['lang' => 'Can use smilies in private messages', 'cat' => 'pm'],
	'acl_u_pm_img'		=> ['lang' => 'Can use [img] BBCode tag in private messages', 'cat' => 'pm'],
	'acl_u_pm_flash'	=> ['lang' => 'Can use [flash] BBCode tag in private messages', 'cat' => 'pm'],

	'acl_u_canplus'		=> ['lang' => 'Can make positive ratings', 'cat' => 'misc'],
	'acl_u_canminus'	=> ['lang' => 'Can make negative ratings', 'cat' => 'misc'],
	'acl_u_sendemail'	=> ['lang' => 'Can send emails', 'cat' => 'misc'],
	'acl_u_sendim'		=> ['lang' => 'Can send instant messages', 'cat' => 'misc'],
	'acl_u_ignoreflood'	=> ['lang' => 'Can ignore flood limit', 'cat' => 'misc'],
	'acl_u_hideonline'	=> ['lang' => 'Can hide online status', 'cat' => 'misc'],
	'acl_u_viewonline'	=> ['lang' => 'Can view hidden online users', 'cat' => 'misc'],
	'acl_u_search'		=> ['lang' => 'Can search board', 'cat' => 'misc'],
]);

// Forum Permissions
$lang = array_merge($lang, [
	'acl_f_list'		=> ['lang' => 'Can see forum', 'cat' => 'post'],
	'acl_f_read'		=> ['lang' => 'Can read forum', 'cat' => 'post'],
	'acl_f_post'		=> ['lang' => 'Can start new topics', 'cat' => 'post'],
	'acl_f_reply'		=> ['lang' => 'Can reply to topics', 'cat' => 'post'],
	'acl_f_announce'	=> ['lang' => 'Can post announcements', 'cat' => 'post'],
	'acl_f_sticky'		=> ['lang' => 'Can post stickies', 'cat' => 'post'],

	'acl_f_poll'		=> ['lang' => 'Can create polls', 'cat' => 'polls'],
	'acl_f_vote'		=> ['lang' => 'Can vote in polls', 'cat' => 'polls'],
	'acl_f_votechg'		=> ['lang' => 'Can change existing vote', 'cat' => 'polls'],

	'acl_f_attach'		=> ['lang' => 'Can attach files', 'cat' => 'content'],
	'acl_f_download'	=> ['lang' => 'Can download files', 'cat' => 'content'],
	'acl_f_sigs'		=> ['lang' => 'Can use signatures', 'cat' => 'content'],
	'acl_f_bbcode'		=> ['lang' => 'Can use BBCode', 'cat' => 'content'],
	'acl_f_smilies'		=> ['lang' => 'Can use smilies', 'cat' => 'content'],
	'acl_f_img'			=> ['lang' => 'Can use [img] BBCode tag', 'cat' => 'content'],
	'acl_f_flash'		=> ['lang' => 'Can use [flash] BBCode tag', 'cat' => 'content'],

	'acl_f_edit'		=> ['lang' => 'Can edit own posts', 'cat' => 'actions'],
	'acl_f_delete'		=> ['lang' => 'Can delete own posts', 'cat' => 'actions'],
	'acl_f_user_lock'	=> ['lang' => 'Can lock own topics', 'cat' => 'actions'],
	'acl_f_bump'		=> ['lang' => 'Can bump topics', 'cat' => 'actions'],
	'acl_f_report'		=> ['lang' => 'Can report posts', 'cat' => 'actions'],

	'acl_f_search'		=> ['lang' => 'Can search the forum', 'cat' => 'misc'],
	'acl_f_ignoreflood' => ['lang' => 'Can ignore flood limit', 'cat' => 'misc'],
	'acl_f_postcount'	=> ['lang' => 'Increment post counter<br /><em>Please note that this setting only affects new posts.</em>', 'cat' => 'misc'],
	'acl_f_noapprove'	=> ['lang' => 'Can post without approval', 'cat' => 'misc'],
]);

// Moderator Permissions
$lang = array_merge($lang, [
	'acl_m_edit'		=> ['lang' => 'Can edit posts', 'cat' => 'post_actions'],
	'acl_m_delete'		=> ['lang' => 'Can delete posts', 'cat' => 'post_actions'],
	'acl_m_approve'		=> ['lang' => 'Can approve posts', 'cat' => 'post_actions'],
	'acl_m_report'		=> ['lang' => 'Can close and delete reports', 'cat' => 'post_actions'],
	'acl_m_chgposter'	=> ['lang' => 'Can change post author', 'cat' => 'post_actions'],

	'acl_m_move'	=> ['lang' => 'Can move topics', 'cat' => 'topic_actions'],
	'acl_m_lock'	=> ['lang' => 'Can lock topics', 'cat' => 'topic_actions'],
	'acl_m_split'	=> ['lang' => 'Can split topics', 'cat' => 'topic_actions'],
	'acl_m_merge'	=> ['lang' => 'Can merge topics', 'cat' => 'topic_actions'],

	'acl_m_info'	=> ['lang' => 'Can view post details', 'cat' => 'misc'],
	'acl_m_warn'	=> ['lang' => 'Can issue warnings<br /><em>This setting is only assigned globally. It is not forum based.</em>', 'cat' => 'misc'], // This moderator setting is only global (and not local)
	'acl_m_ban'		=> ['lang' => 'Can manage bans<br /><em>This setting is only assigned globally. It is not forum based.</em>', 'cat' => 'misc'], // This moderator setting is only global (and not local)
]);

// Admin Permissions
$lang = array_merge($lang, [
	'acl_a_board'		=> ['lang' => 'Can alter board settings/check for updates', 'cat' => 'settings'],
	'acl_a_server'		=> ['lang' => 'Can alter server/communication settings', 'cat' => 'settings'],
	'acl_a_phpinfo'		=> ['lang' => 'Can view php settings', 'cat' => 'settings'],

	'acl_a_forum'		=> ['lang' => 'Can manage forums', 'cat' => 'forums'],
	'acl_a_forumadd'	=> ['lang' => 'Can add new forums', 'cat' => 'forums'],
	'acl_a_forumdel'	=> ['lang' => 'Can delete forums', 'cat' => 'forums'],
	'acl_a_prune'		=> ['lang' => 'Can prune forums', 'cat' => 'forums'],

	'acl_a_icons'		=> ['lang' => 'Can alter topic icons and smilies', 'cat' => 'posting'],
	'acl_a_words'		=> ['lang' => 'Can alter word censors', 'cat' => 'posting'],
	'acl_a_bbcode'		=> ['lang' => 'Can define BBCode tags', 'cat' => 'posting'],
	'acl_a_attach'		=> ['lang' => 'Can alter attachment related settings', 'cat' => 'posting'],

	'acl_a_user'		=> ['lang' => 'Can manage users<br /><em>This also includes seeing the users browser agent within the viewonline list.</em>', 'cat' => 'user_group'],
	'acl_a_userdel'		=> ['lang' => 'Can delete/prune users', 'cat' => 'user_group'],
	'acl_a_group'		=> ['lang' => 'Can manage groups', 'cat' => 'user_group'],
	'acl_a_groupadd'	=> ['lang' => 'Can add new groups', 'cat' => 'user_group'],
	'acl_a_groupdel'	=> ['lang' => 'Can delete groups', 'cat' => 'user_group'],
	'acl_a_ranks'		=> ['lang' => 'Can manage ranks', 'cat' => 'user_group'],
	'acl_a_profile'		=> ['lang' => 'Can manage custom profile fields', 'cat' => 'user_group'],
	'acl_a_names'		=> ['lang' => 'Can manage disallowed names', 'cat' => 'user_group'],
	'acl_a_ban'			=> ['lang' => 'Can manage bans', 'cat' => 'user_group'],

	'acl_a_viewauth'	=> ['lang' => 'Can view permission masks', 'cat' => 'permissions'],
	'acl_a_authgroups'	=> ['lang' => 'Can alter permissions for individual groups', 'cat' => 'permissions'],
	'acl_a_authusers'	=> ['lang' => 'Can alter permissions for individual users', 'cat' => 'permissions'],
	'acl_a_fauth'		=> ['lang' => 'Can alter forum permission class', 'cat' => 'permissions'],
	'acl_a_mauth'		=> ['lang' => 'Can alter moderator permission class', 'cat' => 'permissions'],
	'acl_a_aauth'		=> ['lang' => 'Can alter admin permission class', 'cat' => 'permissions'],
	'acl_a_uauth'		=> ['lang' => 'Can alter user permission class', 'cat' => 'permissions'],
	'acl_a_roles'		=> ['lang' => 'Can manage roles', 'cat' => 'permissions'],
	'acl_a_switchperm'	=> ['lang' => 'Can use others permissions', 'cat' => 'permissions'],

	'acl_a_styles'		=> ['lang' => 'Can manage styles', 'cat' => 'misc'],
	'acl_a_viewlogs'	=> ['lang' => 'Can view logs', 'cat' => 'misc'],
	'acl_a_clearlogs'	=> ['lang' => 'Can clear logs', 'cat' => 'misc'],
	'acl_a_modules'		=> ['lang' => 'Can manage modules', 'cat' => 'misc'],
	'acl_a_language'	=> ['lang' => 'Can manage language packs', 'cat' => 'misc'],
	'acl_a_email'		=> ['lang' => 'Can send mass email', 'cat' => 'misc'],
	'acl_a_bots'		=> ['lang' => 'Can manage bots', 'cat' => 'misc'],
	'acl_a_reasons'		=> ['lang' => 'Can manage report/denial reasons', 'cat' => 'misc'],
	'acl_a_backup'		=> ['lang' => 'Can backup/restore database', 'cat' => 'misc'],
	'acl_a_search'		=> ['lang' => 'Can manage search backends and settings', 'cat' => 'misc'],
]);
