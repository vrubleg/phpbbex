<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
include($phpbb_root_path . 'common.php');

// Start session management
$user->session_begin();
$auth->acl($user->data);

core::init();
core::run();
