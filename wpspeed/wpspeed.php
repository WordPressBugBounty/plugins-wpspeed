<?php

/**
 * Plugin Name: WPSpeed
 * Plugin URI: https://storejextensions.org/extensions/wpspeed.html
 * Description: WPSpeed allows your website to reach the maximum score and performance on Google PageSpeed Insights, GTmetrix and Core Web Vitals. <a href="https://storejextensions.org/extensions/wpspeed.html">Upgrade to PRO</a> to unlock all pages of the website, the FREE version is limited to the home page only. You can also get our <a href="https://storejextensions.org/wordpress-speed-performance-service.html">Optimization Service</a> to boost performance of your Wordpress site thanks to our expertise team.
 * Version: 2.6.5
 * Author: JExtensions Store
 * Author URI: https://storejextensions.org
 * License: GNU/GPLv3
 * Text Domain: wpspeed
 * Domain Path: /languages
 */

/**
 * WPSpeed - Performs several front-end optimizations for fast downloads
 *
 * @package WPSpeed
 * @author JExtensions Store <info@storejextensions.org>
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license GNU/GPLv3, or later. See LICENSE file
 *         
 *          If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

// Requires PHP >= 7.2.0
if (! (PHP_VERSION_ID >= 70200)) {
	if (is_admin ()) {
		set_transient ( 'wpspeed_notices', 'WPSpeed requires a PHP version ">= 7.2.0". You are running ' . PHP_VERSION . '.', 60 * 5 );
		if ($messages = get_transient ( 'wpspeed_notices' )) {
			echo $messages;
		}
		delete_transient ( 'wpspeed_notices' );
	}
	return;
}

define ( '_WP_EXEC', '1' );
define ( '_WPSPEED_EXEC', 1 );
define ( 'WPSPEED_VERSION', '2.6.5' );

define ( 'WPSPEED_FILE_PATH', __FILE__ );
define ( 'WPSPEED_URL', plugin_dir_url ( WPSPEED_FILE_PATH ) );
define ( 'WPSPEED_DIR', plugin_dir_path ( WPSPEED_FILE_PATH ) );
define ( 'WPSPEED_CACHE_DIR', WP_CONTENT_DIR . '/cache/wpspeed/' );

require_once (WPSPEED_DIR . '/vendor/autoload.php');
require_once (WPSPEED_DIR . 'src/Dispatcher.php');

/**
 * Initialize and run plugin
 */
WPSpeed\Dispatcher::init ();
