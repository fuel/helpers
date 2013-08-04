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

use DatePeriod;
use ArrayAccess;
use DateInterval;
use DateTime;

/**
 * DateRange Class
 *
 * DatePeriod drop-in replacement, which provides read-only ArrayAccess to the object
 *
 * @package    Fuel\Common
 * @since 2.0.0
 */
class DateRange extends DatePeriod implements ArrayAccess
{
	/**
	 * Make the DatePeriod constructor more flexible
	 */
	public function __construct($start, $interval, $end, $options = null)
	{
		// make sure $start is a DateTime object
		if ( ! $start instanceOf DateTime)
		{
			$start = new DateTime($start);
		}

		// make sure $interval is a DateInterval object
		if ( ! $interval instanceOf DateInterval)
		{
			$interval = DateInterval::createFromDateString($interval);
		}

		// make sure $end is a DateTime object
		if ( ! $end instanceOf DateTime)
		{
			if (is_numeric($end) and $end > 1000)
			{
				$end = new DateTime('@'.$end);
			}
			elseif (is_string($end))
			{
				$end = new DateTime($end);
			}
		}

		call_user_func_array('parent::__construct', array($start, $interval, $end, $options));
	}
	/**
	 * Not implemented
	 */
    public function offsetSet($offset, $value)
    {
		// this object is read-only!
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
