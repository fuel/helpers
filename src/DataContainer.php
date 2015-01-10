<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common;

use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;
use Countable;
use InvalidArgumentException;

/**
 * Generic data container
 *
 * @package  Fuel\Common
 * @since  2.0.0
 */
class DataContainer implements ArrayAccess, IteratorAggregate, Countable
{
	/**
	 * @var    DataContainer  parent container, for inheritance
	 * @since  2.0.0
	 */
	protected $parent;

	/**
	 * @var    bool  wether we want to use parent cascading
	 * @since  2.0.0
	 */
	protected $parentEnabled = false;

	/**
	 * @var    array  container data
	 * @since  2.0.0
	 */
	protected $data = array();

	/**
	 * @var    bool   wether the container is read-only
	 * @since  2.0.0
	 */
	protected $readOnly = false;

	/**
	 * @var    bool   wether the container data has been modified
	 * @since  2.0.0
	 */
	protected $isModified = false;

	/**
	 * Constructor
	 *
	 * @param  array    $data      container data
	 * @param  boolean  $readOnly  wether the container is read-only
	 * @since  2.0.0
	 */
	public function __construct(array $data = array(), $readOnly = false)
	{
		$this->data = $data;
		$this->readOnly = $readOnly;
	}

	/**
	 * Get the parent of this container
	 *
	 * @return  DataContainer
	 * @since   2.0.0
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Set the parent of this container, to support inheritance
	 *
	 * @param   DataContainer  $parent  the parent container object
	 * @return  $this
	 * @since   2.0.0
	 */
	public function setParent(DataContainer $parent = null)
	{
		$this->parent = $parent;

		if ($this->parent)
		{
			$this->enableParent();
		}
		else
		{
			$this->disableParent();
		}

		return $this;
	}

	/**
	 * Enable the use of the parent object, if set
	 *
	 * @return  $this
	 * @since   2.0.0
	 */
	public function enableParent()
	{
		if ($this->parent)
		{
			$this->parentEnabled = true;
		}

		return $this;
	}

	/**
	 * Disable the use of the parent object
	 *
	 * @return  $this
	 * @since   2.0.0
	 */
	public function disableParent()
	{
		$this->parentEnabled = false;

		return $this;
	}

	/**
	 * Check whether or not this container has an active parent
	 *
	 * @return  bool
	 * @since   2.0.0
	 */
	public function hasParent()
	{
		return $this->parentEnabled;
	}

	/**
	 * Retrieve the modified state of the container
	 *
	 * @return  bool
	 * @since   2.0.0
	 */
	public function isModified()
	{
		return $this->isModified;
	}

	/**
	 * Replace the container's data.
	 *
	 * @param   array  $data  new data
	 * @return  $this
	 * @throws  RuntimeException
	 * @since   2.0.0
	 */
	public function setContents(array $data)
	{
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		$this->data = $data;

		$this->isModified = true;

		return $this;
	}

	/**
	 * Get the container's data
	 *
	 * @return  array  container's data
	 * @since   2.0.0
	 */
	public function getContents()
	{
		if ($this->parentEnabled)
		{
			return Arr::merge($this->parent->getContents(), $this->data);
		}
		else
		{
			return $this->data;
		}
	}

	/**
	 * Set wether the container is read-only.
	 *
	 * @param   boolean  $readOnly  wether it's a read-only container
	 * @return  $this
	 * @since   2.0.0
	 */
	public function setReadOnly($readOnly = true)
	{
		$this->readOnly = (bool) $readOnly;

		return $this;
	}

	/**
	 * Merge arrays into the container.
	 *
	 * @param   array  $arg  array to merge with
	 * @return  $this
	 * @throws  RuntimeException
	 * @since   2.0.0
	 */
	public function merge($arg)
	{
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		$arguments = array_map(function ($array) use (&$valid)
		{
			if ($array instanceof DataContainer)
			{
				return $array->getContents();
			}

			return $array;

		}, func_get_args());

		array_unshift($arguments, $this->data);
		$this->data = call_user_func_array(__NAMESPACE__.'\Arr::merge', $arguments);

		$this->isModified = true;

		return $this;
	}

	/**
	 * Check wether the container is read-only.
	 *
	 * @return  boolean  $readOnly  wether it's a read-only container
	 * @since   2.0.0
	 */
	public function isReadOnly()
	{
		return $this->readOnly;
	}

	/**
	 * isset magic method
	 */
	public function __isset($key)
	{
		return $this->has($key);
	}

	/**
	 * Check if a key was set upon this bag's data
	 *
	 * @param   string  $key
	 * @return  bool
	 * @since   2.0.0
	 */
	public function has($key)
	{
		$result = Arr::has($this->data, $key);

		if ( ! $result and $this->parentEnabled)
		{
			$result = $this->parent->has($key);
		}

		return $result;
	}

	/**
	 * get magic method
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Get a key's value from this bag's data
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  mixed
	 * @since   2.0.0
	 */
	public function get($key, $default = null)
	{
		$fail = uniqid('__FAIL__', true);

		$result = Arr::get($this->data, $key, $fail);

		if ($result === $fail)
		{
			if ($this->parentEnabled)
			{
				$result = $this->parent->get($key, $default);
			}
			else
			{
				$result = result($default);
			}
		}

		return $result;
	}

	/**
	 * set magic method
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Set a config value
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @throws  \RuntimeException
	 * @since   2.0.0
	 */
	public function set($key, $value)
	{
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		$this->isModified = true;

		if ($key === null)
		{
			$this->data[] = $value;

			return $this;
		}

		Arr::set($this->data, $key, $value);

		return $this;
	}

	/**
	 * Delete data from the container
	 *
	 * @param   string   $key  key to delete
	 * @return  boolean  delete success boolean
	 * @since   2.0.0
	 */
	public function delete($key)
	{
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		$this->isModified = true;

		if (($result = Arr::delete($this->data, $key)) === false and $this->parentEnabled)
		{
			$result = $this->parent->delete($key);
		}

		return $result;
	}

	/**
	 * Allow usage of isset() on the param bag as an array
	 *
	 * @param   string  $key
	 * @return  bool
	 * @since   2.0.0
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
	 * @since   2.0.0
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
	 * @param  string  $key
	 * @param  mixed   $value
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
	 * @throws  RuntimeException
	 * @since   2.0.0
	 */
	public function offsetUnset($key)
	{
		return $this->delete($key);
	}

	/**
	 * IteratorAggregate implementation
	 *
	 * @return  IteratorAggregate  iterator
	 * @since   2.0.0
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getContents());
	}

	/**
	 * Countable implementation
	 *
	 * @return  int  number of items stored in the container
	 * @since   2.0.0
	 */
	public function count()
	{
		return count($this->getContents());
	}
}
