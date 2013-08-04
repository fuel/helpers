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
