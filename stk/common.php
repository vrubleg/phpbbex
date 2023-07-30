<?php
/**
* @package phpBBex Support Toolkit
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// What version are we using?
define('STK_VERSION', '1.0.7-PL3');

define('ADMIN_START', true);

// This seems like a rather nasty thing to do, but the only places this IN_LOGIN is checked is in session.php when creating a session
// Reason for having it is that it allows us in the STK if we can not login and the board is disabled.
define('IN_LOGIN', true);

// Make that phpBB itself understands out paths
$phpbb_root_path = PHPBB_ROOT_PATH;
$phpEx = PHP_EXT;

// Prepare some vars
$stk_no_error = false;
define('PHPBB_MSG_HANDLER', 'stk_msg_handler');

// Include all common stuff
require(STK_ROOT_PATH . 'includes/functions.' . PHP_EXT);
require(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);
require(STK_ROOT_PATH . 'includes/plugin.' . PHP_EXT);
require PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT;

// When not in the ERK we setup the user at this point
// and load UML.
if (!defined('IN_ERK'))
{
	include STK_ROOT_PATH . 'includes/critical_repair.' . PHP_EXT;
	$critical_repair = new critical_repair();

	$user->session_begin();
	$auth->acl($user->data);
	$user->setup('acp/common', $config['default_style']);

	$umil = new umil(true);
}

// Load STK config when not in the erk
if (!isset($stk_config))
{
	$stk_config = array();
	include STK_ROOT_PATH . 'config.' . PHP_EXT;
}

// Setup some common variables
$action = request_var('action', '');
$submit = request_var('submit', false);
