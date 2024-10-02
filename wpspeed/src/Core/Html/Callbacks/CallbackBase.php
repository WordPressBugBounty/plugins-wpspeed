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

namespace WPSpeed\Core\Html\Callbacks;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

use WPSpeed\Core\Html\Processor;
use WPSpeed\Platform\Settings;

abstract class CallbackBase
{
	/** @var Settings        Plugin parameters */
	public $oParams;
	/** @var $sRegex        Regex used to process HTML */
	public $sRegex;
	/** @var array          Array of excludes parameters */
	protected $aExcludes;
	/** @var Processor      Processor object */
	protected $oProcessor;

	public function __construct( $oProcessor )
	{
		$this->oProcessor = $oProcessor;
		$this->oParams    = $oProcessor->oParams;
	}

	abstract function processMatches( $aMatches );

	protected function getMValue( $sValue )
	{
		return ! empty( $sValue ) ? $sValue : false;
	}
}
