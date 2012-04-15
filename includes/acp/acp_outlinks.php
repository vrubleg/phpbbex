<?php
if (!defined('IN_PHPBB')) exit;

class acp_outlinks
{
	function load_out_links()
	{
		global $config;
		if (empty($config['outlinks'])) return array();

		// Rows separated by \n, columns separated by \t
		$outlinks = explode("\n", $config['outlinks']);
		foreach ($outlinks as &$outlink)
		{
			$row = explode("\t", $outlink);
			if (is_numeric($row[0]))
			{
				// Legacy format: id, title, url
				$outlink = array(
					'title'		=> !empty($row[1]) ? $row[1] : '',
					'url'		=> !empty($row[2]) ? $row[2] : '',
					'nofollow'	=> 0,
					'newwindow'	=> 0,
				);
			}
			else
			{
				// New format: title, url, flags
				$outlink = array(
					'title'		=> !empty($row[0]) ? $row[0] : '',
					'url'		=> !empty($row[1]) ? $row[1] : '',
					'nofollow'	=> !empty($row[2]) && (intval($row[2]) & 0x1),
					'newwindow'	=> !empty($row[2]) && (intval($row[2]) & 0x2),
				);
			}
		}
		return $outlinks;
	}

	function save_out_links($outlinks)
	{
		foreach ($outlinks as &$outlink)
		{
			$flags = ($outlink['nofollow'] ? 0x1 : 0) + ($outlink['newwindow'] ? 0x2 : 0);
			$outlink = trim($outlink['title']) . "\t" . trim($outlink['url']) . ($flags ? ("\t" . $flags) : '');
		}
		$outlinks = implode("\n", $outlinks);
		set_config('outlinks', $outlinks);
	}

	var $u_action;
	function main($acp_id, $acp_mode)
	{
		global $db, $user, $auth, $template, $cache;

		// Set up general vars
		$action = request_var('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;
		$action = (isset($_POST['save'])) ? 'save' : $action;
		$s_hidden_fields = '';
		$outlinks = $this->load_out_links();

		// Page init
		$this->tpl_name = 'acp_outlinks';
		$this->page_title = 'ACP_OUTLINKS';
		$form_name = 'acp_outlinks';
		add_form_key($form_name);

		switch ($action)
		{
			case 'edit':
				$id = request_var('id', -1);
				if (isset($outlinks[$id]))
				{
					$link_info = $outlinks[$id];
					$s_hidden_fields .= '<input type="hidden" name="id" value="' . $id . '" />';
				}
				else
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

			case 'add':
				$link_info = isset($id) ? $outlinks[$id] : array('title' => '', 'url' => '', 'nofollow' => 0, 'newwindow' => 0);
				$template->assign_vars(array(
					'S_EDIT_LINK'		=> true,
					'U_ACTION'			=> $this->u_action,
					'U_BACK'			=> $this->u_action,
					'LINK_TITLE'		=> $link_info['title'],
					'LINK_URL'			=> $link_info['url'],
					'LINK_NOFOLLOW'		=> $link_info['nofollow'],
					'LINK_NEWWINDOW'	=> $link_info['newwindow'],
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields
				));
				return;

			case 'save':
				$id = request_var('id', -1);
				$link_data = array(
					'title'		=> trim(str_replace(array("\n", "\t"), '', utf8_normalize_nfc(request_var('title', '', true)))),
					'url'		=> trim(str_replace(array("\n", "\t"), '', request_var('url', ''))),
					'nofollow'	=> request_var('nofollow', 0),
					'newwindow'	=> request_var('newwindow', 0),
				);
				if (!check_form_key($form_name) || empty($link_data['title']) || empty($link_data['url']))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}
				$newlink = empty($outlinks[$id]);
				if ($newlink)
				{
					$outlinks[] = $link_data;
				}
				else
				{
					$outlinks[$id] = $link_data;
				}
				$this->save_out_links($outlinks);
				$message = ($newlink) ? $user->lang['LINK_ADDED'] : $user->lang['LINK_UPDATED'];
				trigger_error($message . adm_back_link($this->u_action));
				break;

			case 'delete':
				$id = request_var('id', -1);
				if (empty($outlinks[$id]))
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				if (confirm_box(true))
				{
					unset($outlinks[$id]);
					$this->save_out_links($outlinks);
					trigger_error($user->lang['LINK_REMOVED'] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
						'i'			=> $acp_id,
						'mode'		=> $acp_mode,
						'id'		=> $id,
						'action'	=> 'delete',
					)));
				}
				break;

			case 'move_up':
			case 'move_down':
				$id = request_var('id', -1);
				if (empty($outlinks[$id]))
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				if ($action == 'move_up')
				{
					if ($id <= 0) break;
					$tmp = $outlinks[$id-1];
					$outlinks[$id-1] = $outlinks[$id];
					$outlinks[$id] = $tmp;
				}
				else
				{
					if (empty($outlinks[$id+1])) break;
					$tmp = $outlinks[$id+1];
					$outlinks[$id+1] = $outlinks[$id];
					$outlinks[$id] = $tmp;
				}
				$this->save_out_links($outlinks);
				break;
		}

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields)
		);

		foreach ($outlinks as $id => $row)
		{
			$template->assign_block_vars('items', array(
				'TITLE'			=> $row['title'],
				'URL'			=> $row['url'],
				'NOFOLLOW'		=> $row['nofollow'],
				'NEWWINDOW'		=> $row['newwindow'],
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $id,
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $id,
				'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;id=' . $id,
				'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;id=' . $id,
			));
		}
	}
}
