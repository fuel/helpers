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
	protected $maxNestingLevel = 5;

	/**
	 * @var  bool  Whether or not the dump nesting is opened by default
	 */
	protected $jsOpenToggle = false;

	/**
	 * @var  bool  Flag to track if the required javascript has already been sent
	 */
	protected $jsDisplayed = false;

	/**
	 * @var  array  Cache for fileLines(), to avoid duplicate lookups
	 */
	protected $filesCache = array();

	/**
	 * @var  DataContainer  Input datacontainer
	 */
	protected $input;

	/**
	 * @var  Inflector  Inflector instance
	 */
	protected $inflector;

	/**
	 */
	public function __construct($input, $inflector)
	{
		$this->input = $input;
		$this->inflector = $inflector;
	}

	/**
	 * Setter for maxNestingLevel
	 *
	 * @param  int  Maximum nesting level for dump output
	 *
	 * @return  int  The current nesting level
	 */
	public function setNestingLevel($level = null)
	{
		if (func_num_args() and is_numeric($level) and $level > 0)
		{
			$this->maxNestingLevel = $level;
		}

		return $this->maxNestingLevel;
	}

	/**
	 * Setter for jsToggleOpen
	 *
	 * @param  bool  true for Open by default, false for closed
	 *
	 * @return  bool  The current toggle state
	 */
	public function setOpenToggle($toggle = null)
	{
		if (func_num_args() and is_bool($toggle))
		{
			$this->jsOpenToggle = $toggle;
		}

		return $this->jsOpenToggle;
	}

	/**
	 * Quick and nice way to output mixed variable(s)
	 */
	public function dump()
	{
		if ((bool) defined('STDIN'))
		{
			// no fancy flying on the commandline, just dump 'm
			foreach (func_get_args() as $arg)
			{
				var_dump($arg);
			}
		}
		else
		{
			// @codeCoverageIgnoreStart
			call_user_func_array(array($this, 'dumpAsHtml'), func_get_args());
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Quick and nice way to output mixed variable(s) to the browser
	 */
	public function dumpAsHtml()
	{
		$arguments = func_get_args();
		$total = count($arguments);

		$backtrace = debug_backtrace();

		// locate the first file entry that isn't this class itself
		foreach ($backtrace as $stack => $trace)
		{
			if (isset($trace['file']))
			{
				// If being called from within, or using call_user_func(), get the next entry
				if (strpos($trace['function'], 'call_user_func') === 0 or (isset($trace['class']) and $trace['class'] == get_class($this)))
				{
					continue;
				}

				$callee = $trace;
				$label = $this->inflector->humanize($backtrace[$stack+1]['function']);

				// get info about what was dumped
				$callee['code'] = '';
				for ($i = $callee['line']; $i > 0; $i--)
				{
					$line = $this->fileLines($callee['file'], $i, false, 0);
					$callee['code'] = reset($line).' '.trim($callee['code']);
					$tokens = token_get_all('<?php '.trim($callee['code']));
					if (is_array($tokens[1]) and isset($tokens[1][0]) and $tokens[1][0] != 377)
					{
						break;
					}
				}

				$results = array();
				$r = false;
				$c = 0;

				foreach($tokens as $token)
				{
					// skip everything before our function call
					if ($r === false)
					{
						if (isset($token[1]) and $token[1] == $callee['function'])
						{
							$r = 0;
						}
						continue;
					}

					// and quit if we find an end-of-statement
					if ($token == ';')
					{
						break;
					}

					// check for a start-of-expresssion
					elseif ($token == '(')
					{
						$c++;
						if ($c === 1)
						{
							continue;
						}
					}

					// check for an end-of-expresssion
					elseif ($token == ')')
					{
						$c--;
						if ($c === 0)
						{
							$r++;
							continue;
						}
					}

					// new expression in the same dump
					elseif ($token == ',' and $c === 1)
					{
						$r++;
						continue;
					}

					// make sure we have an array entry to add to, and add the token
					if ( ! isset($results[$r]))
					{
						$results[$r] = '';
					}
					$results[$r] .= is_array($token) ? $token[1] : $token;
				}

				// make sure we've parsed the same number of expressions as we have arguments
				if (count($results) == $total)
				{
					$callee['code'] = $results;
				}
				else
				{
					// parsing failed, try it the old fashioned way
					if (preg_match('/(.*'.$callee['function'].'\()(.*?)\);(.*)/', $callee['code'], $matches))
					{
						$callee['code'] = 'Variable'.($total==1?'':'s').' dumped: '.$matches[2];
					}
				}

				$callee['file'] = cleanpath($callee['file']);

				break;
			}
		}

		if ( ! $this->jsDisplayed)
		{
			echo <<<JS
<script type="text/javascript">function fuel_debug_toggle(a){if(document.getElementById){if(document.getElementById(a).style.display=="none"){document.getElementById(a).style.display="block"}else{document.getElementById(a).style.display="none"}}else{if(document.layers){if(document.id.display=="none"){document.id.display="block"}else{document.id.display="none"}}else{if(document.all.id.style.display=="none"){document.all.id.style.display="block"}else{document.all.id.style.display="none"}}}};</script>
JS;
			$this->jsDisplayed = true;
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
			echo $this->format('', $argument);
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
	public function format($name, $var, $level = 0, $indent_char = '&nbsp;&nbsp;&nbsp;&nbsp;', $scope = '')
	{
		static $itemCounter = 1;

		$return = str_repeat($indent_char, $level);

		if (is_array($var))
		{
			$id = 'fuel_debug_'.$itemCounter++;
			$return .= "<i>{$scope}</i> <strong>{$name}</strong>";
			$return .=  " (Array, ".count($var)." element".(count($var)!=1?"s":"").")";
			if (count($var) > 0 and $this->maxNestingLevel > $level)
			{
				$return .= " <a href=\"javascript:fuel_debug_toggle('$id');\" title=\"Click to ".(($this->jsOpenToggle or $level == 0)?"close":"open")."\">&crarr;</a>".PHP_EOL;
			}
			else
			{
				$return .= PHP_EOL;
			}

			if ($this->maxNestingLevel <= $level)
			{
				$return .= str_repeat($indent_char, $level + 1)."...".PHP_EOL;
			}
			else
			{
				$sub_return = '';
				foreach ($var as $key => $val)
				{
					$sub_return .= $this->format($key, $val, $level + 1);
				}
				if (count($var) > 0)
				{
					$return .= "<span id=\"$id\" style=\"display: ".(($this->jsOpenToggle or $level == 0)?"block":"none").";\">$sub_return</span>";
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
		elseif (is_int($var))
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
		elseif (is_object($var))
		{
			// dirty hack to get the object id
			ob_start();
			var_dump($var);
			$contents = ob_get_contents();
			ob_end_clean();

			// process it based on the xdebug presence and configuration
			// @codeCoverageIgnoreStart
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
			// @codeCoverageIgnoreEnd

			$id = 'fuel_debug_'.$itemCounter++;
			$rvar = new \ReflectionObject($var);
			$vars = $rvar->getProperties();
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> (Object #".$matches[2]."): ".get_class($var);
			if (count($vars) > 0 and $this->maxNestingLevel > $level)
			{
				$return .= " <a href=\"javascript:fuel_debug_toggle('$id');\" title=\"Click to ".($this->jsOpenToggle?"close":"open")."\">&crarr;</a>".PHP_EOL;
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
				if ($this->maxNestingLevel <= $level)
				{
					$sub_return .= str_repeat($indent_char, $level + 1)."...".PHP_EOL;
				}
				else
				{
					$sub_return .= $this->format($prop->name, $prop->getValue($var), $level + 1, $indent_char, $scope);
				}
			}

			if (count($vars) > 0)
			{
				$return .= "<span id=\"$id\" style=\"display: ".($this->jsOpenToggle?"block":"none").";\">$sub_return</span>";
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
	public function fileLines($filepath, $line_num, $highlight = true, $padding = 5)
	{
		// deal with eval'd code
		if (strpos($filepath, 'eval()\'d code') !== false)
		{
			return '';
		}

		// We cache the entire file to reduce disk IO for multiple errors
		if ( ! isset($this->filesCache[$filepath]))
		{
			$this->filesCache[$filepath] = file($filepath, FILE_IGNORE_NEW_LINES);
			array_unshift($this->filesCache[$filepath], '');
		}

		$start = max(0, $line_num - $padding);
		$length = ($line_num - $start) + $padding + 1;

		if (($start + $length) > count($this->filesCache[$filepath]) - 1)
		{
			$length = null;
		}

		$debug_lines = array_slice($this->filesCache[$filepath], $start, $length, true);

		if ($highlight)
		{
			$to_replace = array('<code>', '</code>', '<span style="color: #0000BB">&lt;?php&nbsp;', PHP_EOL);
			$replace_with = array('', '', '<span style="color: #0000BB">', '');

			foreach ($debug_lines as &$line)
			{
				$line = str_replace($to_replace, $replace_with, highlight_string('<?php ' . $line, true));
			}
		}

		return $debug_lines;
	}

	public function backtrace()
	{
		return $this->dump(debug_backtrace());
	}

	/**
	* Prints a list of all currently declared classes.
	*
	* @access public
	*/
	public function classes()
	{
		return $this->dump(get_declared_classes());
	}

	/**
	* Prints a list of all currently declared interfaces (PHP5 only).
	*
	* @access public
	*/
	public function interfaces()
	{
		return $this->dump(get_declared_interfaces());
	}

	/**
	* Prints a list of all currently included (or required) files.
	*
	* @access public
	*/
	public function includes()
	{
	return $this->dump(get_included_files());
	}

	/**
	 * Prints a list of all currently declared functions.
	 *
	 * @access public
	 */
	public function functions()
	{
		return $this->dump(get_defined_functions());
	}

	/**
	 * Prints a list of all currently declared constants.
	 *
	 * @access public
	 */
	public function constants()
	{
		return $this->dump(get_defined_constants());
	}

	/**
	 * Prints a list of all currently loaded PHP extensions.
	 *
	 * @access public
	 */
	public function extensions()
	{
		return $this->dump(get_loaded_extensions());
	}

	/**
	 * Prints a list of all HTTP request headers.
	 *
	 * @access public
	 */
	public function headers()
	{
		// get the current request headers and dump them
		return $this->dump($this->input->headers());
	}

	/**
	 * Prints a list of the configuration settings read from <i>php.ini</i>
	 *
	 * @access public
	 */
	public function phpini()
	{
		return is_readable(get_cfg_var('cfg_file_path')) ? $this->dump(parse_ini_file(get_cfg_var('cfg_file_path'), true)) : false;
	}

	/**
	 * Benchmark anything that is callable
	 *
	 * @access public
	 */
	public function benchmark($callable, array $params = array())
	{
		// get the before-benchmark time
		list($usec, $sec) = explode(" ", microtime());
		$time_before = ((float)$usec + (float)$sec);

		// call the function to be benchmarked
		$result = is_callable($callable) ? call_user_func_array($callable, $params) : null;

		// get the after-benchmark time
		list($usec, $sec) = explode(" ", microtime());
		$time_after = ((float)$usec + (float)$sec);

		return array(
			'time' => sprintf('%1.6f', $time_after - $time_before),
			'result' => $result
		);
	}

}
