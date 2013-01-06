<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Common\Data;

use ArrayAccess;

/**
 * Generic data container
 *
 * @package  FuelPHP\Common
 *
 * @since  2.0.0
 */
class Container implements ArrayAccess
{
	/**
	 * @var  array
	 *
	 * @since  2.0.0
	 */
	protected $content;

	/**
	 * @var  bool
	 *
	 * @since  2.0.0
	 */
	protected $readOnly = true;

	/**
	 * Constructor
	 *
	 * @param  array  $content
	 *
	 * @since  2.0.0
	 */
	public function __construct(array $content = null, $readOnly = true)
	{
		$this->content = $content ?: array();
		$this->readOnly = is_bool($readOnly) ?: true;
	}

	/**
	 * Backdoor to add extra content to a readonly object
	 *
	 * Use of this is discouraged and should be considered FuelPHP-internal
	 *
	 * @access  protected
	 * @param   array  $extraContent
	 * @param   bool   $overwrite
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function _add(array $extraContent, $overwrite = false)
	{
		$this->content = $overwrite
			? array_merge($this->content, $extraContent)
			: $this->content + $extraContent;
	}

	/**
	 * Check if a key was set upon this bag's content
	 *
	 * @param   string  $key
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function has($key)
	{
		return strpos($key, '.') === false
			? isset($this->content[$key])
			: array_get_dot_key($key, $this->content, $return);
	}

	/**
	 * Get a key's value from this bag's content
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function get($key, $default = null)
	{
		if ( ! array_get_dot_key($key, $this->content, $return))
		{
			return __val($default);
		}

		return $return;
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
		return $this->offsetSet($key, $value);
	}

	/**
	 * Get this bag's entire content
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function all()
	{
		return $this->content;
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
	 *
	 * @since  2.0.0
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}

	/**
	 * Disallow setting values like an array
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function offsetSet($key, $value)
	{
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		if (strpos($key, '.') === false)
		{
			$this->content[$key] = $value;
		}
		else
		{
			array_set_dot_key($key, $this->content, $value);
		}
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
		if ($this->readOnly)
		{
			throw new \RuntimeException('Changing values on this Data Container is not allowed.');
		}

		if (strpos($key, '.') === false)
		{
			if (isset($this->content[$key]))
			{
				unset($this->content[$key]);
			}
		}
		else
		{
			array_set_dot_key($key, $this->content, null, true);
		}
	}
}
