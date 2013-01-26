<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Common
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Common;

require_once('..'.DIRECTORY_SEPARATOR.'helpers.php');

/**
 * Common functions to deal with arrays.
 *
 * @package FuelPHP\Common
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class Arr
{
	
	public function delete(array &$array, $dotkey)
	{
		return arr_delete($array, $dotkey);
	}
	
	public function get(array $array, $dotkey, $default = null)
	{
		return arr_get($array, $dotkey, $default);
	}
	
	public function has(array $array, $dotkey)
	{
		return arr_has($array, $dotkey);
	}
	
	public function is_assoc(array $array)
	{
		return arr_is_assoc($array);
	}
	
	public function merge(array $array)
	{
		return arr_merge($array);
	}
	
	public function set(array &$array, $dotkey, $value = null)
	{
		return arr_set($array, $dotkey, $value);
	}
	
}
