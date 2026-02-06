<?php

namespace WPSpeed;

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
use WPSpeed\Core\Admin\Tasks;
use WPSpeed\Core\Helper;
use WPSpeed\Core\Logger;
use WPSpeed\Core\Optimize;
use WPSpeed\Core\PageCache;
use WPSpeed\Core\Exception;
use WPSpeed\Platform\Cache;
use WPSpeed\Platform\Plugin;
use WPSpeed\Platform\Settings;
use WPSpeed\Platform\Uri;
use WPSpeed\Platform\Utility;

abstract class Dispatcher {
	/**
	 *
	 * @var Settings|null
	 */
	private static $oParams;
	protected static function runActivationRoutines() {
		// Handles activation routines
		if (! file_exists ( WPSPEED_DIR . 'root.php' )) {
			try {
				$wp_filesystem = Cache::getWpFileSystem ();
			} catch ( Exception $e ) {
				return false;
			}

			if ($wp_filesystem === false) {
				return false;
			}

			$file = WPSPEED_DIR . 'root.php';
			$abspath = ABSPATH;
			$code = <<<PHPCODE
<?php	
\$WPSPEED_ROOT = '$abspath';
PHPCODE;
			$wp_filesystem->put_contents ( $file, $code, FS_CHMOD_FILE );
		}
	}
	public static function init() {
		self::$oParams = Plugin::getPluginParams ();
		self::runActivationRoutines ();
		$active_plugins = ( array ) get_option ( 'active_plugins', [ ] );
		
		if (is_admin ()) {
			require_once WPSPEED_DIR . '/src/Admin.php';
			add_action ( 'admin_menu', [
					'WPSpeed\\Admin',
					'addAdminMenu'
			] );
			add_action ( 'admin_init', [
					'WPSpeed\\Admin',
					'registerOptions'
			] );
			add_filter ( 'plugin_action_links', [
					'WPSpeed\\Admin',
					'loadActionLinks'
			], 10, 2 );
		} else {
			if(self::$oParams->get ( 'disableCoreLazyload', 0 )) {
				add_filter( 'wp_lazy_loading_enabled', '__return_false' );
			}
			
			$url_exclude = self::$oParams->get ( 'menuexcludedurl', [ ] );
			$isBackendRequest = Utility::get ( 'wpspeedrunbackend' );
			
			if (defined ( 'WP_USE_THEMES' ) && WP_USE_THEMES && $isBackendRequest != 1 && version_compare ( PHP_VERSION, '5.3.0', '>=' ) && ! defined ( 'DOING_AJAX' ) && ! defined ( 'DOING_CRON' ) && ! defined ( 'APP_REQUEST' ) && ! defined ( 'XMLRPC_REQUEST' ) && (! defined ( 'SHORTINIT' ) || (defined ( 'SHORTINIT' ) && ! SHORTINIT)) && ! Helper::findExcludes ( $url_exclude, Uri::getInstance ()->toString () )) {
				// Disable NextGen Resource Manager; incompatible with plugin
				// add_filter( 'run_ngg_resource_manager', '__return_false' );
				add_action ( 'init', [
						__CLASS__,
						'initializeCache'
				], 0 );
				
				ob_start ( [
						__CLASS__,
						'runOptimize'
				] );
			}
		}
		
		add_action ( 'plugins_loaded', [
				__CLASS__,
				'loadPluginTextDomain'
		] );
		register_uninstall_hook ( WPSPEED_FILE_PATH, [
				'WPSpeed\\Dispatcher',
				'runUninstallRoutines'
		] );
		
		if (self::$oParams->get ( 'order_plugin', '1' )) {
			add_action ( 'activated_plugin', [
					__CLASS__,
					'orderPlugin'
			] );
			add_action ( 'deactivated_plugin', [
					__CLASS__,
					'orderPlugin'
			] );
		}
		
		if (self::$oParams->get ( 'lazyload_enable', '1' )) { 
			add_action ( 'wp_head', [
					__CLASS__,
					'enqueueLazyLoad'
			] );
		}
		
		if (self::$oParams->get ( 'enable_instant_page', '0' )) {
			add_action ( 'wp_head', [
					__CLASS__,
					'enqueueInstantPage'
			] );
		}
		
		if (self::$oParams->get ( 'pro_cache_platform', '0' )) {
			add_filter ( 'wpspeed_get_page_cache_id', [
					__CLASS__,
					'getPageCacheHash'
			], 10, 2 );
		}
		
		// Ajax functions
		add_action ( 'wp_ajax_multiselect', [
				'WPSpeed\\Admin',
				'doAjaxMultiSelect'
		] );
		
		add_action ( 'wp_ajax_wpspeed_hook_pagespeed', [
				'WPSpeed\\Admin',
				'executePageSpeedTest'
		] );
		
		// Helper functions for encoding urls
		function base64_encode_url($string) {
			return strtr ( base64_encode ( $string ), '+/=', '._-' );
		}
		function base64_decode_url($string) {
			return base64_decode ( strtr ( $string, '._-', '+/=' ) );
		}
	}
	public static function initializeCache() {
		PageCache::initialize ();
	}
	public static function runUninstallRoutines() {
		delete_option ( 'wpspeed_settings' );

		try {
			Cache::deleteCache ();
		} catch ( Exception $e ) {
		}

		Tasks::cleanHtaccess ();
	}
	public static function runOptimize($sHtml) {
		global $wpdb;
		
		if (! Helper::validateHtml ( $sHtml )) {
			return $sHtml;
		}
		
		// Check pages
		// Define your custom query
		$query = "SELECT slug FROM {$wpdb->prefix}terms";
		
		// Run the query
		$results = $wpdb->get_col($query);
		$uriPath = trim(Uri::getInstance()->getPath(), '/');
		if(trim(Uri::getInstance()->getPath(), '/') && !in_array($uriPath, $results)) {
			return $sHtml;
		}

		$disable_logged_in = self::$oParams->get ( 'disable_logged_in_users', '1' );

		// Need to call Utility::isGuest after init has been called
		if ($disable_logged_in && ! Utility::isGuest ()) {
			return $sHtml;
		}

		try {
			$sOptimizedHtml = Optimize::optimize ( self::$oParams, $sHtml );
			
			if (self::$oParams->get ( 'enable_instant_page', '0' ) && self::$oParams->get ( 'instant_page_delay', 'fast' ) == 'slow') {
				$sOptimizedHtml = preg_replace('/<body/i', '<body data-instant-intensity="150"',  $sOptimizedHtml);
			}
			
			Pagecache::store ( $sOptimizedHtml );
		} catch ( Exception $e ) {
			Logger::log ( $e->getMessage (), self::$oParams );

			$sOptimizedHtml = $sHtml;
		}

		return $sOptimizedHtml;
	}
	public static function loadPluginTextDomain() {
		load_plugin_textdomain ( 'wpspeed', false, basename ( dirname ( WPSPEED_FILE_PATH ) ) . '/languages' );
	}
	public static function orderPlugin() {
		$active_plugins = ( array ) get_option ( 'active_plugins', [ ] );
		$order = [ 
				'wp-rocket/wp-rocket.php',
				'w2-total-cache/w3-total-cache.php',
				'litespeed-cache/litespeed-cache.php',
				'wp-fastest-cache/wpFastestCache.php',
				'wp-optimize/wp-optimize.php',
				'comet-cache/comet-cache.php',
				'hyper-cache/plugin.php',
				'swift-performance/performance.php',
				'wpspeed/wpspeed.php',
				'wp-super-cache/wp-cache.php',
				'akeebabackupwp/akeebabackupwp.php'
		];

		// Get the plugins in $order that are currently activated
		$order_short_list = array_intersect ( $order, $active_plugins );
		// Remove plugins in $order_short_list from list of activated plugins
		$active_plugins_slist = array_diff ( $active_plugins, $order_short_list );
		// Merge $order with $active_plugins_list
		$ordered_active_plugins = array_merge ( $order_short_list, $active_plugins_slist );

		update_option ( 'active_plugins', $ordered_active_plugins );

		return true;
	}
	public static function enqueueLazyLoad() {
		wp_register_script ( 'wpspeed-lazyloader-js', WPSPEED_URL . 'media/core/js/ls.loader.js', [ ], WPSPEED_VERSION );
		wp_enqueue_script ( 'wpspeed-lazyloader-js' );

		if (self::$oParams->get ( 'pro_lazyload_effects', '0' )) {
			wp_enqueue_style ( 'wpspeed-lazyload-css', WPSPEED_URL . 'media/core/css/ls.effects.css', [ ], WPSPEED_VERSION );

			wp_register_script ( 'wpspeed-lseffects-js', WPSPEED_URL . 'media/core/js/ls.loader.effects.js', [ 
					'wpspeed-lazyloader-js'
			], WPSPEED_VERSION );
			wp_enqueue_script ( 'wpspeed-lseffects-js' );
		}

		if (self::$oParams->get ( 'pro_lazyload_bgimages', '0' ) || self::$oParams->get ( 'pro_lazyload_audiovideo', '0' )) {
			wp_register_script ( 'wpspeed-unveilhooks-js', WPSPEED_URL . 'media/lazysizes/ls.unveilhooks.js', [ 
					'wpspeed-lazyloader-js'
			], WPSPEED_VERSION );
			wp_enqueue_script ( 'wpspeed-unveilhooks-js' );
		}

		wp_register_script ( 'wpspeed-lazyload-js', WPSPEED_URL . 'media/lazysizes/lazysizes.js', [ 
				'wpspeed-lazyloader-js'
		], WPSPEED_VERSION );
		wp_enqueue_script ( 'wpspeed-lazyload-js' );
	}
	public static function enqueueInstantPage() {
		wp_register_script ( 'wpspeed-instantpage-js', WPSPEED_URL . 'media/core/js/instantpage-5.2.0.js', [ ], WPSPEED_VERSION );
		wp_enqueue_script ( 'wpspeed-instantpage-js' );
	}
	public static function getPageCacheHash($parts) {
		if (wp_is_mobile ()) {
			$parts [] = '_MOBILE_';
		}

		return $parts;
	}
}