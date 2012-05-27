<?php
/**
*
* @package phpBB3
* @version $Id: time_delta.php,v 1.001 2008/03/20 23:19:30 rxu Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
This class is based on  Delta_Russian class created by Dmitry Koterov, http://forum.dklab.ru
*/

class time_delta 
{
	// returns the associative array with date deltas.
	static private function getdelta($first, $last)
	{
		if ($last < $first) return false;

		// Solve H:M:S part.
		$hms = ($last - $first) % (3600 * 24);
		$delta['seconds'] = $hms % 60;
		$delta['minutes'] = floor($hms/60) % 60;
		$delta['hours']   = floor($hms/3600) % 60;

		// Now work only with date, delta time = 0.
		$last -= $hms;
		$f = getdate($first);
		$l = getdate($last); // the same daytime as $first!

		$dYear = $dMon = $dDay = 0;

		// Delta day. Is negative, month overlapping.
		$dDay += $l['mday'] - $f['mday'];
		if ($dDay < 0) 
		{
			$monlen = self::monthlength(date("Y", $first), date("m", $first));
			$dDay += $monlen;
			$dMon--;
		}
		$delta['mday'] = $dDay;

		// Delta month. If negative, year overlapping.
		$dMon += $l['mon'] - $f['mon'];
		if ($dMon < 0) 
		{
			$dMon += 12;
			$dYear --;
		}
		$delta['mon'] = $dMon;

		// Delta year.
		$dYear += $l['year'] - $f['year'];
		$delta['year'] = $dYear;
		
		return $delta;
	}

	// Makes the spellable phrase.
	static public function get_verbal($first_time, $last_time, $accuracy = false, $max_parts = false, $keep_zeros = false)
	{
		global $user;
		// Solve data delta.
		$delta = self::getdelta($first_time, $last_time);
		if (!$delta)
		{
			return false;
		}

		// Make spellable phrase.
		$parts = array();
		$parts_count = 0;
		foreach (array_reverse($delta) as $measure => $value) 
		{
			if ($max_parts && $max_parts <= $parts_count)
			{
				break;
			}
			if (!$value && (!$keep_zeros || !$parts_count)) 
			{
				if ($measure !== $accuracy)
				{
					if ($parts_count) $parts_count++;
					continue;
				}
				else if (count($parts))
				{
					break;
				}
			}
			$parts_count++;
			$parts[] = self::declension($value, $user->lang['D_' . strtoupper($measure)]);
			if ($measure === $accuracy)
			{
				break;
			}
		}
		return join(' ', $parts);
	}

	// Returns the length (in days) of the specified month.
	static private function monthlength($year, $mon)
	{
		$l = 28;
		while (checkdate($mon, $l+1, $year))
		{
			$l++;
		}
		return $l;
	}

	// Spell result in appropriate form depending on integer value,
	// i.e.: "1 answer", "2 answers", "13 answers", et cetera.
	// $int — an integer value.
	// $expressions is an array, i.e.: array("answer", "answers", "answers") 
	static private function declension($int, $expressions)
	{
	   settype($int, "integer");
	   $count = $int % 100;
	   if ($count >= 5 && $count <= 20)
	   {
		  $result = $int . ' ' . $expressions['2'];
	   }
	   else
	   {	   
		  $count = $count % 10;
		  if ($count == 1)
		  {
			 $result = $int . ' ' . $expressions['0'];
		  }
		  else if ($count >= 2 && $count <= 4)
		  {
			 $result = $int . ' ' . $expressions['1'];
		  }
		  else
		  {
			 $result = $int . ' ' . $expressions['2'];
		  }
	   }
	   return $result;
	}
}
?>