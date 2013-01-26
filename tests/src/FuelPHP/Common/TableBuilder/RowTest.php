<?php

namespace FuelPHP\Common\TableBuilder;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-01-25 at 15:51:45.
 */
class RowTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var Cell
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new Row;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		
	}

	/**
	 * @covers FuelPHP\Common\TableBuilder\Row::set
	 * @group Common
	 */
	public function testAddCell()
	{
		$this->object[] = new Cell();
		
		$this->assertEquals(1, count($this->object));
	}
	
	/**
	 * @covers FuelPHP\Common\TableBuilder\Row::set
	 * @expectedException \InvalidArgumentException
	 * @group Common
	 */
	public function testAddCellInvalid()
	{
		$this->object[] = 'failure';
	}

}
