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

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

class Http2Excludes
{
	public static function addHttp2Includes(Http2 $oHttp2)
	{
		$aIncludeFiles = $oHttp2->oParams->get( 'pro_http2_include', array() );

		if ( empty( $aIncludeFiles ) )
		{
			return false;
		}

		foreach ( $aIncludeFiles as $sIncludeFile )
		{
			preg_match( "#\.\K(?:js|css|webp|gif|jpe?g|png|woff2?|ttf)(?=$|[\#?])#i", $sIncludeFile, $aM );

			switch ( $aM[0] )
			{
				case  'js':
					$sType = 'script';
					break;
				case 'css':
					$sType = 'style';
					break;
				case 'woff':
				case 'woff2':
				case 'ttf':
					$sType = 'font';
					break;
				case 'webp':
				case 'gif':
				case 'jpg':
				case 'jpeg':
				case 'png':
					$sType = 'image';
					break;
				default:
					$sType = '';
					break;
			}

			if ( $sType != '' )
			{
				$oHttp2->addHttp2Preload( $sIncludeFile, $sType );
			}
		}

		return true;
	}

	public static function findHttp2Excludes(Http2 $oHttp2, $sUrl, $bDeferred)
	{
		if ( Helper::findExcludes( $oHttp2->oParams->get( 'pro_http2_exclude', array() ), $sUrl ) )
		{
			return true;
		}

		//If file is marked deferred when 'Exclude deferred' is enabled, return
		if ( $oHttp2->oParams->get( 'pro_http2_exclude_deferred', '1' ) && $bDeferred )
		{
			return true;
		}

		return false;
	}
}