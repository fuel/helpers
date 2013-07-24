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

use Fuel\Dependency\ServiceProvider;

/**
 * ServicesProvider class
 *
 * Defines the services published by this namespace to the DiC
 *
 * @package  Fuel\Common
 *
 * @since  2.0.0
 */
class ServicesProvider extends ServiceProvider
{
	/**
	 * @var  array  list of service names provided by this provider
	 */
	public $provides = array('datacontainer');

	/**
	 * Service provider definitions
	 */
	public function provide()
	{
		// \Fuel\Common\DataContainer
		$this->register('datacontainer', function ($dic, array $data = array(), $readOnly = false)
		{
			return new DataContainer($data, $readOnly);
		});
	}
}
