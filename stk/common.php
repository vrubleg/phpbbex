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

// Include all common stuff
require_once(STK_ROOT_PATH . 'includes/functions.php');
require_once(PHPBB_ROOT_PATH . 'common.php');
require_once(STK_ROOT_PATH . 'includes/plugin.php');
require_once(PHPBB_ROOT_PATH . 'includes/umil.php');

$user->session_begin();
$auth->acl($user->data);
$user->setup('acp/common', $config['default_style']);

$umil = new phpbb_umil();

// Setup some common variables
$action = request_var('action', '');
$submit = request_var('submit', false);
