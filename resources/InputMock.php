<?php
namespace Fuel\Common;

/**
 * mock for Input DataContainer
 *
 * @codeCoverageIgnore
 */
class InputMock
{
	/**
	 * @param  mixed $input  a variable to strip tags from
	 *
	 * @return  mixed
	 */
	public function getParam($name)
	{
		return null;
	}

	/**
	 * @return  array
	 */
	public function headers()
	{
		return array(
			'Content-Type' => 'application/json',
			'Content-Length' => 12345,
		);
	}
}
