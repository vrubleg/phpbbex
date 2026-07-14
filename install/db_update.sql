-- General scheme updates
CREATE TABLE phpbb_user_confirm_keys (
	confirm_key varchar(10) NOT NULL,
	user_id mediumint(8) UNSIGNED NOT NULL,
	confirm_time int(11) UNSIGNED NOT NULL,
	PRIMARY KEY  (confirm_key),
	KEY user_id (user_id)
) CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`;

CREATE TABLE phpbb_browser_tracking (
	browser_id char(32) DEFAULT '' NOT NULL,
	user_id mediumint(8) UNSIGNED NOT NULL,
	tracking_first_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	tracking_last_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
	tracking_hits int(11) UNSIGNED DEFAULT '0' NOT NULL,
	browser_ua varchar(250) DEFAULT '' NOT NULL,
	tracking_first_ip varchar(40) DEFAULT '' NOT NULL,
	tracking_last_ip varchar(40) DEFAULT '' NOT NULL,
	PRIMARY KEY (browser_id,user_id)
) CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`;

ALTER TABLE phpbb_bbcodes ADD COLUMN bbcode_order smallint(4) DEFAULT '0' NOT NULL AFTER bbcode_id;

ALTER TABLE phpbb_posts ADD COLUMN poster_browser_id char(32) DEFAULT '' NOT NULL AFTER poster_ip;
ALTER TABLE phpbb_posts ADD COLUMN post_merged int(11) UNSIGNED DEFAULT '0' NOT NULL AFTER post_time;

-- Convert old Posts Merging MOD data (if available) to the new format.
UPDATE phpbb_posts SET post_merged = post_time, post_time=post_created WHERE post_created != 0 AND post_merged = 0;
ALTER TABLE phpbb_posts DROP COLUMN post_created;

ALTER TABLE phpbb_topics ADD COLUMN poll_show_voters tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER poll_vote_change;
ALTER TABLE phpbb_topics ADD COLUMN topic_first_post_show tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER poll_show_voters;

ALTER TABLE phpbb_topics ADD COLUMN topic_priority mediumint(8) DEFAULT '0' NOT NULL AFTER topic_type;
ALTER TABLE phpbb_topics ADD INDEX topic_priority (topic_priority);

ALTER TABLE phpbb_forums ADD COLUMN forum_topic_sortby_type varchar(1) DEFAULT '' NOT NULL AFTER forum_rules_uid;
ALTER TABLE phpbb_forums ADD COLUMN forum_topic_sortby_dir varchar(1) DEFAULT '' NOT NULL AFTER forum_topic_sortby_type;

ALTER TABLE phpbb_poll_votes ADD COLUMN vote_time int(11) UNSIGNED DEFAULT '0' NOT NULL AFTER vote_user_id;

ALTER TABLE phpbb_users ADD COLUMN user_gender tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER user_birthday;
ALTER TABLE phpbb_users ADD COLUMN user_topics mediumint(8) UNSIGNED DEFAULT '0' NOT NULL AFTER user_inactive_time;
ALTER TABLE phpbb_users ADD COLUMN user_skype varchar(32) DEFAULT '' NOT NULL AFTER user_jabber;
ALTER TABLE phpbb_users ADD COLUMN user_browser_ua varchar(250) DEFAULT '' NOT NULL AFTER user_ip;

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

-- New phpBBex ACL rights
REPLACE INTO phpbb_acl_options (auth_option, is_global) VALUES ('u_ignoreedittime', 1);
REPLACE INTO phpbb_acl_options (auth_option, is_global) VALUES ('u_ignorefpedittime', 1);
REPLACE INTO phpbb_acl_options (auth_option, is_global) VALUES ('u_canplus', 1);
REPLACE INTO phpbb_acl_options (auth_option, is_global) VALUES ('u_canminus', 1);
REPLACE INTO phpbb_acl_roles_data (role_id, auth_option_id, auth_setting) SELECT 5, auth_option_id, 1 FROM phpbb_acl_options WHERE auth_option IN ('u_canplus', 'u_canminus');
REPLACE INTO phpbb_acl_roles_data (role_id, auth_option_id, auth_setting) SELECT 6, auth_option_id, 1 FROM phpbb_acl_options WHERE auth_option IN ('u_canplus', 'u_canminus');
REPLACE INTO phpbb_acl_roles_data (role_id, auth_option_id, auth_setting) SELECT 7, auth_option_id, 1 FROM phpbb_acl_options WHERE auth_option IN ('u_canplus', 'u_canminus');
REPLACE INTO phpbb_acl_roles_data (role_id, auth_option_id, auth_setting) SELECT 8, auth_option_id, 1 FROM phpbb_acl_options WHERE auth_option IN ('u_canplus', 'u_canminus');
REPLACE INTO phpbb_acl_roles_data (role_id, auth_option_id, auth_setting) SELECT 9, auth_option_id, 1 FROM phpbb_acl_options WHERE auth_option IN ('u_canplus', 'u_canminus');

-- Remove subjects with 'Re: '
-- UPDATE phpbb_posts SET post_subject = '' WHERE post_subject LIKE 'Re: %';

-- Remove subjects with 'Re: ' (excluding first posts, it is much slower)
UPDATE phpbb_posts p LEFT JOIN phpbb_topics t ON t.topic_first_post_id = p.post_id SET p.post_subject = '' WHERE p.post_subject LIKE 'Re: %' AND t.topic_first_post_id IS NULL;

-- Resolve conflicts with the new system bbcodes
DELETE FROM phpbb_bbcodes WHERE bbcode_tag IN ('s', 'tt', 'upd', 'upd=', 'spoiler', 'spoiler=');
SELECT (@new_bbcode_id:=GREATEST(MAX(bbcode_id)+1, 17)) FROM phpbb_bbcodes;
UPDATE phpbb_bbcodes SET bbcode_id=@new_bbcode_id WHERE bbcode_id = 13;
SELECT (@new_bbcode_id:=GREATEST(MAX(bbcode_id)+1, 17)) FROM phpbb_bbcodes;
UPDATE phpbb_bbcodes SET bbcode_id=@new_bbcode_id WHERE bbcode_id = 14;
SELECT (@new_bbcode_id:=GREATEST(MAX(bbcode_id)+1, 17)) FROM phpbb_bbcodes;
UPDATE phpbb_bbcodes SET bbcode_id=@new_bbcode_id WHERE bbcode_id = 15;
SELECT (@new_bbcode_id:=GREATEST(MAX(bbcode_id)+1, 17)) FROM phpbb_bbcodes;
UPDATE phpbb_bbcodes SET bbcode_id=@new_bbcode_id WHERE bbcode_id = 16;

-- Post rates
CREATE TABLE phpbb_post_rates (
	user_id mediumint(8) unsigned NOT NULL,
	post_id mediumint(8) unsigned NOT NULL,
	rate tinyint(4) NOT NULL DEFAULT '0',
	rate_time int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (user_id,post_id),
	KEY post_id (post_id),
	KEY user_id (user_id)
) CHARACTER SET `utf8mb4` COLLATE `utf8mb4_bin`;

ALTER TABLE phpbb_posts
	ADD COLUMN post_rating_positive mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER post_reported,
	ADD COLUMN post_rating_negative mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER post_rating_positive;

ALTER TABLE phpbb_users
	ADD COLUMN user_rating_positive mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER user_last_search,
	ADD COLUMN user_rating_negative mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER user_rating_positive,
	ADD COLUMN user_rated_positive mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER user_rating_negative,
	ADD COLUMN user_rated_negative mediumint(8) UNSIGNED NOT NULL DEFAULT 0 AFTER user_rated_positive;

UPDATE phpbb_extension_groups SET group_name = 'AUDIO' WHERE cat_id = 3;
UPDATE phpbb_extension_groups SET group_name = 'VIDEO' WHERE cat_id = 2;
UPDATE phpbb_extension_groups SET cat_id = 0 WHERE cat_id = 6;
