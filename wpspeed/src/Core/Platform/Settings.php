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

use WPSpeed\Core\Interfaces\Settings as SettingsInterface;

defined( '_WP_EXEC' ) or die( 'Restricted access' );

class Settings implements SettingsInterface
{
	private $params = [];

	/**
	 *
	 * @param   array  $params
	 */
	public function __construct( $params )
	{
		if ( ! is_array( $params ) )
		{
			$params = [];
		}

		$this->params = $params;

		if ( ! defined( 'WPSPEED_DEBUG' ) )
		{
			define( 'WPSPEED_DEBUG', ( $this->get( 'debug', 0 ) ) );
		}
	}

	/**
	 *
	 * @param   string  $param
	 * @param   mixed   $default
	 *
	 * @return mixed
	 */
	public function get( $param, $default = null )
	{
		if ( ! isset( $this->params[$param] ) )
		{
			return $default;
		}

		return $this->params[$param];
	}

	/**
	 *
	 * @param   array  $params
	 *
	 * @return Settings
	 */
	public static function getInstance( $params )
	{
		return new Settings( $params );
	}

	/**
	 *
	 * @param   string  $param
	 * @param   mixed   $value
	 */
	public function set( $param, $value )
	{
		$this->params[$param] = $value;
	}

	/**
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->params;
	}

	/**
	 * Delete a value from the settings array
	 *
	 * @param   string  $param  The parameter value to be deleted
	 *
	 * @return   null
	 */
	public function remove( $param )
	{
		unset( $this->params[$param] );

		return;
	}

	public function toArray()
	{
		return $this->params;
	}
}
