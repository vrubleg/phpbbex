<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
define('ADMIN_START', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
require_once($phpbb_root_path . 'common.php');

// Start session management
$user->session_begin(false);
$auth->acl($user->data);
$user->setup();

// Set custom template for admin area
$template->set_custom_template($phpbb_root_path . 'adm/style', 'admin');

$template->set_filenames(array(
	'body' => 'colour_swatch.html')
);

$form = request_var('form', '');
$name = request_var('name', '');

// We validate form and name here, only id/class allowed
$form = (!preg_match('/^[a-z0-9_-]+$/i', $form)) ? '' : $form;
$name = (!preg_match('/^[a-z0-9_-]+$/i', $name)) ? '' : $name;

$template->assign_vars(array(
	'OPENER'		=> $form,
	'NAME'			=> $name,
	'T_IMAGES_PATH'	=> "{$phpbb_root_path}images/",

	'S_USER_LANG'			=> $user->lang['USER_LANG'],
	'S_CONTENT_DIRECTION'	=> $user->lang['DIRECTION'],
	'S_CONTENT_ENCODING'	=> 'UTF-8',
));

$template->display('body');

garbage_collection();
