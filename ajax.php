<?php
define('IN_PHPBB', true);
if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './'); }
require_once(PHPBB_ROOT_PATH . 'common.php');

// Start session management
$user->session_begin();
$auth->acl($user->data);

core::init();
core::run();
