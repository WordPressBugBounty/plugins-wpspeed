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

use WPSpeed\Core\Interfaces\Plugin as PluginInterface;

defined( '_WP_EXEC' ) or die( 'Restricted access' );

class Plugin implements PluginInterface
{

	protected static $plugin = null;

	/**
	 *
	 * @return void
	 */
	public static function getPluginId()
	{
		return;
	}

	/**
	 *
	 * @return void
	 */
	public static function getPlugin()
	{
		return;
	}

	/**
	 *
	 * @param   Settings  $params
	 */
	public static function saveSettings( Settings $params )
	{
		$options = $params->getOptions();

		update_option( 'wpspeed_settings', $options );
	}

	/**
	 *
	 * @return Settings
	 */
	public static function getPluginParams()
	{
		static $params = null;

		if ( is_null( $params ) )
		{
			$options = get_option( 'wpspeed_settings' );
			$params  = Settings::getInstance( $options );
		}

		return $params;
	}

}
