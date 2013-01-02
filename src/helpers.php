<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Kernel
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

if ( ! function_exists('__val'))
{
	/**
	 * Checks if a return value is a Closure without params, and if
	 * so executes it before returning it.
	 *
	 * @param   mixed  $val
	 * @return  mixed
	 */
	function __val($val)
	{
		if ($val instanceof Closure)
		{
			return $val();
		}

		return $val;
	}
}

if ( ! function_exists('array_set_dot_key'))
{
	/**
	 * Set a value on an array according to a dot-notated key
	 *
	 * @param   string              $key
	 * @param   array|\ArrayAccess  $input
	 * @param   mixed               $setting
	 * @param   bool                $unsetOnNull
	 * @return  bool
	 * @throws  \InvalidArgumentException
	 *
	 * @since  2.0.0
	 */
	function array_set_dot_key($key, &$input, &$setting, $unsetOnNull = false)
	{
		if ( ! is_array($input) and ! $input instanceof \ArrayAccess)
		{
			throw new \InvalidArgumentException('The second argument of array_set_dot_key() must be an array or ArrayAccess object.');
		}

		// Explode the key and start iterating
		$keys = explode('.', $key);
		while (count($keys) > 1)
		{
			$key = array_shift($keys);
			if ( ! isset($input[$key])
				or ( ! empty($keys) and ! is_array($input[$key]) and ! $input[$key] instanceof \ArrayAccess))
			{
				// Unset impossible
				if ($unsetOnNull and is_null($setting))
				{
					return false;
				}

				// Create new subarray or overwrite non array
				$input[$key] = array();
			}
			$input =& $input[$key];
		}
		$key = array_shift($keys);

		if ($unsetOnNull and is_null($setting))
		{
			if ( ! isset($input[$key]))
			{
				return false;
			}
			$setting = $input[$key];
			unset($input[$key]);
		}
		else
		{
			$input[$key] = $setting;
		}

		return true;
	}
}

if ( ! function_exists('array_get_dot_key'))
{
	/**
	 * Get a value from an array according to a dot-notated key
	 *
	 * @param   string              $key
	 * @param   array|\ArrayAccess  $input
	 * @param   mixed               $return
	 * @return  bool
	 * @throws  \InvalidArgumentException
	 *
	 * @since  2.0.0
	 */
	function array_get_dot_key($key, &$input, &$return)
	{
		if ( ! is_array($input) and ! $input instanceof \ArrayAccess)
		{
			throw new \InvalidArgumentException('The second argument of array_get_dot_key() must be an array or ArrayAccess object.');
		}

		// Explode the key and start iterating
		$keys = explode('.', $key);
		while (count($keys) > 0)
		{
			$key = array_shift($keys);
			if ( ! isset($input[$key])
				or ( ! empty($keys) and ! is_array($input[$key]) and ! $input[$key] instanceof \ArrayAccess))
			{
				// Value not found, return failure
				return false;
			}
			$input =& $input[$key];
		}

		// return success
		$return = $input;
		return true;
	}
}
