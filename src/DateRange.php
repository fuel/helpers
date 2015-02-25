<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common;

use DatePeriod;
use ArrayAccess;
use DateInterval;
use DateTime;

/**
 * DatePeriod drop-in replacement, which provides read-only ArrayAccess to the object
 *
 * @package Fuel\Common
 *
 * @since 2.0
 */
class DateRange extends DatePeriod implements ArrayAccess
{
	/**
	 * Make the DatePeriod constructor more flexible
	 */
	public function __construct($start, $interval = null, $end = null, $options = null)
	{
		// deal with ISO string calls first
		if (func_num_args() < 3 and is_string($start))
		{
			parent::__construct($start, $interval);
			return;
		}

		// make sure $start is a DateTime object
		if ( ! $start instanceOf DateTime)
		{
			// use Date instead of DateTime, it is more flexible
			$start = new Date($start);
		}

		// we need a DateTime object to continue
		if ($start instanceOf Date)
		{
			$start = $start->getDateTime();
		}

		// make sure $interval is a DateInterval object
		if ( ! $interval instanceOf DateInterval)
		{
			$interval = DateInterval::createFromDateString($interval);
		}

		// make sure $end is a DateTime object
		if ( ! $end instanceOf DateTime)
		{
			// recurrences limited to 10000, any larger and we assume its a timestamp
			if (is_numeric($end) and $end > 10000)
			{
				// use Date instead of DateTime, it is more flexible
				$end = new Date('@'.$end);
			}
			elseif (is_string($end))
			{
				$end = new Date($end);
			}
		}

		// we need a DateTime object to continue
		if ($end instanceOf Date)
		{
			$end = $end->getDateTime();
		}

		parent::__construct($start, $interval, $end, $options);
	}
	/**
	 * Not implemented
	 */
    public function offsetSet($offset, $value)
    {
		// this object is read-only!
		throw new \RuntimeException('You can not set a value on a read-only DateRange object.');
    }

	/**
	 * Check if a given object exists
	 */
    public function offsetExists($offset)
    {
		foreach ($this as $key => $value)
		{
			if ($key == $offset)
			{
				return true;
			}
		}
		return false;
    }

    /**
	 * Not implemented
     */
    public function offsetUnset($offset)
    {
		// this object is read-only!
		throw new \RuntimeException('You can not unset a value from a read-only DateRange object.');
    }

	/**
	 *
	 */
    public function offsetGet($offset)
    {
		foreach ($this as $key => $value)
		{
			if ($key == $offset)
			{
				return $value;
			}
		}
		return null;
    }
}
