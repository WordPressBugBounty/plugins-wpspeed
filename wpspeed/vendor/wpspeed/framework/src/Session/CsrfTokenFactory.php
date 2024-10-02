<?php
/**
 * @package   WPSpeed
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license   GNU GPL version 3 or later
 */

/**
 * The Session package is based on the Session package in Aura for PHP. Please consult the LICENSE file in the
 * WPSFramework\Session package for copyright and license information.
 */

namespace WPSFramework\Session;

/**
 *
 * A factory to create CSRF token objects.
 */
class CsrfTokenFactory
{
	/**
	 *
	 * Creates a CsrfToken object.
	 *
	 * @param Manager $manager The session manager.
	 *
	 * @return CsrfToken
	 *
	 */
	public function newInstance(Manager $manager)
	{
		$segment = $manager->newSegment('WPSFramework\Session\CsrfToken');

		return new CsrfToken($segment);
	}
}
