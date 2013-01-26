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
 * 
 *
 * @package FuelPHP\Common
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class TableBuilder
{
	
	protected $_rows = array();
	
	protected $_currentRow = null;
	
	public function addCell($content)
	{
		if ( is_null($this->_currentRow) )
		{
			$this->createRow();
		}
		
		$this->_currentRow[] = new Cell($content);
		
		return $this;
	}
	
	protected function createRow()
	{
		$this->_currentRow = new Row;
	}

	public function addRow()
	{
		$this->_rows[] = $this->_currentRow;
		$this->_currentRow = null;
		
		return $this;
	}
	
	public function getRows()
	{
		return $this->_rows;
	}
}
