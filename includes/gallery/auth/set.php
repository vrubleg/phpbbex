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

class phpbb_gallery_auth_set
{
	private $_bits = 0;

	private $_counts = [
		'i_count'	=> 0,
		'a_count'	=> 0,
	];

	public function __construct($bits = 0, $i_count = 0, $a_count = 0)
	{
		$this->_bits = $bits;

		$this->_counts = [
			'i_count'	=> $i_count,
			'a_count'	=> $a_count,
		];
	}

	public function set_bit($bit, $set)
	{
		$this->_bits = phpbb_optionset($bit, $set, $this->_bits);
	}

	public function get_bit($bit)
	{
		return phpbb_optionget($bit, $this->_bits);
	}

	public function get_bits()
	{
		return $this->_bits;
	}

	public function set_count($data, $set)
	{
		$this->_counts[$data] = (int) $set;
	}

	public function get_count($data)
	{
		return (int) $this->_counts[$data];
	}
}
