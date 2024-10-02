<?php

/**
 * WPSpeed - Performs several front-end optimizations for fast downloads
 *
 * @package   WPSpeed
 * @author    JExtensions Store <info@storejextensions.org>
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */


namespace WPSpeed\Platform;

defined('_WP_EXEC') or die('Restricted access');

use WPSpeed\Core\Helper;
use WPSpeed\Core\Interfaces\Uri as UriInterface;

class Uri implements UriInterface
{

	private $aUri;
	private static $aInstances;
	private static $base = array();

	/**
	 *
	 * @param   string  $path
	 */
	public function setPath($path)
	{
		$this->aUri['path'] = $path;
	}

	/**
	 *
	 * @return string
	 */
	public function getPath()
	{
		return isset($this->aUri['path']) ? $this->_cleanPath($this->aUri['path']) : '';
	}

	/**
	 *
	 * @param   array  $parts
	 *
	 * @return string
	 */
	public function toString(array $parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
	{
		$url = '';

		if (in_array('scheme', $parts) && isset($this->aUri['scheme']))
		{

			$url .= $this->aUri['scheme'] . '://';
		}

		if (in_array('user', $parts) && isset($this->aUri['user']))
		{

			$url .= $this->aUri['user'];

			if (in_array('pass', $parts) && isset($this->aUri['pass']))
			{

				$url .= ':' . $this->aUri['pass'];
			}

			$url .= '@';
		}

		if (in_array('host', $parts) && isset($this->aUri['host']))
		{

			$url .= $this->aUri['host'];
		}

		if (in_array('port', $parts) && isset($this->aUri['port']))
		{
			$url .= ':' . $this->aUri['port'];
		}

		if (in_array('path', $parts) && isset($this->aUri['path']))
		{
			$url .= $this->getPath();
		}

		if (in_array('query', $parts) && isset($this->aUri['query']))
		{
			$url .= '?' . $this->aUri['query'];
		}

		if (in_array('fragment', $parts) && isset($this->aUri['fragment']))
		{
			$url .= '#' . $this->aUri['fragment'];
		}

		return $url;
	}

	/**
	 *
	 * @param   bool  $pathonly
         *
	 * @return string
         *  $pathonly == TRUE => /folder or ''
         *  $pathonly == FALSE => http://localhost/folder/ or http://localhost/
	 */
	public static function base($pathonly = false)
	{
		if (empty(self::$base))
		{
			//$uri = self::getInstance();
			//
			//$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			//$path = str_replace('/wp-admin', '', $path);
			//
			//self::$base['pathonly'] = $path;
			//self::$base['base'] = $uri->toString(array('scheme', 'host', 'port')) . $path . '/';
			self::$base['pathonly'] = site_url(null, 'relative');
			self::$base['base']     = site_url('/');
		}

		return $pathonly ? self::$base['pathonly'] : self::$base['base'];
	}

	/**
	 *
	 * @param   string  $uri
	 *
	 * @return Uri
	 */
	public static function getInstance($uri = 'SERVER')
	{
		if (empty(self::$aInstances[$uri]))
		{
			self::$aInstances[$uri] = new Uri($uri);
		}

		return self::$aInstances[$uri];
	}

	/**
	 *
	 * @param   string  $uri
	 */
	private function __construct($uri)
	{

		if ($uri == 'SERVER')
		{
			$scheme = is_ssl() ? 'https://' : 'http://';
			$uri    = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			// Extra cleanup to remove invalid chars in the URL to prevent injections through the Host header
			$uri = str_replace(array("'", '"', '<', '>'), array("%27", "%22", "%3C", "%3E"), $uri);
		}

		$this->aUri = Helper::parseUrl($uri);
	}

	/**
	 *
	 * @param     $query
	 */
	public function setQuery($query)
	{
		$this->aUri['query'] = http_build_query($query);
	}

	/**
	 *
	 * @return string
	 */
	public static function currentUrl()
	{
		$oUri = Uri::getInstance();

		return $oUri->toString(array('scheme', 'host', 'port', 'path'));
	}


	/**
	 *
	 * @param   string  $path
	 *
	 * @return string
	 */
	private function _cleanPath($path)
	{
		$path = explode('/', preg_replace('#(/+)#', '/', $path));

		for ($i = 0, $n = count($path); $i < $n; $i++)
		{
			if ($path[$i] == '.' || $path[$i] == '..')
			{
				if (($path[$i] == '.') || ($path[$i] == '..' && $i == 1 && $path[0] == ''))
				{
					unset($path[$i]);
					$path = array_values($path);
					$i--;
					$n--;
				}
				elseif ($path[$i] == '..' && ($i > 1 || ($i == 1 && $path[0] != '')))
				{
					unset($path[$i]);
					unset($path[$i - 1]);
					$path = array_values($path);
					$i    -= 2;
					$n    -= 2;
				}
			}
		}

		return implode('/', $path);
	}

	/**
	 *
	 * @param   string  $host
	 */
	public function setHost($host)
	{
		$this->aUri['host'] = $host;
	}

	/**
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->aUri['host'];
	}

	/**
	 *
	 * @return string
	 */
	public function getQuery()
	{
		return $this->aUri['query'];
	}

	/**
	 *
	 * @return string
	 */
	public function getScheme()
	{
		return $this->aUri['scheme'];
	}

	/**
	 *
	 * @param   string  $scheme
	 */
	public function setScheme($scheme)
	{
		$this->aUri['scheme'] = $scheme;
	}

}
