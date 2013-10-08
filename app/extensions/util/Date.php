<?php
/*
* @author  Cosmin Mitroi
*/

namespace app\extensions\util;

use lithium\security\Auth;

use \DateTime;
use \DateTimeZone;

class Date
{
	const DEFAULT_FORMAT = 'M j, Y';
	const SHORT_FORMAT   = 'Y-m-d';
	const LONG_FORMAT    = 'Y-m-d H:i:s';
	const NEW_FORMAT     = 'd/m/Y';
	const COMM_FORMAT    = 'M j, Y \a\t g:ia';
	const USER_FORMT     = 'd.m.Y';
	
	public static $formats = array(
		'short'   => self::SHORT_FORMAT,
		'long'    => self::LONG_FORMAT,
		'new'     => self::NEW_FORMAT,
		'comm'    => self::COMM_FORMAT,
		'user'    => self::USER_FORMT,
		'default' => self::DEFAULT_FORMAT,
	);
	
	public static function today($offset = 0)
	{
		$offset    = (int) $offset;
		$timestamp = mktime(0, 0, 0, date('n'), date('j') + $offset, date('Y'));
		
		return $timestamp;
	}
	
	public static function currentDate($timezone)
	{

		$userTimeZone = $timezone;
		$timezone = new DateTimeZone($userTimeZone);
		$current  = new DateTime('NOW');
		$current->setTimeZone($timezone);
		
		return $current->format('Y-m-d H:i:s');
	}
	/*
	* @return  date formatted according to given format.
	* @author  Cosmin Mitroi
	*/	
	public static function format($datetime, $format = 'default')
	{
		$timezone = 'GMT';
		
		try {

			$timezone = new DateTimeZone($timezone);
			$datetime = new DateTime($datetime,$timezone);
			
		} catch (Exception $e) {
			return NULL;
		}
		
		if (array_key_exists($format, static::$formats)) {
			$format = static::$formats[$format];
		}
		
		return $datetime->format($format);
	}
	
	public static function dateByTimeZone($date,$timezone)
	{
		$dateTimeZone = new DateTimeZone($timezone) ;
		$newDate      = new DateTime($date,$dateTimeZone);

		return $newDate;
	}
		
	/*
	* Determines the difference between two timestamps.
	* A time difference function that outputs the time passed in facebook's style: 29 minutes ago, 1 day ago, or 4 months ago.
	* @return string Human readable time difference.
	* @author  Cosmin Mitroi
	*/
	public function humanizeDateDiff($date) {

		$user = Auth::check('member');
		$userTimezone = UsersDetails::first($user['id']);
		
		$timezone = !empty($userTimezone->timezone_name) ? $userTimezone->timezone_name : 'GMT';
		
		$now       = strtotime(self::currentDate($timezone));
		$comm_date = strtotime($date);
		
		$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths = array("60","60","24","7","4.35","12","10");
		
		// check validity of date
		if(empty($comm_date)) {
			return "Bad date";
		}
		
		// is it future date or past date
		if ($now > $comm_date) {
			$difference = $now - $comm_date;
			$tense      = "ago";
		} else {
			$difference = $comm_date - $now;
			$tense      = "from now";
		}
		
		for ($i = 0; $difference >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
			$difference /= $lengths[$i];
		}
		$difference = round($difference);
		
		if ($difference != 1) {
			$periods[$i] .= "s";
		}
		
		return "$difference $periods[$i] {$tense}";
	}
	
  }

?>