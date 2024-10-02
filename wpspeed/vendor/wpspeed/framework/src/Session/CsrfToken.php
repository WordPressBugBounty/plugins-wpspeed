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
 * Cross-site request forgery token tools.
 */
class CsrfToken
{
	/**
	 *
	 * Session segment for values in this class.
	 *
	 * @var Segment
	 *
	 */
	protected $segment;

	/**
	 *
	 * Constructor.
	 *
	 * @param Segment          $segment A segment for values in this class.
	 *
	 */
	public function __construct(Segment $segment)
	{
		$this->segment = $segment;

		if (!isset($this->segment->value))
		{
			$this->regenerateValue();
		}
	}

	/**
	 *
	 * Checks whether an incoming CSRF token value is valid.
	 *
	 * @param string $value The incoming token value.
	 *
	 * @return bool True if valid, false if not.
	 *
	 */
	public function isValid($value)
	{
		return $value === $this->getValue();
	}

	/**
	 *
	 * Gets the value of the outgoing CSRF token.
	 *
	 * @return string
	 *
	 */
	public function getValue()
	{
		return $this->segment->value;
	}

	/**
	 *
	 * Regenerates the value of the outgoing CSRF token.
	 *
	 * @return void
	 *
	 */
	public function regenerateValue()
	{
		$this->segment->value = hash('sha512', random_bytes(32));
	}
}
