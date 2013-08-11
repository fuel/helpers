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
 * Format class
 *
 * Help convert between various formats such as XML, JSON, CSV, etc.
 *
 * @package  Fuel\Common
 * @since  1.0.0
 */
class Format
{
	/**
	 * @var  array|mixed  Input data to convert
	 */
	protected $data = array();

	/**
	 * @var  array|mixed  Configuration for this Format instance
	 */
	protected $config = array();

	/**
	 * @var  Fuel\Foundation\Input  current Input object
	 */
	protected $input;

	/**
	 * @param  mixed $data  Input data to format or convert
	 * @param  mixed $from_type  The data type of the input data
	 */
	public function __construct($data = null, $from_type = null, Array $config = array(), $input = null)
	{
		// If the provided data is already formatted we should probably convert it to an array
		if ($from_type !== null)
		{
			if (method_exists($this, '_from' . ucfirst($from_type)))
			{
				$data = call_user_func(array($this, '_from' . $ucfirst($from_type)), $data);
			}

			else
			{
				throw new \FuelException('Format class does not support conversion from "' . $from_type . '".');
			}
		}

		$this->data = $data;

		$this->config = $config;

		$this->input = $input;
	}

	// FORMATING OUTPUT ---------------------------------------------------------

	/**
	 * To array conversion
	 *
	 * Goes through the input and makes sure everything is either a scalar value or array
	 *
	 * @param   mixed  $data
	 * @return  array
	 */
	public function toArray($data = null)
	{
		if ($data === null)
		{
			$data = $this->data;
		}

		$array = array();

		if (is_object($data) and ! $data instanceof \Iterator)
		{
			$data = get_object_vars($data);
		}

		if (empty($data))
		{
			return array();
		}

		foreach ($data as $key => $value)
		{
			if (is_object($value) or is_array($value))
			{
				$array[$key] = $this->toArray($value);
			}
			else
			{
				$array[$key] = $value;
			}
		}

		return $array;
	}

	/**
	 * To XML conversion
	 *
	 * @param   mixed        $data
	 * @param   null         $structure
	 * @param   null|string  $basenode
	 * @return  string
	 */
	public function toXml($data = null, $structure = null, $basenode = 'xml')
	{
		if ($data == null)
		{
			$data = $this->data;
		}

		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set('zend.ze1_compatibility_mode', 0);
		}

		if ($structure == null)
		{
			$structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
		}

		// Force it to be something useful
		if ( ! is_array($data) and ! is_object($data))
		{
			$data = (array) $data;
		}

		foreach ($data as $key => $value)
		{
			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z_\-0-9]/i', '', $key);

			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = (\Inflector::singularize($basenode) != $basenode) ? \Inflector::singularize($basenode) : 'item';
			}

			// if there is another array found recrusively call this function
			if (is_array($value) or is_object($value))
			{
				$node = $structure->addChild($key);

				// recursive call if value is not empty
				if( ! empty($value))
				{
					$this->toXml($value, $node, $key);
				}
			}

			else
			{
				// add single node.
				$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, "UTF-8");

				$structure->addChild($key, $value);
			}
		}

		// pass back as string. or simple xml object if you want!
		return $structure->asXML();
	}

	/**
	 * To CSV conversion
	 *
	 * @param   mixed   $data
	 * @param   mixed   $delimiter
	 * @return  string
	 */
	public function toCsv($data = null, $delimiter = null)
	{
		// csv format settings
		$newline = \Arr::get($this->config, 'csv.newline', "\n");
		$delimiter or $delimiter = \Arr::get($this->config, 'csv.delimiter', ',');
		$enclosure = \Arr::get($this->config, 'csv.enclosure', '"');
		$escape = \Arr::get($this->config, 'csv.escape', '\\');

		// escape function
		$escaper = function($items) use($enclosure, $escape) {
			return array_map(function($item) use($enclosure, $escape){
				return str_replace($enclosure, $escape.$enclosure, $item);
			}, $items);
		};

		if ($data === null)
		{
			$data = $this->data;
		}

		if (is_object($data) and ! $data instanceof \Iterator)
		{
			$data = $this->toArray($data);
		}

		// Multi-dimensional array
		if (is_array($data) and \Arr::isMulti($data))
		{
			$data = array_values($data);

			if (\Arr::isAssoc($data[0]))
			{
				$headings = array_keys($data[0]);
			}
			else
			{
				$headings = array_shift($data);
			}
		}
		// Single array
		else
		{
			$headings = array_keys((array) $data);
			$data = array($data);
		}

		$output = $enclosure.implode($enclosure.$delimiter.$enclosure, $escaper($headings)).$enclosure.$newline;

		foreach ($data as $row)
		{
			$output .= $enclosure.implode($enclosure.$delimiter.$enclosure, $escaper((array) $row)).$enclosure.$newline;
		}

		return rtrim($output, $newline);
	}

	/**
	 * To JSON conversion
	 *
	 * @param   mixed  $data
	 * @param   bool   wether to make the json pretty
	 * @return  string
	 */
	public function toJson($data = null, $pretty = false)
	{
		if ($data === null)
		{
			$data = $this->data;
		}

		// To allow exporting ArrayAccess objects like Orm\Model instances they need to be
		// converted to an array first
		$data = (is_array($data) or is_object($data)) ? $this->toArray($data) : $data;
		return $pretty ? $this->pretty_json($data) : json_encode($data);
	}

	/**
	 * To JSONP conversion
	 *
	 * @param   mixed   $data
	 * @param   bool    $pretty    wether to make the json pretty
	 * @param   string  $callback  JSONP callback
	 * @return  string  formatted JSONP
	 */
	public function toJsonp($data = null, $pretty = false, $callback = null)
	{
		if ( ! $callback and $this->input)
		{
			$callback = $this->input->getParam('callback');
			if ( ! $callback)
			{
				$callback = 'response';
			}
		}

		return $callback.'('.$this->toJson($data, $pretty).')';
	}

	/**
	 * Serialize
	 *
	 * @param   mixed  $data
	 * @return  string
	 */
	public function toSerialized($data = null)
	{
		if ($data === null)
		{
			$data = $this->data;
		}

		return serialize($data);
	}

	/**
	 * Return as a string representing the PHP structure
	 *
	 * @param   mixed  $data
	 * @return  string
	 */
	public function toPhp($data = null)
	{
		if ($data === null)
		{
			$data = $this->data;
		}

		return var_export($data, true);
	}

	/**
	 * Convert to YAML
	 *
	 * @param   mixed   $data
	 * @throws RuntimeException if the Symfony/YAML composer package is not installed
	 * @return  string
	 */
	public function toYaml($data = null)
	{
		if ($data == null)
		{
			$data = $this->data;
		}

		if ( ! class_exists('Symfony\Component\Yaml\Yaml'))
		{
			throw new \RuntimeException('You need to install the "symfony/yaml" composer package to use Format::toYaml()');
		}

		$parser = new \Symfony\Component\Yaml\Yaml();
		return $parser::dump($data);
	}

	/**
	 * Import XML data
	 *
	 * @param   string  $string
	 * @return  array
	 */
	protected function _fromXml($string)
	{
		$_arr = is_string($string) ? simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA) : $string;
		$arr = array();

		// Convert all objects SimpleXMLElement to array recursively
		foreach ((array)$_arr as $key => $val)
		{
			$arr[$key] = (is_array($val) or is_object($val)) ? $this->_fromXml($val) : $val;
		}

		return $arr;
	}

	/**
	 * Import YAML data
	 *
	 * @param   string  $string
	 * @return  array
	 */
	protected function _fromYaml($string)
	{
		if ($data == null)
		{
			$data = $this->data;
		}

		if ( ! class_exists('Symfony\Component\Yaml\Yaml'))
		{
			throw new \RuntimeException('You need to install the "symfony/yaml" composer package to use Format::fromYaml()');
		}

		$parser = new \Symfony\Component\Yaml\Yaml();
		return $parser::parse($data);
	}

	/**
	 * Import CSV data
	 *
	 * @param   string  $string
	 * @return  array
	 */
	protected function _fromCsv($string)
	{
		$data = array();

		$rows = preg_split('/(?<='.preg_quote(\Arr::get($this->config, 'csv.enclosure', '"')).')'.\Arr::get($this->config, 'csv.regex_newline', '\n').'/', trim($string));

		// csv config
		$delimiter = \Arr::get($this->config, 'csv.delimiter', ',');
		$enclosure = \Arr::get($this->config, 'csv.enclosure', '"');
		$escape = \Arr::get($this->config, 'csv.escape', '\\');

		// Get the headings
		$headings = str_replace($escape.$enclosure, $enclosure, str_getcsv(array_shift($rows), $delimiter, $enclosure, $escape));

		foreach ($rows as $row)
		{
			$data_fields = str_replace($escape.$enclosure, $enclosure, str_getcsv($row, $delimiter, $enclosure, $escape));

			if (count($data_fields) == count($headings))
			{
				$data[] = array_combine($headings, $data_fields);
			}

		}

		return $data;
	}

	/**
	 * Import JSON data
	 *
	 * @param   string  $string
	 * @return  mixed
	 */
	private function _fromJson($string)
	{
		return json_decode(trim($string));
	}

	/**
	 * Import Serialized data
	 *
	 * @param   string  $string
	 * @return  mixed
	 */
	private function _fromSerialize($string)
	{
		return unserialize(trim($string));
	}

	/**
	 * Makes json pretty the json output.
	 * Borrowed from http://www.php.net/manual/en/function.json-encode.php#80339
	 *
	 * @param   string  $json  json encoded array
	 * @return  string|false  pretty json output or false when the input was not valid
	 */
	protected function pretty_json($data)
	{
		$json = json_encode($data);

		if ( ! $json)
		{
			return false;
		}

		$tab = "\t";
		$newline = "\n";
		$new_json = "";
		$indent_level = 0;
		$in_string = false;
		$len = strlen($json);

		for ($c = 0; $c < $len; $c++)
		{
			$char = $json[$c];
			switch($char)
			{
				case '{':
				case '[':
					if ( ! $in_string)
					{
						$new_json .= $char.$newline.str_repeat($tab, $indent_level+1);
						$indent_level++;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '}':
				case ']':
					if ( ! $in_string)
					{
						$indent_level--;
						$new_json .= $newline.str_repeat($tab, $indent_level).$char;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ',':
					if ( ! $in_string)
					{
						$new_json .= ','.$newline.str_repeat($tab, $indent_level);
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ':':
					if ( ! $in_string)
					{
						$new_json .= ': ';
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '"':
					if ($c > 0 and $json[$c-1] !== '\\')
					{
						$in_string = ! $in_string;
					}
				default:
					$new_json .= $char;
					break;
			}
		}

		return $new_json;
	}
}
