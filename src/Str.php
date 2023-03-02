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
use RuntimeException;
use Fuel\Framework\Fuel;

use function preg_match;
use function preg_match_all;
use function preg_quote;
use function strip_tags;
use function strlen;
use function substr;
use function strcmp;
use function strncmp;
use function mb_strlen;
use function mb_strpos;
use function mb_strrpos;
use function mb_substr;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_stripos;
use function mb_strripos;
use function mb_strstr;
use function mb_stristr;
use function mb_strrchr;
use function mb_substr_count;
use function mb_convert_case;
use function strtok;
use function in_array;
use function end;
use function array_pop;
use function min;
use function array_reverse;
use function count;
use function implode;
use function md5;
use function sha1;
use function uniqid;
use function mt_rand;
use function array_rand;
use function sprintf;
use function strtr;
use function json_decode;
use function json_last_error;
use function defined;
use function libxml_use_internal_errors;
use function simplexml_load_string;
use function unserialize;

use const PREG_OFFSET_CAPTURE;
use const PREG_SET_ORDER;
use const JSON_ERROR_NONE;
use const MB_CASE_TITLE;

/**
 * String handling with encoding support
 */
class Str
{
    /**
     * Truncates a string to the given length.  It will optionally preserve
     * HTML tags if $is_html is set to true.
     *
     * @static
     * @param string $string
     * @param int $limit
     * @param string $continuation
     * @param bool $is_html
     * @return string
     * @since 1.0.0
     */
    public static function truncate(string $string, int $limit, string $continuation = '...', bool $is_html = false): string
    {
        static $self_closing_tags = array(
            'area', 'base', 'br', 'col', 'command', 'embed'
            , 'hr', 'img', 'input', 'keygen', 'link', 'meta'
            , 'param', 'source', 'track', 'wbr'
        );

        $offset = 0;
        $tags = [];

        if (true === $is_html)
        {
            // Handle special characters.
            preg_match_all('/&[a-z]+;/i', strip_tags($string), $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

            // fix preg_match_all broken multibyte support
            if (strlen($string !== mb_strlen($string)))
            {
                $correction = 0;
                foreach ($matches as $index => $match)
                {
                    $matches[$index][0][1] -= $correction;
                    $correction += (strlen($match[0][0]) - mb_strlen($match[0][0]));
                }
            }
            foreach ($matches as $match)
            {
                if ($match[0][1] >= $limit)
                {
                    break;
                }
                $limit += (static::length($match[0][0]) - 1);
            }

            // Handle all the html tags.
            preg_match_all('/<[^>]+>([^<]*)/', $string, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

            // fix preg_match_all broken multibyte support
            if (strlen($string !== mb_strlen($string)))
            {
                $correction = 0;
                foreach ($matches as $index => $match)
                {
                    $matches[$index][0][1] -= $correction;
                    $matches[$index][1][1] -= $correction;
                    $correction += (strlen($match[0][0]) - mb_strlen($match[0][0]));
                }
            }

            foreach ($matches as $match)
            {
                if($match[0][1] - $offset >= $limit)
                {
                    break;
                }

                $tag = static::sub(strtok($match[0][0], " \t\n\r\0\x0B>"), 1);
                if ('/' != $tag[0])
                {
                    if ( ! in_array($tag, $self_closing_tags))
                    {
                        $tags[] = $tag;
                    }
                }
                elseif (end($tags) == static::sub($tag, 1))
                {
                    array_pop($tags);
                }
                $offset += $match[1][1] - $match[0][1];
            }
        }

        $new_string = static::sub($string, 0, $limit = min(static::length($string),  $limit + $offset));
        $new_string .= (static::length($string) > $limit ? $continuation : '');
        $new_string .= (count($tags = array_reverse($tags)) ? '</'.implode('></', $tags).'>' : '');

        return $new_string;
    }

    /**
     * Add's _1 to a string or increment the ending number to allow _2, _3, etc
     *
     * @static
     * @param string $str
     * @param int $first
     * @param string $separator
     * @return string
     * @since 1.0.0
     */
    public static function increment(string $str, int $first = 1, string $separator = '_'): string
    {
        preg_match('/(.+)'.$separator.'([0-9]+)$/', $str, $match);

        return isset($match[2]) ? $match[1].$separator.($match[2] + 1) : $str.$separator.$first;
    }

    /**
     * Checks whether a string has a precific beginning.
     *
     * NB: ignore-case might not work for Unicode characters!
     *
     * @static
     * @param string $str
     * @param string $start
     * @param bool $ignore_case
     * @return bool
     * @since 1.0.0
     */
    public static function starts_with(string $str, string $start, bool $ignore_case = false): bool
    {
        return (bool) preg_match('/^'.preg_quote($start, '/').'/m'.($ignore_case ? 'i' : ''), $str);
    }

    /**
     * Checks whether a string has a precific ending.
     *
     * NB: ignore-case might not work for Unicode characters!
     *
     * @static
     * @param string $str
     * @param string $end
     * @param bool $ignore_case
     * @return bool
     * @since 1.0.0
     */
    public static function ends_with(string $str, string $end, bool $ignore_case = false): bool
    {
        return (bool) preg_match('/'.preg_quote($end, '/').'$/m'.($ignore_case ? 'i' : ''), $str);
    }

    /**
      * Creates a random string of characters
      *
     * @static
      * @param string $type
      * @param int @length
      * @return string
     * @since 1.0.0
      */
    public static function random(string $type = 'alnum', int $length = 16): string
    {
        switch($type)
        {
            case 'basic':
                return mt_rand();
            case 'unique':
                return md5(uniqid(mt_rand()));
            case 'sha1' :
                return sha1(uniqid(mt_rand(), true));
            case 'uuid':
                $pool = array_rand(['8', '9', 'a', 'b']);
                return sprintf('%s-%s-4%s-%s%s-%s',
                    static::random('hexdec', 8),
                    static::random('hexdec', 4),
                    static::random('hexdec', 3),
                    $pool,
                    static::random('hexdec', 3),
                    static::random('hexdec', 12));
            default:
                $pool = match($type)
                {
                    'alpha' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                    'alnum' =>'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                    'numeric' => '0123456789',
                    'nozero' => '123456789',
                    'distinct' => '2345679ACDEFHJKLMNPRSTUVWXYZ',
                    'hexdec' => '0123456789abcdef',
                    default =>'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                };

                $str = '';
                for ($i=0; $i < $length; $i++)
                {
                    $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
                }
                return $str;
        }
    }

    /**
     * Returns a closure that will alternate between the args which to return.
     * If you call the closure with false as the arg it will return the value without
     * alternating the next time.
     *
     * @static
     * @param mixed $args,...
     * @return  Closure
     * @since 1.0.0
     */
    public static function alternator(mixed ...$args): Closure
    {
        return function ($next = true) use ($args)
        {
            static $i = 0;
            return $args[($next ? $i++ : $i) % count($args)];
        };
    }

    /**
     * Parse the params from a string using strtr()
     *
     * @static
     * @param string $string
     * @param array $array
     * @return string
     * @since 1.0.0
     */
    public static function tr(string $string, array $array = []): string
    {
        $tr_arr = [];

        foreach ($array as $from => $to)
        {
            if (':' !== substr($from, 0, 1))
            {
                $from = ':'.$from;
            }
            $tr_arr[$from] = $to;
        }

        return strtr($string, $tr_arr);
    }

    /**
     * Check if a string is json encoded
     *
     * @static
     * @param string $string
     * @return bool
     * @since 1.0.0
     */
    public static function is_json(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Check if a string is a valid XML
     *
     * @static
     * @param string $string
     * @return bool
     * @throws \RuntimeException
     * @since 1.0.0
     */
    public static function is_xml(string $string): bool
    {
        if ( ! defined('LIBXML_COMPACT'))
        {
            throw new RuntimeException('Fuel2: libxml is required to use Str::is_xml()');
        }

        $internal_errors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);
        $result = (false !== simplexml_load_string($string));
        libxml_use_internal_errors($internal_errors);

        return $result;
    }

    /**
     * Check if a string is serialized
     *
     * @static
     * @param string $string
     * @return bool
     * @since 1.0.0
     */
    public static function is_serialized(string $string): bool
    {
        try
        {
            $array = unserialize($string);
        }
        catch(Exception)
        {
            $array = false;
        }

        return ! (false === $array and 'b:0;' !== $string);
    }

    /**
     * Check if a string is html
     *
     * @static
     * @param string $string
     * @return bool
     * @since 1.0.0
     */
    public static function is_html(string $string): bool
    {
        return strlen(strip_tags($string)) !== strlen($string);
    }

    // multibyte safe functions

    /**
     * strpos — Find the position of the first occurrence of a substring in a string
     *
     * @static
     * @param string $str
     * @return int
     * @since 1.0.0
     */
    public static function strlen(string $str): int
    {
        return mb_strlen($str, 'UTF-8');
    }

    /**
     * strpos — Find position of first occurrence of string in a string
     *
     * @static
     * @param string $haystack
     * @param mixed $needle
     * @param int $offset
     * @return mixed
     * @since 1.0.0
     */
    public static function strpos(string $haystack, mixed $needle, int $offset = 0): mixed
    {
        return mb_strpos($haystack, $needle, $offset, 'UTF-8');
    }

    /**
     * strrpos — Find position of last occurrence of a string in a string
     *
     * @static
     * @param string $haystack
     * @param mixed $needle
     * @param int $offset
     * @return mixed
     * @since 1.0.0
     */
    public static function strrpos(string $haystack, mixed $needle, int $offset = 0): mixed
    {
        return mb_strrpos($haystack, $needle, $offset, 'UTF-8');
    }

    /*
     * substr — Get part of string
     *
     * @static
     * @param string $str
     * @param int $start
     * @param int $length
     * @return mixed
     * @since 1.0.0.
     */
    public static function substr(string $str, int $start, int $length = null): mixed
    {
        // substr functions don't parse null correctly if the string is multibyte
        if (null === $length)
        {
            $length = mb_strlen($str, 'UTF-8') - $start;
        }

        return mb_substr($str, $start, $length, 'UTF-8');
    }

    /**
     * strtolower — Make a string lowercase
     *
     * @static
     * @param string $str
     * @return string
     * @since 1.0.0
     */
    public static function strtolower(string $str): string
    {
        return mb_strtolower($str, 'UTF-8');
    }

    /**
     * strtoupper — Make a string uppercase
     *
     * @static
     * @param string $str
     * @return string
     * @since 1.0.0
     */
    public static function strtoupper(string $str): string
    {
        return mb_strtoupper($str, 'UTF-8');
    }

    /**
     * Binary safe case-insensitive string comparison
     *
     * @static
     * @param string $str1
     * @param string @str2
     * @return int
     * @since 2.0.0
     */
    public static function strcasecmp(string $str1, string $str2): int
    {
        $str1 = static::strtolower($str1);
        $str2 = static::strtolower($str2);

        return strcmp($str1, $str2);
    }

    /**
     * Binary safe case-insensitive string comparison of the first n characters
     *
     * @static
     * @param string $str1
     * @param string $str2
     * @param int $len
     * @return int
     * @since 2.0.0
     */
    public static function strncasecmp(string $str1, string $str2, int $len): int
    {
        $str1 = static::strtolower($str1);
        $str2 = static::strtolower($str2);

        return strncmp($str1, $str2, $len);
    }

    /**
     * stripos — Find the position of the first occurrence of a case-insensitive substring in a string
     *
     * @static
     * @param string $haystack
     * @param mixed $needle
     * @param int $offset
     * @return mixed
     * @since 1.0.0
     */
    public static function stripos(string $haystack, mixed $needle, int $offset = 0): mixed
    {
        return mb_stripos($haystack, $needle, $offset, 'UTF-8');
    }

    /**
     * strripos — Finds position of last occurrence of a string within another, case insensitive
     *
     * @static
     * @param string $haystack
     * @param mixed $needle
     * @param int $offset
     * @return mixed
     * @since 1.0.0
     */
    public static function strripos(string $haystack, mixed $needle, int $offset = 0): mixed
    {
        return mb_strripos($haystack, $needle, $offset, 'UTF-8');
    }

    /**
     * strstr — Finds first occurrence of a string within another
     *
     * @static
     * @param string $haystack
     * @param mixed $needle
     * @param bool $before_needle
     * @return mixed
     * @since 1.0.0
     */
    public static function strstr(string $haystack, mixed $needle, bool $before_needle = false): mixed
    {
        return mb_strstr($haystack, $needle, $before_needle, $encoding, 'UTF-8');
    }

    /**
     * stristr — Finds first occurrence of a string within another, case-insensitive
     *
     * @static
     * @param string $haystack
     * @param mixed $needle
     * @param bool $before_needle
     * @return mixed
     * @since 1.0.0
     */
    public static function stristr(string $haystack, mixed $needle, bool $before_needle = false): mixed
    {
        return mb_stristr($haystack, $needle, $before_needle, 'UTF-8');
    }

    /**
     * strrchr — Finds the last occurrence of a character in a string within another
     *
     * @static
     * @param string $haystack
     * @param mixed $needle
     * @param bool $before_needle
     * @return mixed
     * @since 1.0.0
     */
    public static function strrchr(string $haystack, mixed $needle, bool $before_needle = false, $encoding = null): mixed
    {
        return mb_strrchr($haystack, $needle, $before_needle, 'UTF-8');
    }

    /**
     * substr_count — Count the number of substring occurrences
     *
     * @static
     * @param string $haystack
     * @param mixed $needle
     * @return int
     * @since 1.0.0
     */
    public static function substr_count(string $haystack, mixed $needle): int
    {
        return mb_substr_count($haystack, $needle, 'UTF-8');
    }

    /**
     * lcfirst
     *
     * Does not strtoupper first
     *
     * @static
     * @param string $str
     * @return  string
     * @since 1.0.0
     */
    public static function lcfirst(string $str): string
    {
        return mb_strtolower(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($str, 1, mb_strlen($str, 'UTF-8'), 'UTF-8');
    }

    /**
     * ucfirst
     *
     * Does not strtolower first
     *
     * @static
     * @param string $str
     * @return  string
     * @since 1.0.0
     */
    public static function ucfirst(string $str): string
    {
        return mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($str, 1, mb_strlen($str, 'UTF-8'), 'UTF-8');
    }

    /**
     * ucwords
     *
     * First strtolower then ucwords
     *
     * @static
     * Note: ucwords normally doesn't strtolower first, but MB_CASE_TITLE does,
     * so ucwords now too
     *
     * @param string $str
     * @return  string
     */
    public static function ucwords(string $str): string
    {
        return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
    }
}
