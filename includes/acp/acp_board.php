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

/**
* @todo add cron intervals to server settings? (database_gc, queue_interval, session_gc, search_gc, cache_gc, warnings_gc)
*/
class acp_board
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $config, $cache;

		$user->add_lang('acp/board');

		$action	= request_var('action', '');
		$submit = isset($_POST['submit']);

		$form_key = 'acp_board';
		add_form_key($form_key);

		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
		switch ($mode)
		{
			case 'settings':
				$display_vars = array(
					'title'	=> 'ACP_BOARD_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_BOARD_SETTINGS',
						'sitename'				=> array('lang' => 'SITE_NAME',				'validate' => 'string',	'type' => 'text:40:255', 'explain' => false),
						'site_desc'				=> array('lang' => 'SITE_DESC',				'validate' => 'string',	'type' => 'text:40:255', 'explain' => false),
						'site_keywords'			=> array('lang' => 'SITE_KEYWORDS',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => false),
						'copyright_notice'		=> array('lang' => 'COPYRIGHT_NOTICE',		'validate' => 'string',	'type' => 'htmlarea:3:1000', 'explain' => true),
						'board_disable'			=> array('lang' => 'DISABLE_BOARD',			'validate' => 'bool',	'type' => 'custom', 'method' => 'board_disable', 'explain' => true),
						'board_disable_msg'		=> false,
						'default_lang'			=> array('lang' => 'DEFAULT_LANGUAGE',		'validate' => 'lang',	'type' => 'select', 'function' => 'language_select', 'params' => array('{CONFIG_VALUE}'), 'explain' => false),
						'override_user_lang'	=> array('lang' => 'OVERRIDE_LANGUAGE',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'auto_guest_lang'		=> array('lang' => 'AUTO_GUEST_LANG',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'default_dateformat'	=> array('lang' => 'DEFAULT_DATE_FORMAT',	'validate' => 'string',	'type' => 'custom', 'method' => 'dateformat_select', 'explain' => true),
						'override_user_dateformat'	=> array('lang' => 'OVERRIDE_DATEFORMAT',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'board_timezone'		=> array('lang' => 'SYSTEM_TIMEZONE',		'validate' => 'string',	'type' => 'select', 'function' => 'tz_select', 'params' => array('{CONFIG_VALUE}', 1), 'explain' => true),
						'board_dst'				=> array('lang' => 'SYSTEM_DST',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'override_user_timezone'	=> array('lang' => 'OVERRIDE_TIMEZONE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'default_style'			=> array('lang' => 'DEFAULT_STYLE',			'validate' => 'int',	'type' => 'select', 'function' => 'style_select', 'params' => array('{CONFIG_VALUE}', false), 'explain' => false),
						'override_user_style'	=> array('lang' => 'OVERRIDE_STYLE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'active_topics_days'	=> array('lang' => 'ACTIVE_TOPICS_DAYS',	'validate' => 'int',	'type' => 'select', 'method' => 'select_days', 'params' => array('{CONFIG_VALUE}', '{KEY}', true), 'explain' => true),
						'active_users_days'		=> array('lang' => 'ACTIVE_USERS_DAYS',		'validate' => 'int',	'type' => 'select', 'method' => 'select_days', 'params' => array('{CONFIG_VALUE}', '{KEY}', false), 'explain' => true),

						'legend5'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'features':
				$display_vars = array(
					'title'	=> 'ACP_BOARD_FEATURES',
					'vars'	=> array(
						'legend1'				=> 'ACP_BOARD_FEATURES',
						'allow_privmsg'			=> array('lang' => 'BOARD_PM',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_topic_notify'	=> array('lang' => 'ALLOW_TOPIC_NOTIFY',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_forum_notify'	=> array('lang' => 'ALLOW_FORUM_NOTIFY',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_namechange'		=> array('lang' => 'ALLOW_NAME_CHANGE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_attachments'		=> array('lang' => 'ALLOW_ATTACHMENTS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_pm_attach'		=> array('lang' => 'ALLOW_PM_ATTACHMENTS',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_pm_report'		=> array('lang' => 'ALLOW_PM_REPORT',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_bbcode'			=> array('lang' => 'ALLOW_BBCODE',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_smilies'			=> array('lang' => 'ALLOW_SMILIES',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig'				=> array('lang' => 'ALLOW_SIG',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_nocensors'		=> array('lang' => 'ALLOW_NO_CENSORS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_bookmarks'		=> array('lang' => 'ALLOW_BOOKMARKS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_birthdays'		=> array('lang' => 'ALLOW_BIRTHDAYS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'login_via_email_enable'=> array('lang' => 'LOGIN_VIA_EMAIL_ENABLE', 'validate' => 'int',	'type' => 'custom', 'method' => 'login_via_email_options', 'explain' => true),

						'legend2'				=> 'WARNINGS',
						'warning_post_default'	=> array('lang' => 'WARNING_POST_DEFAULT',	'validate' => 'string',	'type' => 'textarea:1:255', 'explain' => false),
						'warnings_expire_days'	=> array('lang' => 'WARNINGS_EXPIRE',		'validate' => 'int',	'type' => 'text:3:4', 'explain' => true, 'append' => ' ' . $user->lang['DAYS']),

						'legend3'				=> 'RATINGS',
						'rate_enabled'			=> array('lang' => 'RATE_ENABLED',			'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false),
						'display_raters'		=> array('lang' => 'DISPLAY_RATERS',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false),
						'rate_only_topics'		=> array('lang' => 'RATE_ONLY_TOPICS',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false),
						'rate_time'				=> array('lang' => 'RATE_TIME',				'validate' => 'int',	'type' => 'text:10:10',		'explain' => false, 'append' => ' ' . $user->lang['SECONDS']),
						'rate_topic_time'		=> array('lang' => 'RATE_TOPIC_TIME',		'validate' => 'int',	'type' => 'text:10:10',		'explain' => false, 'append' => ' ' . $user->lang['SECONDS']),
						'rate_change_time'		=> array('lang' => 'RATE_CHANGE_TIME',		'validate' => 'int',	'type' => 'text:10:10',		'explain' => false, 'append' => ' ' . $user->lang['SECONDS']),
						'rate_no_negative'		=> array('lang' => 'RATE_NO_NEGATIVE',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false),
						'rate_no_positive'		=> array('lang' => 'RATE_NO_POSITIVE',		'validate' => 'bool',	'type' => 'radio:yes_no',	'explain' => false),

						'legend4'				=> 'WHO_IS_ONLINE',
						'load_online'			=> array('lang' => 'YES_ONLINE',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_online_guests'	=> array('lang' => 'YES_ONLINE_GUESTS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_online_bots'		=> array('lang' => 'YES_ONLINE_BOTS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_onlinetrack'		=> array('lang' => 'YES_ONLINE_TRACK',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_online_time'		=> array('lang' => 'ONLINE_LENGTH',			'validate' => 'int:0',	'type' => 'text:4:3', 'explain' => true, 'append' => ' ' . $user->lang['MINUTES']),

						'legend5'				=> 'ACP_LOAD_SETTINGS',
						'load_unreads_search'	=> array('lang' => 'YES_UNREAD_SEARCH',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_db_track'			=> array('lang' => 'YES_POST_MARKING',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend6'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'logs':
				$display_vars = array(
					'title'	=> 'ACP_LOGGING_SETTINGS',
					'vars'	=> array(
						'legend1'					=> 'ACP_LOGGING_SETTINGS',
						'keep_admin_logs_days'		=> array('lang' => 'KEEP_ADMIN_LOGS_DAYS',		'validate' => 'int',	'type' => 'select', 'method' => 'select_days', 'params' => array('{CONFIG_VALUE}', '{KEY}', true), 'explain' => false),
						'keep_mod_logs_days'		=> array('lang' => 'KEEP_MOD_LOGS_DAYS',		'validate' => 'int',	'type' => 'select', 'method' => 'select_days', 'params' => array('{CONFIG_VALUE}', '{KEY}', true), 'explain' => false),
						'keep_critical_logs_days'	=> array('lang' => 'KEEP_CRITICAL_LOGS_DAYS',	'validate' => 'int',	'type' => 'select', 'method' => 'select_days', 'params' => array('{CONFIG_VALUE}', '{KEY}', true), 'explain' => false),
						'keep_user_logs_days'		=> array('lang' => 'KEEP_USER_LOGS_DAYS',		'validate' => 'int',	'type' => 'select', 'method' => 'select_days', 'params' => array('{CONFIG_VALUE}', '{KEY}', true), 'explain' => false),
						'keep_register_logs_days'	=> array('lang' => 'KEEP_REGISTER_LOGS_DAYS',	'validate' => 'int',	'type' => 'select', 'method' => 'select_days', 'params' => array('{CONFIG_VALUE}', '{KEY}', true), 'explain' => false),

						'legend2'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'style':
				$display_vars = array(
					'title'	=> 'ACP_STYLE_SETTINGS',
					'vars'	=> array(
						'legend1'							=> 'STYLE_SETTINGS_GENERAL',
						'style_min_width'					=> array('lang' => 'STYLE_MIN_WIDTH',					'validate' => 'int',	'type' => 'text:10:10', 'explain' => false, 'append' => ' ' . $user->lang['PIXEL']),
						'style_max_width'					=> array('lang' => 'STYLE_MAX_WIDTH',					'validate' => 'int',	'type' => 'text:10:10', 'explain' => false, 'append' => ' ' . $user->lang['PIXEL']),
						'style_back_to_top'					=> array('lang' => 'STYLE_BACK_TO_TOP',					'validate' => 'int',	'type' => 'custom', 'function' => 'h_radio', 'params' => array('config[style_back_to_top]', array(1 => 'ON_LEFT', 2 => 'ON_RIGHT', 0 => 'NO'), '{CONFIG_VALUE}', '{KEY}'), 'explain' => false),
						'style_rounded_corners'				=> array('lang' => 'STYLE_ROUNDED_CORNERS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_new_year'					=> array('lang' => 'STYLE_NEW_YEAR',					'validate' => 'int',	'type' => 'custom', 'function' => 'h_radio', 'params' => array('config[style_new_year]', array(-1 => 'AUTO', 1 => 'YES', 0 => 'NO'), '{CONFIG_VALUE}', '{KEY}'), 'explain' => false),
						'style_show_sitename_in_headerbar'	=> array('lang' => 'STYLE_SHOW_SITENAME_IN_HEADERBAR',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_jumpbox'						=> array('lang' => 'YES_JUMPBOX',						'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'skip_typical_notices'				=> array('lang' => 'SKIP_TYPICAL_NOTICES',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'posting_topic_review'				=> array('lang' => 'POSTING_TOPIC_REVIEW',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend2'							=> 'STYLE_SETTINGS_INDEX',
						'announce_index'					=> array('lang'	=> 'ANNOUNCE_INDEX',					'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'active_topics_on_index'			=> array('lang' => 'ACTIVE_TOPICS_ON_INDEX',			'validate' => 'int',	'type' => 'text:3:4', 'explain' => true),
						'load_birthdays'					=> array('lang' => 'YES_BIRTHDAYS',						'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_moderators'					=> array('lang' => 'YES_MODERATORS',					'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_show_feeds_in_forumlist'		=> array('lang' => 'STYLE_SHOW_FEEDS_IN_FORUMLIST',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend3'							=> 'STYLE_SETTINGS_VIEWTOPIC',
						'style_show_social_buttons'			=> array('lang' => 'STYLE_SHOW_SOCIAL_BUTTONS',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_vt_show_post_numbers'		=> array('lang' => 'STYLE_VT_SHOW_POST_NUMBERS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'display_raters'					=> array('lang' => 'DISPLAY_RATERS',					'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend4'							=> 'STYLE_SETTINGS_MINIPROFILE',
						'style_mp_on_left'					=> array('lang' => 'STYLE_MP_ON_LEFT',					'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_topic_poster'		=> array('lang' => 'STYLE_MP_SHOW_TOPIC_POSTER',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_gender'				=> array('lang' => 'STYLE_MP_SHOW_GENDER',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_age'					=> array('lang' => 'STYLE_MP_SHOW_AGE',					'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_from'				=> array('lang' => 'STYLE_MP_SHOW_FROM',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_warnings'			=> array('lang' => 'STYLE_MP_SHOW_WARNINGS',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_rating'				=> array('lang' => 'STYLE_MP_SHOW_RATING',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_rating_detailed'		=> array('lang' => 'STYLE_MP_SHOW_RATING_DETAILED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_rated'				=> array('lang' => 'STYLE_MP_SHOW_RATED',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_rated_detailed'		=> array('lang' => 'STYLE_MP_SHOW_RATED_DETAILED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_posts'				=> array('lang' => 'STYLE_MP_SHOW_POSTS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_topics'				=> array('lang' => 'STYLE_MP_SHOW_TOPICS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_joined'				=> array('lang' => 'STYLE_MP_SHOW_JOINED',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_with_us'				=> array('lang' => 'STYLE_MP_SHOW_WITH_US',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_mp_show_buttons'				=> array('lang' => 'STYLE_MP_SHOW_BUTTONS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend5'							=> 'STYLE_SETTINGS_PROFILE',
						'style_p_show_rating'				=> array('lang' => 'STYLE_MP_SHOW_RATING',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_p_show_rating_detailed'		=> array('lang' => 'STYLE_MP_SHOW_RATING_DETAILED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_p_show_rated'				=> array('lang' => 'STYLE_MP_SHOW_RATED',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_p_show_rated_detailed'		=> array('lang' => 'STYLE_MP_SHOW_RATED_DETAILED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend6'							=> 'STYLE_SETTINGS_MEMBERLIST',
						'style_ml_show_row_numbers'			=> array('lang' => 'STYLE_MP_SHOW_ROW_NUMBERS',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_gender'				=> array('lang' => 'STYLE_MP_SHOW_GENDER',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_rank'				=> array('lang' => 'STYLE_MP_SHOW_RANK',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_rating'				=> array('lang' => 'STYLE_MP_SHOW_RATING',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_rating_detailed'		=> array('lang' => 'STYLE_MP_SHOW_RATING_DETAILED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_rated'				=> array('lang' => 'STYLE_MP_SHOW_RATED',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_rated_detailed'		=> array('lang' => 'STYLE_MP_SHOW_RATED_DETAILED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_posts'				=> array('lang' => 'STYLE_MP_SHOW_POSTS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_topics'				=> array('lang' => 'STYLE_MP_SHOW_TOPICS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_from'				=> array('lang' => 'STYLE_MP_SHOW_FROM',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_website'				=> array('lang' => 'STYLE_MP_SHOW_WEBSITE',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_joined'				=> array('lang' => 'STYLE_MP_SHOW_JOINED',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'style_ml_show_last_active'			=> array('lang' => 'STYLE_MP_SHOW_LAST_ACTIVE',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend7'							=> 'CUSTOM_PROFILE_FIELDS',
						'load_cpf_memberlist'				=> array('lang' => 'LOAD_CPF_MEMBERLIST',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_cpf_viewprofile'				=> array('lang' => 'LOAD_CPF_VIEWPROFILE',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_cpf_viewtopic'				=> array('lang' => 'LOAD_CPF_VIEWTOPIC',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend8'							=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'avatar':
				$display_vars = array(
					'title'	=> 'ACP_AVATAR_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_AVATAR_SETTINGS',

						'avatar_min_width'		=> array('lang' => 'MIN_AVATAR_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,),
						'avatar_min_height'		=> array('lang' => 'MIN_AVATAR_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,),
						'avatar_max_width'		=> array('lang' => 'MAX_AVATAR_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,),
						'avatar_max_height'		=> array('lang' => 'MAX_AVATAR_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,),

						'allow_avatar'			=> array('lang' => 'ALLOW_AVATARS',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_avatar_local'	=> array('lang' => 'ALLOW_AVATAR_LOCAL',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_avatar_upload'	=> array('lang' => 'ALLOW_AVATAR_UPLOAD',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_avatar_remote_upload'=> array('lang' => 'ALLOW_AVATAR_REMOTE_UPLOAD', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
						'allow_avatar_remote'	=> array('lang' => 'ALLOW_AVATAR_REMOTE',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'avatar_filesize'		=> array('lang' => 'MAX_AVATAR_FILESIZE',	'validate' => 'int:0',	'type' => 'text:4:10', 'explain' => true, 'append' => ' ' . $user->lang['BYTES']),
						'avatar_min'			=> array('lang' => 'MIN_AVATAR_SIZE',		'validate' => 'int:0',	'type' => 'dimension:3:4', 'explain' => true, 'append' => ' ' . $user->lang['PIXEL']),
						'avatar_max'			=> array('lang' => 'MAX_AVATAR_SIZE',		'validate' => 'int:0',	'type' => 'dimension:3:4', 'explain' => true, 'append' => ' ' . $user->lang['PIXEL']),
					)
				);
			break;

			case 'message':
				$display_vars = array(
					'title'	=> 'ACP_MESSAGE_SETTINGS',
					'lang'	=> 'ucp',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'allow_privmsg'			=> array('lang' => 'BOARD_PM',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'pm_max_boxes'			=> array('lang' => 'BOXES_MAX',				'validate' => 'int:0',	'type' => 'text:4:4', 'explain' => true),
						'pm_max_msgs'			=> array('lang' => 'BOXES_LIMIT',			'validate' => 'int:0',	'type' => 'text:4:4', 'explain' => true),
						'full_folder_action'	=> array('lang' => 'FULL_FOLDER_ACTION',	'validate' => 'int',	'type' => 'select', 'method' => 'full_folder_select', 'explain' => true),
						'pm_edit_time'			=> array('lang' => 'PM_EDIT_TIME',			'validate' => 'int:0',	'type' => 'text:5:5', 'explain' => true, 'append' => ' ' . $user->lang['MINUTES']),
						'pm_max_recipients'		=> array('lang' => 'PM_MAX_RECIPIENTS',		'validate' => 'int:0',	'type' => 'text:5:5', 'explain' => true),

						'legend2'				=> 'GENERAL_OPTIONS',
						'allow_mass_pm'			=> array('lang' => 'ALLOW_MASS_PM',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'auth_bbcode_pm'		=> array('lang' => 'ALLOW_BBCODE_PM',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'auth_smilies_pm'		=> array('lang' => 'ALLOW_SMILIES_PM',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_pm_attach'		=> array('lang' => 'ALLOW_PM_ATTACHMENTS',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_pm'			=> array('lang' => 'ALLOW_SIG_PM',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'auth_img_pm'			=> array('lang' => 'ALLOW_IMG_PM',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'auth_flash_pm'			=> array('lang' => 'ALLOW_FLASH_PM',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'enable_pm_icons'		=> array('lang' => 'ENABLE_PM_ICONS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend3'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'post':
				$display_vars = array(
					'title'	=> 'ACP_POST_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_OPTIONS',
						'allow_topic_notify'	=> array('lang' => 'ALLOW_TOPIC_NOTIFY',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_forum_notify'	=> array('lang' => 'ALLOW_FORUM_NOTIFY',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'enable_topic_icons'	=> array('lang' => 'ENABLE_TOPIC_ICONS',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_bbcode'			=> array('lang' => 'ALLOW_BBCODE',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_post_flash'		=> array('lang' => 'ALLOW_POST_FLASH',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_smilies'			=> array('lang' => 'ALLOW_SMILIES',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_post_links'		=> array('lang' => 'ALLOW_POST_LINKS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_nocensors'		=> array('lang' => 'ALLOW_NO_CENSORS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_bookmarks'		=> array('lang' => 'ALLOW_BOOKMARKS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'enable_post_confirm'	=> array('lang' => 'VISUAL_CONFIRM_POST',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend2'				=> 'POSTING',
						'bump_type'				=> false,
						'edit_time'				=> array('lang' => 'EDIT_TIME',				'validate' => 'int:0',		'type' => 'text:5:5', 'explain' => true, 'append' => ' ' . $user->lang['MINUTES']),
						'delete_time'			=> array('lang' => 'DELETE_TIME',			'validate' => 'int:0',		'type' => 'text:5:5', 'explain' => true, 'append' => ' ' . $user->lang['MINUTES']),
						'display_last_edited'	=> array('lang' => 'DISPLAY_LAST_EDITED',	'validate' => 'bool',		'type' => 'radio:yes_no', 'explain' => true),
						'flood_interval'		=> array('lang' => 'FLOOD_INTERVAL',		'validate' => 'int:0',		'type' => 'text:3:10', 'explain' => true, 'append' => ' ' . $user->lang['SECONDS']),
						'merge_interval'		=> array('lang' => 'MERGE_INTERVAL',		'validate' => 'int',		'type' => 'text:3:4', 'explain' => true, 'append' => ' ' . $user->lang['HOURS']),
						'bump_interval'			=> array('lang' => 'BUMP_INTERVAL',			'validate' => 'int:0',		'type' => 'custom', 'method' => 'bump_interval', 'explain' => true),
						'topics_per_page'		=> array('lang' => 'TOPICS_PER_PAGE',		'validate' => 'int:1',		'type' => 'text:3:4', 'explain' => false),
						'posts_per_page'		=> array('lang' => 'POSTS_PER_PAGE',		'validate' => 'int:1',		'type' => 'text:3:4', 'explain' => false),
						'smilies_per_page'		=> array('lang' => 'SMILIES_PER_PAGE',		'validate' => 'int:1',		'type' => 'text:3:4', 'explain' => false),
						'max_poll_options'		=> array('lang' => 'MAX_POLL_OPTIONS',		'validate' => 'int:2:127',	'type' => 'text:4:4', 'explain' => false),
						'min_post_chars'		=> array('lang' => 'MIN_CHAR_LIMIT',		'validate' => 'int:1',		'type' => 'text:4:6', 'explain' => true),
						'max_post_chars'		=> array('lang' => 'CHAR_LIMIT',			'validate' => 'int:0',		'type' => 'text:4:6', 'explain' => true),
						'max_post_smilies'		=> array('lang' => 'SMILIES_LIMIT',			'validate' => 'int:0',		'type' => 'text:4:4', 'explain' => true),
						'max_post_urls'			=> array('lang' => 'MAX_POST_URLS',			'validate' => 'int:0',		'type' => 'text:5:4', 'explain' => true),
						'min_post_font_size'	=> array('lang' => 'MIN_POST_FONT_SIZE',	'validate' => 'int:0',		'type' => 'text:5:4', 'explain' => true, 'append' => ' %'),
						'max_post_font_size'	=> array('lang' => 'MAX_POST_FONT_SIZE',	'validate' => 'int:0',		'type' => 'text:5:4', 'explain' => true, 'append' => ' %'),
						'max_quote_depth'		=> array('lang' => 'QUOTE_DEPTH_LIMIT',		'validate' => 'int:-1:9',	'type' => 'text:4:4', 'explain' => true),
						'max_spoiler_depth'		=> array('lang' => 'SPOILER_DEPTH_LIMIT',	'validate' => 'int:-1:9',	'type' => 'text:4:4', 'explain' => true),
						'max_post_imgs'			=> array('lang' => 'MAX_POST_IMGS',			'validate' => 'int:0',		'type' => 'text:5:4', 'explain' => true),
						'max_post_img_width'	=> array('lang' => 'MAX_POST_IMG_WIDTH',	'validate' => 'int:0',		'type' => 'text:5:4', 'explain' => true, 'append' => ' ' . $user->lang['PIXEL']),
						'max_post_img_height'	=> array('lang' => 'MAX_POST_IMG_HEIGHT',	'validate' => 'int:0',		'type' => 'text:5:4', 'explain' => true, 'append' => ' ' . $user->lang['PIXEL']),
						'posting_topic_review'	=> array('lang' => 'POSTING_TOPIC_REVIEW',	'validate' => 'bool',		'type' => 'radio:yes_no', 'explain' => false),

						'legend3'							=> 'QUICK_REPLY',
						'allow_quick_reply'					=> array('lang' => 'ALLOW_QUICK_REPLY',				'validate' => 'int',	'type' => 'select', 'method' => 'allow_quick_reply_for', 'explain' => true),
						'allow_quick_reply_subject'			=> array('lang' => 'ALLOW_QUICK_REPLY_SUBJECT',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_quick_reply_checkboxes'		=> array('lang' => 'ALLOW_QUICK_REPLY_CHECKBOXES',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_quick_reply_attachbox'		=> array('lang' => 'ALLOW_QUICK_REPLY_ATTACHBOX',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_quick_reply_smilies'			=> array('lang' => 'ALLOW_QUICK_REPLY_SMILIES',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_quick_full_quote'			=> array('lang' => 'ALLOW_QUICK_FULL_QUOTE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'allow_quick_post'					=> array('lang' => 'ALLOW_QUICK_TOPIC',				'validate' => 'int',	'type' => 'select', 'method' => 'allow_quick_reply_for', 'explain' => true),
						'allow_quick_post_icons'			=> array('lang' => 'ALLOW_QUICK_REPLY_ICONS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_quick_post_checkboxes'		=> array('lang' => 'ALLOW_QUICK_REPLY_CHECKBOXES',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_quick_post_attachbox'		=> array('lang' => 'ALLOW_QUICK_REPLY_ATTACHBOX',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_quick_post_smilies'			=> array('lang' => 'ALLOW_QUICK_REPLY_SMILIES',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend4'							=> 'EXTERNAL_LINKS',
						'external_links_newwindow'			=> array('lang' => 'EXTERNAL_LINKS_NEWWINDOW',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'external_links_newwindow_exclude'	=> array('lang' => 'EXTERNAL_LINKS_EXCLUDE',	'validate' => 'string',	'type' => 'textarea:3:1000', 'explain' => true),
						'external_links_nofollow'			=> array('lang' => 'EXTERNAL_LINKS_NOFOLLOW',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'external_links_nofollow_exclude'	=> array('lang' => 'EXTERNAL_LINKS_EXCLUDE',	'validate' => 'string',	'type' => 'textarea:3:1000', 'explain' => true),

						'legend4'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'signature':
				$display_vars = array(
					'title'	=> 'ACP_SIGNATURE_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_OPTIONS',
						'allow_sig'				=> array('lang' => 'ALLOW_SIG',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_bbcode'		=> array('lang' => 'ALLOW_SIG_BBCODE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_img'			=> array('lang' => 'ALLOW_SIG_IMG',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_flash'		=> array('lang' => 'ALLOW_SIG_FLASH',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_smilies'		=> array('lang' => 'ALLOW_SIG_SMILIES',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_links'		=> array('lang' => 'ALLOW_SIG_LINKS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend2'				=> 'GENERAL_SETTINGS',
						'max_sig_chars'			=> array('lang' => 'MAX_SIG_LENGTH',		'validate' => 'int:0',	'type' => 'text:5:4', 'explain' => true),
						'max_sig_lines'			=> array('lang' => 'MAX_SIG_LINES',			'validate' => 'int:0',	'type' => 'text:5:4', 'explain' => true),
						'max_sig_urls'			=> array('lang' => 'MAX_SIG_URLS',			'validate' => 'int:0',	'type' => 'text:5:4', 'explain' => true),
						'min_sig_font_size'		=> array('lang' => 'MIN_SIG_FONT_SIZE',		'validate' => 'int:0',	'type' => 'text:5:4', 'explain' => true, 'append' => ' %'),
						'max_sig_font_size'		=> array('lang' => 'MAX_SIG_FONT_SIZE',		'validate' => 'int:0',	'type' => 'text:5:4', 'explain' => true, 'append' => ' %'),
						'max_sig_smilies'		=> array('lang' => 'MAX_SIG_SMILIES',		'validate' => 'int:0',	'type' => 'text:5:4', 'explain' => true),
						'max_sig_imgs'			=> array('lang' => 'MAX_SIG_IMGS',			'validate' => 'int:0',	'type' => 'text:5:4', 'explain' => true),
						'max_sig_img_width'		=> array('lang' => 'MAX_SIG_IMG_WIDTH',		'validate' => 'int:0',	'type' => 'text:5:4', 'explain' => true, 'append' => ' ' . $user->lang['PIXEL']),
						'max_sig_img_height'	=> array('lang' => 'MAX_SIG_IMG_HEIGHT',	'validate' => 'int:0',	'type' => 'text:5:4', 'explain' => true, 'append' => ' ' . $user->lang['PIXEL']),

						'legend3'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'registration':
				$display_vars = array(
					'title'	=> 'ACP_REGISTER_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'max_name_chars'		=> array('lang' => 'USERNAME_LENGTH', 'validate' => 'int:8:180', 'type' => false, 'method' => false, 'explain' => false,),
						'max_pass_chars'		=> array('lang' => 'PASSWORD_LENGTH', 'validate' => 'int:8:255', 'type' => false, 'method' => false, 'explain' => false,),

						'require_activation'	=> array('lang' => 'ACC_ACTIVATION',	'validate' => 'int',	'type' => 'select', 'method' => 'select_acc_activation', 'explain' => true),
						'new_member_post_limit'	=> array('lang' => 'NEW_MEMBER_POST_LIMIT', 'validate' => 'int:0:255', 'type' => 'text:4:4', 'explain' => true, 'append' => ' ' . $user->lang['POSTS']),
						'new_member_group_default'=> array('lang' => 'NEW_MEMBER_GROUP_DEFAULT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
						'min_name_chars'		=> array('lang' => 'USERNAME_LENGTH',	'validate' => 'int:1',	'type' => 'custom:5:180', 'method' => 'username_length', 'explain' => true),
						'min_pass_chars'		=> array('lang' => 'PASSWORD_LENGTH',	'validate' => 'int:1',	'type' => 'custom', 'method' => 'password_length', 'explain' => true),
						'allow_name_chars'		=> array('lang' => 'USERNAME_CHARS',	'validate' => 'string',	'type' => 'select', 'method' => 'select_username_chars', 'explain' => true),
						'pass_complex'			=> array('lang' => 'PASSWORD_TYPE',		'validate' => 'string',	'type' => 'select', 'method' => 'select_password_chars', 'explain' => true),
						'chg_passforce'			=> array('lang' => 'FORCE_PASS_CHANGE',	'validate' => 'int:0',	'type' => 'text:3:3', 'explain' => true, 'append' => ' ' . $user->lang['DAYS']),

						'legend2'				=> 'GENERAL_OPTIONS',
						'allow_namechange'		=> array('lang' => 'ALLOW_NAME_CHANGE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_emailreuse'		=> array('lang' => 'ALLOW_EMAIL_REUSE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'enable_confirm'		=> array('lang' => 'VISUAL_CONFIRM_REG',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'max_login_attempts'	=> array('lang' => 'MAX_LOGIN_ATTEMPTS',	'validate' => 'int:0',	'type' => 'text:3:3', 'explain' => true),
						'max_reg_attempts'		=> array('lang' => 'REG_LIMIT',				'validate' => 'int:0',	'type' => 'text:4:4', 'explain' => true),

						'legend3'			=> 'ACP_SUBMIT_CHANGES',
					)
				);
				// Login via E-Mail
				if ($config['login_via_email_enable'])
				{
					unset($display_vars['vars']['allow_emailreuse']);
				}
			break;

			case 'feed':
				$display_vars = array(
					'title'	=> 'ACP_FEED_MANAGEMENT',
					'vars'	=> array(
						'legend1'					=> 'ACP_FEED_GENERAL',
						'feed_enable'				=> array('lang' => 'ACP_FEED_ENABLE',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_item_statistics'		=> array('lang' => 'ACP_FEED_ITEM_STATISTICS',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true),
						'feed_http_auth'			=> array('lang' => 'ACP_FEED_HTTP_AUTH',			'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true),

						'legend2'					=> 'ACP_FEED_POST_BASED',
						'feed_limit_post'			=> array('lang' => 'ACP_FEED_LIMIT',				'validate' => 'int:5',	'type' => 'text:3:4',				'explain' => true),
						'feed_overall'				=> array('lang' => 'ACP_FEED_OVERALL',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_forum'				=> array('lang' => 'ACP_FEED_FORUM',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_topic'				=> array('lang' => 'ACP_FEED_TOPIC',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),

						'legend3'					=> 'ACP_FEED_TOPIC_BASED',
						'feed_limit_topic'			=> array('lang' => 'ACP_FEED_LIMIT',				'validate' => 'int:5',	'type' => 'text:3:4',				'explain' => true),
						'feed_topics_new'			=> array('lang' => 'ACP_FEED_TOPICS_NEW',			'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_topics_active'		=> array('lang' => 'ACP_FEED_TOPICS_ACTIVE',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_news_id'				=> array('lang' => 'ACP_FEED_NEWS',					'validate' => 'string',	'type' => 'custom', 'method' => 'select_news_forums', 'explain' => true),

						'legend4'					=> 'ACP_FEED_SETTINGS_OTHER',
						'feed_overall_forums'		=> array('lang'	=> 'ACP_FEED_OVERALL_FORUMS',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_exclude_id'			=> array('lang' => 'ACP_FEED_EXCLUDE_ID',			'validate' => 'string',	'type' => 'custom', 'method' => 'select_exclude_forums', 'explain' => true),
					)
				);
			break;

			case 'load':
				$display_vars = array(
					'title'	=> 'ACP_LOAD_SETTINGS',
					'vars'	=> array(
						'legend1'			=> 'GENERAL_SETTINGS',
						'limit_load'		=> array('lang' => 'LIMIT_LOAD',		'validate' => 'string',	'type' => 'text:4:4', 'explain' => true),
						'session_length'	=> array('lang' => 'SESSION_LENGTH',	'validate' => 'int:60',	'type' => 'text:5:10', 'explain' => true, 'append' => ' ' . $user->lang['SECONDS']),
						'active_sessions'	=> array('lang' => 'LIMIT_SESSIONS',	'validate' => 'int:0',	'type' => 'text:4:4', 'explain' => true),
						'load_online_time'	=> array('lang' => 'ONLINE_LENGTH',		'validate' => 'int:0',	'type' => 'text:4:3', 'explain' => true, 'append' => ' ' . $user->lang['MINUTES']),

						'legend2'				=> 'GENERAL_OPTIONS',
						'load_db_track'			=> array('lang' => 'YES_POST_MARKING',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_db_lastread'		=> array('lang' => 'YES_READ_MARKING',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_anon_lastread'	=> array('lang' => 'YES_ANON_READ_MARKING',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_online'			=> array('lang' => 'YES_ONLINE',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_onlinetrack'		=> array('lang' => 'YES_ONLINE_TRACK',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_birthdays'		=> array('lang' => 'YES_BIRTHDAYS',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_unreads_search'	=> array('lang' => 'YES_UNREAD_SEARCH',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_moderators'		=> array('lang' => 'YES_MODERATORS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_jumpbox'			=> array('lang' => 'YES_JUMPBOX',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_user_activity'	=> array('lang' => 'LOAD_USER_ACTIVITY',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_tplcompile'		=> array('lang' => 'RECOMPILE_STYLES',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend3'				=> 'CUSTOM_PROFILE_FIELDS',
						'load_cpf_memberlist'	=> array('lang' => 'LOAD_CPF_MEMBERLIST',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_cpf_viewprofile'	=> array('lang' => 'LOAD_CPF_VIEWPROFILE',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_cpf_viewtopic'	=> array('lang' => 'LOAD_CPF_VIEWTOPIC',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend4'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'auth':
				$display_vars = array(
					'title'	=> 'ACP_AUTH_SETTINGS',
					'vars'	=> array(
						'legend1'		=> 'ACP_AUTH_SETTINGS',
						'auth_method'	=> array('lang' => 'AUTH_METHOD',	'validate' => 'string',	'type' => 'select', 'method' => 'select_auth_method', 'explain' => false)
					)
				);
			break;

			case 'server':
				$display_vars = array(
					'title'	=> 'ACP_SERVER_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_SERVER_SETTINGS',
						'gzip_compress'			=> array('lang' => 'ENABLE_GZIP',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend2'				=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'security':
				$display_vars = array(
					'title'	=> 'ACP_SECURITY_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_SECURITY_SETTINGS',
						'allow_autologin'		=> array('lang' => 'ALLOW_AUTOLOGIN',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'max_autologin_time'	=> array('lang' => 'AUTOLOGIN_LENGTH',		'validate' => 'int:0',	'type' => 'text:5:5', 'explain' => true, 'append' => ' ' . $user->lang['DAYS']),
						'ip_check'				=> array('lang' => 'IP_VALID',				'validate' => 'int',	'type' => 'custom', 'method' => 'select_ip_check', 'explain' => true),
						'browser_check'			=> array('lang' => 'BROWSER_VALID',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'forwarded_for_check'	=> array('lang' => 'FORWARDED_FOR_VALID',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'referer_validation'	=> array('lang' => 'REFERER_VALID',		'validate' => 'int:0:3','type' => 'custom', 'method' => 'select_ref_check', 'explain' => true),
						'check_dnsbl'			=> array('lang' => 'CHECK_DNSBL',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'email_check_mx'		=> array('lang' => 'EMAIL_CHECK_MX',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'max_pass_chars'		=> array('lang' => 'PASSWORD_LENGTH', 'validate' => 'int:8:255', 'type' => false, 'method' => false, 'explain' => false,),
						'min_pass_chars'		=> array('lang' => 'PASSWORD_LENGTH',	'validate' => 'int:1',	'type' => 'custom', 'method' => 'password_length', 'explain' => true),
						'pass_complex'			=> array('lang' => 'PASSWORD_TYPE',			'validate' => 'string',	'type' => 'select', 'method' => 'select_password_chars', 'explain' => true),
						'chg_passforce'			=> array('lang' => 'FORCE_PASS_CHANGE',		'validate' => 'int:0',	'type' => 'text:3:3', 'explain' => true, 'append' => ' ' . $user->lang['DAYS']),
						'max_login_attempts'	=> array('lang' => 'MAX_LOGIN_ATTEMPTS',	'validate' => 'int:0',	'type' => 'text:3:3', 'explain' => true),
						'ip_login_limit_max'	=> array('lang' => 'IP_LOGIN_LIMIT_MAX',	'validate' => 'int:0',	'type' => 'text:3:3', 'explain' => true),
						'ip_login_limit_time'	=> array('lang' => 'IP_LOGIN_LIMIT_TIME',	'validate' => 'int:0',	'type' => 'text:5:5', 'explain' => true, 'append' => ' ' . $user->lang['SECONDS']),
						'ip_login_limit_use_forwarded'	=> array('lang' => 'IP_LOGIN_LIMIT_USE_FORWARDED',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'tpl_allow_php'			=> array('lang' => 'TPL_ALLOW_PHP',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'form_token_lifetime'	=> array('lang' => 'FORM_TIME_MAX',			'validate' => 'int:-1',	'type' => 'text:5:5', 'explain' => true, 'append' => ' ' . $user->lang['SECONDS']),
						'form_token_sid_guests'	=> array('lang' => 'FORM_SID_GUESTS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

					)
				);
			break;

			case 'email':
				$display_vars = array(
					'title'	=> 'ACP_EMAIL_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'email_enable'			=> array('lang' => 'ENABLE_EMAIL',			'validate' => 'bool',	'type' => 'radio:enabled_disabled', 'explain' => true),
						'board_email_form'		=> array('lang' => 'BOARD_EMAIL_FORM',		'validate' => 'bool',	'type' => 'radio:enabled_disabled', 'explain' => true),
						'email_package_size'	=> array('lang' => 'EMAIL_PACKAGE_SIZE',	'validate' => 'int:0',	'type' => 'text:5:5', 'explain' => true),
						'board_contact'			=> array('lang' => 'CONTACT_EMAIL',			'validate' => 'email',	'type' => 'text:25:100', 'explain' => true),
						'board_contact_name'	=> array('lang' => 'CONTACT_EMAIL_NAME',	'validate' => 'string',	'type' => 'text:25:100', 'explain' => false),
						'board_email'			=> array('lang' => 'ADMIN_EMAIL',			'validate' => 'email',	'type' => 'text:25:100', 'explain' => true),
						'email_force_sender'	=> array('lang' => 'EMAIL_FORCE_SENDER',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'board_email_sig'		=> array('lang' => 'EMAIL_SIG',				'validate' => 'string',	'type' => 'textarea:5:30', 'explain' => true),
						'board_hide_emails'		=> array('lang' => 'BOARD_HIDE_EMAILS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend2'				=> 'SMTP_SETTINGS',
						'smtp_delivery'			=> array('lang' => 'USE_SMTP',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'smtp_host'				=> array('lang' => 'SMTP_SERVER',			'validate' => 'string',	'type' => 'text:25:50', 'explain' => true),
						'smtp_port'				=> array('lang' => 'SMTP_PORT',				'validate' => 'int:0',	'type' => 'text:4:5', 'explain' => true),
						'smtp_verify_cert'		=> array('lang' => 'SMTP_VERIFY_CERT',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'smtp_auth_method'		=> array('lang' => 'SMTP_AUTH_METHOD',		'validate' => 'string',	'type' => 'select', 'method' => 'mail_auth_select', 'explain' => true),
						'smtp_username'			=> array('lang' => 'SMTP_USERNAME',			'validate' => 'string',	'type' => 'text:25:255', 'explain' => true),
						'smtp_password'			=> array('lang' => 'SMTP_PASSWORD',			'validate' => 'string',	'type' => 'text:25:255', 'explain' => true),

						'legend3'				=> 'ACP_SUBMIT_CHANGES',
						'send_test_email'		=> array('lang' => 'SEND_TEST_EMAIL',		'validate' => 'bool',	'type' => 'custom', 'method' => 'send_test_email', 'explain' => true),
					)
				);
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;
		$error = array();

		// Get configuration values and decode HTML special chars if type of value is HTML
		$cfg_array = array();
		if (!isset($_REQUEST['config']))
		{
			$cfg_array = $config;
		}
		else
		{
			$cfg_array = utf8_normalize_nfc(request_var('config', array('' => ''), true));
			foreach ($display_vars['vars'] as $config_name => $config_vars)
			{
				if (isset($cfg_array[$config_name]) && isset($config_vars['type']) && strpos($config_vars['type'], 'html') === 0)
				{
					$cfg_array[$config_name] = htmlspecialchars_decode($cfg_array[$config_name]);
				}
			}
		}

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			if (in_array($config_name, ['auth_method', 'feed_news_id', 'feed_exclude_id', 'send_test_email']))
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				set_config($config_name, $config_value);
			}
		}

		// Store news and exclude ids
		if ($mode == 'feed' && $submit)
		{
			$cache->destroy('_feed_news_forum_ids');
			$cache->destroy('_feed_excluded_forum_ids');

			$this->store_feed_forums(FORUM_OPTION_FEED_NEWS, 'feed_news_id');
			$this->store_feed_forums(FORUM_OPTION_FEED_EXCLUDE, 'feed_exclude_id');
		}

		if ($mode == 'auth')
		{
			// Retrieve a list of auth plugins and check their config values
			$auth_plugins = array();

			$dp = @opendir(PHPBB_ROOT_PATH . 'includes/auth');

			if ($dp)
			{
				while (($file = readdir($dp)) !== false)
				{
					if (preg_match('#^auth_(.*?)\.php' . '$#', $file))
					{
						$auth_plugins[] = basename(preg_replace('#^auth_(.*?)\.php' . '$#', '\1', $file));
					}
				}
				closedir($dp);

				sort($auth_plugins);
			}

			$updated_auth_settings = false;
			$old_auth_config = array();
			foreach ($auth_plugins as $method)
			{
				if ($method && file_exists(PHPBB_ROOT_PATH . 'includes/auth/auth_' . $method . '.php'))
				{
					require_once(PHPBB_ROOT_PATH . 'includes/auth/auth_' . $method . '.php');

					$method = 'acp_' . $method;
					if (function_exists($method))
					{
						if ($fields = $method($this->new_config))
						{
							// Check if we need to create config fields for this plugin and save config when submit was pressed
							foreach ($fields['config'] as $field)
							{
								if (!isset($config[$field]))
								{
									set_config($field, '');
								}

								if (!isset($cfg_array[$field]) || strpos($field, 'legend') !== false)
								{
									continue;
								}

								$old_auth_config[$field] = $this->new_config[$field];
								$config_value = $cfg_array[$field];
								$this->new_config[$field] = $config_value;

								if ($submit)
								{
									$updated_auth_settings = true;
									set_config($field, $config_value);
								}
							}
						}
						unset($fields);
					}
				}
			}

			if ($submit && (($cfg_array['auth_method'] != $this->new_config['auth_method']) || $updated_auth_settings))
			{
				$method = basename($cfg_array['auth_method']);
				if ($method && in_array($method, $auth_plugins))
				{
					require_once(PHPBB_ROOT_PATH . 'includes/auth/auth_' . $method . '.php');

					$method = 'init_' . $method;
					if (function_exists($method))
					{
						if ($error = $method())
						{
							foreach ($old_auth_config as $config_name => $config_value)
							{
								set_config($config_name, $config_value);
							}
							trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
					set_config('auth_method', basename($cfg_array['auth_method']));
				}
				else
				{
					trigger_error('NO_AUTH_PLUGIN', E_USER_ERROR);
				}
			}
		}

		if ($mode == 'email' && request_var('send_test_email', false))
		{
			if ($config['email_enable'])
			{
				require_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.php');

				$messenger = new messenger(false);
				$messenger->template('test');
				$messenger->to($user->data['user_email'], $user->data['username']);
				$messenger->anti_abuse_headers($config, $user);
				$messenger->assign_vars(array(
					'USERNAME'	=> htmlspecialchars_decode($user->data['username']),
					'MESSAGE'	=> htmlspecialchars_decode(request_var('send_test_email_text', '', true)),
				));
				$messenger->send(NOTIFY_EMAIL);

				trigger_error($user->lang['CONFIG_UPDATED'] . '<br>' . $user->lang('TEST_EMAIL_SENT') . adm_back_link($this->u_action));
			}
			else
			{
				$user->add_lang('memberlist');
				trigger_error($user->lang('EMAIL_DISABLED') . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		if ($submit)
		{
			add_log('admin', 'LOG_CONFIG_' . strtoupper($mode));

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->tpl_name = 'acp_board';
		$this->page_title = $display_vars['title'];

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}

		if ($mode == 'auth')
		{
			$template->assign_var('S_AUTH', true);

			foreach ($auth_plugins as $method)
			{
				if ($method && file_exists(PHPBB_ROOT_PATH . 'includes/auth/auth_' . $method . '.php'))
				{
					$method = 'acp_' . $method;
					if (function_exists($method))
					{
						$fields = $method($this->new_config);

						if ($fields['tpl'])
						{
							$template->assign_block_vars('auth_tpl', array(
								'TPL'	=> $fields['tpl'])
							);
						}
						unset($fields);
					}
				}
			}
		}
	}

	/**
	* Select auth method
	*/
	function select_auth_method($selected_method, $key = '')
	{
		$auth_plugins = array();

		$dp = @opendir(PHPBB_ROOT_PATH . 'includes/auth');

		if (!$dp)
		{
			return '';
		}

		while (($file = readdir($dp)) !== false)
		{
			if (preg_match('#^auth_(.*?)\.php' . '$#', $file))
			{
				$auth_plugins[] = preg_replace('#^auth_(.*?)\.php' . '$#', '\1', $file);
			}
		}
		closedir($dp);

		sort($auth_plugins);

		$auth_select = '';
		foreach ($auth_plugins as $method)
		{
			$selected = ($selected_method == $method) ? ' selected="selected"' : '';
			$auth_select .= '<option value="' . $method . '"' . $selected . '>' . ucfirst($method) . '</option>';
		}

		return $auth_select;
	}

	/**
	* Select mail authentication method
	*/
	function mail_auth_select($selected_method, $key = '')
	{
		global $user;

		$auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5', 'DIGEST-MD5');
		$s_smtp_auth_options = '';

		foreach ($auth_methods as $method)
		{
			$s_smtp_auth_options .= '<option value="' . $method . '"' . (($selected_method == $method) ? ' selected="selected"' : '') . '>' . $user->lang['SMTP_' . str_replace('-', '_', $method)] . '</option>';
		}

		return $s_smtp_auth_options;
	}

	/**
	* Select full folder action
	*/
	function full_folder_select($value, $key = '')
	{
		global $user;

		return '<option value="1"' . (($value == 1) ? ' selected="selected"' : '') . '>' . $user->lang['DELETE_OLDEST_MESSAGES'] . '</option><option value="2"' . (($value == 2) ? ' selected="selected"' : '') . '>' . $user->lang['HOLD_NEW_MESSAGES_SHORT'] . '</option>';
	}

	/**
	* Select ip validation
	*/
	function select_ip_check($value, $key = '')
	{
		$radio_ary = array(4 => 'ALL', 3 => 'CLASS_C', 2 => 'CLASS_B', 0 => 'NO_IP_VALIDATION');

		return h_radio('config[ip_check]', $radio_ary, $value, $key);
	}

	/**
	* Select referer validation
	*/
	function select_ref_check($value, $key = '')
	{
		$radio_ary = array(REFERER_VALIDATE_PATH => 'REF_PATH', REFERER_VALIDATE_HOST => 'REF_HOST', REFERER_VALIDATE_NONE => 'NO_REF_VALIDATION');

		return h_radio('config[referer_validation]', $radio_ary, $value, $key);
	}

	/**
	* Select account activation method
	*/
	function select_acc_activation($selected_value, $value)
	{
		global $user, $config;

		$act_ary = array(
		  'ACC_DISABLE' => USER_ACTIVATION_DISABLE,
		  'ACC_NONE' => USER_ACTIVATION_NONE,
		);
		if ($config['email_enable'])
		{
			$act_ary['ACC_USER'] = USER_ACTIVATION_SELF;
			$act_ary['ACC_ADMIN'] = USER_ACTIVATION_ADMIN;
		}
		$act_options = '';

		foreach ($act_ary as $key => $value)
		{
			$selected = ($selected_value == $value) ? ' selected="selected"' : '';
			$act_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$key] . '</option>';
		}

		return $act_options;
	}

	/**
	* Select days
	*/
	function select_days($value, $key, $zero)
	{
		global $user, $config;

		$limit_days = array(0 => $user->lang['ALL_DAYS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
		if (!$zero) unset($limit_days[0]);
		$act_options = '';
		foreach ($limit_days as $days => $title)
		{
			$selected = ($value == $days) ? ' selected="selected"' : '';
			$act_options .= '<option value="' . $days . '"' . $selected . '>' . $title . '</option>';
		}

		return $act_options;
	}

	/**
	* Login via E-Mail options
	*/
	function login_via_email_options($value, $key = '')
	{
		global $config, $user;

		$radio_ary = array(
			LOGIN_VIA_EMAIL_YES		=> 'YES',
			LOGIN_VIA_EMAIL_NO		=> 'NO',
			LOGIN_VIA_EMAIL_SILENT	=> 'LOGIN_VIA_EMAIL_SILENT',
			LOGIN_VIA_EMAIL_ONLY	=> 'LOGIN_VIA_EMAIL_ONLY',
		);

		return h_radio('config[login_via_email_enable]', $radio_ary, $value, $key);
	}

	/**
	* Maximum/Minimum username length
	*/
	function username_length($value, $key = '')
	{
		global $user;

		return '<input id="' . $key . '" type="text" size="3" maxlength="3" name="config[min_name_chars]" value="' . $value . '" /> ' . $user->lang['MIN_CHARS'] . '&nbsp;&nbsp;<input type="text" size="3" maxlength="3" name="config[max_name_chars]" value="' . $this->new_config['max_name_chars'] . '" /> ' . $user->lang['MAX_CHARS'];
	}

	/**
	* Allowed chars in usernames
	*/
	function select_username_chars($selected_value, $key)
	{
		global $user;

		$user_char_ary = array('USERNAME_UNICHARS_SPACERS', 'USERNAME_UNICHARS_NOSPACE', 'USERNAME_LATCHARS_SPACERS', 'USERNAME_LATCHARS_NOSPACE');
		$user_char_options = '';

		foreach ($user_char_ary as $user_type)
		{
			$selected = ($selected_value == $user_type) ? ' selected="selected"' : '';
			$user_char_options .= '<option value="' . $user_type . '"' . $selected . '>' . $user->lang[$user_type] . '</option>';
		}

		return $user_char_options;
	}

	/**
	* Maximum/Minimum password length
	*/
	function password_length($value, $key)
	{
		global $user;

		return '<input id="' . $key . '" type="text" size="3" maxlength="3" name="config[min_pass_chars]" value="' . $value . '" /> ' . $user->lang['MIN_CHARS'] . '&nbsp;&nbsp;<input type="text" size="3" maxlength="3" name="config[max_pass_chars]" value="' . $this->new_config['max_pass_chars'] . '" /> ' . $user->lang['MAX_CHARS'];
	}

	/**
	* Required chars in passwords
	*/
	function select_password_chars($selected_value, $key)
	{
		global $user;

		$pass_type_ary = array('PASS_TYPE_ANY', 'PASS_TYPE_CASE', 'PASS_TYPE_ALPHA', 'PASS_TYPE_SYMBOL');
		$pass_char_options = '';
		foreach ($pass_type_ary as $pass_type)
		{
			$selected = ($selected_value == $pass_type) ? ' selected="selected"' : '';
			$pass_char_options .= '<option value="' . $pass_type . '"' . $selected . '>' . $user->lang[$pass_type] . '</option>';
		}

		return $pass_char_options;
	}

	/**
	* Select bump interval
	*/
	function bump_interval($value, $key)
	{
		global $user;

		$s_bump_type = '';
		$types = array('m' => 'MINUTES', 'h' => 'HOURS', 'd' => 'DAYS');
		foreach ($types as $type => $lang)
		{
			$selected = ($this->new_config['bump_type'] == $type) ? ' selected="selected"' : '';
			$s_bump_type .= '<option value="' . $type . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		return '<input id="' . $key . '" type="text" size="3" maxlength="4" name="config[bump_interval]" value="' . $value . '" />&nbsp;<select name="config[bump_type]">' . $s_bump_type . '</select>';
	}

	/**
	* Quick reply
	*/
	function allow_quick_reply_for($value, $key = '')
	{
		global $user;

		$options_ary = array(0 => 'ALLOW_QUICK_REPLY_NONE', 1 => 'ALLOW_QUICK_REPLY_REG', 2 => 'ALLOW_QUICK_REPLY_ALL');

		$allow_quick_reply_options = '';
		foreach ($options_ary as $key_value=>$option)
		{
			$selected = ($value == $key_value) ? ' selected="selected"' : '';
			$allow_quick_reply_options .= '<option value="' . $key_value . '"' . $selected . '>' . $user->lang[$option] . '</option>';
		}

		return $allow_quick_reply_options;
	}

	/**
	* Board disable option and message
	*/
	function board_disable($value, $key)
	{
		global $user;

		$radio_ary = array(1 => 'YES', 0 => 'NO');

		return h_radio('config[board_disable]', $radio_ary, $value) . '<br /><input id="' . $key . '" type="text" name="config[board_disable_msg]" maxlength="255" size="40" value="' . $this->new_config['board_disable_msg'] . '" />';
	}

	/**
	* Select default dateformat
	*/
	function dateformat_select($value, $key)
	{
		global $user, $config;

		// Let the format_date function operate with the acp values
		$old_tz = $user->timezone;
		$old_dst = $user->dst;

		$user->timezone = $config['board_timezone'] * 3600;
		$user->dst = $config['board_dst'] * 3600;

		$dateformat_options = '';

		foreach ($user->lang['dateformats'] as $format => $null)
		{
			$dateformat_options .= '<option value="' . $format . '"' . (($format == $value) ? ' selected="selected"' : '') . '>';
			$dateformat_options .= $user->format_date(time(), $format, false) . ((strpos($format, '|') !== false) ? $user->lang['VARIANT_DATE_SEPARATOR'] . $user->format_date(time(), $format, true) : '');
			$dateformat_options .= '</option>';
		}

		$dateformat_options .= '<option value="custom"';
		if (!isset($user->lang['dateformats'][$value]))
		{
			$dateformat_options .= ' selected="selected"';
		}
		$dateformat_options .= '>' . $user->lang['CUSTOM_DATEFORMAT'] . '</option>';

		// Reset users date options
		$user->timezone = $old_tz;
		$user->dst = $old_dst;

		return "<select name=\"dateoptions\" id=\"dateoptions\" onchange=\"if (this.value == 'custom') { document.getElementById('" . addslashes($key) . "').value = '" . addslashes($value) . "'; } else { document.getElementById('" . addslashes($key) . "').value = this.value; }\">$dateformat_options</select>
		<input type=\"text\" name=\"config[$key]\" id=\"$key\" value=\"$value\" maxlength=\"30\" />";
	}

	/**
	* Select multiple forums
	*/
	function select_news_forums($value, $key)
	{
		global $user, $config;

		$forum_list = make_forum_select(false, false, true, true, true, false, true);

		// Build forum options
		$s_forum_options = '<select id="' . $key . '" name="' . $key . '[]" multiple="multiple">';
		foreach ($forum_list as $f_id => $f_row)
		{
			$f_row['selected'] = phpbb_optionget(FORUM_OPTION_FEED_NEWS, $f_row['forum_options']);

			$s_forum_options .= '<option value="' . $f_id . '"' . (($f_row['selected']) ? ' selected="selected"' : '') . (($f_row['disabled']) ? ' disabled="disabled" class="disabled-option"' : '') . '>' . $f_row['padding'] . $f_row['forum_name'] . '</option>';
		}
		$s_forum_options .= '</select>';

		return $s_forum_options;
	}

	function select_exclude_forums($value, $key)
	{
		global $user, $config;

		$forum_list = make_forum_select(false, false, true, true, true, false, true);

		// Build forum options
		$s_forum_options = '<select id="' . $key . '" name="' . $key . '[]" multiple="multiple">';
		foreach ($forum_list as $f_id => $f_row)
		{
			$f_row['selected'] = phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $f_row['forum_options']);

			$s_forum_options .= '<option value="' . $f_id . '"' . (($f_row['selected']) ? ' selected="selected"' : '') . (($f_row['disabled']) ? ' disabled="disabled" class="disabled-option"' : '') . '>' . $f_row['padding'] . $f_row['forum_name'] . '</option>';
		}
		$s_forum_options .= '</select>';

		return $s_forum_options;
	}

	function store_feed_forums($option, $key)
	{
		global $db, $cache;

		// Get key
		$values = request_var($key, array(0 => 0));

		// Empty option bit for all forums
		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET forum_options = forum_options - ' . (1 << $option) . '
			WHERE ' . $db->sql_bit_and('forum_options', $option, '<> 0');
		$db->sql_query($sql);

		// Already emptied for all...
		if (sizeof($values))
		{
			// Set for selected forums
			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET forum_options = forum_options + ' . (1 << $option) . '
				WHERE ' . $db->sql_in_set('forum_id', $values);
			$db->sql_query($sql);
		}

		// Empty sql cache for forums table because options changed
		$cache->destroy('sql', FORUMS_TABLE);
	}

	function send_test_email($value, $key)
	{
		global $user;

		return '<label><input type="radio" class="radio" id="' . $key . '" name="' . $key . '" value="1" onchange="document.getElementById(\'' . $key . '_text\').style.display = \'block\'"> ' . $user->lang('YES') . '</label> <label><input type="radio" class="radio" name="' . $key . '" value="0" checked="checked" onchange="document.getElementById(\'' . $key . '_text\').style.display = \'none\'"> ' . $user->lang('NO') . '</label><textarea rows="5" id="' . $key . '_text" name="' . $key . '_text" placeholder="' . $user->lang('MESSAGE') . '" style="display: none"></textarea>';

		return '<input class="button2" type="submit" id="' . $key . '" name="' . $key . '" value="' . $user->lang('SEND_TEST_EMAIL') . '"><textarea id="' . $key . '_text" name="' . $key . '_text" placeholder="' . $user->lang('MESSAGE') . '"></textarea>';
	}

}
