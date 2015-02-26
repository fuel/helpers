<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common\Providers;

use League\Container\ServiceProvider;
use Fuel\Common;

/**
 * Fuel ServiceProvider class for Common
 *
 * @package Fuel\Common
 *
 * @since 2.0
 */
class FuelServiceProvider extends ServiceProvider
{
	/**
	 * @var array
	 */
	protected $provides = [
		'arr',
		'datacontainer',
		'cookiejar',
		'format',
		'date',
		'num',
		'str',
		'inflector',
		'debug'
	];

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->add('arr', 'Fuel\Common\Arr');

		$this->container->add('datacontainer', function (array $data = [], $readOnly = false)
		{
			return new Common\DataContainer($data, $readOnly);
		});

		// \Fuel\Common\CookieJar
		$this->container->add('cookiejar', function (array $config = [], array $data = [])
		{
			return new Common\CookieJar($config, $data);
		});

		$this->container->add('format', function ($data = null, $fromType = null, array $config = [])
		{
			$configInstance = $this->container->get('configInstance');
			$input = $this->container->get('inputInstance');

			$config = \Arr::merge($configInstance->load('format', true), $config);

			$inflector = $this->container->get('inflector');

			return new Common\Format($data, $fromType, $config, $input, $inflector);
		});

		$this->container->add('pagination', function ($view)
		{
			$input = $this->container->get('inputInstance');
			$viewManager = $this->container->get('viewManagerInstance');

			return new Common\Pagination($viewManager, $input, $view);
		});

		$this->container->add('date', function ($time = "now", $timezone = null, array $config = [])
		{
			$configInstance = $this->container->get('configInstance');
			$config = \Arr::merge($configInstance->load('date', true), $config);

			return new Common\Date($time, $timezone, $config);
		});

		$this->container->add('num', function (array $config = [], array $lang = [])
		{
			$configInstance = $this->container->get('configInstance');
			$langInstance = $this->container->get('langInstance');

			$config = \Arr::merge($configInstance->load('num', true), $config);
			$lang = \Arr::merge($langInstance->load('byteunits', true), $lang);

			return new Common\Num($config, $lang);
		});

		$this->container->singleton('str', 'Fuel\Common\Str');

		$this->container->singleton('inflector', 'Fuel\Common\Inflector')
			->withArgument(null)
			->withArgument('security')
			->withArgument('str');

		$this->container->singleton('debug', 'Fuel\Common\Debug')
			->withArgument(null)
			->withArgument('inflector');
	}
}
