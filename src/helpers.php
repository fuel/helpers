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

if ( ! function_exists('arr_set'))
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
	function arr_set(array &$array, $dotkey = null, $value = null)
	{
		$set = $dotkey;

		if ( ! is_array($dotkey))
		{
			$set = array($dotkey => $value);
		}
		
		//Special case for when $dotkey is null
		if(  is_null($dotkey))
		{
			$array[] = $value;
			return;
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

if ( ! function_exists('arr_get'))
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
	function arr_get(array $array, $dotkey, $default = null)
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

if ( ! function_exists('arr_has'))
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
	function arr_has(array $array, $dotkey)
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

if ( ! function_exists('arr_delete'))
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
	function arr_delete(array &$array, $dotkey)
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

if ( ! function_exists('arr_merge'))
{
	/**
	 * Merge 2 arrays recursively, differs in 2 important ways from array_merge_recursive()
	 * - When there's 2 different values and not both arrays, the latter value overwrites the earlier
	 *   instead of merging both into an array
	 * - Numeric keys that don't conflict aren't changed, only when a numeric key already exists is the
	 *   value added using array_push()
	 *
	 * @param   array  multiple variables all of which must be arrays
	 * @return  array
	 * @throws  \InvalidArgumentException
	 */
	function arr_merge(array $array)
	{
		$arrays = array_slice(func_get_args(), 1);

		foreach ($arrays as $arr)
		{
			if ( ! is_array($arr))
			{
				throw new \InvalidArgumentException('arr_merge() - all arguments must be arrays.');
			}

			foreach ($arr as $k => $v)
			{
				// numeric keys are appended
				if (is_int($k))
				{
					array_key_exists($k, $array) ? array_push($array, $v) : $array[$k] = $v;
				}
				elseif (is_array($v) and array_key_exists($k, $array) and is_array($array[$k]))
				{
					$array[$k] = arr_merge($array[$k], $v);
				}
				else
				{
					$array[$k] = $v;
				}
			}
		}

		return $array;
	}
}

if ( ! function_exists('arr_is_assoc'))
{
	/**
	 * Determine wether an array is associative
	 *
	 * @param   array    $array  array to check
	 * @return  boolean  wether it is an associative array
	 */
	function arr_is_assoc(array $array)
	{
		return array_keys($array) !== range(0, count($array) - 1);
	}
}
