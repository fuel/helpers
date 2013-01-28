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
 * Deals with constructing table structures.
 *
 * @package FuelPHP\Common
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class Table
{

	/**
	 * @var array Contains constructed rows
	 */
	protected $_rows = array();
	
	/**
	 * @var Row The row that new cells will be added to
	 */
	protected $_currentRow = null;
	
	/**
	 * @var array Contains the attributes to associate with this table.
	 */
	protected $_attributes = array();
	
	/**
	 * Adds a Cell to the current Row.
	 *
	 * @param mixed $content Anything that is not a Cell will be added as content to a new Cell
	 * @return \FuelPHP\Common\Table For method chaining
	 */
	public function addCell($content)
	{
		$currentRow = $this->getCurrentRow();
		//If we have been given a Cell then just add it, else create a new cell
		if ($content instanceof Table\Cell)
		{
			$currentRow[] = $content;
		}
		else
		{
			$currentRow[] = $this->constructCell($content);
		}

		//Return current object for method chaining
		return $this;
	}

	/**
	 * Creates a new Cell with the given content.
	 *
	 * @param mixed $content The content for the new Cell
	 * @return \FuelPHP\Common\Cell
	 */
	protected function constructCell($content=null)
	{
		return new Table\Cell($content);
	}

	/**
	 * Creates a new Row object and assigns it as the currently active row.
	 */
	protected function createRow()
	{
		$this->_currentRow = new Table\Row;
	}

	/**
	 * Adds the Row that's currently being constructed to the list of finished
	 * Rows.
	 *
	 * @return \FuelPHP\Common\Table
	 */
	public function addRow()
	{
		$this->_rows[] = $this->_currentRow;
		$this->_currentRow = null;

		return $this;
	}

	/**
	 * Returns a list of all currently consructed Rows
	 *
	 * @return array
	 */
	public function getRows()
	{
		return $this->_rows;
	}

	/**
	 * Gets the currently active row. The row will not be added until addRow()
	 * is called.
	 *
	 * @return type
	 */
	public function getCurrentRow()
	{
		if (is_null($this->_currentRow))
		{
			$this->createRow();
		}

		return $this->_currentRow;
	}
	
	/**
	 * Sets the atributes of the Table
	 * 
	 * @param array $newAttributes
	 * @return \FuelPHP\Common\Table
	 */
	public function setAttributes(array $newAttributes)
	{
		$this->_attributes = $newAttributes;
		
		return $this;
	}
	
	/**
	 * Gets the attributes of this Table
	 * 
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}
}
