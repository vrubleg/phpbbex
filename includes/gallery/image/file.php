<?php
/**
*
* @package NV Image Tools
* @copyright (c) 2009 nickvergessen
* @license GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* A little class for all the actions that the gallery does on images.
*
* resize, rotate, read exif, create thumbnail, write to hdd, send to browser
*/
class phpbb_gallery_image_file
{
	public $chmod = 0777;

	public $errors = [];
	private $browser_cache = true;
	private $last_modified = 0;

	public $exif_data_force_db = false;

	public $image;
	public $image_content_type;
	public $image_name = '';
	public $image_quality = 100;
	public $image_size = [];
	public $image_source = '';
	public $image_type;

	public $max_file_size = 0;
	public $max_height = 0;
	public $max_width = 0;

	public $resized = false;
	public $rotated = false;

	public $thumb_height = 0;
	public $thumb_width = 0;

	public function set_image_options($max_file_size, $max_height, $max_width)
	{
		$this->max_file_size = $max_file_size;
		$this->max_height = $max_height;
		$this->max_width = $max_width;
	}

	public function set_image_data($source = '', $name = '', $size = 0, $force_empty_image = false)
	{
		if ($source)
		{
			$this->image_source = $source;
		}
		if ($name)
		{
			$this->image_name = $name;
		}
		if ($size)
		{
			$this->image_size['file'] = $size;
		}
		if ($force_empty_image)
		{
			$this->image = null;
			$this->rotated = false;
			$this->resized = false;
			$this->exif_data_force_db = false;
		}
	}

	/**
	* Get image mimetype by filename
	*
	* Only use this, if the image is secure. As we created all these images, they should be...
	*/
	static public function mimetype_by_filename($filename)
	{
		switch (utf8_substr(strtolower($filename), -4))
		{
			case '.png':
				return 'image/png';
			break;
			case '.gif':
				return 'image/gif';
			break;
			case 'jpeg':
			case '.jpg':
				return 'image/jpeg';
			break;
		}

		return '';
	}

	/**
	* Read image
	*/
	public function read_image($force_filesize = false)
	{
		if (!file_exists($this->image_source))
		{
			return false;
		}

		switch (utf8_substr(strtolower($this->image_source), -4))
		{
			case '.png':
				$this->image = imagecreatefrompng($this->image_source);
				imagealphablending($this->image, true); // Set alpha blending on ...
				imagesavealpha($this->image, true); // ... and save alphablending!
				$this->image_type = 'png';
			break;
			case '.gif':
				$this->image = imagecreatefromgif($this->image_source);
				$this->image_type = 'gif';
			break;
			default:
				$this->image = imagecreatefromjpeg($this->image_source);
				$this->image_type = 'jpeg';
			break;
		}

		$file_size = 0;
		if (isset($this->image_size['file']))
		{
			$file_size = $this->image_size['file'];
		}
		else if ($force_filesize)
		{
			$file_size = @filesize($this->image_source);
		}

		$image_size = getimagesize($this->image_source);

		$this->image_size['file'] = $file_size;
		$this->image_size['width'] = $image_size[0];
		$this->image_size['height'] = $image_size[1];

		$this->image_content_type = $image_size['mime'];
	}

	/**
	* Write image to disk
	*/
	public function write_image($destination, $quality = 100, $destroy_image = false)
	{
		switch ($this->image_type)
		{
			case 'jpeg':
				@imagejpeg($this->image, $destination, $quality);
			break;
			case 'png':
				@imagepng($this->image, $destination);
			break;
			case 'gif':
				@imagegif($this->image, $destination);
			break;
		}
		@chmod($destination, $this->chmod);

		if ($destroy_image && PHP_VERSION_ID < 80000)
		{
			imagedestroy($this->image);
		}
	}

	/**
	* We need to disable the "last-modified" caching for guests and in cases of image-errors,
	* so that they can view them, if they logged in or the error was fixed.
	*/
	public function disable_browser_cache()
	{
		$this->browser_cache = false;
	}

	/**
	* Collect the last timestamp where something changed.
	* This must contain:
	*   - Last change of the file
	*   - Last change of user's permissions
	*   - Last change of user's groups
	*/
	public function set_last_modified($timestamp)
	{
		$this->last_modified = max($timestamp, $this->last_modified);
	}

	/**
	* Check if the browser has the file already and set the appropriate headers.
	* @returns false if a resend is in order.
	*/
	function set_modified_headers()
	{
		// let's see if we have to send the file at all
		$last_load = phpbb_parse_if_modified_since();
		if ($last_load !== false && $last_load >= $this->last_modified)
		{
			http_response_code(304);
			return true;
		}
		else
		{
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->last_modified) . ' GMT');
			header('Cache-Control: max-age=1, must-revalidate');
		}
		return false;
	}

	/**
	* Sending the image to the browser.
	* Mostly copied from phpBB::download/file.php
	*/
	public function send_image_to_browser($content_length = 0)
	{
		global $db, $user;

		if (!$this->image_content_type)
		{
			// We don't have the image, so we guess the mime_type by filename
			$this->image_content_type = $this->mimetype_by_filename($this->image_source);
			if (!$this->image_content_type)
			{
				trigger_error('NO_MIMETYPE_MATCHED');
			}
		}

		header('Pragma: public');
		header('Content-Type: ' . $this->image_content_type);
		header('X-Content-Type-Options: nosniff');
		header('Content-Disposition: inline');

		if ($content_length)
		{
			header('Content-Length: ' . $content_length);
		}

		garbage_collection();

		$cached = false;
		if ($this->browser_cache)
		{
			$this->set_last_modified(@filemtime($this->image_source));
			$cached = $this->set_modified_headers();
		}

		if ($cached)
		{
			return;
		}
		elseif ($this->image)
		{
			$image_function = 'image' . $this->image_type;
			$image_function($this->image);
		}
		else
		{
			// Try to deliver in chunks
			@set_time_limit(0);

			$fp = @fopen($this->image_source, 'rb');

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
				@readfile($this->image_source);
			}

			flush();
		}
	}

	public function create_thumbnail($max_width, $max_height, $print_details = false, $additional_height = 0, $image_size = [])
	{
		$this->resize_image($max_width, $max_height, (($print_details) ? $additional_height : 0));

		// Create image details credits to Dr.Death
		if ($print_details && sizeof($image_size))
		{
			$dimension_font = 1;
			$dimension_string = $image_size['width'] . "x" . $image_size['height'] . "(" . intval($image_size['file'] / 1024) . "KiB)";
			$dimension_colour = imagecolorallocate($this->image, 255, 255, 255);
			$dimension_height = imagefontheight($dimension_font);
			$dimension_width = imagefontwidth($dimension_font) * strlen($dimension_string);
			$dimension_x = ($this->image_size['width'] - $dimension_width) / 2;
			$dimension_y = $this->image_size['height'] + (($additional_height - $dimension_height) / 2);
			$black_background = imagecolorallocate($this->image, 0, 0, 0);
			imagefilledrectangle($this->image, 0, $this->thumb_height, $this->thumb_width, $this->thumb_height + $additional_height, $black_background);
			imagestring($this->image, 1, $dimension_x, $dimension_y, $dimension_string, $dimension_colour);
		}
	}

	public function resize_image($max_width, $max_height, $additional_height = 0)
	{
		if (!$this->image)
		{
			$this->read_image();
		}

		if (($this->image_size['height'] <= $max_height) && ($this->image_size['width'] <= $max_width))
		{
			// image is small enough, nothing to do here.
			return;
		}

		if (($this->image_size['height'] / $max_height) > ($this->image_size['width'] / $max_width))
		{
			$this->thumb_height = $max_height;
			$this->thumb_width  = round($max_width * (($this->image_size['width'] / $max_width) / ($this->image_size['height'] / $max_height)));
		}
		else
		{
			$this->thumb_height = round($max_height * (($this->image_size['height'] / $max_height) / ($this->image_size['width'] / $max_width)));
			$this->thumb_width  = $max_width;
		}

		$image_copy = @imagecreatetruecolor($this->thumb_width, $this->thumb_height + $additional_height);
		if ($this->image_type != 'jpeg')
		{
			imagealphablending($image_copy, false);
			imagesavealpha($image_copy, true);
			$transparent = imagecolorallocatealpha($image_copy, 255, 255, 255, 127);
			imagefilledrectangle($image_copy, 0, 0, $this->thumb_width, $this->thumb_height + $additional_height, $transparent);
		}

		imagecopyresampled($image_copy, $this->image, 0, 0, 0, 0, $this->thumb_width, $this->thumb_height, $this->image_size['width'], $this->image_size['height']);

		imagealphablending($image_copy, true);
		imagesavealpha($image_copy, true);
		$this->image = $image_copy;

		$this->image_size['height'] = $this->thumb_height;
		$this->image_size['width'] = $this->thumb_width;

		$this->resized = true;
		// We loose the exif data, so force to store them in the database
		$this->exif_data_force_db = true;
	}

	/**
	* Rotate the image
	* Usage optimized for 0°, 90°, 180° and 270° because of the height and width
	*/
	public function rotate_image($angle, $ignore_dimensions)
	{
		if (!function_exists('imagerotate'))
		{
			$this->errors[] = ['ROTATE_IMAGE_FUNCTION', $angle];
			return;
		}

		if (($angle <= 0) || (($angle % 90) != 0))
		{
			$this->errors[] = ['ROTATE_IMAGE_ANGLE', $angle];
			return;
		}

		if (!$this->image)
		{
			$this->read_image();
		}

		if ((($angle / 90) % 2) == 1)
		{
			// Left or Right, we need to switch the height and width
			if (!$ignore_dimensions && (($this->image_size['height'] > $this->max_width) || ($this->image_size['width'] > $this->max_height)))
			{
				// image would be to wide/high
				if ($this->image_size['height'] > $this->max_width)
				{
					$this->errors[] = ['ROTATE_IMAGE_WIDTH'];
				}
				if ($this->image_size['width'] > $this->max_height)
				{
					$this->errors[] = ['ROTATE_IMAGE_HEIGHT'];
				}
				return;
			}
			$new_width = $this->image_size['height'];
			$this->image_size['height'] = $this->image_size['width'];
			$this->image_size['width'] = $new_width;
		}

		$this->image = imagerotate($this->image, $angle, 0);

		$this->rotated = true;
		// We loose the exif data, so force to store them in the database
		$this->exif_data_force_db = true;
	}

	/**
	* Delete file from disc.
	*
	* @param    mixed       $files      String with filename or an array of filenames
	*                                   Array-Format: $image_id => $filename
	* @param    array       $locations  Array of valid url::path()s where the image should be deleted from
	*/
	static public function delete($files, $locations = ['thumbnail', 'medium', 'upload'])
	{
		if (!is_array($files))
		{
			$files = [1 => $files];
		}

		foreach ($files as $image_id => $file)
		{
			foreach ($locations as $location)
			{
				@unlink(phpbb_gallery_url::path($location) . $file);
			}
		}
	}
}
