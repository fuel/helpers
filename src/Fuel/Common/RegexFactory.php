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

use \LogicException;

/**
 * Class for building regular expressions
 *
 * @package Fuel\Common
 * @author  Fuel Development Team
 *
 * @since 2.0
 */
class RegexFactory
{

	/**
	 * Contains the expression being built
	 *
	 * @var string
	 */
	protected $expression;

	/**
	 * Keeps track of any open delimiters to ensure everything is closed correctly
	 *
	 * @var int
	 */
	protected $openDelimiters;

	/**
	 * Contains the status of any flags that have been added
	 *
	 * @var array
	 */
	protected $flags;

	public function __construct()
	{
		$this->reset();
	}

	/**
	 * Resets the string being built
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function reset()
	{
		$this->expression = '';
		$this->openDelimiters = 0;

		return $this;
	}

	/**
	 * Gets the built expression
	 *
	 * @param bool $delimit Set to false to disable adding the delimiters to the string
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public function get($delimit = true)
	{
		// If there are still open delimiters throw an exception
		if ($this->openDelimiters !== 0)
		{
			throw new LogicException('A delimiter has not been closed!');
		}

		$expression = $this->expression;

		if ($delimit)
		{
			$expression = '/' . $expression . '/'; //TODO: append modifiers
		}

		return $expression;
	}

	/**
	 * Calls get() to return the built regex
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public function __toString()
	{
		return $this->get();
	}

	/**
	 * Adds any given value to the expression being built
	 *
	 * @param string $value
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function value($value)
	{
		$this->expression .= $value;

		return $this;
	}

	/**
	 * Starts a group capture
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function startGroupCapture()
	{
		$this->expression .= '(';
		$this->openDelimiters++;

		return $this;
	}

	/**
	 * Ends a group capture
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function endGroupCapture()
	{
		$this->value(')');
		$this->openDelimiters--;

		return $this;
	}

	/**
	 * Starts a range indicator
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function startRange()
	{
		$this->value('[');
		$this->openDelimiters++;

		return $this;
	}

	/**
	 * Ends a range indicator
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function endRange()
	{
		$this->value(']');
		$this->openDelimiters--;

		return $this;
	}

	/**
	 * Adds a a-z range
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function lowercase()
	{
		$this->value('a-z');

		return $this;
	}

	/**
	 * Adds a A-Z range
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function uppercase()
	{
		$this->value('A-Z');

		return $this;
	}

	/**
	 * Adds a 0-9 range
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function numeric()
	{
		$this->value('0-9');

		return $this;
	}

	/**
	 * Adds an "any" matcher
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function any()
	{
		$this->value('.');

		return $this;
	}

	/**
	 * Matches against the start of the string
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function start()
	{
		$this->value('^');

		return $this;
	}

	/**
	 * Matches the end of the string
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function end()
	{
		$this->value('$');

		return $this;
	}

	/**
	 * Matches none of one of the preceding statements
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function noneOrOne()
	{
		$this->value('?');

		return $this;
	}

	/**
	 * Matches none or more of the preceding statement
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function noneOrMany()
	{
		$this->value('*');

		return $this;
	}

	/**
	 * Matches one or more of the preceding statement
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function oneOrMore()
	{
		$this->value('+');

		return $this;
	}

	/**
	 * Adds an or "|"
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function addOr()
	{
		$this->value('|');

		return $this;
	}

	/**
	 * Adds a {} to match a quantity
	 *
	 * @param int      $min
	 * @param int|null $max
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function matchQuantity($min, $max = null)
	{
		$match = '{'.$min;

		if ($max !== null)
		{
			$match .= ',' . $max;
		}

		$match .= '}';

		$this->value($match);

		return $this;
	}

}
