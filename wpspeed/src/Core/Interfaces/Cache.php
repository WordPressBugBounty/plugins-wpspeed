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

namespace WPSpeed\Core\Interfaces;

defined('_WPSPEED_EXEC') or die('Restricted access');

interface Cache
{
	/**
	 *
	 * @param   string    $id
	 * @param   callable  $function
	 * @param   array     $args
	 */
	public static function getCallbackCache($id, $function, $args);

	/**
	 *
	 * @param   string  $id
	 * @param   bool    $checkexpire
	 */
	public static function getCache($id, $checkexpire = false);

	/**
	 *
	 *
	 */
	public static function gc();

	/**
	 *
	 * @param   string  $content
	 * @param   string  $id
	 */
	public static function saveCache($content, $id);

	/**
	 *
	 * @param   string  $context
	 */
	public static function deleteCache($context = 'both');
}
