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

use function in_array;
use function range;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function strval;
use function strtoupper;
use function str_replace;
use function lcfirst;
use function ucfirst;
use function array_keys;
use function array_values;
use function html_entity_decode;
use function trim;
use function substr;

use const ENT_QUOTES;

/**
 * Some of this code was written by Flinn Mueller.
 * Note: It deals with the Engliah language only !
 *
 * @since 1.0.0
 */
class Inflector
{
	/**
	 * List of items in the English language you can not count
	 *
	 * @since 1.0.0
	 */
	protected static array $uncountable_words = [
		'equipment', 'information', 'rice', 'money',
		'species', 'series', 'fish', 'meta',
		'chocolade',
	];

	/**
	 * singular to plural conversion rules
	 *
	 * @since 1.0.0
	 */
	protected static array $plural_rules = [
		'/^(ox)$/i'                 => '\1\2en',     // ox
		'/([m|l])ouse$/i'           => '\1ice',      // mouse, louse
		'/(matr|vert|ind)ix|ex$/i'  => '\1ices',     // matrix, vertex, index
		'/(x|ch|ss|sh)$/i'          => '\1es',       // search, switch, fix, box, process, address
		'/([^aeiouy]|qu)y$/i'       => '\1ies',      // query, ability, agency
		'/(hive)$/i'                => '\1s',        // archive, hive
		'/(?:([^f])fe|([lr])f)$/i'  => '\1\2ves',    // half, safe, wife
		'/sis$/i'                   => 'ses',        // basis, diagnosis
		'/([ti])um$/i'              => '\1a',        // datum, medium
		'/(p)erson$/i'              => '\1eople',    // person, salesperson
		'/(m)an$/i'                 => '\1en',       // man, woman, spokesman
		'/(c)hild$/i'               => '\1hildren',  // child
		'/(buffal|tomat)o$/i'       => '\1\2oes',    // buffalo, tomato
		'/(bu|campu)s$/i'           => '\1\2ses',    // bus, campus
		'/(alias|status|virus)$/i'  => '\1es',       // alias
		'/(octop)us$/i'             => '\1i',        // octopus
		'/(ax|cris|test)is$/i'      => '\1es',       // axis, crisis
		'/s$/'                     => 's',          // no change (compatibility)
		'/$/'                      => 's',
	];

	/**
	 * plural to singular conversion rules
	 *
	 * @since 1.0.0
	 */
	protected static array $singular_rules = [
		'/(matr)ices$/i'         => '\1ix',
		'/(vert|ind)ices$/i'     => '\1ex',
		'/^(ox)en/i'             => '\1',
		'/(alias)es$/i'          => '\1',
		'/([octop|vir])i$/i'     => '\1us',
		'/(cris|ax|test)es$/i'   => '\1is',
		'/(shoe)s$/i'            => '\1',
		'/(o)es$/i'              => '\1',
		'/(bus|campus)es$/i'     => '\1',
		'/([m|l])ice$/i'         => '\1ouse',
		'/(x|ch|ss|sh)es$/i'     => '\1',
		'/(m)ovies$/i'           => '\1\2ovie',
		'/(s)eries$/i'           => '\1\2eries',
		'/([^aeiouy]|qu)ies$/i'  => '\1y',
		'/([lr])ves$/i'          => '\1f',
		'/(tive)s$/i'            => '\1',
		'/(hive)s$/i'            => '\1',
		'/([^f])ves$/i'          => '\1fe',
		'/(^analy)ses$/i'        => '\1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
		'/([ti])a$/i'            => '\1um',
		'/(p)eople$/i'           => '\1\2erson',
		'/(m)en$/i'              => '\1an',
		'/(s)tatuses$/i'         => '\1\2tatus',
		'/(c)hildren$/i'         => '\1\2hild',
		'/(n)ews$/i'             => '\1\2ews',
		'/([^us])s$/i'           => '\1',
	];

	/**
	 * -----------------------------------------------------------------------------
	 * Load any localized rulesets based on the current language configuration
	 * If not exists, the current rules remain active
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function load_rules(): void
	{
		die('@TODO');
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Add order suffix to numbers ex. 1st 2nd 3rd 4th 5th
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function ordinalize(int $number): string
	{
		if (in_array(($number % 100), range(11, 13)))
		{
			return $number.'th';
		}

		return match($number % 10)
		{
			1 => $number.'st',
			2 => $number.'nd',
			3 => $number.'rd',
			default => $number.'th',
		};
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Gets the plural version of the given word
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function pluralize(string $word, int $count = 0): string
	{
		if ($count === 1)
		{
			return $result;
		}

		if ( ! static::isCountable($result))
		{
			return $result;
		}

		foreach (static::$plural_rules as $rule => $replacement)
		{
			if (preg_match($rule, $result))
			{
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Gets the singular version of the given word
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function singularize(string $word): string
	{
		if ( ! static::isCountable($result))
		{
			return $result;
		}

		foreach (static::$singular_rules as $rule => $replacement)
		{
			if (preg_match($rule, $result))
			{
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Takes a string that has words separated by underscores and turns it into
	 * a PascalCased string.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function pascalize(string $underscored_word): string
	{
		return preg_replace_callback(
			'/(^|_)(.)/',
			function ($parm)
			{
				return strtoupper($parm[2]);
			},
			$underscored_word
		);
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Takes a string that has words separated by underscores and turns it into
	 * a camelCased string.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function camelize(string $underscored_word): string
	{
		return lcfirst(static::pascalize($underscored_word));
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Takes a PascalCased or Camelcased string and returns an underscore separated version.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function underscore(string $word): string
	{
		return Str::strtolower(preg_replace('/([A-Z]+)([A-Z])/', '\1_\2', preg_replace('/([a-z\d])([A-Z])/', '\1_\2', ucfirst($word))));
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Translate string to 7-bit ASCII.
	 * Note: only works with UTF-8.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function ascii(string $str, bool $allow_non_ascii = false): string
	{
		static $mapping = [
			'/æ|ǽ/' => 'ae',
			'/œ/'   => 'oe',

			'/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ|А/'   => 'A',
			'/à|á|â|ã|ä|å|ǻ|ā|ă|ą|ǎ|ª|а/' => 'a',

			'/Б/' => 'B',
			'/б/' => 'b',

			'/Ç|Ć|Ĉ|Ċ|Č|Ц/' => 'C',
			'/ç|ć|ĉ|ċ|č|ц/' => 'c',

			'/Ð|Ď|Đ|Д/' => 'D',
			'/ð|ď|đ|д/' => 'd',

			'/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|Е|Ё|Э/' => 'E',
			'/è|é|ê|ë|ē|ĕ|ė|ę|ě|е|ё|э/' => 'e',

			'/Ф/'   => 'F',
			'/ƒ|ф/' => 'f',

			'/Ĝ|Ğ|Ġ|Ģ|Г/' => 'G',
			'/ĝ|ğ|ġ|ģ|г/' => 'g',

			'/Ĥ|Ħ|Х/' => 'H',
			'/ĥ|ħ|х/' => 'h',

			'/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|И/' => 'I',
			'/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|и/' => 'i',

			'/Ĵ|Й/' => 'J',
			'/ĵ|й/' => 'j',

			'/Ķ|К/' => 'K',
			'/ķ|к/' => 'k',

			'/Ĺ|Ļ|Ľ|Ŀ|Ł|Л/' => 'L',
			'/ĺ|ļ|ľ|ŀ|ł|л/' => 'l',

			'/М/' => 'M',
			'/м/' => 'm',

			'/Ñ|Ń|Ņ|Ň|Н/'   => 'N',
			'/ñ|ń|ņ|ň|ŉ|н/' => 'n',

			'/Ò|Ó|Ö|Ő|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|О/'   => 'O',
			'/ò|ó|ö|ő|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|о/' => 'o',

			'/П/' => 'P',
			'/п/' => 'p',

			'/Ŕ|Ŗ|Ř|Р/' => 'R',
			'/ŕ|ŗ|ř|р/' => 'r',

			'/Ś|Ŝ|Ş|Š|С/'   => 'S',
			'/ś|ŝ|ş|š|ſ|с/' => 's',

			'/Ţ|Ť|Ŧ|Т/' => 'T',
			'/ţ|ť|ŧ|т/' => 't',

			'/Ù|Ú|Ü|Ű|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|У/' => 'U',
			'/ù|ú|ü|ű|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|у/' => 'u',

			'/В/' => 'V',
			'/в/' => 'v',

			'/Ý|Ÿ|Ŷ|Ы/' => 'Y',
			'/ý|ÿ|ŷ|ы/' => 'y',

			'/Ŵ/' => 'W',
			'/ŵ/' => 'w',

			'/Ź|Ż|Ž|З/' => 'Z',
			'/ź|ż|ž|з/' => 'z',

			'/Æ|Ǽ/' => 'AE',

			'/ß/' => 'ss',
			'/Ĳ/' => 'IJ',
			'/ĳ/' => 'ij',
			'/Œ/' => 'OE',
			'/Ч/' => 'Ch',
			'/ч/' => 'ch',
			'/Ю/' => 'Ju',
			'/ю/' => 'ju',
			'/Я/' => 'Ja',
			'/я/' => 'ja',
			'/Ш/' => 'Sh',
			'/ш/' => 'sh',
			'/Щ/' => 'Shch',
			'/щ/' => 'shch',
			'/Ж/' => 'Zh',
			'/ж/' => 'zh',
		];

		$str = preg_replace(array_keys($mapping), array_values($mapping), $str);

		if (false === $allow_non_ascii)
		{
			return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $str);
		}

		return $str;
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Converts your text to a URL-friendly title so it can be used in the URL.
	 * Only works with UTF8 input and and only outputs 7 bit ASCII characters.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function friendlyTitle(string $str, string $sep = '-', bool $lowercase = false, bool $allow_non_ascii = false): string
	{
		// Remove tags
// @TODO
//        $str = \Security::strip_tags($str);

		// Decode all entities to their simpler forms
		$str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');

		// Only allow 7bit characters
		$str = static::ascii($str, $allow_non_ascii);

		if (true === $allow_non_ascii)
		{
			// Strip regular special chars
			$str = preg_replace("#[\.;:\]\}\[\{\+\)\(\*&\^\$\#@\!±`%~']#iu", '', $str);
		}
		else
		{
			// Strip unwanted characters
			$str = preg_replace("#[^a-z0-9]#i", $sep, $str);
		}

		// Remove all quotes
		$str = preg_replace("#[\"\']#", '', $str);

		// Replace apostrophes by separators
		$str = preg_replace("#[\’]#", '-', $str);

		// Replace repeating characters
		$str = preg_replace("#[/_|+ -]+#u", $sep, $str);

		// Remove separators from both ends
		$str = trim($str, $sep);

		// And convert to lowercase if needed
		if (true === $lowercase)
		{
			$str = Str::strtolower($str);
		}

		return $str;
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Turns an underscore or dash separated word and turns it into a human looking string.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function humanize(string $str, string $sep = '_', bool $lowercase = true): string
	{
		// Allow dash, otherwise default to underscore
		$sep = $sep != '-' ? '_' : $sep;

		if (true === $lowercase)
		{
			$str = Str::ucfirst($str);
		}

		return str_replace($sep, " ", strval($str));
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Takes the namespace off the given class name.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function deNamespace(string $class_name): string
	{
		$class_name = trim($class_name, '\\');
		if ($last_separator = Str::strrpos($class_name, '\\'))
		{
			$class_name = Str::substr($class_name, $last_separator + 1);
		}
		return $class_name;
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Returns the namespace of the given class name.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function getNamespace(string $class_name): string
	{
		$class_name = trim($class_name, '\\');
		if ($last_separator = Str::strrpos($class_name, '\\'))
		{
			return Str::substr($class_name, 0, $last_separator + 1);
		}
		return '';
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Takes a class name and determines the table name.  The table name is a
	 * pluralized version of the class name.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function tableize(string $class_name): string
	{
		$class_name = static::denamespace($class_name);
		if (Str::strncasecmp($class_name, 'Model_', 6) === 0)
		{
			$class_name = substr($class_name, 6);
		}
		return Str::strtolower(static::pluralize(static::underscore($class_name)));
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Checks if the given word has a plural version.
	 * -----------------------------------------------------------------------------
	 *
	 * @since 1.0.0
	 */
	public static function isCountable(string $word): bool
	{
		return ! (in_array(Str::strtolower(strval($word)), static::$uncountable_words));
	}
}
