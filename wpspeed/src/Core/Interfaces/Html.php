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

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

interface Html
{
	/**
	 * Returns HTML of the front page
	 *
	 * @return string
	 */
	public function getHomePageHtml();

	public function getMainMenuItemsHtmls();
}
