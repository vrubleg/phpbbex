<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
define('IN_INSTALL', true);

if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './../'); }
require_once(PHPBB_ROOT_PATH . 'includes/startup.php');

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
	if (file_exists(PHPBB_ROOT_PATH . 'cache/allow_upd_' . $key . '.key'))
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

require_once(PHPBB_ROOT_PATH . 'includes/acm/acm_' . $acm_type . '.php');
require_once(PHPBB_ROOT_PATH . 'includes/cache.php');
require_once(PHPBB_ROOT_PATH . 'includes/template.php');
require_once(PHPBB_ROOT_PATH . 'includes/session.php');
require_once(PHPBB_ROOT_PATH . 'includes/auth.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_content.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_install.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions_user.php');
require_once(PHPBB_ROOT_PATH . 'includes/constants.php');
require_once(PHPBB_ROOT_PATH . 'includes/db/mysql.php');
require_once(PHPBB_ROOT_PATH . 'includes/utf/utf_tools.php');
require_once(PHPBB_ROOT_PATH . 'includes/db/db_tools.php');

// The cache, files, and images/avatars/upload directories have to be writeable!
if (!phpbb_is_writable(PHPBB_ROOT_PATH . 'cache')) { die('Make "cache" directory writeable!'); }
if (!phpbb_is_writable(PHPBB_ROOT_PATH . UPLOADS_PATH)) { die('Make "' . UPLOADS_PATH . '" directory writeable!'); }
if (!phpbb_is_writable(PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH)) { die('Make "' . AVATAR_UPLOADS_PATH . '" directory writeable!'); }

$user = new phpbb_user();
$cache = new phpbb_cache();
$db = new dbal_mysql();

$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);
unset($dbpasswd); // For safety purposes.
$db_tools = new phpbb_db_tools($db, true);

$user->ip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';
$user->ip = (stripos($user->ip, '::ffff:') === 0) ? substr($user->ip, 7) : $user->ip;

// Load config.
$config = [];
$sql = 'SELECT * FROM ' . CONFIG_TABLE;
$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
{
	$config[$row['config_name']] = $row['config_value'];
}
$db->sql_freeresult($result);

// Check phpBBex version.

if (!empty($config['phpbbex_version']) && version_compare($config['phpbbex_version'], '1.10.0', '>'))
{
	die('Error! Database schema has newer version than supported.');
}

// Helper functions.

function remove_module($module_class, $module_basename, $module_mode)
{
	global $db;

	$sql = 'SELECT * FROM ' . MODULES_TABLE . "
		WHERE module_class = '" . $db->sql_escape($module_class) . "'
			AND module_basename = '" . $db->sql_escape($module_basename) . "'
			AND " . ($module_basename ? 'module_mode' : 'module_langname') . " = '" . $db->sql_escape($module_mode) . "'";
	$result = $db->sql_query($sql);
	$rowset = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	if (!$rowset || count($rowset) != 1)
	{
		return false;
	}

	$row = $rowset[0];
	$row['module_id'] = (int) $row['module_id'];
	$row['left_id'] = (int) $row['left_id'];
	$row['right_id'] = (int) $row['right_id'];

	if ($row['left_id'] + 1 != $row['right_id'])
	{
		// Can't remove, it has some children. Should not happen.
		return false;
	}

	$sql = 'DELETE FROM ' . MODULES_TABLE . "
		WHERE module_class = '" . $db->sql_escape($module_class) . "'
			AND module_id = {$row['module_id']}";
	$db->sql_query($sql);

	// Resync tree
	$diff = 2;

	$sql = 'UPDATE ' . MODULES_TABLE . "
		SET right_id = right_id - {$diff}
		WHERE module_class = '" . $db->sql_escape($module_class) . "'
			AND left_id < {$row['right_id']} AND right_id > {$row['right_id']}";
	$db->sql_query($sql);

	$sql = 'UPDATE ' . MODULES_TABLE . "
		SET left_id = left_id - {$diff}, right_id = right_id - {$diff}
		WHERE module_class = '" . $db->sql_escape($module_class) . "'
			AND left_id > {$row['right_id']}";
	$db->sql_query($sql);

	return true;
}

function remove_module_category($module_class, $module_langname)
{
	return remove_module($module_class, '', $module_langname);
}

function remove_permissions($permissions)
{
	global $db, $cache;

	$option_ids = [];

	$result = $db->sql_query('SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE. ' WHERE ' . $db->sql_in_set('auth_option', $permissions));
	while ($row = $db->sql_fetchrow($result))
	{
		$option_ids[] = (int) $row['auth_option_id'];
	}
	$db->sql_freeresult($result);

	if (!empty($option_ids))
	{
		foreach ([ACL_GROUPS_TABLE, ACL_ROLES_DATA_TABLE, ACL_USERS_TABLE, ACL_OPTIONS_TABLE] as $table)
		{
			$db->sql_query("DELETE FROM {$table} WHERE " . $db->sql_in_set('auth_option_id', $option_ids));
		}

		// Reset permissions cache...
		$cache->destroy('_acl_options');
		require_once(PHPBB_ROOT_PATH . 'includes/acp/auth.php');
		$auth_admin = new auth_admin();
		$auth_admin->acl_clear_prefetch();
	}
}

function remove_config_values($names)
{
	global $db;

	$db->sql_query('DELETE FROM ' . CONFIG_TABLE . " WHERE config_name IN ('" . implode("', '", $names) . "')");
}

// Update!

$purge_default = 'cache';
$bots_default = false;

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

	// Reset options for all users (enable quick reply, etc)
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " MODIFY user_options int(11) UNSIGNED DEFAULT '233343' NOT NULL");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_options = 233343");

	// Show all forums in active topics
	$db->sql_query("ALTER TABLE " . FORUMS_TABLE . " MODIFY forum_flags tinyint(4) DEFAULT '16' NOT NULL");
	$db->sql_query("UPDATE " . FORUMS_TABLE . " SET forum_flags = forum_flags|16");

	// Reset CAPTCHA settings.
	set_config('captcha_plugin', extension_loaded('gd') ? 'phpbb_captcha_gd' : 'phpbb_captcha_nogd');
	set_config('captcha_gd_foreground_noise', 0);
	set_config('captcha_gd_x_grid', 25);
	set_config('captcha_gd_y_grid', 25);
	set_config('captcha_gd_wave', 0);
	set_config('captcha_gd_3d_noise', 1);
	set_config('captcha_gd_fonts', 1);
	set_config('confirm_refresh', 1);

	// Remove obsolete config values.
	remove_config_values([
		'style_show_liveinternet_counter',
		'style_google_analytics_id',
		'copyright_notice_html',
		'style_auto_new_year',
	]);

	// Remove obsolete .htaccess file that would prevent direct access to uploaded avatars.
	@unlink(PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH . '/.htaccess');

	// New defaults.
	set_config('active_topics_on_index', '5');
	set_config('active_topics_days', '30');
	set_config('active_users_days', '90');
	set_config('announce_index', '1');
	set_config('copyright_notice', '');
	set_config('max_post_imgs', '0');
	set_config('max_sig_imgs', '0');
	set_config('max_sig_lines', '4');
	set_config('max_spoiler_depth', '2');
	set_config('merge_interval', '18');
	set_config('outlinks', '');
	set_config('override_user_lang', '0');
	set_config('override_user_timezone', '0');
	set_config('site_keywords', '');
	set_config('warning_post_default', '');
	set_config('auto_guest_lang', '0');
	set_config('default_search_titleonly', '0');
	set_config('search_highlight_keywords', '0');
	set_config('rate_enabled', '1');
	set_config('rate_only_topics', '0');
	set_config('rate_time', 0);
	set_config('rate_topic_time', -1);
	set_config('rate_change_time', 60*5);
	set_config('rate_no_negative', '0');
	set_config('rate_no_positive', '0');
	set_config('style_min_width', '875');
	set_config('style_max_width', '1280');
	set_config('style_back_to_top', '1');
	set_config('style_rounded_corners', '1');
	set_config('style_new_year', '-1');
	set_config('style_show_sitename_in_headerbar', '1');
	set_config('style_show_feeds_in_forumlist', '0');
	set_config('style_vt_show_post_numbers', '0');
	set_config('display_raters', '0');
	set_config('style_mp_on_left', '0');
	set_config('style_mp_show_topic_poster', '0');
	set_config('style_mp_show_gender', '1');
	set_config('style_mp_show_age', '1');
	set_config('style_mp_show_from', '1');
	set_config('style_mp_show_warnings', '1');
	set_config('style_mp_show_rating', '1');
	set_config('style_mp_show_rating_detailed', '0');
	set_config('style_mp_show_rated', '0');
	set_config('style_mp_show_rated_detailed', '0');
	set_config('style_mp_show_posts', '0');
	set_config('style_mp_show_topics', '0');
	set_config('style_mp_show_joined', '0');
	set_config('style_mp_show_with_us', '1');
	set_config('style_mp_show_buttons', '1');
	set_config('style_p_show_rating', '1');
	set_config('style_p_show_rating_detailed', '1');
	set_config('style_p_show_rated', '0');
	set_config('style_p_show_rated_detailed', '0');
	set_config('style_ml_show_row_numbers', '1');
	set_config('style_ml_show_gender', '1');
	set_config('style_ml_show_rank', '1');
	set_config('style_ml_show_rating', '1');
	set_config('style_ml_show_rating_detailed', '0');
	set_config('style_ml_show_rated', '0');
	set_config('style_ml_show_rated_detailed', '0');
	set_config('style_ml_show_posts', '1');
	set_config('style_ml_show_topics', '1');
	set_config('style_ml_show_from', '1');
	set_config('style_ml_show_website', '0');
	set_config('style_ml_show_joined', '1');
	set_config('style_ml_show_last_active', '1');
	set_config('avatar_max_height', '100');
	set_config('avatar_max_width', '100');
	set_config('avatar_min_height', '64');
	set_config('avatar_min_width', '64');
	set_config('allow_sig_bbcode', '0');
	set_config('allow_sig_img', '0');
	set_config('allow_sig_links', '0');
	set_config('allow_sig_smilies', '0');
	set_config('max_sig_chars', '200');
	set_config('require_activation', '1');
	set_config('default_dateformat', '|d.m.Y|{, H:i}');
	set_config('edit_time', '60');
	set_config('delete_time', '15');
	set_config('feed_enable', '1');
	set_config('feed_item_statistics', '0');
	set_config('feed_overall', '0');
	set_config('load_moderators', '0');
	set_config('max_poll_options', '25');
	set_config('max_post_smilies', '20');
	set_config('max_post_urls', '20');
	set_config('max_quote_depth', '2');
	set_config('pm_max_msgs', '1000');
	set_config('posts_per_page', '20');
	set_config('topics_per_page', '50');
	set_config('external_links_newwindow', '0');
	set_config('external_links_newwindow_exclude', '');
	set_config('min_post_font_size', '85');
	set_config('max_post_font_size', '200');
	set_config('min_sig_font_size', '100');
	set_config('max_sig_font_size', '100');
	set_config('phpbbex_version', '1.7.0');
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
	set_config('phpbbex_version', '1.8.0');
}

if (version_compare($config['phpbbex_version'], '1.9.5', '<'))
{
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " ADD COLUMN user_telegram varchar(32) DEFAULT '' NOT NULL AFTER user_skype");
	set_config('phpbbex_version', '1.9.5');
}

if (version_compare($config['phpbbex_version'], '1.9.6', '<'))
{
	// The COPPA group is not special anymore.

	$db->sql_query("UPDATE " . GROUPS_TABLE . " SET group_type = " . GROUP_HIDDEN . " WHERE group_name = 'REGISTERED_COPPA'");
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

	set_config('phpbbex_version', '1.9.6');
}

if (version_compare($config['phpbbex_version'], '1.9.7', '<'))
{
	// Update schema.

	$db->sql_return_on_error(true);
	$db->sql_query("ALTER TABLE " . RANKS_TABLE . " ADD COLUMN rank_hide_title tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER rank_title");
	$db->sql_return_on_error(false);

	// Remove obsolete permissions.

	remove_permissions([
		'a_jabber',
		'f_email',
		'f_print',
		'f_subscribe',
		'u_pm_delete',
		'u_pm_emailpm',
		'u_pm_forward',
		'u_pm_printpm',
		'u_savedrafts',
	]);

	$db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'acl_a_server' WHERE module_auth = 'acl_a_jabber'");

	// Add new config options.

	$db->sql_query("INSERT IGNORE INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('smtp_verify_cert', '1')");

	// Remove obsolete config values.

	remove_config_values([
		'forward_pm',
		'print_pm',
		'email_function_name',
		'premium_key',
		'img_imagick',
		'upload_path',
		'avatar_gallery_path',
		'avatar_path',
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
		'captcha_gd',
	]);

	// Update DB schema version.

	set_config('phpbbex_version', '1.9.7');
}

if (version_compare($config['phpbbex_version'], '1.9.8', '<'))
{
	// Remove unused columns.

	$db->sql_return_on_error(true);
	$db->sql_query("ALTER TABLE " . FORUMS_TABLE . " DROP COLUMN enable_icons");
	$db->sql_return_on_error(false);

	// Remove obsolete permissions.

	remove_permissions([
		'f_icons',
	]);

	// Remove obsolete FORUM_FLAG_QUICK_REPLY and FORUM_FLAG_POST_REVIEW from forum_flags.

	$allowed_forum_flags = FORUM_FLAG_LINK_TRACK | FORUM_FLAG_PRUNE_POLL | FORUM_FLAG_PRUNE_ANNOUNCE | FORUM_FLAG_PRUNE_STICKY | FORUM_FLAG_ACTIVE_TOPICS;
	update_bitfield_column(FORUMS_TABLE, 'forum_flags', 0, $allowed_forum_flags);

	// Remove obsolete config values.

	remove_config_values([
		'recaptcha_privkey',
		'recaptcha_pubkey',
		'social_media_cover_url',
		'dbms_version',
		'allow_quick_reply_options',
		'allow_quick_post_options',
		'style_posting_topic_review',
		'no_typical_info_pages',
	]);

	// Update obsolete values.

	if (!in_array($config['allow_name_chars'], ['USERNAME_UNICHARS_SPACERS', 'USERNAME_UNICHARS_NOSPACE', 'USERNAME_LATCHARS_SPACERS', 'USERNAME_LATCHARS_NOSPACE']))
	{
		set_config('allow_name_chars', 'USERNAME_UNICHARS_SPACERS');
	}

	// Reset CAPTCHA plugin if obsolete reCAPTCHA v1 is used.

	if ($config['captcha_plugin'] == 'phpbb_recaptcha')
	{
		set_config('captcha_plugin', extension_loaded('gd') ? 'phpbb_captcha_gd' : 'phpbb_captcha_nogd');
	}

	// New config values.

	set_config('enable_topic_icons', '1');
	set_config('allow_quick_reply', '2');
	set_config('allow_quick_reply_subject', '0');
	set_config('allow_quick_reply_checkboxes', '1');
	set_config('allow_quick_reply_attachbox', '1');
	set_config('allow_quick_reply_smilies', '1');
	set_config('allow_quick_full_quote', '0');
	set_config('allow_quick_post', '1');
	set_config('allow_quick_post_icons', '1');
	set_config('allow_quick_post_checkboxes', '1');
	set_config('allow_quick_post_attachbox', '1');
	set_config('allow_quick_post_smilies', '1');
	set_config('posting_topic_review', '1');
	set_config('skip_typical_notices', '1');

	// Update DB schema version.

	set_config('phpbbex_version', '1.9.8');
}

if (version_compare($config['phpbbex_version'], '1.9.9', '<'))
{
	// Normalize old avatar file names.

	$sql = 'SELECT user_id, user_avatar FROM ' . USERS_TABLE . ' WHERE user_avatar_type = ' . AVATAR_UPLOAD;
	$result = $db->sql_query($sql);
	$batch = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	foreach ($batch as $row)
	{
		$avatar = $row['user_avatar'];
		if (!$avatar) { continue; }

		if (strpos($avatar, '_') !== false)
		{
			$target_path = PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH . '/' . $avatar;
			if (file_exists($target_path)) { continue; }

			// Strip timestamp part.
			$avatar = strchr($avatar, '_', true) . strrchr($avatar, '.');

			// phpBB 3.0 file name in db, but phpBBex 1.5 in filesystem.
			$old_path = PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH . '/' . $avatar;
			if (file_exists($old_path))
			{
				rename($old_path, $target_path);
				continue;
			}

			// phpBB 3.0 avatar file name in both places.
			if (isset($config['avatar_salt']))
			{
				$old_path = PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH . '/' . $config['avatar_salt'] . '_' . $avatar;
				if (file_exists($old_path))
				{
					rename($old_path, $target_path);
				}
			}
		}
		else
		{
			// phpBBex 1.5 file name. Add current mtime to filenames.

			$old_path = PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH . '/' . $avatar;
			if (!file_exists($old_path)) { continue; }

			[$name, $ext] = explode('.', $avatar);
			if ($ext == 'jpeg') { $ext = 'jpg'; }
			$avatar = $name . '_' . filemtime($old_path) . '.' . $ext;

			$target_path = PHPBB_ROOT_PATH . AVATAR_UPLOADS_PATH . '/' . $avatar;
			if (rename($old_path, $target_path))
			{
				$db->sql_query("UPDATE " . USERS_TABLE . " SET user_avatar = '" . $db->sql_escape($avatar) . "' WHERE user_id = " . intval($row['user_id']));
			}
		}
	}

	// New settings.

	if (!isset($config['email_force_sender']))
	{
		set_config('email_force_sender', '0');
	}

	// Remove obsolete config values.

	remove_config_values([
		'rand_seed',
		'rand_seed_last_update',
		'hot_threshold',
		'avatar_salt',
		'override_user_dst',
		'style_counter_html_1',
		'style_counter_html_2',
		'style_counter_html_3',
		'style_counter_html_4',
		'style_counter_html_5',
	]);

	// Remove no longer used columns.

	$db->sql_return_on_error(true);
	$db->sql_query('ALTER TABLE ' . GROUPS_TABLE . ' DROP COLUMN group_avatar');
	$db->sql_query('ALTER TABLE ' . GROUPS_TABLE . ' DROP COLUMN group_avatar_type');
	$db->sql_query('ALTER TABLE ' . GROUPS_TABLE . ' DROP COLUMN group_avatar_width');
	$db->sql_query('ALTER TABLE ' . GROUPS_TABLE . ' DROP COLUMN group_avatar_height');
	$db->sql_query('ALTER TABLE ' . FORUMS_TABLE . ' DROP COLUMN forum_style');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_topic_show_days');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_post_show_days');
	$db->sql_return_on_error(false);

	// Update DB schema version.

	set_config('phpbbex_version', '1.9.9');
}

// Not ready yet. Replace '<=' by '<' before the release.
if (version_compare($config['phpbbex_version'], '1.10.0', '<='))
{
	// Remove obsolete Gallery stuff.
	remove_module('acp', 'gallery', 'import_images');
	remove_permissions(['a_gallery_import']);

	$db->sql_query("UPDATE " . MODULES_TABLE . "
		SET module_display = 0
		WHERE module_class = 'ucp'
			AND module_basename = 'gallery'
			AND module_mode = 'manage_albums'");

	remove_config_values([
		'phpbb_gallery_allow_zip',
		'phpbb_gallery_contests_ended',
		'phpbb_gallery_disp_nextprev_thumbnail',
		'phpbb_gallery_gdlib_version',
		'phpbb_gallery_rrc_gindex_contests',
		'phpbb_gallery_watermark_changed',
		'phpbb_gallery_watermark_enabled',
		'phpbb_gallery_watermark_height',
		'phpbb_gallery_watermark_position',
		'phpbb_gallery_watermark_source',
		'phpbb_gallery_watermark_width',
	]);

	if ($db_tools->sql_table_exists(GALLERY_ALBUMS_TABLE) && $db_tools->sql_column_exists(GALLERY_ALBUMS_TABLE, 'album_watermark'))
	{
		foreach ($db_tools->sql_column_remove(GALLERY_ALBUMS_TABLE, 'album_watermark') as $sql)
		{
			$db->sql_query($sql);
		}
	}
	if ($db_tools->sql_table_exists(GALLERY_ALBUMS_TABLE))
	{
		// Convert contest albums to regular upload albums before removing contest metadata.
		$db->sql_query('UPDATE ' . GALLERY_ALBUMS_TABLE . '
			SET album_type = 1
			WHERE album_type = 2');

		if ($db_tools->sql_column_exists(GALLERY_ALBUMS_TABLE, 'album_contest'))
		{
			foreach ($db_tools->sql_column_remove(GALLERY_ALBUMS_TABLE, 'album_contest') as $sql)
			{
				$db->sql_query($sql);
			}
		}

		// Merge every personal album tree into its owner's main personal album.
		if ($db_tools->sql_table_exists(GALLERY_USERS_TABLE) && $db_tools->sql_table_exists(GALLERY_IMAGES_TABLE))
		{
			$sql = 'SELECT a.album_id, a.parent_id, a.left_id, a.album_user_id, gu.personal_album_id
				FROM ' . GALLERY_ALBUMS_TABLE . ' a
				LEFT JOIN ' . GALLERY_USERS_TABLE . ' gu
					ON gu.user_id = a.album_user_id
				WHERE a.album_user_id <> 0
				ORDER BY a.album_user_id, a.left_id, a.album_id';
			$result = $db->sql_query($sql);
			$personal_album_trees = [];
			while ($row = $db->sql_fetchrow($result))
			{
				$personal_album_trees[(int) $row['album_user_id']][] = $row;
			}
			$db->sql_freeresult($result);

			foreach ($personal_album_trees as $album_user_id => $albums)
			{
				$main_album_id = (int) $albums[0]['personal_album_id'];
				$album_ids = array_map(function ($album)
				{
					return (int) $album['album_id'];
				}, $albums);

				if (!in_array($main_album_id, $album_ids))
				{
					$main_album_id = 0;
					foreach ($albums as $album)
					{
						if (!$album['parent_id'])
						{
							$main_album_id = (int) $album['album_id'];
							break;
						}
					}
					$main_album_id = $main_album_id ?: (int) $albums[0]['album_id'];
				}

				$subalbum_ids = array_values(array_diff($album_ids, [$main_album_id]));
				if ($subalbum_ids)
				{
					$db->sql_query('UPDATE ' . GALLERY_IMAGES_TABLE . '
						SET image_album_id = ' . $main_album_id . '
						WHERE ' . $db->sql_in_set('image_album_id', $subalbum_ids));

					if ($db_tools->sql_table_exists(GALLERY_REPORTS_TABLE))
					{
						$db->sql_query('UPDATE ' . GALLERY_REPORTS_TABLE . '
							SET report_album_id = ' . $main_album_id . '
							WHERE ' . $db->sql_in_set('report_album_id', $subalbum_ids));
					}

					$db->sql_query('DELETE FROM ' . LOG_TABLE . '
						WHERE log_type = ' . LOG_GALLERY . '
							AND ' . $db->sql_in_set('album_id', $subalbum_ids));

					foreach ([GALLERY_ATRACK_TABLE, GALLERY_WATCH_TABLE, GALLERY_PERMISSIONS_TABLE, GALLERY_MODSCACHE_TABLE] as $table)
					{
						if ($db_tools->sql_table_exists($table))
						{
							$column = ($table == GALLERY_PERMISSIONS_TABLE) ? 'perm_album_id' : 'album_id';
							$db->sql_query('DELETE FROM ' . $table . '
								WHERE ' . $db->sql_in_set($column, $subalbum_ids));
						}
					}

					$db->sql_query('DELETE FROM ' . GALLERY_ALBUMS_TABLE . '
						WHERE ' . $db->sql_in_set('album_id', $subalbum_ids));
				}

				$db->sql_query('UPDATE ' . GALLERY_USERS_TABLE . '
					SET personal_album_id = ' . $main_album_id . '
					WHERE user_id = ' . $album_user_id);

				$sql = 'SELECT COUNT(image_id) AS images_real
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE image_status <> 3
						AND image_album_id = ' . $main_album_id;
				$result = $db->sql_query($sql);
				$images_real = (int) $db->sql_fetchfield('images_real');
				$db->sql_freeresult($result);

				$sql = 'SELECT COUNT(image_id) AS images
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE image_status <> 0
						AND image_status <> 3
						AND image_album_id = ' . $main_album_id;
				$result = $db->sql_query($sql);
				$images = (int) $db->sql_fetchfield('images');
				$db->sql_freeresult($result);

				$sql = 'SELECT image_id, image_time, image_name, image_username, image_user_colour, image_user_id
					FROM ' . GALLERY_IMAGES_TABLE . '
					WHERE image_status <> 0
						AND image_status <> 3
						AND image_album_id = ' . $main_album_id . '
					ORDER BY image_time DESC';
				$result = $db->sql_query_limit($sql, 1);
				$last_image = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$album_data = [
					'parent_id'                 => 0,
					'left_id'                   => 1,
					'right_id'                  => 2,
					'album_parents'             => '',
					'album_images'              => $images,
					'album_images_real'         => $images_real,
					'album_last_image_id'       => $last_image ? (int) $last_image['image_id'] : 0,
					'album_last_image_time'     => $last_image ? (int) $last_image['image_time'] : 0,
					'album_last_image_name'     => $last_image ? $last_image['image_name'] : '',
					'album_last_username'       => $last_image ? $last_image['image_username'] : '',
					'album_last_user_colour'    => $last_image ? $last_image['image_user_colour'] : '',
					'album_last_user_id'        => $last_image ? (int) $last_image['image_user_id'] : 0,
				];
				$db->sql_query('UPDATE ' . GALLERY_ALBUMS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $album_data) . '
					WHERE album_id = ' . $main_album_id);
			}
		}

		if ($db_tools->sql_column_exists(GALLERY_ALBUMS_TABLE, 'album_auth_access'))
		{
			foreach ($db_tools->sql_column_remove(GALLERY_ALBUMS_TABLE, 'album_auth_access') as $sql)
			{
				$db->sql_query($sql);
			}
		}
	}
	if ($db_tools->sql_table_exists(GALLERY_IMAGES_TABLE))
	{
		foreach (['image_contest', 'image_contest_end', 'image_contest_rank'] as $column)
		{
			if ($db_tools->sql_column_exists(GALLERY_IMAGES_TABLE, $column))
			{
				foreach ($db_tools->sql_column_remove(GALLERY_IMAGES_TABLE, $column) as $sql)
				{
					$db->sql_query($sql);
				}
			}
		}
	}
	foreach ($db_tools->sql_table_drop($table_prefix . 'gallery_contests') as $sql)
	{
		$db->sql_query($sql);
	}
	if ($db_tools->sql_table_exists(GALLERY_ROLES_TABLE) && $db_tools->sql_column_exists(GALLERY_ROLES_TABLE, 'i_watermark'))
	{
		foreach ($db_tools->sql_column_remove(GALLERY_ROLES_TABLE, 'i_watermark') as $sql)
		{
			$db->sql_query($sql);
		}
	}
	if ($db_tools->sql_table_exists(GALLERY_ROLES_TABLE) && $db_tools->sql_column_exists(GALLERY_ROLES_TABLE, 'a_restrict'))
	{
		foreach ($db_tools->sql_column_remove(GALLERY_ROLES_TABLE, 'a_restrict') as $sql)
		{
			$db->sql_query($sql);
		}
	}
	if ($db_tools->sql_table_exists(GALLERY_ROLES_TABLE))
	{
		foreach (['a_count', 'a_unlimited'] as $column)
		{
			if ($db_tools->sql_column_exists(GALLERY_ROLES_TABLE, $column))
			{
				foreach ($db_tools->sql_column_remove(GALLERY_ROLES_TABLE, $column) as $sql)
				{
					$db->sql_query($sql);
				}
			}
		}
	}
	if ($db_tools->sql_table_exists(GALLERY_USERS_TABLE))
	{
		if ($db_tools->sql_column_exists(GALLERY_USERS_TABLE, 'subscribe_pegas'))
		{
			foreach ($db_tools->sql_column_remove(GALLERY_USERS_TABLE, 'subscribe_pegas') as $sql)
			{
				$db->sql_query($sql);
			}
		}
		if ($db_tools->sql_column_exists(GALLERY_USERS_TABLE, 'watch_favo'))
		{
			foreach ($db_tools->sql_column_remove(GALLERY_USERS_TABLE, 'watch_favo') as $sql)
			{
				$db->sql_query($sql);
			}
		}

		$db->sql_query('UPDATE ' . GALLERY_USERS_TABLE . "
			SET user_permissions = '',
				user_permissions_changed = " . time());
	}

	// Migrate settings.

	if ($config['board_hide_emails'] ?? 0)
	{
		$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_allow_viewemail = 0');
	}

	// Remove obsolete config values.

	remove_config_values([
		'allow_avatar_remote',
		'login_via_email_enable',
		'auth_method',
		'ldap_base_dn',
		'ldap_email',
		'ldap_password',
		'ldap_port',
		'ldap_server',
		'ldap_uid',
		'ldap_user',
		'ldap_user_filter',
		'load_jumpbox',
		'load_unreads_search',
		'load_anon_lastread',
		'style_show_social_buttons',
		'check_dnsbl',
		'board_email_form',
		'board_hide_emails',
		'allow_emailreuse',
		'allow_autologin',
		'form_token_sid_guests',
		'form_token_lifetime',
		'load_tplcompile',
		'record_online_date',
		'record_online_users',
		'load_online_guests',
		'load_online_bots',
		'load_db_track',
		'override_user_dateformat',
		'merge_no_forums',
		'merge_no_topics',
	]);

	// New defaults.

	set_config('allow_login_via_email', '1');
	set_config('max_autologin_time', '400');
	set_config('session_length', '14400');
	set_config('referer_validation', '1');
	set_config('cache_mtime_check', '1');
	set_config('max_sig_chars', min((int) $config['max_sig_chars'], 500));
	set_config('attachment_quota', '2147483648');
	set_config('max_filesize', '1048576');
	set_config('max_filesize_pm', '524288');
	set_config('allow_pm_attach', '1');
	set_config('max_attachments', '30');
	set_config('max_attachments_pm', '1');
	set_config('img_create_thumbnail', '1');
	set_config('allow_avatar', '1');
	set_config('allow_avatar_upload', '1');
	set_config('allow_avatar_remote_upload', '0');
	set_config('avatar_filesize', '20480');
	set_config('allow_mass_pm', '0');
	set_config('pm_max_recipients', '5');

	// Remove obsolete modules.

	remove_module('acp', 'board', 'auth');
	remove_module('acp', 'update', 'version_check');
	remove_module('acp', 'send_statistics', 'send_statistics');
	remove_module('acp', 'board', 'cookie');
	remove_module('acp', 'quick_reply', 'quick_reply');
	remove_module('ucp', 'pm', 'popup');
	remove_module('acp', 'database', 'backup');
	remove_module('acp', 'database', 'restore');

	// Remove obsolete permissions.

	remove_permissions([
		'a_backup',
		'u_sendemail',
		'u_pm_download',
		'u_sendim',
	]);

	// Add the PM recipient-limit bypass permission without granting it to any role.
	require_once(PHPBB_ROOT_PATH . 'includes/acp/auth.php');
	$auth_admin = new auth_admin();
	if (empty($auth_admin->acl_options['id']['u_masspm_nomax']))
	{
		$auth_admin->acl_add_option(['global' => ['u_masspm_nomax']]);
	}

	// Update cached module rights.

	$db->sql_query('UPDATE ' . MODULES_TABLE . "
		SET module_auth = 'cfg_allow_avatar && (cfg_allow_avatar_local || cfg_allow_avatar_upload || cfg_allow_avatar_remote_upload)'
		WHERE module_class = 'ucp' AND module_basename = 'profile' AND module_mode = 'avatar'");

	// Update schema.

	$db->sql_return_on_error(true);

	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP INDEX user_email_hash');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_email_hash');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_last_confirm_key');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_topic_sortby_type');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_topic_sortby_dir');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_post_sortby_type');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_post_sortby_dir');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_dateformat');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_topics_per_page');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_posts_per_page');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_emailtime');
	$db->sql_query('ALTER TABLE ' . USERS_TABLE . ' DROP COLUMN user_lastpage');
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_sig = LEFT(user_sig, 500) WHERE CHAR_LENGTH(user_sig) > 500");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " MODIFY user_sig varchar(500) DEFAULT '' NOT NULL");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_interests = LEFT(user_interests, 1000) WHERE CHAR_LENGTH(user_interests) > 1000");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " CHANGE user_interests user_about varchar(1000) DEFAULT '' NOT NULL");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_about = LEFT(CONCAT(user_occ, IF(user_about = '', '', '\n'), user_about), 1000), user_occ = '' WHERE CHAR_LENGTH(user_occ) > 50");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " CHANGE user_occ user_occupation varchar(50) DEFAULT '' NOT NULL");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_about = LEFT(CONCAT(user_from, IF(user_about = '', '', '\n'), user_about), 1000), user_from = '' WHERE CHAR_LENGTH(user_from) > 50");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " MODIFY user_from varchar(50) DEFAULT '' NOT NULL");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_jabber = LEFT(user_jabber, 100) WHERE CHAR_LENGTH(user_jabber) > 100");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_skype = LEFT(user_skype, 32) WHERE CHAR_LENGTH(user_skype) > 32");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_telegram = LEFT(user_telegram, 32) WHERE CHAR_LENGTH(user_telegram) > 32");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_website = LEFT(user_website, 100) WHERE CHAR_LENGTH(user_website) > 100");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " MODIFY user_jabber varchar(100) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " MODIFY user_skype varchar(32) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " MODIFY user_telegram varchar(32) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " MODIFY user_website varchar(100) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " MODIFY user_allow_viewemail tinyint(1) UNSIGNED DEFAULT '0' NOT NULL");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " ADD INDEX user_email(user_email)");
	$db->sql_query("ALTER TABLE " . POSTS_TABLE . " ADD INDEX poster_topic(poster_id, topic_id)"); // For checking if a user posted in listed topics.
	$db->sql_query("DROP TABLE {$table_prefix}topics_posted");
	$db->sql_query('ALTER TABLE ' . SESSIONS_TABLE . ' DROP COLUMN session_page');
	$db->sql_query('ALTER TABLE ' . FORUMS_TABLE . ' DROP COLUMN forum_password');
	$db->sql_query('ALTER TABLE ' . FORUMS_TABLE . ' DROP COLUMN forum_topic_show_days');
	$db->sql_query('ALTER TABLE ' . FORUMS_TABLE . ' DROP COLUMN forum_topics_per_page');
	$db->sql_query('ALTER TABLE ' . GROUPS_TABLE . ' DROP COLUMN group_message_limit');
	$db->sql_query('ALTER TABLE ' . GROUPS_TABLE . ' DROP COLUMN group_max_recipients');
	$db->sql_query("DROP TABLE {$table_prefix}forums_access");
	$db->sql_query("ALTER TABLE " . CONFIRM_TABLE . " MODIFY code varchar(32) DEFAULT '' NOT NULL");

	// Use lang_code as a universal language id instead of the old lang_id, lang_iso, and lang_dir.

	$db->sql_query("UPDATE " . LANG_TABLE . " SET lang_dir = LEFT(lang_dir, 5) WHERE CHAR_LENGTH(lang_dir) > 5");
	$db->sql_query("ALTER TABLE " . LANG_TABLE . " CHANGE lang_dir lang_code varchar(5) CHARACTER SET ascii COLLATE ascii_bin DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . PROFILE_LANG_TABLE . " ADD COLUMN lang_code varchar(5) CHARACTER SET ascii COLLATE ascii_bin DEFAULT '' NOT NULL AFTER field_id");
	$db->sql_query("UPDATE " . PROFILE_LANG_TABLE . " pl, " . LANG_TABLE . " l SET pl.lang_code = l.lang_code WHERE pl.lang_id = l.lang_id");
	$db->sql_query("ALTER TABLE " . PROFILE_LANG_TABLE . " DROP PRIMARY KEY");
	$db->sql_query("ALTER TABLE " . PROFILE_LANG_TABLE . " DROP COLUMN lang_id");
	$db->sql_query("ALTER TABLE " . PROFILE_LANG_TABLE . " ADD PRIMARY KEY (field_id, lang_code)");
	$db->sql_query("ALTER TABLE " . PROFILE_FIELDS_LANG_TABLE . " ADD COLUMN lang_code varchar(5) CHARACTER SET ascii COLLATE ascii_bin DEFAULT '' NOT NULL AFTER field_id");
	$db->sql_query("UPDATE " . PROFILE_FIELDS_LANG_TABLE . " pfl, " . LANG_TABLE . " l SET pfl.lang_code = l.lang_code WHERE pfl.lang_id = l.lang_id");
	$db->sql_query("ALTER TABLE " . PROFILE_FIELDS_LANG_TABLE . " DROP PRIMARY KEY");
	$db->sql_query("ALTER TABLE " . PROFILE_FIELDS_LANG_TABLE . " DROP COLUMN lang_id");
	$db->sql_query("ALTER TABLE " . PROFILE_FIELDS_LANG_TABLE . " ADD PRIMARY KEY (field_id, lang_code, option_id)");
	$db->sql_query("ALTER TABLE " . LANG_TABLE . " MODIFY lang_id tinyint(4) NOT NULL");
	$db->sql_query("ALTER TABLE " . LANG_TABLE . " DROP PRIMARY KEY");
	$db->sql_query("ALTER TABLE " . LANG_TABLE . " DROP INDEX lang_iso");
	$db->sql_query("ALTER TABLE " . LANG_TABLE . " DROP COLUMN lang_id");
	$db->sql_query("ALTER TABLE " . LANG_TABLE . " DROP COLUMN lang_iso");
	$db->sql_query("ALTER TABLE " . LANG_TABLE . " ADD PRIMARY KEY (lang_code)");
	$db->sql_query("ALTER TABLE " . LANG_TABLE . " DROP COLUMN lang_author");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_lang = LEFT(user_lang, 5) WHERE CHAR_LENGTH(user_lang) > 5");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " CHANGE user_lang user_lang_code varchar(5) CHARACTER SET ascii COLLATE ascii_bin DEFAULT '' NOT NULL");
	$db->sql_query("UPDATE " . CONFIG_TABLE . " SET config_name = 'default_lang_code' WHERE config_name = 'default_lang'");

	// Adjust QA CAPTCHA tables.

	if ($db_tools->sql_table_exists("{$table_prefix}captcha_questions"))
	{
		$db->sql_query("UPDATE {$table_prefix}captcha_questions SET lang_iso = LEFT(lang_iso, 5) WHERE CHAR_LENGTH(lang_iso) > 5");
		$db->sql_query("ALTER TABLE {$table_prefix}captcha_questions CHANGE lang_iso lang_code varchar(5) CHARACTER SET ascii COLLATE ascii_bin DEFAULT '' NOT NULL");
		$db->sql_query("ALTER TABLE {$table_prefix}captcha_questions DROP COLUMN lang_id");
	}
	if ($db_tools->sql_table_exists("{$table_prefix}qa_confirm"))
	{
		$db->sql_query("ALTER TABLE {$table_prefix}qa_confirm DROP INDEX lookup");
		$db->sql_query("ALTER TABLE {$table_prefix}qa_confirm DROP COLUMN lang_iso");
		$db->sql_query("ALTER TABLE {$table_prefix}qa_confirm ADD INDEX lookup(session_id, confirm_type)");
	}

	// Migrate phpbb_user_browser_ids to the new phpbb_browser_tracking.

	$db->sql_query("ALTER TABLE {$table_prefix}user_browser_ids RENAME TO " . BROWSER_TRACKING_TABLE);
	$db->sql_query("ALTER TABLE " . BROWSER_TRACKING_TABLE . " CHANGE created tracking_first_time int(11) UNSIGNED DEFAULT '0' NOT NULL");
	$db->sql_query("ALTER TABLE " . BROWSER_TRACKING_TABLE . " CHANGE last_visit tracking_last_time int(11) UNSIGNED DEFAULT '0' NOT NULL");
	$db->sql_query("ALTER TABLE " . BROWSER_TRACKING_TABLE . " CHANGE visits tracking_hits int(11) UNSIGNED DEFAULT '0' NOT NULL");
	$db->sql_query("ALTER TABLE " . BROWSER_TRACKING_TABLE . " CHANGE agent browser_ua varchar(250) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . BROWSER_TRACKING_TABLE . " ADD COLUMN tracking_first_ip varchar(40) DEFAULT '' NOT NULL AFTER browser_ua");
	$db->sql_query("ALTER TABLE " . BROWSER_TRACKING_TABLE . " CHANGE last_ip tracking_last_ip varchar(40) DEFAULT '' NOT NULL");
	$db->sql_query("UPDATE " . BROWSER_TRACKING_TABLE . " SET tracking_first_ip = tracking_last_ip WHERE tracking_first_ip = ''");

	// Rename legacy UA column names to make it clear that they store user-agent strings.

	$db->sql_query("ALTER TABLE " . LOGIN_ATTEMPT_TABLE . " CHANGE attempt_browser attempt_browser_ua varchar(250) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . SESSIONS_TABLE . " CHANGE session_browser session_browser_ua varchar(250) DEFAULT '' NOT NULL");
	$db->sql_query("ALTER TABLE " . USERS_TABLE . " CHANGE user_browser user_browser_ua varchar(250) DEFAULT '' NOT NULL");

	// Update supported file extensions.

	// Reduce list of supported file extensions.
	$db->sql_query("DELETE FROM " . EXTENSIONS_TABLE . " WHERE extension IN ('docm', 'xlsm', 'xlsb', 'pptm', 'avi', 'wma', 'wmv', 'mpeg', 'mpg', 'mov', 'swf', 'xml', 'diff', 'sql', 'odg')");
	// Cleanup extensions forgotten since v1.9.4 update.
	$db->sql_query("DELETE FROM " . EXTENSIONS_TABLE . " WHERE extension IN ('3g2', '3gp', 'ace', 'ai', 'c', 'cpp', 'diz', 'dot', 'dotm', 'dotx', 'gtar', 'h', 'hpp', 'ini', 'js', 'oga', 'ogv', 'ps', 'qt', 'ram', 'rm', 'tar', 'tga', 'tif', 'tiff')");
	// Remove invalid extensions that are not supported by the new schema.
	$db->sql_query("DELETE FROM " . EXTENSIONS_TABLE . " WHERE BINARY extension NOT REGEXP '^[a-z0-9_-]{1,10}$'");
	// Remove duplicate extensions before using the extension as a primary key.
	$db->sql_query("DELETE e1 FROM " . EXTENSIONS_TABLE . " e1, " . EXTENSIONS_TABLE . " e2 WHERE e1.extension = e2.extension AND e1.extension_id > e2.extension_id");
	// Upgrade schema.
	$db->sql_query("ALTER TABLE " . EXTENSIONS_TABLE . " MODIFY extension_id mediumint(8) UNSIGNED NOT NULL");
	$db->sql_query("ALTER TABLE " . EXTENSIONS_TABLE . " DROP PRIMARY KEY");
	$db->sql_query("ALTER TABLE " . EXTENSIONS_TABLE . " DROP INDEX extension");
	$db->sql_query("ALTER TABLE " . EXTENSIONS_TABLE . " CHANGE extension extension varchar(10) CHARACTER SET ascii COLLATE ascii_bin DEFAULT '' NOT NULL FIRST");
	$db->sql_query("ALTER TABLE " . EXTENSIONS_TABLE . " ADD PRIMARY KEY (extension)");
	$db->sql_query("ALTER TABLE " . EXTENSIONS_TABLE . " DROP COLUMN extension_id");
	// Reinsert webp into the correct group if needed.
	$db->sql_query("INSERT INTO " . EXTENSIONS_TABLE . " (extension, group_id) VALUES ('webp', 1) ON DUPLICATE KEY UPDATE group_id = 1");

	// Update anonymous user.

	$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_browser_ua = '', user_ip = '' WHERE user_id = " . ANONYMOUS);

	// Demote bots from users to guests.

	$db->sql_query('ALTER TABLE ' . BOTS_TABLE . ' ADD COLUMN bot_lastvisit int(11) UNSIGNED DEFAULT 0 NOT NULL AFTER bot_name');
	$db->sql_query('ALTER TABLE ' . SESSIONS_TABLE . ' ADD COLUMN session_bot_id mediumint(8) UNSIGNED DEFAULT 0 NOT NULL AFTER session_user_id');
	$db->sql_query('ALTER TABLE ' . SESSIONS_TABLE . ' ADD INDEX session_bot_id(session_bot_id)');

	$db->sql_return_on_error(false);

	if ($db_tools->sql_column_exists(BOTS_TABLE, 'user_id'))
	{
		$sql = 'SELECT b.bot_id, b.user_id, u.user_lastvisit
			FROM ' . BOTS_TABLE . ' b
			LEFT JOIN ' . USERS_TABLE . ' u ON b.user_id = u.user_id
			WHERE b.user_id <> 0';
		$result = $db->sql_query($sql);

		$bot_user_ids = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$bot_id = (int) $row['bot_id'];
			$bot_user_id = (int) $row['user_id'];
			$bot_user_ids[] = $bot_user_id;

			$db->sql_query('UPDATE ' . BOTS_TABLE . '
				SET bot_lastvisit = ' . (int) $row['user_lastvisit'] . "
				WHERE bot_id = {$bot_id}");
		}
		$db->sql_freeresult($result);

		foreach ($bot_user_ids as $bot_user_id)
		{
			user_delete('remove', $bot_user_id);
		}

		$db->sql_query('ALTER TABLE ' . BOTS_TABLE . ' DROP COLUMN user_id');
	}

	// No more BOTS group.

	$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " WHERE group_name = 'BOTS' AND group_type = " . GROUP_SPECIAL;
	$result = $db->sql_query($sql);
	$bot_group_id = (int) $db->sql_fetchfield('group_id');
	$db->sql_freeresult($result);

	if ($bot_group_id)
	{
		$sql = 'SELECT user_id
			FROM ' . USER_GROUP_TABLE . "
			WHERE group_id = {$bot_group_id}";
		$result = $db->sql_query($sql);
		$bot_group_has_users = (bool) $db->sql_fetchfield('user_id');
		$db->sql_freeresult($result);

		if (!$bot_group_has_users)
		{
			$db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . " WHERE group_id = {$bot_group_id}");
			$db->sql_query('DELETE FROM ' . GROUPS_TABLE . " WHERE group_id = {$bot_group_id}");
		}
		else
		{
			// If there are some users in the group, make it not special at least.
			$db->sql_query("UPDATE " . GROUPS_TABLE . " SET group_type = " . GROUP_HIDDEN . " WHERE group_id = {$bot_group_id}");
		}
	}

	// Style components are merged into a single style table.
	// Currently, prosilver is the only v1.10 compatible theme in the world, so we can remove the rest from DB.

	$db->sql_query("DROP TABLE IF EXISTS {$table_prefix}styles_template");
	$db->sql_query("DROP TABLE IF EXISTS {$table_prefix}styles_template_data");
	$db->sql_query("DROP TABLE IF EXISTS {$table_prefix}styles_theme");
	$db->sql_query("DROP TABLE IF EXISTS {$table_prefix}styles_imageset");
	$db->sql_query("DROP TABLE IF EXISTS {$table_prefix}styles_imageset_data");
	$db->sql_query("DROP TABLE IF EXISTS " . STYLES_TABLE);
	$db->sql_query("CREATE TABLE " . STYLES_TABLE . " (
		style_id mediumint(8) UNSIGNED NOT NULL auto_increment,
		style_name varchar(30) DEFAULT '' NOT NULL,
		style_active tinyint(1) UNSIGNED DEFAULT '1' NOT NULL,
		template_dir varchar(50) CHARACTER SET ascii COLLATE ascii_bin DEFAULT '' NOT NULL,
		theme_dir varchar(50) CHARACTER SET ascii COLLATE ascii_bin DEFAULT '' NOT NULL,
		imageset_dir varchar(50) CHARACTER SET ascii COLLATE ascii_bin DEFAULT '' NOT NULL,
		PRIMARY KEY (style_id),
		UNIQUE style_name (style_name)
	) CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`");
	$db->sql_query("INSERT INTO " . STYLES_TABLE . " (style_name, style_active, template_dir, theme_dir, imageset_dir) VALUES ('prosilver', 1, 'prosilver', 'prosilver', 'prosilver')");
	$db->sql_query("UPDATE " . USERS_TABLE . " SET user_style = 1");
	set_config('default_style', '1');

	remove_module('acp', 'styles', 'template');
	remove_module('acp', 'styles', 'theme');
	remove_module('acp', 'styles', 'imageset');
	remove_module('acp', 'styles', 'style');
	_add_modules([
		'style' => [
			'class' => 'acp',
			'cat'   => 'ACP_GENERAL_TASKS',
			'base'  => 'styles',
			'title' => 'ACP_STYLES',
			'auth'  => 'acl_a_styles',
		],
	]);
	remove_module_category('acp', 'ACP_STYLE_COMPONENTS');
	remove_module_category('acp', 'ACP_STYLE_MANAGEMENT');
	remove_module_category('acp', 'ACP_CAT_STYLES');

	// Clear cache and reset bots.

	$bots_default = true;
	$purge_default = 'all';

	set_config('phpbbex_version', '1.10.0');
}

// Update bots if bots=1 is passed.
if (request_var('bots', $bots_default))
{
	$bots_updates = [
		// Bot deletions.
		'Aport [Bot]'               => false,
		'Alta Vista [Bot]'          => false,
		'FAST Enterprise [Crawler]' => false,
		'Francis [Bot]'             => false,
		'Google Desktop'            => false,
		'Heise IT-Markt [Crawler]'  => false,
		'Heritrix [Crawler]'        => false,
		'IBM Research [Bot]'        => false,
		'ICCrawler - ICjobs'        => false,
		'Metager [Bot]'             => false,
		'MSN NewsBlogs'             => false,
		'NG-Search [Bot]'           => false,
		'Nutch [Bot]'               => false,
		'Nutch/CVS [Bot]'           => false,
		'OmniExplorer [Bot]'        => false,
		'Online link [Validator]'   => false,
		'Seekport [Bot]'            => false,
		'Sensis [Crawler]'          => false,
		'SEO Crawler'               => false,
		'Seoma [Crawler]'           => false,
		'SEOSearch [Crawler]'       => false,
		'Snappy [Bot]'              => false,
		'Synoo [Bot]'               => false,
		'Telekom [Bot]'             => false,
		'W3 [Sitesearch]'           => false,
		'WiseNut [Bot]'             => false,
		'Yahoo MMCrawler [Bot]'     => false,
		'Yahoo Slurp [Bot]'         => false,
		'YahooSeeker [Bot]'         => false,
		'Yandex [Addurl]'           => false,
		'Yandex [Catalog]'          => false,
		'Rambler [Bot]'             => false,
		'WebAlta [Bot]'             => false,
		'Google Feedfetcher'        => false,
		'Yandex [Images]'           => false,
		'Yandex [Video]'            => false,
		'Yandex [Media]'            => false,
		'Yandex [Blogs]'            => false,
		'Yandex [Direct]'           => false,
		'Yandex [Metrika]'          => false,
		'Yandex [News]'             => false,
		'AdsBot [Google]'           => false,
		'MSN [Bot]'                 => false,
		'MSNbot Media'              => false,
		'psbot [Picsearch]'         => false,
		'ichiro [Crawler]'          => false,
		'Alexa [Bot]'               => false,
		'Ask Jeeves [Bot]'          => false,
		'Exabot [Bot]'              => false,
		'FAST WebCrawler [Crawler]' => false,
		'Gigabot [Bot]'             => false,
		'Majestic-12 [Bot]'         => false,
		'Steeler [Crawler]'         => false,
		'TurnitinBot [Bot]'         => false,
		'Voyager [Bot]'             => false,
		'W3C [Linkcheck]'           => false,
		'W3C [Validator]'           => false,
		'Seostar [Bot]'             => false,
		'BLEX [Bot]'                => false,
		'MailRu [Bot]'              => false,
		'Ubermetrics [Bot]'         => false,
		// Bot updates and additions.
		'Baidu [Spider]'            => 'Baiduspider',
		'Bing [Bot]'                => 'bingbot/',
		'Google [Bot]'              => 'Googlebot',
		'Google Ads [Bot]'          => 'AdsBot-Google',
		'Google Adsense [Bot]'      => 'Mediapartners-Google',
		'YaCy [Bot]'                => 'yacybot',
		'Yahoo [Bot]'               => 'Yahoo! Slurp',
		'Ahrefs [Bot]'              => 'AhrefsBot/',
		'Senti [Bot]'               => 'SentiBot/',
		'Petal [Bot]'               => 'PetalBot',
		'Barkrowler [Bot]'          => 'Barkrowler/',
		'Trendiction [Bot]'         => 'trendiction.de/bot',
		'DuckDuck [Bot]'            => 'duckduckgo.com',
		'Yandex [Bot]'              => 'YandexBot/',
		'Yandex Images [Bot]'       => 'YandexImages/',
		'Yandex Metrika [Bot]'      => 'YandexMetrika/',
		'Feedly [Bot]'              => 'Feedly/',
		'Feedspot [Bot]'            => 'Feedspot/',
	];

	// Update loop.
	foreach ($bots_updates as $bot_name => $bot_agent)
	{
		$sql = 'SELECT bot_id
			FROM ' . BOTS_TABLE . "
			WHERE bot_name = '" . $db->sql_escape($bot_name) . "'";
		$result = $db->sql_query($sql);
		$bot_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$bot_row)
		{
			if ($bot_agent === false) { continue; }

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', [
				'bot_active'    => 1,
				'bot_name'      => (string) $bot_name,
				'bot_agent'     => (string) $bot_agent,
				'bot_ip'        => '',
			]);
			$db->sql_query($sql);
		}
		else
		{
			if ($bot_agent === false)
			{
				$sql = 'DELETE FROM ' . BOTS_TABLE . "
					WHERE bot_id = " . (int) $bot_row['bot_id'];
				$db->sql_query($sql);
			}
			else
			{
				$sql = 'UPDATE ' . BOTS_TABLE . "
					SET bot_agent = '" .  $db->sql_escape($bot_agent) . "'
					WHERE bot_id = " . (int) $bot_row['bot_id'];
				$db->sql_query($sql);
			}
		}
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
			case TOPICS_TRACK_TABLE:
			case TOPICS_WATCH_TABLE:
			case USER_GROUP_TABLE:
			case WARNINGS_TABLE:
			case WORDS_TABLE:
			case ZEBRA_TABLE:
			case USER_CONFIRM_KEYS_TABLE:
			case BROWSER_TRACKING_TABLE:
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
		require_once(PHPBB_ROOT_PATH . 'includes/umil.php');
		$umil = new phpbb_umil();
		$umil->cache_purge(['auth', 'data']);
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

$database_update_info = database_update_info();

$error_ary = [];
$errored = false;

// Load language files.
if (!isset($config['default_lang']) || !file_exists(PHPBB_ROOT_PATH . 'language/' . $config['default_lang']))
{
	die('Error! Default language is not found!');
}
require(PHPBB_ROOT_PATH . 'language/' . $config['default_lang'] . '/common.php');
require(PHPBB_ROOT_PATH . 'language/' . $config['default_lang'] . '/acp/common.php');
require(PHPBB_ROOT_PATH . 'language/' . $config['default_lang'] . '/install.php');

?>
<!DOCTYPE html>
<html lang="<?php echo $lang['HTML_LANG_CODE']; ?>">
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

	$next_version = $versions[$i + 1] ?? $updates_to_version;

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
		$statements = $db_tools->perform_schema_changes([$key => $changes]);

		foreach ($statements as $sql)
		{
			_sql($sql, $errored, $error_ary);
		}
	}
}

_write_result($no_updates, $errored, $error_ary);

// Data updates
$error_ary = [];
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
	$next_version = $versions[$i + 1] ?? $updates_to_version;

	// If the installed version to be updated to is < than the current version, and if the current version is >= as the version to be updated to next, we will skip the process
	if (version_compare($version, $current_version, '<') && version_compare($current_version, $next_version, '>='))
	{
		continue;
	}

	change_database_data($no_updates, $version);
}

_write_result($no_updates, $errored, $error_ary);

$error_ary = [];
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
		SET config_value = '{$updates_to_version}'
		WHERE config_name = 'version'";
	_sql($sql, $errored, $error_ary);
}

// Reset permissions
$sql = 'UPDATE ' . USERS_TABLE . "
	SET user_permissions = '',
		user_perm_from = 0";
_sql($sql, $errored, $error_ary);

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

	<div id="page-footer">Powered by <a href="//phpbbex.com/">phpBBex</a></div>
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
	global $db;

	require_once(PHPBB_ROOT_PATH . 'includes/acp/acp_modules.php');

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
		$categories = [];
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
			$module_row = [
				'module_basename'   => $module_data['base'],
				'module_enabled'    => (isset($module_data['enabled'])) ? (int) $module_data['enabled'] : 1,
				'module_display'    => (isset($module_data['display'])) ? (int) $module_data['display'] : 1,
				'parent_id'         => $parent_id,
				'module_class'      => $module_data['class'],
				'module_langname'   => $module_data['title'],
				'module_mode'       => $module_mode,
				'module_auth'       => $module_data['auth'],
			];

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

function database_update_info()
{
	return [
		// Changes from 3.0.0 to the next version
		'3.0.0'         => [
			'add_columns'       => [
				FORUMS_TABLE            => [
					'display_subforum_list'     => ['BOOL', 1],
				],
				SESSIONS_TABLE          => [
					'session_forum_id'      => ['UINT', 0],
				],
			],
			'drop_keys'     => [
				GROUPS_TABLE            => ['group_legend'],
			],
			'add_index'     => [
				SESSIONS_TABLE          => [
					'session_forum_id'      => ['session_forum_id'],
				],
				GROUPS_TABLE            => [
					'group_legend_name'     => ['group_legend', 'group_name'],
				],
			],
		],
		'3.0.1-RC1'     => [],
		'3.0.1'         => [],
		'3.0.2-RC1'     => [
			'change_columns'    => [
				DRAFTS_TABLE            => [
					'draft_subject'     => ['STEXT_UNI', ''],
				],
				FORUMS_TABLE    => [
					'forum_last_post_subject' => ['STEXT_UNI', ''],
				],
				POSTS_TABLE     => [
					'post_subject'          => ['STEXT_UNI', '', 'true_sort'],
				],
				PRIVMSGS_TABLE  => [
					'message_subject'       => ['STEXT_UNI', ''],
				],
				TOPICS_TABLE    => [
					'topic_title'               => ['STEXT_UNI', '', 'true_sort'],
					'topic_last_post_subject'   => ['STEXT_UNI', ''],
				],
			],
			'drop_keys'     => [
				SESSIONS_TABLE          => ['session_forum_id'],
			],
			'add_index'     => [
				SESSIONS_TABLE          => [
					'session_fid'       => ['session_forum_id'],
				],
			],
		],
		'3.0.2-RC2'     => [],
		'3.0.2'         => [],
		'3.0.3-RC1'     => [],
		'3.0.3'         => [
			'add_columns'       => [
				PROFILE_FIELDS_TABLE            => [
					'field_show_profile'        => ['BOOL', 0],
				],
			],
			'change_columns'    => [
				USERS_TABLE                 => [
					'user_style'            => ['UINT', 0],
				],
			],
		],
		'3.0.4-RC1'     => [],
		'3.0.4'         => [],
		'3.0.5-RC1'     => [],
		'3.0.5'     => [
			'add_columns'       => [
				CONFIRM_TABLE           => [
					'attempts'      => ['UINT', 0],
				],
				USERS_TABLE         => [
					'user_new'          => ['BOOL', 1],
					'user_reminded'     => ['TINT:4', 0],
					'user_reminded_time'=> ['TIMESTAMP', 0],
				],
				GROUPS_TABLE            => [
					'group_skip_auth'       => ['BOOL', 0, 'after' => 'group_founder_manage'],
				],
				PRIVMSGS_TABLE      => [
					'message_reported'  => ['BOOL', 0],
				],
				REPORTS_TABLE       => [
					'pm_id'             => ['UINT', 0],
				],
				PROFILE_FIELDS_TABLE            => [
					'field_show_on_vt'      => ['BOOL', 0],
				],
				FORUMS_TABLE        => [
					'forum_options'         => ['UINT:20', 0],
				],
			],
			'add_index'     => [
				REPORTS_TABLE       => [
					'post_id'       => ['post_id'],
					'pm_id'         => ['pm_id'],
				],
				POSTS_TABLE         => [
					'post_username'     => ['post_username:255'],
				],
			],
		],
		'3.0.6-RC1'     => [],
		'3.0.6-RC2'     => [],
		'3.0.6-RC3'     => [],
		'3.0.6-RC4'     => [],
		'3.0.6'     => [
			'drop_keys'     => [
				LOG_TABLE           => ['log_time'],
			],
			'add_index'     => [
				TOPICS_TRACK_TABLE  => [
					'topic_id'      => ['topic_id'],
				],
			],
		],
		'3.0.7-RC1'     => [],
		'3.0.7-RC2'     => [],
		'3.0.7'     => [],
		'3.0.7-PL1'     => [],
		'3.0.8-RC1'     => [],
		'3.0.8'         => [
			'add_tables'        => [
				LOGIN_ATTEMPT_TABLE => [
					'COLUMNS'           => [
						// this column was removed from the database updater
						// after 3.0.9-RC3 was released. It might still exist
						// in 3.0.9-RCX installations and has to be dropped in
						// 3.0.15 after the db_tools class is capable of properly
						// removing a primary key.
						// 'attempt_id'         => array('UINT', NULL, 'auto_increment'),
						'attempt_ip'            => ['VCHAR:40', ''],
						'attempt_browser_ua'    => ['VCHAR:250', ''],
						'attempt_forwarded_for' => ['VCHAR:255', ''],
						'attempt_time'          => ['TIMESTAMP', 0],
						'user_id'               => ['UINT', 0],
						'username'              => ['VCHAR_UNI:255', 0],
						'username_clean'        => ['VCHAR_CI', 0],
					],
					//'PRIMARY_KEY'     => 'attempt_id',
					'KEYS'              => [
						'att_ip'            => ['INDEX', ['attempt_ip', 'attempt_time']],
						'att_for'   => ['INDEX', ['attempt_forwarded_for', 'attempt_time']],
						'att_time'          => ['INDEX', ['attempt_time']],
						'user_id'               => ['INDEX', 'user_id'],
					],
				],
			],
			'change_columns'    => [
				BBCODES_TABLE   => [
					'bbcode_id' => ['USINT', 0],
				],
			],
		],
		'3.0.9-RC1'     => [],
		'3.0.9-RC2'     => [],
		'3.0.9-RC3'     => [],
		'3.0.9-RC4'     => [],
		'3.0.9'         => [],
		'3.0.10-RC1'    => [],
		'3.0.10-RC2'    => [],
		'3.0.10-RC3'    => [],
		'3.0.10'        => [],
		'3.0.11-RC1'    => [
			'add_columns'       => [
				PROFILE_FIELDS_TABLE            => [
					'field_show_novalue'        => ['BOOL', 0],
				],
			],
		],
		'3.0.11-RC2'    => [],
		'3.0.11'        => [],

		/** @todo DROP LOGIN_ATTEMPT_TABLE.attempt_id in 3.0.15-RC1 */
	];
}

function change_database_data(&$no_updates, $version)
{
	global $db, $db_tools, $errored, $error_ary, $config, $table_prefix;

	switch ($version)
	{
		case '3.0.0':

			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_last_view_time = topic_last_post_time
				WHERE topic_last_view_time = 0";
			_sql($sql, $errored, $error_ary);

			// Update smiley sizes
			$smileys = ['icon_e_surprised.gif', 'icon_eek.gif', 'icon_cool.gif', 'icon_lol.gif', 'icon_mad.gif', 'icon_razz.gif', 'icon_redface.gif', 'icon_cry.gif', 'icon_evil.gif', 'icon_twisted.gif', 'icon_rolleyes.gif', 'icon_exclaim.gif', 'icon_question.gif', 'icon_idea.gif', 'icon_arrow.gif', 'icon_neutral.gif', 'icon_mrgreen.gif', 'icon_e_ugeek.gif'];

			foreach ($smileys as $smiley)
			{
				if (file_exists(PHPBB_ROOT_PATH . 'images/smilies/' . $smiley))
				{
					[$width, $height] = getimagesize(PHPBB_ROOT_PATH . 'images/smilies/' . $smiley);

					$sql = 'UPDATE ' . SMILIES_TABLE . '
						SET smiley_width = ' . $width . ', smiley_height = ' . $height . "
						WHERE smiley_url = '" . $db->sql_escape($smiley) . "'";

					_sql($sql, $errored, $error_ary);
				}
			}

			$no_updates = false;
		break;

		case '3.0.1':

			set_config('referer_validation', '1');
			set_config('check_attachment_content', '1');
			set_config('mime_triggers', 'body|head|html|img|plaintext|a href|pre|script|table|title');

			$no_updates = false;
		break;

		case '3.0.2':
			set_config('enable_queue_trigger', '0');
			set_config('queue_trigger_posts', '3');

			// Add new permission u_masspm_group and duplicate settings from u_masspm
			require_once(PHPBB_ROOT_PATH . 'includes/acp/auth.php');
			$auth_admin = new auth_admin();

			// Only add the new permission if it does not already exist
			if (empty($auth_admin->acl_options['id']['u_masspm_group']))
			{
				$auth_admin->acl_add_option(['global' => ['u_masspm_group']]);

				// Now the tricky part, filling the permission
				$old_id = $auth_admin->acl_options['id']['u_masspm'];
				$new_id = $auth_admin->acl_options['id']['u_masspm_group'];

				$tables = [ACL_GROUPS_TABLE, ACL_ROLES_DATA_TABLE, ACL_USERS_TABLE];

				foreach ($tables as $table)
				{
					$sql = 'SELECT *
						FROM ' . $table . '
						WHERE auth_option_id = ' . $old_id;
					$result = _sql($sql, $errored, $error_ary);

					$sql_ary = [];
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

			$sql = 'UPDATE ' . MODULES_TABLE . '
				SET module_auth = \'acl_a_email && cfg_email_enable\'
				WHERE module_class = \'acp\'
					AND module_basename = \'email\'';
			_sql($sql, $errored, $error_ary);

			$no_updates = false;
		break;

		case '3.0.3-RC1':
			$sql = 'UPDATE ' . LOG_TABLE . "
				SET log_operation = 'LOG_DELETE_TOPIC'
				WHERE log_operation = 'LOG_TOPIC_DELETED'";
			_sql($sql, $errored, $error_ary);

			$no_updates = false;
		break;

		case '3.0.3':
			// Update the Custom Profile Fields based on previous settings to the new format
			$sql = 'SELECT field_id, field_required, field_show_on_reg, field_hide
					FROM ' . PROFILE_FIELDS_TABLE;
			$result = _sql($sql, $errored, $error_ary);

			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary = [
					'field_required'    => 0,
					'field_show_on_reg' => 0,
					'field_hide'        => 0,
					'field_show_profile'=> 0,
				];

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

		case '3.0.4':

			// Maximum number of keywords
			set_config('max_num_search_keywords', 10);

			// Remove static config var and put it back as dynamic variable
			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET is_dynamic = 1
				WHERE config_name = 'search_indexing_state'";
			_sql($sql, $errored, $error_ary);

			// Before we are able to add a unique key to auth_option, we need to remove duplicate entries

			// We get duplicate entries first
			$sql = 'SELECT auth_option
				FROM ' . ACL_OPTIONS_TABLE . '
				GROUP BY auth_option
				HAVING COUNT(*) >= 2';
			$result = $db->sql_query($sql);

			$auth_options = [];
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
			$changes = [
				'drop_keys'         => [
					ACL_OPTIONS_TABLE       => ['auth_option'],
				],
			];

			$statements = $db_tools->perform_schema_changes($changes);

			foreach ($statements as $sql)
			{
				_sql($sql, $errored, $error_ary);
			}

			$changes = [
				'add_unique_index'  => [
					ACL_OPTIONS_TABLE       => [
						'auth_option'       => ['auth_option'],
					],
				],
			];

			$statements = $db_tools->perform_schema_changes($changes);

			foreach ($statements as $sql)
			{
				_sql($sql, $errored, $error_ary);
			}

			$no_updates = false;

		break;

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
			$modules_to_install = [
				'feed'                  => [
					'base'      => 'board',
					'class'     => 'acp',
					'title'     => 'ACP_FEED_SETTINGS',
					'auth'      => 'acl_a_board',
					'cat'       => 'ACP_BOARD_CONFIGURATION',
					'after'     => ['signature', 'ACP_SIGNATURE_SETTINGS']
				],
				'warnings'              => [
					'base'      => 'users',
					'class'     => 'acp',
					'title'     => 'ACP_USER_WARNINGS',
					'auth'      => 'acl_a_user',
					'display'   => 0,
					'cat'       => 'ACP_CAT_USERS',
					'after'     => ['feedback', 'ACP_USER_FEEDBACK']
				],
				'setting_forum_copy'    => [
					'base'      => 'permissions',
					'class'     => 'acp',
					'title'     => 'ACP_FORUM_PERMISSIONS_COPY',
					'auth'      => 'acl_a_fauth && acl_a_authusers && acl_a_authgroups && acl_a_mauth',
					'cat'       => 'ACP_FORUM_BASED_PERMISSIONS',
					'after'     => ['setting_forum_local', 'ACP_FORUM_PERMISSIONS']
				],
				'pm_reports'            => [
					'base'      => 'pm_reports',
					'class'     => 'mcp',
					'title'     => 'MCP_PM_REPORTS_OPEN',
					'auth'      => 'aclf_m_report',
					'cat'       => 'MCP_REPORTS'
				],
				'pm_reports_closed'     => [
					'base'      => 'pm_reports',
					'class'     => 'mcp',
					'title'     => 'MCP_PM_REPORTS_CLOSED',
					'auth'      => 'aclf_m_report',
					'cat'       => 'MCP_REPORTS'
				],
				'pm_report_details'     => [
					'base'      => 'pm_reports',
					'class'     => 'mcp',
					'title'     => 'MCP_PM_REPORT_DETAILS',
					'auth'      => 'aclf_m_report',
					'cat'       => 'MCP_REPORTS'
				],
			];

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
				$sql = 'INSERT INTO ' .  GROUPS_TABLE . " (group_name, group_type, group_founder_manage, group_colour, group_legend, group_avatar, group_desc, group_desc_uid) VALUES ('NEWLY_REGISTERED', 3, 0, '', 0, '', '', '')";
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

				$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES ('ROLE_USER_NEW_MEMBER', 'ROLE_DESCRIPTION_USER_NEW_MEMBER', 'u_', {$next_order_id})";
				_sql($sql, $errored, $error_ary);
				$u_role = $db->sql_nextid();

				if (!$errored)
				{
					// Now add the correct data to the roles...
					// The standard role says that new users are not able to send a PM, Mass PM, are not able to PM groups
					$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT {$u_role}, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'u_%' AND auth_option IN ('u_sendpm', 'u_masspm', 'u_masspm_group')";
					_sql($sql, $errored, $error_ary);

					// Add user role to group
					$sql = 'INSERT INTO ' . ACL_GROUPS_TABLE . " (group_id, forum_id, auth_option_id, auth_role_id, auth_setting) VALUES ({$group_id}, 0, 0, {$u_role}, 0)";
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

				$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . " (role_name, role_description, role_type, role_order) VALUES  ('ROLE_FORUM_NEW_MEMBER', 'ROLE_DESCRIPTION_FORUM_NEW_MEMBER', 'f_', {$next_order_id})";
				_sql($sql, $errored, $error_ary);
				$f_role = $db->sql_nextid();

				if (!$errored)
				{
					$sql = 'INSERT INTO ' . ACL_ROLES_DATA_TABLE . " (role_id, auth_option_id, auth_setting) SELECT {$f_role}, auth_option_id, 0 FROM " . ACL_OPTIONS_TABLE . " WHERE auth_option LIKE 'f_%' AND auth_option IN ('f_noapprove')";
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
			require_once(PHPBB_ROOT_PATH . 'includes/acp/auth.php');
			$auth_admin = new auth_admin();
			$auth_admin->acl_clear_prefetch();

			// Minimum number of characters
			if (!isset($config['min_post_chars']))
			{
				set_config('min_post_chars', '1');
			}

			if (!isset($config['delete_time']))
			{
				set_config('delete_time', $config['edit_time']);
			}

			$no_updates = false;
		break;

		case '3.0.6-RC2':

			// Update the Custom Profile Fields based on previous settings to the new format
			$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . '
				SET field_show_on_vt = 1
				WHERE field_hide = 0
					AND (field_required = 1 OR field_show_on_reg = 1 OR field_show_profile = 1)';
			_sql($sql, $errored, $error_ary);
			$no_updates = false;

		break;

		case '3.0.6':

			// ATOM Feeds
			set_config('feed_overall', '1');
			set_config('feed_http_auth', '0');
			set_config('feed_limit_post', (string) (isset($config['feed_limit']) ? (int) $config['feed_limit'] : 15));
			set_config('feed_limit_topic', (string) (isset($config['feed_overall_topics_limit']) ? (int) $config['feed_overall_topics_limit'] : 10));
			set_config('feed_topics_new', (!empty($config['feed_overall_topics']) ? '1' : '0'));
			set_config('feed_topics_active', (!empty($config['feed_overall_topics']) ? '1' : '0'));
			$no_updates = false;
		break;

		case '3.0.7-PL1':
			// Install modules
			$modules_to_install = [
				'post'                  => [
					'base'      => 'board',
					'class'     => 'acp',
					'title'     => 'ACP_POST_SETTINGS',
					'auth'      => 'acl_a_board',
					'cat'       => 'ACP_MESSAGES',
					'after'     => ['message', 'ACP_MESSAGE_SETTINGS']
				],
			];

			_add_modules($modules_to_install);

			// Delete shadow topics pointing to not existing topics
			$batch_size = 500;

			// Set of affected forums we have to resync
			$sync_forum_ids = [];

			do
			{
				$sql_array = [
					'SELECT'    => 't1.topic_id, t1.forum_id',
					'FROM'      => [
						TOPICS_TABLE    => 't1',
					],
					'LEFT_JOIN' => [
						[
							'FROM'  => [TOPICS_TABLE    => 't2'],
							'ON'    => 't1.topic_moved_id = t2.topic_id',
						],
					],
					'WHERE'     => 't1.topic_moved_id <> 0
								AND t2.topic_id IS NULL',
				];
				$sql = $db->sql_build_query('SELECT', $sql_array);
				$result = $db->sql_query_limit($sql, $batch_size);

				$topic_ids = [];
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
				$sql_ary = [
					'group_name'    => substr($row['group_name'], 10), // Strip off 'EXT_GROUP_'
				];

				$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE group_id = ' . $row['group_id'];
				_sql($sql, $errored, $error_ary);
			}
			$db->sql_freeresult($result);

			$no_updates = false;
		break;

		case '3.0.9':
			if (!isset($config['email_max_chunk_size']))
			{
				set_config('email_max_chunk_size', '50');
			}

			$no_updates = false;
		break;

		case '3.0.10':
			// Delete orphan private messages
			$batch_size = 500;

			$sql_array = [
				'SELECT'    => 'p.msg_id',
				'FROM'      => [
					PRIVMSGS_TABLE  => 'p',
				],
				'LEFT_JOIN' => [
					[
						'FROM'  => [PRIVMSGS_TO_TABLE => 't'],
						'ON'    => 'p.msg_id = t.msg_id',
					],
				],
				'WHERE'     => 't.user_id IS NULL',
			];
			$sql = $db->sql_build_query('SELECT', $sql_array);

			do
			{
				$result = $db->sql_query_limit($sql, $batch_size);

				$delete_pms = [];
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
					require_once(PHPBB_ROOT_PATH . 'includes/acp/acp_bbcodes.php');
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

		default:
		break;
	}
}
