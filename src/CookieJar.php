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

/**
 * Cookie Jar, a container for cookies
 *
 * @package  Fuel\Common
 * @since  2.0.0
 */
class CookieJar implements ArrayAccess, IteratorAggregate, Countable
{
	/**
	 * @var    CookieJar  parent jar, for inheritance
	 * @since  2.0.0
	 */
	protected $parent;

	/**
	 * @var    CookieJar  child jars, to maintain a chain
	 * @since  2.0.0
	 */
	protected $children = array();

	/**
	 * @var    bool  wether we want to use parent cascading
	 * @since  2.0.0
	 */
	protected $parentEnabled = false;

	/**
	 * @var    array  the cookie jar
	 * @since  2.0.0
	 */
	protected $jar = array();

	/**
	 * @var  array  configuration defaults
	 */
	protected $config = array(
		'expiration'  => 0,           // int, Cookie expiration
		'path'        => '/',         // string, Cookie path
		'domain'      => null,        // string, Cookie domain
		'secure'      => false,       // bool, Send only over HTTPS
		'http_only'   => false,       // only accessible via HTTP client-side
	);

	/**
	 * Constructor
	 *
	 * @param  array    $data      container data
	 * @param  boolean  $readOnly  wether the container is read-only
	 * @since  2.0.0
	 */
	public function __construct(Array $config = array(), Array $data = array(), $wrapper = null)
	{
		// merge the config
		$this->config = array_merge($this->config, $config);

		// process the data passed
		foreach ($data as $key => $value)
		{
			$this->jar[$key] = new Cookie($key, $this->config, $value, $wrapper ?: new SetcookieWrapper());
		}
	}

	/**
	 * Get the parent of this jar
	 *
	 * @return  CookieJar
	 * @since   2.0.0
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Set the parent of this cookie jar, to support inheritance
	 *
	 * @param   CookieJar  $parent  the parent cookie jar object
	 * @return  $this
	 * @since   2.0.0
	 */
	public function setParent(CookieJar $parent = null)
	{
		$this->parent = $parent;

		if ($this->parent)
		{
			$this->enableParent();
			$this->parent->setChild($this);
		}
		else
		{
			$this->disableParent();
		}

		return $this;
	}

	/**
	 * Enable the use of the parent jar, if set
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
	 * Check if this jar has a parent
	 *
	 * @return  bool
	 * @since   2.0.0
	 */
	public function hasParent()
	{
		return $this->parentEnabled === true;
	}

	/**
	 * Check if we have this cookie in the jar
	 *
	 * @param   string  $key
	 * @return  bool
	 * @since   2.0.0
	 */
	public function has($key)
	{
		if ( ! isset($this->jar[$key]) or $this->jar[$key]->isDeleted())
		{
			if ( ! $this->parentEnabled or ! $this->parent->has($key))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Remove a cookie from the cookie jar
	 *
	 * @param   string   $key  key to delete
	 * @return  boolean  delete success boolean
	 * @since   2.0.0
	 */
	public function delete($key)
	{
		if (isset($this->jar[$key]) and ! $this->jar[$key]->isDeleted())
		{
			return $this->jar[$key]->delete();
		}
		elseif ($this->has($key))
		{
			return $this->parent->delete($key);
		}

		return false;
	}

	/**
	 * Get a cookie's value from the cookie jar
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  mixed
	 * @since   2.0.0
	 */
	public function get($key, $default = null)
	{
		if (isset($this->jar[$key]) and ! $this->jar[$key]->isDeleted())
		{
			return $this->jar[$key]->getValue();
		}
		elseif ($this->has($key))
		{
			return $this->parent->get($key, $default);
		}

		return $default;
	}

	/**
	 * Set a cookie to a new value
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @throws  \InvalidArgumentException
	 * @since   2.0.0
	 */
	public function set($key, $value = null)
	{
		if (is_array($key))
		{
			foreach ($key as $arraykey => $value)
			{
				$this->set($arraykey, $value);
			}
		}
		else
		{
			if (isset($this->jar[$key]) and ! $this->jar[$key]->isDeleted())
			{
				if ($value instanceOf Cookie)
				{
					$this->jar[$key] = $value;
				}
				else
				{
					$this->jar[$key]->setValue($value);
				}
			}
			elseif ($this->has($key))
			{
				$this->parent->set($key, $value);
			}
			else
			{
				$this->jar[$key] = new Cookie($key, $this->config, $value);
			}
		}

		return $this;
	}

	/**
	 * Send all cookies to the client
	 *
	 * @return  bool
	 * @since   2.0.0
	 */
	public function send()
	{
		$result = true;

		// process the cookies in this jar
		foreach ($this->jar as $cookie)
		{
			if ( ! $cookie->send())
			{
				$result = false;
			}
		}

		// and the cookies in this jar's children
		foreach ($this->children as $cookie)
		{
			if ( ! $cookie->send())
			{
				$result = false;
			}
		}

		return $result;
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
		$arguments = array_map(function ($array)
		{
			if ($array instanceof DataContainer)
			{
				return $array->getContents();
			}

			return $array;

		}, func_get_args());

		$data = call_user_func_array(__NAMESPACE__.'\Arr::merge', $arguments);

		$this->set($data);

		return $this;
	}

	/**
	 * Allows the ArrayIterator to fetch parent data
	 *
	 * @since   2.0.0
	 */
	protected function getJar()
	{
		if ($this->parentEnabled)
		{
			return array_merge($this->parent->getJar(), $this->jar);
		}
		else
		{
			return $this->jar;
		}
	}

	/**
	 * Register a child of this cookie jar, to support inheritance
	 *
	 * @param   CookieJar  $child  the child cookie jar object
	 *
	 * @since   2.0.0
	 */
	public function setChild(CookieJar $child)
	{
		if ( ! in_array($child, $this->children))
		{
			$this->children[] = $child;
		}
	}

	/**
	 * Allow usage of isset() on the cookie jar as an array
	 *
	 * @param   string  $key
	 *
	 * @return  bool
	 *
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
		if (isset($this->jar[$key]) and ! $this->jar[$key]->isDeleted())
		{
			return $this->jar[$key];
		}
		elseif ($this->has($key))
		{
			return $this->parent[$key];
		}
		else
		{
			throw new \OutOfBoundsException('Access to undefined cookie: '.$key);
		}
	}

	/**
	 * Allow setting values like an array
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @since  2.0.0
	 */
	public function offsetSet($key, $value)
	{
		if ($value instanceOf Cookie)
		{
			$this->jar[$key] = $value;
		}
	}

	/**
	 * Allow unsetting values like an array
	 *
	 * @param   string  $key
	 * @since   2.0.0
	 */
	public function offsetUnset($key)
	{
		if (isset($this->jar[$key]))
		{
			$this->jar[$key]->delete();
		}
		elseif ($this->parentEnabled)
		{
			$this->parent->delete($key);
		}
	}

	/**
	 * IteratorAggregate implementation
	 *
	 * @return  IteratorAggregate  iterator
	 * @since   2.0.0
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getJar());
	}

	/**
	 * Countable implementation
	 *
	 * @return  int  number of items stored in the container
	 * @since   2.0.0
	 */
	public function count()
	{
		$count = count($this->jar);
		if ($this->parentEnabled)
		{
			$count += $this->parent->count();
		}
		return $count;
	}
}
