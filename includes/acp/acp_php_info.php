<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_php_info
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $config;

		if ($mode != 'info')
		{
			trigger_error('NO_MODE', E_USER_ERROR);
		}

		$this->tpl_name = 'acp_php_info';
		$this->page_title = 'ACP_PHP_INFO';

		ob_start();
		phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_VARIABLES);
		$phpinfo = ob_get_clean();

		$phpinfo = trim($phpinfo);

		// Here we play around a little with the PHP Info HTML to try and stylise
		// it along phpBB's lines ... hopefully without breaking anything. The idea
		// for this was nabbed from the PHP annotated manual
		preg_match_all('#<body[^>]*>(.*)</body>#si', $phpinfo, $output);

		if (empty($phpinfo) || empty($output[1][0]))
		{
			trigger_error('NO_PHPINFO_AVAILABLE', E_USER_WARNING);
		}

		$output = $output[1][0];

		// expose_php can make the image not exist
		if (preg_match('#<a[^>]*><img[^>]*></a>#', $output))
		{
			$output = preg_replace('#<tr class="v"><td>(.*?<a[^>]*><img[^>]*></a>)(.*?)</td></tr>#s', '<tr class="row1"><td><table class="type2"><tr><td>\2</td><td>\1</td></tr></table></td></tr>', $output);
		}
		else
		{
			$output = preg_replace('#<tr class="v"><td>(.*?)</td></tr>#s', '<tr class="row1"><td><table class="type2"><tr><td>\1</td></tr></table></td></tr>', $output);
		}
		$output = preg_replace('#<table[^>]+>#i', '<table>', $output);
		$output = preg_replace('#<img border="0"#i', '<img', $output);
		$output = str_replace(array('class="e"', 'class="v"', 'class="h"', '<hr />', '<font', '</font>'), array('class="row1"', 'class="row2"', '', '', '<span', '</span>'), $output);

		// Fix invalid anchor names (eg "module_Zend Optimizer")
		$output = preg_replace_callback('#<a name="([^"]+)">#', array($this, 'remove_spaces'), $output);

		if (empty($output))
		{
			trigger_error('NO_PHPINFO_AVAILABLE', E_USER_WARNING);
		}

		$orig_output = $output;

		preg_match_all('#<div class="center">(.*)</div>#siU', $output, $output);
		$output = (!empty($output[1][0])) ? $output[1][0] : $orig_output;

		$template->assign_var('PHPINFO', $output);
	}

	function remove_spaces($matches)
	{
		return '<a name="' . str_replace(' ', '_', $matches[1]) . '">';
	}
}
