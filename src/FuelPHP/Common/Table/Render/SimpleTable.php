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
	
	protected function container(\FuelPHP\Common\Table $table, array $rows)
	{
		$html = '<table';
		$this->addAttributes($html, $table->getAttributes());
		$html .= '><thead></thead><tbody>' .
			implode("\n", $rows) .
			'</tbody><tfoot></tfoot></table>';
		
		return $html;
	}
	
	protected function row(\FuelPHP\Common\Table\Row $row, array $cells)
	{
		$html = '<tr';
		$this->addAttributes($html, $row->getAttributes());
		$html .= '>' . implode('', $cells) . '</tr>';
		
		return $html;
	}
	
	protected function cell(\FuelPHP\Common\Table\Cell $cell)
	{
		$html = '<td';
		$this->addAttributes($html, $cell->getAttributes());
		$html .= '>' . $cell->getContent() . '</td>';
		
		return $html;
	}
	
	protected function addAttributes(&$html, array $attributesArray)
	{
		if( count($attributesArray) > 0)
		{
			$attributes = \FuelPHP\Common\Html::forge()
				->arrayToAttributes($attributesArray);
			
			$html .= ' ' . $attributes;
		}
	}
	
}
