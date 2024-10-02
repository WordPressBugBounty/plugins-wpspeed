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

namespace WPSpeed\Platform;

use WPSpeed\Core\Interfaces\Excludes as ExcludesInterface;

defined('_WP_EXEC') or die('Restricted access');

class Excludes implements ExcludesInterface
{

	/**
	 * @param   string  $type
	 * @param   string  $section
	 *
	 * @return array|void
	 */
        public static function body($type, $section = 'file')
        {
                if ($type == 'js')
                {
                        if ($section == 'script')
                        {
                                return array();
                        }
                        else
                        {
                                return array('js.stripe.com');
                        }
                }

                if ($type == 'css')
                {
                        return array();
                }

                return;
        }

	/**
	 *
	 * @return string
	 */
        public static function extensions()
        {
                return Paths::rewriteBaseFolder();
        }

	/**
	 * @param   string  $type
	 * @param   string  $section
	 *
	 * @return array|void
	 */
        public static function head($type, $section = 'file')
        {
                if ($type == 'js')
                {
                        if ($section == 'script')
                        {
                                return array();
                        }
                        else
                        {
                                return array('js.stripe.com');
                        }
                }

                if ($type == 'css')
                {
                        return array();
                }

                return;
        }

	/**
	 * @param   string  $url
	 *
	 * @return boolean
	 */
        public static function editors($url)
        {
                return (preg_match('#/editors/#i', $url));
        }
}
