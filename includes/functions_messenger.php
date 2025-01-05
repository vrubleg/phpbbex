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

/**
* Messenger
*/
class messenger
{
	var $vars, $msg, $replyto, $from, $subject;
	var $addresses = array();
	var $extra_headers = array();

	var $use_queue = true;
	var $queue;
	var $jabber;

	var $tpl_obj = NULL;
	var $tpl_msg = array();

	/**
	* Constructor
	*/
	function __construct($use_queue = true)
	{
		global $config;

		$this->use_queue = (!$config['email_package_size']) ? false : $use_queue;
		$this->subject = '';
	}

	/**
	* Resets all the data (address, template file, etc etc) to default
	*/
	function reset()
	{
		$this->addresses = $this->extra_headers = array();
		$this->vars = $this->msg = $this->replyto = $this->from = '';
	}

	/**
	* Sets an email address to send to
	*/
	function to($address, $realname = '')
	{
		global $config;

		if (!trim($address))
		{
			return;
		}

		$pos = isset($this->addresses['to']) ? sizeof($this->addresses['to']) : 0;

		$this->addresses['to'][$pos]['email'] = trim($address);

		// If empty sendmail_path on windows, PHP changes the to line
		if (!$config['smtp_delivery'] && DIRECTORY_SEPARATOR == '\\')
		{
			$this->addresses['to'][$pos]['name'] = '';
		}
		else
		{
			$this->addresses['to'][$pos]['name'] = trim($realname);
		}
	}

	/**
	* Sets an cc address to send to
	*/
	function cc($address, $realname = '')
	{
		if (!trim($address))
		{
			return;
		}

		$pos = isset($this->addresses['cc']) ? sizeof($this->addresses['cc']) : 0;
		$this->addresses['cc'][$pos]['email'] = trim($address);
		$this->addresses['cc'][$pos]['name'] = trim($realname);
	}

	/**
	* Sets an bcc address to send to
	*/
	function bcc($address, $realname = '')
	{
		if (!trim($address))
		{
			return;
		}

		$pos = isset($this->addresses['bcc']) ? sizeof($this->addresses['bcc']) : 0;
		$this->addresses['bcc'][$pos]['email'] = trim($address);
		$this->addresses['bcc'][$pos]['name'] = trim($realname);
	}

	/**
	* Sets a im contact to send to
	*/
	function im($address, $realname = '')
	{
		// IM-Addresses could be empty
		if (!trim($address))
		{
			return;
		}

		$pos = isset($this->addresses['im']) ? sizeof($this->addresses['im']) : 0;
		$this->addresses['im'][$pos]['uid'] = trim($address);
		$this->addresses['im'][$pos]['name'] = trim($realname);
	}

	/**
	* Set the reply to address
	*/
	function replyto($address)
	{
		$this->replyto = trim($address);
	}

	/**
	* Set the from address
	*/
	function from($address)
	{
		$this->from = trim($address);
	}

	/**
	* set up subject for mail
	*/
	function subject($subject = '')
	{
		$this->subject = trim($subject);
	}

	/**
	* set up extra mail headers
	*/
	function headers($headers)
	{
		$this->extra_headers[] = trim($headers);
	}

	/**
	* Adds X-AntiAbuse headers
	*
	* @param array $config		Configuration array
	* @param user $user			A user object
	*
	* @return null
	*/
	function anti_abuse_headers($config, $user)
	{
		$this->headers('X-AntiAbuse: Host - ' . $user->host);
		$this->headers('X-AntiAbuse: User ID - ' . $user->data['user_id']);
		$this->headers('X-AntiAbuse: User IP - ' . $user->ip);
	}

	/**
	* Set email template to use
	*/
	function template($template_file, $template_lang = '', $template_path = '')
	{
		global $config, $phpbb_root_path, $user;

		if (!trim($template_file))
		{
			trigger_error('No template file for emailing set.', E_USER_ERROR);
		}

		if (!trim($template_lang))
		{
			// fall back to board default language if the user's language is
			// missing $template_file.  If this does not exist either,
			// $tpl->set_custom_template will do a trigger_error
			$template_lang = basename($config['default_lang']);
		}

		// tpl_msg now holds a template object we can use to parse the template file
		if (!isset($this->tpl_msg[$template_lang . $template_file]))
		{
			$this->tpl_msg[$template_lang . $template_file] = new phpbb_template();
			$tpl = &$this->tpl_msg[$template_lang . $template_file];

			$fallback_template_path = false;

			if (!$template_path)
			{
				$template_path = (!empty($user->lang_path)) ? $user->lang_path : $phpbb_root_path . 'language/';
				$template_path .= $template_lang . '/email';

				// we can only specify default language fallback when the path is not a custom one for which we
				// do not know the default language alternative
				if ($template_lang !== basename($config['default_lang']))
				{
					$fallback_template_path = (!empty($user->lang_path)) ? $user->lang_path : $phpbb_root_path . 'language/';
					$fallback_template_path .= basename($config['default_lang']) . '/email';
				}
			}

			$tpl->set_custom_template($template_path, $template_lang . '_email', $fallback_template_path);

			$tpl->set_filenames(array(
				'body'		=> $template_file . '.txt',
			));
		}

		$this->tpl_obj = &$this->tpl_msg[$template_lang . $template_file];
		$this->vars = &$this->tpl_obj->_rootref;
		$this->tpl_msg = array();

		return true;
	}

	/**
	* assign variables to email template
	*/
	function assign_vars($vars)
	{
		if (!is_object($this->tpl_obj))
		{
			return;
		}

		$this->tpl_obj->assign_vars($vars);
	}

	function assign_block_vars($blockname, $vars)
	{
		if (!is_object($this->tpl_obj))
		{
			return;
		}

		$this->tpl_obj->assign_block_vars($blockname, $vars);
	}

	/**
	* Send the mail out to the recipients set previously in var $this->addresses
	*/
	function send($method = NOTIFY_EMAIL, $break = false)
	{
		global $config, $user;

		// We add some standard variables we always use, no need to specify them always
		if (!isset($this->vars['U_BOARD']))
		{
			$this->assign_vars(array(
				'U_BOARD'	=> generate_board_url(),
			));
		}

		if (!isset($this->vars['EMAIL_SIG']))
		{
			$this->assign_vars(array(
				'EMAIL_SIG'	=> str_replace('<br />', "\n", "-- \n" . htmlspecialchars_decode($config['board_email_sig'])),
			));
		}

		if (!isset($this->vars['SITENAME']))
		{
			$this->assign_vars(array(
				'SITENAME'	=> htmlspecialchars_decode($config['sitename']),
			));
		}

		// Parse message through template
		$this->msg = trim($this->tpl_obj->assign_display('body'));

		// Because we use \n for newlines in the body message we need to fix line encoding errors for those admins who uploaded email template files in the wrong encoding
		$this->msg = str_replace("\r\n", "\n", $this->msg);

		// We now try and pull a subject from the email body ... if it exists,
		// do this here because the subject may contain a variable
		$drop_header = '';
		$match = array();
		if (preg_match('#^(Subject:(.*?))$#m', $this->msg, $match))
		{
			$this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : $user->lang['NO_EMAIL_SUBJECT']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->subject = (($this->subject != '') ? $this->subject : $user->lang['NO_EMAIL_SUBJECT']);
		}

		if ($drop_header)
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}

		if ($break)
		{
			return true;
		}

		switch ($method)
		{
			case NOTIFY_EMAIL:
				$result = $this->msg_email();
			break;

			case NOTIFY_IM:
				$result = $this->msg_jabber();
			break;

			case NOTIFY_BOTH:
				$result = $this->msg_email();
				$this->msg_jabber();
			break;
		}

		$this->reset();
		return $result;
	}

	/**
	* Add error message to log
	*/
	static function error($type, $msg)
	{
		global $user, $phpbb_root_path, $config;

		// Session doesn't exist, create it
		if (!isset($user->session_id) || $user->session_id === '')
		{
			$user->session_begin();
		}

		$calling_page = (!empty($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : $_ENV['SCRIPT_NAME'];

		$message = '';
		switch ($type)
		{
			case 'EMAIL':
				$message = '<strong>EMAIL/' . (($config['smtp_delivery']) ? 'SMTP' : 'PHP') . '</strong>';
			break;

			default:
				$message = "<strong>$type</strong>";
			break;
		}

		$message .= '<br /><em>' . htmlspecialchars($calling_page) . '</em><br /><br />' . $msg . '<br />';
		add_log('critical', 'LOG_ERROR_' . $type, $message);
	}

	/**
	* Save to queue
	*/
	function save_queue()
	{
		global $config;

		if ($config['email_package_size'] && $this->use_queue && !empty($this->queue))
		{
			$this->queue->save();
			return;
		}
	}

	/**
	* Generates a valid message id to be used in emails
	*
	* @return string message id
	*/
	function generate_message_id()
	{
		return md5(unique_id()) . '@' . HTTP_HOST;
	}

	/**
	* Return email header
	*/
	function build_header($to, $cc, $bcc)
	{
		global $config;

		// We could use keys here, but we won't do this for 3.0.x to retain backwards compatibility
		$headers = array();

		$headers[] = 'From: ' . $this->from;

		if ($cc)
		{
			$headers[] = 'Cc: ' . $cc;
		}

		if ($bcc)
		{
			$headers[] = 'Bcc: ' . $bcc;
		}

		$headers[] = 'Reply-To: ' . $this->replyto;
		$headers[] = 'Return-Path: <' . $config['board_email'] . '>';
		$headers[] = 'Sender: <' . $config['board_email'] . '>';
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Message-ID: <' . $this->generate_message_id() . '>';
		$headers[] = 'Date: ' . date('r', time());
		$headers[] = 'Content-Type: text/plain; charset=UTF-8'; // format=flowed
		$headers[] = 'Content-Transfer-Encoding: 8bit'; // 7bit
		$headers[] = 'X-Mailer: phpBB3';

		if (count($this->extra_headers))
		{
			$headers = array_merge($headers, $this->extra_headers);
		}

		return $headers;
	}

	/**
	* Send out emails
	*/
	function msg_email()
	{
		global $config, $user;

		if (empty($config['email_enable']))
		{
			return false;
		}

		// Addresses to send to?
		if (empty($this->addresses) || (empty($this->addresses['to']) && empty($this->addresses['cc']) && empty($this->addresses['bcc'])))
		{
			// Send was successful. ;)
			return true;
		}

		$use_queue = false;
		if ($config['email_package_size'] && $this->use_queue)
		{
			if (empty($this->queue))
			{
				$this->queue = new queue();
				$this->queue->init('email', $config['email_package_size']);
			}
			$use_queue = true;
		}

		$encode_eol = ($config['smtp_delivery'] || PHP_VERSION_ID >= 80000) ? "\r\n" : PHP_EOL;

		$default_contact = (empty($config['board_contact_name']) ? '' : (mail_encode($config['board_contact_name'], $encode_eol) . ' ')) . '<' . $config['board_contact'] . '>';
		if (empty($this->replyto))
		{
			$this->replyto = $default_contact;
		}
		if (empty($this->from))
		{
			$this->from = $default_contact;
		}

		// Build to, cc and bcc strings
		$to = $cc = $bcc = '';
		foreach ($this->addresses as $type => $address_ary)
		{
			if ($type == 'im')
			{
				continue;
			}

			foreach ($address_ary as $which_ary)
			{
				${$type} .= ((${$type} != '') ? ', ' : '') . (($which_ary['name'] != '') ? mail_encode($which_ary['name'], $encode_eol) . ' <' . $which_ary['email'] . '>' : $which_ary['email']);
			}
		}

		// Build header
		$headers = $this->build_header($to, $cc, $bcc);

		// Send message ...
		if (!$use_queue)
		{
			$mail_to = ($to == '') ? 'undisclosed-recipients:;' : $to;
			$err_msg = '';

			if ($config['smtp_delivery'])
			{
				$result = smtpmail($this->addresses, mail_encode($this->subject), wordwrap(utf8_wordwrap($this->msg, 250), 997, "\n", true), $err_msg, $headers);
			}
			else
			{
				$result = phpbb_mail($mail_to, $this->subject, $this->msg, $headers, $encode_eol, $err_msg);
			}

			if (!$result)
			{
				self::error('EMAIL', $err_msg);
				return false;
			}
		}
		else
		{
			$this->queue->put('email', array(
				'to'			=> $to,
				'addresses'		=> $this->addresses,
				'subject'		=> $this->subject,
				'msg'			=> $this->msg,
				'headers'		=> $headers)
			);
		}

		return true;
	}

	/**
	* Send jabber message out
	*/
	function msg_jabber()
	{
		global $config, $db, $user, $phpbb_root_path;

		if (empty($config['jab_enable']) || empty($config['jab_host']) || empty($config['jab_username']) || empty($config['jab_password']))
		{
			return false;
		}

		if (empty($this->addresses['im']))
		{
			// Send was successful. ;)
			return true;
		}

		$use_queue = false;
		if ($config['jab_package_size'] && $this->use_queue)
		{
			if (empty($this->queue))
			{
				$this->queue = new queue();
				$this->queue->init('jabber', $config['jab_package_size']);
			}
			$use_queue = true;
		}

		$addresses = array();
		foreach ($this->addresses['im'] as $type => $uid_ary)
		{
			$addresses[] = $uid_ary['uid'];
		}
		$addresses = array_unique($addresses);

		if (!$use_queue)
		{
			require_once($phpbb_root_path . 'includes/functions_jabber.php');
			$this->jabber = new jabber($config['jab_host'], $config['jab_port'], $config['jab_username'], htmlspecialchars_decode($config['jab_password']), $config['jab_use_ssl']);

			if (!$this->jabber->connect())
			{
				self::error('JABBER', $user->lang['ERR_JAB_CONNECT'] . '<br />' . $this->jabber->get_log());
				return false;
			}

			if (!$this->jabber->login())
			{
				self::error('JABBER', $user->lang['ERR_JAB_AUTH'] . '<br />' . $this->jabber->get_log());
				return false;
			}

			foreach ($addresses as $address)
			{
				$this->jabber->send_message($address, $this->msg, $this->subject);
			}

			$this->jabber->disconnect();
		}
		else
		{
			$this->queue->put('jabber', array(
				'addresses'		=> $addresses,
				'subject'		=> $this->subject,
				'msg'			=> $this->msg)
			);
		}
		unset($addresses);
		return true;
	}
}

/**
* handling email and jabber queue
*/
class queue
{
	var $data = array();
	var $queue_data = array();
	var $package_size = 0;
	var $cache_file = '';

	/**
	* constructor
	*/
	function __construct()
	{
		global $phpbb_root_path;

		$this->data = array();
		$this->cache_file = "{$phpbb_root_path}cache/queue.php";
	}

	/**
	* Init a queue object
	*/
	function init($object, $package_size)
	{
		$this->data[$object] = array();
		$this->data[$object]['package_size'] = $package_size;
		$this->data[$object]['data'] = array();
	}

	/**
	* Put object in queue
	*/
	function put($object, $scope)
	{
		$this->data[$object]['data'][] = $scope;
	}

	/**
	* Obtains exclusive lock on queue cache file.
	* Returns resource representing the lock
	*/
	function lock()
	{
		// For systems that can't have two processes opening
		// one file for writing simultaneously
		if (file_exists($this->cache_file . '.lock'))
		{
			$mode = 'rb';
		}
		else
		{
			$mode = 'wb';
		}

		$lock_fp = @fopen($this->cache_file . '.lock', $mode);

		if ($mode == 'wb')
		{
			if (!$lock_fp)
			{
				// Two processes may attempt to create lock file at the same time.
				// Have the losing process try opening the lock file again for reading
				// on the assumption that the winning process created it
				$mode = 'rb';
				$lock_fp = @fopen($this->cache_file . '.lock', $mode);
			}
			else
			{
				// Only need to set mode when the lock file is written
				@chmod($this->cache_file . '.lock', 0666);
			}
		}

		if ($lock_fp)
		{
			@flock($lock_fp, LOCK_EX);
		}

		return $lock_fp;
	}

	/**
	* Releases lock on queue cache file, using resource obtained from lock()
	*/
	function unlock($lock_fp)
	{
		// lock() will return null if opening lock file, and thus locking, failed.
		// Accept null values here so that client code does not need to check them
		if ($lock_fp)
		{
			@flock($lock_fp, LOCK_UN);
			fclose($lock_fp);
		}
	}

	/**
	* Process queue
	* Using lock file
	*/
	function process()
	{
		global $db, $config, $phpbb_root_path, $user;

		$lock_fp = $this->lock();

		// avoid races, check file existence once
		$have_cache_file = file_exists($this->cache_file);
		if (!$have_cache_file || $config['last_queue_run'] > time() - $config['queue_interval'])
		{
			if (!$have_cache_file)
			{
				set_config('last_queue_run', time(), true);
			}

			$this->unlock($lock_fp);
			return;
		}

		set_config('last_queue_run', time(), true);

		require_once($this->cache_file);

		foreach ($this->queue_data as $object => $data_ary)
		{
			@set_time_limit(0);

			if (!isset($data_ary['package_size']))
			{
				$data_ary['package_size'] = 0;
			}

			$package_size = $data_ary['package_size'];
			$num_items = (!$package_size || sizeof($data_ary['data']) < $package_size) ? sizeof($data_ary['data']) : $package_size;

			/*
			* This code is commented out because it causes problems on some web hosts.
			* The core problem is rather restrictive email sending limits.
			* This code is nly useful if you have no such restrictions from the
			* web host and the package size setting is wrong.

			// If the amount of emails to be sent is way more than package_size than we need to increase it to prevent backlogs...
			if (sizeof($data_ary['data']) > $package_size * 2.5)
			{
				$num_items = sizeof($data_ary['data']);
			}
			*/

			switch ($object)
			{
				case 'email':
					// Delete the email queued objects if mailing is disabled
					if (!$config['email_enable'])
					{
						unset($this->queue_data['email']);
						continue 2;
					}
				break;

				case 'jabber':
					if (!$config['jab_enable'])
					{
						unset($this->queue_data['jabber']);
						continue 2;
					}

					require_once($phpbb_root_path . 'includes/functions_jabber.php');
					$this->jabber = new jabber($config['jab_host'], $config['jab_port'], $config['jab_username'], htmlspecialchars_decode($config['jab_password']), $config['jab_use_ssl']);

					if (!$this->jabber->connect())
					{
						messenger::error('JABBER', $user->lang['ERR_JAB_CONNECT']);
						continue 2;
					}

					if (!$this->jabber->login())
					{
						messenger::error('JABBER', $user->lang['ERR_JAB_AUTH']);
						continue 2;
					}

				break;

				default:
					$this->unlock($lock_fp);
					return;
			}

			for ($i = 0; $i < $num_items; $i++)
			{
				// Make variables available...
				extract(array_shift($this->queue_data[$object]['data']));

				switch ($object)
				{
					case 'email':
						$err_msg = '';
						$to = (!$to) ? 'undisclosed-recipients:;' : $to;

						if ($config['smtp_delivery'])
						{
							$result = smtpmail($addresses, mail_encode($subject), wordwrap(utf8_wordwrap($msg, 250), 997, "\n", true), $err_msg, $headers);
						}
						else
						{
							$result = phpbb_mail($to, $subject, $msg, $headers, (PHP_VERSION_ID >= 80000 ? "\r\n" : PHP_EOL), $err_msg);
						}

						if (!$result)
						{
							messenger::error('EMAIL', $err_msg);
							continue 2;
						}
					break;

					case 'jabber':
						foreach ($addresses as $address)
						{
							if ($this->jabber->send_message($address, $msg, $subject) === false)
							{
								messenger::error('JABBER', $this->jabber->get_log());
								continue 3;
							}
						}
					break;
				}
			}

			// No more data for this object? Unset it
			if (!sizeof($this->queue_data[$object]['data']))
			{
				unset($this->queue_data[$object]);
			}

			// Post-object processing
			switch ($object)
			{
				case 'jabber':
					// Hang about a couple of secs to ensure the messages are
					// handled, then disconnect
					$this->jabber->disconnect();
				break;
			}
		}

		if (!sizeof($this->queue_data))
		{
			@unlink($this->cache_file);
		}
		else
		{
			if ($fp = @fopen($this->cache_file, 'wb'))
			{
				fwrite($fp, "<?php\nif (!defined('IN_PHPBB')) exit;\n\$this->queue_data = unserialize(" . var_export(serialize($this->queue_data), true) . ");\n\n?>");
				fclose($fp);

				phpbb_chmod($this->cache_file, CHMOD_READ | CHMOD_WRITE);
			}
		}

		$this->unlock($lock_fp);
	}

	/**
	* Save queue
	*/
	function save()
	{
		if (!sizeof($this->data))
		{
			return;
		}

		$lock_fp = $this->lock();

		if (file_exists($this->cache_file))
		{
			require_once($this->cache_file);

			foreach ($this->queue_data as $object => $data_ary)
			{
				if (isset($this->data[$object]) && sizeof($this->data[$object]))
				{
					$this->data[$object]['data'] = array_merge($data_ary['data'], $this->data[$object]['data']);
				}
				else
				{
					$this->data[$object]['data'] = $data_ary['data'];
				}
			}
		}

		if ($fp = @fopen($this->cache_file, 'w'))
		{
			fwrite($fp, "<?php\nif (!defined('IN_PHPBB')) exit;\n\$this->queue_data = unserialize(" . var_export(serialize($this->data), true) . ");\n\n?>");
			fclose($fp);

			phpbb_chmod($this->cache_file, CHMOD_READ | CHMOD_WRITE);
		}

		$this->unlock($lock_fp);
	}
}

/**
* Replacement or substitute for PHP's mail command
*/
function smtpmail($addresses, $subject, $message, &$err_msg, $headers = false)
{
	global $config, $user;

	// Fix any bare linefeeds in the message to make it RFC821 Compliant.
	$message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);

	if ($headers !== false)
	{
		if (!is_array($headers))
		{
			// Make sure there are no bare linefeeds in the headers
			$headers = preg_replace('#(?<!\r)\n#si', "\n", $headers);
			$headers = explode("\n", $headers);
		}

		// Ok this is rather confusing all things considered,
		// but we have to grab bcc and cc headers and treat them differently
		// Something we really didn't take into consideration originally
		$headers_used = array();

		foreach ($headers as $header)
		{
			if (strpos(strtolower($header), 'cc:') === 0 || strpos(strtolower($header), 'bcc:') === 0)
			{
				continue;
			}
			$headers_used[] = trim($header);
		}

		$headers = chop(implode("\r\n", $headers_used));
	}

	if (trim($subject) == '')
	{
		$err_msg = (isset($user->lang['NO_EMAIL_SUBJECT'])) ? $user->lang['NO_EMAIL_SUBJECT'] : 'No email subject specified';
		return false;
	}

	if (trim($message) == '')
	{
		$err_msg = (isset($user->lang['NO_EMAIL_MESSAGE'])) ? $user->lang['NO_EMAIL_MESSAGE'] : 'Email message was blank';
		return false;
	}

	$mail_rcpt = $mail_to = $mail_cc = array();

	// Build correct addresses for RCPT TO command and the client side display (TO, CC)
	if (isset($addresses['to']) && sizeof($addresses['to']))
	{
		foreach ($addresses['to'] as $which_ary)
		{
			$mail_to[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name'])) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
			$mail_rcpt['to'][] = '<' . trim($which_ary['email']) . '>';
		}
	}

	if (isset($addresses['bcc']) && sizeof($addresses['bcc']))
	{
		foreach ($addresses['bcc'] as $which_ary)
		{
			$mail_rcpt['bcc'][] = '<' . trim($which_ary['email']) . '>';
		}
	}

	if (isset($addresses['cc']) && sizeof($addresses['cc']))
	{
		foreach ($addresses['cc'] as $which_ary)
		{
			$mail_cc[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name'])) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
			$mail_rcpt['cc'][] = '<' . trim($which_ary['email']) . '>';
		}
	}

	$smtp = new smtp_class();

	$errno = 0;
	$errstr = '';

	$smtp->add_backtrace('Connecting to ' . $config['smtp_host'] . ':' . $config['smtp_port']);

	// Ok we have error checked as much as we can to this point let's get on it already.
	if (!class_exists('phpbb_error_collector'))
	{
		global $phpbb_root_path;
		require_once($phpbb_root_path . 'includes/error_collector.php');
	}
	$collector = new phpbb_error_collector;
	$collector->install();

	$socket_options = array();
	if (isset($config['smtp_verify_cert']) && !$config['smtp_verify_cert'])
	{
		$socket_options['ssl'] = array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true);
	}
	$socket_context = stream_context_create($socket_options);
	$socket_address = $config['smtp_host'] . ':' . $config['smtp_port'];
	$smtp->socket = stream_socket_client($socket_address, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $socket_context);

	$collector->uninstall();
	$error_contents = $collector->format_errors();

	if (!$smtp->socket)
	{
		if ($errstr)
		{
			$errstr = utf8_convert_message($errstr);
		}

		$err_msg = (isset($user->lang['NO_CONNECT_TO_SMTP_HOST'])) ? sprintf($user->lang['NO_CONNECT_TO_SMTP_HOST'], $errno, $errstr) : "Could not connect to smtp host : $errno : $errstr";
		$err_msg .= ($error_contents) ? '<br /><br />' . htmlspecialchars($error_contents) : '';
		return false;
	}

	// Wait for reply
	if ($err_msg = $smtp->server_parse('220', __LINE__))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// Let me in. This function handles the complete authentication process
	if ($err_msg = $smtp->log_into_server($config['smtp_host'], $config['smtp_username'], htmlspecialchars_decode($config['smtp_password']), $config['smtp_auth_method']))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// From this point onward most server response codes should be 250
	// Specify who the mail is from....
	$smtp->server_send('MAIL FROM:<' . $config['board_email'] . '>');
	if ($err_msg = $smtp->server_parse('250', __LINE__))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// Specify each user to send to and build to header.
	$to_header = implode(', ', $mail_to);
	$cc_header = implode(', ', $mail_cc);

	// Now tell the MTA to send the Message to the following people... [TO, BCC, CC]
	$rcpt = false;
	foreach ($mail_rcpt as $type => $mail_to_addresses)
	{
		foreach ($mail_to_addresses as $mail_to_address)
		{
			// Add an additional bit of error checking to the To field.
			if (preg_match('#[^ ]+\@[^ ]+#', $mail_to_address))
			{
				$smtp->server_send("RCPT TO:$mail_to_address");
				if ($err_msg = $smtp->server_parse('250', __LINE__))
				{
					// We continue... if users are not resolved we do not care
					if ($smtp->numeric_response_code != 550)
					{
						$smtp->close_session($err_msg);
						return false;
					}
				}
				else
				{
					$rcpt = true;
				}
			}
		}
	}

	// We try to send messages even if a few people do not seem to have valid email addresses, but if no one has, we have to exit here.
	if (!$rcpt)
	{
		$user->session_begin();
		$err_msg .= '<br /><br />';
		$err_msg .= (isset($user->lang['INVALID_EMAIL_LOG'])) ? sprintf($user->lang['INVALID_EMAIL_LOG'], htmlspecialchars($mail_to_address)) : '<strong>' . htmlspecialchars($mail_to_address) . '</strong> possibly an invalid email address?';
		$smtp->close_session($err_msg);
		return false;
	}

	// Ok now we tell the server we are ready to start sending data
	$smtp->server_send('DATA');

	// This is the last response code we look for until the end of the message.
	if ($err_msg = $smtp->server_parse('354', __LINE__))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// Send the Subject Line...
	$smtp->server_send("Subject: $subject");

	// Now the To Header.
	$to_header = ($to_header == '') ? 'undisclosed-recipients:;' : $to_header;
	$smtp->server_send("To: $to_header");

	// Now the CC Header.
	if ($cc_header != '')
	{
		$smtp->server_send("CC: $cc_header");
	}

	// Now any custom headers....
	if ($headers !== false)
	{
		$smtp->server_send("$headers\r\n");
	}

	// Ok now we are ready for the message...
	$smtp->server_send($message);

	// Ok the all the ingredients are mixed in let's cook this puppy...
	$smtp->server_send('.');
	if ($err_msg = $smtp->server_parse('250', __LINE__))
	{
		$smtp->close_session($err_msg);
		return false;
	}

	// Now tell the server we are done and close the socket...
	$smtp->server_send('QUIT');
	$smtp->close_session($err_msg);

	return true;
}

/**
* SMTP Class
* Auth Mechanisms originally taken from the AUTH Modules found within the PHP Extension and Application Repository (PEAR)
*/
class smtp_class
{
	var $server_response = '';
	var $socket = 0;
	var $socket_tls = false;
	var $responses = array();
	var $commands = array();
	var $numeric_response_code = 0;

	var $backtrace = false;
	var $backtrace_log = array();

	function __construct()
	{
		// Always create a backtrace for admins to identify SMTP problems
		$this->backtrace = true;
		$this->backtrace_log = array();
	}

	/**
	* Add backtrace message for debugging
	*/
	function add_backtrace($message)
	{
		if ($this->backtrace)
		{
			$this->backtrace_log[] = utf8_htmlspecialchars($message);
		}
	}

	/**
	* Send command to smtp server
	*/
	function server_send($command, $private_info = false)
	{
		fputs($this->socket, $command . "\r\n");

		(!$private_info) ? $this->add_backtrace("# $command") : $this->add_backtrace('# Omitting sensitive information');

		// We could put additional code here
	}

	/**
	* We use the line to give the support people an indication at which command the error occurred
	*/
	function server_parse($response, $line)
	{
		global $user;

		$this->server_response = '';
		$this->responses = array();
		$this->numeric_response_code = 0;

		while (substr($this->server_response, 3, 1) != ' ')
		{
			if (!($this->server_response = fgets($this->socket, 256)))
			{
				return (isset($user->lang['NO_EMAIL_RESPONSE_CODE'])) ? $user->lang['NO_EMAIL_RESPONSE_CODE'] : 'Could not get mail server response codes';
			}
			$this->responses[] = substr(rtrim($this->server_response), 4);
			$this->numeric_response_code = (int) substr($this->server_response, 0, 3);

			$this->add_backtrace("LINE: $line <- {$this->server_response}");
		}

		if (!(substr($this->server_response, 0, 3) == $response))
		{
			$this->numeric_response_code = (int) substr($this->server_response, 0, 3);
			return (isset($user->lang['EMAIL_SMTP_ERROR_RESPONSE'])) ? sprintf($user->lang['EMAIL_SMTP_ERROR_RESPONSE'], $line, $this->server_response) : "Ran into problems sending Mail at <strong>Line $line</strong>. Response: $this->server_response";
		}

		return 0;
	}

	/**
	* Close session
	*/
	function close_session(&$err_msg)
	{
		fclose($this->socket);

		if ($this->backtrace)
		{
			$message = '<h1>Backtrace</h1><p>' . implode('<br />', $this->backtrace_log) . '</p>';
			$err_msg .= $message;
		}
	}

	/**
	* Log into server and get possible auth codes if neccessary
	*/
	function log_into_server($hostname, $username, $password, $default_auth_method)
	{
		global $config, $user;

		// Hello the server and parse its capabilities.
		if (($err_msg = $this->hello()) !== true)
		{
			return $err_msg;
		}

		// Handle STARTTLS extension according to RFC 3207.
		if (!$this->socket_tls && isset($this->commands['STARTTLS']) && !preg_match('#^(tls|ssl)(v[-_.\d\w]+)?://#i', $config['smtp_host']) && function_exists('stream_socket_enable_crypto'))
		{
			$this->server_send('STARTTLS');
			if ($err_msg = $this->server_parse('220', __LINE__))
			{
				return $err_msg;
			}

			$collector = new phpbb_error_collector;
			$collector->install();
			$this->socket_tls = stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
			$collector->uninstall();

			if (!$this->socket_tls)
			{
				$err_msg = 'STARTTLS: Could not enable TLS on a socket';
				if (count($collector->errors)) { $err_msg .= '<br /><br />' . htmlspecialchars($collector->format_errors()); }
				return $err_msg;
			}

			// Switched to TLS. According to RFC 3207, say hello again.
			if (($err_msg = $this->hello()) !== true)
			{
				return $err_msg;
			}
		}

		// If we are not authenticated yet, something might be wrong if no username and passwd passed
		if (!$username || !$password)
		{
			return false;
		}

		if (!isset($this->commands['AUTH']))
		{
			return (isset($user->lang['SMTP_NO_AUTH_SUPPORT'])) ? $user->lang['SMTP_NO_AUTH_SUPPORT'] : 'SMTP server does not support authentication';
		}

		// Get best authentication method
		$available_methods = explode(' ', $this->commands['AUTH']);

		// Allowed auth methods and their ordering if the default auth method was not found.
		$auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5', 'DIGEST-MD5');
		$method = '';

		if (in_array($default_auth_method, $auth_methods) && in_array($default_auth_method, $available_methods))
		{
			$method = $default_auth_method;
		}
		else
		{
			foreach ($auth_methods as $_method)
			{
				if (in_array($_method, $available_methods))
				{
					$method = $_method;
					break;
				}
			}
		}

		if (!$method)
		{
			return (isset($user->lang['NO_SUPPORTED_AUTH_METHODS'])) ? $user->lang['NO_SUPPORTED_AUTH_METHODS'] : 'No supported authentication methods';
		}

		$method = 'auth_' . strtolower(str_replace('-', '_', $method));
		return $this->$method($username, $password);
	}

	/**
	* Try to EHLO or HELO the server and parse its capabilities.
	*
	* @return mixed True if the authentication process is supposed to continue.
	*               False if already authenticated.
	*               Error string message otherwise.
	*/
	protected function hello()
	{
		static $local_host = null;

		// Prepare local host.
		if ($local_host === null)
		{
			global $user;

			// Here we try to determine the *real* hostname (reverse DNS entry preferrably).
			$local_host = $user->host;

			if (function_exists('php_uname'))
			{
				$local_host = php_uname('n');

				// Able to resolve name to IP.
				if (($addr = @gethostbyname($local_host)) !== $local_host)
				{
					// Able to resolve IP back to name.
					if (($name = @gethostbyaddr($addr)) !== $addr)
					{
						$local_host = $name;
					}
				}
			}
		}

		// Try EHLO first.
		$this->server_send("EHLO {$local_host}");
		if ($err_msg = $this->server_parse('250', __LINE__))
		{
			// 503 response code means that we're already authenticated.
			if ($this->numeric_response_code == 503)
			{
				return false;
			}

			// If EHLO fails, try HELO.
			$this->server_send("HELO {$local_host}");
			if ($err_msg = $this->server_parse('250', __LINE__))
			{
				return ($this->numeric_response_code == 503) ? false : $err_msg;
			}
		}

		// Parse response.
		$this->commands = array();
		foreach ($this->responses as $response)
		{
			$response = explode(' ', $response);
			$response_code = $response[0];
			unset($response[0]);
			$this->commands[$response_code] = implode(' ', $response);
		}

		return true;
	}

	/**
	* Plain authentication method
	*/
	protected function auth_plain($username, $password)
	{
		$this->server_send('AUTH PLAIN');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$base64_method_plain = base64_encode("\0" . $username . "\0" . $password);
		$this->server_send($base64_method_plain, true);
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}

	/**
	* Login authentication method
	*/
	protected function auth_login($username, $password)
	{
		$this->server_send('AUTH LOGIN');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$this->server_send(base64_encode($username), true);
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return $err_msg;
		}

		$this->server_send(base64_encode($password), true);
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}

	/**
	* cram_md5 authentication method
	*/
	protected function auth_cram_md5($username, $password)
	{
		$this->server_send('AUTH CRAM-MD5');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$md5_challenge = base64_decode($this->responses[0]);
		$password = (strlen($password) > 64) ? pack('H32', md5($password)) : ((strlen($password) < 64) ? str_pad($password, 64, chr(0)) : $password);
		$md5_digest = md5((substr($password, 0, 64) ^ str_repeat(chr(0x5C), 64)) . (pack('H32', md5((substr($password, 0, 64) ^ str_repeat(chr(0x36), 64)) . $md5_challenge))));

		$base64_method_cram_md5 = base64_encode($username . ' ' . $md5_digest);

		$this->server_send($base64_method_cram_md5, true);
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}

	/**
	* digest_md5 authentication method
	* A real pain in the ***
	*/
	protected function auth_digest_md5($username, $password)
	{
		global $config, $user;

		$this->server_send('AUTH DIGEST-MD5');
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return ($this->numeric_response_code == 503) ? false : $err_msg;
		}

		$md5_challenge = base64_decode($this->responses[0]);

		// Parse the md5 challenge - from AUTH_SASL (PEAR)
		$tokens = array();
		while (preg_match('/^([a-z-]+)=("[^"]+(?<!\\\)"|[^,]+)/i', $md5_challenge, $matches))
		{
			// Ignore these as per rfc2831
			if ($matches[1] == 'opaque' || $matches[1] == 'domain')
			{
				$md5_challenge = substr($md5_challenge, strlen($matches[0]) + 1);
				continue;
			}

			// Allowed multiple "realm" and "auth-param"
			if (!empty($tokens[$matches[1]]) && ($matches[1] == 'realm' || $matches[1] == 'auth-param'))
			{
				if (is_array($tokens[$matches[1]]))
				{
					$tokens[$matches[1]][] = preg_replace('/^"(.*)"$/', '\\1', $matches[2]);
				}
				else
				{
					$tokens[$matches[1]] = array($tokens[$matches[1]], preg_replace('/^"(.*)"$/', '\\1', $matches[2]));
				}
			}
			else if (!empty($tokens[$matches[1]])) // Any other multiple instance = failure
			{
				$tokens = array();
				break;
			}
			else
			{
				$tokens[$matches[1]] = preg_replace('/^"(.*)"$/', '\\1', $matches[2]);
			}

			// Remove the just parsed directive from the challenge
			$md5_challenge = substr($md5_challenge, strlen($matches[0]) + 1);
		}

		// Realm
		if (empty($tokens['realm']))
		{
			$tokens['realm'] = (function_exists('php_uname')) ? php_uname('n') : $user->host;
		}

		// Maxbuf
		if (empty($tokens['maxbuf']))
		{
			$tokens['maxbuf'] = 65536;
		}

		// Required: nonce, algorithm
		if (empty($tokens['nonce']) || empty($tokens['algorithm']))
		{
			$tokens = array();
		}
		$md5_challenge = $tokens;

		if (!empty($md5_challenge))
		{
			$str = '';
			for ($i = 0; $i < 32; $i++)
			{
				$str .= chr(mt_rand(0, 255));
			}
			$cnonce = base64_encode($str);

			$digest_uri = 'smtp/' . preg_replace('#^[-_.\d\w]+://#', '', $config['smtp_host']);

			$auth_1 = sprintf('%s:%s:%s', pack('H32', md5(sprintf('%s:%s:%s', $username, $md5_challenge['realm'], $password))), $md5_challenge['nonce'], $cnonce);
			$auth_2 = 'AUTHENTICATE:' . $digest_uri;
			$response_value = md5(sprintf('%s:%s:00000001:%s:auth:%s', md5($auth_1), $md5_challenge['nonce'], $cnonce, md5($auth_2)));

			$input_string = sprintf('username="%s",realm="%s",nonce="%s",cnonce="%s",nc="00000001",qop=auth,digest-uri="%s",response=%s,%d', $username, $md5_challenge['realm'], $md5_challenge['nonce'], $cnonce, $digest_uri, $response_value, $md5_challenge['maxbuf']);
		}
		else
		{
			return (isset($user->lang['INVALID_DIGEST_CHALLENGE'])) ? $user->lang['INVALID_DIGEST_CHALLENGE'] : 'Invalid digest challenge';
		}

		$base64_method_digest_md5 = base64_encode($input_string);
		$this->server_send($base64_method_digest_md5, true);
		if ($err_msg = $this->server_parse('334', __LINE__))
		{
			return $err_msg;
		}

		$this->server_send(' ');
		if ($err_msg = $this->server_parse('235', __LINE__))
		{
			return $err_msg;
		}

		return false;
	}
}

/**
* Encodes the given string for proper display in UTF-8 or US-ASCII.
*
* This version is based on iconv_mime_encode() implementation
* from symfomy/polyfill-iconv
* https://github.com/symfony/polyfill-iconv/blob/fd324208ec59a39ebe776e6e9ec5540ad4f40aaa/Iconv.php#L355
*
* @param string $str
* @param string $eol Lines delimiter (optional to be backwards compatible)
*
* @return string
*/
function mail_encode($str, $eol = "\r\n")
{
	// Check if string contains ASCII only characters
	$is_ascii = strlen($str) === utf8_strlen($str);

	$scheme = $is_ascii ? 'Q' : 'B';

	// Define start delimiter, end delimiter
	// Use the Quoted-Printable encoding for ASCII strings to avoid unnecessary encoding in Base64
	$start = '=?' . ($is_ascii ? 'US-ASCII' : 'UTF-8') . '?' . $scheme . '?';
	$end = '?=';

	// Maximum encoded-word length is 75 as per RFC 2047 section 2.
	// $split_length *must* be a multiple of 4, but <= 75 - strlen($start . $eol . $end)!!!
	$split_length = 75 - strlen($start . $eol . $end);
	$split_length = $split_length - $split_length % 4;

	$line_length = strlen($start) + strlen($end);
	$line_offset = strlen($start) + 1;
	$line_data = '';

	$is_quoted_printable = 'Q' === $scheme;

	preg_match_all('/./us', $str, $chars);
	$chars = $chars[0] ?? [];

	$str = [];
	foreach ($chars as $char)
	{
		$encoded_char = $is_quoted_printable
			? $char = preg_replace_callback(
				'/[()<>@,;:\\\\".\[\]=_?\x20\x00-\x1F\x80-\xFF]/',
				function ($matches)
				{
					$hex = dechex(ord($matches[0]));
					$hex = strlen($hex) == 1 ? "0$hex" : $hex;
					return '=' . strtoupper($hex);
				},
				$char
			)
			: base64_encode($line_data . $char);

		if (isset($encoded_char[$split_length - $line_length]))
		{
			if (!$is_quoted_printable)
			{
				$line_data = base64_encode($line_data);
			}
			$str[] = $start . $line_data . $end;
			$line_length = $line_offset;
			$line_data = '';
		}

		$line_data .= $char;
		$is_quoted_printable && $line_length += strlen($char);
	}

	if ($line_data !== '')
	{
		if (!$is_quoted_printable)
		{
			$line_data = base64_encode($line_data);
		}
		$str[] = $start . $line_data . $end;
	}

	return implode($eol . ' ', $str);
}

/**
* Wrapper for sending out emails with the PHP's mail function
*/
function phpbb_mail($to, $subject, $msg, $headers, $eol, &$err_msg)
{
	global $config, $phpbb_root_path;

	// We use the EOL character for the OS here because the PHP mail function does not correctly transform line endings. On Windows SMTP is used (SMTP is \r\n), on UNIX a command is used...
	// Reference: http://bugs.php.net/bug.php?id=15841
	$headers = implode($eol, $headers);

	if (!class_exists('phpbb_error_collector'))
	{
		require_once($phpbb_root_path . 'includes/error_collector.php');
	}

	$collector = new phpbb_error_collector;
	$collector->install();

	$sendmail_args = (isset($config['email_force_sender']) && $config['email_force_sender']) ? ('-f' . $config['board_email']) : '';

	// On some PHP Versions mail() *may* fail if there are newlines within the subject.
	// Newlines are used as a delimiter for lines in mail_encode() according to RFC 2045 section 6.8.
	// Because PHP can't decide what is wanted we revert back to the non-RFC-compliant way of separating by one space (Use '' as parameter to mail_encode() results in SPACE used)
	$result = mail($to, mail_encode($subject, ''), wordwrap(utf8_wordwrap($msg, 250), 997, "\n", true), $headers, $sendmail_args);

	$collector->uninstall();
	$err_msg = $collector->format_errors();

	return $result;
}
