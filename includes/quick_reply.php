<?php
/** 
*
* @package phpBB3
* @version $Id: quick_reply.php,v 1.6.4 2007/12/22 01:10:26 rxu Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* Minimum Requirement: PHP 4.3.3
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

$s_quick_reply = false;
$mode = (isset($topic_id)) ? 'reply' : 'post';

$s_quick_reply_userprefs = ($user->optionget('viewquick' . $mode)) ? true : false;
$s_quick_reply_guests = ($user->data['user_id'] == ANONYMOUS && $config['allow_quick_' . $mode] == 2) ? true : false;
$s_quick_reply_display = ($user->data['user_id'] == ANONYMOUS) ? $s_quick_reply_guests : $s_quick_reply_userprefs;

if ($config['allow_quick_' . $mode] && $s_quick_reply_display)
{
	$main_data = array();
	$main_data = ($mode == 'reply') ? $topic_data : $forum_data;
	
	if ($auth->acl_get('f_' . $mode, $forum_id))
	{
		$s_quick_reply = true;
	}

	if ($main_data['forum_type'] != FORUM_POST)
	{
		$s_quick_reply = false;
	}

	if (($main_data['forum_status'] == ITEM_LOCKED || (isset($main_data['topic_status']) && $main_data['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $forum_id))
	{
		$s_quick_reply = false;
	}
}

if (!$s_quick_reply)
{
	return false;
}

include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
$user->add_lang(array('posting', 'mcp'));

// Set some default variables
$uninit = array('post_attachment' => 0, 'poster_id' => $user->data['user_id'], 'enable_magic_url' => 0, 'topic_status' => 0, 'topic_type' => POST_NORMAL, 'post_subject' => '', 'topic_title' => '', 'post_time' => 0, 'post_edit_reason' => '', 'notify_set' => 0);
foreach ($uninit as $var_name => $default_value)
{
	if (!isset($main_data[$var_name]))
	{
		$main_data[$var_name] = $default_value;
	}
}
unset($uninit);

$options = array('allow_' . $mode . '_icons' => 1, 'allow_' . $mode . '_checkboxes' => 2, 'allow_' . $mode . '_attachbox' => 3, 'allow_' . $mode . '_smilies' => 4);
foreach ($options as $key => $value)
{
	$config[$key] = ($config['allow_quick_' . $mode . '_options'] & 1 << $value) ? 1 : 0;

}

$bbcode_status	= ($config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
$smilies_status	= ($bbcode_status && $config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
$img_status		= ($bbcode_status && $auth->acl_get('f_img', $forum_id)) ? true : false;
$url_status		= ($config['allow_post_links']) ? true : false;
$flash_status	= ($bbcode_status && $auth->acl_get('f_flash', $forum_id) && $config['allow_post_flash']) ? true : false;
$quote_status	= ($auth->acl_get('f_' . $mode, $forum_id)) ? true : false;

if ($config['allow_' . $mode . '_smilies'])
{
	generate_smilies('inline', $forum_id);
}

$s_topic_icons = false;
if ($main_data['enable_icons'] && $auth->acl_get('f_icons', $forum_id) && $config['allow_' . $mode . '_icons'])
{
	$s_topic_icons = posting_gen_topic_icons($mode, ($mode == 'reply') ? $main_data['icon_id'] : '');
}

$bbcode_checked		= ($config['allow_bbcode']) ? !$user->optionget('bbcode') : 1;
$smilies_checked	= ($config['allow_smilies']) ? !$user->optionget('smilies') : 1;
$urls_checked		= false;
$sig_checked		= ($config['allow_sig'] && $user->optionget('attachsig')) ? true: false;
$lock_topic_checked	= (isset($main_data['topic_status']) && $main_data['topic_status'] == ITEM_LOCKED) ? 1 : 0;

// Check if user is watching this topic
if ($mode != 'post' && $config['allow_topic_notify'] && $user->data['is_registered'])
{
	$sql = 'SELECT topic_id
		FROM ' . TOPICS_WATCH_TABLE . '
		WHERE topic_id = ' . $topic_id . '
			AND user_id = ' . $user->data['user_id'];
	$result = $db->sql_query($sql);
	$main_data['notify_set'] = (int) $db->sql_fetchfield('topic_id');
	$db->sql_freeresult($result);
}

// If the user is replying or posting and not already watching this topic but set to always being notified we need to overwrite this setting
$notify_set			= ($config['allow_topic_notify'] && $user->data['is_registered'] && !$main_data['notify_set']) ? $user->data['user_notify'] : $main_data['notify_set'];
$notify_checked		= ($mode == 'post') ? $user->data['user_notify'] : $notify_set;

// Action URL, include session_id for security purpose
$s_action = append_sid("{$phpbb_root_path}posting.$phpEx", "mode=$mode&amp;f=$forum_id", true, $user->session_id);
$s_action .= (isset($topic_id) && $topic_id) ? "&amp;t=$topic_id" : '';

// Visual Confirmation
if ($config['enable_post_confirm'] && !$user->data['is_registered'])
{
	include($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
	$captcha =& phpbb_captcha_factory::get_instance($config['captcha_plugin']);
	$captcha->init(CONFIRM_POST);
}

// Posting uses is_solved for legacy reasons. Plugins have to use is_solved to force themselves to be displayed.
if ($config['enable_post_confirm'] && !$user->data['is_registered'] && (isset($captcha) && $captcha->is_solved() === false) && ($mode == 'post' || $mode == 'reply'))
{
	$template->assign_vars(array(
		'S_CONFIRM_CODE'			=> true,
		'CAPTCHA_TEMPLATE'			=> $captcha->get_template(),
	));
}

$qr_hidden_fields = array(
	'topic_cur_post_id'		=> (isset($main_data['topic_last_post_id'])) ? (int) $main_data['topic_last_post_id'] : 0,
	'lastclick'				=> (int) time()
);

// Attachment entry
$show_attach_box = (@ini_get('file_uploads') != '0' && strtolower(@ini_get('file_uploads')) != 'off' && $auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach') && $config['allow_attachments'] && $config['allow_' . $mode . '_attachbox']);

add_form_key('posting');

// Send vars to template
$template->assign_vars(array(
	'S_QUICK_REPLY'			=> $s_quick_reply,
	'QR_HIDDEN_FIELDS'		=> build_hidden_fields($qr_hidden_fields),
	'U_QR_ACTION'			=> $s_action,
	'S_FORM_ENCTYPE'		=> ($show_attach_box) ? ' enctype="multipart/form-data"' : '',
	'SUBJECT'				=> '',
	'EXTRA_OPTIONS_DISPLAY'	=> ($config['allow_' . $mode . '_checkboxes']),
	
	'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
	'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
	'IMG_STATUS'			=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
	'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
	'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
	'URL_STATUS'			=> ($bbcode_status && $url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],
	
	'L_QUICK_REPLY'				=> $user->lang['QUICK_' . strtoupper($mode)],
	'L_ICON'					=> ($mode == 'reply') ? $user->lang['POST_ICON'] : $user->lang['TOPIC_ICON'],
	'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',
	
	'S_DISPLAY_USERNAME'		=> (!$user->data['is_registered']) ? true : false,	
	'S_SHOW_TOPIC_ICONS'		=> $s_topic_icons,
	'S_BBCODE_ALLOWED'			=> $bbcode_status,
	'S_BBCODE_CHECKED'			=> ($bbcode_checked) ? ' checked="checked"' : '',
	'S_SMILIES_ALLOWED'			=> ($smilies_status && $config['allow_' . $mode . '_smilies']) ? true : false,
	'S_SMILIES_CHECKED'			=> ($smilies_checked) ? ' checked="checked"' : '',
	'S_SIG_ALLOWED'				=> ($auth->acl_get('f_sigs', $forum_id) && $config['allow_sig'] && $user->data['is_registered']) ? true : false,
	'S_SIGNATURE_CHECKED'		=> ($sig_checked) ? ' checked="checked"' : '',
	'S_NOTIFY_ALLOWED'			=> (!$user->data['is_registered'] || !$config['allow_topic_notify'] || !$config['email_enable']) ? false : true,
	'S_NOTIFY_CHECKED'			=> ($notify_checked) ? ' checked="checked"' : '',
	'S_LOCK_TOPIC_ALLOWED'		=> (($mode == 'reply') && ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && !empty($main_data['topic_poster']) && $user->data['user_id'] == $main_data['topic_poster'] && $main_data['topic_status'] == ITEM_UNLOCKED))) ? true : false,
	'S_LOCK_TOPIC_CHECKED'		=> ($lock_topic_checked) ? ' checked="checked"' : '',
	'S_LINKS_ALLOWED'			=> $url_status,
	'S_MAGIC_URL_CHECKED'		=> ($urls_checked) ? ' checked="checked"' : '',
	'S_FIRST_POST_SHOW_ALLOWED'	=> ($mode == 'post'),
	'S_NEW_MESSAGE'				=> ($mode == 'post'),
	
	'S_BBCODE_IMG'			=> $img_status,
	'S_BBCODE_URL'			=> $url_status,
	'S_BBCODE_FLASH'		=> $flash_status,
	'S_BBCODE_QUOTE'		=> $quote_status,
	'S_SHOW_ATTACH_BOX'		=> $show_attach_box,
	)

);

// Build custom bbcodes array
display_custom_bbcodes();

return true;
?>