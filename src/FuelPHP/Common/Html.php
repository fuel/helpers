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
abstract class Html
{
	
	/**
	 * Returns a HTML tag.
	 * 
	 * @param string $name Name of the tag to render. (Eg, img, form, a...)
	 * @param array $attributes Any attributes to apply to the tag to render
	 * @param null|string $content If not set to null will create a tag of the form "&lt;name&lt;content&lt;/name&lt;". Otherwise will render as a single tag.
	 * @return string
	 */
	public static function tag($name, $attributes=array(), $content=null)
	{
		$tag = '<'.$name;
		
		$attributeString = static::arrayToAttributes($attributes);
		
		//Add the attribute string if needed
		if ( ! empty($attributeString))
		{
			$tag .= ' '.$attributeString;
		}
		
		//Work out how we are going to close the tag
		if ( is_null($content))
		{
			//No content for the tag so just close it.
			$tag .= '/>';
		}
		else
		{
			$tag .= '>' . $content . '</' . $name . '>';
		}
		
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
	public static function arrayToAttributes(array $attributes)
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
