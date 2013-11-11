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
 * Tests for RegexFactoryTest
 *
 * @package Fuel\Common
 * @author  Fuel Development Team
 *
 * @covers  Fuel\Common\RegexFactory
 */
class RegexFactoryTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var RegexFactory
	 */
	protected $object;

	protected function setUp()
	{
		$this->object = new RegexFactory;

		return $this;
	}

	/**
	 * @coversDefaultClass __construct
	 * @coversDefaultClass get
	 * @group              Common
	 */
	public function testDefaultString()
	{
		$this->assertEquals(
			'',
			$this->object->get(false)
		);

		$this->assertEquals(
			'//',
			$this->object->get(true)
		);
	}

	/**
	 * @coversDefaultClass __toString
	 * @group              Common
	 */
	public function testToString()
	{
		$this->assertEquals(
			'//',
			(string) $this->object
		);
	}

	/**
	 * @coversDefaultClass value
	 * @group              Common
	 */
	public function testMatchValue()
	{
		$value = 'a';
		$this->object->value($value);

		$this->assertEquals(
			$value,
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass startGroupCapture
	 * @coversDefaultClass endGroupCapture
	 * @group              Common
	 */
	public function testGroupCapture()
	{
		$this->object->startGroupCapture();
		$this->object->endGroupCapture();

		$this->assertEquals(
			'()',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultGroup get
	 * @expectedException  \LogicException
	 * @group              Common
	 */
	public function testInvalidDelimiters()
	{
		$this->object->startGroupCapture();

		$this->object->get();
	}

	/**
	 * @coversDefaultClass startRange
	 * @coversDefaultClass endRange
	 * @group              Common
	 */
	public function testRange()
	{
		$this->object->startRange();
		$this->object->endRange();

		$this->assertEquals(
			'[]',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass lowercase
	 * @group              Common
	 */
	public function testLowercase()
	{
		$this->object->lowercase();

		$this->assertEquals(
			'a-z',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass uppercase
	 * @group              Common
	 */
	public function testUppercase()
	{
		$this->object->uppercase();

		$this->assertEquals(
			'A-Z',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass numeric
	 * @group              Common
	 */
	public function testNumeric()
	{
		$this->object->numeric();

		$this->assertEquals(
			'0-9',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass any
	 * @group              Common
	 */
	public function testAny()
	{
		$this->object->any();

		$this->assertEquals(
			'.',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass start
	 * @group              Common
	 */
	public function testStart()
	{
		$this->object->start();

		$this->assertEquals(
			'^',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass end
	 * @group              Common
	 */
	public function testEnd()
	{
		$this->object->end();

		$this->assertEquals(
			'$',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass noneOrOne
	 * @group              Common
	 */
	public function testNoneOrOne()
	{
		$this->object->noneOrOne();

		$this->assertEquals(
			'?',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass noneOrMany
	 * @group              Common
	 */
	public function testNoneOrMany()
	{
		$this->object->noneOrMany();

		$this->assertEquals(
			'*',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass oneOrMore
	 * @group              Common
	 */
	public function testOneOrMore()
	{
		$this->object->oneOrMore();

		$this->assertEquals(
			'+',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass addOr
	 * @group              Common
	 */
	public function testAddOr()
	{
		$this->object->addOr();

		$this->assertEquals(
			'|',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass matchQuantity
	 * @group              Common
	 */
	public function testMatchQuantity()
	{
		$this->object->matchQuantity(1);

		$this->assertEquals(
			'{1}',
			$this->object->get(false)
		);

		$this->object->reset();

		$this->object->matchQuantity(1, 13);

		$this->assertEquals(
			'{1,13}',
			$this->object->get(false)
		);
	}

	/**
	 * @coversDefaultClass caseInsensitive
	 * @group              Common
	 */
	public function testCaseInsensitive()
	{
		$this->object->caseInsensitive();

		$this->assertEquals(
			'//i',
			$this->object->get()
		);
	}

	/**
	 * @coversDefaultClass ignoreWhitespace
	 * @group              Common
	 */
	public function testIgnoreWhitespace()
	{
		$this->object->ignoreWhitespace();

		$this->assertEquals(
			'//x',
			$this->object->get()
		);
	}

	/**
	 * @coversDefaultClass singleSubstitution
	 * @group              Common
	 */
	public function testSingleSubstitution()
	{
		$this->object->singleSubstitution();

		$this->assertEquals(
			'//o',
			$this->object->get()
		);
	}

	/**
	 * @coversDefaultClass dotNewline
	 * @group              Common
	 */
	public function testDotNewline()
	{
		$this->object->dotNewline();

		$this->assertEquals(
			'//m',
			$this->object->get()
		);
	}

}
