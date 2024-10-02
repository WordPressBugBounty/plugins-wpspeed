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

namespace WPSpeed\Core\Admin\Ajax;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

abstract class Ajax
{
	private function __construct()
	{
		ini_set('pcre.backtrack_limit', 1000000);
		ini_set('pcre.recursion_limit', 1000000);

		if (version_compare(PHP_VERSION, '7.0.0', '>='))
		{
			ini_set('pcre.jit', 0);
		}
	}

	public static function getInstance($sClass)
	{
		$sFullClass = 'WPSpeed\\Core\\Admin\\Ajax\\' . $sClass;

		return new $sFullClass();
	}

	abstract function run();
}