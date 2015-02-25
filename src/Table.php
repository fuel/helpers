<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common;

use Fuel\Common\Table\Row;
use Fuel\Common\Table\Cell;
use Fuel\Common\Table\EnumRowType;

/**
 * Deals with constructing table structures.
 *
 * @package Fuel\Common
 *
 * @since 2.0
 */
class Table
{
	/**
	 * @var array
	 */
	protected $rows = [];

	/**
	 * @var array
	 */
	protected $headerRows = [];

	/**
	 * @var array
	 */
	protected $footerRows = [];

	/**
	 * @var Row The row that new cells will be added to
	 */
	protected $currentRow = null;

	/**
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Adds a Cell to the current Row
	 *
	 * @param mixed $content    Anything that is not a Cell will be added as content to a new Cell
	 * @param array $attributes The array of attributes to assign the new Cell
	 *
	 * @return $this
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

		return $this;
	}

	/**
	 * Creates a new Cell with the given content
	 *
	 * @param mixed $content    The content for the new Cell
	 * @param array $attributes The attributes for the Cell
	 *
	 * @return Cell
	 */
	protected function constructCell($content = null, $attributes = array())
	{
		$cell = new Cell($content);
		$cell->setAttributes($attributes);

		return $cell;
	}

	/**
	 * Creates a new Row object and assigns it as the currently active row
	 *
	 * @param EnumRowType $type The type of the new row, uses Body by default
	 *
	 * @return $this
	 */
	public function createRow($type = EnumRowType::Body)
	{
		$this->currentRow = new Row;
		$this->currentRow->setType($type);

		return $this;
	}

	/**
	 * Adds the Row that's currently being constructed to the list of finished Rows
	 *
	 * @return $this
	 */
	public function addRow()
	{
		switch ( $this->currentRow->getType() )
		{
			case EnumRowType::Body:
				$this->rows[] = $this->currentRow;
				break;
			case EnumRowType::Header:
				$this->headerRows[] = $this->currentRow;
				break;
			case EnumRowType::Footer:
				$this->footerRows[] = $this->currentRow;
				break;
			default:
				throw new \InvalidArgumentException('Unknown row type');
		}

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
	 * Returns a list of currently constructed header rows
	 *
	 * @return array
	 */
	public function getHeaderRows()
	{
		return $this->headerRows;
	}

	/**
	 * Returns a list of currently constructed footer rows
	 *
	 * @return array
	 */
	public function getFooterRows()
	{
		return $this->footerRows;
	}

	/**
	 * Gets the currently active row. The row will not be added until addRow() is called
	 *
	 * @return Row
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
	 *
	 * @return $this
	 */
	public function setCurrentRowAttributes(array $attributes)
	{
		$this->getCurrentRow()->setAttributes($attributes);

		return $this;
	}

	/**
	 * Returns the attributes of this Table
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Sets the atributes of the Table
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
}
