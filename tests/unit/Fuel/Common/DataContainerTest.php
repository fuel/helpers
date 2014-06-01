<?php

namespace Fuel\Common;

use Codeception\TestCase\Test;

class DataContainerTest extends Test
{
	/**
	 * @expectedException  \OutOfBoundsException
	 * @group Common
	 */
	public function testGetSet()
	{
		$c = new DataContainer;
		$data = array('this' => 'here');
		$this->assertEquals(array(), $c->getContents());
		$c->setContents($data);
		$this->assertEquals($data, $c->getContents());

		$p = new DataContainer;
		$p->what = 'where';
		$c->setParent($p);
		$data = array('this' => 'here', 'what' => 'where');
		$this->assertEquals($data, $c->getContents());
		$c->has('what');
		$this->assertEquals('where', $c->what);
		$this->assertTrue($c->delete('what'));
		$this->assertEquals(null, $c->what);
		$c->disableParent();

		$c->set('this', 'new');
		$this->assertEquals('new', $c->get('this'));
		$this->assertEquals('default', $c->get('nothing', 'default'));
		$c['what'] = 'this';
		$this->assertEquals('this', $c['what']);
		$this->assertEquals($c['what'], $c->what);

		$c['exception'];

	}

	/**
	 * @group Common
	 */
	public function testAddMultipleAsArray()
	{
		$c = new DataContainer();
		$c[] = 'foo';
		$c[] = 'bar';

		$this->assertEquals(2, count($c));
	}

	/**
	 * @expectedException  \RuntimeException
	 * @group Common
	 */
	public function testReadOnlyReplace()
	{
		$c = new DataContainer(array(), true);
		$c->setContents(array('new' => 'stuff'));
	}

	/**
	 * @expectedException  \RuntimeException
	 * @group Common
	 */
	public function testReadOnlyMerge()
	{
		$c = new DataContainer(array(), true);
		$c->merge(array('new' => 'stuff'));
	}

	/**
	 * @expectedException  \RuntimeException
	 * @group Common
	 */
	public function testReadOnly()
	{
		$c = new DataContainer(array(
			'some' => array(
				'data' => true,
			),
		));

		$this->assertTrue($c->get('some.data'));
		$this->assertFalse($c->isReadOnly());
		$c->set('some.thing', true);
		$c->setReadOnly(true);
		$c->set('some.other.thing', true);
	}

	/**
	 * @expectedException  \RuntimeException
	 * @group Common
	 */
	public function testReadOnlyArrayAccess()
	{
		$c = new DataContainer(array(
			'some' => array(
				'data' => true,
			),
		), true);

		unset($c['some']);
	}

	/**
	 * @group Common
	 */
	public function testIsModified()
	{
		$c = new DataContainer(array(
			'yes' => true,
		));
		$this->assertFalse($c->isModified());
		$c->set('yes', false);
		$this->assertTrue($c->isModified());
	}

	/**
	 * @group Common
	 */
	public function testHas()
	{
		$c = new DataContainer(array(
			'yes' => true,
		));

		$this->assertFalse($c->has('this'));
		$this->assertFalse(isset($c['this']));
		$this->assertTrue($c->has('yes'));
		$this->assertTrue(isset($c['yes']));
	}

	/**
	 * @group Common
	 */
	public function testDelete()
	{
		$c = new DataContainer;
		$this->assertFalse($c->delete('nope'));
		$c['deep.key'] = true;
		$this->assertTrue($c->delete('deep.key'));
		$this->assertFalse($c->delete('deep.key'));
		$this->assertFalse($c->delete('deep.other'));
		$this->assertFalse($c->delete('other.key'));
	}

	/**
	 * @group Common
	 */
	public function testMerge()
	{
		$c = new DataContainer(array(
			'this' => 'is',
			'nested' => array(
				'values' => 'awesome',
			),
			'set' => array(
				1, 2, 3
			),
		));

		$c->merge(new DataContainer(array(
			'nested' => array(
				'thing' => 'added',
			),
		)), array('set' => array('yeah')));

		$expected = array(
			'this' => 'is',
			'nested' => array(
				'values' => 'awesome',
				'thing' => 'added',
			),
			'set' => array(1,2,3,'yeah'),
		);

		$this->assertEquals($expected, $c->getContents());
	}

	/**
	 * @expectedException  \InvalidArgumentException
	 * @group Common
	 */
	public function testInvalidMerge()
	{
		$c = new DataContainer;
		$c->merge(1);
	}

	/**
	 * @group Common
	 */
	public function testIteratorAggregate()
	{
		$c = new DataContainer(array(
			'some' => 'value',
			'is' => 'this',
		));

		foreach($c as $key => $value)
		{
			$this->assertTrue($c->has($key));
			$this->assertEquals($value, $c[$key]);
		}
	}

	/**
	 * @group Common
	 */
	public function testParents()
	{
		$p = new DataContainer;
		$c = new DataContainer;

		$this->assertEquals(null, $c->getParent());
		$this->assertFalse($c->hasParent());

		$this->assertEquals($c, $c->setParent($p));
		$this->assertEquals($p, $c->getParent());
		$this->assertTrue($c->hasParent());

		$this->assertEquals($c, $c->disableParent());
		$this->assertFalse($c->hasParent());
		$this->assertEquals($c, $c->enableParent());
		$this->assertTrue($c->hasParent());

		$this->assertEquals($c, $c->setParent(null));
		$this->assertFalse($c->hasParent());
	}
}
