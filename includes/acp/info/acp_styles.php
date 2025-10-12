<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

class acp_styles_info
{
	function module()
	{
		return [
			'filename'	=> 'acp_styles',
			'title'		=> 'ACP_CAT_STYLES',
			'version'	=> '1.0.0',
			'modes'		=> [
				'style'		=> ['title' => 'ACP_STYLES', 'auth' => 'acl_a_styles', 'cat' => ['ACP_STYLE_MANAGEMENT']],
				'template'	=> ['title' => 'ACP_TEMPLATES', 'auth' => 'acl_a_styles', 'cat' => ['ACP_STYLE_COMPONENTS']],
				'theme'		=> ['title' => 'ACP_THEMES', 'auth' => 'acl_a_styles', 'cat' => ['ACP_STYLE_COMPONENTS']],
				'imageset'	=> ['title' => 'ACP_IMAGESETS', 'auth' => 'acl_a_styles', 'cat' => ['ACP_STYLE_COMPONENTS']],
			],
		];
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
