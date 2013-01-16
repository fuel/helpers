<?php

use FuelPHP\Common\DataContainer;

class DataContainerTests extends PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException  OutOfBoundsException
	 */
	public function testGetSet()
	{
		$c = new DataContainer;
		$data = array('this' => 'here');
		$this->assertEquals(array(), $c->getContents());
		$c->setContents($data);
		$this->assertEquals($data, $c->getContents());
		$this->assertEquals($data, $c->all());
		$c->set('this', 'new');
		$this->assertEquals('new', $c->get('this'));
		$this->assertEquals('default', $c->get('nothing', 'default'));
		$c['what'] = 'this';
		$this->assertEquals('this', $c['what']);
		$c['exception'];
	}

	/**
	 * @expectedException  RuntimeException
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
	 * @expectedException  RuntimeException
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

	public function testDelete()
	{
		$c = new DataContainer;
		$this->assertFalse($c->delete('nope'));
		$c['deep.key'] = true;
		$this->assertTrue($c->delete('deep.key'));
		$this->assertFalse($c->delete('deep.key'));
		$this->assertFalse($c->delete('deep.other'));
	}
}