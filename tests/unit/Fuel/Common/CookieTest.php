<?php

namespace Fuel\Common;

use Codeception\TestCase\Test;

class CookieTest extends Test
{

	/**
	 * @var Cookie
	 */
	public $instance;

	/**
	 * @covers Fuel\Common\Cookie::__construct
	 * @group Common
	 */
	public function _before()
	{
		$this->instance = new Cookie('test', array(), null, new CookieStub());
	}

	/**
	 * @covers Fuel\Common\Cookie::isNew
	 * @group Common
	 */
	public function testIsNew()
	{
		$this->assertTrue($this->instance->isNew());

		$test2 = new Cookie('test-2', array(), 'value');
		$this->assertFalse($test2->isNew());
	}

	/**
	 * @covers Fuel\Common\Cookie::__call
	 * @group Common
	 */
	public function testGetterSetter()
	{
		$this->instance->setExpiration(10);
		$this->assertEquals(10, $this->instance->getExpiration());

		$this->assertEquals('test', $this->instance->getName());

		$this->instance->setValue('value');
		$this->assertEquals('value', $this->instance->getValue());

		$this->instance->setUnknown('something');
		$this->assertEquals(null, $this->instance->getUnknown());
	}

	/**
	 * @covers Fuel\Common\Cookie::__call
	 * @expectedException  InvalidArgumentException
	 * @group Common
	 */
	public function testGetterSetterInvalidArgument()
	{
		$this->instance->setExpiration();
	}

	/**
	 * @covers Fuel\Common\Cookie::delete
	 * @covers Fuel\Common\Cookie::isDeleted
	 * @group Common
	 */
	public function testDelete()
	{
		$this->assertFalse($this->instance->isDeleted());
		$this->instance->delete();
		$this->assertTrue($this->instance->isDeleted());
	}

	/**
	 * @covers Fuel\Common\Cookie::isSent
	 * @covers Fuel\Common\Cookie::send
	 * @group Common
	 */
	public function testSend()
	{
		$this->assertFalse($this->instance->isSent());
		$this->instance->send();

		$this->instance->delete();
		$this->instance->send();
		$this->assertTrue($this->instance->isSent());
	}

	/**
	 * @covers Fuel\Common\Cookie::__call
	 * @expectedException  RuntimeException
	 * @group Common
	 */
	public function testGetterSetterRuntimeException()
	{
		$this->instance->send();
		$this->assertTrue($this->instance->isSent());
		$this->instance->setValue('already-sent');
	}
}
