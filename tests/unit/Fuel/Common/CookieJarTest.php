<?php

namespace Fuel\Common;

use Codeception\TestCase\Test;

class CookieJarTest extends Test
{

	/**
	 * @var CookieJar
	 */
	public $instance;

	/**
	 * @var CookieJar
	 */
	public $parent;

	/**
	 * @covers Fuel\Common\CookieJar::__construct
	 * @covers Fuel\Common\CookieJar::setParent
	 * @group Common
	 */
	public function _before()
	{
		$this->instance = new CookieJar(array(), array('child' => 'value'), new TestCookieWrapper());
		$this->parent = new CookieJar(array(), array('parent' => 'value'), new TestCookieWrapper());
	}

	/**
	 * @covers Fuel\Common\CookieJar::setParent
	 * @covers Fuel\Common\CookieJar::hasParent
	 * @covers Fuel\Common\CookieJar::getParent
	 * @covers Fuel\Common\CookieJar::enableParent
	 * @covers Fuel\Common\CookieJar::disableParent
	 * @group Common
	 */
	public function testParents()
	{
		$this->assertEquals($this->instance, $this->instance->enableParent());
		$this->assertFalse($this->instance->hasParent());

		$result = $this->instance->setParent($this->parent);
		$this->assertEquals($result, $this->instance);
		$this->assertEquals($this->parent, $this->instance->getParent());

		$this->assertTrue($this->instance->hasParent());
		$this->assertEquals($this->instance, $this->instance->disableParent());
		$this->assertFalse($this->instance->hasParent());
		$this->assertEquals($this->instance, $this->instance->enableParent());
		$this->assertTrue($this->instance->hasParent());

		$this->instance->setParent(null);
		$this->assertEquals(null, $this->instance->getParent());
		$this->assertFalse($this->instance->hasParent());
	}

	/**
	 * @covers Fuel\Common\CookieJar::has
	 * @covers Fuel\Common\CookieJar::enableParent
	 * @covers Fuel\Common\CookieJar::disableParent
	 * @group Common
	 */
	public function testHas()
	{
		$result = $this->instance->setParent($this->parent);

		$this->assertTrue($this->instance->has('child'));
		$this->assertTrue($this->instance->has('parent'));

		$this->instance->disableParent();
		$this->assertTrue($this->instance->has('child'));
		$this->assertFalse($this->instance->has('parent'));
		$this->assertTrue($this->parent->has('parent'));
	}

	/**
	 * @covers Fuel\Common\CookieJar::get
	 * @covers Fuel\Common\CookieJar::delete
	 * @group Common
	 */
	public function testGetDelete()
	{
		$result = $this->instance->setParent($this->parent);

		$result = $this->instance->get('child');
		$this->assertEquals($result, 'value');
		$result = $this->instance->get('parent');
		$this->assertEquals($result, 'value');
		$result = $this->instance->get('unknown', 'not found');
		$this->assertEquals($result, 'not found');

		$this->instance->disableParent();
		$result = $this->instance->get('parent', 'no parent');
		$this->assertEquals($result, 'no parent');


		$result = $this->instance->delete('child');
		$this->assertTrue($result);
		$result = $this->instance->delete('parent');
		$this->assertFalse($result);
		$this->instance->enableParent();
		$result = $this->instance->delete('parent');
		$this->assertTrue($result);
	}

	/**
	 * @covers Fuel\Common\CookieJar::set
	 * @covers Fuel\Common\CookieJar::get
	 * @group Common
	 */
	public function testSet()
	{
		$result = $this->instance->setParent($this->parent);

		$result = $this->instance->set('child', 'new value');
		$this->assertEquals($result, $this->instance);
		$result = $this->instance->get('child');
		$this->assertEquals($result, 'new value');

		$result = $this->instance->set('parent', 'new value');
		$this->assertEquals($result, $this->instance);
		$result = $this->instance->get('parent');
		$this->assertEquals($result, 'new value');

		$result = $this->instance->set('new key', 'new value');
		$result = $this->instance->get('new key');
		$this->assertEquals($result, 'new value');

		$result = $this->instance->set(array('new2' => 'value2'));
		$result = $this->instance->get('new2');
		$this->assertEquals($result, 'value2');

		$cookie = new Cookie('cookie', array(), 'cookie');
		$result = $this->instance->set('new2', $cookie);
		$result = $this->instance->get('new2');
		$this->assertEquals($result, 'cookie');

	}

	/**
	 * @covers Fuel\Common\CookieJar::send
	 * @covers Fuel\Common\CookieJar::setChild
	 * @group Common
	 */
	public function testSend()
	{
		$grandchild = new CookieJar(array(), array('grandchild' => 'value'), new TestCookieWrapper());
		$this->instance->setChild($grandchild);

		$result = $this->instance->send();
		$this->assertTrue($result);

		$result = $this->instance->set('child', 'new value');
		$result = $this->instance->send();
		$this->assertTrue($result);

		$this->instance = new CookieJar(array(), array('child' => 'value'), new TestCookieWrapper(false));
		$this->parent = new CookieJar(array(), array('parent' => 'value'), new TestCookieWrapper(false));
		$grandchild = new CookieJar(array(), array('grandchild' => 'value'), new TestCookieWrapper(false));
		$this->instance->setChild($grandchild);

		$result = $this->instance->send();
		$this->assertTrue($result);

		$result = $this->instance->set('child', 'new value');
		$result = $this->instance->send();
		$this->assertFalse($result);
		$result = $grandchild->set('grandchild', 'new value');
		$result = $this->instance->send();
		$this->assertFalse($result);
	}

	/**
	 * @covers Fuel\Common\CookieJar::merge
	 * @group Common
	 */
	public function testMerge()
	{
		$data1 = array('merge1' => 'test merge 1');
		$data2 = new DataContainer(array('merge2' => 'test merge 2'));
		$result = $this->instance->merge($data1, $data2);
		$this->assertEquals($result, $this->instance);

		$this->assertTrue($this->instance->has('merge1'));
		$this->assertTrue($this->instance->has('merge2'));
	}

	/**
	 * @covers Fuel\Common\CookieJar::getJar
	 * @covers Fuel\Common\CookieJar::getIterator
	 * @group Common
	 */
	public function testIterator()
	{
		$data = array();
		foreach ($this->instance as $key => $value)
		{
			$this->assertTrue($value instanceOf Cookie);
			$data[$key] = $value;
		}

		$this->assertEquals(1, count($data));
		$this->assertArrayHasKey('child', $data);


		$this->instance->setParent($this->parent);

		$data = array();
		foreach ($this->instance as $key => $value)
		{
			$this->assertTrue($value instanceOf Cookie);
			$data[$key] = $value;
		}

		$this->assertEquals(2, count($data));
		$this->assertArrayHasKey('child', $data);
		$this->assertArrayHasKey('parent', $data);
	}

	/**
	 * @covers Fuel\Common\CookieJar::getJar
	 * @covers Fuel\Common\CookieJar::offsetExists
	 * @covers Fuel\Common\CookieJar::offsetGet
	 * @covers Fuel\Common\CookieJar::offsetSet
	 * @covers Fuel\Common\CookieJar::offsetUnset
	 * @group Common
	 */
	public function testArrayAccess()
	{
		$this->assertTrue(isset($this->instance['child']));
		$this->assertFalse(isset($this->instance['parent']));

		$result = $this->instance->setParent($this->parent);
		$this->assertTrue(isset($this->instance['parent']));

		$result = $this->instance['child'];
		$this->assertTrue($result instanceOf Cookie);
		$this->assertEquals($result->getName(), 'child');
		$result = $this->instance['parent'];
		$this->assertTrue($result instanceOf Cookie);
		$this->assertEquals($result->getName(), 'parent');

		$this->instance['child2'] = $result;
		$this->assertEquals($this->instance['child2'], $this->instance['parent']);

		unset($this->instance['child']);
		unset($this->instance['parent']);
	}

	/**
	 * @covers Fuel\Common\CookieJar::offsetGet
	 * @expectedException  OutOfBoundsException
	 * @group Common
	 */
	public function testArrayAccessGetException()
	{
		$result = $this->instance['does-not-exist'];
	}

	/**
	 * @covers Fuel\Common\CookieJar::count
	 * @group Common
	 */
	public function testCount()
	{
		$this->assertEquals(1, $this->instance->count());
		$this->instance->setParent($this->parent);
		$this->assertEquals(2, $this->instance->count());
		$this->instance->disableParent();
		$this->assertEquals(1, $this->instance->count());
	}
}
