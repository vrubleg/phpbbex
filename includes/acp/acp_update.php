<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_update
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('install');

		$this->tpl_name = 'acp_update';
		$this->page_title = 'ACP_VERSION_CHECK';

		// Get current and latest version
		$errstr = '';
		$errno = 0;

		$info = obtain_latest_version_info(request_var('versioncheck_force', false));

		if ($info === false)
		{
			trigger_error('VERSIONCHECK_FAIL', E_USER_WARNING);
		}

		$info = explode("\n", $info);
		$latest_version = trim($info[0]);

		$announcement_url = trim($info[1]);
		$announcement_url = (strpos($announcement_url, '&amp;') === false) ? str_replace('&', '&amp;', $announcement_url) : $announcement_url;

		$template->assign_vars(array(
			'S_UP_TO_DATE'		=> phpbb_version_compare($latest_version, $config['phpbbex_version'], '<='),
			'S_VERSION_CHECK'	=> true,
			'U_ACTION'			=> $this->u_action,
			'U_VERSIONCHECK_FORCE' => append_sid($this->u_action . '&amp;versioncheck_force=1'),

			'LATEST_VERSION'		=> $latest_version,
			'CURRENT_PHPBB_VERSION'	=> $config['version'],
			'CURRENT_VERSION'		=> $config['phpbbex_version'],

			'UPDATE_INSTRUCTIONS'	=> $user->lang('UPGRADE_INSTRUCTIONS', $latest_version, $announcement_url),
		));
	}
}

?>