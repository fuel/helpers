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
 * Defines a cell of a Row
 *
 * @package FuelPHP\Common\TableBuilder
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
	 * @return \FuelPHP\Common\TableBuilder\Cell
	 */
	public function setContent($content)
	{
		$this->_content = $content;
		return $this;
	}
}
