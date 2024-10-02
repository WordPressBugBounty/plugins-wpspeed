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

namespace WPSpeed\Admin;

use WPSFramework\Container\Container as WPSpeedContainer;

class aContainer extends WPSpeedContainer
{
	public function __construct( array $values = array() )
	{
		

		parent::__construct( $values );
	}
}