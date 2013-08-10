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
 * Debug class, helper class to assign in debugging the application
 *
 * @package  Fuel\Common
 * @since  2.0.0
 */
class Debug
{
	/**
	 * @var  int  Maximum nesting level for dump output
	 */
	protected static $maxNestingLevel = 5;

	/**
	 * @var  bool  Whether or not the dump nesting is opened by default
	 */
	protected static $jsOpenToggle = false;

	/**
	 * @var  bool  Flag to track if the required javascript has already been sent
	 */
	protected static $jsDisplayed = false;

	/**
	 * @var  array  Cache for fileLines(), to avoid duplicate lookups
	 */
	protected static $filesCache = array();

	/**
	 * Setter for maxNestingLevel
	 *
	 * @param  int  Maximum nesting level for dump output
	 *
	 * @return  int  The current nesting level
	 */
	public static function setNestingLevel($level = null)
	{
		if (func_num_args() and is_numeric($level) and $level > 0)
		{
			static::$maxNestingLevel = $level;
		}

		return static::$maxNestingLevel;
	}

	/**
	 * Setter for jsToggleOpen
	 *
	 * @param  bool  true for Open by default, false for closed
	 *
	 * @return  bool  The current toggle state
	 */
	public static function setOpenToggle($toggle = null)
	{
		if (func_num_args() and is_bool($toggle))
		{
			static::$jsOpenToggle = $toggle;
		}

		return static::$jsOpenToggle;
	}

	/**
	 * Quick and nice way to output a mixed variable to the browser
	 *
	 * @return	string
	 */
	public static function dump()
	{
		if ((bool) defined('STDIN'))
		{
			// no fancy flying on the commandline, just dump 'm
			foreach (func_get_args() as $arg)
			{
				var_dump($arg);
			}
			return;
		}

		$arguments = func_get_args();
		$total = count($arguments);

		$backtrace = debug_backtrace();

		// locate the first file entry that isn't this class itself
		foreach ($backtrace as $stack => $trace)
		{
			if (isset($trace['file']))
			{
				// If being called from within, or using call_user_func(), get the next entry
				if ($trace['file'] === __FILE__ or strpos($trace['function'], 'call_user_func') === 0)
				{
					continue;
				}

				$callee = $trace;
				$label = \Inflector::humanize($backtrace[$stack+1]['function']);

				// get info about what was dumped
				$callee['code'] = static::fileLines($callee['file'], $callee['line'], false, 0);
				$callee['code'] = reset($callee['code']);
				if (preg_match('/(.*dump\()(.*?)\);(.*)/', $callee['code'], $matches))
				{
					$results = array();
					$r = 0;
					foreach(explode(',', $matches[2]) as $part)
					{
						if ( ! isset($results[$r]))
						{
							$results[$r] = '';
							$s = $e = 0;
						}

						$s += substr_count($part, '(');
						$e += substr_count($part, ')');

						if ($s === $e)
						{
							$results[$r++] .= $part;
						}
						else
						{
							$results[$r] .= $part;
						}
					}

					if (count($results) == $total)
					{
						$callee['code'] = $results;
					}
					else
					{
						$callee['code'] = 'Variable'.($total==1?'':'s').' dumped: '.$matches[2];
					}
				}
				else
				{
					$callee['code'] = 'Statement: '.$callee['code'];
				}

				$callee['file'] = cleanpath($callee['file']);

				break;
			}
		}

		if ( ! static::$jsDisplayed)
		{
			echo <<<JS
<script type="text/javascript">function fuel_debug_toggle(a){if(document.getElementById){if(document.getElementById(a).style.display=="none"){document.getElementById(a).style.display="block"}else{document.getElementById(a).style.display="none"}}else{if(document.layers){if(document.id.display=="none"){document.id.display="block"}else{document.id.display="none"}}else{if(document.all.id.style.display=="none"){document.all.id.style.display="block"}else{document.all.id.style.display="none"}}}};</script>
JS;
			static::$jsDisplayed = true;
		}

		echo '<div class="fuelphp-dump" style="font-size: 13px;background: #EEE !important; border:1px solid #666; color: #000 !important; padding:10px;">';
		echo '<h1 style="padding: 0 0 5px 0; margin: 0; font: bold 110% sans-serif;">File: '.$callee['file'].' @ line: '.$callee['line'].'</h1>';
		if (is_string($callee['code']))
		{
			echo '<h5 style="border-bottom: 1px solid #CCC;padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 85% sans-serif;">'.$callee['code'].'</h5>'.PHP_EOL;
		}
		echo '<pre style="overflow:auto;font-size:100%;">';

		$i = 0;
		foreach ($arguments as $argument)
		{
			if (is_string($callee['code']))
			{
				echo '<strong>Variable #'.(++$i).' of '.$total.':</strong>'.PHP_EOL;
			}
			elseif (substr(trim($callee['code'][$i]),0,1) == '$')
			{
				echo '<strong>Variable: '.trim($callee['code'][$i++]).'</strong>'.PHP_EOL;
			}
			else
			{
				echo '<strong>Expression: '.trim($callee['code'][$i++]).'</strong>'.PHP_EOL;
			}
			echo static::format('', $argument);
			if ($i < $total)
			{
				echo PHP_EOL;
			}
		}

		echo "</pre>";
		echo "</div>";
	}

	/**
	 * Formats the given $var's output in a nice looking, Foldable interface.
	 *
	 * @param	string	$name	the name of the var
	 * @param	mixed	$var	the variable
	 * @param	int		$level	the indentation level
	 * @param	string	$indent_char	the indentation character
	 * @return	string	the formatted string.
	 */
	public static function format($name, $var, $level = 0, $indent_char = '&nbsp;&nbsp;&nbsp;&nbsp;', $scope = '')
	{
		$return = str_repeat($indent_char, $level);

		if (is_array($var))
		{
			$id = 'fuel_debug_'.mt_rand();
			$return .= "<i>{$scope}</i> <strong>{$name}</strong>";
			$return .=  " (Array, ".count($var)." element".(count($var)!=1?"s":"").")";
			if (count($var) > 0 and static::$maxNestingLevel > $level)
			{
				$return .= " <a href=\"javascript:fuel_debug_toggle('$id');\" title=\"Click to ".((static::$jsOpenToggle or $level == 0)?"close":"open")."\">&crarr;</a>".PHP_EOL;
			}
			else
			{
				$return .= PHP_EOL;
			}

			if (static::$maxNestingLevel <= $level)
			{
				$return .= str_repeat($indent_char, $level + 1)."...".PHP_EOL;
			}
			else
			{
				$sub_return = '';
				foreach ($var as $key => $val)
				{
					$sub_return .= static::format($key, $val, $level + 1);
				}
				if (count($var) > 0)
				{
					$return .= "<span id=\"$id\" style=\"display: ".((static::$jsOpenToggle or $level == 0)?"block":"none").";\">$sub_return</span>";
				}
				else
				{
					$return .= $sub_return;
				}
			}

		}
		elseif (is_string($var))
		{
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> (String): <span style=\"color:#E00000;\">\"".htmlentities($var, ENT_QUOTES, 'UTF-8', false)."\"</span> (".strlen($var)." characters)".PHP_EOL;
		}
		elseif (is_float($var))
		{
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> (Float): {$var}".PHP_EOL;
		}
		elseif (is_long($var))
		{
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> (Integer): {$var}".PHP_EOL;
		}
		elseif (is_null($var))
		{
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> : null".PHP_EOL;
		}
		elseif (is_bool($var))
		{
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> (Boolean): ".($var ? 'true' : 'false').PHP_EOL;
		}
		elseif (is_double($var))
		{
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> (Double): {$var}".PHP_EOL;
		}
		elseif (is_object($var))
		{
			// dirty hack to get the object id
			ob_start();
			var_dump($var);
			$contents = ob_get_contents();
			ob_end_clean();

			// process it based on the xdebug presence and configuration
			if (extension_loaded('xdebug') and ini_get('xdebug.overload_var_dump') === '1')
			{
				if (ini_get('html_errors'))
				{
					preg_match('~(.*?)\)\[<i>(\d+)(.*)~', $contents, $matches);
				}
				else
				{
					preg_match('~class (.*?)#(\d+)(.*)~', $contents, $matches);
				}
			}
			else
			{
				preg_match('~object\((.*?)#(\d+)(.*)~', $contents, $matches);
			}

			$id = 'fuel_debug_'.mt_rand();
			$rvar = new \ReflectionObject($var);
			$vars = $rvar->getProperties();
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> (Object #".$matches[2]."): ".get_class($var);
			if (count($vars) > 0 and static::$maxNestingLevel > $level)
			{
				$return .= " <a href=\"javascript:fuel_debug_toggle('$id');\" title=\"Click to ".(static::$jsOpenToggle?"close":"open")."\">&crarr;</a>".PHP_EOL;
			}

			$sub_return = '';
			foreach ($rvar->getProperties() as $prop)
			{
				$prop->isPublic() or $prop->setAccessible(true);
				if ($prop->isPrivate())
				{
					$scope = '<span style="color:red;">private</span>';
				}
				elseif ($prop->isProtected())
				{
					$scope = '<span style="color:blue;">protected</span>';
				}
				else
				{
					$scope = '<span style="color:green;">public</span>';
				}
				if (static::$maxNestingLevel <= $level)
				{
					$sub_return .= str_repeat($indent_char, $level + 1)."...".PHP_EOL;
				}
				else
				{
					$sub_return .= static::format($prop->name, $prop->getValue($var), $level + 1, $indent_char, $scope);
				}
			}

			if (count($vars) > 0)
			{
				$return .= "<span id=\"$id\" style=\"display: ".(static::$jsOpenToggle?"block":"none").";\">$sub_return</span>";
			}
			else
			{
				$return .= $sub_return;
			}
		}
		else
		{
			$return .= "<i>{$scope}</i> <strong>{$name}</strong>: {$var}".PHP_EOL;
		}
		return $return;
	}

	/**
	 * Returns the debug lines from the specified file
	 *
	 * @access	protected
	 * @param	string		the file path
	 * @param	int			the line number
	 * @param	bool		whether to use syntax highlighting or not
	 * @param	int			the amount of line padding
	 * @return	array
	 */
	public static function fileLines($filepath, $line_num, $highlight = true, $padding = 5)
	{
		// deal with eval'd code
		if (strpos($filepath, 'eval()\'d code') !== false)
		{
			return '';
		}

		// We cache the entire file to reduce disk IO for multiple errors
		if ( ! isset(static::$filesCache[$filepath]))
		{
			static::$filesCache[$filepath] = file($filepath, FILE_IGNORE_NEW_LINES);
			array_unshift(static::$filesCache[$filepath], '');
		}

		$start = max(0, $line_num - $padding);

		$length = ($line_num - $start) + $padding + 1;
		if (($start + $length) > count(static::$filesCache[$filepath]) - 1)
		{
			$length = null;
		}

		$debug_lines = array_slice(static::$filesCache[$filepath], $start, $length, true);

		if ($highlight)
		{
			$to_replace = array('<code>', '</code>', '<span style="color: #0000BB">&lt;?php&nbsp;', PHP_EOL);
			$replace_with = array('', '', '<span style="color: #0000BB">', '');

			foreach ($debug_lines as & $line)
			{
				$line = str_replace($to_replace, $replace_with, highlight_string('<?php ' . $line, t));
			}
		}

		return $debug_lines;
	}

	public static function backtrace()
	{
		return static::dump(debug_backtrace());
	}

	/**
	* Prints a list of all currently declared classes.
	*
	* @access public
	* @static
	*/
	public static function classes()
	{
		return static::dump(get_declared_classes());
	}

	/**
	* Prints a list of all currently declared interfaces (PHP5 only).
	*
	* @access public
	* @static
	*/
	public static function interfaces()
	{
		return static::dump(get_declared_interfaces());
	}

	/**
	* Prints a list of all currently included (or required) files.
	*
	* @access public
	* @static
	*/
	public static function includes()
	{
	return static::dump(get_included_files());
	}

	/**
	 * Prints a list of all currently declared functions.
	 *
	 * @access public
	 * @static
	 */
	public static function functions()
	{
		return static::dump(get_defined_functions());
	}

	/**
	 * Prints a list of all currently declared constants.
	 *
	 * @access public
	 * @static
	 */
	public static function constants()
	{
		return static::dump(get_defined_constants());
	}

	/**
	 * Prints a list of all currently loaded PHP extensions.
	 *
	 * @access public
	 * @static
	 */
	public static function extensions()
	{
		return static::dump(get_loaded_extensions());
	}

	/**
	 * Prints a list of all HTTP request headers.
	 *
	 * @access public
	 * @static
	 */
	public static function headers()
	{
		// get the current request headers and dump them
		return static::dump(\Input::headers());
	}

	/**
	 * Prints a list of the configuration settings read from <i>php.ini</i>
	 *
	 * @access public
	 * @static
	 */
	public static function phpini()
	{
		if ( ! is_readable(get_cfg_var('cfg_file_path')))
		{
			return false;
		}

		// render it
		return static::dump(parse_ini_file(get_cfg_var('cfg_file_path'), true));
	}

	/**
	 * Benchmark anything that is callable
	 *
	 * @access public
	 * @static
	 */
	public static function benchmark($callable, array $params = array())
	{
		// get the before-benchmark time
		if (function_exists('getrusage'))
		{
			$dat = getrusage();
			$utime_before = $dat['ru_utime.tv_sec'] + round($dat['ru_utime.tv_usec']/1000000, 4);
			$stime_before = $dat['ru_stime.tv_sec'] + round($dat['ru_stime.tv_usec']/1000000, 4);
		}
		else
		{
			list($usec, $sec) = explode(" ", microtime());
			$utime_before = ((float)$usec + (float)$sec);
			$stime_before = 0;
		}

		// call the function to be benchmarked
		$result = is_callable($callable) ? call_user_func_array($callable, $params) : null;

		// get the after-benchmark time
		if (function_exists('getrusage'))
		{
			$dat = getrusage();
			$utime_after = $dat['ru_utime.tv_sec'] + round($dat['ru_utime.tv_usec']/1000000, 4);
			$stime_after = $dat['ru_stime.tv_sec'] + round($dat['ru_stime.tv_usec']/1000000, 4);
		}
		else
		{
			list($usec, $sec) = explode(" ", microtime());
			$utime_after = ((float)$usec + (float)$sec);
			$stime_after = 0;
		}

		return array(
			'user' => sprintf('%1.6f', $utime_after - $utime_before),
			'system' => sprintf('%1.6f', $stime_after - $stime_before),
			'result' => $result
		);
	}

}
