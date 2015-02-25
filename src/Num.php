<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @author     Chase "Syntaqx" Hutchins
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common;

/**
 * Numeric helper class. Provides additional formatting methods for working with
 * numeric values.
 *
 * Credit where credit is due:
 *
 * Techniques and inspiration were taken from all over, including:
 *	Kohana Framework: kohanaframework.org
 *	CakePHP: cakephp.org
 *
 * @since 1.0
 */
class Num
{
	/**
	 * @var  array  Byte units
	 */
	protected $byteUnits = array(
		'B'   => 0,
		'K'   => 10,
		'Ki'  => 10,
		'KB'  => 10,
		'KiB' => 10,
		'M'   => 20,
		'Mi'  => 20,
		'MB'  => 20,
		'MiB' => 20,
		'G'   => 30,
		'Gi'  => 30,
		'GB'  => 30,
		'GiB' => 30,
		'T'   => 40,
		'Ti'  => 40,
		'TB'  => 40,
		'TiB' => 40,
		'P'   => 50,
		'Pi'  => 50,
		'PB'  => 50,
		'PiB' => 50,
		'E'   => 60,
		'Ei'  => 60,
		'EB'  => 60,
		'EiB' => 60,
		'Z'   => 70,
		'Zi'  => 70,
		'ZB'  => 70,
		'ZiB' => 70,
		'Y'   => 80,
		'Yi'  => 80,
		'YB'  => 80,
		'YiB' => 80,
	);

	/**
	 * @var  array  Configuration values
	 */
	protected $config = array(
		// formatPhone()
		'phone' => '(000) 000-0000',

		// smartFormatPhone()
		'smartPhone' => array(
			7  => '000-0000',
			10 => '(000) 000-0000',
			11 => '0 (000) 000-0000',
		),

		// formatExp()
		'exp' => '00-00',

		// maskCreditCard()
		'creditCard' => '**** **** **** 0000',
	);

	/**
	 * Class constructor
	 *
	 * @param  array  $config     Configuration array
	 * @param  array  $byteUnits  Optional language dependent list of Byte Units
	 *
	 * @return  void
	 *
	 * @since 2.0.0
	 */
	public function __construct(Array $config = array(), Array $byteUnits = array())
	{
		$this->config = array_merge($this->config, $config);
		$this->byteUnits = array_merge($this->byteUnits, $byteUnits);
	}

	/**
	 * Converts a file size number to a byte value. File sizes are defined in
	 * the format: SB, where S is the size (1, 8.5, 300, etc.) and B is the
	 * byte unit (K, MiB, GB, etc.). All valid byte units are defined in
	 * $this->byteUnits
	 *
	 * Usage:
	 * <code>
	 * $num = new \Fuel\Common\Num();
	 * echo $num->bytes('200K');  // 204800
	 * echo $num->bytes('5MiB');  // 5242880
	 * echo $num->bytes('1000');  // 1000
	 * echo $num->bytes('2.5GB'); // 2684354560
	 * </code>
	 *
	 * @author     Kohana Team
	 * @copyright  (c) 2009-2011 Kohana Team
	 * @license    http://kohanaframework.org/license
	 *
	 * @param  string  File size in SB format
	 *
	 * @return  float
	 *
	 * @since 1.0.0
	 */
	public function bytes($size = 0)
	{
		// Prepare the size
		$size = trim((string) $size);

		// Construct an OR list of byte units for the regex
		$accepted = implode('|', array_keys($this->byteUnits));

		// Construct the regex pattern for verifying the size format
		$pattern = '/^([0-9]+(?:\.[0-9]+)?)('.$accepted.')?$/Di';

		// Verify the size format and store the matching parts
		if (!preg_match($pattern, $size, $matches))
		{
			throw new \Exception('The byte unit size, "'.$size.'", is improperly formatted.');
		}

		// Find the float value of the size
		$size = (float) $matches[1];

		// Find the actual unit, assume B if no unit specified
		$unit = Arr::get($matches, 2, 'B');

		// Convert the size into bytes
		$bytes = $size * pow(2, $this->byteUnits[$unit]);

		return $bytes;
	}

	/**
	 * Converts a number of bytes to a human readable number by taking the
	 * number of that unit that the bytes will go into it. Supports TB value.
	 *
	 * Note: Integers in PHP are limited to 32 bits, unless they are on 64 bit
	 * architectures, then they have 64 bit size. If you need to place the
	 * larger size then what the PHP integer type will hold, then use a string.
	 * It will be converted to a double, which should always have 64 bit length.
	 *
	 * @param  int  the byte number to format
	 * @param  int  number of decimals
	 *
	 * @return  boolean|string  formatted string, or false if formatting failed
	 *
	 * @since 1.0.0
	 */
	public function formatBytes($bytes = 0, $decimals = 0)
	{
		static $quant = array(
			'TB' => 1099511627776,  // pow( 1024, 4)
			'GB' => 1073741824,     // pow( 1024, 3)
			'MB' => 1048576,        // pow( 1024, 2)
			'KB' => 1024,           // pow( 1024, 1)
			'B ' => 1,              // pow( 1024, 0)
		);

		foreach ($quant as $unit => $mag )
		{
			if (doubleval($bytes) >= $mag)
			{
				return sprintf('%01.'.$decimals.'f', ($bytes / $mag)).' '.$unit;
			}
		}

		return false;
	}

	/**
	 * Converts a number into a more readable human-type number.
	 *
	 * Usage:
	 * <code>
	 * $num = new \Fuel\Common\Num();
	 * echo $num->quantity(7000); // 7K
	 * echo $num->quantity(7500); // 8K
	 * echo $num->quantity(7500, 1); // 7.5K
	 * </code>
	 *
	 * @param  int  Number to convert
	 * @param  int  Number of decimals in the converted result
	 *
	 * @return  string  The converted number
	 *
	 * @since 1.0.0
	 */
	public function quantity($num, $decimals = 0)
	{
		if ($num >= 1000 && $num < 1000000)
		{
			return sprintf('%01.'.$decimals.'f', (sprintf('%01.0f', $num) / 1000)).'K';
		}
		elseif ($num >= 1000000 && $num < 1000000000)
		{
			return sprintf('%01.'.$decimals.'f', (sprintf('%01.0f', $num) / 1000000)).'M';
		}
		elseif ($num >= 1000000000)
		{
			return sprintf('%01.'.$decimals.'f', (sprintf('%01.0f', $num) / 1000000000)).'B';
		}

		return $num;
	}

	/**
	 * Formats a number by injecting non-numeric characters in a specified
	 * format into the string in the positions they appear in the format.
	 *
	 * Usage:
	 * <code>
	 * $num = new \Fuel\Common\Num();
	 * echo $num->format('1234567890', '(000) 000-0000'); // (123) 456-7890
	 * echo $num->format('1234567890', '000.000.0000'); // 123.456.7890
	 * </code>
	 *
	 * @link    http://snippets.symfony-project.org/snippet/157
	 *
	 * @param  string  The string to format
	 * @param  string  The format to apply
	 *
	 * @return  string  Formatted number
	 *
	 * @since 1.0.0
	 */
	public function format($string, $format)
	{
		if(empty($format) or empty($string))
		{
			return $string;
		}

		$result = '';
		$fpos = 0;
		$spos = 0;

		while ((strlen($format) - 1) >= $fpos)
		{
			if (ctype_alnum(substr($format, $fpos, 1)))
			{
				$result .= substr($string, $spos, 1);
				$spos++;
			}
			else
			{
				$result .= substr($format, $fpos, 1);
			}

			$fpos++;
		}

		return $result;
	}

	/**
	 * Transforms a number by masking characters in a specified mask format, and
	 * ignoring characters that should be injected into the string without
	 * matching a character from the original string (defaults to space).
	 *
	 * Usage:
	 * <code>
	 * $num = new \Fuel\Common\Num();
	 * echo $num->maskString('1234567812345678', '************0000'); ************5678
	 * echo $num->maskString('1234567812345678', '**** **** **** 0000'); // **** **** **** 5678
	 * echo $num->maskString('1234567812345678', '**** - **** - **** - 0000', ' -'); // **** - **** - **** - 5678
	 * </code>
	 *
	 * @link    http://snippets.symfony-project.org/snippet/157
	 *
	 * @param  string  The string to transform
	 * @param  string  The mask format
	 * @param  string  A string (defaults to a single space) containing characters to ignore in the format
	 *
	 * @return  string  The masked string
	 *
	 * @since 1.0.0
	 */
	public function maskString($string, $format = '', $ignore = ' ')
	{
		if(empty($format) or empty($string))
		{
			return $string;
		}

		$result = '';
		$fpos = 0;
		$spos = 0;

		while ((strlen($format) - 1) >= $fpos)
		{
			if (ctype_alnum(substr($format, $fpos, 1)))
			{
				$result .= substr($string, $spos, 1);
				$spos++;
			}
			else
			{
				$result .= substr($format, $fpos, 1);

				if (strpos($ignore, substr($format, $fpos, 1)) === false)
				{
					++$spos;
				}
			}

			++$fpos;
		}

		return $result;
	}

	/**
	 * Formats a phone number.
	 *
	 * @link    http://snippets.symfony-project.org/snippet/157
	 *
	 * @param  string  The unformatted phone number to format
	 * @param  string  The format to use, defaults to '(000) 000-0000'
	 *
	 * @return  string  The formatted string
	 *
	 * @see  format
	 *
	 * @since 1.0.0
	 */
	public function formatPhone($string = '', $format = null)
	{
		if ($format === null)
		{
			$format = isset($this->config['phone']) ? $this->config['phone'] : '(000) 000-0000';
		}

		return $this->format($string, $format);
	}

	/**
	 * Formats a variable length phone number, using a standard format.
	 *
	 * Usage:
	 * <code>
	 * $num = new \Fuel\Common\Num();
	 * echo $num->smartFormatPhone('1234567'); // 123-4567
	 * echo $num->smartFormatPhone('1234567890'); // (123) 456-7890
	 * echo $num->smartFormatPhone('91234567890'); // 9 (123) 456-7890
	 * echo $num->smartFormatPhone('123456'); // => 123456
	 * </code>
	 *
	 * @param  string  The unformatted phone number to format
	 *
	 * @return  string  The formatted string
	 *
	 * @see  format
	 *
	 * @since 1.0.0
	 */
	public function smartFormatPhone($string)
	{
		$formats = isset($this->config['smartPhone']) ? $this->config['smartPhone'] : null;

		if (is_array($formats) and isset($formats[strlen($string)]))
		{
			return $this->format($string, $formats[strlen($string)]);
		}

		return $string;
	}

	/**
	 * Formats a credit card expiration string. Expects 4-digit string (MMYY).
	 *
	 * @param  string  The unformatted expiration string to format
	 * @param  string  The format to use, defaults to '00-00'
	 *
	 * @return  string  The formatted string
	 *
	 * @see  format
	 *
	 * @since 1.0.0
	 */
	public function formatExp($string, $format = null)
	{
		if ($format === null)
		{
			$format = isset($this->config['exp']) ? $this->config['exp'] : '00-00';
		}

		return $this->format($string, $format);
	}

	/**
	 * Formats (masks) a credit card.
	 *
	 * @param  string  The unformatted credit card number to format
	 * @param  string  The format to use, defaults to '**** **** **** 0000'
	 *
	 * @return  string  The masked string
	 *
	 * @see     maskString
	 *
	 * @since 1.0.0
	 */
	public function maskCreditCard($string, $format = null)
	{
		if ($format === null)
		{
			$format = isset($this->config['creditCard']) ? $this->config['creditCard'] : '**** **** **** 0000';
		}

		return $this->maskString($string, $format);
	}
}
