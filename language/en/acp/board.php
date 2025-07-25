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

// Board Settings
$lang = array_merge($lang, array(
	'ACP_BOARD_SETTINGS_EXPLAIN'	=> 'Here you can determine the basic operation of your board, give it a fitting name and description, and among other settings adjust the default values for timezone and language.',
	'ACTIVE_TOPICS_DAYS'			=> 'Topic is active for',
	'ACTIVE_TOPICS_DAYS_EXPLAIN'	=> 'Default activity period for "Active topics" page. Set this value to 0 to display all topics.',
	'ACTIVE_USERS_DAYS'				=> 'User is active for',
	'ACTIVE_USERS_DAYS_EXPLAIN'		=> 'The user is considered active for entered number of days.',
	'AUTO_GUEST_LANG'				=> 'Detect language for guests',
	'AUTO_GUEST_LANG_EXPLAIN'		=> 'Detects guest’s language automatically',
	'CUSTOM_DATEFORMAT'				=> 'Custom…',
	'DEFAULT_DATE_FORMAT'			=> 'Date format',
	'DEFAULT_DATE_FORMAT_EXPLAIN'	=> 'The date format is the same as the PHP <code>date</code> function.',
	'DEFAULT_LANGUAGE'				=> 'Default language',
	'DEFAULT_STYLE'					=> 'Default style',
	'DISABLE_BOARD'					=> 'Disable board',
	'DISABLE_BOARD_EXPLAIN'			=> 'This will make the board unavailable to users who are neither administrators nor moderators. You can also enter a short (255 character) message to display if you wish.',
	'OVERRIDE_STYLE'				=> 'Override user style',
	'OVERRIDE_STYLE_EXPLAIN'		=> 'Replaces user’s style with the default.',
	'OVERRIDE_LANGUAGE'				=> 'Override user language',
	'OVERRIDE_LANGUAGE_EXPLAIN'		=> 'Replaces user’s language with the default.',
	'OVERRIDE_DATEFORMAT'			=> 'Override user date format',
	'OVERRIDE_DATEFORMAT_EXPLAIN'	=> 'Replaces user’s date format with default',
	'OVERRIDE_TIMEZONE'				=> 'Override user time zone',
	'OVERRIDE_TIMEZONE_EXPLAIN'		=> 'Replaces user’s time zone with default',
	'SITE_DESC'						=> 'Site description',
	'SITE_NAME'						=> 'Site name',
	'SITE_KEYWORDS'					=> 'Site keywords',
	'SYSTEM_DST'					=> 'Enable Summer Time/<abbr title="Daylight Saving Time">DST</abbr>',
	'SYSTEM_TIMEZONE'				=> 'Guest timezone',
	'SYSTEM_TIMEZONE_EXPLAIN'			=> 'Timezone to use for displaying times to users who are not logged in (guests, bots). Logged in users set their timezone during registration and can change it in their user control panel.',
	'WARNINGS_EXPIRE'				=> 'Warning duration',
	'WARNINGS_EXPIRE_EXPLAIN'		=> 'Number of days that will elapse before a warning will automatically expire from a user’s record. Set this value to 0 to make warnings permanent.',
	'WARNING_POST_DEFAULT'			=> 'Default warning message',
));

// Logging settings
$lang = array_merge($lang, array(
	'ACP_LOGGING_SETTINGS_EXPLAIN'	=> 'Here you can determine logging settings of your board.',
	'KEEP_ADMIN_LOGS_DAYS'			=> 'Keep administrator logs for',
	'KEEP_MOD_LOGS_DAYS'			=> 'Keep moderator logs for',
	'KEEP_CRITICAL_LOGS_DAYS'		=> 'Keep error logs for',
	'KEEP_USER_LOGS_DAYS'			=> 'Keep user logs for',
	'KEEP_REGISTER_LOGS_DAYS'		=> 'Keep registration logs for',
));

// Display Settings
$lang = array_merge($lang, array(
	'ACP_STYLE_SETTINGS_EXPLAIN'		=> 'Here you can show/hide several features.',

	'STYLE_SETTINGS_GENERAL'			=> 'General',
	'STYLE_MIN_WIDTH'					=> 'Minimum page width',
	'STYLE_MAX_WIDTH'					=> 'Maximum page width',
	'STYLE_SHOW_SITENAME_IN_HEADERBAR'	=> 'Display site name and description in header bar',
	'STYLE_BACK_TO_TOP'					=> '"Back to top" button',
	'STYLE_ROUNDED_CORNERS'				=> 'Rounded corners',
	'STYLE_NEW_YEAR'					=> 'Winter-style headerbar',
	'SKIP_TYPICAL_NOTICES'				=> 'Skip typical notice pages',
	'SKIP_TYPICAL_NOTICES_EXPLAIN'		=> 'Redirect instantly after posting, voting, marking topics read, login or logout.',
	'POSTING_TOPIC_REVIEW'				=> 'Display topic review panel at posting page',

	'STYLE_SETTINGS_INDEX'				=> 'Board Index',
	'STYLE_SHOW_FEEDS_IN_FORUMLIST'		=> 'Display RSS feeds in forum list',

	'STYLE_SETTINGS_VIEWTOPIC'			=> 'View Topic',
	'STYLE_SHOW_SOCIAL_BUTTONS'			=> 'Display social buttons',
	'STYLE_VT_SHOW_POST_NUMBERS'		=> 'Display post numbers',

	'STYLE_SETTINGS_PROFILE'			=> 'Profile',
	'STYLE_SETTINGS_MINIPROFILE'		=> 'Mini profile',
	'STYLE_MP_ON_LEFT'					=> 'Mini profiles on left',
	'STYLE_MP_SHOW_TOPIC_POSTER'		=> 'Display topic starter',
	'STYLE_MP_SHOW_GENDER'				=> 'Display gender',
	'STYLE_MP_SHOW_AGE'					=> 'Display age',
	'STYLE_MP_SHOW_FROM'				=> 'Display from',
	'STYLE_MP_SHOW_WARNINGS'			=> 'Display warnings',
	'STYLE_MP_SHOW_RATING'				=> 'Display reputation',
	'STYLE_MP_SHOW_RATING_DETAILED'		=> 'Display detailed reputation',
	'STYLE_MP_SHOW_RATED'				=> 'Display loyalty',
	'STYLE_MP_SHOW_RATED_DETAILED'		=> 'Display detailed loyalty',
	'STYLE_MP_SHOW_POSTS'				=> 'Display posts counter',
	'STYLE_MP_SHOW_TOPICS'				=> 'Display topics counter',
	'STYLE_MP_SHOW_JOINED'				=> 'Display joined date',
	'STYLE_MP_SHOW_WITH_US'				=> 'Display how long user is with us',
	'STYLE_MP_SHOW_BUTTONS'				=> 'Display contact buttons',

	'STYLE_SETTINGS_MEMBERLIST'			=> 'Member list',
	'STYLE_MP_SHOW_ROW_NUMBERS'			=> 'Display sequential numbers',
	'STYLE_MP_SHOW_RANK'				=> 'Display user rank',
	'STYLE_MP_SHOW_WEBSITE'				=> 'Display user site',
	'STYLE_MP_SHOW_LAST_ACTIVE'			=> 'Display user last active date',

	'COPYRIGHT_NOTICE'					=> 'Copyrights (HTML)',
	'COPYRIGHT_NOTICE_EXPLAIN'			=> 'Use {POWERED_BY} or {L_POWERED_BY} placeholder to output phpBBex copyright.',

	'AUTO'								=> 'Auto',
	'ON_LEFT'							=> 'On Left',
	'ON_RIGHT'							=> 'On Right',
));

// Board Features
$lang = array_merge($lang, array(
	'ACP_BOARD_FEATURES_EXPLAIN'	=> 'Here you can enable/disable several board features.',

	'ALLOW_ATTACHMENTS'			=> 'Allow attachments',
	'ALLOW_BIRTHDAYS'			=> 'Allow birthdays',
	'ALLOW_BIRTHDAYS_EXPLAIN'	=> 'Allow birthdays to be entered and age being displayed in profiles.',
	'ALLOW_BOOKMARKS'			=> 'Allow bookmarking topics',
	'ALLOW_BOOKMARKS_EXPLAIN'	=> 'User is able to store personal bookmarks.',
	'ALLOW_BBCODE'				=> 'Allow BBCode',
	'ALLOW_FORUM_NOTIFY'		=> 'Allow subscribing to forums',
	'ALLOW_NAME_CHANGE'			=> 'Allow username changes',
	'ALLOW_NO_CENSORS'			=> 'Allow disabling of word censoring',
	'ALLOW_NO_CENSORS_EXPLAIN'	=> 'Users can choose to disable the automatic word censoring of posts and private messages.',
	'ALLOW_PM_ATTACHMENTS'		=> 'Allow attachments in private messages',
	'ALLOW_PM_REPORT'			=> 'Allow users to report private messages',
	'ALLOW_PM_REPORT_EXPLAIN'	=> 'If this setting is enabled, users have the option of reporting a private message they have received or sent to the board’s moderators. These private messages will then be visible in the Moderator Control Panel.',
	'ALLOW_SIG'					=> 'Allow signatures',
	'ALLOW_SIG_BBCODE'			=> 'Allow BBCode in user signatures',
	'ALLOW_SIG_FLASH'			=> 'Allow use of <code>[FLASH]</code> BBCode tag in user signatures',
	'ALLOW_SIG_IMG'				=> 'Allow use of <code>[IMG]</code> BBCode tag in user signatures',
	'ALLOW_SIG_LINKS'			=> 'Allow use of links in user signatures',
	'ALLOW_SIG_LINKS_EXPLAIN'	=> 'If disallowed the <code>[URL]</code> BBCode tag and automatic/magic URLs are disabled.',
	'ALLOW_SIG_SMILIES'			=> 'Allow use of smilies in user signatures',
	'ALLOW_SMILIES'				=> 'Allow smilies',
	'ALLOW_TOPIC_NOTIFY'		=> 'Allow subscribing to topics',
	'BOARD_PM'					=> 'Private messaging',
	'BOARD_PM_EXPLAIN'			=> 'Enable private messaging for all users.',
	'ANNOUNCE_INDEX'				=> 'Display global annoucements',
	'ACTIVE_TOPICS_ON_INDEX'		=> 'Display active topics',
	'ACTIVE_TOPICS_ON_INDEX_EXPLAIN'=> 'Leave empty or 0 to disable active topics on index.',

	'RATINGS'					=> 'Post ratings',
	'RATE_ENABLED'				=> 'Enable ratings',
	'RATE_ONLY_TOPICS'			=> 'Rate only topics (first posts)',
	'RATE_TOPIC_TIME'			=> 'Limit topic (first post) rating time',
	'RATE_TIME'					=> 'Limit post rating time',
	'RATE_CHANGE_TIME'			=> 'Limit rating change time',
	'RATE_NO_NEGATIVE'			=> 'No negative',
	'RATE_NO_POSITIVE'			=> 'No positive',
	'DISPLAY_RATERS'			=> 'Display who and how rated messages',
));

// Avatar Settings
$lang = array_merge($lang, array(
	'ACP_AVATAR_SETTINGS_EXPLAIN'	=> 'Avatars are generally small, unique images a user can associate with themselves. Depending on the style they are usually displayed below the username when viewing topics. Here you can determine how users can define their avatars. Please note that in order to upload avatars you need to have created the directory you name below and ensure it can be written to by the web server. Please also note that file size limits are only imposed on uploaded avatars, they do not apply to remotely linked images.',

	'ALLOW_AVATARS'					=> 'Enable avatars',
	'ALLOW_AVATARS_EXPLAIN'			=> 'Allow general usage of avatars;<br />If you disable avatars in general or avatars of a certain mode, the disabled avatars will no longer be shown on the board, but users will still be able to download their own avatars in the User Control Panel.',
	'ALLOW_AVATAR_LOCAL'			=> 'Enable gallery avatars',
	'ALLOW_AVATAR_REMOTE'			=> 'Enable remote avatars',
	'ALLOW_AVATAR_REMOTE_EXPLAIN'	=> 'Not recommended! Avatars linked to from another website.',
	'ALLOW_AVATAR_REMOTE_UPLOAD'			=> 'Enable remote avatar uploading',
	'ALLOW_AVATAR_REMOTE_UPLOAD_EXPLAIN'	=> 'Allow uploading of avatars from another website.',
	'ALLOW_AVATAR_UPLOAD'			=> 'Enable avatar uploading',
	'MAX_AVATAR_SIZE'				=> 'Maximum avatar dimensions',
	'MAX_AVATAR_SIZE_EXPLAIN'		=> 'Width x Height in pixels.',
	'MAX_AVATAR_FILESIZE'			=> 'Maximum avatar file size',
	'MAX_AVATAR_FILESIZE_EXPLAIN'	=> 'For uploaded avatar files. If this value is 0, the uploaded filesize is only limited by your PHP configuration.',
	'MIN_AVATAR_SIZE'				=> 'Minimum avatar dimensions',
	'MIN_AVATAR_SIZE_EXPLAIN'		=> 'Width x Height in pixels.',
));

// Message Settings
$lang = array_merge($lang, array(
	'ACP_MESSAGE_SETTINGS_EXPLAIN'		=> 'Here you can set all default settings for private messaging.',

	'ALLOW_BBCODE_PM'			=> 'Allow BBCode in private messages',
	'ALLOW_FLASH_PM'			=> 'Allow use of <code>[FLASH]</code> BBCode tag',
	'ALLOW_FLASH_PM_EXPLAIN'	=> 'Note that the ability to use flash in private messages, if enabled here, also depends on the permissions.',
	'ALLOW_IMG_PM'				=> 'Allow use of <code>[IMG]</code> BBCode tag',
	'ALLOW_MASS_PM'				=> 'Allow sending of private messages to multiple users and groups',
	'ALLOW_MASS_PM_EXPLAIN'		=> 'Sending to groups can be adjusted per group within the group settings page.',
	'ALLOW_QUOTE_PM'			=> 'Allow quotes in private messages',
	'ALLOW_SIG_PM'				=> 'Allow signature in private messages',
	'ALLOW_SMILIES_PM'			=> 'Allow smilies in private messages',
	'BOXES_LIMIT'				=> 'Maximum private messages per box',
	'BOXES_LIMIT_EXPLAIN'		=> 'Users may receive no more than this many messages in each of their private message boxes. Set this value to 0 to allow unlimited messages.',
	'BOXES_MAX'					=> 'Maximum private message folders',
	'BOXES_MAX_EXPLAIN'			=> 'By default users may create this many personal folders for private messages.',
	'ENABLE_PM_ICONS'			=> 'Enable use of topic icons in private messages',
	'FULL_FOLDER_ACTION'		=> 'Full folder default action',
	'FULL_FOLDER_ACTION_EXPLAIN'=> 'Default action to take if a user’s folder is full assuming the user’s folder action, if set at all, is not applicable. The only exception is for the “Delivered” folder where the default action is always to delete old messages.',
	'HOLD_NEW_MESSAGES'			=> 'Hold new messages',
	'PM_EDIT_TIME'				=> 'Limit editing time',
	'PM_EDIT_TIME_EXPLAIN'		=> 'Limits the time available to edit a private message not already delivered. Setting the value to 0 disables this behaviour.',
	'PM_MAX_RECIPIENTS'			=> 'Maximum number of allowed recipients',
	'PM_MAX_RECIPIENTS_EXPLAIN'	=> 'The maximum number of allowed recipients in a private message. If 0 is entered, an unlimited number is allowed. This setting can be adjusted for every group within the group settings page.',
));

// Post Settings
$lang = array_merge($lang, array(
	'ACP_POST_SETTINGS_EXPLAIN'			=> 'Here you can set all default settings for posting.',

	'ENABLE_TOPIC_ICONS'				=> 'Enable topic icons',
	'ALLOW_POST_LINKS'					=> 'Allow links in posts/private messages',
	'ALLOW_POST_LINKS_EXPLAIN'			=> 'If disallowed the <code>[URL]</code> BBCode tag and automatic/magic URLs are disabled.',
	'ALLOW_POST_FLASH'					=> 'Allow use of <code>[FLASH]</code> BBCode tag in posts',
	'ALLOW_POST_FLASH_EXPLAIN'			=> 'If disallowed the <code>[FLASH]</code> BBCode tag is disabled in posts. Otherwise the permission system controls which users can use the <code>[FLASH]</code> BBCode tag.',

	// Quick reply
	'QUICK_REPLY'					=> 'Quick reply',
	'ALLOW_QUICK_REPLY'				=> 'Allow quick reply',
	'ALLOW_QUICK_REPLY_EXPLAIN'		=> 'Users can post reply directly at topic view.',
	'ALLOW_QUICK_REPLY_NONE'		=> 'None',
	'ALLOW_QUICK_REPLY_REG'			=> 'Registered only',
	'ALLOW_QUICK_REPLY_ALL'			=> 'All',
	'ALLOW_QUICK_REPLY_ICONS'		=> 'Topic icons',
	'ALLOW_QUICK_REPLY_SUBJECT'		=> 'Subject',
	'ALLOW_QUICK_REPLY_CHECKBOXES'	=> 'Checkboxes',
	'ALLOW_QUICK_REPLY_ATTACHBOX'	=> 'Attachbox',
	'ALLOW_QUICK_REPLY_SMILIES'		=> 'Smilies',
	'ALLOW_QUICK_FULL_QUOTE'		=> 'Allow full quote',
	'ALLOW_QUICK_TOPIC'				=> 'Allow quick topic',
	'ALLOW_QUICK_TOPIC_EXPLAIN'		=> 'Users can start topic directly at forum view.',

	'MERGE_INTERVAL'				=> 'Merging posts interval',
	'MERGE_INTERVAL_EXPLAIN'		=> 'Number of hours a messages from the user will be merged with his topic last message. Leave empty or 0 to disable merging.',
	'BUMP_INTERVAL'					=> 'Bump interval',
	'BUMP_INTERVAL_EXPLAIN'			=> 'Number of minutes, hours or days between the last post to a topic and the ability to bump that topic. Setting the value to 0 disables bumping entirely.',
	'CHAR_LIMIT'					=> 'Maximum characters per post/message',
	'CHAR_LIMIT_EXPLAIN'			=> 'The number of characters allowed within a post/private message. Set to 0 for unlimited characters.',
	'DELETE_TIME'					=> 'Limit deleting time',
	'DELETE_TIME_EXPLAIN'			=> 'Limits the time available to delete a new post. Setting the value to 0 disables this behaviour.',
	'DISPLAY_LAST_EDITED'			=> 'Display last edited time information',
	'DISPLAY_LAST_EDITED_EXPLAIN'	=> 'Choose if the last edited by information to be displayed on posts.',
	'EDIT_TIME'						=> 'Limit editing time',
	'EDIT_TIME_EXPLAIN'				=> 'Limits the time available to edit a new post. Setting the value to 0 disables this behaviour.',
	'FLOOD_INTERVAL'				=> 'Flood interval',
	'FLOOD_INTERVAL_EXPLAIN'		=> 'Number of seconds a user must wait between posting new messages. To enable users to ignore this alter their permissions.',
	'MAX_POLL_OPTIONS'				=> 'Maximum number of poll options',
	'MAX_POST_IMGS'					=> 'Maximum images per post',
	'MAX_POST_IMGS_EXPLAIN'			=> 'Maximum number of images in a post. Set to 0 for unlimited images.',
	'MIN_POST_FONT_SIZE'			=> 'Minimum font size per post',
	'MIN_POST_FONT_SIZE_EXPLAIN'	=> 'Minimum font size allowed in a post. Set to 0 for unlimited font size.',
	'MAX_POST_FONT_SIZE'			=> 'Maximum font size per post',
	'MAX_POST_FONT_SIZE_EXPLAIN'	=> 'Maximum font size allowed in a post. Set to 0 for unlimited font size.',
	'MAX_POST_IMG_HEIGHT'			=> 'Maximum image height per post',
	'MAX_POST_IMG_HEIGHT_EXPLAIN'	=> 'Maximum height of an image/flash file in postings. Set to 0 for unlimited size.',
	'MAX_POST_IMG_WIDTH'			=> 'Maximum image width per post',
	'MAX_POST_IMG_WIDTH_EXPLAIN'	=> 'Maximum width of an image/flash file in postings. Set to 0 for unlimited size.',
	'MAX_POST_URLS'					=> 'Maximum links per post',
	'MAX_POST_URLS_EXPLAIN'			=> 'Maximum number of URLs in a post. Set to 0 for unlimited links.',
	'MIN_CHAR_LIMIT'				=> 'Minimum characters per post/message',
	'MIN_CHAR_LIMIT_EXPLAIN'		=> 'The minimum number of characters the user need to enter within a post/private message. The minimum for this setting is 1.',
	'POSTING'						=> 'Posting',
	'POSTS_PER_PAGE'				=> 'Posts per page',
	'QUOTE_DEPTH_LIMIT'				=> 'Maximum nesting depth for quotes',
	'QUOTE_DEPTH_LIMIT_EXPLAIN'		=> 'Maximum quote nesting depth in a post. Set to 0 for unlimited depth or -1 for disable quote.',
	'SPOILER_DEPTH_LIMIT'			=> 'Maximum nesting depth for spoilers',
	'SPOILER_DEPTH_LIMIT_EXPLAIN'	=> 'Maximum spoiler nesting depth in a post. Set to 0 for unlimited depth or -1 for disable spoiler.',
	'SMILIES_LIMIT'					=> 'Maximum smilies per post',
	'SMILIES_LIMIT_EXPLAIN'			=> 'Maximum number of smilies in a post. Set to 0 for unlimited smilies.',
	'SMILIES_PER_PAGE'				=> 'Smilies per page',
	'TOPICS_PER_PAGE'				=> 'Topics per page',
	'EXTERNAL_LINKS'				=> 'External Links',
	'EXTERNAL_LINKS_NEWWINDOW'		=> 'Open in new windows',
	'EXTERNAL_LINKS_NOFOLLOW'		=> 'Add attribute rel="nofollow"',
	'EXTERNAL_LINKS_EXCLUDE'		=> 'Exclude',
	'EXTERNAL_LINKS_EXCLUDE_EXPLAIN'=> 'Domains or URLs which is not treated as external. Exceptions are placed on separate lines or can be separated by commas.',
));

// Signature Settings
$lang = array_merge($lang, array(
	'ACP_SIGNATURE_SETTINGS_EXPLAIN'	=> 'Here you can set all default settings for signatures.',

	'MIN_SIG_FONT_SIZE'				=> 'Minimum signature font size',
	'MIN_SIG_FONT_SIZE_EXPLAIN'		=> 'Minimum font size allowed in user signatures. Set to 0 for unlimited size.',
	'MAX_SIG_FONT_SIZE'				=> 'Maximum signature font size',
	'MAX_SIG_FONT_SIZE_EXPLAIN'		=> 'Maximum font size allowed in user signatures. Set to 0 for unlimited size.',
	'MAX_SIG_IMGS'					=> 'Maximum signature images',
	'MAX_SIG_IMGS_EXPLAIN'			=> 'Maximum number of images in user signatures. Set to 0 for unlimited links.',
	'MAX_SIG_IMG_HEIGHT'			=> 'Maximum signature image height',
	'MAX_SIG_IMG_HEIGHT_EXPLAIN'	=> 'Maximum height of an image/flash file in user signatures. Set to 0 for unlimited height.',
	'MAX_SIG_IMG_WIDTH'				=> 'Maximum signature image width',
	'MAX_SIG_IMG_WIDTH_EXPLAIN'		=> 'Maximum width of an image/flash file in user signatures. Set to 0 for unlimited width.',
	'MAX_SIG_LENGTH'				=> 'Maximum signature length',
	'MAX_SIG_LENGTH_EXPLAIN'		=> 'Maximum number of characters in user signatures.',
	'MAX_SIG_LINES'					=> 'Maximum lines per signature',
	'MAX_SIG_LINES_EXPLAIN'			=> 'Maximum lines allowed in user signatures. Set to 0 for unlimited lines.',
	'MAX_SIG_SMILIES'				=> 'Maximum smilies per signature',
	'MAX_SIG_SMILIES_EXPLAIN'		=> 'Maximum smilies allowed in user signatures. Set to 0 for unlimited smilies.',
	'MAX_SIG_URLS'					=> 'Maximum signature links',
	'MAX_SIG_URLS_EXPLAIN'			=> 'Maximum number of links in user signatures. Set to 0 for unlimited links.',
));

// Registration Settings
$lang = array_merge($lang, array(
	'ACP_REGISTER_SETTINGS_EXPLAIN'		=> 'Here you are able to define registration and profile related settings.',

	'ACC_ACTIVATION'				=> 'Account activation',
	'ACC_ACTIVATION_EXPLAIN'		=> 'This determines whether users have immediate access to the board or if confirmation is required. You can also completely disable new registrations. “Board-wide email” must be enabled in order to use user or admin activation.',
	'NEW_MEMBER_POST_LIMIT'			=> 'New member post limit',
	'NEW_MEMBER_POST_LIMIT_EXPLAIN'	=> 'New members are within the <em>Newly Registered Users</em> group until they reach this number of posts. You can use this group to keep them from using the PM system or to review their posts. <strong>A value of 0 disables this feature.</strong>',
	'NEW_MEMBER_GROUP_DEFAULT'		=> 'Set Newly Registered Users group to default',
	'NEW_MEMBER_GROUP_DEFAULT_EXPLAIN'	=> 'If set to yes, and a new member post limit is specified, newly registered users will not only be put into the <em>Newly Registered Users</em> group, but this group will also be their default one. This may come in handy if you want to assign a group default rank and/or avatar the user then inherits.',

	'ACC_ADMIN'					=> 'By admin',
	'ACC_DISABLE'				=> 'Disable registration',
	'ACC_NONE'					=> 'No activation (immediate access)',
	'ACC_USER'					=> 'By user (email verification)',
	'ALLOW_EMAIL_REUSE'			=> 'Allow email address re-use',
	'ALLOW_EMAIL_REUSE_EXPLAIN'	=> 'Different users can register with the same email address.',
	'MAX_CHARS'					=> 'Max',
	'MIN_CHARS'					=> 'Min',
	'NO_AUTH_PLUGIN'			=> 'No suitable auth plugin found.',
	'PASSWORD_LENGTH'			=> 'Password length',
	'PASSWORD_LENGTH_EXPLAIN'	=> 'Minimum and maximum number of characters in passwords.',
	'REG_LIMIT'					=> 'Registration attempts',
	'REG_LIMIT_EXPLAIN'			=> 'Number of attempts users can make at solving the anti-spambot task before being locked out of that session.',
	'USERNAME_CHARS'			=> 'Limit username chars',
	'USERNAME_CHARS_EXPLAIN'	=> 'Restrict type of characters that may be used in usernames, spacers are: space, dot, hyphen and underscore.',
	'USERNAME_LATCHARS_NOSPACE'	=> 'Latin alphanumeric without spaces',
	'USERNAME_LATCHARS_SPACERS'	=> 'Latin alphanumeric and spacers',
	'USERNAME_UNICHARS_NOSPACE'	=> 'Any alphanumeric without spaces',
	'USERNAME_UNICHARS_SPACERS'	=> 'Any alphanumeric and spacers',
	'USERNAME_LENGTH'			=> 'Username length',
	'USERNAME_LENGTH_EXPLAIN'	=> 'Minimum and maximum number of characters in usernames.',
));

// Feeds
$lang = array_merge($lang, array(
	'ACP_FEED_MANAGEMENT'				=> 'General syndication feeds settings',
	'ACP_FEED_MANAGEMENT_EXPLAIN'		=> 'This module makes available various ATOM feeds, parsing any BBCode in posts to make them readable in external feeds.',

	'ACP_FEED_GENERAL'					=> 'General feed settings',
	'ACP_FEED_POST_BASED'				=> 'Post-based feed settings',
	'ACP_FEED_TOPIC_BASED'				=> 'Topic-based feed settings',
	'ACP_FEED_SETTINGS_OTHER'			=> 'Other feeds and settings',

	'ACP_FEED_ENABLE'					=> 'Enable feeds',
	'ACP_FEED_ENABLE_EXPLAIN'			=> 'Turns on or off ATOM feeds for the entire board.<br />Disabling this switches off all feeds, no matter how the options below are set.',
	'ACP_FEED_LIMIT'					=> 'Number of items',
	'ACP_FEED_LIMIT_EXPLAIN'			=> 'The maximum number of feed items to display.',

	'ACP_FEED_OVERALL'					=> 'Enable board-wide feed',
	'ACP_FEED_OVERALL_EXPLAIN'			=> 'Board-wide new posts.',
	'ACP_FEED_FORUM'					=> 'Enable per-forum feeds',
	'ACP_FEED_FORUM_EXPLAIN'			=> 'Single forum and subforums new posts.',
	'ACP_FEED_TOPIC'					=> 'Enable per-topic feeds',
	'ACP_FEED_TOPIC_EXPLAIN'			=> 'Single topics new posts.',

	'ACP_FEED_TOPICS_NEW'				=> 'Enable new topics feed',
	'ACP_FEED_TOPICS_NEW_EXPLAIN'		=> 'Enables the “New Topics” feed, which displays the last created topics including the first post.',
	'ACP_FEED_TOPICS_ACTIVE'			=> 'Enable active topics feed',
	'ACP_FEED_TOPICS_ACTIVE_EXPLAIN'	=> 'Enables the “Active Topics” feed, which displays the last active topics including the last post.',
	'ACP_FEED_NEWS'						=> 'News feed',
	'ACP_FEED_NEWS_EXPLAIN'				=> 'Pull the first post from these forums. Select no forums to disable news feed.<br />Select multiple forums by holding <samp>CTRL</samp> and clicking.',

	'ACP_FEED_OVERALL_FORUMS'			=> 'Enable forums feed',
	'ACP_FEED_OVERALL_FORUMS_EXPLAIN'	=> 'Enables the “All forums” feed, which displays a list of forums.',

	'ACP_FEED_HTTP_AUTH'				=> 'Allow HTTP Authentication',
	'ACP_FEED_HTTP_AUTH_EXPLAIN'		=> 'Enables HTTP authentication, which allows users to receive content that is hidden to guest users by adding the <samp>auth=http</samp> parameter to the feed URL. Please note that some PHP setups require additional changes to the .htaccess file. Instructions can be found in that file.',
	'ACP_FEED_ITEM_STATISTICS'			=> 'Item statistics',
	'ACP_FEED_ITEM_STATISTICS_EXPLAIN'	=> 'Display individual statistics underneath feed items<br />(e.g. posted by, date and time, replies, views)',
	'ACP_FEED_EXCLUDE_ID'				=> 'Exclude these forums',
	'ACP_FEED_EXCLUDE_ID_EXPLAIN'		=> 'Content from these will be <strong>not included in feeds</strong>. Select no forum to pull data from all forums.<br />Select/Deselect multiple forums by holding <samp>CTRL</samp> and clicking.',
));

// Visual Confirmation Settings
$lang = array_merge($lang, array(
	'ACP_VC_SETTINGS_EXPLAIN'				=> 'Here you can select and configure plugins, which are designed to block automated form submissions by spambots. These plugins typically work by challenging the user with a <em>CAPTCHA</em>, a test which is designed to be difficult for computers to solve.',
	'AVAILABLE_CAPTCHAS'					=> 'Available plugins',
	'CAPTCHA_UNAVAILABLE'					=> 'The plugin cannot be selected as its requirements are not met.',
	'CAPTCHA_GD'							=> 'GD image',
	'CAPTCHA_GD_3D'							=> 'GD 3D image',
	'CAPTCHA_GD_FOREGROUND_NOISE'			=> 'Foreground noise',
	'CAPTCHA_GD_EXPLAIN'					=> 'Uses GD to make a more advanced anti-spambot image.',
	'CAPTCHA_GD_FOREGROUND_NOISE_EXPLAIN'	=> 'Use foreground noise to make the image harder to read.',
	'CAPTCHA_GD_X_GRID'						=> 'Background noise x-axis',
	'CAPTCHA_GD_X_GRID_EXPLAIN'				=> 'Use lower settings of this to make the image harder to read. 0 will disable x-axis background noise.',
	'CAPTCHA_GD_Y_GRID'						=> 'Background noise y-axis',
	'CAPTCHA_GD_Y_GRID_EXPLAIN'				=> 'Use lower settings of this to make the image harder to read. 0 will disable y-axis background noise.',
	'CAPTCHA_GD_WAVE'						=> 'Wave distortion',
	'CAPTCHA_GD_WAVE_EXPLAIN'				=> 'This applies a wave distortion to the image.',
	'CAPTCHA_GD_3D_NOISE'					=> 'Add 3D-noise objects',
	'CAPTCHA_GD_3D_NOISE_EXPLAIN'			=> 'This adds additional objects to the image, over the letters.',
	'CAPTCHA_GD_FONTS'						=> 'Use different fonts',
	'CAPTCHA_GD_FONTS_EXPLAIN'				=> 'This setting controls how many different letter shapes are used. You can just use the default shapes or introduce altered letters. Adding lowercase letters is also possible.',
	'CAPTCHA_FONT_DEFAULT'					=> 'Default',
	'CAPTCHA_FONT_NEW'						=> 'New Shapes',
	'CAPTCHA_FONT_LOWER'					=> 'Also use lowercase',
	'CAPTCHA_NO_GD'							=> 'Simple image',
	'CAPTCHA_PREVIEW_MSG'					=> 'Your changes have not been saved, this is just a preview.',
	'CAPTCHA_PREVIEW_EXPLAIN'				=> 'The plugin as it would look like using the current selection.',

	'CAPTCHA_SELECT'						=> 'Installed plugins',
	'CAPTCHA_SELECT_EXPLAIN'				=> 'The dropdown holds the plugins recognised by the board. Grey entries are not available right now and might need configuration prior to use.',
	'CAPTCHA_CONFIGURE'						=> 'Configure plugins',
	'CAPTCHA_CONFIGURE_EXPLAIN'				=> 'Change the settings for the selected plugin.',
	'CONFIGURE'								=> 'Configure',
	'CAPTCHA_NO_OPTIONS'					=> 'This plugin has no configuration options.',

	'VISUAL_CONFIRM_POST'					=> 'Enable spambot countermeasures for guest postings',
	'VISUAL_CONFIRM_POST_EXPLAIN'			=> 'Requires guest users to pass the anti-spambot task to help prevent automated postings.',
	'VISUAL_CONFIRM_REG'					=> 'Enable spambot countermeasures for registrations',
	'VISUAL_CONFIRM_REG_EXPLAIN'			=> 'Requires new users to pass the anti-spambot task to help prevent automated registrations.',
	'VISUAL_CONFIRM_REFRESH'				=> 'Allow users to refresh the anti-spambot task',
	'VISUAL_CONFIRM_REFRESH_EXPLAIN'		=> 'Allows users to request a new anti-spambot task if they are unable to solve the current task during registration. Some plugins might not support this option.',
));

// Cookie Settings
$lang = array_merge($lang, array(
	'ONLINE_LENGTH'				=> 'View online time span',
	'ONLINE_LENGTH_EXPLAIN'		=> 'Number of minutes after which inactive users will not appear in “Who is online” listings.',
	'SESSION_LENGTH'			=> 'Session length',
	'SESSION_LENGTH_EXPLAIN'	=> 'Sessions will expire after this time, in seconds.',
));

// Load Settings
$lang = array_merge($lang, array(
	'ACP_LOAD_SETTINGS_EXPLAIN'	=> 'Here you can enable and disable certain board functions to reduce the amount of processing required. On most servers there is no need to disable any functions. However on certain systems or in shared hosting environments it may be beneficial to disable capabilities you do not really need. You can also specify limits for system load and active sessions beyond which the board will go offline.',

	'CUSTOM_PROFILE_FIELDS'			=> 'Custom profile fields',
	'LIMIT_LOAD'					=> 'Limit system load',
	'LIMIT_LOAD_EXPLAIN'			=> 'If the system’s 1-minute load average exceeds this value the board will automatically go offline. A value of 1.0 equals ~100% utilisation of one processor. This only functions on UNIX based servers and where this information is accessible. The value here resets itself to 0 if phpBBex was unable to get the load limit.',
	'LIMIT_SESSIONS'				=> 'Limit sessions',
	'LIMIT_SESSIONS_EXPLAIN'		=> 'If the number of sessions exceeds this value within a one minute period the board will go offline. Set to 0 for unlimited sessions.',
	'LOAD_CPF_MEMBERLIST'			=> 'Allow styles to display custom profile fields in memberlist',
	'LOAD_CPF_VIEWPROFILE'			=> 'Display custom profile fields in user profiles',
	'LOAD_CPF_VIEWTOPIC'			=> 'Display custom profile fields on topic pages',
	'LOAD_USER_ACTIVITY'			=> 'Show user’s activity',
	'LOAD_USER_ACTIVITY_EXPLAIN'	=> 'Displays active topic/forum in user profiles and user control panel. It is recommended to disable this on boards with more than one million posts.',
	'RECOMPILE_STYLES'				=> 'Recompile stale style components',
	'RECOMPILE_STYLES_EXPLAIN'		=> 'Check for updated style components on filesystem and recompile.',
	'YES_ANON_READ_MARKING'			=> 'Enable topic marking for guests',
	'YES_ANON_READ_MARKING_EXPLAIN'	=> 'Stores read/unread status information for guests. If disabled, posts are always marked read for guests.',
	'YES_BIRTHDAYS'					=> 'Display birthday list',
	'YES_JUMPBOX'					=> 'Enable display of jumpbox',
	'YES_MODERATORS'				=> 'Enable display of moderators',
	'YES_ONLINE'					=> 'Enable online user listings',
	'YES_ONLINE_EXPLAIN'			=> 'Display online user information on index page.',
	'YES_ONLINE_GUESTS'				=> 'Enable online guest listings',
	'YES_ONLINE_BOTS'				=> 'Enable online bot listings',
	'YES_ONLINE_TRACK'				=> 'Enable display of user online/offline information',
	'YES_ONLINE_TRACK_EXPLAIN'		=> 'Display online information for user in profiles and topic pages.',
	'YES_POST_MARKING'				=> 'Enable dotted topics',
	'YES_POST_MARKING_EXPLAIN'		=> 'Indicates whether user has posted to a topic.',
	'YES_READ_MARKING'				=> 'Enable server-side topic marking',
	'YES_READ_MARKING_EXPLAIN'		=> 'Stores read/unread status information in the database rather than a cookie.',
	'YES_UNREAD_SEARCH'				=> 'Enable search for unread posts',
));

// Auth settings
$lang = array_merge($lang, array(
	'ACP_AUTH_SETTINGS_EXPLAIN'	=> 'phpBBex supports authentication plug-ins, or modules. These allow you determine how users are authenticated when they log into the board. By default three plug-ins are provided; DB, LDAP and Apache. Not all methods require additional information so only fill out fields if they are relevant to the selected method.',

	'AUTH_METHOD'				=> 'Select an authentication method',

	'APACHE_SETUP_BEFORE_USE'	=> 'You have to setup apache authentication before you switch phpBBex to this authentication method. Keep in mind that the username you use for apache authentication has to be the same as your phpBBex username. Apache authentication can only be used with mod_php (not with a CGI version).',

	'LDAP_DN'						=> 'LDAP base <var>dn</var>',
	'LDAP_DN_EXPLAIN'				=> 'This is the Distinguished Name, locating the user information, e.g. <samp>o=My Company,c=US</samp>.',
	'LDAP_EMAIL'					=> 'LDAP email attribute',
	'LDAP_EMAIL_EXPLAIN'			=> 'Set this to the name of your user entry email attribute (if one exists) in order to automatically set the email address for new users. Leaving this empty results in empty email address for users who log in for the first time.',
	'LDAP_INCORRECT_USER_PASSWORD'	=> 'Binding to LDAP server failed with specified user/password.',
	'LDAP_NO_EMAIL'					=> 'The specified email attribute does not exist.',
	'LDAP_NO_IDENTITY'				=> 'Could not find a login identity for %s.',
	'LDAP_PASSWORD'					=> 'LDAP password',
	'LDAP_PASSWORD_EXPLAIN'			=> 'Leave blank to use anonymous binding, otherwise fill in the password for the above user. Required for Active Directory Servers.<br /><em><strong>Warning:</strong> This password will be stored as plain text in the database, visible to everybody who can access your database or who can view this configuration page.</em>',
	'LDAP_PORT'						=> 'LDAP server port',
	'LDAP_PORT_EXPLAIN'				=> 'Optionally you can specify a port which should be used to connect to the LDAP server instead of the default port 389.',
	'LDAP_SERVER'					=> 'LDAP server name',
	'LDAP_SERVER_EXPLAIN'			=> 'If using LDAP this is the hostname or IP address of the LDAP server. Alternatively you can specify an URL like ldap://hostname:port/',
	'LDAP_UID'						=> 'LDAP <var>uid</var>',
	'LDAP_UID_EXPLAIN'				=> 'This is the key under which to search for a given login identity, e.g. <var>uid</var>, <var>sn</var>, etc.',
	'LDAP_USER'						=> 'LDAP user <var>dn</var>',
	'LDAP_USER_EXPLAIN'				=> 'Leave blank to use anonymous binding. If filled in phpBBex uses the specified distinguished name on login attempts to find the correct user, e.g. <samp>uid=Username,ou=MyUnit,o=MyCompany,c=US</samp>. Required for Active Directory Servers.',
	'LDAP_USER_FILTER'				=> 'LDAP user filter',
	'LDAP_USER_FILTER_EXPLAIN'		=> 'Optionally you can further limit the searched objects with additional filters. For example <samp>objectClass=posixGroup</samp> would result in the use of <samp>(&amp;(uid=$username)(objectClass=posixGroup))</samp>',
));

// Server Settings
$lang = array_merge($lang, array(
	'ACP_SERVER_SETTINGS_EXPLAIN'	=> 'Here you define server settings.',

	'ENABLE_GZIP'				=> 'Enable GZip compression',
	'ENABLE_GZIP_EXPLAIN'		=> 'Generated content will be compressed prior to sending it to the user. This reduces network traffic but will also increase CPU usage.',
));

// Security Settings
$lang = array_merge($lang, array(
	'ACP_SECURITY_SETTINGS_EXPLAIN'		=> 'Here you are able to define session and login related settings.',

	'ALL'							=> 'All',
	'ALLOW_AUTOLOGIN'				=> 'Allow persistent logins',
	'ALLOW_AUTOLOGIN_EXPLAIN'		=> 'Determines whether users can autologin when they visit the board.',
	'AUTOLOGIN_LENGTH'				=> 'Persistent login key expiration length (in days)',
	'AUTOLOGIN_LENGTH_EXPLAIN'		=> 'Number of days after which persistent login keys are removed or zero to disable.',
	'BROWSER_VALID'					=> 'Validate browser',
	'BROWSER_VALID_EXPLAIN'			=> 'Enables browser validation for each session improving security.',
	'CHECK_DNSBL'					=> 'Check IP against DNS Blackhole List',
	'CHECK_DNSBL_EXPLAIN'			=> 'If enabled the user’s IP address is checked against the following DNSBL services on registration and posting: <a href="http://spamcop.net">spamcop.net</a> and <a href="http://www.spamhaus.org">www.spamhaus.org</a>. This lookup may take a while, depending on the server’s configuration. If slowdowns are experienced or too many false positives reported it is recommended to disable this check.',
	'CLASS_B'						=> 'A.B',
	'CLASS_C'						=> 'A.B.C',
	'EMAIL_CHECK_MX'				=> 'Check email domain for valid MX record',
	'EMAIL_CHECK_MX_EXPLAIN'		=> 'If enabled, the email domain provided on registration and profile changes is checked for a valid MX record.',
	'FORCE_PASS_CHANGE'				=> 'Force password change',
	'FORCE_PASS_CHANGE_EXPLAIN'		=> 'Require user to change their password after a set number of days. Setting this value to 0 disables this behaviour.',
	'FORM_TIME_MAX'					=> 'Maximum time to submit forms',
	'FORM_TIME_MAX_EXPLAIN'			=> 'The time a user has to submit a form. Use -1 to disable. Note that a form might become invalid if the session expires, regardless of this setting.',
	'FORM_SID_GUESTS'				=> 'Tie forms to guest sessions',
	'FORM_SID_GUESTS_EXPLAIN'		=> 'If enabled, the form token issued to guests will be session-exclusive. This can cause problems with some ISPs.',
	'FORWARDED_FOR_VALID'			=> 'Validate <var>X_FORWARDED_FOR</var> header',
	'FORWARDED_FOR_VALID_EXPLAIN'	=> 'Sessions will only be continued if the sent <var>X_FORWARDED_FOR</var> header equals the one sent with the previous request. Bans will be checked against IPs in <var>X_FORWARDED_FOR</var> too.',
	'IP_VALID'						=> 'Session IP validation',
	'IP_VALID_EXPLAIN'				=> 'Determines how much of the users IP is used to validate a session; <samp>All</samp> compares the complete address, <samp>A.B.C</samp> the first x.x.x, <samp>A.B</samp> the first x.x, <samp>None</samp> disables checking. On IPv6 addresses <samp>A.B.C</samp> compares the first 4 blocks and <samp>A.B</samp> the first 3 blocks.',
	'IP_LOGIN_LIMIT_MAX'			=> 'Maximum number of login attempts per IP address',
	'IP_LOGIN_LIMIT_MAX_EXPLAIN'	=> 'The threshold of login attempts allowed from a single IP address before an anti-spambot task is triggered. Enter 0 to prevent the anti-spambot task from being triggered by IP addresses.',
	'IP_LOGIN_LIMIT_TIME'			=> 'IP address login attempt expiration time',
	'IP_LOGIN_LIMIT_TIME_EXPLAIN'	=> 'Login attempts expire after this period.',
	'IP_LOGIN_LIMIT_USE_FORWARDED'	=> 'Limit login attempts by <var>X_FORWARDED_FOR</var> header',
	'IP_LOGIN_LIMIT_USE_FORWARDED_EXPLAIN'	=> 'Instead of limiting login attempts by IP address they are limited by <var>X_FORWARDED_FOR</var> values. <br /><em><strong>Warning:</strong> Only enable this if you are operating a proxy server that sets <var>X_FORWARDED_FOR</var> to trustworthy values.</em>',
	'MAX_LOGIN_ATTEMPTS'			=> 'Maximum number of login attempts per username',
	'MAX_LOGIN_ATTEMPTS_EXPLAIN'	=> 'The number of login attempts allowed for a single account before the anti-spambot task is triggered. Enter 0 to prevent the anti-spambot task from being triggered for distinct user accounts.',
	'NO_IP_VALIDATION'				=> 'None',
	'NO_REF_VALIDATION'				=> 'None',
	'PASSWORD_TYPE'					=> 'Password complexity',
	'PASSWORD_TYPE_EXPLAIN'			=> 'Determines how complex a password needs to be when set or altered, subsequent options include the previous ones.',
	'PASS_TYPE_ALPHA'				=> 'Must contain letters and numbers',
	'PASS_TYPE_ANY'					=> 'No requirements',
	'PASS_TYPE_CASE'				=> 'Must be mixed case',
	'PASS_TYPE_SYMBOL'				=> 'Must contain symbols',
	'REF_HOST'						=> 'Only validate host',
	'REF_PATH'						=> 'Also validate path',
	'REFERER_VALID'					=> 'Validate Referer',
	'REFERER_VALID_EXPLAIN'			=> 'If enabled, the referer of POST requests will be checked against the host/script path settings. This may cause issues with boards using several domains and or external logins.',
	'TPL_ALLOW_PHP'					=> 'Allow php in templates',
	'TPL_ALLOW_PHP_EXPLAIN'			=> 'If this option is enabled, <code>PHP</code> and <code>INCLUDEPHP</code> statements will be recognised and parsed in templates.',
));

// Email Settings
$lang = array_merge($lang, array(
	'ACP_EMAIL_SETTINGS_EXPLAIN'	=> 'This information is used when the board sends emails to your users. Please ensure the email address you specify is valid, any bounced or undeliverable messages will likely be sent to that address. If your host does not provide a native (PHP based) email service you can instead send messages directly using SMTP. This requires the address of an appropriate SMTP server.',

	'ADMIN_EMAIL'					=> 'Return email address',
	'ADMIN_EMAIL_EXPLAIN'			=> 'This will be used as the return address on all emails, the technical contact email address. It will always be used as the <samp>Sender</samp> address in emails.',
	'BOARD_EMAIL_FORM'				=> 'Users send email via board',
	'BOARD_EMAIL_FORM_EXPLAIN'		=> 'Instead of showing the users email address users are able to send emails via the board.',
	'BOARD_HIDE_EMAILS'				=> 'Hide email addresses of all users',
	'BOARD_HIDE_EMAILS_EXPLAIN'		=> 'This function keeps email addresses completely private.',
	'CONTACT_EMAIL'					=> 'Contact email address',
	'CONTACT_EMAIL_EXPLAIN'			=> 'This address will be used whenever a specific contact point is needed, e.g. spam, error output, etc. It will always be used as the <samp>From</samp> and <samp>Reply-To</samp> address in emails.',
	'CONTACT_EMAIL_NAME'			=> 'Notifications sender name',
	'EMAIL_FORCE_SENDER'			=> 'Force return email address',
	'EMAIL_FORCE_SENDER_EXPLAIN'	=> 'It might be required for some servers. This will set the <samp>Return-Path</samp> to the return email address instead of using the local user and hostname of the server. This setting does not apply when using SMTP.<br><em><strong>Warning:</strong> Requires the user that the webserver runs as to be added as trusted user to the sendmail configuration.</em>',
	'EMAIL_PACKAGE_SIZE'			=> 'Email package size',
	'EMAIL_PACKAGE_SIZE_EXPLAIN'	=> 'This is the number of maximum emails sent out in one package. This setting is applied to the internal message queue; set this value to 0 if you have problems with non-delivered notification emails.',
	'EMAIL_SIG'						=> 'Email signature',
	'EMAIL_SIG_EXPLAIN'				=> 'This text will be attached to all emails the board sends.',
	'ENABLE_EMAIL'					=> 'Enable board-wide emails',
	'ENABLE_EMAIL_EXPLAIN'			=> 'If this is set to disabled, no emails will be sent by the board at all.<br /><em><strong>Warning:</strong> the user and admin account activation settings require this setting to be enabled.</em>',
	'SEND_TEST_EMAIL'				=> 'Send a test email',
	'SEND_TEST_EMAIL_EXPLAIN'		=> 'This will send a test email to the address defined in your account.',
	'SMTP_AUTH_METHOD'				=> 'Authentication method for SMTP',
	'SMTP_AUTH_METHOD_EXPLAIN'		=> 'Only used if a username/password is set.',
	'SMTP_CRAM_MD5'					=> 'CRAM-MD5',
	'SMTP_DIGEST_MD5'				=> 'DIGEST-MD5',
	'SMTP_LOGIN'					=> 'LOGIN',
	'SMTP_PASSWORD'					=> 'SMTP password',
	'SMTP_PASSWORD_EXPLAIN'			=> 'Only enter a password if your SMTP server requires it.<br /><em><strong>Warning:</strong> This password will be stored as plain text in the database, visible to everybody who can access your database or who can view this configuration page.</em>',
	'SMTP_PLAIN'					=> 'PLAIN',
	'SMTP_PORT'						=> 'SMTP server port',
	'SMTP_PORT_EXPLAIN'				=> 'Usually, 25 is for unencrypted SMTP, 465 is for SMTPS with tls:// address, and 587 is for SMTP with STARTTLS support.',
	'SMTP_SERVER'					=> 'SMTP server address',
	'SMTP_SERVER_EXPLAIN'			=> 'Use tls:// prefix for SMTPS, no prefix for SMTP.',
	'SMTP_SETTINGS'					=> 'SMTP settings',
	'SMTP_USERNAME'					=> 'SMTP username',
	'SMTP_USERNAME_EXPLAIN'			=> 'Only enter a username if your SMTP server requires it.',
	'SMTP_VERIFY_CERT'				=> 'Verify TLS certificate',
	'SMTP_VERIFY_CERT_EXPLAIN'		=> 'Require verification of TLS certificate used by SMTP server.',
	'TEST_EMAIL_SENT'				=> 'The test email has been sent.',
	'USE_SMTP'						=> 'Use SMTP server for email',
	'USE_SMTP_EXPLAIN'				=> 'Select “Yes” if you want or have to send email via a named server instead of the local mail function.',
));

// Jabber settings
$lang = array_merge($lang, array(
	'ACP_JABBER_SETTINGS_EXPLAIN'	=> 'Here you can enable and control the use of Jabber for instant messaging and board notifications. Jabber is an open source protocol and therefore available for use by anyone. Some Jabber servers include gateways or transports which allow you to contact users on other networks. Not all servers offer all transports and changes in protocols can prevent transports from operating. Please be sure to enter already registered account details - phpBBex will use the details you enter here as is.',

	'JAB_ENABLE'				=> 'Enable Jabber',
	'JAB_ENABLE_EXPLAIN'		=> 'Enables use of Jabber messaging and notifications.',
	'JAB_GTALK_NOTE'			=> 'Please note that GTalk will not work because the <samp>dns_get_record</samp> function could not be found. This function is not available in PHP4, and is not implemented on Windows platforms. It currently does not work on BSD-based systems, including Mac OS.',
	'JAB_PACKAGE_SIZE'			=> 'Jabber package size',
	'JAB_PACKAGE_SIZE_EXPLAIN'	=> 'This is the number of messages sent in one package. If set to 0 the message is sent immediately and will not be queued for later sending.',
	'JAB_PASSWORD'				=> 'Jabber password',
	'JAB_PASSWORD_EXPLAIN'		=> '<em><strong>Warning:</strong> This password will be stored as plain text in the database, visible to everybody who can access your database or who can view this configuration page.</em>',
	'JAB_PORT'					=> 'Jabber port',
	'JAB_PORT_EXPLAIN'			=> 'Leave blank unless you know it is not port 5222.',
	'JAB_SERVER'				=> 'Jabber server',
	'JAB_SERVER_EXPLAIN'		=> 'See %sjabber.org%s for a list of servers.',
	'JAB_SETTINGS_CHANGED'		=> 'Jabber settings changed successfully.',
	'JAB_USE_SSL'				=> 'Use SSL to connect',
	'JAB_USE_SSL_EXPLAIN'		=> 'If enabled a secure connection is tried to be established. The Jabber port will be modified to 5223 if port 5222 is specified.',
	'JAB_USERNAME'				=> 'Jabber username or JID',
	'JAB_USERNAME_EXPLAIN'		=> 'Specify a registered username or a valid JID. The username will not be checked for validity. If you only specify a username, then your JID will be the username and the server you specified above. Else, specify a valid JID, for example user@jabber.org.',
));
