<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Common;

use ArrayAccess;
use InvalidArgumentException;

/**
 * Generic data container
 *
 * @package  FuelPHP\Common
 *
 * @since  2.0.0
 */
class DataContainer implements ArrayAccess
{
	/**
	 * @var  array
	 *
	 * @since  2.0.0
	 */
	protected $data = array();

	/**
	 * @var  bool
	 *
	 * @since  2.0.0
	 */
	protected $readOnly = false;

	/**
	 * Constructor
	 *
	 * @param  array  $data
	 *
	 * @since  2.0.0
	 */
	public function __construct(array $data = null, $readOnly = false)
	{
		$this->data = $data ?: array();
		$this->readOnly = $readOnly;
	}


	/**
	 * Replace the container's data.
	 *
	 * @param   array  $data  new data
	 * @return  $this
	 * @throws  RuntimeException
	 */
	public function setContents(array $data)
	{
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		$this->data = $data;

		return $this;
	}

	/**
	 * Get the container's data
	 *
	 * @return  array  container's data
	 */
	public function getContents()
	{
		return $this->data;
	}

	/**
	 * Set wether the container is read-only.
	 *
	 * @param   boolean  $readOnly  wether it's a read-only container
	 * @return  $this
	 */
	public function setReadOnly($readOnly = true)
	{
		$this->readOnly = (bool) $readOnly;

		return $this;
	}

	/**
	 * Merge the arrays into the container.
	 *
	 * @param   array  $arg  array to merge with
	 * @return  $this
	 * @throws  RuntimeException
	 */
	public function merge($arg)
	{
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		$arguments = func_get_args();
		$valid = true;

		$arguments = array_map(function ($array) use (&$valid)
		{
			if ($array instanceof DataContainer)
			{
				return $array->getContents();
			}

			return $array;
		}, $arguments);

		array_unshift($arguments, $this->data);
		$this->data = call_user_func_array('arr_merge', $arguments);

		return $this;
	}

	/**
	 * Check wether the container is read-only.
	 *
	 * @return  boolean  $readOnly  wether it's a read-only container
	 */
	public function isReadOnly()
	{
		return $this->readOnly;
	}

	/**
	 * Check if a key was set upon this bag's data
	 *
	 * @param   string  $key
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function has($key)
	{
		return arr_has($this->data, $key);
	}

	/**
	 * Get a key's value from this bag's data
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function get($key, $default = null)
	{
		return arr_get($this->data, $key, $default);
	}

	/**
	 * Set a config value
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function set($key, $value)
	{
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		arr_set($this->data, $key, $value);

		return $this;
	}

	public function delete($key)
	{
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		return arr_delete($this->data, $key);
	}

	/**
	 * Get this bag's entire data
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function all()
	{
		return $this->data;
	}

	/**
	 * Allow usage of isset() on the param bag as an array
	 *
	 * @param   string  $key
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function offsetExists($key)
	{
		return $this->has($key);
	}

	/**
	 * Allow fetching values as an array
	 *
	 * @param   string  $key
	 * @return  mixed
	 * @throws  OutOfBoundsException
	 * @since  2.0.0
	 */
	public function offsetGet($key)
	{
		return $this->get($key, function() use ($key)
		{
			throw new \OutOfBoundsException('Access to undefined index: '.$key);
		});
	}

	/**
	 * Disallow setting values like an array
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 *
	 * @since  2.0.0
	 */
	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Disallow unsetting values like an array
	 *
	 * @param   string  $key
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function offsetUnset($key)
	{
		return $this->delete($key);
	}
}
