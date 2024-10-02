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

namespace WPSpeed\Core\Css\Callbacks;

use WPSpeed\Core\Css\Parser;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );


class FormatCss extends CallbackBase
{
	public $sValidCssRules;

	function processMatches( $aMatches, $sContext )
	{
		if ( isset ( $aMatches[7] ) && !preg_match( '#' . $this->sValidCssRules . '#i', $aMatches[7] ) )
		{
			return '';
		}

		return $aMatches[0];
	}
}
