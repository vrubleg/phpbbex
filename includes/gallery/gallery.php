<?php
/**
*
* @package phpBB Gallery
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_gallery
{
	static public $auth = null;
	static public $user = null;

	static public $display_popup = false;
	static public $loaded = false;

	/**
	* Constructor: setup() also creates a phpbb-session, if you already have one, be sure to use init()
	*/
	static public function setup($lang_set = false, $update_session = true)
	{
		global $auth, $config, $db, $template, $user, $cache;

		$lang_sets = array('mods/info_acp_gallery');
		if (is_array($lang_set))
		{
			$lang_sets = array_merge($lang_sets, $lang_set);
		}
		elseif (is_string($lang_set))
		{
			$lang_sets[] = $lang_set;
		}

		// Start session management
		$user->session_begin($update_session);
		/**
		* Maybe we need this for the feed
		if (!empty($config['feed_http_auth']) && request_var('auth', '') == 'http')
		{
			phpbb_http_login(array(
				'auth_message'	=> 'Feed',
				'viewonline'	=> request_var('viewonline', true),
			));
		}
		*/
		$auth->acl($user->data);
		$user->setup($lang_sets);

		phpbb_gallery_url::_include('functions_phpbb', 'phpbb', 'includes/gallery/');
		phpbb_gallery_plugins::init(phpbb_gallery_url::path());

		// Little precaution.
		$user->data['user_id'] = (int) $user->data['user_id'];

		self::$user = new phpbb_gallery_user($db, $user->data['user_id']);

		$user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
		self::$auth = new phpbb_gallery_auth($user_id);

		if (phpbb_gallery_config::get('prune_orphan_time') < time())
		{
			// Delete orphan uploaded files, which are older than half an hour...
			phpbb_gallery_upload::prune_orphan();
			phpbb_gallery_config::set('prune_orphan_time', time() + 1800);
		}

		if (request_var('display', '') == 'popup')
		{
			self::init_popup();
		}

		$template->assign_vars(array(
			'S_IN_GALLERY'					=> true,
			'U_GALLERY_SEARCH'				=> phpbb_gallery_url::append_sid('search'),
			'S_GALLERY_FEEDS'				=> phpbb_gallery_config::get('feed_enable'),
			'U_GALLERY_FEED'				=> phpbb_gallery_url::append_sid('feed'),
		));

		// Okay, this is not the best way, but we disable the phpbb feeds and display the ones of the gallery.
		$config['feed_overall'] = $config['feed_overall_forums'] = $config['feed_topics_new'] = $config['feed_topics_active'] = false;

		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $user->lang['GALLERY'],
			'U_VIEW_FORUM'	=> phpbb_gallery_url::append_sid('index'),
			'FIRST'			=> true,
		));

		self::$loaded = true;
	}

	/**
	* Sets up some basic stuff for the gallery.
	*/
	static public function init()
	{
		global $db, $user;

		phpbb_gallery_url::_include('functions_phpbb', 'phpbb', 'includes/gallery/');
		phpbb_gallery_plugins::init(phpbb_gallery_url::path());

		// Little precaution.
		$user->data['user_id'] = (int) $user->data['user_id'];

		self::$user = new phpbb_gallery_user($db, $user->data['user_id']);

		$user_id = ($user->data['user_perm_from'] == 0) ? $user->data['user_id'] : $user->data['user_perm_from'];
		self::$auth = new phpbb_gallery_auth($user_id);

		self::$loaded = true;
		if (request_var('display', '') == 'popup')
		{
			self::init_popup();
		}

	}

	/**
	* Sets up some basic stuff for the gallery.
	*/
	static public function init_popup()
	{
		global $template, $user;

		self::$display_popup = '&amp;display=popup';

		$can_upload = phpbb_gallery::$auth->acl_album_ids('i_upload', 'bool');

		$template->assign_vars(array(
			'S_IN_GALLERY_POPUP'			=> (request_var('display', '') == 'popup') ? true : false,

			'U_POPUP_OWN'		=> phpbb_gallery_url::append_sid('search', 'user_id=' . (int) $user->data['user_id']),
			'U_POPUP_RECENT'	=> phpbb_gallery_url::append_sid('search', 'search_id=recent'),
			'U_POPUP_UPLOAD'	=> ($can_upload) ? phpbb_gallery_url::append_sid('posting', 'mode=upload') : '',
		));
	}
}
