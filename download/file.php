<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$download_id = request_var('id', 0);
$mode = request_var('mode', '');
$thumbnail = request_var('t', false);

// Start session management, do not update session page.
$user->session_begin(false);
$auth->acl($user->data);
$user->setup('viewtopic');

if (!$download_id)
{
	send_status_line(404, 'Not Found');
	trigger_error('NO_ATTACHMENT_SELECTED');
}

if (!$config['allow_attachments'] && !$config['allow_pm_attach'])
{
	send_status_line(404, 'Not Found');
	trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
}

$sql = 'SELECT attach_id, in_message, post_msg_id, extension, is_orphan, poster_id, filetime
	FROM ' . ATTACHMENTS_TABLE . "
	WHERE attach_id = $download_id";
$result = $db->sql_query_limit($sql, 1);
$attachment = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$attachment)
{
	send_status_line(404, 'Not Found');
	trigger_error('ERROR_NO_ATTACHMENT');
}

if ((!$attachment['in_message'] && !$config['allow_attachments']) || ($attachment['in_message'] && !$config['allow_pm_attach']))
{
	send_status_line(404, 'Not Found');
	trigger_error('ATTACHMENT_FUNCTIONALITY_DISABLED');
}

$row = array();

if ($attachment['is_orphan'])
{
	// We allow admins having attachment permissions to see orphan attachments...
	$own_attachment = ($auth->acl_get('a_attach') || $attachment['poster_id'] == $user->data['user_id']) ? true : false;

	if (!$own_attachment || ($attachment['in_message'] && !$auth->acl_get('u_pm_download')) || (!$attachment['in_message'] && !$auth->acl_get('u_download')))
	{
		send_status_line(404, 'Not Found');
		trigger_error('ERROR_NO_ATTACHMENT');
	}

	// Obtain all extensions...
	$extensions = $cache->obtain_attach_extensions(true);
}
else
{
	if (!$attachment['in_message'])
	{
		//
		$sql = 'SELECT p.forum_id, f.forum_name, f.forum_password, f.parent_id
			FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f
			WHERE p.post_id = ' . $attachment['post_msg_id'] . '
				AND p.forum_id = f.forum_id';
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// Global announcement?
		$f_download = (!$row) ? $auth->acl_getf_global('f_download') : $auth->acl_get('f_download', $row['forum_id']);

		if ($auth->acl_get('u_download') && $f_download)
		{
			if ($row && $row['forum_password'])
			{
				// Do something else ... ?
				login_forum_box($row);
			}
		}
		else
		{
			send_status_line(403, 'Forbidden');
			trigger_error('SORRY_AUTH_VIEW_ATTACH');
		}
	}
	else
	{
		$row['forum_id'] = false;
		if (!$auth->acl_get('u_pm_download'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error('SORRY_AUTH_VIEW_ATTACH');
		}

		// Check if the attachment is within the users scope...
		$sql = 'SELECT user_id, author_id
			FROM ' . PRIVMSGS_TO_TABLE . '
			WHERE msg_id = ' . $attachment['post_msg_id'];
		$result = $db->sql_query($sql);

		$allowed = false;
		while ($user_row = $db->sql_fetchrow($result))
		{
			if ($user->data['user_id'] == $user_row['user_id'] || $user->data['user_id'] == $user_row['author_id'])
			{
				$allowed = true;
				break;
			}
		}
		$db->sql_freeresult($result);

		if (!$allowed)
		{
			send_status_line(403, 'Forbidden');
			trigger_error('ERROR_NO_ATTACHMENT');
		}
	}

	// disallowed?
	$extensions = array();
	if (!extension_allowed($row ? $row['forum_id'] : false, $attachment['extension'], $extensions))
	{
		send_status_line(404, 'Forbidden');
		trigger_error(sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));
	}
}

if (!download_allowed())
{
	send_status_line(403, 'Forbidden');
	trigger_error($user->lang['LINKAGE_FORBIDDEN']);
}

$download_mode = (int) $extensions[$attachment['extension']]['download_mode'];

// Fetching filename here to prevent sniffing of filename
$sql = 'SELECT attach_id, is_orphan, in_message, post_msg_id, extension, physical_filename, real_filename, mimetype, filetime
	FROM ' . ATTACHMENTS_TABLE . "
	WHERE attach_id = $download_id";
$result = $db->sql_query_limit($sql, 1);
$attachment = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$attachment)
{
	send_status_line(404, 'Not Found');
	trigger_error('ERROR_NO_ATTACHMENT');
}

$attachment['physical_filename'] = utf8_basename($attachment['physical_filename']);
$display_cat = $extensions[$attachment['extension']]['display_cat'];
if ($display_cat >= ATTACHMENT_CATEGORY_COUNT) { $display_cat = ATTACHMENT_CATEGORY_NONE; }

if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !$user->optionget('viewimg'))
{
	$display_cat = ATTACHMENT_CATEGORY_NONE;
}

if ($thumbnail)
{
	$attachment['physical_filename'] = 'thumb_' . $attachment['physical_filename'];
}
else if (($display_cat == ATTACHMENT_CATEGORY_NONE || $display_cat == ATTACHMENT_CATEGORY_AUDIO || $display_cat == ATTACHMENT_CATEGORY_VIDEO/* || $display_cat == ATTACHMENT_CATEGORY_IMAGE*/) && !$attachment['is_orphan'])
{
	// Update download count
	$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
		SET download_count = download_count + 1
		WHERE attach_id = ' . $attachment['attach_id'];
	$db->sql_query($sql);
}

// Determine the 'presenting'-method
if ($download_mode == PHYSICAL_LINK)
{
	// This presenting method should no longer be used
	if (!@is_dir($phpbb_root_path . $config['upload_path']))
	{
		send_status_line(500, 'Internal Server Error');
		trigger_error($user->lang['PHYSICAL_DOWNLOAD_NOT_POSSIBLE']);
	}

	redirect($phpbb_root_path . $config['upload_path'] . '/' . $attachment['physical_filename']);
	file_gc();
}
else
{
	send_file_to_browser($attachment, $config['upload_path'], $display_cat);
	file_gc();
}

/**
* Send file to browser
*/
function send_file_to_browser($attachment, $upload_dir, $category)
{
	global $user, $db, $config, $phpbb_root_path;

	$filename = $phpbb_root_path . $upload_dir . '/' . $attachment['physical_filename'];

	if (!@file_exists($filename) && substr($attachment['physical_filename'],0,6) == 'thumb_')
	{
		$image_file = substr($attachment['physical_filename'],6);
		include_once("../includes/functions_posting.php");
		if (! create_thumbnail($phpbb_root_path . $upload_dir . '/' . $image_file, $filename, ''))
		{ // disable thumbnail
			$db->sql_query('UPDATE ' . ATTACHMENTS_TABLE . ' SET thumbnail = 0 WHERE attach_id = ' . $attachment['attach_id']);
		};
	};

	if (!@file_exists($filename))
	{
		send_status_line(404, 'Not Found');
		trigger_error('ERROR_NO_ATTACHMENT');
	}

	// Correct the mime type - we force application/octetstream for all files, except images
	// Please do not change this, it is a security precaution
	if ($category != ATTACHMENT_CATEGORY_IMAGE || strpos($attachment['mimetype'], 'image') !== 0)
	{
		$attachment['mimetype'] = 'application/octet-stream';
	}

	// Forced MIME type for audio and video files
	if ($mime = get_attachment_mime($category, $attachment['extension']))
	{
		$attachment['mimetype'] = $mime;
	}

	if (@ob_get_length())
	{
		@ob_end_clean();
	}

	// Now send the File Contents to the Browser
	$size = @filesize($filename);

	// To correctly display further errors we need to make sure we are using the correct headers for both (unsetting content-length may not work)

	// Check if headers already sent or not able to get the file contents.
	if (headers_sent() || !@file_exists($filename) || !@is_readable($filename))
	{
		// PHP track_errors setting On?
		if (!empty($php_errormsg))
		{
			send_status_line(500, 'Internal Server Error');
			trigger_error($user->lang['UNABLE_TO_DELIVER_FILE'] . '<br />' . sprintf($user->lang['TRACKED_PHP_ERROR'], $php_errormsg));
		}

		send_status_line(500, 'Internal Server Error');
		trigger_error('UNABLE_TO_DELIVER_FILE');
	}

	// Now the tricky part... let's dance
	header('Cache-Control: public');

	/**
	* Commented out X-Sendfile support. To not expose the physical filename within the header if xsendfile is absent we need to look into methods of checking it's status.
	*
	* Try X-Sendfile since it is much more server friendly - only works if the path is *not* outside of the root path...
	* lighttpd has core support for it. An apache2 module is available at http://celebnamer.celebworld.ws/stuff/mod_xsendfile/
	*
	* Not really ideal, but should work fine...
	* <code>
	*	if (strpos($upload_dir, '/') !== 0 && strpos($upload_dir, '../') === false)
	*	{
	*		header('X-Sendfile: ' . $filename);
	*	}
	* </code>
	*/

	// Send out the Headers.
	header('Content-Type: ' . $attachment['mimetype']);
	header('X-Content-Type-Options: nosniff');
	header('Content-Disposition: ' . ((strpos($attachment['mimetype'], 'image') === 0 || strpos($attachment['mimetype'], 'audio') === 0 || strpos($attachment['mimetype'], 'video') === 0) ? 'inline' : 'attachment') . "; filename*=UTF-8''" . rawurlencode(htmlspecialchars_decode($attachment['real_filename'])));

	// Close the db connection before sending the file
	$db->sql_close();

	if (!set_modified_headers($attachment['filetime']))
	{
		// Send Content-Length only if set_modified_headers() does not send
		// status 304 - Not Modified
		if ($size)
		{
			header("Content-Length: $size");
		}

		// Try to deliver in chunks
		@set_time_limit(0);

		$fp = @fopen($filename, 'rb');

		if ($fp !== false)
		{
			while (!feof($fp))
			{
				echo fread($fp, 8192);
			}
			fclose($fp);
		}
		else
		{
			@readfile($filename);
		}

		flush();
	}
	file_gc();
}

/**
* Check if downloading item is allowed
*/
function download_allowed()
{
	global $config, $user, $db;

	if (!$config['secure_downloads'])
	{
		return true;
	}

	$url = (!empty($_SERVER['HTTP_REFERER'])) ? trim($_SERVER['HTTP_REFERER']) : trim(getenv('HTTP_REFERER'));

	if (!$url)
	{
		return ($config['secure_allow_empty_referer']) ? true : false;
	}

	// Split URL into domain and script part
	$url = @parse_url($url);

	if ($url === false)
	{
		return ($config['secure_allow_empty_referer']) ? true : false;
	}

	$hostname = $url['host'];
	unset($url);

	$allowed = ($config['secure_allow_deny']) ? false : true;
	$iplist = array();

	if (($ip_ary = @gethostbynamel($hostname)) !== false)
	{
		foreach ($ip_ary as $ip)
		{
			if ($ip)
			{
				$iplist[] = $ip;
			}
		}
	}

	// Check for own server...
	$server_name = $user->host;

	// Forcing server vars is the only way to specify/override the protocol
	if ($config['force_server_vars'] || !$server_name)
	{
		$server_name = $config['server_name'];
	}

	if (preg_match('#^.*?' . preg_quote($server_name, '#') . '.*?$#i', $hostname))
	{
		$allowed = true;
	}

	// Get IP's and Hostnames
	if (!$allowed)
	{
		$sql = 'SELECT site_ip, site_hostname, ip_exclude
			FROM ' . SITELIST_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$site_ip = trim($row['site_ip']);
			$site_hostname = trim($row['site_hostname']);

			if ($site_ip)
			{
				foreach ($iplist as $ip)
				{
					if (preg_match('#^' . str_replace('\*', '.*?', preg_quote($site_ip, '#')) . '$#i', $ip))
					{
						if ($row['ip_exclude'])
						{
							$allowed = ($config['secure_allow_deny']) ? false : true;
							break 2;
						}
						else
						{
							$allowed = ($config['secure_allow_deny']) ? true : false;
						}
					}
				}
			}

			if ($site_hostname)
			{
				if (preg_match('#^' . str_replace('\*', '.*?', preg_quote($site_hostname, '#')) . '$#i', $hostname))
				{
					if ($row['ip_exclude'])
					{
						$allowed = ($config['secure_allow_deny']) ? false : true;
						break;
					}
					else
					{
						$allowed = ($config['secure_allow_deny']) ? true : false;
					}
				}
			}
		}
		$db->sql_freeresult($result);
	}

	return $allowed;
}

/**
* Check if the browser has the file already and set the appropriate headers-
* @returns false if a resend is in order.
*/
function set_modified_headers($stamp)
{
	header('Cache-Control: public, max-age=2592000');

	// let's see if we have to send the file at all
	$last_load = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime(trim($_SERVER['HTTP_IF_MODIFIED_SINCE'])) : false;
	if ($last_load !== false && $last_load >= $stamp)
	{
		send_status_line(304, 'Not Modified');
		return true;
	}
	else
	{
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $stamp) . ' GMT');
		return false;
	}
}

function file_gc()
{
	global $cache, $db;
	if (!empty($cache))
	{
		$cache->unload();
	}
	$db->sql_close();
	exit;
}
