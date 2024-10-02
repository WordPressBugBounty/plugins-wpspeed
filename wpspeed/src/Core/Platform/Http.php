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

use WPSpeed\Core\Admin\CurlHelper;
use WPSpeed\Core\Interfaces\Http as HttpInterface;

class Http implements HttpInterface
{

	protected $transport = '';

	public function __construct($transports)
	{
		if (count($transports) == 1 && $transports[0] == 'curl')
		{
			$this->transport = 'Requests_Transport_cURL';
		}
	}

	/**
	 *
	 * @staticvar null $available
	 * @return boolean
	 */
	public function available()
	{
		static $available = null;

		if (is_null($available))
		{
			global $wp_version;

			$args = array(
				'method'              => 'GET',
				'timeout'             => 10,
				'redirection'         => 5,
				'httpversion'         => '1.0',
				'user-agent'          => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url'),
				'reject_unsafe_urls'  => false,
				'blocking'            => true,
				'headers'             => array(),
				'cookies'             => array(),
				'body'                => null,
				'compress'            => false,
				'decompress'          => true,
				'sslverify'           => true,
				'sslcertificates'     => Paths::rootPath() . '/' . WPINC . '/certificates/ca-bundle.crt',
				'stream'              => false,
				'filename'            => null,
				'limit_response_size' => null,
				'ssl'                 => is_ssl()
			);

			$request_order = apply_filters('http_api_transports', array('curl', 'streams'), $args, null);

			// Loop over each transport on each HTTP request looking for one which will serve this request's needs.
			foreach ($request_order as $transport)
			{
				$class = 'WP_HTTP_' . ucfirst($transport);

				// Check to see if this transport is a possibility, calls the transport statically.
				if (!call_user_func(array($class, 'test'), $args, null))
				{
					continue;
				}

				$available = true;

				return $available;
			}

			$available = false;
		}

		return $available;
	}

	/**
	 *
	 * @param   string      $sPath
	 * @param   array|null  $aPost
	 * @param   array|null  $aHeaders
	 * @param   string      $sUserAgent
	 *
	 * @return array
	 */
	public function request($sPath, $aPost = null, $aHeaders = null, $sUserAgent = '')
	{
		$args = array('timeout' => 10);

		if ($this->transport === 'Requests_Transport_cURL'
			&& isset($aHeaders['Content-Type']) && $aHeaders['Content-Type'] == 'multipart/form-data')
		{
			return CurlHelper::curlRequest($sPath, $aPost);
		}

		if (isset($aHeaders))
		{
			$args['headers'] = $aHeaders;
		}

		if (!empty($sUserAgent))
		{
			$args['user-agent'] = $sUserAgent;
		}

		if (isset($aPost))
		{
			$args['body'] = $aPost;

			$response = wp_remote_post($sPath, $args);
		}
		else
		{
			$response = wp_remote_get($sPath, $args);
		}

		return array(
			'body' => wp_remote_retrieve_body($response),
			'code' => (int) wp_remote_retrieve_response_code($response)
		);
	}
}
