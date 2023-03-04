<?php declare(strict_types=1);

/**
 * The Fuel PHP Framework is a fast, simple and flexible development framework
 *
 * @package    fuel
 * @version    2.0.0
 * @author     FlexCoders Ltd, Fuel The PHP Framework Team
 * @license    MIT License
 * @copyright  2023 FlexCoders Ltd, The Fuel PHP Framework Team
 * @link       https://fuelphp.org
 */

namespace Fuel\Helpers;

use Exception;
use UnitEnum;
use ReflectionObject;
use Fuel\Framework\Fuel;

use function array_slice;
use function array_unshift;
use function call_fuel_func_array;
use function count;
use function explode;
use function extension_loaded;
use function file;
use function function_exists;
use function get_cfg_var;
use function get_class;
use function get_declared_classes;
use function get_declared_interfaces;
use function get_declared_traits;
use function get_defined_constants;
use function get_defined_functions;
use function get_included_files;
use function get_loaded_extensions;
use function getrusage;
use function highlight_string;
use function htmlentities;
use function ini_get;
use function is_array;
use function is_bool;
use function is_double;
use function is_float;
use function is_long;
use function is_null;
use function is_object;
use function is_readable;
use function is_string;
use function microtime;
use function mt_rand;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function parse_ini_file;
use function preg_match;
use function round;
use function sprintf;
use function str_repeat;
use function strlen;
use function strpos;
use function strval;
use function var_dump;

use const PHP_SAPI;

/**
 * The Debug class is a simple utility for debugging variables, objects, arrays, etc by outputting information to the display.
 *
 * @since 1.0.0
 */
class Debug
{
	/**
	 * Limit the amount of nesting in the debug output
	 *
	 * @since 1.0.0
	 */
	public static int $maxNestingLevel = 5;

	/**
	 * Defines whether nested variable are collapsed or expanded
	 *
	 * @since 2.0.0
	 */
	public static bool $jsToggleOpen = false;

	/**
	 * Composer Autoloader Instance
	 *
	 * @since 2.0.0
	 */
	protected static bool $jsDisplayed = false;

	/**
	 * Cache to avoid recurrent source file loads
	 *
	 * @since 2.0.0
	 */
	protected static array $files = [];

	/**
	 * -----------------------------------------------------------------------------
	 * Quick and nice way to output a mixed variable to the browser
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function dump(mixed ...$args): void
	{
		if (PHP_SAPI === 'cli')
		{
			// no fancy flying, jump dump 'm
			var_dump($args);
		}
		else
		{
			$trace = (new Exception)->getTrace();

			// deal with function helpers
			if (isset($trace[0]['file']) and strpos($trace[0]['file'], 'fuel/framework/functions/functions.php') !== false)
			{
				$callee = $trace[1];
			}
			else
			{
				$callee = $trace[0];
			}

			$label = 'Debug';

			if (static::$jsDisplayed === false)
			{
				echo <<<JS
	<script type="text/javascript">function fuel_debug_toggle(a){if(document.getElementById){if(document.getElementById(a).style.display=="none"){document.getElementById(a).style.display="block"}else{document.getElementById(a).style.display="none"}}else{if(document.layers){if(document.id.display=="none"){document.id.display="block"}else{document.id.display="none"}}else{if(document.all.id.style.display=="none"){document.all.id.style.display="block"}else{document.all.id.style.display="none"}}}};</script>
JS;
				static::$jsDisplayed = true;
			}
			echo '<div class="fuelphp-dump" style="font-size: 13px;background: #EEE !important; border:1px solid #666; color: #000 !important; padding:10px;">';
			echo '<h1 style="border-bottom: 1px solid #CCC; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">'.$callee['file'].' @ line: '.$callee['line'].'</h1>';
			echo '<pre style="overflow:auto;font-size:100%;">';

			$i = 0;
			$total = count($args);
			foreach ($args as $arg)
			{
				echo '<strong>Variable #'.(++$i).' of '.$total.'</strong>:<br />';
				echo static::format('', $arg);
				echo '<br />';
			}

			echo "</pre>";
			echo "</div>";
		}
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Quick and nice way to output a mixed variable to the browser
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function inspect(mixed ...$args): void
	{
		$trace = (new Exception)->getTrace();

		$callee = $trace[0];
		$label = 'Debug';

		if ( ! static::$jsDisplayed)
		{
			echo <<<JS
<script type="text/javascript">function fuel_debug_toggle(a){if(document.getElementById){if(document.getElementById(a).style.display=="none"){document.getElementById(a).style.display="block"}else{document.getElementById(a).style.display="none"}}else{if(document.layers){if(document.id.display=="none"){document.id.display="block"}else{document.id.display="none"}}else{if(document.all.id.style.display=="none"){document.all.id.style.display="block"}else{document.all.id.style.display="none"}}}};</script>
JS;
			static::$jsDisplayed = true;
		}
		echo '<div class="fuelphp-inspect" style="font-size: 13px;background: #EEE !important; border:1px solid #666; color: #000 !important; padding:10px;">';
		echo '<h1 style="border-bottom: 1px solid #CCC; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">'.$callee['file'].' @ line: '.$callee['line'].'</h1>';
		echo '<pre style="overflow:auto;font-size:100%;">';

			$i = 0;
			$total = count($args);
			foreach ($args as $arg)
			{
				echo '<strong>'.$label.' #'.(++$i).' of '.$total.'</strong>:<br />';
				echo static::format('...', $arg);
				echo '<br />';
			}

		echo "</pre>";
		echo "</div>";
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Formats the given $var's output in a nice looking, Foldable interface.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	protected static function format(string $name, mixed $var, int $level = 0, string $indent_char = '&nbsp;&nbsp;&nbsp;&nbsp;', string $scope = ''): string
	{
		$return = str_repeat($indent_char, $level);
		if (is_array($var))
		{
			$id = 'fuel_debug_'.mt_rand();
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong>";
			$return .=  " (Array, ".count($var)." element".(count($var)!=1 ? "s" : "").")";
			if (count($var) > 0 and static::$maxNestingLevel > $level)
			{
				$return .= " <a href=\"javascript:fuel_debug_toggle('$id');\" title=\"Click to ".(static::$jsToggleOpen ? "close" : "open")."\">&crarr;</a>\n";
			}
			else
			{
				$return .= "\n";
			}

			if (static::$maxNestingLevel <= $level)
			{
				$return .= str_repeat($indent_char, $level + 1)."...\n";
			}
			else
			{
				$sub_return = '';
				foreach ($var as $key => $val)
				{
					$sub_return .= static::format(strval($key), $val, $level + 1);
				}
				if (count($var) > 0)
				{
					$return .= "<span id=\"$id\" style=\"display: ".(static::$jsToggleOpen ? "block" : "none").";\">$sub_return</span>";
				}
				else
				{
					$return .= $sub_return;
				}
			}

		}
		elseif (is_string($var))
		{
//@TODO
//            $return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (String): <span style=\"color:#E00000;\">\"".\Security::htmlentities($var)."\"</span> (".strlen($var)." characters)\n";
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (String): <span style=\"color:#E00000;\">\"".htmlentities($var)."\"</span> (".strlen($var)." characters)\n";
		}
		elseif (is_float($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (Float): {$var}\n";
		}
		elseif (is_long($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (Integer): {$var}\n";
		}
		elseif (is_null($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong>  null\n";
		}
		elseif (is_bool($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (Boolean): ".($var ? 'true' : 'false')."\n";
		}
		elseif (is_double($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (Double): {$var}\n";
		}
		elseif ($var instanceOf UnitEnum)
		{
			// dirty hack to get the enum
			ob_start();
			var_dump($var);
			$contents = ob_get_contents();
			ob_end_clean();

			preg_match('~enum\((.*?)\)~', $contents, $matches);

			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (Enum): {$matches[1]}\n";
		}
		elseif (is_object($var))
		{
			// dirty hack to get the object id
			ob_start();
			var_dump($var);
			$contents = ob_get_contents();
			ob_end_clean();

			// process it based on the xdebug presence and configuration
			if (extension_loaded('xdebug') and ini_get('xdebug.overload_var_dump'))
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
			$rvar = new ReflectionObject($var);
			$vars = $rvar->getProperties();
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> (Object #".$matches[2]."): ".get_class($var);
			if (count($vars) > 0 and static::$maxNestingLevel > $level)
			{
				$return .= " <a href=\"javascript:fuel_debug_toggle('$id');\" title=\"Click to ".(static::$jsToggleOpen ? "close" : "open")."\">&crarr;</a>\n";
			}

			$sub_return = '';
			foreach ($rvar->getProperties() as $prop)
			{
				$prop->isPublic() or $prop->setAccessible(true);
				if ($prop->isPrivate())
				{
					$scope = 'private';
				}
				elseif ($prop->isProtected())
				{
					$scope = 'protected';
				}
				else
				{
					$scope = 'public';
				}
				if (static::$maxNestingLevel <= $level)
				{
					$sub_return .= str_repeat($indent_char, $level + 1)."...\n";
				}
				else
				{
					$sub_return .= static::format($prop->name, $prop->getValue($var), $level + 1, $indent_char, $scope);
				}
			}

			if (count($vars) > 0)
			{
				$return .= "<span id=\"$id\" style=\"display: ".(static::$jsToggleOpen ? "block" : "none").";\">$sub_return</span>";
			}
			else
			{
				$return .= $sub_return;
			}
		}
		else
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong>: {$var}\n";
		}

		return $return;
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Returns the debug lines from the specified file
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function file_lines(string $filepath, int $line_num, bool $highlight = true, int $padding = 5): array
	{
		// deal with eval'd code and runtime-created function
		if (strpos($filepath, 'eval()\'d code') !== false or strpos($filepath, 'runtime-created function') !== false)
		{
			return '';
		}

		// We cache the entire file to reduce disk IO for multiple errors
		if ( ! isset(static::$files[$filepath]))
		{
			static::$files[$filepath] = file($filepath, FILE_IGNORE_NEW_LINES);
			array_unshift(static::$files[$filepath], '');
		}

		$start = $line_num - $padding;
		if ($start < 0)
		{
			$start = 0;
		}

		$length = ($line_num - $start) + $padding + 1;
		if (($start + $length) > count(static::$files[$filepath]) - 1)
		{
			$length = NULL;
		}

		$debug_lines = array_slice(static::$files[$filepath], $start, $length, TRUE);

		if ($highlight)
		{
			$to_replace = array('<code>', '</code>', '<span style="color: #0000BB">&lt;?php&nbsp;', "\n");
			$replace_with = array('', '', '<span style="color: #0000BB">', '');

			foreach ($debug_lines as & $line)
			{
				$line = str_replace($to_replace, $replace_with, highlight_string('<?php ' . $line, TRUE));
			}
		}

		return $debug_lines;
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Output the call stack from here, or the supplied one.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function backtrace($trace = null): string|null
	{
		$trace or $trace = debug_backtrace();

		if (app()->get(Fuel::class)->isCli())
		{
			// Special case for CLI since the var_dump of a backtrace is of little use.
			$str = '';
			foreach ($trace as $i => $frame)
			{
				$line = "#$i\t";

				if ( ! isset($frame['file']))
				{
					$line .= "[internal function]";
				}
				else
				{
					$line .= $frame['file'] . ":" . $frame['line'];
				}

				$line .= "\t";

				if (isset($frame['function']))
				{
					if (isset($frame['class']))
					{
						$line .= $frame['class'] . '::';
					}

					$line .= $frame['function'] . "()";
				}

				$str .= $line . "\n";

			}

			return $str;
		}

		return static::dump($trace);
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Prints a list of all currently declared classes.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function classes(): void
	{
		static::dump(get_declared_classes());
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Prints a list of all currently declared interfaces
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function interfaces(): void
	{
		static::dump(get_declared_interfaces());
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Prints a list of all currently declared traits
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function traits(): void
	{
		static::dump(get_declared_traits());
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Prints a list of all currently included (or required) files.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function includes(): void
	{
		static::dump(get_included_files());
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Prints a list of all currently declared functions.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function functions(): void
	{
		static::dump(get_defined_functions());
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Prints a list of all currently declared constants.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function constants(): void
	{
		static::dump(get_defined_constants());
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Prints a list of all currently loaded PHP extensions.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function extensions(): void
	{
		static::dump(get_loaded_extensions());
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Prints a list of all HTTP request headers.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function headers(): void
	{
		// get the current request headers and dump them
		static::dump(app()->get(Fuel::class)->getRequest()->getHeaders());
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Prints a list of the configuration settings read from <i>php.ini</i>
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function phpini()
	{
		if (is_readable(get_cfg_var('cfg_file_path')))
		{
			// render it
			return static::dump(parse_ini_file(get_cfg_var('cfg_file_path'), true));
		}

	}

	/**
	 * -----------------------------------------------------------------------------
	 * Benchmark anything that is callable
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function benchmark(callable $callable, array $params = []): array
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
			$utime_before = ((float) $usec + (float) $sec);
			$stime_before = 0;
		}

		// call the function to be benchmarked
		$result = call_fuel_func_array($callable, $params);

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
			$utime_after = ((float) $usec + (float) $sec);
			$stime_after = 0;
		}

		return array(
			'user' => sprintf('%1.6f', $utime_after - $utime_before),
			'system' => sprintf('%1.6f', $stime_after - $stime_before),
			'result' => $result,
		);
	}
}
