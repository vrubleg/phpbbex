<?php
define('IN_PHPBB', true);
if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './'); }
$phpbb_root_path = PHPBB_ROOT_PATH;
require_once($phpbb_root_path . 'common.php');

// Start session management
$user->session_begin();
$auth->acl($user->data);

core::init();
core::run();
