<?php
namespace Fuel\Common;

/**
 * mock for Security
 *
 * @codeCoverageIgnore
 */
class SecurityMock
{
	/**
	 * @param  mixed $input  a variable to strip tags from
	 *
	 * @return  mixed
	 */
	public function stripTags($value)
	{
		if ( ! is_array($value))
		{
			$value = filter_var($value, FILTER_SANITIZE_STRING);
		}
		else
		{
			foreach ($value as $k => $v)
			{
				$value[$k] = $this->stripTags($v);
			}
		}

		return $value;
	}
}
