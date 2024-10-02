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

namespace WPSpeed\Core;

defined('_WPSPEED_EXEC') or die('Restricted access');

class Exception extends \Exception
{

        public function __construct($message = null, $code = 0)
        {
                if (!$message)
                {
			$this->message = 'Unknown Exception';
                }

		$error_message = get_class($this) . " '{$message}' in {$this->getFile()}({$this->getLine()})\n"
		       . "{$this->getTraceAsString()}";	

                parent::__construct($error_message, $code);
        }
}

