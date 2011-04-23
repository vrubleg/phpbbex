CREATE TABLE phpbb_user_confirm_keys (
	confirm_key varchar(10) NOT NULL,
	user_id mediumint(8) NOT NULL,
	confirm_time int(11) NOT NULL,
	PRIMARY KEY  (confirm_key),
	KEY user_id (user_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;

ALTER TABLE phpbb_bbcodes
	ADD COLUMN bbcode_order smallint(4) DEFAULT '0' NOT NULL AFTER bbcode_id;

ALTER TABLE phpbb_posts
	ADD COLUMN post_created int(11) UNSIGNED DEFAULT '0' NOT NULL AFTER post_time;

ALTER TABLE phpbb_topics
	ADD COLUMN poll_show_voters tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER poll_vote_change,
	ADD COLUMN topic_first_post_show tinyint(1) UNSIGNED DEFAULT '0' NOT NULL AFTER poll_show_voters;

ALTER TABLE phpbb_users
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

INSERT INTO phpbb_config (config_name, config_value) VALUES ('announce_index', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_quick_reply_options', '20');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_quick_post', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('allow_quick_post_options', '20');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('login_via_email_enable', '1');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_post_imgs', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_sig_imgs', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('max_sig_lines', '4');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('merge_interval', '18');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('merge_no_forums', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('merge_no_topics', '0');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('site_keywords', '');
INSERT INTO phpbb_config (config_name, config_value) VALUES ('warning_post_default', '');

DELETE FROM phpbb_bbcodes WHERE bbcode_tag = 's';
UPDATE phpbb_bbcodes SET bbcode_id=(SELECT MAX(bbcode_id)+1) WHERE bbcode_id = 13;
