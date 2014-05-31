<?php
/**
 * @package    Fuel\Common
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Common;

/**
 * String handling with encoding support
 *
 * PHP needs to be compiled with --enable-mbstring
 * or a fallback without encoding support is used
 */
class Str
{
	protected $encoding = 'UTF-8';

  /**
	 * Truncates a string to the given length.  It will optionally preserve
	 * HTML tags if $is_html is set to true.
	 *
	 * @param   string  $string        the string to truncate
	 * @param   int     $limit         the number of characters to truncate too
	 * @param   string  $continuation  the string to use to denote it was truncated
	 * @param   bool    $is_html       whether the string has HTML
	 * @return  string  the truncated string
	 */
	public function truncate($string, $limit, $continuation = '...', $is_html = false)
	{
		$offset = 0;
		$tags = array();
		if ($is_html)
		{
			// Handle special characters.
			preg_match_all('/&[a-z]+;/i', strip_tags($string), $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
			foreach ($matches as $match)
			{
				if ($match[0][1] >= $limit)
				{
					break;
				}
				$limit += ($this->length($match[0][0]) - 1);
			}

			// Handle all the html tags.
			preg_match_all('/<[^>]+>([^<]*)/', $string, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
			foreach ($matches as $match)
			{
				if($match[0][1] - $offset >= $limit)
				{
					break;
				}
				$tag = $this->sub(strtok($match[0][0], " \t\n\r\0\x0B>"), 1);
				if($tag[0] != '/')
				{
					$tags[] = $tag;
				}
				elseif (end($tags) == $this->sub($tag, 1))
				{
					array_pop($tags);
				}
				$offset += $match[1][1] - $match[0][1];
			}
		}
		$new_string = $this->sub($string, 0, $limit = min($this->length($string),  $limit + $offset));
		$new_string .= ($this->length($string) > $limit ? $continuation : '');
		$new_string .= (count($tags = array_reverse($tags)) ? '</'.implode('></',$tags).'>' : '');
		return $new_string;
	}

	/**
	 * Add's _1 to a string or increment the ending number to allow _2, _3, etc
	 *
	 * @param   string  $str  required
	 * @return  string
	 */
	public function increment($str, $first = 1, $separator = '_')
	{
		preg_match('/(.+)'.$separator.'([0-9]+)$/', $str, $match);

		return isset($match[2]) ? $match[1].$separator.($match[2] + 1) : $str.$separator.$first;
	}

	/**
	 * Checks wether a string has a precific beginning.
	 *
	 * @param   string   $str          string to check
	 * @param   string   $start        beginning to check for
	 * @param   boolean  $ignore_case  wether to ignore the case
	 * @return  boolean  wether a string starts with a specified beginning
	 */
	public function startsWith($str, $start, $ignore_case = false)
	{
		return (bool) preg_match('/^'.preg_quote($start, '/').'/m'.($ignore_case ? 'i' : ''), $str);
	}

	/**
	 * Checks wether a string has a precific ending.
	 *
	 * @param   string   $str          string to check
	 * @param   string   $end          ending to check for
	 * @param   boolean  $ignore_case  wether to ignore the case
	 * @return  boolean  wether a string ends with a specified ending
	 */
	public function endsWith($str, $end, $ignore_case = false)
	{
		return (bool) preg_match('/'.preg_quote($end, '/').'$/m'.($ignore_case ? 'i' : ''), $str);
	}

	/**
	 * substr
	 *
	 * @param   string    $str       required
	 * @param   int       $start     required
	 * @param   int|null  $length
	 * @param   string    $encoding  default UTF-8
	 * @return  string
	 */
	public function sub($str, $start, $length = null, $encoding = null)
	{
		$encoding or $encoding = $this->encoding;

		// substr functions don't parse null correctly
		$length = is_null($length) ? (function_exists('mb_substr') ? mb_strlen($str, $encoding) : strlen($str)) - $start : $length;

		return function_exists('mb_substr')
			? mb_substr($str, $start, $length, $encoding)
			: substr($str, $start, $length);
	}

	/**
	 * strlen
	 *
	 * @param   string  $str       required
	 * @param   string  $encoding  default UTF-8
	 * @return  int
	 */
	public function length($str, $encoding = null)
	{
		$encoding or $encoding = $this->encoding;

		return function_exists('mb_strlen')
			? mb_strlen($str, $encoding)
			: strlen($str);
	}

	/**
	 * lower
	 *
	 * @param   string  $str       required
	 * @param   string  $encoding  default UTF-8
	 * @return  string
	 */
	public function lower($str, $encoding = null)
	{
		$encoding or $encoding = $this->encoding;

		return function_exists('mb_strtolower')
			? mb_strtolower($str, $encoding)
			: strtolower($str);
	}

	/**
	 * upper
	 *
	 * @param   string  $str       required
	 * @param   string  $encoding  default UTF-8
	 * @return  string
	 */
	public function upper($str, $encoding = null)
	{
		$encoding or $encoding = $this->encoding;

		return function_exists('mb_strtoupper')
			? mb_strtoupper($str, $encoding)
			: strtoupper($str);
	}

	/**
	 * lcfirst
	 *
	 * Does not strtoupper first
	 *
	 * @param   string  $str       required
	 * @param   string  $encoding  default UTF-8
	 * @return  string
	 */
	public function lcfirst($str, $encoding = null)
	{
		$encoding or $encoding = $this->encoding;

		return function_exists('mb_strtolower')
			? mb_strtolower(mb_substr($str, 0, 1, $encoding), $encoding).
				mb_substr($str, 1, mb_strlen($str, $encoding), $encoding)
			: lcfirst($str);
	}

	/**
	 * ucfirst
	 *
	 * Does not strtolower first
	 *
	 * @param   string $str       required
	 * @param   string $encoding  default UTF-8
	 * @return   string
	 */
	public function ucfirst($str, $encoding = null)
	{
		$encoding or $encoding = $this->encoding;

		return function_exists('mb_strtoupper')
			? mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
				mb_substr($str, 1, mb_strlen($str, $encoding), $encoding)
			: ucfirst($str);
	}

	/**
	 * ucwords
	 *
	 * First strtolower then ucwords
	 *
	 * ucwords normally doesn't strtolower first
	 * but MB_CASE_TITLE does, so ucwords now too
	 *
	 * @param   string   $str       required
	 * @param   string   $encoding  default UTF-8
	 * @return  string
	 */
	public function ucwords($str, $encoding = null)
	{
		$encoding or $encoding = $this->encoding;

		return function_exists('mb_convert_case')
			? mb_convert_case($str, MB_CASE_TITLE, $encoding)
			: ucwords(strtolower($str));
	}

	/**
	  * Creates a random string of characters
	  *
	  * @param   string  the type of string
	  * @param   int     the number of characters
	  * @return  string  the random string
	  */
	public function random($type = 'alnum', $length = 16)
	{
		switch($type)
		{
			case 'basic':
				$result = mt_rand();
				break;

			case 'unique':
				$result = md5(uniqid(mt_rand()));
				break;

			case 'sha1' :
				$result = sha1(uniqid(mt_rand(), true));
				break;

			default:
			case 'alnum':
			case 'numeric':
			case 'nozero':
			case 'alpha':
			case 'distinct':
			case 'hexdec':
				switch ($type)
				{
					case 'alpha':
						$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;

					case 'numeric':
						$pool = '0123456789';
						break;

					case 'nozero':
						$pool = '123456789';
						break;

					case 'distinct':
						$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
						break;

					case 'hexdec':
						$pool = '0123456789abcdef';
						break;

					default:
					case 'alnum':
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				}

				$result = '';
				for ($i=0; $i < $length; $i++)
				{
					$result .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
				}
		}

		return $result;
	}

	/**
	 * Returns a closure that will alternate between the args which to return.
	 * If you call the closure with false as the arg it will return the value without
	 * alternating the next time.
	 *
	 * @return  Closure
	 */
	public function alternator()
	{
		// the args are the values to alternate
		$args = func_get_args();

		return function ($next = true) use ($args)
		{
			static $i = 0;
			return $args[($next ? $i++ : $i) % count($args)];
		};
	}

	/**
	 * Parse the params from a string using strtr()
	 *
	 * @param   string  string to parse
	 * @param   array   params to str_replace
	 * @return  string
	 */
	public function tr($string, $array = array())
	{
		if (is_string($string))
		{
			$tr_arr = array();

			foreach ($array as $from => $to)
			{
				substr($from, 0, 1) !== ':' and $from = ':'.$from;
				$tr_arr[$from] = $to;
			}
			unset($array);

			return strtr($string, $tr_arr);
		}
		else
		{
			return $string;
		}
	}

	/**
	 * Check if a string is json encoded
	 *
	 * @param  string $string string to check
	 * @return bool
	 */
	public function is_json($string)
	{
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Check if a string is a valid XML
	 *
	 * @param  string $string string to check
	 * @return bool
	 */
	public function is_xml($string)
	{
		$internal_errors = libxml_use_internal_errors();
		libxml_use_internal_errors(true);
		$result = simplexml_load_string($string) !== false;
		libxml_use_internal_errors($internal_errors);

		return $result;
	}

	/**
	 * Check if a string is serialized
	 *
	 * @param  string $string string to check
	 * @return bool
	 */
	public function is_serialized($string)
	{
		$array = @unserialize($string);
		return ! ($array === false and $string !== 'b:0;');
	}

	/**
	 * Check if a string is html
	 *
	 * @param  string $string string to check
	 * @return bool
	 */
	public function is_html($string)
	{
		return strlen(strip_tags($string)) < strlen($string);
	}
}
