<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common\Table\Render;

use Fuel\Common\Table\Render;
use Fuel\Common\Table\Cell;
use Fuel\Common\Table\Row;
use Fuel\Common\Table;
use Fuel\Common\Html;

/**
 * Uses a table structure built with Table to create a HTML table tag and
 * content.
 *
 * @package Fuel\Common\Table\Render
 * @since   2.0.0
 * @author  Fuel Development Team
 */
class SimpleTable extends Render
{

	/**
	 * Generates a "correct" table tag to contain the rendered rows.
	 *
	 * @param  Table   $table
	 * @param  array   $rows
	 * @param  array   $headers
	 * @param  array   $footers
	 * @return string
	 */
	protected function container(Table $table, array $rows, array $headers, array $footers)
	{
		$html = '<table';
		$this->addAttributes($html, $table->getAttributes());
		$html .= '><thead>';
		$html .= implode("\n", $headers);
		$html .= '</thead><tbody>';
		$html .= implode("\n", $rows);
		$html .= '</tbody><tfoot>';
		$html .= implode("\n", $footers);
		$html .= '</tfoot></table>';

		return $html;
	}

	/**
	 * Generates a tr with the given rendered cells.
	 *
	 * @param  Row     $row
	 * @param  array   $cells
	 * @return string
	 */
	protected function row(Row $row, array $cells)
	{
		$html = '<tr';
		$this->addAttributes($html, $row->getAttributes());
		$html .= '>' . implode('', $cells) . '</tr>';

		return $html;
	}

	/**
	 * Creates a td tag using the given Cell
	 *
	 * @param Cell     $cell
	 * @return string
	 */
	protected function cell(Cell $cell)
	{
		$html = '<td';
		$this->addAttributes($html, $cell->getAttributes());
		$html .= '>' . $cell->getContent() . '</td>';

		return $html;
	}

	/**
	 * Helper function to convert an array into a list of attributes and append
	 * them to a string.
	 *
	 * @param string $html        The string to append the attributes to. Passed by reference.
	 * @param array  $attributes  The key-value array that defines the attributes.
	 */
	protected function addAttributes(&$html, array $attributes)
	{
		if ( count($attributes) > 0 )
		{
			$attributes = Html::arrayToAttributes($attributes);

			$html .= ' ' . $attributes;
		}
	}

	protected function footerCell(Table\Cell $cell)
	{
		return $this->cell($cell);
	}

	protected function footerRow(Table\Row $row, array $cells)
	{
		return $this->row($row, $cells);
	}

	protected function headerCell(Table\Cell $cell)
	{
		$html = '<th';
		$this->addAttributes($html, $cell->getAttributes());
		$html .= '>' . $cell->getContent() . '</th>';

		return $html;
	}

	protected function headerRow(Table\Row $row, array $cells)
	{
		return $this->row($row, $cells);
	}

}
