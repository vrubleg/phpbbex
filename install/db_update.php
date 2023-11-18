<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
define('IN_INSTALL', true);

define('NEWEST_PHPBBEX_VERSION', '1.9.7');

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';

require($phpbb_root_path . 'includes/startup.php');

if (!defined('PHPBB_INSTALLED'))
{
	header('Location: ./index.php');
	exit();
}

header('Content-Type: text/plain; charset=utf-8');

// Check if there is a recent allow key file, not older than 60 minutes.

$time_span = intval(time() / 1200);
$curr_keys = [
	substr(md5($time_span - 0), 0, 8),
	substr(md5($time_span - 1), 0, 8),
	substr(md5($time_span - 2), 0, 8)
];

$allowed = false;
foreach ($curr_keys as $key)
{
	if (file_exists($phpbb_root_path . 'cache/allow_upd_' . $key . '.key'))
	{
		$allowed = true;
		break;
	}
}

if (!$allowed)
{
	http_response_code(403);
	die('Create an empty file at /cache/allow_upd_' . $curr_keys[0] . '.key to allow running the script.');
}

// We are allowed, run the update!

@set_time_limit(0);

// Include files
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.php');
require($phpbb_root_path . 'includes/cache.php');
require($phpbb_root_path . 'includes/template.php');
require($phpbb_root_path . 'includes/session.php');
require($phpbb_root_path . 'includes/auth.php');
require($phpbb_root_path . 'includes/functions.php');
require($phpbb_root_path . 'includes/functions_content.php');
require($phpbb_root_path . 'includes/functions_admin.php');
require($phpbb_root_path . 'includes/functions_install.php');
require($phpbb_root_path . 'includes/functions_user.php');
require($phpbb_root_path . 'includes/constants.php');
require($phpbb_root_path . 'includes/db/mysql.php');
require($phpbb_root_path . 'includes/utf/utf_tools.php');
require($phpbb_root_path . 'includes/db/db_tools.php');

$user = new phpbb_user();
$cache = new phpbb_cache();
$db = new dbal_mysql();

// Connect to DB.
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, false);
unset($dbpasswd); // For safety purposes.

$user->ip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';
$user->ip = (stripos($user->ip, '::ffff:') === 0) ? substr($user->ip, 7) : $user->ip;

// Load config.
$config = array();
$sql = 'SELECT * FROM ' . CONFIG_TABLE;
$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
{
	$config[$row['config_name']] = $row['config_value'];
}
$db->sql_freeresult($result);

// Load language files.
if (!isset($config['default_lang']) || !file_exists($phpbb_root_path . 'language/' . $config['default_lang']))
{
	die('Error! Default language is not found!');
}
require($phpbb_root_path . 'language/' . $config['default_lang'] . '/common.php');
require($phpbb_root_path . 'language/' . $config['default_lang'] . '/acp/common.php');
require($phpbb_root_path . 'language/' . $config['default_lang'] . '/install.php');

// Check phpBBex version.

if (!empty($config['phpbbex_version']) && version_compare($config['phpbbex_version'], NEWEST_PHPBBEX_VERSION, '>'))
{
	die('Error! Database schema has newer version than supported.');
}

$purge_default = 'cache';

if (empty($config['phpbbex_version']) || version_compare($config['phpbbex_version'], '1.7.0', '<'))
{
	if (empty($config['version']) || version_compare($config['version'], '3.0.0', '<') || version_compare($config['version'], '3.0.14', '>'))
	{
		die('Error! Database schema has to be phpBB 3.0.x or phpBBex 1.x.x before running the DB update.');
	}

	if (version_compare($config['version'], '3.0.12', '<'))
	{
		// Oh, no, it's too old phpBB 3.0.x, we have to run the original DB update first.
		goto original_db_update;
	}

	$sql_queries = file_get_contents('db_update.sql');
	$sql_queries = str_replace('phpbb_', $table_prefix, $sql_queries);
	$sql_queries = sql_split_queries($sql_queries);

	$db->sql_return_on_error(true);
	foreach ($sql_queries as $sql)
	{
		$db->sql_query($sql);
	}
	$db->sql_return_on_error(false);

	$config['phpbbex_version'] = '1.7.0';
	$purge_default = 'all';
}

if (version_compare($config['phpbbex_version'], '1.8.0', '<'))
{
	$db->sql_return_on_error(true);
	$db->sql_query("ALTER TABLE " . TOPICS_TABLE . " ADD INDEX topic_poster(topic_poster)");
	$db->sql_return_on_error(false);
	$db->sql_query("REPLACE INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('keep_admin_logs_days', '365')");
	$db->sql_query("REPLACE INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('keep_mod_logs_days', '365')");
	$db->sql_query("REPLACE INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('keep_critical_logs_days', '7')");
	$db->sql_query("REPLACE INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('keep_user_logs_days', '365')");
	$db->sql_query("REPLACE INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('keep_register_logs_days', '7')");
	$db->sql_query("UPDATE " . CONFIG_TABLE . " SET config_value = '1.8.0' WHERE config_name = 'phpbbex_version'");
}

if (version_compare($config['phpbbex_version'], '1.9.5', '<'))
{
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " ADD COLUMN user_telegram varchar(255) DEFAULT '' NOT NULL AFTER user_skype");
	$db->sql_query("INSERT INTO " . STYLES_IMAGESET_DATA_TABLE . " (image_name, image_filename, image_lang, image_height, image_width, imageset_id) VALUES ('icon_contact_telegram', 'icon_contact_telegram.gif', '', 20, 20, 1)");
	$db->sql_query("UPDATE " . CONFIG_TABLE . " SET config_value = '1.9.5' WHERE config_name = 'phpbbex_version'");
	$purge_default = 'all';
}

if (version_compare($config['phpbbex_version'], '1.9.6', '<'))
{
	// Disable obsolete modules (they can be removed in the ACP safely).

	$db->sql_query("UPDATE " . MODULES_TABLE . " SET module_enabled = 0 WHERE module_class = 'acp' AND module_basename IN ('update', 'send_statistics')");

	// The COPPA group is not special anymore.

	$db->sql_query("UPDATE " . GROUPS_TABLE . " SET group_type = 2 WHERE group_name = 'REGISTERED_COPPA'");
	$db->sql_query("DELETE FROM " . CONFIG_TABLE . " WHERE config_name IN ('coppa_enable', 'coppa_mail', 'coppa_fax')");

	// Delete obsolete AIM, YIM, and MSN columns that always were unused in phpBBex.

	$db->sql_return_on_error(true);
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " DROP COLUMN user_aim");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " DROP COLUMN user_yim");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " DROP COLUMN user_msnm");
	$db->sql_return_on_error(false);

	// Remove unused columns from sessions table.

	$db->sql_return_on_error(true);
	$db->sql_query("ALTER TABLE " . SESSIONS_TABLE . " DROP COLUMN session_forum_id");
	$db->sql_query("ALTER TABLE " . SESSIONS_TABLE . " DROP COLUMN session_album_id"); // For Gallery MOD.
	$db->sql_return_on_error(false);

	// Upgrade max UA length from 150 to 250.

	$db->sql_query("ALTER TABLE " . LOGIN_ATTEMPT_TABLE . " MODIFY attempt_browser varchar(250) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . SESSIONS_TABLE . " MODIFY session_browser varchar(250) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " MODIFY user_browser varchar(250) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . USER_BROWSER_IDS_TABLE . " MODIFY agent varchar(250) DEFAULT '' NOT NULL");

	$db->sql_query("UPDATE " . CONFIG_TABLE . " SET config_value = '1.9.6' WHERE config_name = 'phpbbex_version'");
}

if (version_compare($config['phpbbex_version'], '1.9.7', '<'))
{
	// Update schema.

	$db->sql_return_on_error(true);
	$db->sql_query("ALTER TABLE " . RANKS_TABLE . " ADD COLUMN rank_hide_title tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER rank_title");
	$db->sql_return_on_error(false);

	// Remove obsolete permissions.

	$permissions = array(
		'a_jabber',
		'f_email',
		'f_print',
		'f_subscribe',
		'u_pm_delete',
		'u_pm_emailpm',
		'u_pm_forward',
		'u_pm_printpm',
		'u_savedrafts',
	);
	$option_ids = array();

	$result = $db->sql_query('SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE. ' WHERE ' . $db->sql_in_set('auth_option', $permissions));
	while ($row = $db->sql_fetchrow($result))
	{
		$option_ids[] = (int) $row['auth_option_id'];
	}
	$db->sql_freeresult($result);

	if (!empty($option_ids))
	{
		foreach (array(ACL_GROUPS_TABLE, ACL_ROLES_DATA_TABLE, ACL_USERS_TABLE, ACL_OPTIONS_TABLE) as $table)
		{
			$db->sql_query("DELETE FROM $table WHERE " . $db->sql_in_set('auth_option_id', $option_ids));
		}

		// Reset permissions cache...
		$cache->destroy('_acl_options');
		require_once($phpbb_root_path . 'includes/acp/auth.php');
		$auth_admin = new auth_admin();
		$auth_admin->acl_clear_prefetch();
	}

	$db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'acl_a_server' WHERE module_auth = 'acl_a_jabber'");

	// Add new config options.

	$db->sql_query("INSERT IGNORE INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('smtp_verify_cert', '1')");

	// Remove obsolete config values.

	$obsolete_values = [
		'forward_pm',
		'print_pm',
		'email_function_name',
		'premium_key',
		'img_imagick',
		'upload_path',
		'avatar_gallery_path',
		'avatar_path',
		'avatar_salt',
		'ranks_path',
		'smilies_path',
		'icons_path',
		'upload_icons_path',
		'force_server_vars',
		'server_protocol',
		'server_name',
		'server_port',
		'script_path',
		'cookie_name',
		'cookie_domain',
		'cookie_secure',
		'no_sid',
		'version',
	];

	$db->sql_query('DELETE FROM ' . CONFIG_TABLE . " WHERE config_name IN ('" . implode("', '", $obsolete_values) . "')");

	// Update DB schema version.

	$db->sql_query("UPDATE " . CONFIG_TABLE . " SET config_value = '1.9.7' WHERE config_name = 'phpbbex_version'");
}

// Update bots if bots=1 is passed.
if (request_var('bots', 0))
{
	$bots_updates = array(
		// Bot deletions.
		'Aport [Bot]'				=> false,
		'Alta Vista [Bot]'			=> false,
		'FAST Enterprise [Crawler]'	=> false,
		'Francis [Bot]'				=> false,
		'Google Desktop'			=> false,
		'Heise IT-Markt [Crawler]'	=> false,
		'Heritrix [Crawler]'		=> false,
		'IBM Research [Bot]'		=> false,
		'ICCrawler - ICjobs'		=> false,
		'Metager [Bot]'				=> false,
		'MSN NewsBlogs'				=> false,
		'NG-Search [Bot]'			=> false,
		'Nutch [Bot]'				=> false,
		'Nutch/CVS [Bot]'			=> false,
		'OmniExplorer [Bot]'		=> false,
		'Online link [Validator]'	=> false,
		'Seekport [Bot]'			=> false,
		'Sensis [Crawler]'			=> false,
		'SEO Crawler'				=> false,
		'Seoma [Crawler]'			=> false,
		'SEOSearch [Crawler]'		=> false,
		'Snappy [Bot]'				=> false,
		'Synoo [Bot]'				=> false,
		'Telekom [Bot]'				=> false,
		'W3 [Sitesearch]'			=> false,
		'WiseNut [Bot]'				=> false,
		'Yahoo MMCrawler [Bot]'		=> false,
		'Yahoo Slurp [Bot]'			=> false,
		'YahooSeeker [Bot]'			=> false,
		'Yandex [Addurl]'			=> false,
		'Yandex [Catalog]'			=> false,
		'Rambler [Bot]'				=> false,
		'WebAlta [Bot]'				=> false,
		'Google Feedfetcher'		=> false,
		'Yandex [Images]'			=> false,
		'Yandex [Video]'			=> false,
		'Yandex [Media]'			=> false,
		'Yandex [Blogs]'			=> false,
		'Yandex [Direct]'			=> false,
		'Yandex [Metrika]'			=> false,
		'Yandex [News]'				=> false,
		'AdsBot [Google]'			=> false,
		'MSN [Bot]'					=> false,
		'MSNbot Media'				=> false,
		'psbot [Picsearch]'			=> false,
		'ichiro [Crawler]'			=> false,
		// Bot updates and additions.
		'Alexa [Bot]'				=> 'ia_archiver',
		'Ask Jeeves [Bot]'			=> 'Ask Jeeves',
		'Baidu [Spider]'			=> 'Baiduspider',
		'Bing [Bot]'				=> 'bingbot/',
		'Exabot [Bot]'				=> 'Exabot',
		'FAST WebCrawler [Crawler]'	=> 'FAST-WebCrawler/',
		'Gigabot [Bot]'				=> 'Gigabot/',
		'Google [Bot]'				=> 'Googlebot',
		'Google Ads [Bot]'			=> 'AdsBot-Google',
		'Google Adsense [Bot]'		=> 'Mediapartners-Google',
		'Majestic-12 [Bot]'			=> 'MJ12bot/',
		'Steeler [Crawler]'			=> 'http://www.tkl.iis.u-tokyo.ac.jp/~crawler/',
		'TurnitinBot [Bot]'			=> 'TurnitinBot/',
		'Voyager [Bot]'				=> 'voyager/',
		'W3C [Linkcheck]'			=> 'W3C-checklink/',
		'W3C [Validator]'			=> 'W3C_Validator',
		'YaCy [Bot]'				=> 'yacybot',
		'Yahoo [Bot]'				=> 'Yahoo! Slurp',
		'Ahrefs [Bot]'				=> 'AhrefsBot/',
		'Senti [Bot]'				=> 'SentiBot/',
		'Petal [Bot]'				=> 'PetalBot',
		'Barkrowler [Bot]'			=> 'Barkrowler/',
		'Ubermetrics [Bot]'			=> 'techinfo@ubermetrics-technologies.com',
		'Trendiction [Bot]'			=> 'trendiction.de/bot',
		'Seostar [Bot]'				=> 'https://seostar.co/robot/',
		'BLEX [Bot]'				=> 'BLEXBot/',
		'DuckDuck [Bot]'			=> 'duckduckgo.com',
		'Yandex [Bot]'				=> 'YandexBot/',
		'Yandex Images [Bot]'		=> 'YandexImages/',
		'Yandex Metrika [Bot]'		=> 'YandexMetrika/',
		'MailRu [Bot]'				=> 'Mail.Ru/',
		'Feedly [Bot]'				=> 'Feedly/',
		'Feedspot [Bot]'			=> 'Feedspot/',
	);

	// Get BOTS group.
	$sql = 'SELECT group_id, group_colour
		FROM ' . GROUPS_TABLE . "
		WHERE group_name = 'BOTS'";
	$result = $db->sql_query($sql);
	$group_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	if (!$group_row) { die('Cannot find BOTS group.'); }

	// Update loop.
	foreach ($bots_updates as $bot_name => $bot_agent)
	{
		$bot_name_clean = utf8_clean_string($bot_name);

		$sql = 'SELECT user_id, user_type
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $db->sql_escape($bot_name_clean) . "'";
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$user_row)
		{
			if ($bot_agent === false) { continue; }

			$bot_ip = '';

			$user_row = array(
				'user_type'				=> USER_IGNORE,
				'group_id'				=> $group_row['group_id'],
				'username'				=> $bot_name,
				'user_regdate'			=> time(),
				'user_password'			=> '',
				'user_colour'			=> $group_row['group_colour'],
				'user_email'			=> '',
				'user_lang'				=> $config['default_lang'],
				'user_style'			=> $config['default_style'],
				'user_timezone'			=> 0,
				'user_dateformat'		=> $config['default_dateformat'],
				'user_allow_massemail'	=> 0,
			);

			$bot_user_id = user_add($user_row);

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'bot_active'	=> 1,
				'bot_name'		=> (string) $bot_name,
				'user_id'		=> (int) $bot_user_id,
				'bot_agent'		=> (string) $bot_agent,
				'bot_ip'		=> (string) $bot_ip,
			));

			$db->sql_query($sql);
		}
		else
		{
			if ($user_row['user_type'] != USER_IGNORE) { continue; }

			$bot_user_id = (int) $user_row['user_id'];

			if ($bot_agent === false)
			{
				$sql = 'DELETE FROM ' . BOTS_TABLE . "
					WHERE user_id = $bot_user_id";
				$db->sql_query($sql);

				user_delete('remove', $bot_user_id);
			}
			else
			{
				$sql = 'UPDATE ' . BOTS_TABLE . "
					SET bot_agent = '" .  $db->sql_escape($bot_agent) . "'
					WHERE user_id = $bot_user_id";
				$db->sql_query($sql);
			}
		}
	}

	// Disable receiving PMs for bots.
	$sql = 'SELECT user_id
		FROM ' . BOTS_TABLE;
	$result = $db->sql_query($sql);

	$bot_user_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$bot_user_ids[] = (int) $row['user_id'];
	}
	$db->sql_freeresult($result);

	if (!empty($bot_user_ids))
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_allow_pm = 0
			WHERE ' . $db->sql_in_set('user_id', $bot_user_ids);
		$db->sql_query($sql);
	}
}

// Convert tables to InnoDB with utf8mb4 encoding if utf8mb4=1 is passed.
if (request_var('utf8mb4', 0))
{
	// Drop fulltext search index if present.

	$drop_indexes = [];

	$sql = 'SHOW INDEX FROM ' . POSTS_TABLE;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['Index_type'] == 'FULLTEXT' && in_array($row['Key_name'], ['post_text', 'post_subject', 'post_content']) && !in_array($row['Key_name'], $drop_indexes))
		{
			$drop_indexes[] = $row['Key_name'];
		}
	}
	$db->sql_freeresult($result);

	if ($drop_indexes)
	{
		$sql = 'ALTER TABLE ' . POSTS_TABLE;
		for ($i = 0; $i < count($drop_indexes); $i++)
		{
			$sql .= ($i == 0 ? ' ' : ', ') . 'DROP INDEX ' . $drop_indexes[$i];
		}
		$result = $db->sql_query($sql);
	}

	// New maximum word size is 191 character. Trim existing words.

	$db->sql_query("UPDATE " . SEARCH_WORDLIST_TABLE . " SET word_text=SUBSTR(word_text, 1, 191) WHERE CHAR_LENGTH(word_text) > 191");

	// Get list of tables that are not in utf8mb4 encoding.

	$convert_tables = [];

	$sql = "SHOW TABLE STATUS WHERE `Name` LIKE '{$table_prefix}%' AND `Collation` <> 'utf8mb4_bin'";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$convert_tables[] = $row['Name'];
	}
	$db->sql_freeresult($result);

	// Convert tables to InnoDB with utf8mb4 encoding.

	foreach ($convert_tables as $table)
	{
		// "CONVERT TO CHARACTER SET `utf8mb4`" implies "DEFAULT CHARACTER SET `utf8mb4`".
		$sql = "ALTER TABLE `{$table}` ENGINE=InnoDB, CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`";
		switch ($table)
		{
			case ACL_GROUPS_TABLE:
			case ACL_OPTIONS_TABLE:
			case ACL_ROLES_DATA_TABLE:
			case ACL_ROLES_TABLE:
			case ACL_USERS_TABLE:
			case ATTACHMENTS_TABLE:
			case BANLIST_TABLE:
			case BBCODES_TABLE:
			case BOOKMARKS_TABLE:
			case BOTS_TABLE:
			case CONFIRM_TABLE:
			case DISALLOW_TABLE:
			case DRAFTS_TABLE:
			case EXTENSIONS_TABLE:
			case EXTENSION_GROUPS_TABLE:
			case FORUMS_TABLE:
			case FORUMS_ACCESS_TABLE:
			case FORUMS_TRACK_TABLE:
			case FORUMS_WATCH_TABLE:
			case ICONS_TABLE:
			case LANG_TABLE:
			case LOG_TABLE:
			case MODERATOR_CACHE_TABLE:
			case MODULES_TABLE:
			case POLL_OPTIONS_TABLE:
			case POLL_VOTES_TABLE:
			case PRIVMSGS_TABLE:
			case PRIVMSGS_FOLDER_TABLE:
			case PRIVMSGS_RULES_TABLE:
			case PRIVMSGS_TO_TABLE:
			case PROFILE_FIELDS_TABLE:
			case PROFILE_FIELDS_DATA_TABLE:
			case PROFILE_FIELDS_LANG_TABLE:
			case PROFILE_LANG_TABLE:
			case RANKS_TABLE:
			case REPORTS_TABLE:
			case REPORTS_REASONS_TABLE:
			case SEARCH_RESULTS_TABLE:
			case SEARCH_WORDMATCH_TABLE:
			case SESSIONS_TABLE:
			case SESSIONS_KEYS_TABLE:
			case SITELIST_TABLE:
			case SMILIES_TABLE:
			case STYLES_TEMPLATE_DATA_TABLE:
			case STYLES_IMAGESET_DATA_TABLE:
			case TOPICS_POSTED_TABLE:
			case TOPICS_TRACK_TABLE:
			case TOPICS_WATCH_TABLE:
			case USER_GROUP_TABLE:
			case WARNINGS_TABLE:
			case WORDS_TABLE:
			case ZEBRA_TABLE:
			case USER_CONFIRM_KEYS_TABLE:
			case USER_BROWSER_IDS_TABLE:
			case POST_RATES_TABLE:
			// Standard QA CAPTCHA tables.
			case "{$table_prefix}captcha_questions":
			case "{$table_prefix}captcha_answers":
			case "{$table_prefix}qa_confirm":
				// Use default conversion query for most tables.
				break;
			case CONFIG_TABLE:
				$sql .= ",
					MODIFY config_name varchar(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT '' NOT NULL,
					MODIFY config_value varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '' NOT NULL";
				break;
			case GROUPS_TABLE:
				$sql .= ", MODIFY group_name varchar(191) DEFAULT '' NOT NULL";
				break;
			case LOGIN_ATTEMPT_TABLE:
				$sql .= ",
					MODIFY attempt_ip varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT '' NOT NULL,
					MODIFY attempt_forwarded_for varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT '' NOT NULL";
				break;
			case POSTS_TABLE:
				$sql .= ",
					MODIFY post_username varchar(191) DEFAULT '' NOT NULL,
					MODIFY post_subject varchar(255) DEFAULT '' NOT NULL COLLATE utf8mb4_unicode_ci";
				break;
			case TOPICS_TABLE:
				$sql .= ", MODIFY topic_title varchar(255) DEFAULT '' NOT NULL COLLATE utf8mb4_unicode_ci";
				break;
			case SEARCH_WORDLIST_TABLE:
				$sql .= ", MODIFY word_text varchar(191) DEFAULT '' NOT NULL";
				break;
			case STYLES_TABLE:
				$sql .= ", MODIFY style_name varchar(100) DEFAULT '' NOT NULL";
				break;
			case STYLES_IMAGESET_TABLE:
				$sql .= ", MODIFY imageset_name varchar(100) DEFAULT '' NOT NULL";
				break;
			case STYLES_TEMPLATE_TABLE:
				$sql .= ", MODIFY template_name varchar(100) DEFAULT '' NOT NULL";
				break;
			case STYLES_THEME_TABLE:
				$sql .= ", MODIFY theme_name varchar(100) DEFAULT '' NOT NULL";
				break;
			case USERS_TABLE:
				$sql .= ",
					MODIFY username varchar(191) DEFAULT '' NOT NULL,
					MODIFY username_clean varchar(191) DEFAULT '' NOT NULL";
				break;

			// Simple Chat MOD tables.
			case "{$table_prefix}chat_messages":
			case "{$table_prefix}chat_sessions":
				break;

			// Gallery MOD tables.
			case "{$table_prefix}gallery_albums":
			case "{$table_prefix}gallery_albums_track":
			case "{$table_prefix}gallery_comments":
			case "{$table_prefix}gallery_contests":
			case "{$table_prefix}gallery_favorites":
			case "{$table_prefix}gallery_images":
			case "{$table_prefix}gallery_modscache":
			case "{$table_prefix}gallery_permissions":
			case "{$table_prefix}gallery_rates":
			case "{$table_prefix}gallery_reports":
			case "{$table_prefix}gallery_roles":
			case "{$table_prefix}gallery_users":
			case "{$table_prefix}gallery_watch":
				break;
			case "{$table_prefix}gallery_config":
				$sql .= ", MODIFY config_name varchar(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT '' NOT NULL";
				break;

			// Portal MOD tables.
			case "{$table_prefix}portal_config":
				$sql .= ", MODIFY config_name varchar(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT '' NOT NULL";
				break;

			// Skip unknown tables.
			default:
				$sql = null;
				break;
		}
		if ($sql) { $db->sql_query($sql); }
	}
}

// Purge cached data depending on purge argument.
switch (request_var('purge', $purge_default))
{
	case 'none':
		break;

	case 'cache':
	default:
		$cache->purge();
		break;

	case 'all':
		require_once($phpbb_root_path . 'includes/umil.php');

		$umil = new phpbb_umil();
		$umil->cache_purge(array(
			'data',
			'template',
			'theme',
			'imageset',
		));
		break;
}

echo('OK');
garbage_collection();
exit_handler();
die();

//
// Original phpBB 3.0 database update code.
//

original_db_update:

header('Content-Type: text/html; charset=utf-8');

define('UPDATES_TO_VERSION', '3.0.14');

// Enter any version to update from to test updates. The version within the db will not be updated.
define('DEBUG_FROM_VERSION', false);

// Which oldest version does this updater support?
define('OLDEST_FROM_VERSION', '3.0.0');

$updates_to_version = UPDATES_TO_VERSION;
$debug_from_version = DEBUG_FROM_VERSION;
$oldest_from_version = OLDEST_FROM_VERSION;

$db_tools = new phpbb_db_tools($db, true);
$database_update_info = database_update_info();

$error_ary = array();
$errored = false;

?>
<!DOCTYPE html>
<html dir="<?php echo $lang['DIRECTION']; ?>" lang="<?php echo $lang['USER_LANG']; ?>" xml:lang="<?php echo $lang['USER_LANG']; ?>">
<head>
<meta charset="utf-8" />
<title><?php echo $lang['UPDATING_TO_LATEST_STABLE']; ?></title>
<link href="../adm/style/admin.css" rel="stylesheet" media="screen" />
</head>
<body>
<div id="wrap">
	<div id="page-header">&nbsp;</div>

	<div id="page-body">
		<div id="acp">
		<div class="panel">
			<div id="content">
				<div id="main" class="install-body">

	<h1><?php echo $lang['UPDATING_TO_LATEST_STABLE']; ?></h1>

	<br />
<?php

if ($debug_from_version !== false)
{
	$config['version'] = $debug_from_version;
}

echo $lang['PREVIOUS_VERSION'] . ' :: <strong>' . $config['version'] . '</strong><br />';
echo $lang['UPDATED_VERSION'] . ' :: <strong>' . $updates_to_version . '</strong></p>';

$current_version = str_replace('rc', 'RC', strtolower($config['version']));
$latest_version = str_replace('rc', 'RC', strtolower($updates_to_version));
$orig_version = $config['version'];

// Fill DB version
if (empty($config['dbms_version']))
{
	set_config('dbms_version', $db->sql_server_info(true));
}

// Now check if the user wants to update from a version we no longer support updates from
if (version_compare($current_version, $oldest_from_version, '<'))
{
	echo '<br /><br /><h1>' . $lang['ERROR'] . '</h1><br />';
	echo '<p>' . sprintf($lang['DB_UPDATE_NOT_SUPPORTED'], $oldest_from_version, $current_version) . '</p>';

	_print_footer();
	exit_handler();
	exit;
}

// Schema updates
?>
	<br /><br />

	<h1><?php echo $lang['UPDATE_DATABASE_SCHEMA']; ?></h1>

	<br />
	<p><?php echo $lang['PROGRESS']; ?> :: <strong>

<?php

flush();

// We go through the schema changes from the lowest to the highest version
// We try to also include versions 'in-between'...
$no_updates = true;
$versions = array_keys($database_update_info);
for ($i = 0; $i < sizeof($versions); $i++)
{
	$version = $versions[$i];
	$schema_changes = $database_update_info[$version];

	$next_version = (isset($versions[$i + 1])) ? $versions[$i + 1] : $updates_to_version;

	// If the installed version to be updated to is < than the current version, and if the current version is >= as the version to be updated to next, we will skip the process
	if (version_compare($version, $current_version, '<') && version_compare($current_version, $next_version, '>='))
	{
		continue;
	}

	if (!sizeof($schema_changes))
	{
		continue;
	}

	$no_updates = false;

	// We run one index after the other... to be consistent with schema changes...
	foreach ($schema_changes as $key => $changes)
	{
		$statements = $db_tools->perform_schema_changes(array($key => $changes));

		foreach ($statements as $sql)
		{
			_sql($sql, $errored, $error_ary);
		}
	}
}

_write_result($no_updates, $errored, $error_ary);

// Data updates
$error_ary = array();
$errored = $no_updates = false;

?>

<br /><br />
<h1><?php echo $lang['UPDATING_DATA']; ?></h1>
<br />
<p><?php echo $lang['PROGRESS']; ?> :: <strong>

<?php

flush();

$no_updates = true;
$versions = array_keys($database_update_info);

// some code magic
for ($i = 0; $i < sizeof($versions); $i++)
{
	$version = $versions[$i];
	$next_version = (isset($versions[$i + 1])) ? $versions[$i + 1] : $updates_to_version;

	// If the installed version to be updated to is < than the current version, and if the current version is >= as the version to be updated to next, we will skip the process
	if (version_compare($version, $current_version, '<') && version_compare($current_version, $next_version, '>='))
	{
		continue;
	}

	change_database_data($no_updates, $version);
}

_write_result($no_updates, $errored, $error_ary);

$error_ary = array();
$errored = $no_updates = false;

?>

<br /><br />
<h1><?php echo $lang['UPDATE_VERSION_OPTIMIZE']; ?></h1>
<br />
<p><?php echo $lang['PROGRESS']; ?> :: <strong>

<?php

flush();

if ($debug_from_version === false)
{
	// update the version
	$sql = "UPDATE " . CONFIG_TABLE . "
		SET config_value = '$updates_to_version'
		WHERE config_name = 'version'";
	_sql($sql, $errored, $error_ary);
}

// Reset permissions
$sql = 'UPDATE ' . USERS_TABLE . "
	SET user_permissions = '',
		user_perm_from = 0";
_sql($sql, $errored, $error_ary);

// Update the dbms version if everything is ok...
set_config('dbms_version', $db->sql_server_info(true));

_write_result($no_updates, $errored, $error_ary);

?>

<br />
<h1><?php echo $lang['UPDATE_COMPLETED']; ?></h1>
<p><?php echo $lang['UPDATE_FILES_NOTICE']; ?></p>

<?php

// Add database update to log
add_log('admin', 'LOG_UPDATE_DATABASE', $orig_version, $updates_to_version);

// Now we purge the session table as well as all cache files
// $cache->purge();

_print_footer();

garbage_collection();
exit_handler();

/**
* Print out footer
*/
function _print_footer()
{
	echo '
				</div>
			</div>
		</div>
		</div>
	</div>

	<div id="page-footer">
		Powered by ' . POWERED_BY . '
	</div>
</div>

</body>
</html>';
}

/**
* Function for triggering an sql statement
*/
function _sql($sql, &$errored, &$error_ary, $echo_dot = true)
{
	global $db;

	if (defined('DEBUG_EXTRA'))
	{
		echo "<br />\n{$sql}\n<br />";
	}

	$db->sql_return_on_error(true);

	if ($sql === 'begin')
	{
		$result = $db->sql_transaction('begin');
	}
	else if ($sql === 'commit')
	{
		$result = $db->sql_transaction('commit');
	}
	else
	{
		$result = $db->sql_query($sql);
		if ($db->sql_error_triggered)
		{
			$errored = true;
			$error_ary['sql'][] = $db->sql_error_sql;
			$error_ary['error_code'][] = $db->sql_error_returned;
		}
	}

	$db->sql_return_on_error(false);

	if ($echo_dot)
	{
		echo ". \n";
		flush();
	}

	return $result;
}

function _write_result($no_updates, $errored, $error_ary)
{
	global $lang;

	if ($no_updates)
	{
		echo ' ' . $lang['NO_UPDATES_REQUIRED'] . '</strong></p>';
	}
	else
	{
		echo ' <span class="success">' . $lang['DONE'] . '</span></strong><br />' . $lang['RESULT'] . ' :: ';

		if ($errored)
		{
			echo ' <strong>' . $lang['SOME_QUERIES_FAILED'] . '</strong> <ul>';

			for ($i = 0; $i < sizeof($error_ary['sql']); $i++)
			{
				echo '<li>' . $lang['ERROR'] . ' :: <strong>' . htmlspecialchars($error_ary['error_code'][$i]['message']) . '</strong><br />';
				echo $lang['SQL'] . ' :: <strong>' . htmlspecialchars($error_ary['sql'][$i]) . '</strong><br /><br /></li>';
			}

			echo '</ul> <br /><br />' . $lang['SQL_FAILURE_EXPLAIN'] . '</p>';
		}
		else
		{
			echo '<strong>' . $lang['NO_ERRORS'] . '</strong></p>';
		}
	}
}

function _add_modules($modules_to_install)
{
	global $phpbb_root_path, $db;

	include_once($phpbb_root_path . 'includes/acp/acp_modules.php');

	$_module = new acp_modules();

	foreach ($modules_to_install as $module_mode => $module_data)
	{
		$_module->module_class = $module_data['class'];

		// Determine parent id first
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = '" . $db->sql_escape($module_data['class']) . "'
				AND module_langname = '" . $db->sql_escape($module_data['cat']) . "'
				AND module_mode = ''
				AND module_basename = ''";
		$result = $db->sql_query($sql);

		// There may be more than one categories with the same name
		$categories = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$categories[] = (int) $row['module_id'];
		}
		$db->sql_freeresult($result);

		if (!sizeof($categories))
		{
			continue;
		}

		// Add the module to all categories found
		foreach ($categories as $parent_id)
		{
			// Check if the module already exists
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = '" . $db->sql_escape($module_data['base']) . "'
					AND module_class = '" . $db->sql_escape($module_data['class']) . "'
					AND module_langname = '" . $db->sql_escape($module_data['title']) . "'
					AND module_mode = '" . $db->sql_escape($module_mode) . "'
					AND module_auth = '" . $db->sql_escape($module_data['auth']) . "'
					AND parent_id = {$parent_id}";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			// If it exists, we simply continue with the next category
			if ($row)
			{
				continue;
			}

			// Build the module sql row
			$module_row = array(
				'module_basename'	=> $module_data['base'],
				'module_enabled'	=> (isset($module_data['enabled'])) ? (int) $module_data['enabled'] : 1,
				'module_display'	=> (isset($module_data['display'])) ? (int) $module_data['display'] : 1,
				'parent_id'			=> $parent_id,
				'module_class'		=> $module_data['class'],
				'module_langname'	=> $module_data['title'],
				'module_mode'		=> $module_mode,
				'module_auth'		=> $module_data['auth'],
			);

			$_module->update_module_data($module_row, true);

			// Ok, do we need to re-order the module, move it up or down?
			if (!isset($module_data['after']))
			{
				continue;
			}

			$after_mode = $module_data['after'][0];
			$after_langname = $module_data['after'][1];

			// First of all, get the module id for the module this one has to be placed after
			$sql = 'SELECT left_id
				FROM ' . MODULES_TABLE . "
				WHERE module_class = '" . $db->sql_escape($module_data['class']) . "'
					AND module_basename = '" . $db->sql_escape($module_data['base']) . "'
					AND module_langname = '" . $db->sql_escape($after_langname) . "'
					AND module_mode = '" . $db->sql_escape($after_mode) . "'
					AND parent_id = '{$parent_id}'";
			$result = $db->sql_query($sql);
			$first_left_id = (int) $db->sql_fetchfield('left_id');
			$db->sql_freeresult($result);

			if (!$first_left_id)
			{
				continue;
			}

			// Ok, count the number of modules between $after_mode and the added module
			$sql = 'SELECT COUNT(module_id) as num_modules
				FROM ' . MODULES_TABLE . "
				WHERE module_class = '" . $db->sql_escape($module_data['class']) . "'
					AND parent_id = {$parent_id}
					AND left_id BETWEEN {$first_left_id} AND {$module_row['left_id']}";
			$result = $db->sql_query($sql);
			$steps = (int) $db->sql_fetchfield('num_modules');
			$db->sql_freeresult($result);

			// We need to substract 2
			$steps -= 2;

			if ($steps <= 0)
			{
				continue;
			}

			// Ok, move module up $num_modules times. ;)
			$_module->move_module_by($module_row, 'move_up', $steps);
		}
	}

	$_module->remove_cache_file();
}

/****************************************************************************
* ADD YOUR DATABASE SCHEMA CHANGES HERE										*
*****************************************************************************/
function database_update_info()
{
	return array(
		// Changes from 3.0.0 to the next version
		'3.0.0'			=> array(
			// Add the following columns
			'add_columns'		=> array(
				FORUMS_TABLE			=> array(
					'display_subforum_list'		=> array('BOOL', 1),
				),
				SESSIONS_TABLE			=> array(
					'session_forum_id'		=> array('UINT', 0),
				),
			),
			'drop_keys'		=> array(
				GROUPS_TABLE			=> array('group_legend'),
			),
			'add_index'		=> array(
				SESSIONS_TABLE			=> array(
					'session_forum_id'		=> array('session_forum_id'),
				),
				GROUPS_TABLE			=> array(
					'group_legend_name'		=> array('group_legend', 'group_name'),
				),
			),
		),
		// No changes from 3.0.1-RC1 to 3.0.1
		'3.0.1-RC1'		=> array(),
		// No changes from 3.0.1 to 3.0.2-RC1
		'3.0.1'			=> array(),
		// Changes from 3.0.2-RC1 to 3.0.2-RC2
		'3.0.2-RC1'		=> array(
			'change_columns'	=> array(
				DRAFTS_TABLE			=> array(
					'draft_subject'		=> array('STEXT_UNI', ''),
				),
				FORUMS_TABLE	=> array(
					'forum_last_post_subject' => array('STEXT_UNI', ''),
				),
				POSTS_TABLE		=> array(
					'post_subject'			=> array('STEXT_UNI', '', 'true_sort'),
				),
				PRIVMSGS_TABLE	=> array(
					'message_subject'		=> array('STEXT_UNI', ''),
				),
				TOPICS_TABLE	=> array(
					'topic_title'				=> array('STEXT_UNI', '', 'true_sort'),
					'topic_last_post_subject'	=> array('STEXT_UNI', ''),
				),
			),
			'drop_keys'		=> array(
				SESSIONS_TABLE			=> array('session_forum_id'),
			),
			'add_index'		=> array(
				SESSIONS_TABLE			=> array(
					'session_fid'		=> array('session_forum_id'),
				),
			),
		),
		// No changes from 3.0.2-RC2 to 3.0.2
		'3.0.2-RC2'		=> array(),

		// Changes from 3.0.2 to 3.0.3-RC1
		'3.0.2'			=> array(
			// Add the following columns
			'add_columns'		=> array(
				STYLES_TEMPLATE_TABLE			=> array(
					'template_inherits_id'		=> array('UINT:4', 0),
					'template_inherit_path'		=> array('VCHAR', ''),
				),
				GROUPS_TABLE					=> array(
					'group_max_recipients'		=> array('UINT', 0),
				),
			),
		),

		// No changes from 3.0.3-RC1 to 3.0.3
		'3.0.3-RC1'		=> array(),

		// Changes from 3.0.3 to 3.0.4-RC1
		'3.0.3'			=> array(
			'add_columns'		=> array(
				PROFILE_FIELDS_TABLE			=> array(
					'field_show_profile'		=> array('BOOL', 0),
				),
			),
			'change_columns'	=> array(
				STYLES_TABLE				=> array(
					'style_id'				=> array('UINT', NULL, 'auto_increment'),
					'template_id'			=> array('UINT', 0),
					'theme_id'				=> array('UINT', 0),
					'imageset_id'			=> array('UINT', 0),
				),
				STYLES_IMAGESET_TABLE		=> array(
					'imageset_id'				=> array('UINT', NULL, 'auto_increment'),
				),
				STYLES_IMAGESET_DATA_TABLE	=> array(
					'image_id'				=> array('UINT', NULL, 'auto_increment'),
					'imageset_id'			=> array('UINT', 0),
				),
				STYLES_THEME_TABLE			=> array(
					'theme_id'				=> array('UINT', NULL, 'auto_increment'),
				),
				STYLES_TEMPLATE_TABLE		=> array(
					'template_id'			=> array('UINT', NULL, 'auto_increment'),
				),
				STYLES_TEMPLATE_DATA_TABLE	=> array(
					'template_id'			=> array('UINT', 0),
				),
				FORUMS_TABLE				=> array(
					'forum_style'			=> array('UINT', 0),
				),
				USERS_TABLE					=> array(
					'user_style'			=> array('UINT', 0),
				),
			),
		),

		// Changes from 3.0.4-RC1 to 3.0.4
		'3.0.4-RC1'		=> array(),

		// Changes from 3.0.4 to 3.0.5-RC1
		'3.0.4'			=> array(
			'change_columns'	=> array(
				FORUMS_TABLE				=> array(
					'forum_style'			=> array('UINT', 0),
				),
			),
		),

		// No changes from 3.0.5-RC1 to 3.0.5
		'3.0.5-RC1'		=> array(),

		// Changes from 3.0.5 to 3.0.6-RC1
		'3.0.5'		=> array(
			'add_columns'		=> array(
				CONFIRM_TABLE			=> array(
					'attempts'		=> array('UINT', 0),
				),
				USERS_TABLE			=> array(
					'user_new'			=> array('BOOL', 1),
					'user_reminded'		=> array('TINT:4', 0),
					'user_reminded_time'=> array('TIMESTAMP', 0),
				),
				GROUPS_TABLE			=> array(
					'group_skip_auth'		=> array('BOOL', 0, 'after' => 'group_founder_manage'),
				),
				PRIVMSGS_TABLE		=> array(
					'message_reported'	=> array('BOOL', 0),
				),
				REPORTS_TABLE		=> array(
					'pm_id'				=> array('UINT', 0),
				),
				PROFILE_FIELDS_TABLE			=> array(
					'field_show_on_vt'		=> array('BOOL', 0),
				),
				FORUMS_TABLE		=> array(
					'forum_options'			=> array('UINT:20', 0),
				),
			),
			'change_columns'		=> array(
				USERS_TABLE				=> array(
					'user_options'		=> array('UINT:11', 230271),
				),
			),
			'add_index'		=> array(
				REPORTS_TABLE		=> array(
					'post_id'		=> array('post_id'),
					'pm_id'			=> array('pm_id'),
				),
				POSTS_TABLE			=> array(
					'post_username'		=> array('post_username:255'),
				),
			),
		),

		// No changes from 3.0.6-RC1 to 3.0.6-RC2
		'3.0.6-RC1'		=> array(),
		// No changes from 3.0.6-RC2 to 3.0.6-RC3
		'3.0.6-RC2'		=> array(),
		// No changes from 3.0.6-RC3 to 3.0.6-RC4
		'3.0.6-RC3'		=> array(),
		// No changes from 3.0.6-RC4 to 3.0.6
		'3.0.6-RC4'		=> array(),

		// Changes from 3.0.6 to 3.0.7-RC1
		'3.0.6'		=> array(
			'drop_keys'		=> array(
				LOG_TABLE			=> array('log_time'),
			),
			'add_index'		=> array(
				TOPICS_TRACK_TABLE	=> array(
					'topic_id'		=> array('topic_id'),
				),
			),
		),

		// No changes from 3.0.7-RC1 to 3.0.7-RC2
		'3.0.7-RC1'		=> array(),
		// No changes from 3.0.7-RC2 to 3.0.7
		'3.0.7-RC2'		=> array(),
		// No changes from 3.0.7 to 3.0.7-PL1
		'3.0.7'		=> array(),
		// No changes from 3.0.7-PL1 to 3.0.8-RC1
		'3.0.7-PL1'		=> array(),
		// No changes from 3.0.8-RC1 to 3.0.8
		'3.0.8-RC1'		=> array(),
		// Changes from 3.0.8 to 3.0.9-RC1
		'3.0.8'			=> array(
			'add_tables'		=> array(
				LOGIN_ATTEMPT_TABLE	=> array(
					'COLUMNS'			=> array(
						// this column was removed from the database updater
						// after 3.0.9-RC3 was released. It might still exist
						// in 3.0.9-RCX installations and has to be dropped in
						// 3.0.15 after the db_tools class is capable of properly
						// removing a primary key.
						// 'attempt_id'			=> array('UINT', NULL, 'auto_increment'),
						'attempt_ip'			=> array('VCHAR:40', ''),
						'attempt_browser'		=> array('VCHAR:150', ''),
						'attempt_forwarded_for'	=> array('VCHAR:255', ''),
						'attempt_time'			=> array('TIMESTAMP', 0),
						'user_id'				=> array('UINT', 0),
						'username'				=> array('VCHAR_UNI:255', 0),
						'username_clean'		=> array('VCHAR_CI', 0),
					),
					//'PRIMARY_KEY'		=> 'attempt_id',
					'KEYS'				=> array(
						'att_ip'			=> array('INDEX', array('attempt_ip', 'attempt_time')),
						'att_for'	=> array('INDEX', array('attempt_forwarded_for', 'attempt_time')),
						'att_time'			=> array('INDEX', array('attempt_time')),
						'user_id'				=> array('INDEX', 'user_id'),
					),
				),
			),
			'change_columns'	=> array(
				BBCODES_TABLE	=> array(
					'bbcode_id'	=> array('USINT', 0),
				),
			),
		),
		// No changes from 3.0.9-RC1 to 3.0.9-RC2
		'3.0.9-RC1'		=> array(),
		// No changes from 3.0.9-RC2 to 3.0.9-RC3
		'3.0.9-RC2'		=> array(),
		// No changes from 3.0.9-RC3 to 3.0.9-RC4
		'3.0.9-RC3'     => array(),
		// No changes from 3.0.9-RC4 to 3.0.9
		'3.0.9-RC4'     => array(),
		// No changes from 3.0.9 to 3.0.10-RC1
		'3.0.9'			=> array(),
		// No changes from 3.0.10-RC1 to 3.0.10-RC2
		'3.0.10-RC1'	=> array(),
		// No changes from 3.0.10-RC2 to 3.0.10-RC3
		'3.0.10-RC2'	=> array(),
		// No changes from 3.0.10-RC3 to 3.0.10
		'3.0.10-RC3'	=> array(),
		// No changes from 3.0.10 to 3.0.11-RC1
		'3.0.10'		=> array(),
		// Changes from 3.0.11-RC1 to 3.0.11-RC2
		'3.0.11-RC1'	=> array(
			'add_columns'		=> array(
				PROFILE_FIELDS_TABLE			=> array(
					'field_show_novalue'		=> array('BOOL', 0),
				),
			),
		),
		// No changes from 3.0.11-RC2 to 3.0.11
		'3.0.11-RC2'	=> array(),
		// No changes from 3.0.11 to 3.0.12-RC1
		'3.0.11'		=> array(),
		// No changes from 3.0.12-RC1 to 3.0.12-RC2
		'3.0.12-RC1'	=> array(),
		// No changes from 3.0.12-RC2 to 3.0.12-RC3
		'3.0.12-RC2'	=> array(),
		// No changes from 3.0.12-RC3 to 3.0.12
		'3.0.12-RC3'	=> array(),
		// No changes from 3.0.12 to 3.0.13-RC1
		'3.0.12'		=> array(),
		// No changes from 3.0.13-RC1 to 3.0.13
		'3.0.13-RC1'	=> array(),
		// No changes from 3.0.13 to 3.0.13-PL1
		'3.0.13'		=> array(),
		// No changes from 3.0.13-PL1 to 3.0.14-RC1
		'3.0.13-PL1'	=> array(),
		// No changes from 3.0.14-RC1 to 3.0.14
		'3.0.14-RC1'	=> array(),

		/** @todo DROP LOGIN_ATTEMPT_TABLE.attempt_id in 3.0.15-RC1 */
	);
}

/****************************************************************************
* ADD YOUR DATABASE DATA CHANGES HERE										*
* REMEMBER: You NEED to enter a schema array above and a data array here,	*
* even if both or one of them are empty.									*
*****************************************************************************/
function change_database_data(&$no_updates, $version)
{
	global $db, $db_tools, $errored, $error_ary, $config, $table_prefix, $phpbb_root_path;

	switch ($version)
	{
		case '3.0.0':

			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_last_view_time = topic_last_post_time
				WHERE topic_last_view_time = 0";
			_sql($sql, $errored, $error_ary);

			// Update smiley sizes
			$smileys = array('icon_e_surprised.gif', 'icon_eek.gif', 'icon_cool.gif', 'icon_lol.gif', 'icon_mad.gif', 'icon_razz.gif', 'icon_redface.gif', 'icon_cry.gif', 'icon_evil.gif', 'icon_twisted.gif', 'icon_rolleyes.gif', 'icon_exclaim.gif', 'icon_question.gif', 'icon_idea.gif', 'icon_arrow.gif', 'icon_neutral.gif', 'icon_mrgreen.gif', 'icon_e_ugeek.gif');

			foreach ($smileys as $smiley)
			{
				if (file_exists($phpbb_root_path . 'images/smilies/' . $smiley))
				{
					list($width, $height) = getimagesize($phpbb_root_path . 'images/smilies/' . $smiley);

					$sql = 'UPDATE ' . SMILIES_TABLE . '
						SET smiley_width = ' . $width . ', smiley_height = ' . $height . "
						WHERE smiley_url = '" . $db->sql_escape($smiley) . "'";

					_sql($sql, $errored, $error_ary);
				}
			}

			$no_updates = false;
		break;

		// No changes from 3.0.1-RC1 to 3.0.1
		case '3.0.1-RC1':
		break;

		// changes from 3.0.1 to 3.0.2-RC1
		case '3.0.1':

			set_config('referer_validation', '1');
			set_config('check_attachment_content', '1');
			set_config('mime_triggers', 'body|head|html|img|plaintext|a href|pre|script|table|title');

			$no_updates = false;
		break;

		// No changes from 3.0.2-RC1 to 3.0.2-RC2
		case '3.0.2-RC1':
		break;

		// No changes from 3.0.2-RC2 to 3.0.2
		case '3.0.2-RC2':
		break;

		// Changes from 3.0.2 to 3.0.3-RC1
		case '3.0.2':
			set_config('enable_queue_trigger', '0');
			set_config('queue_trigger_posts', '3');

			set_config('pm_max_recipients', '0');

			// Set maximum number of recipients for the registered users, bots, guests group
			$sql = 'UPDATE ' . GROUPS_TABLE . ' SET group_max_recipients = 5
				WHERE ' . $db->sql_in_set('group_name', array('GUESTS', 'REGISTERED', 'REGISTERED_COPPA', 'BOTS'));
			_sql($sql, $errored, $error_ary);

			// Not prefilling yet
			set_config('dbms_version', '');

			// Add new permission u_masspm_group and duplicate settings from u_masspm
			include_once($phpbb_root_path . 'includes/acp/auth.php');
			$auth_admin = new auth_admin();

			// Only add the new permission if it does not already exist
			if (empty($auth_admin->acl_options['id']['u_masspm_group']))
			{
				$auth_admin->acl_add_option(array('global' => array('u_masspm_group')));

				// Now the tricky part, filling the permission
				$old_id = $auth_admin->acl_options['id']['u_masspm'];
				$new_id = $auth_admin->acl_options['id']['u_masspm_group'];

				$tables = array(ACL_GROUPS_TABLE, ACL_ROLES_DATA_TABLE, ACL_USERS_TABLE);

				foreach ($tables as $table)
				{
					$sql = 'SELECT *
						FROM ' . $table . '
						WHERE auth_option_id = ' . $old_id;
					$result = _sql($sql, $errored, $error_ary);

					$sql_ary = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$row['auth_option_id'] = $new_id;
						$sql_ary[] = $row;
					}
					$db->sql_freeresult($result);

					if (sizeof($sql_ary))
					{
						$db->sql_multi_insert($table, $sql_ary);
					}
				}

				// Remove any old permission entries
				$auth_admin->acl_clear_prefetch();
			}

			/**
			* Do not resync post counts here. An admin may do this later from the ACP
			$start = 0;
			$step = ($config['num_posts']) ? (max((int) ($config['num_posts'] / 5), 20000)) : 20000;

			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_posts = 0';
			_sql($sql, $errored, $error_ary);

			do
			{
				$sql = 'SELECT COUNT(post_id) AS num_posts, poster_id
					FROM ' . POSTS_TABLE . '
					WHERE post_id BETWEEN ' . ($start + 1) . ' AND ' . ($start + $step) . '
						AND post_postcount = 1 AND post_approved = 1
					GROUP BY poster_id';
				$result = _sql($sql, $errored, $error_ary);

				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$sql = 'UPDATE ' . USERS_TABLE . " SET user_posts = user_posts + {$row['num_posts']} WHERE user_id = {$row['poster_id']}";
						_sql($sql, $errored, $error_ary);
					}
					while ($row = $db->sql_fetchrow($result));

					$start += $step;
				}
				else
				{
					$start = 0;
				}
				$db->sql_freeresult($result);
			}
			while ($start);
			*/

			$sql = 'UPDATE ' . MODULES_TABLE . '
				SET module_auth = \'acl_a_email && cfg_email_enable\'
				WHERE module_class = \'acp\'
					AND module_basename = \'email\'';
			_sql($sql, $errored, $error_ary);

			$no_updates = false;
		break;

		// Changes from 3.0.3-RC1 to 3.0.3
		case '3.0.3-RC1':
			$sql = 'UPDATE ' . LOG_TABLE . "
				SET log_operation = 'LOG_DELETE_TOPIC'
				WHERE log_operation = 'LOG_TOPIC_DELETED'";
			_sql($sql, $errored, $error_ary);

			$no_updates = false;
		break;

		// Changes from 3.0.3 to 3.0.4-RC1
		case '3.0.3':
			// Update the Custom Profile Fields based on previous settings to the new format
			$sql = 'SELECT field_id, field_required, field_show_on_reg, field_hide
					FROM ' . PROFILE_FIELDS_TABLE;
			$result = _sql($sql, $errored, $error_ary);

			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary = array(
					'field_required'	=> 0,
					'field_show_on_reg'	=> 0,
					'field_hide'		=> 0,
					'field_show_profile'=> 0,
				);

				if ($row['field_required'])
				{
					$sql_ary['field_required'] = $sql_ary['field_show_on_reg'] = $sql_ary['field_show_profile'] = 1;
				}
				else if ($row['field_show_on_reg'])
				{
					$sql_ary['field_show_on_reg'] = $sql_ary['field_show_profile'] = 1;
				}
				else if ($row['field_hide'])
				{
					// Only administrators and moderators can see this CPF, if the view is enabled, they can see it, otherwise just admins in the acp_users module
					$sql_ary['field_hide'] = 1;
				}
				else
				{
					// equivelant to "none", which is the "Display in user control panel" option
					$sql_ary['field_show_profile'] = 1;
				}

				_sql('UPDATE ' . PROFILE_FIELDS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE field_id = ' . $row['field_id'], $errored, $error_ary);
			}
			$no_updates = false;

		break;

		// Changes from 3.0.4-RC1 to 3.0.4
		case '3.0.4-RC1':
		break;

		// Changes from 3.0.4 to 3.0.5-RC1
		case '3.0.4':

			// Captcha config variables
			set_config('captcha_gd_wave', 0);
			set_config('captcha_gd_3d_noise', 1);
			set_config('captcha_gd_fonts', 1);
			set_config('confirm_refresh', 1);

			// Maximum number of keywords
			set_config('max_num_search_keywords', 10);

			// Remove static config var and put it back as dynamic variable
			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET is_dynamic = 1
				WHERE config_name = 'search_indexing_state'";
			_sql($sql, $errored, $error_ary);

			// Hash old MD5 passwords
			$sql = 'SELECT user_id, user_password
					FROM ' . USERS_TABLE . '
					WHERE user_pass_convert = 1';
			$result = _sql($sql, $errored, $error_ary);

			while ($row = $db->sql_fetchrow($result))
			{
				if (strlen($row['user_password']) == 32)
				{
					$sql_ary = array(
						'user_password'	=> phpbb_hash($row['user_password']),
					);

					_sql('UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE user_id = ' . $row['user_id'], $errored, $error_ary);
				}
			}
			$db->sql_freeresult($result);

			// Adjust bot entry
			$sql = 'UPDATE ' . BOTS_TABLE . "
				SET bot_agent = 'ichiro/'
				WHERE bot_agent = 'ichiro/2'";
			_sql($sql, $errored, $error_ary);


			// Before we are able to add a unique key to auth_option, we need to remove duplicate entries

			// We get duplicate entries first
			$sql = 'SELECT auth_option
				FROM ' . ACL_OPTIONS_TABLE . '
				GROUP BY auth_option
				HAVING COUNT(*) >= 2';
			$result = $db->sql_query($sql);

			$auth_options = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$auth_options[] = $row['auth_option'];
			}
			$db->sql_freeresult($result);

			// Remove specific auth options
			if (!empty($auth_options))
			{
				foreach ($auth_options as $option)
				{
					// Select auth_option_ids... the largest id will be preserved
					$sql = 'SELECT auth_option_id
						FROM ' . ACL_OPTIONS_TABLE . "
						WHERE auth_option = '" . $db->sql_escape($option) . "'
						ORDER BY auth_option_id DESC";
					// sql_query_limit not possible here, due to bug in postgresql layer
					$result = $db->sql_query($sql);

					// Skip first row, this is our original auth option we want to preserve
					$row = $db->sql_fetchrow($result);

					while ($row = $db->sql_fetchrow($result))
					{
						// Ok, remove this auth option...
						_sql('DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
						_sql('DELETE FROM ' . ACL_ROLES_DATA_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
						_sql('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
						_sql('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id'], $errored, $error_ary);
					}
					$db->sql_freeresult($result);
				}
			}

			// Now make auth_option UNIQUE, by dropping the old index and adding a UNIQUE one.
			$changes = array(
				'drop_keys'			=> array(
					ACL_OPTIONS_TABLE		=> array('auth_option'),
				),
			);

			$statements = $db_tools->perform_schema_changes($changes);

			foreach ($statements as $sql)
			{
				_sql($sql, $errored, $error_ary);
			}

			$changes = array(
				'add_unique_index'	=> array(
					ACL_OPTIONS_TABLE		=> array(
						'auth_option'		=> array('auth_option'),
					),
				),
			);

			$statements = $db_tools->perform_schema_changes($changes);

			foreach ($statements as $sql)
			{
				_sql($sql, $errored, $error_ary);
			}

			$no_updates = false;

		break;

		// No changes from 3.0.5-RC1 to 3.0.5
		case '3.0.5-RC1':
		break;

		// Changes from 3.0.5 to 3.0.6-RC1
		case '3.0.5':
			// Let's see if the GD Captcha can be enabled... we simply look for what *is* enabled...
			if (!empty($config['captcha_gd']) && !isset($config['captcha_plugin']))
			{
				set_config('captcha_plugin', 'phpbb_captcha_gd');
			}
			else if (!isset($config['captcha_plugin']))
			{
				set_config('captcha_plugin', 'phpbb_captcha_nogd');
			}

			// Entries for the Feed Feature
			set_config('feed_enable', '0');
			set_config('feed_limit', '10');

			set_config('feed_overall_forums', '1');
			set_config('feed_overall_forums_limit', '15');

			set_config('feed_overall_topics', '0');
			set_config('feed_overall_topics_limit', '15');

			set_config('feed_forum', '1');
			set_config('feed_topic', '1');
			set_config('feed_item_statistics', '1');

			// Entries for smiley pagination
			set_config('smilies_per_page', '50');

			// Entry for reporting PMs
			set_config('allow_pm_report', '1');

			// Install modules
			$modules_to_install = array(
				'feed'					=> array(
					'base'		=> 'board',
					'class'		=> 'acp',
					'title'		=> 'ACP_FEED_SETTINGS',
					'auth'		=> 'acl_a_board',
					'cat'		=> 'ACP_BOARD_CONFIGURATION',
					'after'		=> array('signature', 'ACP_SIGNATURE_SETTINGS')
				),
				'warnings'				=> array(
					'base'		=> 'users',
					'class'		=> 'acp',
					'title'		=> 'ACP_USER_WARNINGS',
					'auth'		=> 'acl_a_user',
					'display'	=> 0,
					'cat'		=> 'ACP_CAT_USERS',
					'after'		=> array('feedback', 'ACP_USER_FEEDBACK')
				),
				'setting_forum_copy'	=> array(
					'base'		=> 'permissions',
					'class'		=> 'acp',
					'title'		=> 'ACP_FORUM_PERMISSIONS_COPY',
					'auth'		=> 'acl_a_fauth && acl_a_authusers && acl_a_authgroups && acl_a_mauth',
					'cat'		=> 'ACP_FORUM_BASED_PERMISSIONS',
					'after'		=> array('setting_forum_local', 'ACP_FORUM_PERMISSIONS')
				),
				'pm_reports'			=> array(
					'base'		=> 'pm_reports',
					'class'		=> 'mcp',
					'title'		=> 'MCP_PM_REPORTS_OPEN',
					'auth'		=> 'aclf_m_report',
					'cat'		=> 'MCP_REPORTS'
				),
				'pm_reports_closed'		=> array(
					'base'		=> 'pm_reports',
					'class'		=> 'mcp',
					'title'		=> 'MCP_PM_REPORTS_CLOSED',
					'auth'		=> 'aclf_m_report',
					'cat'		=> 'MCP_REPORTS'
				),
				'pm_report_details'		=> array(
					'base'		=> 'pm_reports',
					'class'		=> 'mcp',
					'title'		=> 'MCP_PM_REPORT_DETAILS',
					'auth'		=> 'aclf_m_report',
					'cat'		=> 'MCP_REPORTS'
				),
			);

			_add_modules($modules_to_install);

			// Add newly_registered group... but check if it already exists (we always supported running the updater on any schema)
			$sql = 'SELECT group_id
				FROM ' . GROUPS_TABLE . "
				WHERE group_name = 'NEWLY_REGISTERED'";
			$result = $db->sql_query($sql);
			$group_id = (int) $db->sql_fetchfield('group_id');
			$db->sql_freeresult($result);

			if (!$group_id)
			{
				$sql = 'INSERT INTO ' .  GROUPS_TABLE . " (group_name, group_type, group_founder_manage, group_colour, group_legend, group_avatar, group_desc, group_desc_uid, group_max_recipients) VALUES ('NEWLY_REGISTERED', 3, 0, '', 0, '', '', '', 5)";
				_sql($sql, $errored, $error_ary);

				$group_id = $db->sql_nextid();
			}

			// Insert new user role... at the end of the chain
			$sql = 'SELECT role_id
				FROM ' . ACL_ROLES_TABLE . "
				WHERE role_name = 'ROLE_USER_NEW_MEMBER'
					AND role_type = 'u_'";
			$result = $db->sql_query($sql);
			$u_role = (int) $db->sql_fetchfield('role_id');
			$db->sql_freeresult($result);

			if (!$u_role)
			{
				$sql = 'SELECT MAX(role_order) as max_order_id
					FROM ' . ACL_ROLES_TABLE . "
					WHERE role_type = 'u_'";
				$result = $db->sql_query($sql);
				$next_order_id = (int) $db->sql_fetchfield('max_order_id');
				$db->sql_freeresult($result);

				$next_order_id++;

				$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES ('ROLE_USER_NEW_MEMBER', 'ROLE_DESCRIPTION_USER_NEW_MEMBER', 'u_', $next_order_id)";
				_sql($sql, $errored, $error_ary);
				$u_role = $db->sql_nextid();

				if (!$errored)
				{
					// Now add the correct data to the roles...
					// The standard role says that new users are not able to send a PM, Mass PM, are not able to PM groups
					$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT $u_role, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'u_%' AND auth_option IN ('u_sendpm', 'u_masspm', 'u_masspm_group')";
					_sql($sql, $errored, $error_ary);

					// Add user role to group
					$sql = 'INSERT INTO ' . ACL_GROUPS_TABLE . " (group_id, forum_id, auth_option_id, auth_role_id, auth_setting) VALUES ($group_id, 0, 0, $u_role, 0)";
					_sql($sql, $errored, $error_ary);
				}
			}

			// Insert new forum role
			$sql = 'SELECT role_id
				FROM ' . ACL_ROLES_TABLE . "
				WHERE role_name = 'ROLE_FORUM_NEW_MEMBER'
					AND role_type = 'f_'";
			$result = $db->sql_query($sql);
			$f_role = (int) $db->sql_fetchfield('role_id');
			$db->sql_freeresult($result);

			if (!$f_role)
			{
				$sql = 'SELECT MAX(role_order) as max_order_id
					FROM ' . ACL_ROLES_TABLE . "
					WHERE role_type = 'f_'";
				$result = $db->sql_query($sql);
				$next_order_id = (int) $db->sql_fetchfield('max_order_id');
				$db->sql_freeresult($result);

				$next_order_id++;

				$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES  ('ROLE_FORUM_NEW_MEMBER', 'ROLE_DESCRIPTION_FORUM_NEW_MEMBER', 'f_', $next_order_id)";
				_sql($sql, $errored, $error_ary);
				$f_role = $db->sql_nextid();

				if (!$errored)
				{
					$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT $f_role, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'f_%' AND auth_option IN ('f_noapprove')";
					_sql($sql, $errored, $error_ary);
				}
			}

			// Set every members user_new column to 0 (old users) only if there is no one yet (this makes sure we do not execute this more than once)
			$sql = 'SELECT 1
				FROM ' . USERS_TABLE . '
				WHERE user_new = 0';
			$result = $db->sql_query_limit($sql, 1);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				$sql = 'UPDATE ' . USERS_TABLE . ' SET user_new = 0';
				_sql($sql, $errored, $error_ary);
			}

			// Newly registered users limit
			if (!isset($config['new_member_post_limit']))
			{
				set_config('new_member_post_limit', (!empty($config['enable_queue_trigger'])) ? $config['queue_trigger_posts'] : 0);
			}

			if (!isset($config['new_member_group_default']))
			{
				set_config('new_member_group_default', 0);
			}

			// To mimick the old "feature" we will assign the forum role to every forum, regardless of the setting (this makes sure there are no "this does not work!!!! YUO!!!" posts...
			// Check if the role is already assigned...
			$sql = 'SELECT forum_id
				FROM ' . ACL_GROUPS_TABLE . '
				WHERE group_id = ' . $group_id . '
					AND auth_role_id = ' . $f_role;
			$result = $db->sql_query($sql);
			$is_options = (int) $db->sql_fetchfield('forum_id');
			$db->sql_freeresult($result);

			// Not assigned at all... :/
			if (!$is_options)
			{
				// Get postable forums
				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type != ' . FORUM_LINK;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					_sql('INSERT INTO ' . ACL_GROUPS_TABLE . ' (group_id, forum_id, auth_option_id, auth_role_id, auth_setting) VALUES (' . $group_id . ', ' . (int) $row['forum_id'] . ', 0, ' . $f_role . ', 0)', $errored, $error_ary);
				}
				$db->sql_freeresult($result);
			}

			// Clear permissions...
			include_once($phpbb_root_path . 'includes/acp/auth.php');
			$auth_admin = new auth_admin();
			$auth_admin->acl_clear_prefetch();

			if (!isset($config['allow_avatar']))
			{
				if ($config['allow_avatar_upload'] || $config['allow_avatar_local'] || $config['allow_avatar_remote'])
				{
					set_config('allow_avatar', '1');
				}
				else
				{
					set_config('allow_avatar', '0');
				}
			}

			if (!isset($config['allow_avatar_remote_upload']))
			{
				if ($config['allow_avatar_remote'] && $config['allow_avatar_upload'])
				{
					set_config('allow_avatar_remote_upload', '1');
				}
				else
				{
					set_config('allow_avatar_remote_upload', '0');
				}
			}

			// Minimum number of characters
			if (!isset($config['min_post_chars']))
			{
				set_config('min_post_chars', '1');
			}

			if (!isset($config['allow_quick_reply']))
			{
				set_config('allow_quick_reply', '1');
			}

			// Set every members user_options column to enable
			// bbcode, smilies and URLs for signatures by default
			$sql = 'SELECT user_options
				FROM ' . USERS_TABLE . '
				WHERE user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
			$result = $db->sql_query_limit($sql, 1);
			$user_option = (int) $db->sql_fetchfield('user_options');
			$db->sql_freeresult($result);

			// Check if we already updated the database by checking bit 15 which we used to store the sig_bbcode option
			if (!($user_option & 1 << 15))
			{
				// 229376 is the added value to enable all three signature options
				$sql = 'UPDATE ' . USERS_TABLE . ' SET user_options = user_options + 229376';
				_sql($sql, $errored, $error_ary);
			}

			if (!isset($config['delete_time']))
			{
				set_config('delete_time', $config['edit_time']);
			}

			$no_updates = false;
		break;

		// No changes from 3.0.6-RC1 to 3.0.6-RC2
		case '3.0.6-RC1':
		break;

		// Changes from 3.0.6-RC2 to 3.0.6-RC3
		case '3.0.6-RC2':

			// Update the Custom Profile Fields based on previous settings to the new format
			$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . '
				SET field_show_on_vt = 1
				WHERE field_hide = 0
					AND (field_required = 1 OR field_show_on_reg = 1 OR field_show_profile = 1)';
			_sql($sql, $errored, $error_ary);
			$no_updates = false;

		break;

		// No changes from 3.0.6-RC3 to 3.0.6-RC4
		case '3.0.6-RC3':
		break;

		// No changes from 3.0.6-RC4 to 3.0.6
		case '3.0.6-RC4':
		break;

		// Changes from 3.0.6 to 3.0.7-RC1
		case '3.0.6':

			// ATOM Feeds
			set_config('feed_overall', '1');
			set_config('feed_http_auth', '0');
			set_config('feed_limit_post', (string) (isset($config['feed_limit']) ? (int) $config['feed_limit'] : 15));
			set_config('feed_limit_topic', (string) (isset($config['feed_overall_topics_limit']) ? (int) $config['feed_overall_topics_limit'] : 10));
			set_config('feed_topics_new', (!empty($config['feed_overall_topics']) ? '1' : '0'));
			set_config('feed_topics_active', (!empty($config['feed_overall_topics']) ? '1' : '0'));

			// Delete all text-templates from the template_data
			$sql = 'DELETE FROM ' . STYLES_TEMPLATE_DATA_TABLE . '
				WHERE template_filename ' . $db->sql_like_expression($db->any_char . '.txt');
			_sql($sql, $errored, $error_ary);

			$no_updates = false;
		break;

		// Changes from 3.0.7-RC1 to 3.0.7-RC2
		case '3.0.7-RC1':

			$sql = 'SELECT user_id, user_email, user_email_hash
				FROM ' . USERS_TABLE . '
				WHERE user_type <> ' . USER_IGNORE . "
					AND user_email <> ''";
			$result = $db->sql_query($sql);

			$i = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				// Snapshot of the phpbb_email_hash() function
				// We cannot call it directly because the auto updater updates the DB first. :/
				$user_email_hash = sprintf('%u', crc32(strtolower($row['user_email']))) . strlen($row['user_email']);

				if ($user_email_hash != $row['user_email_hash'])
				{
					$sql_ary = array(
						'user_email_hash'	=> $user_email_hash,
					);

					$sql = 'UPDATE ' . USERS_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE user_id = ' . (int) $row['user_id'];
					_sql($sql, $errored, $error_ary, ($i % 100 == 0));

					++$i;
				}
			}
			$db->sql_freeresult($result);

			$no_updates = false;

		break;

		// No changes from 3.0.7-RC2 to 3.0.7
		case '3.0.7-RC2':
		break;

		// No changes from 3.0.7 to 3.0.7-PL1
		case '3.0.7':
		break;

		// Changes from 3.0.7-PL1 to 3.0.8-RC1
		case '3.0.7-PL1':
			// Update file extension group names to use language strings.
			$sql = 'SELECT lang_dir
				FROM ' . LANG_TABLE;
			$result = $db->sql_query($sql);

			$extension_groups_updated = array();
			while ($lang_dir = $db->sql_fetchfield('lang_dir'))
			{
				$lang_dir = basename($lang_dir);

				// The language strings we need are either in language/.../acp/attachments.php
				// in the update package if we're updating to 3.0.8-RC1 or later,
				// or they are in language/.../install.php when we're updating from 3.0.7-PL1 or earlier.
				// On an already updated board, they can also already be in language/.../acp/attachments.php
				// in the board root.
				$lang_files = array(
					"{$phpbb_root_path}install/update/new/language/$lang_dir/acp/attachments.php",
					"{$phpbb_root_path}language/$lang_dir/install.php",
					"{$phpbb_root_path}language/$lang_dir/acp/attachments.php",
				);

				foreach ($lang_files as $lang_file)
				{
					if (!file_exists($lang_file))
					{
						continue;
					}

					$lang = array();
					include($lang_file);

					foreach ($lang as $lang_key => $lang_val)
					{
						if (isset($extension_groups_updated[$lang_key]) || strpos($lang_key, 'EXT_GROUP_') !== 0)
						{
							continue;
						}

						$sql_ary = array(
							'group_name'	=> substr($lang_key, 10), // Strip off 'EXT_GROUP_'
						);

						$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE group_name = '" . $db->sql_escape($lang_val) . "'";
						_sql($sql, $errored, $error_ary);

						$extension_groups_updated[$lang_key] = true;
					}
				}
			}
			$db->sql_freeresult($result);

			// Install modules
			$modules_to_install = array(
				'post'					=> array(
					'base'		=> 'board',
					'class'		=> 'acp',
					'title'		=> 'ACP_POST_SETTINGS',
					'auth'		=> 'acl_a_board',
					'cat'		=> 'ACP_MESSAGES',
					'after'		=> array('message', 'ACP_MESSAGE_SETTINGS')
				),
			);

			_add_modules($modules_to_install);

			// update
			$sql = 'UPDATE ' . MODULES_TABLE . '
				SET module_auth = \'cfg_allow_avatar && (cfg_allow_avatar_local || cfg_allow_avatar_remote || cfg_allow_avatar_upload || cfg_allow_avatar_remote_upload)\'
				WHERE module_class = \'ucp\'
					AND module_basename = \'profile\'
					AND module_mode = \'avatar\'';
			_sql($sql, $errored, $error_ary);

			// Delete shadow topics pointing to not existing topics
			$batch_size = 500;

			// Set of affected forums we have to resync
			$sync_forum_ids = array();

			do
			{
				$sql_array = array(
					'SELECT'	=> 't1.topic_id, t1.forum_id',
					'FROM'		=> array(
						TOPICS_TABLE	=> 't1',
					),
					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TOPICS_TABLE	=> 't2'),
							'ON'	=> 't1.topic_moved_id = t2.topic_id',
						),
					),
					'WHERE'		=> 't1.topic_moved_id <> 0
								AND t2.topic_id IS NULL',
				);
				$sql = $db->sql_build_query('SELECT', $sql_array);
				$result = $db->sql_query_limit($sql, $batch_size);

				$topic_ids = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$topic_ids[] = (int) $row['topic_id'];

					$sync_forum_ids[(int) $row['forum_id']] = (int) $row['forum_id'];
				}
				$db->sql_freeresult($result);

				if (!empty($topic_ids))
				{
					$sql = 'DELETE FROM ' . TOPICS_TABLE . '
						WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
					$db->sql_query($sql);
				}
			}
			while (sizeof($topic_ids) == $batch_size);

			// Sync the forums we have deleted shadow topics from.
			sync('forum', 'forum_id', $sync_forum_ids, true, true);

			// Unread posts search load switch
			set_config('load_unreads_search', '1');

			// Reduce queue interval to 60 seconds, email package size to 20
			if ($config['queue_interval'] == 600)
			{
				set_config('queue_interval', '60');
			}

			if ($config['email_package_size'] == 50)
			{
				set_config('email_package_size', '20');
			}

			$no_updates = false;
		break;

		// No changes from 3.0.8-RC1 to 3.0.8
		case '3.0.8-RC1':
		break;

		// Changes from 3.0.8 to 3.0.9-RC1
		case '3.0.8':
			set_config('ip_login_limit_max', '50');
			set_config('ip_login_limit_time', '21600');
			set_config('ip_login_limit_use_forwarded', '0');

			// Update file extension group names to use language strings, again.
			$sql = 'SELECT group_id, group_name
				FROM ' . EXTENSION_GROUPS_TABLE . '
				WHERE group_name ' . $db->sql_like_expression('EXT_GROUP_' . $db->any_char);
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary = array(
					'group_name'	=> substr($row['group_name'], 10), // Strip off 'EXT_GROUP_'
				);

				$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE group_id = ' . $row['group_id'];
				_sql($sql, $errored, $error_ary);
			}
			$db->sql_freeresult($result);

			$no_updates = false;
		break;

		// No changes from 3.0.9-RC1 to 3.0.9-RC2
		case '3.0.9-RC1':
		break;

		// No changes from 3.0.9-RC2 to 3.0.9-RC3
		case '3.0.9-RC2':
		break;

		// No changes from 3.0.9-RC3 to 3.0.9-RC4
		case '3.0.9-RC3':
		break;

		// No changes from 3.0.9-RC4 to 3.0.9
		case '3.0.9-RC4':
		break;

		// Changes from 3.0.9 to 3.0.10-RC1
		case '3.0.9':
			if (!isset($config['email_max_chunk_size']))
			{
				set_config('email_max_chunk_size', '50');
			}

			$no_updates = false;
		break;

		// No changes from 3.0.10-RC1 to 3.0.10-RC2
		case '3.0.10-RC1':
		break;

		// No changes from 3.0.10-RC2 to 3.0.10-RC3
		case '3.0.10-RC2':
		break;

		// No changes from 3.0.10-RC3 to 3.0.10
		case '3.0.10-RC3':
		break;

		// Changes from 3.0.10 to 3.0.11-RC1
		case '3.0.10':
			// Updates users having current style a deactivated one
			$sql = 'SELECT style_id
				FROM ' . STYLES_TABLE . '
				WHERE style_active = 0';
			$result = $db->sql_query($sql);

			$deactivated_style_ids = array();
			while ($style_id = $db->sql_fetchfield('style_id', false, $result))
			{
				$deactivated_style_ids[] = (int) $style_id;
			}
			$db->sql_freeresult($result);

			if (!empty($deactivated_style_ids))
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_style = ' . (int) $config['default_style'] .'
					WHERE ' . $db->sql_in_set('user_style', $deactivated_style_ids);
				_sql($sql, $errored, $error_ary);
			}

			// Delete orphan private messages
			$batch_size = 500;

			$sql_array = array(
				'SELECT'	=> 'p.msg_id',
				'FROM'		=> array(
					PRIVMSGS_TABLE	=> 'p',
				),
				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(PRIVMSGS_TO_TABLE => 't'),
						'ON'	=> 'p.msg_id = t.msg_id',
					),
				),
				'WHERE'		=> 't.user_id IS NULL',
			);
			$sql = $db->sql_build_query('SELECT', $sql_array);

			do
			{
				$result = $db->sql_query_limit($sql, $batch_size);

				$delete_pms = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$delete_pms[] = (int) $row['msg_id'];
				}
				$db->sql_freeresult($result);

				if (!empty($delete_pms))
				{
					$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
						WHERE ' . $db->sql_in_set('msg_id', $delete_pms);
					_sql($sql, $errored, $error_ary);
				}
			}
			while (sizeof($delete_pms) == $batch_size);

			$no_updates = false;
		break;

		// No changes from 3.0.11-RC1 to 3.0.11-RC2
		case '3.0.11-RC1':
		break;

		// No changes from 3.0.11-RC2 to 3.0.11
		case '3.0.11-RC2':
		break;

		// Changes from 3.0.11 to 3.0.12-RC1
		case '3.0.11':
			$sql = 'UPDATE ' . MODULES_TABLE . '
				SET module_auth = \'acl_u_sig\'
				WHERE module_class = \'ucp\'
					AND module_basename = \'profile\'
					AND module_mode = \'signature\'';
			_sql($sql, $errored, $error_ary);

			/**
			* Update BBCodes that currently use the LOCAL_URL tag
			*
			* To fix http://tracker.phpbb.com/browse/PHPBB3-8319 we changed
			* the second_pass_replace value, so that needs updating for existing ones
			*/
			$sql = 'SELECT *
				FROM ' . BBCODES_TABLE . '
				WHERE bbcode_match ' . $db->sql_like_expression($db->any_char . 'LOCAL_URL' . $db->any_char);
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if (!class_exists('acp_bbcodes'))
				{
					require($phpbb_root_path . 'includes/acp/acp_bbcodes.php');
				}
				$bbcode_match = $row['bbcode_match'];
				$bbcode_tpl = $row['bbcode_tpl'];

				$acp_bbcodes = new acp_bbcodes();
				$sql_ary = $acp_bbcodes->build_regexp($bbcode_match, $bbcode_tpl);

				$sql = 'UPDATE ' . BBCODES_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE bbcode_id = ' . (int) $row['bbcode_id'];
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			$no_updates = false;
		break;

		// No changes from 3.0.12-RC1 to 3.0.12-RC2
		case '3.0.12-RC1':
		break;

		// No changes from 3.0.12-RC2 to 3.0.12-RC3
		case '3.0.12-RC2':
		break;

		// No changes from 3.0.12-RC3 to 3.0.12
		case '3.0.12-RC3':
		break;

		// No changes from 3.0.12 to 3.0.13-RC1
		case '3.0.12':
		break;

		// No changes from 3.0.13-RC1 to 3.0.13
		case '3.0.13-RC1':
		break;

		// No changes from 3.0.13 to 3.0.13-PL1
		case '3.0.13':
		break;

		// No changes from 3.0.13-PL1 to 3.0.14-RC1
		case '3.0.13-PL1':
		break;

		// No changes from 3.0.14-RC1 to 3.0.14
		case '3.0.14-RC1':
		break;
	}
}
