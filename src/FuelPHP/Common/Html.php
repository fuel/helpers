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

/**
 * Helper class to deal with common HTML related operations.
 *
 * @package FuelPHP\Common
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class Html
{
	private static $_instance = null;
	
	public static function forge()
	{
		if(is_null(static::$_instance))
		{
			static::$_instance = new static;
		}
		
		return static::$_instance;
	}
	
	private function __construct()
	{
		;
	}
	
	/**
	 * Returns a HTML tag.
	 * 
	 * @param string $name Name of the tag to render. (Eg, img, form, a...)
	 * @param null|string $value If not set to null will create a tag of the form "&lt;name&lt;value&lt;/name&lt;". Otherwise will render as a single tag.
	 * @param array $attributes Any attributes to apply to the tag to render
	 * @return string
	 */
	public function tag($name, $value=null, $attributes=array())
	{
		$tag = '<'.$name;
		
		return $tag;
	}
	
	/**
	 * Produces a string of html tag attributes from an array.
	 * 
	 * array('name' => 'test', 'foo' => 'bar')
	 * becomes:
	 * 'name="test" foo="bar"'
	 * 
	 * @param array $attributes
	 * @return string
	 */
	public function arrayToAttributes(array $attributes)
	{
		//Build a list of single attributes first
		$attributeList = array();
		foreach ($attributes as $key => $value)
		{
			$attributeList[] = $key . '="' . $value . '"';
		}
		
		return implode(' ', $attributeList);
	}
	
}
