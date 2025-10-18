<?php
/**
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
* borrowed from phpBB3
* @author: phpBB Group
* @file: acp_boards
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_gallery_config
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;
	var $new_config = [];

	function main($id, $mode)
	{
		global $db, $user, $auth, $cache, $template;

		phpbb_gallery::init();

		$user->add_lang(['mods/gallery_acp', 'mods/gallery']);

		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_time';
		add_form_key($form_key);

		switch ($mode)
		{
			case 'main':

				// Disable some Options if they can not be used
				if (!function_exists('exif_read_data'))
				{
					$this->display_vars['vars']['disp_exifdata']['type'] = 'custom';
					$this->display_vars['vars']['disp_exifdata']['explain'] = true;
					$this->display_vars['vars']['disp_exifdata']['method'] = 'disabled_boolean';
				}
				if (!function_exists('imagerotate'))
				{
					$this->display_vars['vars']['allow_rotate']['type'] = 'custom';
					$this->display_vars['vars']['allow_rotate']['explain'] = true;
					$this->display_vars['vars']['allow_rotate']['method'] = 'disabled_boolean';
				}
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		phpbb_gallery_config::load(true);
		$this->new_config = phpbb_gallery_config::get_array();
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', ['' => ''], true)) : $this->new_config;
		$error = [];

		// We validate the complete config if whished
		validate_config_vars($this->display_vars['vars'], $cfg_array, $error);
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
		foreach ($this->display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				// Check for RRC-display-options
				if (isset($null['method']) && (($null['method'] == 'rrc_display') || ($null['method'] == 'rrc_modes')))
				{
					// Changing the value, casted by int to not mess up anything
					$config_value = (int) array_sum(request_var($config_name, [0]));
				}
				// Recalculate the Watermark-position
				if (isset($null['method']) && ($null['method'] == 'watermark_position'))
				{
					// Changing the value, casted by int to not mess up anything
					$config_value = request_var('watermark_position_x', 0) + request_var('watermark_position_y', 0);
				}
				if ($config_name == 'link_thumbnail')
				{
					$update_bbcode = request_var('update_bbcode', '');
					// Update the BBCode
					if ($update_bbcode)
					{
						if (!class_exists('acp_bbcodes'))
						{
							phpbb_gallery_url::_include('acp/acp_bbcodes', 'phpbb');
						}
						$acp_bbcodes = new acp_bbcodes();
						$bbcode_match = '[album]{NUMBER}[/album]';
						$bbcode_tpl = $this->bbcode_tpl($config_value);

						$sql_ary = $acp_bbcodes->build_regexp($bbcode_match, $bbcode_tpl);
						$sql_ary = array_merge($sql_ary, [
							'bbcode_match'			=> $bbcode_match,
							'bbcode_tpl'			=> $bbcode_tpl,
							'display_on_posting'	=> true,
							'bbcode_helpline'		=> 'GALLERY_HELPLINE_ALBUM',
						]);

						$sql = 'UPDATE ' . BBCODES_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE bbcode_tag = '" . $sql_ary['bbcode_tag'] . "'";
						$db->sql_query($sql);
						$cache->destroy('sql', BBCODES_TABLE);
					}
				}
				if ((strpos($config_name, 'watermark') !== false) && (phpbb_gallery_config::get($config_name) != $config_value))
				{
					phpbb_gallery_config::set('watermark_changed', time());
				}
				phpbb_gallery_config::set($config_name, $config_value);
			}
		}

		if ($submit)
		{
			$cache->destroy('sql', CONFIG_TABLE);
			trigger_error($user->lang['GALLERY_CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->tpl_name = 'acp_board';
		$this->page_title = $this->display_vars['title'];

		$template->assign_vars([
			'L_TITLE'			=> $user->lang[$this->display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$this->display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action]
		);

		// Output relevant page
		foreach ($this->display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', [
					'S_LEGEND'		=> true,
					'LEGEND'		=> $user->lang[$vars] ?? $vars]
				);

				continue;
			}
			if (isset($vars['append']))
			{
				$vars['append'] = (isset($user->lang[$vars['append']])) ? ' ' . $user->lang[$vars['append']] : $vars['append'];
			}

			$this->new_config[$config_key] = phpbb_gallery_config::get($config_key);

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'])
			{
				$l_explain = $user->lang[$vars['lang'] . '_EXP'] ?? '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', [
				'KEY'			=> $config_key,
				'TITLE'			=> $user->lang[$vars['lang']] ?? $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
			]);

			unset($this->display_vars['vars'][$config_key]);
		}
	}

	/**
	* Disabled Radio Buttons
	*/
	function disabled_boolean($value, $key)
	{
		global $user;

		$tpl = '';

		$tpl .= "<label><input type=\"radio\" name=\"config[$key]\" value=\"1\" disabled=\"disabled\" class=\"radio\" /> " . $user->lang['YES'] . '</label>';
		$tpl .= "<label><input type=\"radio\" id=\"$key\" name=\"config[$key]\" value=\"0\" checked=\"checked\" disabled=\"disabled\"  class=\"radio\" /> " . $user->lang['NO'] . '</label>';

		return $tpl;
	}

	/**
	* Select sort method
	*/
	function sort_method_select($value, $key)
	{
		global $user;

		$sort_method_options = '';

		$sort_method_options .= '<option' . (($value == 't') ? ' selected="selected"' : '') . " value='t'>" . $user->lang['TIME'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'n') ? ' selected="selected"' : '') . " value='n'>" . $user->lang['IMAGE_NAME'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'vc') ? ' selected="selected"' : '') . " value='vc'>" . $user->lang['GALLERY_VIEWS'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'u') ? ' selected="selected"' : '') . " value='u'>" . $user->lang['USERNAME'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'ra') ? ' selected="selected"' : '') . " value='ra'>" . $user->lang['RATING'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'r') ? ' selected="selected"' : '') . " value='r'>" . $user->lang['RATES_COUNT'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'c') ? ' selected="selected"' : '') . " value='c'>" . $user->lang['COMMENTS'] . '</option>';
		$sort_method_options .= '<option' . (($value == 'lc') ? ' selected="selected"' : '') . " value='lc'>" . $user->lang['NEW_COMMENT'] . '</option>';

		return "<select name=\"config[$key]\" id=\"$key\">$sort_method_options</select>";
	}

	/**
	* Select sort order
	*/
	function sort_order_select($value, $key)
	{
		global $user;

		$sort_order_options = '';

		$sort_order_options .= '<option' . (($value == 'd') ? ' selected="selected"' : '') . " value='d'>" . $user->lang['SORT_DESCENDING'] . '</option>';
		$sort_order_options .= '<option' . (($value == 'a') ? ' selected="selected"' : '') . " value='a'>" . $user->lang['SORT_ASCENDING'] . '</option>';

		return "<select name=\"config[$key]\" id=\"$key\">$sort_order_options</select>";
	}

	/**
	* Radio Buttons for GD library
	*/
	function gd_radio($value, $key)
	{
		$key_gd1	= ($value == phpbb_gallery_constants::GDLIB1) ? ' checked="checked"' : '';
		$key_gd2	= ($value == phpbb_gallery_constants::GDLIB2) ? ' checked="checked"' : '';

		$tpl = '';

		$tpl .= "<label><input type=\"radio\" name=\"config[$key]\" value=\"" . phpbb_gallery_constants::GDLIB1 . "\" $key_gd1 class=\"radio\" /> GD1</label>";
		$tpl .= "<label><input type=\"radio\" id=\"$key\" name=\"config[$key]\" value=\"" . phpbb_gallery_constants::GDLIB2 . "\" $key_gd2  class=\"radio\" /> GD2</label>";

		return $tpl;
	}

	/**
	* Display watermark
	*/
	function watermark_source($value, $key)
	{
		global $user;

		return generate_board_url() . "<br /><input type=\"text\" name=\"config[$key]\" id=\"$key\" value=\"$value\" size =\"40\" maxlength=\"125\" /><br /><img src=\"" . generate_board_url() . "/$value\" alt=\"" . $user->lang['WATERMARK'] . "\" />";
	}

	/**
	* Display watermark
	*/
	function watermark_position($value, $key)
	{
		global $user;

		$x_position_options = $y_position_options = '';

		$x_position_options .= '<option' . (($value & phpbb_gallery_constants::WATERMARK_TOP) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_constants::WATERMARK_TOP . "'>" . $user->lang['WATERMARK_POSITION_TOP'] . '</option>';
		$x_position_options .= '<option' . (($value & phpbb_gallery_constants::WATERMARK_MIDDLE) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_constants::WATERMARK_MIDDLE . "'>" . $user->lang['WATERMARK_POSITION_MIDDLE'] . '</option>';
		$x_position_options .= '<option' . (($value & phpbb_gallery_constants::WATERMARK_BOTTOM) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_constants::WATERMARK_BOTTOM . "'>" . $user->lang['WATERMARK_POSITION_BOTTOM'] . '</option>';

		$y_position_options .= '<option' . (($value & phpbb_gallery_constants::WATERMARK_LEFT) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_constants::WATERMARK_LEFT . "'>" . $user->lang['WATERMARK_POSITION_LEFT'] . '</option>';
		$y_position_options .= '<option' . (($value & phpbb_gallery_constants::WATERMARK_CENTER) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_constants::WATERMARK_CENTER . "'>" . $user->lang['WATERMARK_POSITION_CENTER'] . '</option>';
		$y_position_options .= '<option' . (($value & phpbb_gallery_constants::WATERMARK_RIGHT) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_constants::WATERMARK_RIGHT . "'>" . $user->lang['WATERMARK_POSITION_RIGHT'] . '</option>';

		// Cheating is an evil-thing, but most times it's successful, that's why it is used.
		return "<input type='hidden' name='config[$key]' value='$value' /><select name='" . $key . "_x' id='" . $key . "_x'>$x_position_options</select><select name='" . $key . "_y' id='" . $key . "_y'>$y_position_options</select>";
	}

	/**
	* Select the link destination
	*/
	function uc_select($value, $key)
	{
		global $user;

		$sort_order_options = phpbb_gallery_plugins::uc_select($value, $key);


		if ($key != 'link_imagepage')
		{
			$sort_order_options .= '<option' . (($value == 'image_page') ? ' selected="selected"' : '') . " value='image_page'>" . $user->lang['UC_LINK_IMAGE_PAGE'] . '</option>';
		}
		else
		{
			$sort_order_options .= '<option' . (($value == 'next') ? ' selected="selected"' : '') . " value='next'>" . $user->lang['UC_LINK_NEXT'] . '</option>';
		}
		$sort_order_options .= '<option' . (($value == 'image') ? ' selected="selected"' : '') . " value='image'>" . $user->lang['UC_LINK_IMAGE'] . '</option>';
		$sort_order_options .= '<option' . (($value == 'none') ? ' selected="selected"' : '') . " value='none'>" . $user->lang['UC_LINK_NONE'] . '</option>';

		return "<select name='config[$key]' id='$key'>$sort_order_options</select>"
			. (($key == 'link_thumbnail') ? '<br /><input class="checkbox" type="checkbox" name="update_bbcode" id="update_bbcode" value="update_bbcode" /><label for="update_bbcode">' .  $user->lang['UPDATE_BBCODE'] . '</label>' : '');
	}

	/**
	* Select RRC-Config on gallery/index.php and in the profile
	*/
	function rrc_modes($value, $key)
	{
		global $user;

		$rrc_mode_options = '';

		$rrc_mode_options .= "<option value='" . phpbb_gallery_block::MODE_NONE . "'>" . $user->lang['RRC_MODE_NONE'] . '</option>';
		$rrc_mode_options .= '<option' . (($value & phpbb_gallery_block::MODE_RECENT) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::MODE_RECENT . "'>" . $user->lang['RRC_MODE_RECENT'] . '</option>';
		$rrc_mode_options .= '<option' . (($value & phpbb_gallery_block::MODE_RANDOM) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::MODE_RANDOM . "'>" . $user->lang['RRC_MODE_RANDOM'] . '</option>';
		if ($key != 'rrc_profile_mode')
		{
			$rrc_mode_options .= '<option' . (($value & phpbb_gallery_block::MODE_COMMENT) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::MODE_COMMENT . "'>" . $user->lang['RRC_MODE_COMMENTS'] . '</option>';
		}

		// Cheating is an evil-thing, but most times it's successful, that's why it is used.
		return "<input type='hidden' name='config[$key]' value='$value' /><select name='" . $key . "[]' multiple='multiple' id='$key'>$rrc_mode_options</select>";
	}

	/**
	* Select RRC display options
	*/
	function rrc_display($value, $key)
	{
		global $user;

		$rrc_display_options = '';

		$rrc_display_options .= "<option value='" . phpbb_gallery_block::DISPLAY_NONE . "'>" . $user->lang['RRC_DISPLAY_NONE'] . '</option>';
		$rrc_display_options .= '<option' . (($value & phpbb_gallery_block::DISPLAY_ALBUMNAME) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::DISPLAY_ALBUMNAME . "'>" . $user->lang['RRC_DISPLAY_ALBUMNAME'] . '</option>';
		$rrc_display_options .= '<option' . (($value & phpbb_gallery_block::DISPLAY_COMMENTS) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::DISPLAY_COMMENTS . "'>" . $user->lang['RRC_DISPLAY_COMMENTS'] . '</option>';
		$rrc_display_options .= '<option' . (($value & phpbb_gallery_block::DISPLAY_IMAGENAME) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::DISPLAY_IMAGENAME . "'>" . $user->lang['RRC_DISPLAY_IMAGENAME'] . '</option>';
		$rrc_display_options .= '<option' . (($value & phpbb_gallery_block::DISPLAY_IMAGETIME) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::DISPLAY_IMAGETIME . "'>" . $user->lang['RRC_DISPLAY_IMAGETIME'] . '</option>';
		$rrc_display_options .= '<option' . (($value & phpbb_gallery_block::DISPLAY_IMAGEVIEWS) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::DISPLAY_IMAGEVIEWS . "'>" . $user->lang['RRC_DISPLAY_IMAGEVIEWS'] . '</option>';
		$rrc_display_options .= '<option' . (($value & phpbb_gallery_block::DISPLAY_USERNAME) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::DISPLAY_USERNAME . "'>" . $user->lang['RRC_DISPLAY_USERNAME'] . '</option>';
		$rrc_display_options .= '<option' . (($value & phpbb_gallery_block::DISPLAY_RATINGS) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::DISPLAY_RATINGS . "'>" . $user->lang['RRC_DISPLAY_RATINGS'] . '</option>';
		$rrc_display_options .= '<option' . (($value & phpbb_gallery_block::DISPLAY_IP) ? ' selected="selected"' : '') . " value='" . phpbb_gallery_block::DISPLAY_IP . "'>" . $user->lang['RRC_DISPLAY_IP'] . '</option>';

		// Cheating is an evil-thing, but most times it's successful, that's why it is used.
		return "<input type='hidden' name='config[$key]' value='$value' /><select name='" . $key . "[]' multiple='multiple' id='$key'>$rrc_display_options</select>";
	}

	/**
	* BBCode-Template
	*/
	function bbcode_tpl($value)
	{
		$gallery_url = phpbb_gallery_url::path('full');

		if (($value == 'highslide') && in_array('highslide', phpbb_gallery_plugins::$plugins))
		{
			$bbcode_tpl = '<a class="highslide" onclick="return hs.expand(this)" href="' . $gallery_url . 'image.php?image_id={NUMBER}"><img src="' . $gallery_url . 'image.php?mode=thumbnail&amp;image_id={NUMBER}" alt="{NUMBER}" /></a>';
		}
		else if (($value == 'lytebox') && in_array('lytebox', phpbb_gallery_plugins::$plugins))
		{
			$bbcode_tpl = '<a class="image-resize" rel="lytebox" href="' . $gallery_url . 'image.php?image_id={NUMBER}"><img src="' . $gallery_url . 'image.php?mode=thumbnail&amp;image_id={NUMBER}" alt="{NUMBER}" /></a>';
		}
		else if ($value == 'image_page')
		{
			$bbcode_tpl = '<a href="' . $gallery_url . 'image_page.php?image_id={NUMBER}"><img src="' . $gallery_url . 'image.php?mode=thumbnail&amp;image_id={NUMBER}" alt="{NUMBER}" /></a>';
		}
		else
		{
			$bbcode_tpl = '<a href="' . $gallery_url . 'image.php?image_id={NUMBER}"><img src="' . $gallery_url . 'image.php?mode=thumbnail&amp;image_id={NUMBER}" alt="{NUMBER}" /></a>';
		}

		return $bbcode_tpl;
	}

	var $display_vars = [
		'title'	=> 'GALLERY_CONFIG',
		'vars'	=> [
			'legend1'				=> 'GALLERY_CONFIG',
			'allow_comments'		=> ['lang' => 'COMMENT_SYSTEM',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'comment_user_control'	=> ['lang' => 'COMMENT_USER_CONTROL',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => true],
			'comment_length'		=> ['lang' => 'COMMENT_MAX_LENGTH',	'validate' => 'int',	'type' => 'text:7:5',		'gallery' => true,	'explain' => false,	'append' => 'CHARACTERS'],
			'allow_rates'			=> ['lang' => 'RATE_SYSTEM',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'max_rating'			=> ['lang' => 'RATE_SCALE',			'validate' => 'int',	'type' => 'text:7:2',		'gallery' => true,	'explain' => false],
			'allow_hotlinking'		=> ['lang' => 'HOTLINK_PREVENT',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'hotlinking_domains'	=> ['lang' => 'HOTLINK_ALLOWED',		'validate' => 'string',	'type' => 'text:40:255',	'gallery' => true,	'explain' => true],
			'shortnames'			=> ['lang' => 'SHORTED_IMAGENAMES',	'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => true,	'append' => 'CHARACTERS'],

			'legend2'				=> 'ALBUM_SETTINGS',
			'album_rows'			=> ['lang' => 'ROWS_PER_PAGE',			'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],
			'album_columns'			=> ['lang' => 'COLS_PER_PAGE',			'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],
			'album_display'			=> ['lang' => 'RRC_DISPLAY_OPTIONS',	'validate' => 'int',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'rrc_display'],
			'default_sort_key'		=> ['lang' => 'DEFAULT_SORT_METHOD',	'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'sort_method_select'],
			'default_sort_dir'		=> ['lang' => 'DEFAULT_SORT_ORDER',	'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'sort_order_select'],
			'album_images'			=> ['lang' => 'MAX_IMAGES_PER_ALBUM',	'validate' => 'int',	'type' => 'text:7:7',		'gallery' => true,	'explain' => true],
			'mini_thumbnail_disp'	=> ['lang' => 'DISP_FAKE_THUMB',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'mini_thumbnail_size'	=> ['lang' => 'FAKE_THUMB_SIZE',		'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => true,	'append' => 'PIXELS'],

			'legend3'				=> 'SEARCH_SETTINGS',
			'search_display'		=> ['lang' => 'RRC_DISPLAY_OPTIONS',	'validate' => 'int',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'rrc_display'],

			'legend4'				=> 'IMAGE_SETTINGS',
			'num_uploads'			=> ['lang' => 'UPLOAD_IMAGES',			'validate' => 'int',	'type' => 'text:7:2',		'gallery' => true,	'explain' => false],
			'max_filesize'			=> ['lang' => 'MAX_FILE_SIZE',			'validate' => 'int',	'type' => 'text:12:9',		'gallery' => true,	'explain' => false,	'append' => 'BYTES'],
			'max_width'				=> ['lang' => 'MAX_WIDTH',				'validate' => 'int',	'type' => 'text:7:5',		'gallery' => true,	'explain' => false,	'append' => 'PIXELS'],
			'max_height'			=> ['lang' => 'MAX_HEIGHT',			'validate' => 'int',	'type' => 'text:7:5',		'gallery' => true,	'explain' => false,	'append' => 'PIXELS'],
			'allow_resize'			=> ['lang' => 'RESIZE_IMAGES',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'allow_rotate'			=> ['lang' => 'ROTATE_IMAGES',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'jpg_quality'			=> ['lang' => 'JPG_QUALITY',			'validate' => 'int',	'type' => 'text:7:5',		'gallery' => true,	'explain' => true],
			'medium_cache'			=> ['lang' => 'MEDIUM_CACHE',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'medium_width'			=> ['lang' => 'RSZ_WIDTH',				'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => false,	'append' => 'PIXELS'],
			'medium_height'			=> ['lang' => 'RSZ_HEIGHT',			'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => false,	'append' => 'PIXELS'],
			'allow_gif'				=> ['lang' => 'GIF_ALLOWED',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'allow_jpg'				=> ['lang' => 'JPG_ALLOWED',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'allow_png'				=> ['lang' => 'PNG_ALLOWED',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'allow_zip'				=> ['lang' => 'ZIP_ALLOWED',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'description_length'	=> ['lang' => 'IMAGE_DESC_MAX_LENGTH',	'validate' => 'int',	'type' => 'text:7:5',		'gallery' => true,	'explain' => false,	'append' => 'CHARACTERS'],
			'disp_nextprev_thumbnail'	=> ['lang' => 'DISP_NEXTPREV_THUMB','validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'disp_exifdata'			=> ['lang' => 'DISP_EXIF_DATA',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'disp_image_url'		=> ['lang' => 'VIEW_IMAGE_URL',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],

			'legend5'				=> 'THUMBNAIL_SETTINGS',
			'thumbnail_cache'		=> ['lang' => 'THUMBNAIL_CACHE',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'gdlib_version'			=> ['lang' => 'GD_VERSION',			'validate' => 'int',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'gd_radio'],
			'thumbnail_width'		=> ['lang' => 'THUMBNAIL_WIDTH',		'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false,	'append' => 'PIXELS'],
			'thumbnail_height'		=> ['lang' => 'THUMBNAIL_HEIGHT',		'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false,	'append' => 'PIXELS'],
			'thumbnail_quality'		=> ['lang' => 'THUMBNAIL_QUALITY',		'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => true,	'append' => 'PERCENT'],
			'thumbnail_infoline'	=> ['lang' => 'INFO_LINE',				'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],

			'legend6'				=> 'WATERMARK_OPTIONS',
			'watermark_enabled'		=> ['lang' => 'WATERMARK_IMAGES',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'watermark_source'		=> ['lang' => 'WATERMARK_SOURCE',		'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => true,	'method' => 'watermark_source'],
			'watermark_height'		=> ['lang' => 'WATERMARK_HEIGHT',		'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => true,	'append' => 'PIXELS'],
			'watermark_width'		=> ['lang' => 'WATERMARK_WIDTH',		'validate' => 'int',	'type' => 'text:7:4',		'gallery' => true,	'explain' => true,	'append' => 'PIXELS'],
			'watermark_position'	=> ['lang' => 'WATERMARK_POSITION',	'validate' => '',		'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'watermark_position'],

			'legend7'				=> 'UC_LINK_CONFIG',
			'link_thumbnail'		=> ['lang' => 'UC_THUMBNAIL',			'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => true,	'method' => 'uc_select'],
			'link_imagepage'		=> ['lang' => 'UC_IMAGEPAGE',			'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => true,	'method' => 'uc_select'],
			'link_image_name'		=> ['lang' => 'UC_IMAGE_NAME',			'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'uc_select'],
			'link_image_icon'		=> ['lang' => 'UC_IMAGE_ICON',			'validate' => 'string',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'uc_select'],

			'legend8'				=> 'RRC_GINDEX',
			'rrc_gindex_mode'		=> ['lang' => 'RRC_GINDEX_MODE',		'validate' => 'int',	'type' => 'custom',			'gallery' => true,	'explain' => true,	'method' => 'rrc_modes'],
			'rrc_gindex_rows'		=> ['lang' => 'RRC_GINDEX_ROWS',		'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],
			'rrc_gindex_columns'	=> ['lang' => 'RRC_GINDEX_COLUMNS',	'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],
			'rrc_gindex_comments'	=> ['lang' => 'RRC_GINDEX_COMMENTS',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'rrc_gindex_crows'		=> ['lang' => 'RRC_GINDEX_CROWS',		'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],
			'rrc_gindex_contests'	=> ['lang' => 'RRC_GINDEX_CONTESTS',	'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],
			'rrc_gindex_display'	=> ['lang' => 'RRC_DISPLAY_OPTIONS',	'validate' => '',		'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'rrc_display'],
			'rrc_gindex_pegas'		=> ['lang' => 'RRC_GINDEX_PGALLERIES',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],

			'legend9'				=> 'PHPBB_INTEGRATION',
			'disp_total_images'			=> ['lang' => 'DISP_TOTAL_IMAGES',				'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => false,	'explain' => false],
			'profile_user_images'		=> ['lang' => 'DISP_USER_IMAGES_PROFILE',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'profile_pega'				=> ['lang' => 'DISP_PERSONAL_ALBUM_PROFILE',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'rrc_profile_mode'			=> ['lang' => 'RRC_PROFILE_MODE',		'validate' => 'int',	'type' => 'custom',			'gallery' => true,	'explain' => true,	'method' => 'rrc_modes'],
			'rrc_profile_rows'			=> ['lang' => 'RRC_PROFILE_ROWS',		'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],
			'rrc_profile_columns'		=> ['lang' => 'RRC_PROFILE_COLUMNS',	'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],
			'rrc_profile_display'		=> ['lang' => 'RRC_DISPLAY_OPTIONS',	'validate' => 'int',	'type' => 'custom',			'gallery' => true,	'explain' => false,	'method' => 'rrc_display'],
			'rrc_profile_pegas'			=> ['lang' => 'RRC_GINDEX_PGALLERIES',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'viewtopic_icon'			=> ['lang' => 'DISP_VIEWTOPIC_ICON',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => false,	'explain' => false],
			'viewtopic_images'			=> ['lang' => 'DISP_VIEWTOPIC_IMAGES',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => false,	'explain' => false],
			'viewtopic_link'			=> ['lang' => 'DISP_VIEWTOPIC_LINK',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => false,	'explain' => false],

			'legend10'				=> 'INDEX_SETTINGS',
			'pegas_index_album'		=> ['lang' => 'PERSONAL_ALBUM_INDEX',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => true],
			'pegas_per_page'		=> ['lang' => 'PGALLERIES_PER_PAGE',	'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],
			'disp_login'			=> ['lang' => 'DISP_LOGIN',		'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => true],
			'disp_whoisonline'		=> ['lang' => 'DISP_WHOISONLINE',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'disp_birthdays'		=> ['lang' => 'DISP_BIRTHDAYS',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'disp_statistic'		=> ['lang' => 'DISP_STATISTIC',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],

			'legend11'				=> 'FEED_SETTINGS',
			'feed_enable'			=> ['lang' => 'FEED_ENABLED',			'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'feed_enable_pegas'		=> ['lang' => 'FEED_ENABLED_PEGAS',	'validate' => 'bool',	'type' => 'radio:yes_no',	'gallery' => true,	'explain' => false],
			'feed_limit'			=> ['lang' => 'FEED_LIMIT',			'validate' => 'int',	'type' => 'text:7:3',		'gallery' => true,	'explain' => false],

			'legend12'				=> '',
		],
	];
}
