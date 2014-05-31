<?php
/**
 * @package   Fuel\Common
 * @version   2.0
 * @author    Fuel Development Team
 * @license   MIT License
 * @copyright 2010 - 2014 Fuel Development Team
 * @link      http://fuelphp.com
 */

namespace Fuel\Common;

/**
 * Test wrapper for setcookie(), so we can fake calls and results
 */
class TestCookieWrapper
{
	public $return = true;

	public function __construct($return = true)
	{
		$this->return = $return;
	}

	public function setcookie($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false)
	{
		return $this->return;
	}
}
