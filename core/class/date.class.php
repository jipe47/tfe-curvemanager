<?php

/**
 * Allows the manipulation of dates.
 *
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage Misc
 */
class Date
{
	/**
	 * Returns a (possibly short) month name based on it's number.
	 * @param int $number Month number.
	 * @param boolean $short If set, returns a short version of the month.
	 */
	
	public static function getMonth($number, $short = false)
	{
		$l = $short ? "M" : "F";
		return date($l, mktime(0, 0, 0, intval($number), 1, 1990));
	}
	public static function getHour($timestamp)
	{
		return date("H", intval($timestamp)). "h".date("i", intval($timestamp));
	}
	
	public static function getDiff($t1, $t2)
	{
		$diff = $t1 > $t2 ? $t1 - $t2 : $t2 - $t1;
		$second = $diff % 60;
		$minute = round(($diff% 3600) / 60);
		$hour = round(($diff % (3600 * 24)) / 3600);
		$day = round(($diff % (3600 * 24 * 365)) / (24 * 3600));
		$year = round($diff / (3600 * 24 * 365));
		return array(	'second' => $second,
						'minute' => $minute,
						'hour' => $hour,
						'day' => $day,
						'year' => $year			
						);
	}
}

?>