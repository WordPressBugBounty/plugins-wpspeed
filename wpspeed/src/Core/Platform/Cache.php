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

defined( '_WP_EXEC' ) or die( 'Restricted access' );

use WPSpeed\Core\Exception;
use WPSpeed\Platform\Uri;
use WPSpeed\Core\Interfaces\Cache as CacheInterface;

class Cache implements CacheInterface
{
	protected static $wp_filesystem;

	/**
	 *
	 * @param   string    $id
	 * @param   callable  $function
	 * @param   array     $args
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getCallbackCache( $id, $function, $args )
	{
		$wp_filesystem = self::getWpFileSystem();

		if ( $wp_filesystem === false )
		{
			return false;
		}

		$file = self::_getFileName( $id );

		if ( ! self::getCache( $id, true ) )
		{
			$contents = call_user_func_array( $function, $args );

			return self::saveCache( $contents, $id );
		}

		return self::_getCacheFile( $file, $wp_filesystem );
	}

	/**
	 *
	 * @return \WP_Filesystem_Base|false
	 * @throws Exception
	 */
	public static function getWpFileSystem()
	{
		if ( ! isset( self::$wp_filesystem ) )
		{
			// Set the permission constants if not already set.
			if ( ! defined( 'FS_CHMOD_DIR' ) )
			{
				define( 'FS_CHMOD_DIR', ( fileperms( ABSPATH ) & 0777 | 0755 ) );
			}
			if ( ! defined( 'FS_CHMOD_FILE' ) )
			{
				define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
			}

			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

			self::$wp_filesystem = new \WP_Filesystem_Direct( true );

		}

		return self::$wp_filesystem;
	}

	/**
	 *
	 * @param   string  $id
	 * @param   bool    $check_expire
	 * @param   bool    $page_cache
	 *
	 * @return string|false
	 * @throws Exception
	 */
	public static function getCache( $id, $check_expire = false, $page_cache = false )
	{
		$wp_filesystem = self::getWpFileSystem();

		if ( $wp_filesystem === false )
		{
			return false;
		}

		$file = self::_getFileName( $id, $page_cache );

		if ( ! $wp_filesystem->exists( $file ) )
		{
			return false;
		}

		if ( $check_expire && time() > $wp_filesystem->mtime( $file ) + self::getLifetime( $page_cache ) )
		{
			return false;
		}

		return self::_getCacheFile( $file, $wp_filesystem );
	}

	/**
	 *
	 *
	 * @param   string  $content
	 * @param   string  $id
	 * @param   bool    $page_cache
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function saveCache( $content, $id, $page_cache = false )
	{
		$params = Plugin::getPluginParams();
		
		$wp_filesystem = self::getWpFileSystem();
		//required for compatiblity with Hide My WP Ghost https://wordpress.org/support/topic/compatibility-with-hide-my-wp-ghost/
		$content = apply_filters( 'wpspeed_save_content', $content );

		$file_contents = serialize( $content );
		
		if($params->get('base64encode', 0)) {
			$file_contents = base64_encode($file_contents);
		}
		
		$file          = self::_getFileName( $id, $page_cache );

		self::initializeCache();

		if ( $wp_filesystem->put_contents( $file, $file_contents, FS_CHMOD_FILE ) )
		{
			return $content;
		}
		else
		{
			throw new Exception( __( 'Error writing files to cache' ) );
		}
	}

	/**
	 *
	 * @throws Exception
	 */
	public static function initializeCache()
	{
		$wp_filesystem = self::getWpFileSystem();

		if ( $wp_filesystem !== false )
		{
			$index_contents = '<html lang=""><body></body></html>';

			if ( ! $wp_filesystem->exists( WPSPEED_CACHE_DIR ) )
			{
				if ( ! $wp_filesystem->exists( $wp_filesystem->wp_content_dir() . 'cache' ) )
				{
					$wp_filesystem->mkdir( $wp_filesystem->wp_content_dir() . 'cache', FS_CHMOD_DIR );
				}

				$wp_filesystem->mkdir( WPSPEED_CACHE_DIR, FS_CHMOD_DIR );
				$wp_filesystem->put_contents( WPSPEED_CACHE_DIR . 'index.html', $index_contents, FS_CHMOD_FILE );
			}

			if ( ! $wp_filesystem->exists( WPSPEED_CACHE_DIR . 'page' ) )
			{
				$wp_filesystem->mkdir( WPSPEED_CACHE_DIR . 'page', FS_CHMOD_DIR );
				$wp_filesystem->put_contents( WPSPEED_CACHE_DIR . 'page/index.html', $index_contents, FS_CHMOD_FILE );
			}
		}
	}

	/**
	 * @param $value
	 * @param $form_post
	 * @param $type
	 * @param $error
	 * @param $context
	 * @param $extra_fields
	 * @param $allow_relaxed_file_ownership
	 *
	 * @return bool|mixed|void
	 */
	public static function requestFilesystemCredentials( $value, $form_post, $type, $error, $context, $extra_fields, $allow_relaxed_file_ownership )
	{
		$method = get_filesystem_method( array(), $context, $allow_relaxed_file_ownership );

		if ( $method == 'direct' )
		{
			return true;
		}

		if ( ! function_exists( 'wp_generate_password' ) )
		{
			include_once Paths::rootPath() . '/wp-includes/pluggable.php';
		}

		$credentials = get_option( 'ftp_credentials', array( 'hostname' => '', 'username' => '' ) );

		$credentials['hostname'] = defined( 'FTP_HOST' ) ? FTP_HOST : $credentials['hostname'];
		$credentials['username'] = defined( 'FTP_USER' ) ? FTP_USER : $credentials['username'];
		$credentials['password'] = defined( 'FTP_PASS' ) ? FTP_PASS : '';

		if ( $method == 'ssh2' )
		{
			// Check to see if we are setting the public/private keys for ssh
			$credentials['public_key']  = defined( 'FTP_PUBKEY' ) ? FTP_PUBKEY : '';
			$credentials['private_key'] = defined( 'FTP_PRIKEY' ) ? FTP_PRIKEY : '';
		}

		if ( in_array( '', $credentials ) )
		{
			return $value;
		}

		return $credentials;
	}

	/**
	 *
	 * @throws Exception
	 */
	public static function gc()
	{
		$wp_filesystem = self::getWpFileSystem();

		if ( $wp_filesystem === false )
		{
			return false;
		}

		$result = true;

		//Delete any page cache
		$result |= self::deleteCache( 'page' );

		$files = FileSystem::lsFiles( rtrim( WPSPEED_CACHE_DIR, '/\\' ), '.', true );
		$now   = time();

		foreach ( $files as $file )
		{
			$time = $wp_filesystem->mtime( $file );

			if ( ( $time + self::getLifetime() ) < $now || empty( $time ) )
			{
				$result |= $wp_filesystem->delete( $file );
			}
		}

		return $result;
	}

	/**
	 *
	 * @param   string  $context
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function deleteCache( $context = 'both' )
	{
		//Purge LiteSpeed cache
		if ( $context != 'plugin' && class_exists( 'LiteSpeed_Cache_API' ) )
		{
			\LiteSpeed_Cache_API::purge_all();
		}

		$wp_filesystem = self::getWpFileSystem();

		if ( $wp_filesystem === false )
		{
			return false;
		}

		if ( ! $wp_filesystem->exists( WPSPEED_CACHE_DIR ) )
		{
			//most likely already deleted so just return true as not to cause any alarm
			return true;
		}
		if ( $context == 'plugin' )
		{
			$result = true;
			$result |= (bool) $wp_filesystem->rmdir( WPSPEED_CACHE_DIR . 'js', true );
			$result |= (bool) $wp_filesystem->rmdir( WPSPEED_CACHE_DIR . 'css', true );

			return $result;
		}

		$cache_dir = dirname( WPSPEED_CACHE_DIR );
		//Get list of all folders in the cache directory (.../wp-content/cache/)
		$cache_dir_list = $wp_filesystem->dirlist( $cache_dir, false, false );

		foreach ( $cache_dir_list as $entry )
		{
			//Skip the cache if we're only deleting page cache
			if ( $context == 'page' && $entry['name'] == 'wpspeed' )
			{
				$wp_filesystem->rmdir( $cache_dir . '/wpspeed/page', true );

				continue;
			}


			if ( $entry['type'] == 'd' )
			{
				// Delete each cache folder
				if ( ! $wp_filesystem->rmdir( $cache_dir . DIRECTORY_SEPARATOR . $entry['name'], true ) )
				{
					return false;
				}
			}
		}

		if(Plugin::getPluginParams()->get('clear_server_cache', 0)) {
			self::purgeServerCache(Uri::currentUrl());
		}
		
		return true;
	}

	public static function purgeServerCache($url) {
		// Parse the URL
		$parsed_url = parse_url($url);
		
		if ($parsed_url !== false && isset($parsed_url['scheme'], $parsed_url['host'])) {
			// Reconstruct the URL with only the scheme and host (domain)
			$domain = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		} else {
			return false;
		}
		
		$urlFormatted = self::getUrl ( $domain );
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_USERAGENT, 'joomla_purgeCache' );
		curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, "PURGE" );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, array (
				'Host: ' . $urlFormatted ['hostname']
		) );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 200);
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 200); //timeout in seconds
		curl_setopt ( $curl, CURLOPT_URL, $urlFormatted ['url'] );
		$response = curl_exec ( $curl );
		curl_close ( $curl );
		return true;
	}
	
	protected static function getUrl($url) {
		$parsedUrl = parse_url ( $url );
		$hostname = $parsedUrl ['host'];
		$address = gethostbyname ( $hostname );
		$url = $parsedUrl ['scheme'] . '://' . $address;
		if (isset($parsedUrl ['port']) && $parsedUrl ['port']) {
			$url .= ':' . $parsedUrl ['port'];
		}
		if (isset($parsedUrl ['path']) && $parsedUrl ['path']) {
			$url .= $parsedUrl ['path'];
		}
		if (isset($parsedUrl ['query']) && $parsedUrl ['query']) {
			$url .= '?' . $parsedUrl ['query'];
		}
		if (isset($parsedUrl ['fragment']) && $parsedUrl ['fragment']) {
			$url .= '#' . $parsedUrl ['fragment'];
		}
		return array (
				'url' => $url,
				'hostname' => $hostname
		);
	}
	
	protected static function getLifetime( $page_cache = false )
	{
		static $lifetime, $page_cache_lifetime;

		if ( $page_cache )
		{
			if ( ! $page_cache_lifetime )
			{
				$params              = Plugin::getPluginParams();
				$page_cache_lifetime = $params->get( 'page_cache_lifetime', '600' );
			}

			return (int) $page_cache_lifetime;
		}

		if ( ! $lifetime )
		{
			$params = Plugin::getPluginParams();

			$lifetime = $params->get( 'cache_lifetime', '900' );
		}

		return (int) $lifetime;
	}

	/**
	 *
	 * @param   string  $id
	 * @param   bool    $page_cache
	 *
	 * @return string
	 */
	private static function _getFileName( $id, $page_cache = false )
	{
		return WPSPEED_CACHE_DIR . ( $page_cache ? 'page/' : '' ) . md5( NONCE_SALT . $id ) . '.wpc';
	}

	/**
	 *
	 * @param   string               $file
	 * @param   \WP_Filesystem_Base  $wp_filesystem
	 *
	 * @return string
	 */
	private static function _getCacheFile( $file, $wp_filesystem )
	{
		$params = Plugin::getPluginParams();
		
		$content = $wp_filesystem->get_contents( $file );

		if($params->get('base64encode', 0)) {
			$content = base64_decode($content);
		}
		
		return unserialize( $content );
	}
}
