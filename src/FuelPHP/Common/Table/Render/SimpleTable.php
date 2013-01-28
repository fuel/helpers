<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Common\Table\Render
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Common\Table\Render;

/**
 * Uses a table structure built with Table to create a HTML table tag and
 * content.
 *
 * @package FuelPHP\Common\Table\Render
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class SimpleTable extends \FuelPHP\Common\Table\Render
{
	
	protected function container(array $rows)
	{
		return '<table><thead></thead><tbody>' .
			implode("\n", $rows) .
			'</tbody><tfoot></tfoot></table>';
	}
	
	protected function row(\FuelPHP\Common\Table\Row $row, array $cells)
	{
		return '<tr>' . implode('', $cells) . '</tr>';
	}
	
	protected function cell(\FuelPHP\Common\Table\Cell $cell)
	{
		return '<td>' . $cell->getContent() . '</td>';
	}
	
}
