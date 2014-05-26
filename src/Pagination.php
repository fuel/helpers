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
 * @since 1.0.0
 */
class Pagination
{
	/**
	 * @var  Fuel\Foundation\Input  current input instance
	 */
	protected $input;

	/**
	 * @var  Fuel\Display\View  pagination view
	 */
	protected $view;

	/**
	 * @var  array  configuration values
	 */
	protected $config = array(
		'current'        => 0,
		'offset'         => 0,
		'limit'          => 0,
		'totalPages'     => 0,
		'totalItems'     => 0,
		'numberOfLinks'  => 3,
		'uriSegment'     => 3,
		'getVariable'    => '',
		'showFirst'      => false,
		'showLast'       => false,
		'linkOffset'     => 0.5,
		'align'          => 'center',
	);

	/**
	 * @var  string  the pagination Url template. Use {page} for the location of the page number
	 */
	protected $paginationUrl = null;

	/**
	 * Construct a pagination object
	 *
	 * @param  Fuel\Display\Manager  The current applications ViewManager object
	 * @param  Fuel\Foundation\Input  The current requests Input container
	 * @param  string  View to be used to generate the pagination HTML
	 *
	 */
	public function __construct($viewmanager, $input, $view)
	{
		// store the input instance passed
		$this->input = $input;

		// construct the pagination view
		$this->view = $viewmanager->forge($view);
	}

	/**
	 * Render the pagination view when the object is cast to string or echo'd
	 */
	public function __toString()
	{
		return (string) $this->render();
	}

	/**
	 * Getter for configuration items
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Getter for configuration items
	 */
	public function get($key)
	{
		if (isset($this->config[$key]))
		{
			return $this->config[$key];
		}
	}

	/**
	 * Setter for configuration items
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Setter for configuration items
	 */
	public function set($var, $value = null)
	{
		if ( ! is_array($var))
		{
			$var = array ($var => $value);
		}

		foreach ($var as $key => $value)
		{
			$value = $this->validateConfig($key, $value);

			if (isset($this->config[$key]))
			{
				// preserve the type
				if (is_bool($this->config[$key]))
				{
					$this->config[$key] = (bool) $value;
				}
				elseif (is_string($this->config[$key]))
				{
					$this->config[$key] = (string) $value;
				}
				else
				{
					$this->config[$key] = (int) $value;
				}
			}
		}
	}

	/**
	 * Setter for the pagination url. It must contain a {page} placeholder for the page number
	 *
	 * @param  string  $url  The pagination Url template to be used to generate link urls
	 */
	public function setUrl($url)
	{
		$this->paginationUrl = $url;
	}

	/**
	 * Render the pagination view, and return the view
	 *
	 * @return  View  the configured view object
	 */
	public function render()
	{
		// make sure we have a correct url
		$this->paginationUrl();

		// and a current page number
		$this->calculateNumbers();

		$urls = array();

		// generate the URL's for the pagination block
		if ($this->config['totalPages'] > 1)
		{
			// calculate start- and end page numbers
			$start = $this->config['current'] - floor($this->config['numberOfLinks'] * $this->config['linkOffset']);
			$end = $this->config['current'] + floor($this->config['numberOfLinks'] * ( 1 - $this->config['linkOffset']));

			// adjust for the first few pages
			if ($start < 1)
			{
				$end -= $start - 1;
				$start = 1;
			}

			// make sure we don't overshoot the current page due to rounding issues
			if ($end < $this->config['current'])
			{
				$start++;
				$end++;
			}

			// make sure we don't overshoot the total
			if ($end > $this->config['totalPages'])
			{
				$start = max(1, $start - $end + $this->config['totalPages']);
				$end = $this->config['totalPages'];
			}

			// now generate the URL's for the pagination block
			for($i = $start; $i <= $end; $i++)
			{
				$urls[$i] = $this->generateUrl($i);
			}
		}

		// send the generated url's to the view
		$this->view->set('urls', $urls);

		// store the current and total pages
		$this->view->set('active', $this->config['current']);
		$this->view->set('total', $this->config['totalPages']);

		// do we need to add a first link?
		if ($this->config['showFirst'])
		{
			$this->view->set('first', $this->generateUrl(1));
		}

		if (isset($start) and $start > 1)
		{
			$this->view->set('previous', $this->generateUrl($start-1));
		}

		if (isset($end) and $end !== $this->config['totalPages'])
		{
			$this->view->set('next', $this->generateUrl($end+1));
		}

		// do we need to add a last link?
		if ($this->config['showLast'])
		{
			$this->view->set('last', $this->generateUrl($this->config['totalPages']));
		}

		$this->view->set('align', $this->config['align']);

		// return the view
		return $this->view;
	}

	/**
	 * Generate the link to a particular page
	 *
	 * @param  int  $link  page number
	 *
	 * @return  string  generated link to a page
	 */
	protected function generateUrl($link)
	{
		return str_replace('{page}', $link, $this->paginationUrl);
	}

	/**
	 * Construct the pagination Url from the current Url and the configuration set
	 */
	protected function paginationUrl()
	{
		// if we have one, don't bother
		if ( ! empty($this->paginationUrl))
		{
			return;
		}

		// do we have any GET variables?
		$get = $this->input->getQuery();

		// do we need to set one
		if ( ! empty($this->config['getVariable']))
		{
			// don't use curly braces here, http_build_query will encode them
			$get[$this->config['getVariable']] = '___PAGE___';
		}

		// do we need to create a segment?
		if ( ! empty($this->config['uriSegment']))
		{
			$segments = explode('/', trim($this->input->getPathInfo(),'/'));
			$segments[$this->config['uriSegment']] = '{page}';

			// construct the Uri
			$this->paginationUrl = '/'.implode('/', $segments);
		}
		else
		{
			// start with the current Uri
			$this->paginationUrl = $this->input->getPathInfo();
		}

		// attach the extension if needed
		$this->paginationUrl .= $this->input->getExtension();

		// any get variables?
		if ( ! empty($get))
		{
			$this->paginationUrl .= '?'.str_replace('___PAGE___', '{page}', http_build_query($get->getContents()));
		}
	}

	/**
	 * If no current page number is given, calculate it
	 */
	protected function calculateNumbers()
	{
		// do we need to fetch or calculate the current page number?
		if (empty($this->config['current']))
		{
			// do we have a segment number?
			if ( ! empty($this->config['uriSegment']))
			{
				$segments = explode('/', trim($this->input->getPathInfo(),'/'));
				if (isset($segments[$this->config['uriSegment']]) and is_numeric($segments[$this->config['uriSegment']]))
				{
					$this->config['current'] = $segments[$this->config['uriSegment']];
				}
			}

			// do we have a getVariable set?
			if ( ! empty($this->config['getVariable']) and $get = $this->input->getQuery())
			{
				if (isset($get[$this->config['getVariable']]) and is_numeric($get[$this->config['getVariable']]))
				{
					$this->config['current'] = $get[$this->config['uriSegment']];
				}
			}

			// if none could be determine, try to calculate it
			if (empty($this->config['current']) and $this->config['offset'] and $this->config['limit'])
			{
				$this->config['current'] = (int) ($this->config['offset'] / $this->config['limit']) + 1;
			}

			// if all else fails, default to one
			if (empty($this->config['current']))
			{
				$this->config['current'] = 1;
			}
		}

		// do we need to calculate the total number of pages
		if (empty($this->config['totalPages']) and ! empty($this->config['totalItems']) and ! empty($this->config['limit']))
		{
			$this->config['totalPages'] = (int) ($this->config['totalItems'] / $this->config['limit']) + 1;
		}
	}

	/**
	 * Generate a pagination link
	 */
	protected function validateConfig($name, $value)
	{
		switch ($name)
		{
			case 'offset':
			case 'totalItems':
				// make sure it's an integer
				if ($value != intval($value))
				{
					$value = 0;
				}
				// and that it's within bounds
				$value = max(0, $value);
			break;

			// validate integer values
			case 'current':
			case 'limit':
			case 'totalPages':
			case 'numberOfLinks':
			case 'uriSegment':
				// make sure it's an integer
				if ($value != intval($value))
				{
					$value = 1;
				}
				// and that it's within bounds
				$value = max(1, $value);
			break;

			// validate booleans
			case 'showFirst':
			case 'showLast':
				if ( ! is_bool($value))
				{
					$value = (bool) $value;
				}
			break;

			// possible alignment values
			case 'align':
				if ( ! in_array($value = strtolower($value), array('left', 'center', 'right')))
				{
					$value = 'center';
				}
			break;

			// validate the link offset, and adjust if needed
			case 'linkOffset':
				// make sure we have a fraction between 0 and 1
				if ($value > 1)
				{
					$value = $value / 100;
				}

				// and that it's within bounds
				$value = max(0.01, min($value, 0.99));
			break;
		}

		return $value;
	}
}
