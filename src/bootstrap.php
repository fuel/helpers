<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common;

/**
 * FuelPHP Composer library framework bootstrap
 */

// register the services of this composer library
\Dependency::getInstance()->registerService(new ServicesProvider);

// alias helper classes to global
\Alias::alias('Fuel\Common\Arr', 'Arr');
\Alias::alias('Fuel\Common\Str', 'Str');
\Alias::alias('Fuel\Common\Inflector', 'Inflector');

/**
 * FuelPHP Composer library application bootstrap
 */
return function($app) {

	// your app initialisation code here
};
