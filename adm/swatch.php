<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
define('ADMIN_START', true);
if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './../'); }
require_once(PHPBB_ROOT_PATH . 'common.php');

// Start session management
$user->session_begin(false);
$auth->acl($user->data);
$user->setup();

// Set custom template for admin area
$template->set_custom_template(PHPBB_ROOT_PATH . 'adm/style', 'admin');

$template->set_filenames([
	'body' => 'colour_swatch.html'
]);

$form = request_var('form', '');
$name = request_var('name', '');

// We validate form and name here, only id/class allowed
$form = (!preg_match('/^[a-z0-9_-]+$/i', $form)) ? '' : $form;
$name = (!preg_match('/^[a-z0-9_-]+$/i', $name)) ? '' : $name;

$template->assign_vars([
	'OPENER'		=> $form,
	'NAME'			=> $name,
	'T_IMAGES_PATH'	=> PHPBB_ROOT_PATH . 'images/',

	'S_USER_LANG'			=> $user->lang['USER_LANG'],
]);

$template->display('body');

garbage_collection();
