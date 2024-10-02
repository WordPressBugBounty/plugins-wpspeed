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

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );


abstract class CallbackBase
{
	public $oParams;

	protected $aUrl;

	public function __construct( $oParams, $aUrl )
	{
		$this->oParams = $oParams;
		$this->aUrl    = $aUrl;
	}

	abstract function processMatches( $aMatches, $sContext );
}
