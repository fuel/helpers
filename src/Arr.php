<?php declare(strict_types=1);

/**
 * The Fuel PHP Framework is a fast, simple and flexible development framework
 *
 * @package    fuel
 * @version    2.0.0
 * @author     FlexCoders Ltd, Fuel The PHP Framework Team
 * @license    MIT License
 * @copyright  2023 FlexCoders Ltd, The Fuel PHP Framework Team
 * @link       https://fuelphp.org
 */

namespace Fuel\Helpers;

use ArrayAccess;
use InvalidArgumentException;

use function array_key_exists;
use function array_values;
use function array_search;
use function array_keys;
use function array_pop;
use function array_map;
use function array_sum;
use function array_unshift;
use function array_shift;
use function array_combine;
use function array_filter;
use function array_splice;
use function array_slice;
use function implode;
use function is_array;
use function is_int;
use function explode;
use function count;
use function strpos;
use function strtolower;
use function is_object;
use function stripos;
use function is_numeric;
use function preg_match;
use function preg_replace;
use function abs;
use function asort;
use function arsort;
use function sprintf;
use function call_user_func_array;

use const ARRAY_FILTER_USE_BOTH;

/**
 * The Arr class provides a few nice functions for making
 * dealing with arrays easier
 *
 * @since 1.0.0
 */
class Arr
{
    /**
     * -----------------------------------------------------------------------------
     * Gets a dot-notated key from an array, with a default value if it does
     * not exist.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function get(Iterable $array, string|int $key = null, mixed $default = null): mixed
    {
        // if no key is given, return the entire array
        if (null === $key)
        {
            return $array;
        }

        // check for a literal key
        if (array_key_exists($key, $array))
        {
            return $array[$key];
        }

        // check for a dot-notation key
        foreach (explode('.', $key) as $key_part)
        {
            if (false === ($array instanceof ArrayAccess and isset($array[$key_part])))
            {
                if ( ! is_array($array) or ! array_key_exists($key_part, $array))
                {
                    return $default;
                }
            }

            $array = $array[$key_part];
        }

        return $array;
    }

    /**
     * -----------------------------------------------------------------------------
     * Gets a dot-notated key from an array, with a default value if it does
     * not exist.
     * -----------------------------------------------------------------------------
     *
     * @since 2.0.0
     */
    public static function getMultiple(Iterable $array, array $keys, mixed $default = null): array
    {
        // storage for the result
        $return = [];

        // fetch all keys
        foreach ($keys as $key)
        {
            $return[$key] = static::get($array, $key, $default);
        }

        // and return them
        return $return;
    }

    /**
     * -----------------------------------------------------------------------------
     * Set an array item (dot-notated) to the value.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function set(array|ArrayAccess &$array, string $key = null, mixed $value = null): void
    {
        // if no key is given, set the array to the value passed
        if (null === $key)
        {
            $array = $value;
        }

        // treat the key as a dot-notation key
        else
        {
            $keys = explode('.', $key);

            while (count($keys) > 1)
            {
                $key = array_shift($keys);

                if ( ! isset($array[$key]) or ! is_array($array[$key]))
                {
                    $array[$key] = [];
                }

                $array =& $array[$key];
            }

            $array[array_shift($keys)] = $value;
        }
    }

    /**
     * -----------------------------------------------------------------------------
     * Set an array item (dot-notated) to the value.
     * -----------------------------------------------------------------------------
     *
     * @since 2.0.0
     */
    public static function setMultiple(array|ArrayAccess &$array, Iterable $keys): void
    {
        foreach ($keys as $key => $value)
        {
            static::set($array, $key, $value);
        }
    }

    /**
     * -----------------------------------------------------------------------------
     * Pluck an array of values from an array.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function pluck(Iterable $array, string $key, $index = null): array
    {
        // storage for the result
        $return = [];

        // whether or not the key uses dot-notation
        $dot_notation = (false !== strpos($key, '.'));

        // return an indexed array
        if ( ! $index)
        {
            foreach ($array as $i => $a)
            {
                $return[] = (is_object($a) and ! ($a instanceof ArrayAccess))
                    ? $a->{$key}
                    : ($dot_notation ? static::get($a, $key) : $a[$key]);
            }
        }

        // return an assoc array
        else
        {
            foreach ($array as $i => $a)
            {
                if (true !== $index)
                {
                    $i = (is_object($a) and ! ($a instanceof ArrayAccess))
                        ? $a->{$index}
                        : $a[$index];
                }
                $return[$i] = (is_object($a) and ! ($a instanceof ArrayAccess))
                    ? $a->{$key}
                    : ($dot_notation ? static::get($a, $key) : $a[$key]);
            }
        }

        return $return;
    }

    /**
     * -----------------------------------------------------------------------------
     * Array_key_exists with a dot-notated key from an array.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function has(array|ArrayAccess $array, string $key): bool
    {
        foreach (explode('.', $key) as $key_part)
        {
            if ( ! array_key_exists($key_part, $array))
            {
                return false;
            }

            $array = $array[$key_part];
        }

        return true;
    }

    /**
     * -----------------------------------------------------------------------------
     * Unsets dot-notated key from an array
     * -----------------------------------------------------------------------------
     *
     * @since 2.0.0
     */
    public static function delete(array|ArrayAccess &$array, string $key): bool
    {
        // process the parts
        $target =& $array;

        // split the key in it's parts
        $key_parts = explode('.', $key);

        while (true)
        {
            // check if this key exists, and bail out if it doesn't
            if (( ! is_array($target) and ! $target instanceOf ArrayAccess) or ! array_key_exists($key_parts[0], $target))
            {
                return false;
            }

            // last key component?
            if ( ! isset($key_parts[1]))
            {
                // delete the target found
                unset($target[$key_parts[0]]);
                return true;
            }

            // shift the target
            $target =& $target[$key_parts[0]];

            // and on to the next key part
            array_shift($key_parts);
        }
    }

    /**
     * -----------------------------------------------------------------------------
     * Unsets dot-notated key from an array
     * -----------------------------------------------------------------------------
     *
     * @since 2.0.0
     */
    public static function deleteMultiple(array &$array, Iterable $keys = []): array
    {
        // storage for the result
        $return = [];

        // procoess the keys
        foreach ($keys as $key)
        {
            $return[$key] = static::delete($array, $key);
        }

        return $return;
    }

    /**
     * -----------------------------------------------------------------------------
     * Converts a multi-dimensional associative array into an array of
     * key => values with the provided field names
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function assocToKeyval(Iterable $array, string $key_field, string $val_field): array
    {
        $output = [];

        foreach ($array as $row)
        {
            if (isset($row[$key_field]) and isset($row[$val_field]))
            {
                $output[$row[$key_field]] = $row[$val_field];
            }
        }

        return $output;
    }

    /**
     * -----------------------------------------------------------------------------
     * Converts the given 1 dimensional non-associative array to an associative
     * array. The array given must have an even number of elements.
     *
     *     Arr::to_assoc(['foo','bar']); // returns ['foo' => 'bar']
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function toAssoc(array|ArrayAccess $array): array
    {
        if (($count = count($array)) % 2 > 0)
        {
            throw new InvalidArgumentException('Fuel2: toAssoc() requires the number of values in the array to be even.');
        }

        $keys = [];
        $vals = [];

        for ($i = 0; $i < $count - 1; $i += 2)
        {
            $keys[] = array_shift($array);
            $vals[] = array_shift($array);
        }

        return array_combine($keys, $vals);
    }

    /**
     * -----------------------------------------------------------------------------
     * Checks if the given array is an assoc array.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function isAssoc(Iterable $array): bool
    {
        $counter = 0;

        foreach ($array as $key => $unused)
        {
            if ( ! is_int($key) or $key !== $counter++)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * -----------------------------------------------------------------------------
     * Flattens a multi-dimensional associative array down into a 1 dimensional
     * associative array.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function flatten(Iterable $array, string $glue = ':', bool $reset = true, bool $indexed = true): array
    {
        static $return = [];
        static $curr_key = [];

        if ($reset)
        {
            $return = [];
            $curr_key = [];
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
     * -----------------------------------------------------------------------------
     * Flattens a multi-dimensional associative array down into a 1 dimensional
     * associative array.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function flattenAssoc(Iterable $array, string $glue = ':', bool $reset = true): array
    {
        return static::flatten($array, $glue, $reset, false);
    }

    /**
     * -----------------------------------------------------------------------------
     * Reverse a flattened array in its original form.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function reverseFlatten(Iterable $array, string $glue = ':'): array
    {
        $return = [];

        foreach ($array as $key => $value)
        {
            if (false !== stripos($key, $glue))
            {
                $keys = explode($glue, $key);
                $temp =& $return;
                while (count($keys) > 1)
                {
                    $key = array_shift($keys);
                    $key = is_numeric($key) ? (int) $key : $key;
                    if ( ! isset($temp[$key]) or ! is_array($temp[$key]))
                    {
                        $temp[$key] = [];
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
     * -----------------------------------------------------------------------------
     * Filters an array on prefixed associative keys.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function filterPrefixed(Iterable $array, string $prefix, bool $removePrefix = true): array
    {
        $return = [];

        foreach ($array as $key => $val)
        {
            if (preg_match('/^' . $prefix . '/', $key))
            {
                if (true === $removePrefix)
                {
                    $key = preg_replace('/^' . $prefix . '/', '', $key);
                }
                $return[$key] = $val;
            }
        }

        return $return;
    }

    /**
     * -----------------------------------------------------------------------------
     * Recursive version of PHP's array_filter()
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function filterRecursive(Iterable $array, callable $callback = null): array
    {
        foreach ($array as &$value)
        {
            if (is_array($value))
            {
                $value = $callback === null
                    ? static::filterRecursive($value)
                    : static::filterRecursive($value, $callback);
            }
        }

        return null === $callback ? array_filter($array) : array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * -----------------------------------------------------------------------------
     * Removes items from an array that match a key prefix.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function removePrefixed(Iterable $array, string $prefix): array
    {
        foreach ($array as $key => $val)
        {
            if (preg_match('/^' . $prefix . '/', $key))
            {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * -----------------------------------------------------------------------------
     * Filters an array on suffixed associative keys.
     * -----------------------------------------------------------------------------
     *
     * @since 2.0.0
     */
    public static function filterSuffixed(Iterable $array, string $suffix, bool $removeSuffix = true): array
    {
        $return = [];

        foreach ($array as $key => $val)
        {
            if (preg_match('/' . $suffix . '$/', $key))
            {
                if (true === $removeSuffix)
                {
                    $key = preg_replace('/' . $suffix . '$/', '', $key);
                }
                $return[$key] = $val;
            }
        }

        return $return;
    }

    /**
     * -----------------------------------------------------------------------------
     * Removes items from an array that match a key suffix.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function removeSuffixed(Iterable $array, string $suffix): array
    {
        foreach ($array as $key => $val)
        {
            if (preg_match('/' . $suffix . '$/', $key))
            {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * -----------------------------------------------------------------------------
     * Filters an array by an array of keys
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function filterKeys(array|ArrayAccess $array, Iterable $keys, bool $remove = false): array
    {
        $return = [];

        foreach ($keys as $key)
        {
            if (array_key_exists($key, $array))
            {
                if ($remove)
                {
                    unset($array[$key]);
                }
                else
                {
                    $return[$key] = $array[$key];
                }
            }
        }

        return $remove ? $array : $return;
    }

    /**
     * -----------------------------------------------------------------------------
     * Insert value(s) into an array, mostly an array_splice alias
     * WARNING: original array is edited by reference
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function insert(array &$original, array $value, int $pos): void
    {
        if (count($original) < abs($pos))
        {
            throw new InvalidArgumentException('Position given is larger than number of elements in array in which to insert.');
        }

        array_splice($original, $pos, 0, $value);
    }

    /**
     * -----------------------------------------------------------------------------
     * Insert value(s) into an array, mostly an array_splice alias
     * WARNING: original array is edited by reference
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function insertAssoc(array &$original, array $values, $pos): void
    {
        if (count($original) < abs($pos))
        {
            throw new InvalidArgumentException('Position given is larger than number of elements in array in which to insert.');
        }

        $original = array_slice($original, 0, $pos, true) + $values + array_slice($original, $pos, null, true);
    }

    /**
     * -----------------------------------------------------------------------------
     * Insert value(s) into an array before a specific key
     * WARNING: original array is edited by reference
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function insertBeforeKey(array &$original, array $value, string|int $key, bool $isAssoc = false): void
    {
        $pos = array_search($key, array_keys($original));

        if (false === $pos)
        {
            throw new InvalidArgumentException('Unknown key before which to insert the new value into the array.');
        }

        $isAssoc
            ? static::insertAssoc($original, $value, $pos)
            : static::insert($original, $value, $pos);
    }

    /**
     * -----------------------------------------------------------------------------
     * Insert value(s) into an array after a specific key
     * WARNING: original array is edited by reference
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function insertAfterKey(array &$original, array $value, string|int $key, bool $isAssoc = false): void
    {
        $pos = array_search($key, array_keys($original));

        if (false === $pos)
        {
            throw new InvalidArgumentException('Unknown key after which to insert the new value into the array.');
        }

        $isAssoc
            ? static::insertAssoc($original, $value, $pos + 1)
            : static::insert($original, $value, $pos + 1);
    }

    /**
     * -----------------------------------------------------------------------------
     * Insert value(s) into an array after a specific value (first found in array)
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function insertAfterValue(array &$original, array $value, string|int $search, bool $isAssoc = false)
    {
        $key = array_search($search, $original);

        if (false === $key)
        {
            throw new InvalidArgumentException('Unknown value after which to insert the new value into the array.');
        }

        static::insertAfterKey($original, $value, $key, $isAssoc);
    }

    /**
     * -----------------------------------------------------------------------------
     * Insert value(s) into an array before a specific value (first found in array)
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function insertBeforeValue(array &$original, array $value, string|int $search, bool $isAssoc = false): void
    {
        $key = array_search($search, $original);

        if (false === $key)
        {
            throw new InvalidArgumentException('Unknown value before which to insert the new value into the array.');
        }

        static::insertBeforeKey($original, $value, $key, $isAssoc);
    }

    /**
     * -----------------------------------------------------------------------------
     * Sorts a multi-dimensional array by it's values.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function sort(array $array, string $key, string $order = 'asc', int $sortFlags = SORT_REGULAR): array
    {
        if ([] === $array)
        {
            return $array;
        }

        $b = [];

        foreach ($array as $k => $v)
        {
            $b[$k] = static::get($v, $key);
        }

        switch (strtolower($order))
        {
            case 'asc':
                asort($b, $sortFlags);
                break;

            case 'desc':
                arsort($b, $sortFlags);
                break;

            default:
                throw new InvalidArgumentException(sprintf('Invalid value [%s]. Valid values are "asc" or "desc".', $order));
        }

        $c = [];

        foreach ($b as $key => $val)
        {
            $c[] = $array[$key];
        }

        return $c;
    }

    /**
     * -----------------------------------------------------------------------------
     * Sorts an array on multitiple values, with deep sorting support.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function multisort(array $array, array $conditions, bool $ignore_case = false): array
    {
        $temp = [];
        $keys = array_keys($conditions);

        foreach ($keys as $key)
        {
            $temp[$key] = static::pluck($array, $key, true);
            if ( ! is_array($conditions[$key]))
            {
                $conditions[$key] = array($conditions[$key]);
            }
        }

        $args = [];
        foreach ($keys as $key)
        {
            $args[] = $ignore_case ? array_map('strtolower', $temp[$key]) : $temp[$key];
            foreach ($conditions[$key] as $flag)
            {
                $args[] = $flag;
            }
        }

        $args[] =& $array;

        call_user_func_array('array_multisort', $args);

        return $array;
    }

    /**
     * -----------------------------------------------------------------------------
     * Find the average of an array
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function average(array $array): int
    {
        // empty array passed, lets not divide by 0
        if ([] === $array)
        {
            return 0;
        }

        return (array_sum($array) / count($array));
    }

    /**
     * -----------------------------------------------------------------------------
     * Replaces a key name in an array
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function replaceKey(array|ArrayAccess $array, string $old_key, string $new_key): array
    {
        return static::replaceKeys($array, array($old_key => $new_key));
    }

    /**
     * -----------------------------------------------------------------------------
     * Replaces old keys by new keys in an array
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function replaceKeys(array|ArrayAccess $array, array $replace): array
    {
        $result = [];

        foreach ($array as $key => $value)
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
     * -----------------------------------------------------------------------------
     * Merge 2 arrays recursively, differs in 2 important ways from array_merge_recursive()
     * - When there's 2 different values and not both arrays, the latter value overwrites the earlier
     *   instead of merging both into an array
     * - Numeric keys that don't conflict aren't changed, only when a numeric key already exists is the
     *   value added using array_push()
     *
     * Expects multiple variables all of which must be arrays
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function merge(array ...$args): array
    {
        if (count($args) < 2)
        {
            throw new \InvalidArgumentException('merge() needs at minimum two arguments.');
        }

        $array = $args[0];
        array_unshift($args);

        foreach ($args as $arg)
        {
            foreach ($arg as $k => $v)
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
     * -----------------------------------------------------------------------------
     * Merge 2 arrays recursively, differs in 2 important ways from array_merge_recursive()
     * - When there's 2 different values and not both arrays, the latter value overwrites the earlier
     *   instead of merging both into an array
     * - Numeric keys are never changed
     *
     * Expects multiple variables all of which must be arrays
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
    */
    public static function mergeAssoc(array ...$args): array
    {
        if (count($args) < 2)
        {
            throw new \InvalidArgumentException('merge() needs at minimum two arguments.');
        }

        $array = array_unshift($args);

        foreach ($args as $arg)
        {
            foreach ($arg as $k => $v)
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
     * -----------------------------------------------------------------------------
     * Prepends a value with an asociative key to an array.
     * Will overwrite if the value exists.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function prepend(array &$array, string $key, mixed $value = null): void
    {
        static::prependArray($array, [$key => $value]);
    }

    /**
     * -----------------------------------------------------------------------------
     * Prepends a value with an asociative key to an array.
     * Will overwrite if the value exists.
     * -----------------------------------------------------------------------------
     *
     * @since 2.0.0
     */
    public static function prependArray(array &$array, array $keys): void
    {
        $array = $keys + $arr;
    }

    /**
     * -----------------------------------------------------------------------------
     * Recursive version of in_array()
     * -----------------------------------------------------------------------------
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function inArrayRecursive(mixed $needle, array $haystack, bool $strict = false): bool
    {
        foreach ($haystack as $value)
        {
            if (false === $strict and $needle == $value)
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
     * -----------------------------------------------------------------------------
     * Checks if the given array is a multidimensional array.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function isMulti(array $array, bool $all_keys = false): bool
    {
        $values = array_filter($array, 'is_array');
        return $all_keys ? count($array) === count($values) : count($values) > 0;
    }

    /**
     * -----------------------------------------------------------------------------
     * Searches the array for a given value and returns the
     * corresponding key or default value.
     *
     * If $recursive is set to true, then the Arr::search()
     * function will return a delimiter-notated key using $delimiter.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function search(array $array, mixed $value, string|int $default = null, bool $recursive = true, string $delimiter = '.', bool $strict = false): mixed
    {
        $key = array_search($value, $array, $strict);

        if (true === $recursive and false === $key)
        {
            $keys = [];
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
     * -----------------------------------------------------------------------------
     * Returns only unique values in an array. It does not sort. First value is used.
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function unique(array $array): array
    {
        // filter out all duplicate values
        return array_filter(
            $array,
            function ($item)
            {
                // contrary to popular belief, this is not as static as you think...
                static $vars = [];

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
            }
        );
    }

    /**
     * -----------------------------------------------------------------------------
     * Calculate the sum of a "column" in an array
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function sum(array $array, string $key): int
    {
        return array_sum(static::pluck($array, $key));
    }

    /**
     * -----------------------------------------------------------------------------
     * Returns the array with all numeric keys re-indexed, and string keys untouched
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function reIndex(array $array): array
    {
        // reindex this level
        $array = array_merge($array);

        foreach ($array as &$v)
        {
            if (is_array($v))
            {
                $v = static::reIndex($v);
            }
        }

        return $array;
    }

    /**
     * -----------------------------------------------------------------------------
     * Get the previous value or key from an array using the current array key
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function previousByKey(array $array, string $key, bool $getValue = false, bool $strict = false): mixed
    {
        // get the keys of the array
        $keys = array_keys($array);

        // and do a lookup of the key passed
        if (false === ($index = array_search($key, $keys, $strict)))
        {
            // key does not exist
            return false;
        }

        // check if we have a previous key
        elseif ( ! isset($keys[$index - 1]))
        {
            // there is none
            return null;
        }

        // return the value or the key of the array entry the previous key points to
        return true === $getValue ? $array[$keys[$index - 1]] : $keys[$index - 1];
    }

    /**
     * -----------------------------------------------------------------------------
     * Get the next value or key from an array using the current array key
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function nextByKey(array $array, string $key, bool $getValue = false, bool $strict = false): mixed
    {
        // get the keys of the array
        $keys = array_keys($array);

        // and do a lookup of the key passed
        if (false === ($index = array_search($key, $keys, $strict)))
        {
            // key does not exist
            return false;
        }

        // check if we have a previous key
        elseif ( ! isset($keys[$index + 1]))
        {
            // there is none
            return null;
        }

        // return the value or the key of the array entry the previous key points to
        return true === $getValue ? $array[$keys[$index + 1]] : $keys[$index + 1];
    }

    /**
     * -----------------------------------------------------------------------------
     * Get the previous value or key from an array using the current array value
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function previousByValue(array $array, mixed $value, bool $getValue = true, bool $strict = false): mixed
    {
        // find the current value in the array
        if (false === ($key = array_search($value, $array, $strict)))
        {
            // bail out if not found
            return false;
        }

        // get the list of keys, and find our found key
        $keys = array_keys($array);
        $index = array_search($key, $keys);

        // if there is no previous one, bail out
        if ( ! isset($keys[$index - 1]))
        {
            return null;
        }

        // return the value or the key of the array entry the previous key points to
        return true === $getValue ? $array[$keys[$index - 1]] : $keys[$index - 1];
    }

    /**
     * -----------------------------------------------------------------------------
     * Get the next value or key from an array using the current array value
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function nextByValue(array $array, mixed $value, bool $getValue = true, bool $strict = false): mixed
    {
        // find the current value in the array
        if (false === ($key = array_search($value, $array, $strict)))
        {
            // bail out if not found
            return false;
        }

        // get the list of keys, and find our found key
        $keys = array_keys($array);
        $index = array_search($key, $keys);

        // if there is no next one, bail out
        if ( ! isset($keys[$index + 1]))
        {
            return null;
        }

        // return the value or the key of the array entry the next key points to
        return true === $getValue ? $array[$keys[$index + 1]] : $keys[$index + 1];
    }

    /**
     * -----------------------------------------------------------------------------
     * Return the subset of the array defined by the supplied keys.
     *
     * Returns $default for missing keys, as with Arr::get()
     * -----------------------------------------------------------------------------
     *
     * @since 1.0.0
     */
    public static function subset(array $array, Iterable $keys, mixed $default = null): array
    {
        $result = [];

        foreach ($keys as $key)
        {
            static::set($result, $key, static::get($array, $key, $default));
        }

        return $result;
    }
}
