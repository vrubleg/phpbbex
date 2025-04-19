<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './'); }
require_once(PHPBB_ROOT_PATH . 'common.php');

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$mode = request_var('mode', '');

// Load the appropriate faq file
switch ($mode)
{
	case 'bbcode':
		$l_title = $user->lang['BBCODE_GUIDE'];
		$user->add_lang('bbcode', false, true);
	break;

	case 'rules':
		$l_title = $user->lang['BOARD_RULES'];
		$user->add_lang('rules', false, true);
	break;

	default:
		$l_title = $user->lang['FAQ_EXPLAIN'];
		$user->add_lang('faq', false, true);
	break;
}

// Pull the array data from the lang pack
$switch_column = $found_switch = false;
$help_blocks = array();
foreach ($user->help as $help_ary)
{
	if ($help_ary[0] == '--')
	{
		if ($help_ary[1] == '--')
		{
			$switch_column = true;
			$found_switch = true;
			continue;
		}

		$template->assign_block_vars('faq_block', array(
			'BLOCK_TITLE'		=> $help_ary[1],
			'SWITCH_COLUMN'		=> $switch_column,
		));

		if ($switch_column)
		{
			$switch_column = false;
		}
		continue;
	}

	$template->assign_block_vars('faq_block.faq_row', array(
		'FAQ_QUESTION'		=> $help_ary[0],
		'FAQ_ANSWER'		=> $help_ary[1])
	);
}

// Lets build a page ...
$template->assign_vars(array(
	'L_FAQ_TITLE'				=> $l_title,
	'L_BACK_TO_TOP'				=> $user->lang['BACK_TO_TOP'],

	'SWITCH_COLUMN_MANUALLY'	=> (!$found_switch) ? true : false,
));

page_header($l_title, false);

$template->set_filenames(array(
	'body' => 'faq_body.html')
);
make_jumpbox(append_sid(PHPBB_ROOT_PATH . 'viewforum.php'));

page_footer();
