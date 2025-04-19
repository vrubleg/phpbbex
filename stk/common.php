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

// Prepare some vars
$stk_no_error = false;
define('PHPBB_MSG_HANDLER', 'stk_msg_handler');

// Include all common stuff
require_once(STK_ROOT_PATH . 'includes/functions.php');
require_once(PHPBB_ROOT_PATH . 'common.php');
require_once(STK_ROOT_PATH . 'includes/plugin.php');
require_once(PHPBB_ROOT_PATH . 'includes/umil.php');

// When not in the ERK we setup the user at this point and load UML.
if (!defined('IN_ERK'))
{
	require_once(STK_ROOT_PATH . 'includes/critical_repair.php');
	$critical_repair = new critical_repair();

	$user->session_begin();
	$auth->acl($user->data);
	$user->setup('acp/common', $config['default_style']);

	$umil = new phpbb_umil();
}

// Setup some common variables
$action = request_var('action', '');
$submit = request_var('submit', false);
