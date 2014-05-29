<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Kernel
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 */

/**
 * Define the DateTimeInterface for PHP versions < 5.5
 */
if ( ! interface_exists('DateTimeInterface', false))
{
	include __DIR__.'/../resources/DateTimeInterface.php';
}

if ( ! function_exists('importFile'))
{
	/**
	 * Scope isolated include.
	 *
	 * Prevents access to $this/self from included files.
	 */
	function importFile($__file__)
	{
		include $__file__;
	}
}

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

if ( ! function_exists('cleanpath'))
{
	/**
	 * Cleans a file path so that it does not contain absolute file paths.
	 *
	 * @param   string  the filepath
	 * @return  string  the clean path
	 */
	function cleanpath($path)
	{
		static $search = array(ROOTPATH, VENDORPATH, DOCROOT, '\\');
		static $replace = array('ROOTPATH/', 'VENDORPATH/', 'DOCROOT/', DIRECTORY_SEPARATOR);
		return str_ireplace($search, $replace, $path);
	}
}

if ( ! function_exists('strptime'))
{
	/**
	 * Ugly temporary windows fix because windows doesn't support strptime()
	 * It attempts conversion between glibc style formats and PHP's internal
	 * style format (no 100% match!). This really is some fugly code, but
	 * someone at PHP HQ decided strptime should output this awful array instead
	 * of a timestamp LIKE EVERYONE ELSE DOES!!!
	 */
	function strptime($input, $format)
	{
		// convert the format string from glibc to date format (where possible)
		$new_format = str_replace(
			array('%a', '%A', '%d', '%e', '%j', '%u', '%w', '%U'  , '%V', '%W'  , '%b', '%B', '%h', '%m', '%C'  , '%g', '%G', '%y', '%Y', '%H', '%k', '%I', '%l', '%M', '%p', '%P', '%r'     , '%R' , '%S', '%T'   , '%X'  , '%z', '%Z', '%c'  , '%D'   , '%F'   , '%s', '%x'  , '%n', '%t', '%%'),
			array('D' , 'l' , 'd' , 'j' , 'N' , 'z' , 'w' , '[^^]', 'W' , '[^^]', 'M' , 'F' , 'M' , 'm' , '[^^]', 'Y' , 'o' , 'y' , 'Y' , 'H' , 'G' , 'h' , 'g' , 'i' , 'A' , 'a' , 'H:i:s A', 'H:i', 's' , 'H:i:s', '[^^]', 'O' , 'T ', '[^^]', 'm/d/Y', 'Y-m-d', 'U' , '[^^]', "\n", "\t", '%'),
			$format
		);

		// parse the input
		$parsed = date_parse_from_format($new_format, $input);

		// parse succesful?
		if (is_array($parsed) and empty($parsed['errors']))
		{
			return array(
				'tm_year' => $parsed['year'] - 1900,
				'tm_mon'  => $parsed['month'] - 1,
				'tm_mday' => $parsed['day'],
				'tm_hour' => $parsed['hour'] ?: 0,
				'tm_min'  => $parsed['minute'] ?: 0,
				'tm_sec'  => $parsed['second'] ?: 0,
			);
		}
		else
		{
			$masks = array(
				'%d' => '(?P<d>[0-9]{2})',
				'%m' => '(?P<m>[0-9]{2})',
				'%Y' => '(?P<Y>[0-9]{4})',
				'%H' => '(?P<H>[0-9]{2})',
				'%M' => '(?P<M>[0-9]{2})',
				'%S' => '(?P<S>[0-9]{2})',
			);

			$rexep = "#" . strtr(preg_quote($format), $masks) . "#";

			if ( ! preg_match($rexep, $input, $result))
			{
				return false;
			}

			return array(
				"tm_sec"  => isset($result['S']) ? (int) $result['S'] : 0,
				"tm_min"  => isset($result['M']) ? (int) $result['M'] : 0,
				"tm_hour" => isset($result['H']) ? (int) $result['H'] : 0,
				"tm_mday" => isset($result['d']) ? (int) $result['d'] : 0,
				"tm_mon"  => isset($result['m']) ? ($result['m'] ? $result['m'] - 1 : 0) : 0,
				"tm_year" => isset($result['Y']) ? ($result['Y'] > 1900 ? $result['Y'] - 1900 : 0) : 0,
			);
		}
	}
}
