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

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

use CURLFile;
use WPSpeed\Core\FileRetriever;

class CurlHelper
{
	protected $auth = array();

	public function __construct( $dlid, $secret )
	{
		$this->auth = array(
			'auth' => array(
				'dlid'   => $dlid,
				'secret' => $secret
			)
		);
	}

	public static function curlRequest( $url, $data )
	{
		$curl = curl_init();

		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $curl, CURLOPT_FAILONERROR, 1 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );
		curl_setopt( $curl, CURLOPT_CAINFO, dirname( __FILE__, 2 ) . '/libs/cacert.pem' );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 600 );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 600 );

		$response = curl_exec( $curl );
		$error    = curl_errno( $curl );
		$message  = curl_error( $curl );

		curl_close( $curl );

		if ( $error > 0 )
		{
			$curl_error = new \RuntimeException( sprintf( 'cURL returned with the following error: "%s"', $message ), $error );
			$response   = new Json( $curl_error );
		}

		return array(
			'body' => $response,
			'code' => 200
		);
	}

	public static function getMimeType( $file )
	{
		return extension_loaded( 'fileinfo' ) ? mime_content_type( $file ) : 'image/' . preg_replace( array(
				'#\.jpg#',
				'#^.*?\.(jpeg|png|gif)(?:[?\#]|$)#i'
			), array(
				'.jpeg',
				'\1'
			), strtolower( $file ) );
	}

	private function request( $data, $url )
	{
		ini_set( 'upload_max_filesize', '50M' );
		ini_set( 'post_max_size', '50M' );
		ini_set( 'max_input_time', 600 );
		ini_set( 'max_execution_time', 600 );

		$aHeaders       = array( 'Content-Type' => 'multipart/form-data' );
		$oFileRetriever = FileRetriever::getInstance( array( 'curl' ) );

		$response = $oFileRetriever->getFileContents( $url, $data, $aHeaders, '', 60 );

		if ( $oFileRetriever->response_code === 0 && $oFileRetriever->response_error !== '' )
		{
			return new Json( new \Exception( 'Response error: ' . $oFileRetriever->response_error ), 500 );
		}

		if ( is_null( $return = json_decode( $response ) ) )
		{
			return new Json( new \Exception( ( 'Improper formatted response: ' . $response ) ) );
		}

		return $return;
	}
}
