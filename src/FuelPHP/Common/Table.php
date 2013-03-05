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

use FuelPHP\Common\Table\Row;
use FuelPHP\Common\Table\Cell;
use FuelPHP\Common\Table\EnumRowType;

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
	protected $rows = array();

	/**
	 * @var Row The row that new cells will be added to
	 */
	protected $currentRow = null;

	/**
	 * @var array Contains the attributes to associate with this table.
	 */
	protected $attributes = array();

	/**
	 * Adds a Cell to the current Row.
	 *
	 * @param  mixed $content    Anything that is not a Cell will be added as content to a new Cell
	 * @param  array $attributes The array of attributes to assign the new Cell
	 * @return Table For method chaining
	 */
	public function addCell($content, $attributes = array())
	{
		$currentRow = $this->getCurrentRow();
		//If we have been given a Cell then just add it, else create a new cell
		if ( $content instanceof Cell )
		{
			$currentRow[] = $content;
		}
		else
		{
			$currentRow[] = $this->constructCell($content, $attributes);
		}

		//Return current object for method chaining
		return $this;
	}

	/**
	 * Creates a new Cell with the given content.
	 *
	 * @param  mixed $content    The content for the new Cell
	 * @param  array $attributes The attributes for the Cell
	 * @return Cell
	 */
	protected function constructCell($content = null, $attributes = array())
	{
		$cell = new Cell($content);
		$cell->setAttributes($attributes);

		return $cell;
	}

	/**
	 * Creates a new Row object and assigns it as the currently active row.
	 * @param EnumRowType $type The type of the new row, uses Body by default
	 */
	protected function createRow($type = EnumRowType::Body)
	{
		$this->currentRow = new Row;
		$this->currentRow->setType($type);
		
		return $this;
	}

	/**
	 * Adds the Row that's currently being constructed to the list of finished
	 * Rows.
	 *
	 * @return Table
	 */
	public function addRow()
	{
		$this->rows[] = $this->currentRow;
		$this->currentRow = null;

		return $this;
	}

	/**
	 * Returns a list of all currently consructed Rows
	 *
	 * @return array
	 */
	public function getRows()
	{
		return $this->rows;
	}

	/**
	 * Gets the currently active row. The row will not be added until addRow()
	 * is called.
	 *
	 * @return type
	 */
	public function getCurrentRow()
	{
		if ( is_null($this->currentRow) )
		{
			$this->createRow();
		}

		return $this->currentRow;
	}

	/**
	 * Sets the attributes for the currently active Row
	 * 
	 * @param array $attributes
	 * @return Table
	 */
	public function setCurrentRowAttributes(array $attributes)
	{
		$this->getCurrentRow()->setAttributes($attributes);
		return $this;
	}

	/**
	 * Sets the atributes of the Table
	 * 
	 * @param array $newAttributes
	 * @return Table
	 */
	public function setAttributes(array $newAttributes)
	{
		$this->attributes = $newAttributes;

		return $this;
	}

	/**
	 * Gets the attributes of this Table
	 * 
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

}
