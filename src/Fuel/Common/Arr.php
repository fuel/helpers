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

/**
 * The Arr class provides a few nice functions for making
 * dealing with arrays easier
 *
 * @package  Fuel\Common
 * @since  1.0.0
 */
class Arr
{
	/**
	 * Gets a dot-notated key from an array, with a default value if it does
	 * not exist.
	 *
	 * @param   array   $array    The search array
	 * @param   mixed   $key      The dot-notated key or array of keys
	 * @param   string  $default  The default value
	 * @return  mixed
	 */
	public static function get($array, $key, $default = null)
	{
		if ( ! is_array($array) and ! $array instanceof \ArrayAccess)
		{
			throw new \InvalidArgumentException('First parameter must be an array or ArrayAccess object.');
		}

		if (is_null($key))
		{
			return $array;
		}

		if (is_array($key))
		{
			$return = array();
			foreach ($key as $k)
			{
				$return[$k] = static::get($array, $k, $default);
			}
			return $return;
		}

		if (is_object($key))
		{
			$key = (string) $key;
		}

		if (array_key_exists($key, $array))
		{
			return $array[$key];
		}

		foreach (explode('.', $key) as $key_part)
		{
			if (($array instanceof \ArrayAccess and isset($array[$key_part])) === false)
			{
				if ( ! is_array($array) or ! array_key_exists($key_part, $array))
				{
					return result($default);
				}
			}

			$array = $array[$key_part];
		}

		return $array;
	}

	/**
	 * Set an array item (dot-notated) to the value.
	 *
	 * @param   array   $array  The array to insert it into
	 * @param   mixed   $key    The dot-notated key to set or array of keys
	 * @param   mixed   $value  The value
	 * @return  void
	 */
	public static function set(&$array, $key, $value = null)
	{
		if (is_null($key))
		{
			$array = $value;
			return;
		}

		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				static::set($array, $k, $v);
			}
		}
		else
		{
			$keys = explode('.', $key);

			while (count($keys) > 1)
			{
				$key = array_shift($keys);

				if ( ! isset($array[$key]) or ! is_array($array[$key]))
				{
					$array[$key] = array();
				}

				$array =& $array[$key];
			}

			$array[array_shift($keys)] = $value;
		}
	}

	/**
	 * Pluck an array of values from an array.
	 *
	 * @param  array   $array  collection of arrays to pluck from
	 * @param  string  $key    key of the value to pluck
	 * @param  string  $index  optional return array index key, true for original index
	 * @return array   array of plucked values
	 */
	public static function pluck($array, $key, $index = null)
	{
		$return = array();
		$get_deep = strpos($key, '.') !== false;

		if ( ! $index)
		{
			foreach ($array as $i => $a)
			{
				$return[] = (is_object($a) and ! ($a instanceof \ArrayAccess)) ? $a->{$key} :
					($get_deep ? static::get($a, $key) : $a[$key]);
			}
		}
		else
		{
			foreach ($array as $i => $a)
			{
				$index !== true and $i = (is_object($a) and ! ($a instanceof \ArrayAccess)) ? $a->{$index} : $a[$index];
				$return[$i] = (is_object($a) and ! ($a instanceof \ArrayAccess)) ? $a->{$key} :
					($get_deep ? static::get($a, $key) : $a[$key]);
			}
		}

		return $return;
	}

	/**
	 * Array_key_exists with a dot-notated key from an array.
	 *
	 * @param   array   $array    The search array
	 * @param   mixed   $key      The dot-notated key or array of keys
	 * @return  mixed
	 */
	public static function has($array, $key)
	{
		foreach (explode('.', $key) as $key_part)
		{
			if ( ! is_array($array) or ! array_key_exists($key_part, $array))
			{
				return false;
			}

			$array = $array[$key_part];
		}

		return true;
	}

	/**
	 * Unsets dot-notated key from an array
	 *
	 * @param   array   $array    The search array
	 * @param   mixed   $key      The dot-notated key or array of keys
	 * @return  mixed
	 */
	public static function delete(&$array, $key)
	{
		if (is_null($key))
		{
			return false;
		}

		if (is_array($key))
		{
			$return = array();
			foreach ($key as $k)
			{
				$return[$k] = static::delete($array, $k);
			}
			return $return;
		}

		$key_parts = explode('.', $key);

		if ( ! is_array($array) or ! array_key_exists($key_parts[0], $array))
		{
			return false;
		}

		$this_key = array_shift($key_parts);

		if ( ! empty($key_parts))
		{
			$key = implode('.', $key_parts);
			return static::delete($array[$this_key], $key);
		}
		else
		{
			unset($array[$this_key]);
		}

		return true;
	}

	/**
	 * Converts a multi-dimensional associative array into an array of key => values with the provided field names
	 *
	 * @param   array   $assoc      the array to convert
	 * @param   string  $key_field  the field name of the key field
	 * @param   string  $val_field  the field name of the value field
	 * @return  array
	 * @throws  \InvalidArgumentException
	 */
	public static function assocToKeyval($assoc, $key_field, $val_field)
	{
		if ( ! is_array($assoc) and ! $assoc instanceof \Iterator)
		{
			throw new \InvalidArgumentException('The first parameter must be an array.');
		}

		$output = array();
		foreach ($assoc as $row)
		{
			if (isset($row[$key_field]) and isset($row[$val_field]))
			{
				$output[$row[$key_field]] = $row[$val_field];
			}
		}

		return $output;
	}

	/**
	 * Converts the given 1 dimensional non-associative array to an associative
	 * array.
	 *
	 * The array given must have an even number of elements or null will be returned.
	 *
	 *     Arr::to_assoc(array('foo','bar'));
	 *
	 * @param   string      $arr  the array to change
	 * @return  array|null  the new array or null
	 * @throws  \BadMethodCallException
	 */
	public static function toAssoc($arr)
	{
		if (($count = count($arr)) % 2 > 0)
		{
			throw new \BadMethodCallException('Number of values in to_assoc must be even.');
		}
		$keys = $vals = array();

		for ($i = 0; $i < $count - 1; $i += 2)
		{
			$keys[] = array_shift($arr);
			$vals[] = array_shift($arr);
		}
		return array_combine($keys, $vals);
	}

	/**
	 * Checks if the given array is an assoc array.
	 *
	 * @param   array  $arr  the array to check
	 * @return  bool   true if its an assoc array, false if not
	 */
	public static function isAssoc($arr)
	{
		if ( ! is_array($arr))
		{
			throw new \InvalidArgumentException('The parameter must be an array.');
		}

		$counter = 0;
		foreach ($arr as $key => $unused)
		{
			if ( ! is_int($key) or $key !== $counter++)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Flattens a multi-dimensional associative array down into a 1 dimensional
	 * associative array.
	 *
	 * @param   array   the array to flatten
	 * @param   string  what to glue the keys together with
	 * @param   bool    whether to reset and start over on a new array
	 * @param   bool    whether to flatten only associative array's, or also indexed ones
	 * @return  array
	 */
	public static function flatten($array, $glue = ':', $reset = true, $indexed = true)
	{
		static $return = array();
		static $curr_key = array();

		if ($reset)
		{
			$return = array();
			$curr_key = array();
		}

		foreach ($array as $key => $val)
		{
			$curr_key[] = $key;
			if (is_array($val) and ($indexed or array_values($val) !== $val))
			{
				static::flattenAssoc($val, $glue, false);
			}
			else
			{
				$return[implode($glue, $curr_key)] = $val;
			}
			array_pop($curr_key);
		}
		return $return;
	}

	/**
	 * Flattens a multi-dimensional associative array down into a 1 dimensional
	 * associative array.
	 *
	 * @param   array   the array to flatten
	 * @param   string  what to glue the keys together with
	 * @param   bool    whether to reset and start over on a new array
	 * @return  array
	 */
	public static function flattenAssoc($array, $glue = ':', $reset = true)
	{
		return static::flatten($array, $glue, $reset, false);
	}

	/**
	 * Reverse a flattened array in its original form.
	 *
	 * @param   array   $array  flattened array
	 * @param   string  $glue   glue used in flattening
	 * @return  array   the unflattened array
	 */
	public static function reverseFlatten($array, $glue = ':')
	{
		$return = array();

		foreach ($array as $key => $value)
		{
			if (stripos($key, $glue) !== false)
			{
				$keys = explode($glue, $key);
				$temp =& $return;
				while (count($keys) > 1)
				{
					$key = array_shift($keys);
					$key = is_numeric($key) ? (int) $key : $key;
					if ( ! isset($temp[$key]) or ! is_array($temp[$key]))
					{
						$temp[$key] = array();
					}
					$temp =& $temp[$key];
				}

				$key = array_shift($keys);
				$key = is_numeric($key) ? (int) $key : $key;
				$temp[$key] = $value;
			}
			else
			{
				$key = is_numeric($key) ? (int) $key : $key;
				$return[$key] = $value;
			}
		}

		return $return;
	}

	/**
	 * Filters an array on prefixed associative keys.
	 *
	 * @param   array   the array to filter.
	 * @param   string  prefix to filter on.
	 * @param   bool    whether to remove the prefix.
	 * @return  array
	 */
	public static function filterPrefixed($array, $prefix, $remove_prefix = true)
	{
		$return = array();
		foreach ($array as $key => $val)
		{
			if (preg_match('/^'.$prefix.'/', $key))
			{
				if ($remove_prefix === true)
				{
					$key = preg_replace('/^'.$prefix.'/','',$key);
				}
				$return[$key] = $val;
			}
		}
		return $return;
	}

	/**
	 * Recursive version of PHP's array_filter()
	 *
	 * @param   array   the array to filter.
	 * @param   callback   the callback that determines whether or not a value is filtered
	 * @return  array
	 */
	public static function filterRecursive($array, $callback = null)
	{
		foreach ($array as &$value)
		{
			if (is_array($value))
			{
				$value = $callback === null ? static::filterRecursive($value) : static::filterRecursive($value, $callback);
			}
		}

		return $callback === null ? array_filter($array) : array_filter($array, $callback);
   	}

	/**
	 * Removes items from an array that match a key prefix.
	 *
	 * @param   array   the array to remove from
	 * @param   string  prefix to filter on
	 * @return  array
	 */
	public static function removePrefixed($array, $prefix)
	{
		foreach ($array as $key => $val)
		{
			if (preg_match('/^'.$prefix.'/', $key))
			{
				unset($array[$key]);
			}
		}
		return $array;
	}

	/**
	 * Filters an array on suffixed associative keys.
	 *
	 * @param   array   the array to filter.
	 * @param   string  suffix to filter on.
	 * @param   bool    whether to remove the suffix.
	 * @return  array
	 */
	public static function filterSuffixed($array, $suffix, $remove_suffix = true)
	{
		$return = array();
		foreach ($array as $key => $val)
		{
			if (preg_match('/'.$suffix.'$/', $key))
			{
				if ($remove_suffix === true)
				{
					$key = preg_replace('/'.$suffix.'$/','',$key);
				}
				$return[$key] = $val;
			}
		}
		return $return;
	}

	/**
	 * Removes items from an array that match a key suffix.
	 *
	 * @param   array   the array to remove from
	 * @param   string  suffix to filter on
	 * @return  array
	 */
	public static function removeSuffixed($array, $suffix)
	{
		foreach ($array as $key => $val)
		{
			if (preg_match('/'.$suffix.'$/', $key))
			{
				unset($array[$key]);
			}
		}
		return $array;
	}

	/**
	 * Filters an array by an array of keys
	 *
	 * @param   array   the array to filter.
	 * @param   array   the keys to filter
	 * @param   bool    if true, removes the matched elements.
	 * @return  array
	 */
	public static function filterKeys($array, $keys, $remove = false)
	{
		$return = array();
		foreach ($keys as $key)
		{
			if (array_key_exists($key, $array))
			{
				$remove or $return[$key] = $array[$key];
				if($remove)
				{
					unset($array[$key]);
				}
			}
		}
		return $remove ? $array : $return;
	}

	/**
	 * Insert value(s) into an array, mostly an array_splice alias
	 * WARNING: original array is edited by reference, only boolean success is returned
	 *
	 * @param   array        the original array (by reference)
	 * @param   array|mixed  the value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param   int          the numeric position at which to insert, negative to count from the end backwards
	 * @return  bool         false when array shorter then $pos, otherwise true
	 */
	public static function insert(array &$original, $value, $pos)
	{
		if (count($original) < abs($pos))
		{
			throw new \InvalidArgumentException('Position larger than number of elements in array in which to insert.');
		}

		array_splice($original, $pos, 0, $value);

		return true;
	}

	/**
	 * Insert value(s) into an array, mostly an array_splice alias
	 * WARNING: original array is edited by reference, only boolean success is returned
	 *
	 * @param   array        the original array (by reference)
	 * @param   array|mixed  the value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param   int          the numeric position at which to insert, negative to count from the end backwards
	 * @return  bool         false when array shorter then $pos, otherwise true
	 */
	public static function insertAssoc(array &$original, array $values, $pos)
	{
		if (count($original) < abs($pos))
		{
			return false;
		}

		$original = array_slice($original, 0, $pos, true) + $values + array_slice($original, $pos, null, true);

		return true;
	}

	/**
	 * Insert value(s) into an array before a specific key
	 * WARNING: original array is edited by reference, only boolean success is returned
	 *
	 * @param   array        the original array (by reference)
	 * @param   array|mixed  the value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param   string|int   the key before which to insert
	 * @param   bool         wether the input is an associative array
	 * @return  bool         false when key isn't found in the array, otherwise true
	 */
	public static function insertBeforeKey(array &$original, $value, $key, $is_assoc = false)
	{
		$pos = array_search($key, array_keys($original));

		if ($pos === false)
		{
			throw new \InvalidArgumentException('Unknown key before which to insert the new value into the array.');
		}

		return $is_assoc ? static::insertAssoc($original, $value, $pos) : static::insert($original, $value, $pos);
	}

	/**
	 * Insert value(s) into an array after a specific key
	 * WARNING: original array is edited by reference, only boolean success is returned
	 *
	 * @param   array        the original array (by reference)
	 * @param   array|mixed  the value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param   string|int   the key after which to insert
	 * @param   bool         wether the input is an associative array
	 * @return  bool         false when key isn't found in the array, otherwise true
	 */
	public static function insertAfterKey(array &$original, $value, $key, $is_assoc = false)
	{
		$pos = array_search($key, array_keys($original));

		if ($pos === false)
		{
			throw new \InvalidArgumentException('Unknown key after which to insert the new value into the array.');
		}

		return $is_assoc ? static::insertAssoc($original, $value, $pos + 1) : static::insert($original, $value, $pos + 1);
	}

	/**
	 * Insert value(s) into an array after a specific value (first found in array)
	 *
	 * @param   array        the original array (by reference)
	 * @param   array|mixed  the value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param   string|int   the value after which to insert
	 * @param   bool         wether the input is an associative array
	 * @return  bool         false when value isn't found in the array, otherwise true
	 */
	public static function insertAfterValue(array &$original, $value, $search, $is_assoc = false)
	{
		$key = array_search($search, $original);

		if ($key === false)
		{
			throw new \InvalidArgumentException('Unknown value after which to insert the new value into the array.');
		}

		return static::insertAfterKey($original, $value, $key, $is_assoc);
	}

	/**
	 * Insert value(s) into an array before a specific value (first found in array)
	 *
	 * @param   array        the original array (by reference)
	 * @param   array|mixed  the value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param   string|int   the value after which to insert
	 * @param   bool         wether the input is an associative array
	 * @return  bool         false when value isn't found in the array, otherwise true
	 */
	public static function insertBeforeValue(array &$original, $value, $search, $is_assoc = false)
	{
		$key = array_search($search, $original);

		if ($key === false)
		{
			throw new \InvalidArgumentException('Unknown value before which to insert the new value into the array.');
		}

		return static::insertBeforeKey($original, $value, $key, $is_assoc);
	}

	/**
	 * Sorts a multi-dimensional array by it's values.
	 *
	 * @access	public
	 * @param	array	The array to fetch from
	 * @param	string	The key to sort by
	 * @param	string	The order (asc or desc)
	 * @param	int		The php sort type flag
	 * @return	array
	 */
	public static function sort($array, $key, $order = 'asc', $sort_flags = SORT_REGULAR)
	{
		if ( ! is_array($array))
		{
			throw new \InvalidArgumentException('Arr::sort() - $array must be an array.');
		}

		if (empty($array))
		{
			return $array;
		}

		foreach ($array as $k => $v)
		{
			$b[$k] = static::get($v, $key);
		}

		switch ($order)
		{
			case 'asc':
				asort($b, $sort_flags);
			break;

			case 'desc':
				arsort($b, $sort_flags);
			break;

			default:
				throw new \InvalidArgumentException('Arr::sort() - $order must be asc or desc.');
			break;
		}

		foreach ($b as $key => $val)
		{
			$c[] = $array[$key];
		}

		return $c;
	}

	/**
	 * Sorts an array on multitiple values, with deep sorting support.
	 *
	 * @param   array  $array        collection of arrays/objects to sort
	 * @param   array  $conditions   sorting conditions
	 * @param   bool   @ignore_case  wether to sort case insensitive
	 */
	public static function multisort($array, $conditions, $ignore_case = false)
	{
		$temp = array();
		$keys = array_keys($conditions);

		foreach($keys as $key)
		{
			$temp[$key] = static::pluck($array, $key, true);
			is_array($conditions[$key]) or $conditions[$key] = array($conditions[$key]);
		}

		$args = array();
		foreach ($keys as $key)
		{
			$args[] = $ignore_case ? array_map('strtolower', $temp[$key]) : $temp[$key];
			foreach($conditions[$key] as $flag)
			{
				$args[] = $flag;
			}
		}

		$args[] = &$array;

		call_user_func_array('array_multisort', $args);
		return $array;
	}

	/**
	 * Find the average of an array
	 *
	 * @param   array    the array containing the values
	 * @return  numeric  the average value
	 */
	public static function average($array)
	{
		// No arguments passed, lets not divide by 0
		if ( ! ($count = count($array)) > 0)
		{
			return 0;
		}

		return (array_sum($array) / $count);
	}

	/**
	 * Replaces key names in an array by names in $replace
	 *
	 * @param   array			the array containing the key/value combinations
	 * @param   array|string	key to replace or array containing the replacement keys
	 * @param   string			the replacement key
	 * @return  array			the array with the new keys
	 */
	public static function replaceKey($source, $replace, $new_key = null)
	{
		if(is_string($replace))
		{
			$replace = array($replace => $new_key);
		}

		if ( ! is_array($source) or ! is_array($replace))
		{
			throw new \InvalidArgumentException('Arr::replaceKey() - $source must an array. $replace must be an array or string.');
		}

		$result = array();

		foreach ($source as $key => $value)
		{
			if (array_key_exists($key, $replace))
			{
				$result[$replace[$key]] = $value;
			}
			else
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}

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
	public static function merge()
	{
		$array  = func_get_arg(0);
		$arrays = array_slice(func_get_args(), 1);

		if ( ! is_array($array))
		{
			throw new \InvalidArgumentException('Arr::merge() - all arguments must be arrays.');
		}

		foreach ($arrays as $arr)
		{
			if ( ! is_array($arr))
			{
				throw new \InvalidArgumentException('Arr::merge() - all arguments must be arrays.');
			}

			foreach ($arr as $k => $v)
			{
				// numeric keys are appended
				if (is_int($k))
				{
					array_key_exists($k, $array) ? $array[] = $v : $array[$k] = $v;
				}
				elseif (is_array($v) and array_key_exists($k, $array) and is_array($array[$k]))
				{
					$array[$k] = static::merge($array[$k], $v);
				}
				else
				{
					$array[$k] = $v;
				}
			}
		}

		return $array;
	}

	/**
	 * Merge 2 arrays recursively, differs in 2 important ways from array_merge_recursive()
	 * - When there's 2 different values and not both arrays, the latter value overwrites the earlier
	 *   instead of merging both into an array
	 * - Numeric keys are never changed
	 *
	 * @param   array  multiple variables all of which must be arrays
	 * @return  array
	 * @throws  \InvalidArgumentException
	 */
	public static function mergeAssoc()
	{
		$array  = func_get_arg(0);
		$arrays = array_slice(func_get_args(), 1);

		if ( ! is_array($array))
		{
			throw new \InvalidArgumentException('Arr::mergeAssoc() - all arguments must be arrays.');
		}

		foreach ($arrays as $arr)
		{
			if ( ! is_array($arr))
			{
				throw new \InvalidArgumentException('Arr::mergeAssoc() - all arguments must be arrays.');
			}

			foreach ($arr as $k => $v)
			{
				if (is_array($v) and array_key_exists($k, $array) and is_array($array[$k]))
				{
					$array[$k] = static::mergeAssoc($array[$k], $v);
				}
				else
				{
					$array[$k] = $v;
				}
			}
		}

		return $array;
	}

	/**
	 * Prepends a value with an asociative key to an array.
	 * Will overwrite if the value exists.
	 *
	 * @param   array           $arr     the array to prepend to
	 * @param   string|array    $key     the key or array of keys and values
	 * @param   mixed           $valye   the value to prepend
	 */
	public static function prepend(&$arr, $key, $value = null)
	{
		$arr = (is_array($key) ? $key : array($key => $value)) + $arr;
	}

	/**
	 * Recursive in_array
	 *
	 * @param   mixed  $needle    what to search for
	 * @param   array  $haystack  array to search in
	 * @return  bool   wether the needle is found in the haystack.
	 */
	public static function inArrayRecursive($needle, $haystack, $strict = false)
	{
		foreach ($haystack as $value)
		{
			if ( ! $strict and $needle == $value)
			{
				return true;
			}
			elseif ($needle === $value)
			{
				return true;
			}
			elseif (is_array($value) and static::inArrayRecursive($needle, $value, $strict))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the given array is a multidimensional array.
	 *
	 * @param   array  $arr       the array to check
	 * @param   array  $all_keys  if true, check that all elements are arrays
	 * @return  bool   true if its a multidimensional array, false if not
	 */
	public static function isMulti($arr, $all_keys = false)
	{
		$values = array_filter($arr, 'is_array');
		return $all_keys ? count($arr) === count($values) : count($values) > 0;
	}

	/**
	 * Searches the array for a given value and returns the
	 * corresponding key or default value.
	 * If $recursive is set to true, then the Arr::search()
	 * function will return a delimiter-notated key using $delimiter.
	 *
	 * @param   array   $array     The search array
	 * @param   mixed   $value     The searched value
	 * @param   string  $default   The default value
	 * @param   bool    $recursive Whether to get keys recursive
	 * @param   string  $delimiter The delimiter, when $recursive is true
	 * @param   bool    $strict    If true, do a strict key comparison
	 * @return  mixed
	 */
	public static function search($array, $value, $default = null, $recursive = true, $delimiter = '.', $strict = false)
	{
		if ( ! is_array($array) and ! $array instanceof \ArrayAccess)
		{
			throw new \InvalidArgumentException('First parameter must be an array or ArrayAccess object.');
		}

		if ( ! is_null($default) and ! is_int($default) and ! is_string($default))
		{
			throw new \InvalidArgumentException('Expects parameter 3 to be an string or integer or null.');
		}

		if ( ! is_string($delimiter))
		{
			throw new \InvalidArgumentException('Expects parameter 5 must be an string.');
		}

		$key = array_search($value, $array, $strict);

		if ($recursive and $key === false)
		{
			$keys = array();
			foreach ($array as $k => $v)
			{
				if (is_array($v))
				{
					$rk = static::search($v, $value, $default, true, $delimiter, $strict);
					if ($rk !== $default)
					{
						$keys = array($k, $rk);
						break;
					}
				}
			}
			$key = count($keys) ? implode($delimiter, $keys) : false;
		}

		return $key === false ? $default : $key;
	}

	/**
	 * Returns only unique values in an array. It does not sort. First value is used.
	 *
	 * @param   array  $arr       the array to dedup
	 * @return  array   array with only de-duped values
	 */
	public static function unique($arr)
	{
		// filter out all duplicate values
		return array_filter($arr, function($item)
		{
			// contrary to popular belief, this is not as static as you think...
			static $vars = array();

			if (in_array($item, $vars, true))
			{
				// duplicate
				return false;
			}
			else
			{
				// record we've had this value
				$vars[] = $item;

				// unique
				return true;
			}
		});
	}

	/**
	 * Calculate the sum of an array
	 *
	 * @param   array    $array  the array containing the values
	 * @param   string   $key    key of the value to pluck
	 * @return  numeric  the sum value
	 */
	public static function sum($array, $key)
	{
		if ( ! is_array($array) and ! $array instanceof \ArrayAccess)
		{
			throw new \InvalidArgumentException('First parameter must be an array or ArrayAccess object.');
		}

		return array_sum(static::pluck($array, $key));
	}

	/**
	 * Returns the array with all numeric keys re-indexed, and string keys untouched
	 *
	 * @param   array  $arr       the array to reindex
	 * @return  array   reindexed array
	 */
	public static function reIndex($arr)
	{
		// reindex this level
		$arr = array_merge($arr);

		foreach ($arr as $k => &$v)
		{
			is_array($v) and $v = static::reIndex($v);
		}

		return $arr;
	}

	/**
	 * Get the previous value or key from an array using the current array key
	 *
	 * @param   array    $array  the array containing the values
	 * @param   string   $key    key of the current entry to use as reference
	 * @param   bool     $key    if true, return the previous value instead of the previous key
	 * @param   bool     $key    if true, do a strict key comparison
	 *
	 * @return  mixed  the value in the array, null if there is no previous value, or false if the key doesn't exist
	 */
	public static function previousByKey($array, $key, $get_value = false, $strict = false)
	{
		if ( ! is_array($array) and ! $array instanceof \ArrayAccess)
		{
			throw new \InvalidArgumentException('First parameter must be an array or ArrayAccess object.');
		}

		// get the keys of the array
		$keys = array_keys($array);

		// and do a lookup of the key passed
		if (($index = array_search($key, $keys, $strict)) === false)
		{
			// key does not exist
			return false;
		}

		// check if we have a previous key
		elseif ( ! isset($keys[$index-1]))
		{
			// there is none
			return null;
		}

		// return the value or the key of the array entry the previous key points to
		return $get_value ? $array[$keys[$index-1]] : $keys[$index-1];
	}

	/**
	 * Get the next value or key from an array using the current array key
	 *
	 * @param   array    $array  the array containing the values
	 * @param   string   $key    key of the current entry to use as reference
	 * @param   bool     $key    if true, return the next value instead of the next key
	 * @param   bool     $key    if true, do a strict key comparison
	 *
	 * @return  mixed  the value in the array, null if there is no next value, or false if the key doesn't exist
	 */
	public static function nextByKey($array, $key, $get_value = false, $strict = false)
	{
		if ( ! is_array($array) and ! $array instanceof \ArrayAccess)
		{
			throw new \InvalidArgumentException('First parameter must be an array or ArrayAccess object.');
		}

		// get the keys of the array
		$keys = array_keys($array);

		// and do a lookup of the key passed
		if (($index = array_search($key, $keys, $strict)) === false)
		{
			// key does not exist
			return false;
		}

		// check if we have a previous key
		elseif ( ! isset($keys[$index+1]))
		{
			// there is none
			return null;
		}

		// return the value or the key of the array entry the previous key points to
		return $get_value ? $array[$keys[$index+1]] : $keys[$index+1];
	}

	/**
	 * Get the previous value or key from an array using the current array value
	 *
	 * @param   array    $array  the array containing the values
	 * @param   string   $value  value of the current entry to use as reference
	 * @param   bool     $key    if true, return the previous value instead of the previous key
	 * @param   bool     $key    if true, do a strict key comparison
	 *
	 * @return  mixed  the value in the array, null if there is no previous value, or false if the key doesn't exist
	 */
	public static function previousByValue($array, $value, $get_value = true, $strict = false)
	{
		if ( ! is_array($array) and ! $array instanceof \ArrayAccess)
		{
			throw new \InvalidArgumentException('First parameter must be an array or ArrayAccess object.');
		}

		// find the current value in the array
		if (($key = array_search($value, $array, $strict)) === false)
		{
			// bail out if not found
			return false;
		}

		// get the list of keys, and find our found key
		$keys = array_keys($array);
		$index = array_search($key, $keys);

		// if there is no previous one, bail out
		if ( ! isset($keys[$index-1]))
		{
			return null;
		}

		// return the value or the key of the array entry the previous key points to
		return $get_value ? $array[$keys[$index-1]] : $keys[$index-1];
	}

         /**
          * Returns a string from an array based on delimiter, with optional left and right characters
          *
          * @param     array $array the array containing the values
          * @param string $delimiter delimiter for the output string
          * @param string $left value to be used on left of each delimited string
          * @param string $right value to be used on right  of each delimited string
          * @return string
          */
         public static function toDelimitedString( array $array, $delimiter = ',', $left = '', $right = '' )
         {
		if ( ! is_array($array) )
		{
			throw new \InvalidArgumentException('First parameter must be an array.');
		}
		
                $result = '';
                foreach ( $array as $value )
                {
                     // append the left and right chars for each value seperated with delimiter
                     $result .= ( $left . $value . $right . $delimiter);
                }
                 
                $length = mb_strlen($result);
                 
                if ( $length > 0 )
                {
                     $result = mb_substr($result, 0, $length - 1);
                }
                 
                return $result;
         }
    
	/**
	 * Get the next value or key from an array using the current array value
	 *
	 * @param   array    $array  the array containing the values
	 * @param   string   $value  value of the current entry to use as reference
	 * @param   bool     $key    if true, return the next value instead of the next key
	 * @param   bool     $key    if true, do a strict key comparison
	 *
	 * @return  mixed  the value in the array, null if there is no next value, or false if the key doesn't exist
	 */
	public static function nextByValue($array, $value, $get_value = true, $strict = false)
	{
		if ( ! is_array($array) and ! $array instanceof \ArrayAccess)
		{
			throw new \InvalidArgumentException('First parameter must be an array or ArrayAccess object.');
		}

		// find the current value in the array
		if (($key = array_search($value, $array, $strict)) === false)
		{
			// bail out if not found
			return false;
		}

		// get the list of keys, and find our found key
		$keys = array_keys($array);
		$index = array_search($key, $keys);

		// if there is no next one, bail out
		if ( ! isset($keys[$index+1]))
		{
			return null;
		}

		// return the value or the key of the array entry the next key points to
		return $get_value ? $array[$keys[$index+1]] : $keys[$index+1];
	}

	/**
	 * Return the subset of the array defined by the supplied keys.
	 *
	 * Returns $default for missing keys, as with Arr::get()
	 *
	 * @param   array    $array    the array containing the values
	 * @param   array    $keys     list of keys (or indices) to return
	 * @param   mixed    $default  value of missing keys; default null
	 *
	 * @return  array  An array containing the same set of keys provided.
	 */
	public static function subset(array $array, array $keys, $default = null)
	{
		$result = array();

		foreach ($keys as $key)
		{
			static::set($result, $key, static::get($array, $key, $default));
		}

		return $result;
	}
}
