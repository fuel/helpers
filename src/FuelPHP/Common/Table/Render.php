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

use \FuelPHP\Common\Table;

/**
 * Uses a table structure built with Table to create HTML
 *
 * @package FuelPHP\Common\Table
 * @since   2.0.0
 * @author  Fuel Development Team
 */
abstract class Render
{
	
	/**
	 * Renders the given table into a string. Output depends on the subclass.
	 * 
	 * @param  Table   $table
	 * @return string
	 */
	public function renderTable(Table $table)
	{
		//Generate each row
		$rows = array();
		
		foreach($table->getRows() as $row)
		{
			//Build the cells for each row
			$cells = array();
			
			foreach($row as $cell)
			{
				$cells[] = $this->cell($cell);
			}
			
			$rows[] = $this->row($row, $cells);
		}
		
		return $this->container($table, $rows);
	}
	
	/**
	 * Should generate the container tag, eg: &lt;table&gt;
	 * 
	 * @param Table  $table 
	 * @param array  $rows  The constructed rows to show
	 * @return mixed Should Ideally be a string that can be printed later.
	 */
	protected abstract function container(Table $table, array $rows);
	
	/**
	 * Renders a normal Row
	 * 
	 * @param  Row   $row   The current row being rendered
	 * @param  array $cells The constructed Cells that the current Row contains
	 * @return mixed Should ideally be a string that can be printed by
	 * container()
	 */
	protected abstract function row(Row $row, array $cells);
	
	/**
	 * Renders a normal cell
	 * 
	 * @param Cell 
	 */
	protected abstract function cell(Cell $cell);
	
}
