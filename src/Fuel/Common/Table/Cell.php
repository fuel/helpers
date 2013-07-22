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

/**
 * Defines a cell of a Row
 *
 * @package Fuel\Common\Table
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class Cell
{

	/**
	 * @var mixed The content of the Cell
	 */
	protected $content;

	/**
	 * @var array Contains the attributes to associate with this table.
	 */
	protected $attributes = array();

	/**
	 * Creates a new Cell and optionally sets the content.
	 *
	 * @param mixed $content
	 */
	public function __construct($content = null)
	{
		$this->setContent($content);
	}

	/**
	 * Gets the content of the Cell
	 *
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Sets the content of the Cell
	 *
	 * @param  mixed $content
	 * @return Cell
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * Sets the atributes of the Cell
	 *
	 * @param  array $newAttributes
	 * @return Cell
	 */
	public function setAttributes(array $newAttributes)
	{
		$this->attributes = $newAttributes;

		return $this;
	}

	/**
	 * Gets the attributes of this Cell
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

}
