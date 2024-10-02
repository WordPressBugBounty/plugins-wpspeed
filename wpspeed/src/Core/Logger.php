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

namespace WPSpeed\Core;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

use WPSpeed\Platform\Settings;
use WPSpeed\Platform\Utility;

/**
 *
 *
 */
class Logger
{
	static protected $ip = '';

	/**
	 *
	 * @param   string    $sMessage
	 * @param   Settings  $params
	 */
	public static function log( $sMessage, Settings $params )
	{
		WPSPEED_DEBUG ? Utility::log( $sMessage, 'ERROR', 'wpspeed-log-errors.php' ) : null;
	}

	/**
	 *
	 * @param   string  $variable
	 * @param   string  $name
	 */
	public static function debug( $variable, $name = '' )
	{
		$sMessage = $name != '' ? "$name = '" . $variable . "'" : $variable;

		Utility::log( $sMessage, 'DEBUG', 'wpspeed-log-debug.php' );
	}

	/**
	 *
	 * @param   string  $sMessage
	 */
	public static function logInfo( $sMessage )
	{
		Utility::log( $sMessage, 'INFO', 'wpspeed-log-info.php' );
	}

}
