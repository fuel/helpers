<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Common\TableBuilder
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Common\TableBuilder;

/**
 * Defines a Row that contains Cells for a table.
 *
 * @package FuelPHP\Common\TableBuilder
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class Row extends \FuelPHP\Common\DataContainer
{
	
	/**
	 * Overrides the set method from DataContainer to ensure only Cells can be added
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function set($key, $value)
	{
		if ( ! $value instanceof Cell )
		{
			throw new \InvalidArgumentException('Only Cells can be added to Rows');
		}
		
		parent::set($key, $value);
	}
}
