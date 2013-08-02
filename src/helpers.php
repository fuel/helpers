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
		static $search = array(APPSPATH, VENDORPATH, DOCROOT, '\\');
		static $replace = array('APPSPATH/', 'VENDORPATH/', 'DOCROOT/', '/');
		return str_ireplace($search, $replace, $path);
	}
}
