<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common\Table;

use \Fuel\Common\DataContainer;

/**
 * Defines a Row that contains Cells for a table.
 *
 * @package Fuel\Common\Table
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class Row extends DataContainer
{

	/**
	 * @var array Contains the attributes to associate with this table.
	 */
	protected $attributes = array();
	protected $type = EnumRowType::Body;

	/**
	 * Overrides the set method from DataContainer to ensure only Cells can be added
	 *
	 * @throws \InvalidArgumentException
	 */
	public function set($key, $value)
	{
		if ( !$value instanceof Cell )
		{
			throw new \InvalidArgumentException('Only Cells can be added to Rows');
		}

		parent::set($key, $value);
	}

	/**
	 * Sets the atributes of the Row
	 *
	 * @param  array $newAttributes
	 * @return Row
	 */
	public function setAttributes(array $newAttributes)
	{
		$this->attributes = $newAttributes;

		return $this;
	}

	/**
	 * Gets the attributes of this Row
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Gets the type of the row as defined by EnumRowType
	 *
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Sets the type of the row. Should be a value from EnumRowType.
	 *
	 * @param int $type
	 * @return \Fuel\Common\Table\Row
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

}
