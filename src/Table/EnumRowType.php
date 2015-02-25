<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common\Table;

/**
 * Defines types of table rows. Eg, header, footer, body
 *
 * @package Fuel\Common
 *
 * @since 2.0
 */
abstract class EnumRowType
{
	const Header = 0;
	const Body   = 1;
	const Footer = 2;
}
