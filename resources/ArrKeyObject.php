<?php
namespace Fuel\Common;

/**
 * test class to test Arr::get() with an object as key
 *
 * @codeCoverageIgnore
 */
class ArrKeyObject
{
	public $key = 'last';

	public function __toString()
	{
		return $this->key;
	}
}
