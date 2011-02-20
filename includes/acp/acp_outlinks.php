<?php
if (!defined('IN_PHPBB')) exit;

class acp_outlinks
{		
	//
	// Загружает таблицу ссылок. Поля: id, title, url
	function LoadOutLinks()
	{
		global $config;
		$outlinks = ($config['outlinks']) ? $config['outlinks'] : '';
		if($outlinks == "") $outlinks = array(); 
		else
		{
			// Строки разделены \n, столбцы разделены \t
			$outlinks = explode("\n", $outlinks);
			for($i=0; $i<count($outlinks); $i++) $outlinks[$i] = explode("\t", $outlinks[$i]);
		}
		return $outlinks;
	}
	
	//
	// Сохраняет таблицу ссылок
	function SaveOutLinks($outlinks)
	{
		for($i=0; $i<count($outlinks); $i++) $outlinks[$i] = implode("\t", $outlinks[$i]);
		$outlinks = implode("\n", $outlinks);
		set_config('outlinks', $outlinks);
	}
	
	//
	// Главный обработчик модуля
	var $u_action;
	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		// Set up general vars
		$action = request_var('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;
		$action = (isset($_POST['save'])) ? 'save' : $action;
		$s_hidden_fields = '';
		$outlinks = $this->LoadOutLinks();
		
		// Настройки страницы
		$this->tpl_name = 'acp_outlinks';
		$this->page_title = 'ACP_OUTLINKS';
		$form_name = 'acp_outlinks';
		add_form_key($form_name);

		switch ($action)
		{
			case 'edit':
				$link_id = request_var('id', 0);
				for($i=0; $i<count($outlinks); $i++) if($outlinks[$i][0] == $link_id) break;
				if($i==count($outlinks)) trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				$link_info = array('title' => $outlinks[$i][1], 'url' => $outlinks[$i][2]);
				$s_hidden_fields .= '<input type="hidden" name="id" value="' . $link_id . '" />';

			case 'add':
				$template->assign_vars(array(
					'S_EDIT_LINK'		=> true,
					'U_ACTION'			=> $this->u_action,
					'U_BACK'			=> $this->u_action,
					'LINK_TITLE'		=> (isset($link_info['title'])) ? $link_info['title'] : '',
					'LINK_URL'			=> (isset($link_info['url'])) ? $link_info['url'] : '',
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields
					)
				);
				return;

			case 'save':
				$link_id		= request_var('id', 0);
				$title			= utf8_normalize_nfc(request_var('title', '', true));
				$title 			= str_replace(array("\n", "\t"), "", $title);
				$url			= request_var('link', '');
				$url 			= str_replace(array("\n", "\t"), "", $url);
				if (!check_form_key($form_name) || $title=="") trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				$max_id = 0;
				$i = 0;
				for($i; $i<count($outlinks); $i++) 
				{
					if($outlinks[$i][0] > $max_id) $max_id = $outlinks[$i][0];
					if($outlinks[$i][0] == $link_id) break;
				}
				$newlink = ($i==count($outlinks));
				if (!$newlink)
				{
					// Обновляем инфу
					$outlinks[$i][1] = $title;
					$outlinks[$i][2] = $url;
				}
				else
				{
					// Добавляем новую позицию
					$outlinks[] = array(++$max_id, $title, $url);
				}
				$this->SaveOutLinks($outlinks);
				$message = ($newlink) ? $user->lang['LINK_ADDED'] : $user->lang['LINK_UPDATED'];
				trigger_error($message . adm_back_link($this->u_action));
				break;

			case 'delete':
				$link_id		= request_var('id', 0);
				for($i = 0; $i<count($outlinks); $i++) if($outlinks[$i][0] == $link_id) break;
				if($i==count($outlinks)) trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				if (confirm_box(true))
				{
					for($i; $i<(count($outlinks)-1); $i++) $outlinks[$i] = $outlinks[$i+1];
					unset($outlinks[count($outlinks)-1]);
					$this->SaveOutLinks($outlinks);
					trigger_error($user->lang['LINK_REMOVED'] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'id'		=> $link_id,
						'action'	=> 'delete',
					)));
				}
				break;
			
			case 'move_up':
			case 'move_down':
				$link_id		= request_var('id', 0);
				for($i = 0; $i<count($outlinks); $i++) if($outlinks[$i][0] == $link_id) break;
				if($i==count($outlinks)) trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				if($action == 'move_up')
				{
					if($i == 0) break;
					$tmp = $outlinks[$i-1];
					$outlinks[$i-1] = $outlinks[$i];
					$outlinks[$i] = $tmp;					
				}
				else
				{
					if($i==(count($outlinks)-1)) break;
					$tmp = $outlinks[$i+1];
					$outlinks[$i+1] = $outlinks[$i];
					$outlinks[$i] = $tmp;	
				}
				$this->SaveOutLinks($outlinks);
				break;
		}

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields)
		);

		foreach($outlinks as $row)
		{
			$template->assign_block_vars('items', array(
				'TITLE'			=> $row[1],
				'LINK'			=> $row[2],
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $row[0],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $row[0],
				'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;id=' . $row[0],
				'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;id=' . $row[0]
				)
			);
		}
		$db->sql_freeresult($result);
	}
}

?>