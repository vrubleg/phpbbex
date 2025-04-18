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
* Responsible for holding all file relevant information, as well as doing file-specific operations.
* The {@link fileupload fileupload class} can be used to upload several files, each of them being this object to operate further on.
*/
class filespec
{
	var $filename = '';
	var $realname = '';
	var $uploadname = '';
	var $mimetype = '';
	var $extension = '';
	var $filesize = 0;
	var $width = 0;
	var $height = 0;
	var $image_info = array();

	var $destination_file = '';
	var $destination_path = '';

	var $file_moved = false;
	var $init_error = false;
	var $local = false;

	var $error = array();

	var $upload = '';

	/**
	* File Class
	* @access private
	*/
	function __construct($upload_ary, $upload_namespace)
	{
		if (!isset($upload_ary))
		{
			$this->init_error = true;
			return;
		}

		$this->filename = $upload_ary['tmp_name'];
		$this->filesize = $upload_ary['size'];
		$name = $upload_ary['name'];
		$name = trim(utf8_htmlspecialchars(utf8_basename($name)));
		$this->realname = $this->uploadname = $name;
		$this->mimetype = $upload_ary['type'];

		// Opera adds the name to the mime type
		$this->mimetype	= (strpos($this->mimetype, '; name') !== false) ? str_replace(strstr($this->mimetype, '; name'), '', $this->mimetype) : $this->mimetype;

		if (!$this->mimetype)
		{
			$this->mimetype = 'application/octetstream';
		}

		$this->extension = strtolower($this->get_extension($this->realname));

		// Try to get real filesize from temporary folder (not always working) ;)
		$this->filesize = (@filesize($this->filename)) ? @filesize($this->filename) : $this->filesize;

		$this->width = $this->height = 0;
		$this->file_moved = false;

		$this->local = (isset($upload_ary['local_mode'])) ? true : false;
		$this->upload = $upload_namespace;
	}

	/**
	* Cleans destination filename
	*
	* @param real|unique|unique_ext $mode real creates a realname, filtering some characters, lowering every character. Unique creates an unique filename
	* @param string $prefix Prefix applied to filename
	* @access public
	*/
	function clean_filename($mode = 'unique', $prefix = '')
	{
		if ($this->init_error)
		{
			return;
		}

		switch ($mode)
		{
			case 'real':
				// Remove every extension from filename (to not let the mime bug being exposed)
				if (strpos($this->realname, '.') !== false)
				{
					$this->realname = substr($this->realname, 0, strpos($this->realname, '.'));
				}

				// Replace any chars which may cause us problems with _
				$bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|');

				$this->realname = rawurlencode(str_replace($bad_chars, '_', strtolower($this->realname)));
				$this->realname = preg_replace("/%(\w{2})/", '_', $this->realname);

				$this->realname = $prefix . $this->realname . '.' . $this->extension;
			break;

			case 'unique':
				$this->realname = $prefix . bin2hex(random_bytes(16));
			break;

			case 'avatar':
				$this->extension = strtolower($this->extension);
				if ($this->extension == 'jpeg') { $this->extension = 'jpg'; }
				$this->realname = $prefix . '.' . $this->extension;

			break;

			case 'unique_ext':
			default:
				$this->realname = $prefix . bin2hex(random_bytes(16)) . '.' . $this->extension;
			break;
		}
	}

	/**
	* Get property from file object
	*/
	function get($property)
	{
		if ($this->init_error || !isset($this->$property))
		{
			return false;
		}

		return $this->$property;
	}

	/**
	* Check if file is an image (mimetype)
	*
	* @return true if it is an image, false if not
	*/
	function is_image()
	{
		return (strpos($this->mimetype, 'image/') !== false) ? true : false;
	}

	/**
	* Check if the file got correctly uploaded
	*
	* @return true if it is a valid upload, false if not
	*/
	function is_uploaded()
	{
		if (!$this->local && !is_uploaded_file($this->filename))
		{
			return false;
		}

		if ($this->local && !file_exists($this->filename))
		{
			return false;
		}

		return true;
	}

	/**
	* Remove file
	*/
	function remove()
	{
		if ($this->file_moved)
		{
			@unlink($this->destination_file);
		}
	}

	/**
	* Get file extension
	*/
	function get_extension($filename)
	{
		if (strpos($filename, '.') === false)
		{
			return '';
		}

		$filename = explode('.', $filename);
		return array_pop($filename);
	}

	/**
	* Get mimetype. Utilize mime_content_type if the function exist.
	* Not used at the moment...
	*/
	function get_mimetype($filename)
	{
		$mimetype = '';

		if (function_exists('mime_content_type'))
		{
			$mimetype = mime_content_type($filename);
		}

		// Some browsers choke on a mimetype of application/octet-stream
		if (!$mimetype || $mimetype == 'application/octet-stream')
		{
			$mimetype = 'application/octetstream';
		}

		return $mimetype;
	}

	/**
	* Get filesize
	*/
	function get_filesize($filename)
	{
		return @filesize($filename);
	}


	/**
	* Check the first 256 bytes for forbidden content
	*/
	function check_content($disallowed_content)
	{
		if (empty($disallowed_content))
		{
			return true;
		}

		$fp = @fopen($this->filename, 'rb');

		if ($fp !== false)
		{
			$ie_mime_relevant = fread($fp, 256);
			fclose($fp);
			foreach ($disallowed_content as $forbidden)
			{
				if (stripos($ie_mime_relevant, '<' . $forbidden) !== false)
				{
					return false;
				}
			}
		}
		return true;
	}

	/**
	* Move file to destination folder. The PHPBB_ROOT_PATH will be applied to the destination path.
	*
	* @param string $destination_path Destination path, for example AVATAR_UPLOADS_PATH
	* @param bool $overwrite If set to true, an already existing file will be overwritten
	* @param string $chmod Is not used in phpBBex
	*
	* @access public
	*/
	function move_file($destination, $overwrite = false, $skip_image_check = false, $chmod = false)
	{
		global $user;

		if (sizeof($this->error))
		{
			return false;
		}

		// We need to trust the admin in specifying valid upload directories and an attacker not being able to overwrite it...
		$this->destination_path = PHPBB_ROOT_PATH . $destination;

		// Check if the destination path exist...
		if (!file_exists($this->destination_path))
		{
			@unlink($this->filename);
			return false;
		}

		$upload_mode = (@ini_get('open_basedir')) ? 'move' : 'copy';
		$upload_mode = ($this->local) ? 'local' : $upload_mode;
		$this->destination_file = $this->destination_path . '/' . utf8_basename($this->realname);

		// Check if the file already exist, else there is something wrong...
		if (file_exists($this->destination_file) && !$overwrite)
		{
			@unlink($this->filename);
		}
		else
		{
			if (file_exists($this->destination_file))
			{
				@unlink($this->destination_file);
			}

			switch ($upload_mode)
			{
				case 'copy':

					if (!@copy($this->filename, $this->destination_file))
					{
						if (!@move_uploaded_file($this->filename, $this->destination_file))
						{
							$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR'], $this->destination_file);
						}
					}

				break;

				case 'move':

					if (!@move_uploaded_file($this->filename, $this->destination_file))
					{
						if (!@copy($this->filename, $this->destination_file))
						{
							$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR'], $this->destination_file);
						}
					}

				break;

				case 'local':

					if (!@copy($this->filename, $this->destination_file))
					{
						$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR'], $this->destination_file);
					}

				break;
			}

			// Remove temporary filename
			@unlink($this->filename);

			if (sizeof($this->error))
			{
				return false;
			}

			@chmod($this->destination_file, 0666);
		}

		// Try to get real filesize from destination folder
		$this->filesize = (@filesize($this->destination_file)) ? @filesize($this->destination_file) : $this->filesize;

		if ($this->is_image() && !$skip_image_check)
		{
			$this->width = $this->height = 0;

			if (($this->image_info = @getimagesize($this->destination_file)) !== false)
			{
				$this->width = $this->image_info[0];
				$this->height = $this->image_info[1];

				if (!empty($this->image_info['mime']))
				{
					$this->mimetype = $this->image_info['mime'];
				}

				// Check image type
				$types = $this->upload->image_types();

				if (!isset($types[$this->image_info[2]]) || !in_array($this->extension, $types[$this->image_info[2]]))
				{
					if (!isset($types[$this->image_info[2]]))
					{
						$this->error[] = sprintf($user->lang['IMAGE_FILETYPE_INVALID'], $this->image_info[2], $this->mimetype);
					}
					else
					{
						$this->error[] = sprintf($user->lang['IMAGE_FILETYPE_MISMATCH'], $types[$this->image_info[2]][0], $this->extension);
					}
				}

				// Make sure the dimensions match a valid image
				if (empty($this->width) || empty($this->height))
				{
					$this->error[] = $user->lang['ATTACHED_IMAGE_NOT_IMAGE'];
				}
			}
			else
			{
				$this->error[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
			}
		}

		$this->file_moved = true;
		$this->additional_checks();
		unset($this->upload);

		return true;
	}

	/**
	* Performing additional checks
	*/
	function additional_checks()
	{
		global $user;

		if (!$this->file_moved)
		{
			return false;
		}

		// Filesize is too big or it's 0 if it was larger than the maxsize in the upload form
		if ($this->upload->max_filesize && ($this->get('filesize') > $this->upload->max_filesize || $this->filesize == 0))
		{
			$max_filesize = get_formatted_filesize($this->upload->max_filesize, false);

			$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']);

			return false;
		}

		if (!$this->upload->valid_dimensions($this))
		{
			$this->error[] = sprintf($user->lang[$this->upload->error_prefix . 'WRONG_SIZE'], $this->upload->min_width, $this->upload->min_height, $this->upload->max_width, $this->upload->max_height, $this->width, $this->height);

			return false;
		}

		return true;
	}
}

/**
* Class for assigning error messages before a real filespec class can be assigned
*
* @package phpBB3
*/
class fileerror extends filespec
{
	function __construct($error_msg)
	{
		$this->error[] = $error_msg;
	}
}

/**
* File upload class
* Init class (all parameters optional and able to be set/overwritten separately) - scope is global and valid for all uploads
*
* @package phpBB3
*/
class fileupload
{
	var $allowed_extensions = array();
	var $disallowed_content = array('body', 'head', 'html', 'img', 'plaintext', 'a href', 'pre', 'script', 'table', 'title');
	var $max_filesize = 0;
	var $min_width = 0;
	var $min_height = 0;
	var $max_width = 0;
	var $max_height = 0;
	var $error_prefix = '';

	/** @var int Timeout for remote upload */
	var $upload_timeout = 6;

	/**
	* Init file upload class.
	*
	* @param string $error_prefix Used error messages will get prefixed by this string
	* @param array $allowed_extensions Array of allowed extensions, for example array('jpg', 'jpeg', 'gif', 'png')
	* @param int $max_filesize Maximum filesize
	* @param int $min_width Minimum image width (only checked for images)
	* @param int $min_height Minimum image height (only checked for images)
	* @param int $max_width Maximum image width (only checked for images)
	* @param int $max_height Maximum image height (only checked for images)
	*
	*/
	function __construct($error_prefix = '', $allowed_extensions = false, $max_filesize = false, $min_width = false, $min_height = false, $max_width = false, $max_height = false, $disallowed_content = false)
	{
		$this->set_allowed_extensions($allowed_extensions);
		$this->set_max_filesize($max_filesize);
		$this->set_allowed_dimensions($min_width, $min_height, $max_width, $max_height);
		$this->set_error_prefix($error_prefix);
		$this->set_disallowed_content($disallowed_content);
	}

	/**
	* Reset vars
	*/
	function reset_vars()
	{
		$this->max_filesize = 0;
		$this->min_width = $this->min_height = $this->max_width = $this->max_height = 0;
		$this->error_prefix = '';
		$this->allowed_extensions = array();
		$this->disallowed_content = array();
	}

	/**
	* Set allowed extensions
	*/
	function set_allowed_extensions($allowed_extensions)
	{
		if ($allowed_extensions !== false && is_array($allowed_extensions))
		{
			$this->allowed_extensions = $allowed_extensions;
		}
	}

	/**
	* Set allowed dimensions
	*/
	function set_allowed_dimensions($min_width, $min_height, $max_width, $max_height)
	{
		$this->min_width = (int) $min_width;
		$this->min_height = (int) $min_height;
		$this->max_width = (int) $max_width;
		$this->max_height = (int) $max_height;
	}

	/**
	* Set maximum allowed filesize
	*/
	function set_max_filesize($max_filesize)
	{
		if ($max_filesize !== false && (int) $max_filesize)
		{
			$this->max_filesize = (int) $max_filesize;
		}
	}

	/**
	* Set disallowed strings
	*/
	function set_disallowed_content($disallowed_content)
	{
		if ($disallowed_content !== false && is_array($disallowed_content))
		{
			$this->disallowed_content = array_diff($disallowed_content, array(''));
		}
	}

	/**
	* Set error prefix
	*/
	function set_error_prefix($error_prefix)
	{
		$this->error_prefix = $error_prefix;
	}

	/**
	* Form upload method
	* Upload file from users harddisk
	*
	* @param string $form_name Form name assigned to the file input field (if it is an array, the key has to be specified)
	* @return object $file Object "filespec" is returned, all further operations can be done with this object
	* @access public
	*/
	function form_upload($form_name)
	{
		global $user;

		unset($_FILES[$form_name]['local_mode']);
		$file = new filespec($_FILES[$form_name], $this);

		if ($file->init_error)
		{
			$file->error[] = '';
			return $file;
		}

		// Error array filled?
		if (isset($_FILES[$form_name]['error']))
		{
			$error = $this->assign_internal_error($_FILES[$form_name]['error']);

			if ($error !== false)
			{
				$file->error[] = $error;
				return $file;
			}
		}

		// Check if empty file got uploaded (not catched by is_uploaded_file)
		if (isset($_FILES[$form_name]['size']) && $_FILES[$form_name]['size'] == 0)
		{
			$file->error[] = $user->lang[$this->error_prefix . 'EMPTY_FILEUPLOAD'];
			return $file;
		}

		// PHP Upload filesize exceeded
		if ($file->get('filename') == 'none')
		{
			$max_filesize = @ini_get('upload_max_filesize');
			$unit = 'MB';

			if (!empty($max_filesize))
			{
				$unit = strtolower(substr($max_filesize, -1, 1));
				$max_filesize = (int) $max_filesize;

				$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
			}

			$file->error[] = (empty($max_filesize)) ? $user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : sprintf($user->lang[$this->error_prefix . 'PHP_SIZE_OVERRUN'], $max_filesize, $user->lang[$unit]);
			return $file;
		}

		// Not correctly uploaded
		if (!$file->is_uploaded())
		{
			$file->error[] = $user->lang[$this->error_prefix . 'NOT_UPLOADED'];
			return $file;
		}

		$this->common_checks($file);

		return $file;
	}

	/**
	* Move file from another location to phpBB
	*/
	function local_upload($source_file, $filedata = false)
	{
		global $user;

		$form_name = 'local';

		$_FILES[$form_name]['local_mode'] = true;
		$_FILES[$form_name]['tmp_name'] = $source_file;

		if ($filedata === false)
		{
			$_FILES[$form_name]['name'] = utf8_basename($source_file);
			$_FILES[$form_name]['size'] = 0;
			$mimetype = '';

			if (function_exists('mime_content_type'))
			{
				$mimetype = mime_content_type($source_file);
			}

			// Some browsers choke on a mimetype of application/octet-stream
			if (!$mimetype || $mimetype == 'application/octet-stream')
			{
				$mimetype = 'application/octetstream';
			}

			$_FILES[$form_name]['type'] = $mimetype;
		}
		else
		{
			$_FILES[$form_name]['name'] = $filedata['realname'];
			$_FILES[$form_name]['size'] = $filedata['size'];
			$_FILES[$form_name]['type'] = $filedata['type'];
		}

		$file = new filespec($_FILES[$form_name], $this);

		if ($file->init_error)
		{
			$file->error[] = '';
			return $file;
		}

		if (isset($_FILES[$form_name]['error']))
		{
			$error = $this->assign_internal_error($_FILES[$form_name]['error']);

			if ($error !== false)
			{
				$file->error[] = $error;
				return $file;
			}
		}

		// PHP Upload filesize exceeded
		if ($file->get('filename') == 'none')
		{
			$max_filesize = @ini_get('upload_max_filesize');
			$unit = 'MB';

			if (!empty($max_filesize))
			{
				$unit = strtolower(substr($max_filesize, -1, 1));
				$max_filesize = (int) $max_filesize;

				$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
			}

			$file->error[] = (empty($max_filesize)) ? $user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : sprintf($user->lang[$this->error_prefix . 'PHP_SIZE_OVERRUN'], $max_filesize, $user->lang[$unit]);
			return $file;
		}

		// Not correctly uploaded
		if (!$file->is_uploaded())
		{
			$file->error[] = $user->lang[$this->error_prefix . 'NOT_UPLOADED'];
			return $file;
		}

		$this->common_checks($file);

		return $file;
	}

	/**
	* Remote upload method
	* Uploads file from given url
	*
	* @param string $upload_url URL pointing to file to upload, for example http://www.foobar.com/example.gif
	* @return object $file Object "filespec" is returned, all further operations can be done with this object
	* @access public
	*/
	function remote_upload($upload_url)
	{
		global $user;

		$upload_ary = array();
		$upload_ary['local_mode'] = true;

		if (!preg_match('#^(https?://).*?\.(' . implode('|', $this->allowed_extensions) . ')$#i', $upload_url, $match))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'URL_INVALID']);
			return $file;
		}

		if (empty($match[2]))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'URL_INVALID']);
			return $file;
		}

		$url = parse_url($upload_url);

		$host = $url['host'];
		$path = $url['path'];
		$port = (!empty($url['port'])) ? (int) $url['port'] : 80;

		$upload_ary['type'] = 'application/octet-stream';

		$url['path'] = explode('.', $url['path']);
		$ext = array_pop($url['path']);

		$url['path'] = implode('', $url['path']);
		$upload_ary['name'] = utf8_basename($url['path']) . (($ext) ? '.' . $ext : '');
		$filename = $url['path'];
		$filesize = 0;

		$remote_max_filesize = $this->max_filesize;
		if (!$remote_max_filesize)
		{
			$max_filesize = @ini_get('upload_max_filesize');

			if (!empty($max_filesize))
			{
				$unit = strtolower(substr($max_filesize, -1, 1));
				$remote_max_filesize = (int) $max_filesize;

				switch ($unit)
				{
					case 'g':
						$remote_max_filesize *= 1024;
					// no break
					case 'm':
						$remote_max_filesize *= 1024;
					// no break
					case 'k':
						$remote_max_filesize *= 1024;
					// no break
				}
			}
		}

		$stop_at = time() + $this->upload_timeout;
		$errno = 0;
		$errstr = '';

		if (!($fsock = @fsockopen($host, $port, $errno, $errstr, max(1, intval($this->upload_timeout / 2)))))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'NOT_UPLOADED']);
			return $file;
		}

		// Make sure $path not beginning with /
		if (strpos($path, '/') === 0)
		{
			$path = substr($path, 1);
		}

		fputs($fsock, 'GET /' . $path . " HTTP/1.1\r\n");
		fputs($fsock, "HOST: " . $host . "\r\n");
		fputs($fsock, "Connection: close\r\n\r\n");

		// Set a proper timeout for the socket
		socket_set_timeout($fsock, $this->upload_timeout);

		$get_info = false;
		$data = '';
		$length = false;

		while ((!$length || $filesize < $length) && !@feof($fsock))
		{
			// Cancel upload if we exceed timeout
			if (time() >= $stop_at)
			{
				$file = new fileerror($user->lang[$this->error_prefix . 'REMOTE_UPLOAD_TIMEOUT']);
				return $file;
			}

			if ($get_info)
			{
				if ($length)
				{
					// Don't attempt to read past end of file if server indicated length
					$block = @fread($fsock, min($length - $filesize, 1024));
				}
				else
				{
					$block = @fread($fsock, 1024);
				}

				$filesize += strlen($block);

				if ($remote_max_filesize && $filesize > $remote_max_filesize)
				{
					$max_filesize = get_formatted_filesize($remote_max_filesize, false);

					$file = new fileerror(sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']));
					return $file;
				}

				$data .= $block;
			}
			else
			{
				$line = @fgets($fsock, 1024);

				if ($line == "\r\n")
				{
					$get_info = true;
				}
				else
				{
					if (stripos($line, 'content-type: ') !== false)
					{
						$upload_ary['type'] = rtrim(str_replace('content-type: ', '', strtolower($line)));
					}
					else if ($this->max_filesize && stripos($line, 'content-length: ') !== false)
					{
						$length = (int) str_replace('content-length: ', '', strtolower($line));

						if ($remote_max_filesize && $length && $length > $remote_max_filesize)
						{
							$max_filesize = get_formatted_filesize($remote_max_filesize, false);

							$file = new fileerror(sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']));
							return $file;
						}
					}
					else if (stripos($line, '404 not found') !== false)
					{
						$file = new fileerror($user->lang[$this->error_prefix . 'URL_NOT_FOUND']);
						return $file;
					}
				}
			}
		}
		@fclose($fsock);

		if (empty($data))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'EMPTY_REMOTE_DATA']);
			return $file;
		}

		$filename = tempnam(sys_get_temp_dir(), unique_id() . '-');

		if (!($fp = @fopen($filename, 'wb')))
		{
			$file = new fileerror($user->lang[$this->error_prefix . 'NOT_UPLOADED']);
			return $file;
		}

		$upload_ary['size'] = fwrite($fp, $data);
		fclose($fp);
		unset($data);

		$upload_ary['tmp_name'] = $filename;

		$file = new filespec($upload_ary, $this);
		$this->common_checks($file);

		return $file;
	}

	/**
	* Assign internal error
	* @access private
	*/
	function assign_internal_error($errorcode)
	{
		global $user;

		switch ($errorcode)
		{
			case 1:
				$max_filesize = @ini_get('upload_max_filesize');
				$unit = 'MB';

				if (!empty($max_filesize))
				{
					$unit = strtolower(substr($max_filesize, -1, 1));
					$max_filesize = (int) $max_filesize;

					$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
				}

				$error = (empty($max_filesize)) ? $user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : sprintf($user->lang[$this->error_prefix . 'PHP_SIZE_OVERRUN'], $max_filesize, $user->lang[$unit]);
			break;

			case 2:
				$max_filesize = get_formatted_filesize($this->max_filesize, false);

				$error = sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']);
			break;

			case 3:
				$error = $user->lang[$this->error_prefix . 'PARTIAL_UPLOAD'];
			break;

			case 4:
				$error = $user->lang[$this->error_prefix . 'NOT_UPLOADED'];
			break;

			case 6:
				$error = 'Temporary folder could not be found. Please check your PHP installation.';
			break;

			default:
				$error = false;
			break;
		}

		return $error;
	}

	/**
	* Perform common checks
	*/
	function common_checks(&$file)
	{
		global $user;

		// Filesize is too big or it's 0 if it was larger than the maxsize in the upload form
		if ($this->max_filesize && ($file->get('filesize') > $this->max_filesize || $file->get('filesize') == 0))
		{
			$max_filesize = get_formatted_filesize($this->max_filesize, false);

			$file->error[] = sprintf($user->lang[$this->error_prefix . 'WRONG_FILESIZE'], $max_filesize['value'], $max_filesize['unit']);
		}

		// check Filename
		if (preg_match("#[\\/:*?\"<>|]#i", $file->get('realname')))
		{
			$file->error[] = sprintf($user->lang[$this->error_prefix . 'INVALID_FILENAME'], $file->get('realname'));
		}

		// Invalid Extension
		if (!$this->valid_extension($file))
		{
			$file->error[] = sprintf($user->lang[$this->error_prefix . 'DISALLOWED_EXTENSION'], $file->get('extension'));
		}

		// MIME Sniffing
		if (!$this->valid_content($file))
		{
			$file->error[] = sprintf($user->lang[$this->error_prefix . 'DISALLOWED_CONTENT']);
		}
	}

	/**
	* Check for allowed extension
	*/
	function valid_extension(&$file)
	{
		return (in_array($file->get('extension'), $this->allowed_extensions)) ? true : false;
	}

	/**
	* Check for allowed dimension
	*/
	function valid_dimensions(&$file)
	{
		if (!$this->max_width && !$this->max_height && !$this->min_width && !$this->min_height)
		{
			return true;
		}

		if (($file->get('width') > $this->max_width && $this->max_width) ||
			($file->get('height') > $this->max_height && $this->max_height) ||
			($file->get('width') < $this->min_width && $this->min_width) ||
			($file->get('height') < $this->min_height && $this->min_height))
		{
			return false;
		}

		return true;
	}

	/**
	* Check if form upload is valid
	*/
	function is_valid($form_name)
	{
		return (isset($_FILES[$form_name]) && $_FILES[$form_name]['name'] != 'none') ? true : false;
	}


	/**
	* Check for allowed extension
	*/
	function valid_content(&$file)
	{
		return ($file->check_content($this->disallowed_content));
	}

	/**
	* Return image type/extension mapping
	*/
	function image_types()
	{
		return array(
			1 => array('gif'),
			2 => array('jpg', 'jpeg'),
			3 => array('png'),
			4 => array('swf'),
			5 => array('psd'),
			6 => array('bmp'),
			7 => array('tif', 'tiff'),
			8 => array('tif', 'tiff'),
			9 => array('jpg', 'jpeg'),
			10 => array('jpg', 'jpeg'),
			11 => array('jpg', 'jpeg'),
			12 => array('jpg', 'jpeg'),
			13 => array('swc'),
			14 => array('iff'),
			15 => array('wbmp'),
			16 => array('xbm'),
		);
	}
}
