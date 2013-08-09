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
 * Cookie class, encapsulation of a browser cookie
 *
 * @package  Fuel\Common
 * @since  2.0.0
 */
class Cookie
{
	/**
	 * @var  string  The name of this cookie
	 */
	protected $name = null;

	/**
	 * @var  string  The value of this cookie
	 */
	protected $value = null;

	/**
	 * @var  array  Cookie class configuration defaults
	 */
	protected $config = array(
		'expiration'  => 0,           // int, Cookie expiration
		'path'        => '/',         // string, Cookie path
		'domain'      => null,        // string, Cookie domain
		'secure'      => false,       // bool, Send only over HTTPS
		'http_only'   => false,       // only accessible via HTTP client-side
	);

	/**
	 * @var  bool  Is this a new cookie, or one that was send to the server?
	 */
	protected $isNew = true;

	/**
	 * @var  bool  Wether or not we want to delete this cookie
	 */
	protected $isDeleted = false;

	/**
	 * @var  bool  Wether or not this cookie was already sent to the browser
	 */
	protected $isSent = false;

	/**
	 * Create a new cookie object, optionally load an existing cookie value
	 *
	 * @param  string  $name    Name of this cookie
	 * @param  array   $config  Configuration for this cookie
	 * @param  string  $value   Initial value to be set for this cookie
	 */
	public function __construct($name, Array $config = array(), $value = null)
	{
		// store the name and value passed
		$this->name = $name;
		$this->value = $value;

		// merge the config
		$this->config = array_merge($this->config, $config);

		// and if set flag this object as used
		$this->isNew = (func_num_args() < 2);
	}

	/**
	 * Magic getter/setter methods
	 *
	 * @throws  InvalidArgumentException  if a setter is called without a value
	 */
	public function __call($method, $arguments)
	{
		if (substr($method, 0,3) === 'get')
		{
			if (isset($this->config[$var = strtolower(substr($method, 3))]))
			{
				return $this->config[$var];
			}
			elseif ($var == 'value')
			{
				return $this->value;
			}
		}
		elseif (substr($method, 0,3) === 'set')
		{
			if (empty($arguments))
			{
				throw new \InvalidArgumentException($method.' is missing required parameter $value');
			}

			if (isset($this->config[$var = strtolower(substr($method, 3))]))
			{
				$this->config[$var] = $arguments[0];
			}
			elseif ($var == 'value')
			{
				if ($this->isSent)
				{
					throw new \RuntimeException('Cookie "'.$this->name.'" has already been send to the browser, no point updating it');
				}

				$this->value = (string) $arguments[0];

				// reset to new state
				$this->isNew = true;
				$this->isDeleted = false;
				$this->isSent = false;
			}
		}

		return null;
	}

	/**
	 * Return the state of this Cookie object
	 */
	public function delete()
	{
		$this->isDeleted = true;
	}

	/**
	 * Send this cookie to the client
	 *
	 * @since   2.0.0
	 */
	public function send()
	{
		if ( ! $this->isSent)
		{
			if ($this->isDeleted)
			{
				// delete the cookie by nullifying and expiring it
				return setcookie($this->name, null, -86400, $this->config['path'], $this->config['domain'], $this->config['secure'], $this->config['http_only']);
			}
			else
			{
				// set the cookie
				return setcookie($this->name, $this->value, $this->config['expiration'], $this->config['path'], $this->config['domain'], $this->config['secure'], $this->config['http_only']);
			}
		}

		$this->isSent = true;
	}

	/**
	 * Return the state of this Cookie object
	 */
	public function isNew()
	{
		return $this->isNew;
	}

	/**
	 * Return the state of this Cookie object
	 */
	public function isSent()
	{
		return $this->isSent;
	}

	/**
	 * Return the state of this Cookie object
	 */
	public function isDeleted()
	{
		return $this->isDeleted;
	}

}
