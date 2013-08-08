<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common;

use DateInterval;
use DateTimeZone;
use DateTime;
use DateTimeInterface;

/**
 * Date Class
 *
 * DateTime drop-in replacement that supports internationalization and does
 * correction to GMT when your webserver isn't configured correctly.
 *
 * @package  Fuel\Common
 * @since  1.0.0
 *
 * Notes:
 * - Always returns Date objects, will accept both Date objects and UNIX timestamps
 * - Uses either strptime() or DateTime to create a date from a format, depending on the format
 * - Uses strftime formatting for dates www.php.net/manual/en/function.strftime.php
 */
class Date implements DateTimeInterface
{
	/**
	 * @var  DateTime object warnings and errors
	 */
	protected static $lastErrors = array();

	/**
	 * @var  DateTimeZone  default timezone
	 */
	protected static $defaultTimezone;

	/**
	 * @var  string  encoding to use for strftime()
	 */
	protected static $encoding = null;

	/**
	 * @var  int  any GMT offset for ill configured servers (application timezone !== server timezone)
	 */
	protected static $gmtOffset = 0;

	/**
	 * @var  array  custom defined datetime formats
	 */
	protected static $patterns = array(
	);

	/**
	 * Returns new Date object formatted according to the specified format. The
	 * method supports both strptime() formats and DateTime formats
	 *
	 * @param  string               $format  any format supported by DateTime, or a format name configured
	 * @param  string               $time    string representing the time.
	 * @param  string|DateTimeZone  $time    timezone, if null the default timezone will be used
	 *
	 * @return  bool|Date  new Date instance, or false on failure
	 */
	public static function createFromFormat($format = 'local', $time, $timezone = null)
	{
		// deal with the timezone passed
		if ($timezone !== null)
		{
			if ( ! $timezone instanceOf DateTimeZone)
			{
				if ( ! $timezone = new DateTimeZone($timezone))
				{
					return false;
				}
			}
		}
		else
		{
			$timezone = static::defaultTimezone();
		}

		// custom format pattern lookup
		if (array_key_exists($format, static::$patterns))
		{
			$format = static::$patterns[$format];
		}

		// do we have a possible strptime() format to work with?
		if (strpos($format, '%') !== false and $timestamp = strptime($time, $format))
		{
			$time = mktime($timestamp['tm_hour'], $timestamp['tm_min'],
				$timestamp['tm_sec'], $timestamp['tm_mon'] + 1,
				$timestamp['tm_mday'], $timestamp['tm_year'] + 1900);

			if ($time === false)
			{
				throw new \OutOfBoundsException('Input was invalid.'.(PHP_INT_SIZE == 4?' A 32-bit system only supports dates between 1901 and 2038.':''));
			}
			$format = 'U';
		}
		try
		{
			if ($datetime = DateTime::createFromFormat($format, $time, $timezone))
			{
				$datetime = new static('@'.$datetime->getTimestamp(), $timezone);
			}
		}
		catch (\Exception $e)
		{
			static::$lastErrors = DateTime::getLastErrors();
			throw $e;
		}

		if ( ! $datetime)
		{
			return false;
		}

		return $datetime;
	}

	/**
	 * Returns the warnings and errors from the last parsing operation
	 *
	 * @return  array  array of warnings and errors found while parsing a date/time string.
	 */
	public static function getLastErrors()
	{
		return static::$lastErrors;
	}

	/**
	 * Sets the default timezone, the current display timezone of the application
	 *
	 * @return  DateTimeZone
	 */
	public static function defaultTimezone($timezone = null)
	{
		if ($timezone !== null)
		{
			if ( ! $timezone instanceOf DateTimeZone)
			{
				if ( ! $timezone = new DateTimeZone($timezone))
				{
					return false;
				}
			}

			static::$defaultTimezone = $timezone;
		}

		return static::$defaultTimezone;
	}

	/**
	 * what does this do?
	 */
	public static function __set_state(Array $array)
	{
		throw new \RuntimeException('static function __set_state() not supported on a FuelPHP Date object');
	}

	/**
	 * @var  DateTime  object used internally to store the datetime
	 */
	protected $datetime;

	/**
	 * Constructor, create a new Date object using the information passed
	 *
	 * @param  string|int           $time      date/time as a UNIX timestamp or a string
	 * @param  string|DateTimeZone  $timezone  timezone $time is in
	 * @param  array                $config    any custom configuration for this object
	 *
	 * @throws  Exception  if the time passed is not valid
	 */
	public function __construct($time = "now", $timezone = null, Array $config = array())
	{
		// any GMT offset defined?
		if (isset($config['gmtOffset']))
		{
			static::$gmtOffset = $config['gmtOffset'];
		}

		// any encoding defined?
		if (isset($config['encoding']))
		{
			static::$encoding = strtoupper($config['encoding']);
		}

		// do we have a default timezone?
		if (isset($config['defaultTimezone']))
		{
			static::defaultTimezone($config['defaultTimezone']);
		}
		else
		{
			static::defaultTimezone(date_default_timezone_get());
		}

		// any custom format patterns?
		if (isset($config['patterns']) and is_array($config['patterns']))
		{
			static::$patterns = $config['patterns'];
		}

		// default to current time if none is given
		if ($time === null or $time === 'now')
		{
			$time = '@'.strval(time() + static::$gmtOffset);
		}

		// deal with timestamps passed
		elseif (is_numeric($time))
		{
			$time = '@'.$time;
		}

		// construct the datetime object
		if ($time instanceOf DateTime)
		{
			$this->datetime = $time;
		}
		else
		{
			// default to default timezone if none is given
			if ($timezone === null)
			{
				$timezone = static::defaultTimezone();
			}

			try
			{
				$this->datetime = new DateTime($time, $timezone);
			}
			catch (\Exception $e)
			{
				static::$lastErrors = DateTime::getLastErrors();
				throw $e;
			}

			// make sure the objects timezone is ok,
			// some formats default to UTC, no matter what timezone is passed
			$this->datetime->setTimezone($timezone);
		}
	}

	/**
	 */
	public function __wakeup()
	{
		return $this->datetime->__wakeup();
	}

	/**
	 * Allows you to just put the object in a string and get it inserted in the default pattern
	 *
	 * @return  bool|string  the formatted date string on success or false on failure.
	 */
	public function __toString()
	{
		return $this->format();
	}

	/**
	 * Adds an amount of days, months, years, hours, minutes and seconds to the object
	 *
	 * @param  int|DateInterval  $interval  either a number of seconds, or a DateInterval object
	 *
	 * @return  bool|Date  this object for chaining, or false on failure
	 */
	public function add($interval = null)
	{
		if ( ! $interval instanceOf DateInterval)
		{
			if (is_numeric($interval))
			{
				$interval = new DateInterval(($interval > 0 ? '+' : '-').strval(abs($interval)).' sec');
			}
		}

		if ($this->datetime->add($interval))
		{
			return $this;
		}

		return false;
	}

	/**
	 * Subtracts an amount of days, months, years, hours, minutes and seconds to the object
	 *
	 * @param  int|DateInterval  $interval  either a number of seconds, or a DateInterval object
	 *
	 * @return  bool|Date  this object for chaining, or false on failure
	 */
	public function sub($interval = null)
	{
		if ( ! $interval instanceOf DateInterval)
		{
			if (is_numeric($interval))
			{
				$interval = new DateInterval(($interval > 0 ? '+' : '-').strval(abs($interval)).' sec');
			}
		}

		if ($this->datetime->sub($interval))
		{
			return $this;
		}

		return false;
	}

	/**
	 * Alter the timestamp of a Date object by incrementing or decrementing
	 *
	 * @param  string  $modify  any format accepted by strtotime().
	 *
	 * @return bool|Date  this object, or false on failure
	 */
	public function modify($modify)
	{
		if ($this->datetime->modify($modify))
		{
			return $this;
		}

		return false;
	}

	/**
	 * (re)sets the date
	 *
	 * @param  int  $year   Year
	 * @param  int  $month  Month
	 * @param  int  $day    Day
	 *
	 * @return bool|Date  this object, or false on failure
	 */
	public function setDate($year, $month, $day)
	{
		if ($this->datetime->setDate($year, $month, $day))
		{
			return $this;
		}

		return false;
	}

	/**
	 * (re)sets the ISO date
	 *
	 * @param  int  $year   Year
	 * @param  int  $week   Week
	 * @param  int  $day    Day
	 *
	 * @return bool|Date  this object, or false on failure
	 */
	public function setISODate($year, $week, $day = 1)
	{
		if ($this->datetime->setISODate($year, $month, $day))
		{
			return $this;
		}

		return false;
	}

	/**
	 * (re)sets the time
	 *
	 * @param  int  $hour    Hour
	 * @param  int  $minute  Minute
	 * @param  int  $second  Second
	 *
	 * @return bool|Date  this object, or false on failure
	 */
	public function setTime($hour, $minute, $second = 0)
	{
		if ($this->datetime->setTime($hour, $minute, $second))
		{
			return $this;
		}

		return false;
	}

	/**
	 * (re)sets the date and time based on an Unix timestamp
	 *
	 * @param  int  unixTimestamp    unix timestamp
	 *
	 * @return bool|Date  this object, or false on failure
	 */
	public function setTimestamp($unixTimestamp)
	{
		if ($this->datetime->setTimestamp($unixTimestamp))
		{
			return $this;
		}

		return false;
	}

	/**
	 * (re)sets the time zone for the DateTime object
	 *
	 * @param  string|DateTimeZone  $timezone    New timezone, or null for default timezone
	 *
	 * @return bool|Date  this object, or false on failure
	 */
	public function setTimezone($timezone)
	{
		if ( ! $timezone instanceOf DateTimeZone)
		{
			if ($timezone === null)
			{
				$timezone = static::defaultTimezone();
			}
			else
			{
				$timezone = new DateTimeZone($timezone);
			}
		}

		if ($this->datetime->setTimezone($timezone))
		{
			return $this;
		}

		return false;
	}

	/**
	 * Returns the difference between this objects time and anothers
	 *
	 * @param  DateTimeInterface  $datetime2  Other DateTimeInterface object to compare against
	 * @param  bool               $absolute   true if the interval must be forced to be positive
	 *
	 * @return  bool|DateInterval  object defining the difference between the two, or false on failure
	 */
	public function diff($datetime2, $absolute = false)
	{
		return $this->datetime->diff($datetime2, $absolute);
	}

	/**
	 * Returns date formatted according to given format
	 *
	 * @param  string  $format  any format supported by DateTime, or a format name configured
	 *
	 * @return  bool|string  the formatted date string on success or false on failure.
	 */
	public function format($format = 'local', $timezone = null)
	{
		// custom format pattern lookup
		if (array_key_exists($format, static::$patterns))
		{
			$format = static::$patterns[$format];
		}

		// save the objects timezone
		$orginalTimezone = $this->datetime->getTimezone();

		// default GMT offset
		$offset = 0;

		// want to output in the application default timezone?
		if ($timezone === true)
		{
			$timezone = static::defaultTimezone();
		}

		// output timezone specified?
		if ($timezone)
		{
			if ( ! $timezone instanceOf DateTimeZone)
			{
				if ( ! $timezone = new DateTimeZone($timezone))
				{
					return false;
				}
			}
			if ( ! $this->datetime->setTimezone($timezone))
			{
				return false;
			}

			$offset = $this->datetime->getOffset();
		}

		// Create output
		if (strpos($format, '%') === false)
		{
			$result = $this->datetime->format($format);
		}
		else
		{
			$result = strftime($format, $this->datetime->getTimestamp() - $offset);
			if (static::$encoding)
			{
				$result = static::$encoding == 'UTF8' ? utf8_encode($result) : iconv('ISO8859-1', static::$encoding, $result);
			}
		}

		// restore the timezone if needed
		if ($timezone !== null)
		{
			$this->datetime->setTimezone($orginalTimezone);
		}

		return $result;
	}

	/**
	 * Returns the timezone offset in seconds from UTC
	 *
	 * @return  bool|int  the timezone offset in seconds from UTC on success or false on failure.
	 */
	public function getOffset()
	{
		return $this->datetime->getOffset();
	}

	/**
	 * Returns the Unix timestamp
	 *
	 * @return  int  the Unix timestamp representing the date.
	 */
	public function getTimestamp()
	{
		return $this->datetime->getTimestamp();
	}

	/**
	 * Return the time zone of this object
	 *
	 * @return  DateTimeZone  the defined timezone
	 */
	public function getTimezone()
	{
		return $this->datetime->getTimezone();
	}
}
