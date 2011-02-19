<?php
/**
*
* @package acp
* @version $Id: acp_quick_reply.php,v 1.00 2007/07/17 13:57:02 rxu Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* @todo add cron intervals to server settings? (database_gc, queue_interval, session_gc, search_gc, cache_gc, warnings_gc)
*/

/**
* @package acp
*/
class acp_quick_reply
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$quick_reply_options = array('allow_reply_icons'=>1, 'allow_reply_checkboxes'=>2, 'allow_reply_attachbox'=>3, 'allow_reply_smilies'=>4);
		$quick_post_options = array('allow_post_icons'=>1, 'allow_post_checkboxes'=>2, 'allow_post_attachbox'=>3, 'allow_post_smilies'=>4);
				
		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_board';
		add_form_key($form_key);

		if ($mode != 'quick_reply')
		{
			return;
		}
	
		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
				$display_vars = array(
					'title'	=> 'ACP_QUICK_REPLY',
					'vars'	=> array(
						'allow_quick_reply'		=> array('lang' => 'ALLOW_QUICK_REPLY',		'validate' => 'int',	'type' => 'select', 'method' => 'allow_quick_reply', 'explain' => true),
						'allow_reply_icons'		=> array('lang' => 'ALLOW_REPLY_ICONS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_reply_checkboxes'=> array('lang' => 'ALLOW_REPLY_CHECKBOXES','validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_reply_attachbox'	=> array('lang' => 'ALLOW_REPLY_ATTACHBOX',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_reply_smilies'	=> array('lang' => 'ALLOW_REPLY_SMILIES',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'allow_quick_post'		=> array('lang' => 'ALLOW_QUICK_POST',		'validate' => 'int',	'type' => 'select', 'method' => 'allow_quick_post', 'explain' => true),
						'allow_post_icons'		=> array('lang' => 'ALLOW_REPLY_ICONS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_post_checkboxes'	=> array('lang' => 'ALLOW_REPLY_CHECKBOXES','validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_post_attachbox'	=> array('lang' => 'ALLOW_REPLY_ATTACHBOX',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_post_smilies'	=> array('lang' => 'ALLOW_REPLY_SMILIES',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false)

					),
				);

		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}
		
		foreach($quick_reply_options as $key => $value)
		{
			$config[$key] = ($config['allow_quick_reply_options'] & 1 << $value) ? 1 : 0;
		}
		foreach($quick_post_options as $key => $value)
		{
			$config[$key] = ($config['allow_quick_post_options'] & 1 << $value) ? 1 : 0;
		}
		
		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}
			
			$this->new_config[$config_name] = $cfg_array[$config_name];
			
		}

		if ($submit)
		{
			foreach($quick_reply_options as $key=>$value)
			{
					if ($this->new_config[$key] && !($this->new_config['allow_quick_reply_options'] & 1 << $value))
					{
						$this->new_config['allow_quick_reply_options'] += 1 << $value;
					}
					else if(!$this->new_config[$key] && ($this->new_config['allow_quick_reply_options'] & 1 << $value))
					{
						$this->new_config['allow_quick_reply_options'] -= 1 << $value;
					}
			}		
			foreach($quick_post_options as $key=>$value)
			{
					if ($this->new_config[$key] && !($this->new_config['allow_quick_post_options'] & 1 << $value))
					{
						$this->new_config['allow_quick_post_options'] += 1 << $value;
					}
					else if(!$this->new_config[$key] && ($this->new_config['allow_quick_post_options'] & 1 << $value))
					{
						$this->new_config['allow_quick_post_options'] -= 1 << $value;
					}
			}			
		
			add_log('admin', 'LOG_CONFIG_' . strtoupper($mode));
			
			set_config('allow_quick_reply', $this->new_config['allow_quick_reply']);
			set_config('allow_quick_reply_options', $this->new_config['allow_quick_reply_options']);
			set_config('allow_quick_post', $this->new_config['allow_quick_post']);
			set_config('allow_quick_post_options', $this->new_config['allow_quick_post_options']);

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->tpl_name = 'acp_quick_reply';
		$this->page_title = $display_vars['title'];
		
		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars),
				)
			);
		
			unset($display_vars['vars'][$config_key]);
		}

	}

	/**
	* Quick reply
	*/
	function allow_quick_reply($value, $key = '')
	{
		global $user;
		
		$options_ary = array(0 => 'ALLOW_QUICK_REPLY_NONE', 1 => 'ALLOW_QUICK_REPLY_REG', 2 => 'ALLOW_QUICK_REPLY_ALL');

		$allow_quick_reply_options = '';
		foreach ($options_ary as $key_value=>$option)
		{
			$selected = ($value == $key_value) ? ' selected="selected"' : '';
			$allow_quick_reply_options .= '<option value="' . $key_value . '"' . $selected . '>' . $user->lang[$option] . '</option>';
		}


		return $allow_quick_reply_options;
	}
	
	/**
	* Quick post
	*/
	function allow_quick_post($value, $key = '')
	{
		global $user;
		
		$options_ary = array(0 => 'ALLOW_QUICK_REPLY_NONE', 1 => 'ALLOW_QUICK_REPLY_REG', 2 => 'ALLOW_QUICK_REPLY_ALL');

		$allow_quick_post_options = '';
		foreach ($options_ary as $key_value=>$option)
		{
			$selected = ($value == $key_value) ? ' selected="selected"' : '';
			$allow_quick_post_options .= '<option value="' . $key_value . '"' . $selected . '>' . $user->lang[$option] . '</option>';
		}


		return $allow_quick_post_options;
	}

	
}

?>