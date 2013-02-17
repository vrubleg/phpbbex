-- General scheme updates
CREATE TABLE phpbb_user_confirm_keys (
	confirm_key varchar(10) NOT NULL,
	user_id mediumint(8) UNSIGNED NOT NULL,
	confirm_time int(11) UNSIGNED NOT NULL,
	PRIMARY KEY  (confirm_key),
	KEY user_id (user_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;

CREATE TABLE phpbb_user_browser_ids (
	browser_id char(32) DEFAULT '' NOT NULL,
	user_id mediumint(8) UNSIGNED NOT NULL,
	created int(11) UNSIGNED DEFAULT '0' NOT NULL,
	last_visit int(11) UNSIGNED DEFAULT '0' NOT NULL,
	visits int(11) UNSIGNED DEFAULT '0' NOT NULL,
	agent varchar(150) DEFAULT '' NOT NULL,
	last_ip varchar(40) DEFAULT '' NOT NULL,
	PRIMARY KEY (browser_id,user_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;

ALTER TABLE phpbb_bbcodes
	ADD COLUMN bbcode_order smallint(4) DEFAULT '0' NOT NULL AFTER bbcode_id;

ALTER TABLE phpbb_posts
	ADD COLUMN poster_browser_id char(32) DEFAULT '' NOT NULL AFTER poster_ip,
	ADD COLUMN post_created int(11) UNSIGNED DEFAULT '0' NOT NULL AFTER post_time;

ALTER TABLE phpbb_topics
	ADD COLUMN topic_priority mediumint(8) DEFAULT '0' NOT NULL AFTER topic_type,
	ADD COLUMN poll_show_voters tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER poll_vote_change,
	ADD COLUMN topic_first_post_show tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER poll_show_voters;

ALTER TABLE phpbb_poll_votes
	ADD COLUMN vote_time int(11) UNSIGNED DEFAULT '0' NOT NULL AFTER vote_user_id;

ALTER TABLE phpbb_users
	ADD COLUMN user_topics_per_page mediumint(8) UNSIGNED DEFAULT '0' NOT NULL AFTER user_topic_sortby_dir,
	ADD COLUMN user_posts_per_page mediumint(8) UNSIGNED DEFAULT '0' NOT NULL AFTER user_post_sortby_dir,
	ADD COLUMN user_gender tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER user_birthday,
	ADD COLUMN user_topics mediumint(8) UNSIGNED DEFAULT '0' NOT NULL AFTER user_inactive_time,
	ADD COLUMN user_skype varchar(255) DEFAULT '' NOT NULL AFTER user_jabber,
	ADD COLUMN user_browser varchar(150) DEFAULT '' NOT NULL AFTER user_ip;

ALTER TABLE phpbb_warnings
	ADD COLUMN warning_active tinyint(1) UNSIGNED DEFAULT '1' NOT NULL AFTER warning_id,
	ADD COLUMN issuer_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL AFTER warning_active,
	ADD COLUMN warning_days int(11) UNSIGNED DEFAULT '0' NOT NULL AFTER warning_time,
	ADD COLUMN warning_type enum('remark','warning','ban') DEFAULT 'warning' NOT NULL AFTER warning_days,
	ADD COLUMN warning_text text NULL AFTER warning_type,
	ADD INDEX warning_active (warning_active),
	ADD INDEX issuer_id (issuer_id),
	ADD INDEX user_id (user_id),
	ADD INDEX post_id (post_id);

ALTER TABLE phpbb_config MODIFY COLUMN config_value VARCHAR(1000) NOT NULL DEFAULT '';

-- New phpBBex options
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('active_topics_on_index', '5');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('active_topics_days', '30');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('active_users_days', '90');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('announce_index', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_quick_reply', '2');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_quick_reply_options', '20');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_quick_post', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_quick_post_options', '20');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('copyright_notice', 'Powered by {POWERED_BY}');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('load_online_bots', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('login_via_email_enable', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_post_imgs', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_sig_imgs', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_sig_lines', '4');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('merge_interval', '18');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('merge_no_forums', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('merge_no_topics', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('no_sid', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('no_typical_info_pages', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('outlinks', '');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('override_user_lang', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('override_user_dateformat', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('override_user_timezone', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('override_user_dst', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('site_keywords', '');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('warning_post_default', '');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('auto_guest_lang', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('default_search_titleonly', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('search_highlight_keywords', '0');

-- New phpBBex ACL rights
REPLACE INTO phpbb_acl_options (auth_option, is_global) VALUES ('u_ignoreedittime', 1);
REPLACE INTO phpbb_acl_options (auth_option, is_global) VALUES ('u_ignorefpedittime', 1);

-- Reset options for all users (new dateformat, enable quick reply, etc)
UPDATE phpbb_users SET user_options = 233343, user_dateformat = '|d.m.Y|{, H:i}';

-- Other options for robots
UPDATE phpbb_users SET user_dateformat = 'd.m.Y{, H:i}' WHERE group_id = 6;

-- Show all forums in active topics
UPDATE phpbb_forums SET forum_flags = forum_flags|16;

-- Remove subjects with "Re: "
-- UPDATE phpbb_posts SET post_subject = "" WHERE post_subject LIKE "Re: %";

-- Remove subjects with "Re: " (excluding first posts, it is much slower)
-- UPDATE phpbb_posts p LEFT JOIN phpbb_topics t ON t.topic_first_post_id = p.post_id SET p.post_subject = "" WHERE p.post_subject LIKE "Re: %" AND t.topic_first_post_id IS NULL;

-- Delete user bbcode [s]
DELETE FROM phpbb_bbcodes WHERE bbcode_tag = 's';
UPDATE IGNORE phpbb_bbcodes SET bbcode_id=14 WHERE bbcode_id = 13;
UPDATE IGNORE phpbb_bbcodes SET bbcode_id=15 WHERE bbcode_id = 13;
UPDATE IGNORE phpbb_bbcodes SET bbcode_id=16 WHERE bbcode_id = 13;
UPDATE IGNORE phpbb_bbcodes SET bbcode_id=17 WHERE bbcode_id = 13;
UPDATE IGNORE phpbb_bbcodes SET bbcode_id=18 WHERE bbcode_id = 13;
UPDATE IGNORE phpbb_bbcodes SET bbcode_id=19 WHERE bbcode_id = 13;
UPDATE IGNORE phpbb_bbcodes SET bbcode_id=20 WHERE bbcode_id = 13;

-- Post rates
CREATE TABLE phpbb_post_rates (
	user_id mediumint(8) unsigned NOT NULL,
	post_id mediumint(8) unsigned NOT NULL,
	rate tinyint(4) NOT NULL DEFAULT '0',
	rate_time int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (user_id,post_id),
	KEY post_id (post_id),
	KEY user_id (user_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;

ALTER TABLE phpbb_posts
	ADD COLUMN post_rating_positive mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER post_reported,
	ADD COLUMN post_rating_negative mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER post_rating_positive;

ALTER TABLE phpbb_users
	ADD COLUMN user_rating_positive mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER user_last_search,
	ADD COLUMN user_rating_negative mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER user_rating_positive,
	ADD COLUMN user_rated_positive mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER user_rating_negative,
	ADD COLUMN user_rated_negative mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER user_rated_positive;

REPLACE INTO phpbb_config (config_name, config_value) VALUES ('rate_enabled', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('rate_only_topics', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('rate_time', 0);
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('rate_topic_time', -1);
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('rate_change_time', 60*5);
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('rate_no_negative', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('rate_no_positive', '0');

-- Style options
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_min_width', '875');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_max_width', '1280');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_back_to_top', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_rounded_corners', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_new_year', '-1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_show_sitename_in_headerbar', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_show_social_buttons', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_show_feeds_in_forumlist', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_on_left', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_topic_poster', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_gender', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_age', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_from', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_warnings', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_rating', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_rating_detailed', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_rated', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_rated_detailed', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_posts', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_topics', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_joined', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_with_us', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_mp_show_buttons', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_p_show_rating', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_p_show_rating_detailed', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_p_show_rated', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_p_show_rated_detailed', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_row_numbers', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_gender', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_rank', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_rating', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_rating_detailed', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_rated', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_rated_detailed', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_posts', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_topics', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_from', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_website', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_joined', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('style_ml_show_last_active', '1');

-- Reset avatar options to phpBBex defaults
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_upload', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_avatar_remote_upload', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('avatar_filesize', '10240');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('avatar_max_height', '100');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('avatar_max_width', '100');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('avatar_min_height', '64');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('avatar_min_width', '64');

-- Reset signature options to phpBBex defaults (Disable BBCodes, max 200 characters)
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_sig_bbcode', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_sig_img', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_sig_links', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_sig_smilies', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_sig_chars', '200');

-- Reset attachments options to phpBBex defaults
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_pm_attach', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_attachments', '30');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_filesize', '524288');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_filesize_pm', '262144');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('img_create_thumbnail', '1');

-- Reset some other options to phpBBex defaults
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('allow_name_chars', 'USERNAME_LETTER_NUM_SPACERS');
-- REPLACE INTO phpbb_config (config_name, config_value) VALUES ('require_activation', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('default_dateformat', '|d.m.Y|{, H:i}');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('edit_time', '60');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('delete_time', '15');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('feed_enable', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('feed_item_statistics', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('feed_overall', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('form_token_lifetime', '43200');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('gzip_compress', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('load_moderators', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('load_tplcompile', '1');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_poll_options', '25');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_post_smilies', '20');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_post_urls', '20');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('max_quote_depth', '2');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('pm_max_msgs', '1000');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('hot_threshold', '100');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('posts_per_page', '20');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('topics_per_page', '50');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('board_hide_emails', '0');
UPDATE phpbb_users SET user_allow_viewemail = 0;

-- External links
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('external_links_newwindow', '0');
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('external_links_newwindow_exclude', '');

-- New file extensions
ALTER TABLE phpbb_extensions ADD UNIQUE INDEX extension (extension);
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (3, 'diff');
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (3, 'sql');
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (6, 'avi');
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (9, 'oga');
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (9, 'ogv');
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (9, 'mka');
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (9, 'mkv');
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (9, 'webm');
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (9, 'webp');
REPLACE INTO phpbb_extensions (group_id, extension) VALUES (9, 'opus');

-- Disable acp_update
UPDATE phpbb_modules SET module_enabled = 0 WHERE module_basename = 'update' AND module_class = 'acp';

-- Update YandexBot UA and remove Aport [Bot]
UPDATE phpbb_bots SET bot_agent = "YandexBot/" WHERE bot_agent = "Yandex/";
DELETE FROM phpbb_users WHERE username="Aport [Bot]";
DELETE FROM phpbb_bots WHERE bot_name="Aport [Bot]";

-- Remove invalid skypenames
UPDATE phpbb_users SET user_skype="" WHERE user_skype NOT RLIKE "^[a-zA-Z][-_.a-zA-Z0-9]{5,31}$" AND user_skype != "";

-- Remove obsolete options
DELETE FROM phpbb_config WHERE config_name IN ('style_show_liveinternet_counter', 'style_google_analytics_id', 'copyright_notice_html', 'style_auto_new_year');

-- phpBBex version
REPLACE INTO phpbb_config (config_name, config_value) VALUES ('phpbbex_version', '1.5.3');
