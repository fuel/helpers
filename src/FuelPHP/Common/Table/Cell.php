<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Common\Table
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Common\Table;

/**
 * Defines a cell of a Row
 *
 * @package FuelPHP\Common\Table
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class Cell
{
	
	/**
	 * @var mixed The content of the Cell
	 */
	protected $_content;
	
	/**
	 * @var array Contains the attributes to associate with this table.
	 */
	protected $_attributes = array();
	
	/**
	 * Creates a new Cell and optionally sets the content
	 * @param mixed $content
	 */
	public function __construct($content=null)
	{
		$this->setContent($content);
	}
	
	/**
	 * Gets the content of the Cell
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->_content;
	}
	
	/**
	 * Sets the content of the Cell
	 * 
	 * @param mixed $content
	 * @return \FuelPHP\Common\Table\Cell
	 */
	public function setContent($content)
	{
		$this->_content = $content;
		return $this;
	}
	
	/**
	 * Sets the atributes of the Cell
	 * 
	 * @param array $newAttributes
	 * @return \FuelPHP\Common\Table\Cell
	 */
	public function setAttributes(array $newAttributes)
	{
		$this->_attributes = $newAttributes;
		
		return $this;
	}
	
	/**
	 * Gets the attributes of this Cell
	 * 
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}
}
