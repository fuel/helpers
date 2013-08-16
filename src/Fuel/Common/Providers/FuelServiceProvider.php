<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common\Providers;

use Fuel\Dependency\ServiceProvider;

/**
 * FuelPHP ServiceProvider class for this package
 *
 * @package  Fuel\Common
 *
 * @since  2.0.0
 */
class FuelServiceProvider extends ServiceProvider
{
	/**
	 * @var  array  list of service names provided by this provider
	 */
	public $provides = array('datacontainer', 'cookiejar', 'format', 'date', 'num');

	/**
	 * Service provider definitions
	 */
	public function provide()
	{
		// \Fuel\Common\DataContainer
		$this->register('datacontainer', function ($dic, Array $data = array(), $readOnly = false)
		{
			return $dic->resolve('Fuel\Common\DataContainer', array($data, $readOnly));
		});

		// \Fuel\Common\CookieJar
		$this->register('cookiejar', function ($dic, Array $config = array(), Array $data = array())
		{
			return $dic->resolve('Fuel\Common\CookieJar', array($config, $data));
		});

		// \Fuel\Common\Format
		$this->register('format', function ($dic, $data = null, $from_type = null, Array $config = array())
		{
			// get the format config
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$instance = $request->getApplication()->getConfig();
				$input = $request->getInput();
			}
			else
			{
				$instance = $this->container->resolve('application.main')->getConfig();
				$input = $this->container->resolve('application.main')->getInput();
			}
			$config = \Arr::merge($instance->load('format', true), $config);

			return $dic->resolve('Fuel\Common\Format', array($data, $from_type, $config, $input));
		});

		// \Fuel\Common\Date
		$this->register('date', function ($dic, $time = "now", $timezone = null, Array $config = array())
		{
			// get the date config
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$instance = $request->getApplication()->getConfig();
			}
			else
			{
				$instance = $this->container->resolve('application.main')->getConfig();
			}
			$config = \Arr::merge($instance->load('date', true), $config);

			return $dic->resolve('Fuel\Common\Date', array($time, $timezone, $config));
		});

		// \Fuel\Common\Num
		$this->register('num', function ($dic, Array $config = array(), Array $lang = array())
		{
			// get the format config
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$configInstance = $request->getApplication()->getConfig();
				$langInstance = $request->getApplication()->getLang();
			}
			else
			{
				$configInstance = $this->container->resolve('application.main')->getConfig();
				$langInstance = $this->container->resolve('application.main')->getLang();
			}
			$config = \Arr::merge($configInstance->load('num', true), $config);
			$lang = \Arr::merge($langInstance->load('byteunits', true), $lang);

			return $dic->resolve('Fuel\Common\Num', array($config, $lang));
		});
	}
}
