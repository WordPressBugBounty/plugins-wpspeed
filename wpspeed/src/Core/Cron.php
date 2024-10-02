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

use WPSpeed\Platform\Profiler;
use WPSpeed\Platform\Cache;
use WPSpeed\Platform\Settings;
use WPSpeed\Platform\Utility;

class Cron
{
        public $params;
        
        /**
         * 
         * @param Settings $params
         */
        public function __construct($params)
        {
                $this->params = $params;
        }

	/**
	 *
	 *
	 * @return string
	 */
        public function runCronTasks()
        {
                //$this->getAdminObject($oParser);
                $this->garbageCron();
                
                return 'CRON';
        }

        /**
         * 
         */
        public function garbageCron()
        {
                WPSPEED_DEBUG ? Profiler::start('GarbageCron') : null;
                
               // $url = Paths::ajaxUrl('garbagecron');
               // Helper::postAsync($url, $this->params, array('async' => '1'));
		Cache::gc();

                WPSPEED_DEBUG ? Profiler::stop('GarbageCron', true) : null;
        }
}
