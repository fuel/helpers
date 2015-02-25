<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common\Table;

use Fuel\Common\DataContainer;

/**
 * Defines a Row that contains Cells for a table.
 *
 * @package Fuel\Common
 *
 * @since 2.0
 */
class Row extends DataContainer
{
	/**
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * @var integer
	 */
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
	 * Returns the attributes of this Row
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Sets the atributes of the Row
	 *
	 * @param array $newAttributes
	 *
	 * @return $this
	 */
	public function setAttributes(array $newAttributes)
	{
		$this->attributes = $newAttributes;

		return $this;
	}

	/**
	 * Returns the type of the row as defined by EnumRowType
	 *
	 * @return integer
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Sets the type of the row. Should be a value from EnumRowType
	 *
	 * @param integer $type
	 *
	 * @return $this
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}
}
