<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
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
	public $provides = array('datacontainer', 'cookiejar', 'format', 'date', 'num', 'str', 'inflector', 'debug');

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
			// get the config
			$stack = $dic->resolve('requeststack');
			if ($request = $stack->top())
			{
				$configInstance = $request->getComponent()->getConfig();
				$inputInstance = $request->getInput();
			}
			else
			{
				$configInstance = $dic->resolve('application::__main')->getRootComponent()->getConfig();
				$inputInstance = $dic->resolve('application::__main')->getRootComponent()->getInput();
			}
			$config = \Arr::merge($configInstance->load('format', true), $config);

			return $dic->resolve('Fuel\Common\Format', array($data, $from_type, $config, $inputInstance, $dic->resolve('inflector')));
		});

		// \Fuel\Common\Pagination
		$this->register('pagination', function ($dic, $view)
		{
			$stack = $dic->resolve('requeststack');
			if ($request = $stack->top())
			{
				$inputInstance = $request->getInput();
				$viewmanagerInstance = $request->getComponent()->getApplication()->getViewManager();
			}
			else
			{
				$app = $this->container->resolve('application::__main');
				$inputInstance = $app->getRootComponent()->getInput();
				$viewmanagerInstance = $app->getViewManager();
			}

			return $dic->resolve('Fuel\Common\Pagination', array($viewmanagerInstance, $inputInstance, $view));
		});

		// \Fuel\Common\Date
		$this->register('date', function ($dic, $time = "now", $timezone = null, Array $config = array())
		{
			// get the date config
			$stack = $dic->resolve('requeststack');
			if ($request = $stack->top())
			{
				$configInstance = $request->getComponent()->getConfig();
			}
			else
			{
				$configInstance = $dic->resolve('application::__main')->getRootComponent()->getConfig();
			}
			$config = \Arr::merge($configInstance->load('date', true), $config);

			return $dic->resolve('Fuel\Common\Date', array($time, $timezone, $config));
		});

		// \Fuel\Common\Num
		$this->register('num', function ($dic, Array $config = array(), Array $lang = array())
		{
			// get the config and the lang
			$stack = $dic->resolve('requeststack');
			if ($request = $stack->top())
			{
				$configInstance = $request->getComponent()->getConfig();
				$langInstance = $request->getComponent()->getLanguage();
			}
			else
			{
				$configInstance = $dic->resolve('application::__main')->getRootComponent()->getConfig();
				$langInstance = $dic->resolve('application::__main')->getRootComponent()->getLanguage();
			}
			$config = \Arr::merge($configInstance->load('num', true), $config);
			$lang = \Arr::merge($langInstance->load('byteunits', true), $lang);

			return $dic->resolve('Fuel\Common\Num', array($config, $lang));
		});

		// \Fuel\Common\Str
		$this->registerSingleton('str', function ($dic)
		{
			return $dic->resolve('Fuel\Common\Str');
		});

		// \Fuel\Common\Inflector
		$this->registerSingleton('inflector', function ($dic)
		{
			return $dic->resolve('Fuel\Common\Inflector', array(null, $dic->resolve('security'), $dic->resolve('str')));
		});

		// \Fuel\Common\Debug
		$this->registerSingleton('debug', function ($dic)
		{
			return $dic->resolve('Fuel\Common\Debug', array(null, $dic->resolve('inflector')));
		});
	}

}
