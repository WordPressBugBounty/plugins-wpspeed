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

namespace WPSpeed\Core\Admin;

use WPSpeed\Platform\FileSystem;
use WPSpeed\Platform\Paths;
use WPSpeed\Platform\Uri;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

class Helper
{

	public static function copyImage( $src, $dest )
	{
		$dest_dir = dirname( $dest );

		if ( ! @file_exists( $dest_dir ) )
		{
			FileSystem::createFolder( $dest_dir );
		}

		if ( ! ini_get( 'allow_url_fopen' ) )
		{
			if ( ! preg_match( '#^http#i', $src ) )
			{
				return copy( $src, $dest );
			}

			//Open file handler.
			$fp = fopen( $dest, 'wb' );

			//If $fp is FALSE, something went wrong.
			if ( $fp === false )
			{
				return false;
			}

			//Create a cURL handle.
			$ch = curl_init( $src );

			//Pass our file handle to cURL.
			curl_setopt( $ch, CURLOPT_FILE, $fp );

			curl_setopt( $ch, CURLOPT_TIMEOUT, 20 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $ch, CURLOPT_CAINFO, dirname( __FILE__, 2 ) . '/libs/cacert.pem' );

			//Execute the request.
			curl_exec( $ch );

			//If there was an error, throw an Exception
			if ( $errno = curl_errno( $ch ) )
			{
				return false;
			}

			//Get the HTTP status code.
			$statusCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

			//Close the cURL handler.
			if (is_resource($ch)) {
				curl_close ($ch);
			}

			if ( $statusCode == 200 )
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		$context = stream_context_create( array(
			'ssl' => array(
				'verify_peer' => true,
				'cafile'      => dirname( __FILE__, 2 ) . '/libs/cacert.pem'
			)
		) );

		$src_stream = fopen( $src, 'rb', false, $context );

		if ( $src_stream === false )
		{
			return false;
		}

		$dest_stream = fopen( $dest, 'wb' );

		return stream_copy_to_stream( $src_stream, $dest_stream );
	}

	/**
	 * @param $original_image
	 *
	 * @return string
	 * @deprecated
	 */
	public static function getWebpPath( $original_image )
	{
		$file = pathinfo( self::contractFileNameLegacy( $original_image ) );

		return Paths::nextGenImagesPath() . '/' . $file['filename'] . '.webp';
	}

	/**
	 * @param $sFile
	 *
	 * @return array|string|string[]
	 * @deprecated
	 */
	public static function contractFileNameLegacy( $sFile )
	{
		return str_replace( array( Paths::rootPath() . '/', '_', '/' ), array(
			ltrim( Uri::base( true ) . '/', '/' ),
			'__',
			'_'
		), $sFile );
	}


	/**
	 * Returns the 'contracted' path of the file relative to the Uri base as opposed to the web root as in legacy
	 *
	 * @param $filePath
	 *
	 * @return array|string|string[]
	 */
	public static function contractFileName( $filePath )
	{
		return str_replace( [ Paths::rootPath() . '/', '_', '/' ],
			[ '', '__', '_' ],
			$filePath );
	}
}