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

class acp_search
{
	var $u_action;
	var $module_path;
	var $tpl_name;
	var $page_title;

	function main($id, $mode)
	{
		global $user, $template, $config;

		$user->add_lang('acp/search');

		$submit = isset($_POST['submit']);

		$settings = [
			'search_interval'           => 'float',
			'search_anonymous_interval' => 'float',
			'load_search'               => 'bool',
			'min_search_author_chars'   => 'integer',
			'max_num_search_keywords'   => 'integer',
			'search_store_results'      => 'integer',
			'default_search_titleonly'  => 'bool',
			'search_highlight_keywords' => 'bool',
		];

		$cfg_array = (isset($_REQUEST['config'])) ? request_var('config', ['' => ''], true) : [];
		$updated = false;

		foreach ($settings as $config_name => $var_type)
		{
			if (!isset($cfg_array[$config_name]))
			{
				continue;
			}

			// e.g. integer:4:12 (min 4, max 12)
			$var_type = explode(':', $var_type);

			$config_value = $cfg_array[$config_name];
			settype($config_value, $var_type[0]);

			if (isset($var_type[1]))
			{
				$config_value = max($var_type[1], $config_value);
			}

			if (isset($var_type[2]))
			{
				$config_value = min($var_type[2], $config_value);
			}

			// only change config if anything was actually changed
			if ($submit && (!isset($config[$config_name]) || $config[$config_name] != $config_value))
			{
				set_config($config_name, $config_value);
				$updated = true;
			}
		}

		require_once(PHPBB_ROOT_PATH . 'includes/search/fulltext_mysql.php');
		$search = new fulltext_mysql();

		$action = request_var('action', '');
		if ($action)
		{
			switch ($action)
			{
				case 'delete':
					$confirm_lang = 'SEARCH_INDEX_DELETE_CONFIRM';
				break;

				case 'create':
					$confirm_lang = 'SEARCH_INDEX_CREATE_CONFIRM';
				break;

				default:
					trigger_error('NO_ACTION', E_USER_ERROR);
				break;
			}

			if (!confirm_box(true))
			{
				confirm_box(false, $user->lang[$confirm_lang], build_hidden_fields([
					'i'      => $id,
					'mode'   => $mode,
					'action' => $action,
				]));
			}
			else
			{
				@set_time_limit(0);

				switch ($action)
				{
					case 'delete':
						$error = $search->delete_index();
						$message = $user->lang['SEARCH_INDEX_REMOVED'];
						$log_operation = 'LOG_SEARCH_INDEX_REMOVED';
					break;

					case 'create':
						$error = $search->create_index();
						$message = $user->lang['SEARCH_INDEX_CREATED'];
						$log_operation = 'LOG_SEARCH_INDEX_CREATED';
					break;
				}

				if ($error)
				{
					trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$search->tidy();

				add_log('admin', $log_operation);
				trigger_error($message . adm_back_link($this->u_action));
			}
		}

		$stats = $search->get_stats();
		$index_created = $stats['post_subject'] && $stats['post_content'];
		if ($index_created != !empty($config['fulltext_mysql_indexed']))
		{
			set_config('fulltext_mysql_indexed', $index_created);
		}

		if ($submit)
		{
			if ($updated)
			{
				add_log('admin', 'LOG_CONFIG_SEARCH');
			}

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}
		unset($cfg_array);

		$this->tpl_name = 'acp_search';
		$this->page_title = 'ACP_SEARCH_SETTINGS';

		$template->assign_vars([
			'MIN_SEARCH_AUTHOR_CHARS'       => (int) $config['min_search_author_chars'],
			'SEARCH_INTERVAL'               => (float) $config['search_interval'],
			'SEARCH_GUEST_INTERVAL'         => (float) $config['search_anonymous_interval'],
			'SEARCH_STORE_RESULTS'          => (int) $config['search_store_results'],
			'MAX_NUM_SEARCH_KEYWORDS'       => (int) $config['max_num_search_keywords'],
			'FULLTEXT_MYSQL_MIN_WORD_LEN'   => (int) $config['fulltext_mysql_min_word_len'],
			'FULLTEXT_MYSQL_MAX_WORD_LEN'   => (int) $config['fulltext_mysql_max_word_len'],
			'FULLTEXT_MYSQL_INDEXED_POSTS'   => sprintf($user->lang['FULLTEXT_MYSQL_INDEXED_POSTS'], $stats['total_posts']),

			'S_YES_SEARCH'                  => (bool) $config['load_search'],
			'S_DEFAULT_TITLEONLY'           => !empty($config['default_search_titleonly']),
			'S_HIGHLIGHT_KEYWORDS'          => !empty($config['search_highlight_keywords']),
			'S_INDEXED'                     => $index_created,

			'U_ACTION'                      => $this->u_action,
		]);
	}
}
