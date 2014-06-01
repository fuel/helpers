<?php

namespace Fuel\Common;

use Codeception\TestCase\Test;

class StrTest extends Test
{

	/**
	 * @var Str
	 */
	public $instance;

	/**
	 * @group Common
	 */
	public function _before()
	{
		$this->instance = new Str();
	}

	public function truncate_provider()
	{
		return array(
			array(15, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'),
		);
	}

	/**
	 * Test for $this->instance->truncate()
	 *
	 * @test
	 * @dataProvider truncate_provider
	 */
	public function testTruncatePlain($limit, $string)
	{
		$output = $this->instance->truncate($string, $limit);
		$expected = 'Lorem ipsum dol...';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->truncate()
	 *
	 * @test
	 * @dataProvider truncate_provider
	 */
	public function testTruncateCustomContinuation($limit, $string)
	{
		$output = $this->instance->truncate($string, $limit, '..');
		$expected = 'Lorem ipsum dol..';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->truncate()
	 *
	 * @test
	 * @dataProvider truncate_provider
	 */
	public function testTruncateNotHtml($limit, $string)
	{
		$string = '<h1>'.$string.'</h1>';

		$output = $this->instance->truncate($string, $limit, '...', false);
		$expected = '<h1>Lorem ipsum...';
		$this->assertEquals($expected, $output);

		$output = $this->instance->truncate($string, $limit, '...', true);
		$expected = '<h1>Lorem ipsum dol...</h1>';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->truncate()
	 *
	 * @test
	 * @dataProvider truncate_provider
	 */
	public function testTruncateIsHtml($limit, $string)
	{
		$string = '<h1>'.$string.'</h1>';

		$output = $this->instance->truncate($string, $limit, '...', true);
		$expected = '<h1>Lorem ipsum dol...</h1>';
		$this->assertEquals($expected, $output);

		$string .= '&ellip; <h1>additional header</h1>';

		$output = $this->instance->truncate($string, $limit, '...', true);
		$expected = '<h1>Lorem ipsum dol...</h1>';
		$this->assertEquals($expected, $output);

		$string = '&ellip; <h1>short text</h1>';

		$output = $this->instance->truncate($string, $limit, '...', true);
		$expected = '&ellip; <h1>short text</h1>';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->truncate()
	 *
	 * @test
	 * @dataProvider truncate_provider
	 */
	public function testTruncateMultipleTags($limit, $string)
	{
		$limit = 400;
		$string = '<p><strong>'.$string.'</strong></p>';

		$output = $this->instance->truncate($string, $limit, '...', true);
		$this->assertEquals($string, $output);
	}

	/**
	 * Test for $this->instance->increment()
	 *
	 * @test
	 */
	public function testIncrement()
	{
		$values = array('valueA', 'valueB', 'valueC');

		for ($i = 0; $i < count($values); $i ++)
		{
			$output = $this->instance->increment($values[$i], $i);
			$expected = $values[$i].'_'.$i;

			$this->assertEquals($expected, $output);
		}
	}

	/**
	 * Test for $this->instance->lower()
	 *
	 * @test
	 */
	public function testLower()
	{
		$output = $this->instance->lower('HELLO WORLD');
		$expected = "hello world";

		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->upper()
	 *
	 * @test
	 */
	public function testUpper()
	{
		$output = $this->instance->upper('hello world');
		$expected = "HELLO WORLD";

		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->lcfirst()
	 *
	 * @test
	 */
	public function testLcfirst()
	{
		$output = $this->instance->lcfirst('Hello World');
		$expected = "hello World";

		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->ucfirst()
	 *
	 * @test
	 */
	public function testUcfirst()
	{
		$output = $this->instance->ucfirst('hello world');
		$expected = "Hello world";

		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->ucwords()
	 *
	 * @test
	 */
	public function testUcwords()
	{
		$output = $this->instance->ucwords('hello world');
		$expected = "Hello World";

		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->tr()
	 *
	 * @test
	 */
	public function testTr()
	{
		$output = $this->instance->tr(10);
		$expected = 10;
		$this->assertEquals($expected, $output);

		$output = $this->instance->tr(array('test'));
		$expected = array('test');
		$this->assertEquals($expected, $output);

		$output = $this->instance->tr('Your name is :name', array('name' => 'John'));
		$expected = 'Your name is John';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for $this->instance->random()
	 *
	 * @test
	 */
	public function testRandom()
	{
		// testing length
		$output = $this->instance->random('alnum', 34);
		$this->assertEquals(34, strlen($output));

		// testing alnum
		$output = $this->instance->random('alnum', 15);
		$this->assertTrue(ctype_alnum($output));

		// testing numeric
		$output = $this->instance->random('numeric', 20);
		$this->assertTrue(ctype_digit($output));

		// testing hexdec
		$output = $this->instance->random('hexdec', 22);
		$this->assertTrue(ctype_xdigit($output));

		// testing alpha
		$output = $this->instance->random('alpha', 35);
		$this->assertTrue(ctype_alpha($output));

		// testing nozero
		$output = $this->instance->random('nozero', 22);
		$this->assertFalse(strpos($output, '0'));

		// testing distinct
		$output = $this->instance->random('distinct', 34);
		$this->assertEquals(34, strlen($output));

		// testing unique
		$output = $this->instance->random('unique');
		$this->assertEquals(32, strlen($output));
		$this->assertTrue(ctype_xdigit($output));

		// testing sha1
		$output = $this->instance->random('sha1');
		$this->assertEquals(40, strlen($output));
		$this->assertTrue(ctype_xdigit($output));

		// testing basic
		$output = $this->instance->random('basic');
		$this->assertTrue(ctype_digit($output));
	}

	/**
	 * Test for $this->instance->is_json()
	 *
	 * @test
	 */
	public function testIsJson()
	{
		$values = array('fuelphp','is' => array('awesome' => true));

		$string = json_encode($values);
		$this->assertTrue($this->instance->is_json($string));

		$string = serialize($values);
		$this->assertFalse($this->instance->is_json($string));
	}

	/**
	 * Test for $this->instance->is_xml()
	 *
	 * @test
	 * @requires extension libxml
	 */
	public function testIsXml()
	{
		$valid_xml = '<?xml version="1.0" encoding="UTF-8"?>
					<phpunit colors="true" stopOnFailure="false" bootstrap="bootstrap_phpunit.php">
						<php>
							<server name="doc_root" value="../../"/>
							<server name="app_path" value="fuel/app"/>
							<server name="core_path" value="fuel/core"/>
							<server name="package_path" value="fuel/packages"/>
						</php>
					</phpunit>';

		$invalid_xml = '<?xml version="1.0" encoding="UTF-8"?>
					<phpunit colors="true" stopOnFailure="false" bootstrap="bootstrap_phpunit.php">
						<php>
							<server name="doc_root" value="../../"/>
							<server name="app_path" value="fuel/app"/>
							<server name="core_path" value="fuel/core"/>
							<server name="package_path" value="fuel/packages"/>
						</
					</phpunit>';

		$this->assertTrue($this->instance->is_xml($valid_xml));
		$this->assertFalse($this->instance->is_xml($invalid_xml));
	}

	/**
	 * Test for $this->instance->is_xml()
	 *
	 * @test
	 * @requires extension libxml
	 */
	public function testIsXmlException()
	{
	}

	/**
	 * Test for $this->instance->is_serialized()
	 *
	 * @test
	 */
	public function testIsSerialized()
	{
		$values = array('fuelphp','is' => array('awesome' => true));

		$string = json_encode($values);
		$this->assertFalse($this->instance->is_serialized($string));

		$string = serialize($values);
		$this->assertTrue($this->instance->is_serialized($string));
	}

	/**
	 * Test for $this->instance->is_html()
	 *
	 * @test
	 */
	public function testIsHtml()
	{
		$html = '<div class="row"><div class="span12"><strong>FuelPHP</strong> is a simple, flexible, <i>community<i> driven PHP 5.3 web framework based on the best ideas of other frameworks with a fresh start.</p>';
		$simple_string = strip_tags($html);

		$this->assertTrue($this->instance->is_html($html));
		$this->assertFalse($this->instance->is_html($simple_string));
	}

	/**
	 * Test for $this->instance->startsWith()
	 *
	 * @test
	 */
	public function testStartsWith()
	{
		$string = 'HELLO WORLD';

		$output = $this->instance->startsWith($string, 'HELLO');
		$this->assertTrue($output);

		$output = $this->instance->startsWith($string, 'hello');
		$this->assertFalse($output);

		$output = $this->instance->startsWith($string, 'hello', true);
		$this->assertTrue($output);
	}

	/**
	 * Test for $this->instance->endsWith()
	 *
	 * @test
	 */
	public function testEndsWith()
	{
		$string = 'HELLO WORLD';

		$output = $this->instance->endsWith($string, 'WORLD');
		$this->assertTrue($output);

		$output = $this->instance->endsWith($string, 'world');
		$this->assertFalse($output);

		$output = $this->instance->endsWith($string, 'world', true);
		$this->assertTrue($output);
	}

	/**
	 * Test for $this->instance->alternator()
	 *
	 * @test
	 */
	public function testAlternator()
	{
		$alt = $this->instance->alternator('one', 'two', 'three');

		$output = $alt();
		$expected = 'one';
		$this->assertEquals($output, $expected);

		$output = $alt(false);
		$expected = 'two';
		$this->assertEquals($output, $expected);

		$output = $alt();
		$expected = 'two';
		$this->assertEquals($output, $expected);

		$output = $alt();
		$expected = 'three';
		$this->assertEquals($output, $expected);

		$output = $alt();
		$expected = 'one';
		$this->assertEquals($output, $expected);
	}

}
