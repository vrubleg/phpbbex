<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

$acm_type = defined('ACM_TYPE') ? ACM_TYPE : ((extension_loaded('apcu') && apcu_enabled()) ? 'apcu' : 'file');
require_once(PHPBB_ROOT_PATH . 'includes/acm/acm_' . $acm_type . '.php');
