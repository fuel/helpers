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
 * Defines types of table rows. Eg, header, footer, body
 *
 * @package FuelPHP\Common\Table
 * @since   2.0.0
 * @author  Fuel Development Team
 */
abstract class EnumRowType
{

	const Header = 0;
	const Body = 1;
	const Footer = 2;

}
