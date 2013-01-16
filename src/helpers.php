<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Kernel
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

if ( ! function_exists('result'))
{
	/**
	 * Checks if a return value is a Closure without params, and if
	 * so executes it before returning it.
	 *
	 * @param   mixed  $val
	 * @return  mixed  closure result
	 */
	function result($val)
	{
		if ($val instanceof Closure)
		{
			return $val();
		}

		return $val;
	}
}

if ( ! function_exists('array_set'))
{
	/**
	 * Set a value on an array according to a dot-notated key
	 *
	 * @param   array  $array   array
	 * @param   mixed  $dotkey  dot-notated key
	 * @param   mixed  $value   value
	 * @return  voic
	 *
	 * @since  2.0.0
	 */
	function array_set(array &$array, $dotkey, $value = null)
	{
		$set = $dotkey;

		if ( ! is_array($dotkey))
		{
			$set = array($dotkey => $value);
		}

		foreach ($set as $dotkey => $value)
		{
			$arr = &$array;
			$keys = explode('.', $dotkey);
			$last = array_pop($keys);

			while ($key = array_shift($keys))
			{
				if ( ! isset($array[$key]) or ! is_array($array[$key]))
				{
					$array[$key] = array();
				}

				$array = &$array[$key];
			}

			$array[$last] = $value;
		}
	}
}

if ( ! function_exists('array_get'))
{
	/**
	 * Get a value from an array according to a dot-notated key
	 *
	 * @param   array   $array    array
	 * @param   string  $dotkey   dot-notated key
	 * @param   mixed   $default
	 * @return  mixed   array value or default
	 *
	 * @since  2.0.0
	 */
	function array_get(array $array, $dotkey, $default = null)
	{
		$keys = explode('.', $dotkey);

		while($key = array_shift($keys))
		{
			if ( ! isset($array[$key]))
			{
				return result($default);
			}

			$array = $array[$key];
		}

		return $array;
	}
}

if ( ! function_exists('array_has'))
{
	/**
	 * Get wether a value exists in an array according to a dot-notated key
	 *
	 * @param   array   $array    array
	 * @param   string  $dotkey   dot-notated key
	 * @param   mixed   $default
	 * @return  boolean array value or default
	 *
	 * @since  2.0.0
	 */
	function array_has(array $array, $dotkey)
	{
		$keys = explode('.', $dotkey);

		while($key = array_shift($keys))
		{
			if ( ! isset($array[$key]))
			{
				return false;
			}

			$array = $array[$key];
		}

		return true;
	}
}

if ( ! function_exists('array_delete'))
{
	/**
	 * Delete a value from an array according to a dot-notated key
	 *
	 * @param   array   $array    array
	 * @param   string  $dotkey   dot-notated key
	 * @return  boolean wether a value was deleted
	 *
	 * @since  2.0.0
	 */
	function array_delete(array &$array, $dotkey)
	{
		$keys = explode('.', $dotkey);
		$last = array_pop($keys);

		while ($key = array_shift($keys))
		{
			if ( ! isset($array[$key]))
			{
				return false;
			}

			$array = &$array[$key];
		}

		if ( ! isset($array[$last]))
		{
			return false;
		}

		unset($array[$last]);

		return true;
	}
}
