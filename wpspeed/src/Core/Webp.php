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

use WPSpeed\Core\Admin\Helper as AdminHelper;
use WPSpeed\Core\Html\Callbacks\LazyLoad;
use WPSpeed\Core\Html\Parser;
use WPSpeed\Platform\Paths;
use WPSpeed\Platform\Uri;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

class Webp
{
	public static function convert( LazyLoad $oLazyLoad, $aMatches )
	{
		$elementName  = @$aMatches[1] ?: false;
		$srcValue     = @$aMatches[7] ?: false;
		$cssUrlValue  = @$aMatches[9] ?: false;
		$srcsetValue  = @$aMatches[10] ?: false;
		$newFullMatch = false;

		if ( ! in_array( $elementName, [
				'img',
				'input',
				'picture',
				'iframe',
				'source',
				'video',
				'audio'
			] ) && $cssUrlValue
		)
		{
			$sWebpUrl = self::getWebpImages( $cssUrlValue );

			if ( $sWebpUrl != $cssUrlValue )
			{
				$newFullMatch = self::getNewFullMatch( $oLazyLoad, $cssUrlValue, $sWebpUrl, $aMatches );
			}
		}
		elseif ( in_array( $elementName, [ 'img', 'input' ] ) && $srcValue !== false )
		{
			$sWebpUrl = self::getWebpImages( $srcValue );

			if ( $sWebpUrl != $srcValue )
			{
				$newFullMatch = self::getNewFullMatch( $oLazyLoad, $srcValue, $sWebpUrl, $aMatches );
			}

			if ( $srcsetValue !== false )
			{
				$aUrls     = Helper::extractUrlsFromSrcset( $srcsetValue );
				$aWebpUrls = array_map( function ( $v ) {
					return self::getWebpImages( $v );
				}, $aUrls );

				if ( $aUrls != $aWebpUrls )
				{
					$sSrcsetWebpValue = str_replace( $aUrls, $aWebpUrls, $srcsetValue );
					$newFullMatch     = self::getNewFullMatch( $oLazyLoad, $aUrls, $aWebpUrls, $aMatches, $sSrcsetWebpValue );
				}
			}
		}

		if ( $newFullMatch !== false && preg_match( '#' . $oLazyLoad->sRegex . '#six', $newFullMatch, $aNewMatches ) )
		{
			$aMatches = $aNewMatches;
		}

		return $aMatches;
	}

	public static function getWebpImages( $image )
	{
		if ( strpos( $image, 'data:image' ) === 0 || ! self::canIUse() )
		{
			return $image;
		}

		$imagePath = Helper::getFilePath( $image );

		//If path not absolute path on file system return
		if ( strpos( $imagePath, Paths::rootPath() ) === false )
		{
			return $image;
		}

		$aPotentialPaths = [
			self::getWebpPathLegacy( $imagePath ),
			self::getWebpPath( $imagePath )
		];

		foreach ( $aPotentialPaths as $potentialWebpPath )
		{
			if ( @file_exists( $potentialWebpPath ) )
			{
				//replace file system path with root relative path
				$webpRootUrl = str_replace( Paths::nextGenImagesPath(), Paths::nextGenImagesPath( true ), $potentialWebpPath );

				$oUri = clone Uri::getInstance( $image );
				$oUri->setPath( $webpRootUrl );

				return ( strpos( $image, '//' ) === 0 ? '//' : '' ) . $oUri->toString();

			}
		}

		return $image;
	}

	/**
	 * Tries to determine if client supports WEBP images based on https://caniuse.com/webp
	 */
	protected static function canIUse()
	{
		$oBrowser = Browser::getInstance();
		$browser  = $oBrowser->getBrowser();

		//WEBP only supported in Safari running on MacOS 11 or higher, best to avoid.
		if ( $browser == 'Internet Explorer' || $browser == 'Safari' )
		{
			return false;
		}

		return true;
	}

	public static function getWebpPathLegacy( $originalImagePath )
	{
		$file = pathinfo( AdminHelper::contractFileNameLegacy( $originalImagePath ) );

		return Paths::nextGenImagesPath() . '/' . $file['filename'] . '.webp';
	}

	public static function getWebpPath( $originalImagePath )
	{
		$aFileParts = pathinfo( AdminHelper::contractFileName( $originalImagePath ) );

		return Paths::nextGenImagesPath() . '/' . $aFileParts['filename'] . '.webp';
	}

	/**
	 * Rewrites the match for the Lazy-load feature
	 *
	 * @param   LazyLoad      $oLazyLoad        Lazyload object
	 * @param   string|array  $urlValue         Value of original image url
	 * @param   string|array  $webpUrl          Webp image url
	 * @param   array         $aMatches         Match from the Lazy-load function
	 * @param   string        $srcsetWebpValue  Srcset value containing webp urls
	 *
	 * @return array|string|string[]
	 */
	protected static function getNewFullMatch( LazyLoad $oLazyLoad, $urlValue, $webpUrl, $aMatches, $srcsetWebpValue = '' )
	{
		//Don't process img if it contains srcset
		if ( $oLazyLoad->oParams->get( 'pro_webp_old_browsers', '0' ) && $aMatches[1] == 'img' )
		{
			$sSizesAttribute = '';
			$sWidthAttribute = @$aMatches[11] ?: '';

			if ( $sWidthAttribute != '' )
			{
				$sWidthAttribute = strpos( $sWidthAttribute, 'data-', 0 ) !== false ? $sWidthAttribute : 'data-' . $sWidthAttribute;
			}

			$sHeightAttribute = @$aMatches[14] ?: '';

			if ( $sHeightAttribute != '' )
			{
				$sHeightAttribute = strpos( $sHeightAttribute, 'data-', 0 ) !== false ? $sHeightAttribute : 'data-' . $sHeightAttribute;
			}

			if ( $srcsetWebpValue !== '' )
			{//If the image has a srcset value we want to put that on the <source/> element instead.
				$sNewSrcsetWebpValue = $srcsetWebpValue;
				//If there's a sizes attribute on the img element we want to use it
				if ( preg_match( '#' . Parser::HTML_ATTRIBUTE_CP( 'sizes' ) . '#i', $aMatches[0], $aSizesMatches ) )
				{
					$sSizesAttribute = $aSizesMatches[0];
				}

			}
			else
			{
				$sNewSrcsetWebpValue = $webpUrl;
			}

			$sNewFullMatch = <<<HTML
<picture>
    <source srcset="$sNewSrcsetWebpValue" $sWidthAttribute $sHeightAttribute $sSizesAttribute type="image/webp">
    $aMatches[0]
</picture>
HTML;
		}
		else
		{
			$sNewFullMatch = str_replace( $urlValue, $webpUrl, $aMatches[0] );
		}

		return $sNewFullMatch;
	}
}