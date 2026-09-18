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

class phpbb_gallery_exif
{
	/**
	* Constants for the status of the exif-data.
	*/
	const UNAVAILABLE   = 0;
	const AVAILABLE     = 1;
	const UNKNOWN       = 2;
	const DBSAVED       = 3;

	/**
	* phpBB will treat the time from the exifdata like UTC.
	* If your images were taken with an other timezone, you can insert an offset here.
	* The offset is than counted on the timestamp before it is converted into the users time.
	*
	* Offset must be set in seconds.
	*/
	const TIME_OFFSET   = 0;

	/**
	* Is the function available?
	*/
	static public $function_exists = null;

	/**
	* Exif data array with all allowed groups and keys.
	*/
	public $data        = [];

	/**
	* Filtered data array. We don't have empty or invalid values here.
	*/
	public $prepared_data   = [];

	/**
	* Does the image have exif data?
	* Values see constant declaration at the beginning of the class.
	*/
	public $status      = self::UNKNOWN;

	/**
	* Status before the exif data was interpreted.
	*/
	public $orig_status = self::UNKNOWN;

	/**
	* Filtered data array, serialized to a string
	*/
	public $serialized  = '';

	/**
	* Full link to the image-file
	*/
	public $file        = '';

	/**
	* Image-ID, just needed to update the exif-status
	*/
	public $image_id    = false;

	/**
	* Constructor
	*
	* @param    string  $file       Full link to the image-file
	* @param    mixed   $image_id   False or integer
	*/
	public function __construct($file, $image_id = false)
	{
		if (self::$function_exists === null)
		{
			self::$function_exists = function_exists('exif_read_data');
		}
		if ($image_id)
		{
			$this->image_id = (int) $image_id;
		}

		$this->file = $file;
	}

	/**
	* Intepret the values from the database, and read the data if we don't have it.
	*
	* @param    int     $status     Value of a status constant (see beginning of the class)
	* @param    mixed   $data       Either an empty string or the serialized array of the exif from the database
	*/
	public function interpret($status, $data)
	{
		$this->orig_status = $status;
		$this->status = $status;
		if ($this->status == self::DBSAVED)
		{
			$this->data = $this->filter_data(unserialize($data));
		}
		elseif (($this->status == self::AVAILABLE) || ($this->status == self::UNKNOWN))
		{
			$this->read();
		}
	}

	/**
	* Read exif data from the image
	*/
	public function read()
	{
		if (!self::$function_exists || !$this->file || !file_exists($this->file))
		{
			return;
		}

		$this->data = $this->filter_data(@exif_read_data($this->file, 0, true));

		if (!empty($this->data))
		{
			$this->serialized = serialize($this->data);
			$this->status = self::DBSAVED;
		}
		else
		{
			$this->status = self::UNAVAILABLE;
		}

		if ($this->image_id)
		{
			$this->set_status();
		}
	}

	/**
	* Keep only the metadata displayed by the gallery.
	*/
	private function filter_data($data)
	{
		$filtered = [];
		if (!empty($data['EXIF']['DateTimeOriginal']))
		{
			$filtered['EXIF']['DateTimeOriginal'] = $data['EXIF']['DateTimeOriginal'];
		}
		if (!empty($data['IFD0']['Model']))
		{
			$filtered['IFD0']['Model'] = $data['IFD0']['Model'];
		}
		return $filtered;
	}

	/**
	* Validate and prepare the data, so we can send it into the template.
	*/
	private function prepare_data()
	{
		global $user;

		$this->prepared_data = [];
		if (isset($this->data["EXIF"]["DateTimeOriginal"]))
		{
			$timestamp_year = substr($this->data["EXIF"]["DateTimeOriginal"], 0, 4);
			$timestamp_month = substr($this->data["EXIF"]["DateTimeOriginal"], 5, 2);
			$timestamp_day = substr($this->data["EXIF"]["DateTimeOriginal"], 8, 2);
			$timestamp_hour = substr($this->data["EXIF"]["DateTimeOriginal"], 11, 2);
			$timestamp_minute = substr($this->data["EXIF"]["DateTimeOriginal"], 14, 2);
			$timestamp_second = substr($this->data["EXIF"]["DateTimeOriginal"], 17, 2);
			$timestamp = (int) @mktime($timestamp_hour, $timestamp_minute, $timestamp_second, $timestamp_month, $timestamp_day, $timestamp_year);
			if ($timestamp)
			{
				$this->prepared_data['exif_date'] = $user->format_date($timestamp + phpbb_gallery_exif::TIME_OFFSET);
			}
		}
		if (isset($this->data["IFD0"]["Model"]))
		{
			$this->prepared_data['exif_cam_model'] = ucwords($this->data["IFD0"]["Model"]);
		}
	}

	/**
	* Sends the exif into the template
	*
	* @param    string  $block          Name of the template loop the exifs are displayed in.
	*/
	public function send_to_template($block = 'exif_value')
	{
		$this->prepare_data();

		if (!empty($this->prepared_data))
		{
			global $template, $user;

			foreach ($this->prepared_data as $exif => $value)
			{
				$template->assign_block_vars($block, [
					'EXIF_NAME'         => $user->lang[strtoupper($exif)],
					'EXIF_VALUE'        => htmlspecialchars($value),
				]);
			}
		}
	}

	/**
	* Save the new exif status in the database
	*/
	public function set_status()
	{
		if (!$this->image_id || ($this->orig_status == $this->status))
		{
			return false;
		}

		global $db;

		$update_data = ($this->status == self::DBSAVED) ? ", image_exif_data = '" . $db->sql_escape($this->serialized) . "'" : '';
		$sql = 'UPDATE ' . GALLERY_IMAGES_TABLE . '
			SET image_has_exif = ' . $this->status . $update_data . '
			WHERE image_id = ' . $this->image_id;
		$db->sql_query($sql);
	}

}
