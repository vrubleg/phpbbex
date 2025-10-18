<?php
/**
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (!defined('IN_INSTALL'))
{
	exit;
}

class phpbb_gallery_dbal_schema
{
	static public function get_table_data($table)
	{
		return self::$data[$table]['structure'];
	}

	/**
	* Column Types:
	*	INT:x		=> SIGNED int(x)
	*	BINT		=> BIGINT
	*	UINT		=> mediumint(8) UNSIGNED
	*	UINT:x		=> int(x) UNSIGNED
	*	TINT:x		=> tinyint(x)
	*	USINT		=> smallint(4) UNSIGNED (for _order columns)
	*	BOOL		=> tinyint(1) UNSIGNED
	*	VCHAR		=> varchar(255)
	*	CHAR:x		=> char(x)
	*	XSTEXT_UNI	=> text for storing 100 characters (topic_title for example)
	*	STEXT_UNI	=> text for storing 255 characters (normal input field with a max of 255 single-byte chars) - same as VCHAR_UNI
	*	TEXT_UNI	=> text for storing 3000 characters (short text, descriptions, comments, etc.)
	*	MTEXT_UNI	=> mediumtext (post text, large text)
	*	VCHAR:x		=> varchar(x)
	*	TIMESTAMP	=> int(11) UNSIGNED
	*	DECIMAL		=> decimal number (5,2)
	*	DECIMAL:	=> decimal number (x,2)
	*	PDECIMAL	=> precision decimal number (6,3)
	*	PDECIMAL:	=> precision decimal number (x,3)
	*	VCHAR_UNI	=> varchar(255) BINARY
	*	VCHAR_CI	=> varchar_ci for postgresql, others VCHAR
	*/
	static private $data = [
		'albums'	=> [
			'full_name'		=> GALLERY_ALBUMS_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '1.1.0',
			'structure'		=> [
				'COLUMNS'		=> [
					'album_id'					=> ['UINT', null, 'auto_increment'],
					'parent_id'					=> ['UINT', 0],
					'left_id'					=> ['UINT', 1],
					'right_id'					=> ['UINT', 2],
					'album_parents'				=> ['MTEXT_UNI', ''],
					'album_type'				=> ['UINT:3', 1],
					'album_status'				=> ['UINT:1', 1],
					'album_contest'				=> ['UINT', 0],
					'album_name'				=> ['VCHAR:255', ''],
					'album_desc'				=> ['MTEXT_UNI', ''],
					'album_desc_options'		=> ['UINT:3', 7],
					'album_desc_uid'			=> ['VCHAR:8', ''],
					'album_desc_bitfield'		=> ['VCHAR:255', ''],
					'album_user_id'				=> ['UINT', 0],
					'album_images'				=> ['UINT', 0],
					'album_images_real'			=> ['UINT', 0],
					'album_last_image_id'		=> ['UINT', 0],
					'album_image'				=> ['VCHAR', ''],
					'album_last_image_time'		=> ['INT:11', 0],
					'album_last_image_name'		=> ['VCHAR', ''],
					'album_last_username'		=> ['VCHAR', ''],
					'album_last_user_colour'	=> ['VCHAR:6', ''],
					'album_last_user_id'		=> ['UINT', 0],
					'album_watermark'			=> ['UINT:1', 1],
					'album_sort_key'			=> ['VCHAR:8', ''],
					'album_sort_dir'			=> ['VCHAR:8', ''],
					'display_in_rrc'			=> ['UINT:1', 1],
					'display_on_index'			=> ['UINT:1', 1],
					'display_subalbum_list'		=> ['UINT:1', 1],
					'album_feed'				=> ['BOOL', 1],
					'album_auth_access'			=> ['TINT:1', 0],
				],
				'PRIMARY_KEY'	=> 'album_id',
			],
		],
		'albums_track'	=> [
			'full_name'		=> GALLERY_ATRACK_TABLE,
			'added'			=> '0.5.2',
			'modified'		=> '0.5.2',
			'structure'		=> [
				'COLUMNS'		=> [
					'user_id'				=> ['UINT', 0],
					'album_id'				=> ['UINT', 0],
					'mark_time'				=> ['TIMESTAMP', 0],
				],
				'PRIMARY_KEY'	=> ['user_id', 'album_id'],
			],
		],
		'comments'	=> [
			'full_name'		=> GALLERY_COMMENTS_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '1.1.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'comment_id'			=> ['UINT', null, 'auto_increment'],
					'comment_image_id'		=> ['UINT', null],
					'comment_user_id'		=> ['UINT', 0],
					'comment_username'		=> ['VCHAR', ''],
					'comment_user_colour'	=> ['VCHAR:6', ''],
					'comment_user_ip'		=> ['VCHAR:40', ''],
					'comment_signature'		=> ['BOOL', 0],
					'comment_time'			=> ['UINT:11', 0],
					'comment'				=> ['MTEXT_UNI', ''],
					'comment_uid'			=> ['VCHAR:8', ''],
					'comment_bitfield'		=> ['VCHAR:255', ''],
					'comment_edit_time'		=> ['UINT:11', 0],
					'comment_edit_count'	=> ['USINT', 0],
					'comment_edit_user_id'	=> ['UINT', 0],
				],
				'PRIMARY_KEY'	=> 'comment_id',
				'KEYS'		=> [
					'id'			=> ['INDEX', 'comment_image_id'],
					'uid'			=> ['INDEX', 'comment_user_id'],
					'ip'			=> ['INDEX', 'comment_user_ip'],
					'time'			=> ['INDEX', 'comment_time'],
				],
			],
		],
		'config'	=> [
			'full_name'		=> GALLERY_CONFIG_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '0.0.0',
			'structure'		=> [
				'COLUMNS'		=> [
					'config_name'		=> ['VCHAR:191', ''],
					'config_value'		=> ['VCHAR:255', ''],
				],
				'PRIMARY_KEY'	=> 'config_name',
			],
		],
		'contests'	=> [
			'full_name'		=> GALLERY_CONTESTS_TABLE,
			'added'			=> '0.4.1',
			'modified'		=> '0.4.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'contest_id'			=> ['UINT', null, 'auto_increment'],
					'contest_album_id'		=> ['UINT', 0],
					'contest_start'			=> ['UINT:11', 0],
					'contest_rating'		=> ['UINT:11', 0],
					'contest_end'			=> ['UINT:11', 0],
					'contest_marked'		=> ['TINT:1', 0],
					'contest_first'			=> ['UINT', 0],
					'contest_second'		=> ['UINT', 0],
					'contest_third'			=> ['UINT', 0],
				],
				'PRIMARY_KEY'	=> 'contest_id',
			],
		],
		'copyts_albums'	=> [
			'full_name'		=> 'phpbb_gallery_copyts_albums',
			'added'			=> '0.0.0',
			'modified'		=> '0.0.0',
			'structure'		=> [
				'COLUMNS'		=> [
					'album_id'				=> ['UINT', null, 'auto_increment'],
					'parent_id'				=> ['UINT', 0],
					'left_id'				=> ['UINT', 1],
					'right_id'				=> ['UINT', 2],
					'album_name'			=> ['VCHAR:255', ''],
					'album_desc'			=> ['MTEXT_UNI', ''],
					'album_user_id'			=> ['UINT', 0],
				],
				'PRIMARY_KEY'	=> 'album_id',
			],
		],
		'copyts_users'	=> [
			'full_name'		=> 'phpbb_gallery_copyts_users',
			'added'			=> '0.0.0',
			'modified'		=> '0.0.0',
			'structure'		=> [
				'COLUMNS'		=> [
					'user_id'			=> ['UINT', 0],
					'personal_album_id'	=> ['UINT', 0],
				],
				'PRIMARY_KEY'		=> 'user_id',
			],
		],
		'favorites'	=> [
			'full_name'		=> GALLERY_FAVORITES_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '1.1.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'favorite_id'			=> ['UINT', null, 'auto_increment'],
					'user_id'				=> ['UINT', 0],
					'image_id'				=> ['UINT', 0],
				],
				'PRIMARY_KEY'	=> 'favorite_id',
				'KEYS'		=> [
					'uid'		=> ['INDEX', 'user_id'],
					'id'		=> ['INDEX', 'image_id'],
				],
			],
		],
		'images'	=> [
			'full_name'		=> GALLERY_IMAGES_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '1.1.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'image_id'				=> ['UINT', null, 'auto_increment'],
					'image_filename'		=> ['VCHAR:255', ''],
					'image_name'			=> ['VCHAR:255', ''],
					'image_name_clean'		=> ['VCHAR:255', ''],
					'image_desc'			=> ['MTEXT_UNI', ''],
					'image_desc_uid'		=> ['VCHAR:8', ''],
					'image_desc_bitfield'	=> ['VCHAR:255', ''],
					'image_user_id'			=> ['UINT', 0],
					'image_username'		=> ['VCHAR:255', ''],
					'image_username_clean'	=> ['VCHAR:255', ''],
					'image_user_colour'		=> ['VCHAR:6', ''],
					'image_user_ip'			=> ['VCHAR:40', ''],
					'image_time'			=> ['UINT:11', 0],
					'image_album_id'		=> ['UINT', 0],
					'image_view_count'		=> ['UINT:11', 0],
					'image_status'			=> ['UINT:3', 0],
					'image_contest'			=> ['UINT:1', 0],
					'image_contest_end'		=> ['TIMESTAMP', 0],
					'image_contest_rank'	=> ['UINT:3', 0],
					'image_filemissing'		=> ['UINT:3', 0],
					'image_has_exif'		=> ['UINT:3', 2],
					'image_exif_data'		=> ['TEXT', ''],
					'image_rates'			=> ['UINT', 0],
					'image_rate_points'		=> ['UINT', 0],
					'image_rate_avg'		=> ['UINT', 0],
					'image_comments'		=> ['UINT', 0],
					'image_last_comment'	=> ['UINT', 0],
					'image_allow_comments'	=> ['TINT:1', 1],
					'image_favorited'		=> ['UINT', 0],
					'image_reported'		=> ['UINT', 0],
					'filesize_upload'		=> ['UINT:20', 0],
					'filesize_medium'		=> ['UINT:20', 0],
					'filesize_cache'		=> ['UINT:20', 0],
				],
				'PRIMARY_KEY'				=> 'image_id',
				'KEYS'		=> [
					'aid'			=> ['INDEX', 'image_album_id'],
					'uid'			=> ['INDEX', 'image_user_id'],
					'time'			=> ['INDEX', 'image_time'],
				],
			],
		],
		'modscache'	=> [
			'full_name'		=> GALLERY_MODSCACHE_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '1.1.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'album_id'				=> ['UINT', 0],
					'user_id'				=> ['UINT', 0],
					'username'				=> ['VCHAR', ''],
					'group_id'				=> ['UINT', 0],
					'group_name'			=> ['VCHAR', ''],
					'display_on_index'		=> ['TINT:1', 1],
				],
				'KEYS'		=> [
					'doi'		=> ['INDEX', 'display_on_index'],
					'aid'		=> ['INDEX', 'album_id'],
				],
			],
		],
		'permissions'	=> [
			'full_name'		=> GALLERY_PERMISSIONS_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '0.4.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'perm_id'			=> ['UINT', null, 'auto_increment'],
					'perm_role_id'		=> ['UINT', 0],
					'perm_album_id'		=> ['UINT', 0],
					'perm_user_id'		=> ['UINT', 0],
					'perm_group_id'		=> ['UINT', 0],
					'perm_system'		=> ['INT:3', 0],
				],
				'PRIMARY_KEY'			=> 'perm_id',
			],
		],
		'rates'	=> [
			'full_name'		=> GALLERY_RATES_TABLE,
			'added'			=> '0.0.0',
			'modified'		=> '1.1.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'rate_image_id'		=> ['UINT', 0],
					'rate_user_id'		=> ['UINT', 0],
					'rate_user_ip'		=> ['VCHAR:40', ''],
					'rate_point'		=> ['UINT:3', 0],
				],
				'PRIMARY_KEY'	=> ['rate_image_id', 'rate_user_id'],
			],
		],
		'reports'	=> [
			'full_name'		=> GALLERY_REPORTS_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '0.3.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'report_id'				=> ['UINT', null, 'auto_increment'],
					'report_album_id'		=> ['UINT', 0],
					'report_image_id'		=> ['UINT', 0],
					'reporter_id'			=> ['UINT', 0],
					'report_manager'		=> ['UINT', 0],
					'report_note'			=> ['MTEXT_UNI', ''],
					'report_time'			=> ['UINT:11', 0],
					'report_status'			=> ['UINT:3', 0],
				],
				'PRIMARY_KEY'	=> 'report_id',
			],
		],
		'roles'	=> [
			'full_name'		=> GALLERY_ROLES_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '1.1.3',
			'structure'		=> [
				'COLUMNS'		=> [
					'role_id'			=> ['UINT', null, 'auto_increment'],
					'a_list'			=> ['UINT:3', 0],
					'i_view'			=> ['UINT:3', 0],
					'i_watermark'		=> ['UINT:3', 0],
					'i_upload'			=> ['UINT:3', 0],
					'i_edit'			=> ['UINT:3', 0],
					'i_delete'			=> ['UINT:3', 0],
					'i_rate'			=> ['UINT:3', 0],
					'i_approve'			=> ['UINT:3', 0],
					'i_lock'			=> ['UINT:3', 0],
					'i_report'			=> ['UINT:3', 0],
					'i_count'			=> ['UINT', 0],
					'i_unlimited'		=> ['UINT:3', 0],
					'c_read'			=> ['UINT:3', 0],
					'c_post'			=> ['UINT:3', 0],
					'c_edit'			=> ['UINT:3', 0],
					'c_delete'			=> ['UINT:3', 0],
					'm_comments'		=> ['UINT:3', 0],
					'm_delete'			=> ['UINT:3', 0],
					'm_edit'			=> ['UINT:3', 0],
					'm_move'			=> ['UINT:3', 0],
					'm_report'			=> ['UINT:3', 0],
					'm_status'			=> ['UINT:3', 0],
					'a_count'			=> ['UINT', 0],
					'a_unlimited'		=> ['UINT:3', 0],
					'a_restrict'		=> ['UINT:3', 0],
				],
				'PRIMARY_KEY'		=> 'role_id',
			],
		],
		'users'	=> [
			'full_name'		=> GALLERY_USERS_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '1.1.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'user_id'			=> ['UINT', 0],
					'watch_own'			=> ['UINT:3', 0],
					'watch_favo'		=> ['UINT:3', 0],
					'watch_com'			=> ['UINT:3', 0],
					'user_images'		=> ['UINT', 0],
					'personal_album_id'	=> ['UINT', 0],
					'user_lastmark'		=> ['TIMESTAMP', 0],
					'user_last_update'	=> ['TIMESTAMP', 0],
					'user_viewexif'		=> ['UINT:1', 0],
					'user_permissions'	=> ['MTEXT_UNI', ''],
					'user_permissions_changed'	=> ['TIMESTAMP', 0],
					'user_allow_comments'		=> ['TINT:1', 1],
					'subscribe_pegas'			=> ['TINT:1', 0],
				],
				'PRIMARY_KEY'		=> 'user_id',
				'KEYS'		=> [
					'pega'			=> ['INDEX', ['personal_album_id']],
				],
			],
		],
		'watch'	=> [
			'full_name'		=> GALLERY_WATCH_TABLE,
			'added'			=> '0.3.1',
			'modified'		=> '1.1.1',
			'structure'		=> [
				'COLUMNS'		=> [
					'watch_id'		=> ['UINT', null, 'auto_increment'],
					'album_id'		=> ['UINT', 0],
					'image_id'		=> ['UINT', 0],
					'user_id'		=> ['UINT', 0],
				],
				'PRIMARY_KEY'		=> 'watch_id',
				'KEYS'		=> [
					'uid'			=> ['INDEX', 'user_id'],
					'id'			=> ['INDEX', 'image_id'],
					'aid'			=> ['INDEX', 'album_id'],
				],
			],
		],
	];
}
