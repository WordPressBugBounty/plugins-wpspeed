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

namespace WPSpeed\Core\Html\Callbacks;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

use WPSpeed\Core\Helper;
use WPSpeed\Core\Html\Processor;
use WPSpeed\Core\Url;
use WPSpeed\Core\Css\Parser as CssParser;

class Cdn extends CallbackBase
{
	protected $sContext = 'default';

	protected $sDir = '';

	protected $sSearchRegex = '';

	protected $sLocalhost = '';


	function processMatches( $aMatches )
	{
		if ( empty ( $aMatches[0] ) )
		{
			return $aMatches[0];
		}

		switch ( $this->sContext )
		{
			case ( 'cssurl' ):
				//This would be either a <style> element, or an HTML element with a style attribute, containing one or more CSS urls
				$styleOrElement = $aMatches[0];
				$sRegex         = 'url\([\'"]?(' . $this->sSearchRegex . CssParser::CSS_URL_VALUE() . ')([\'"]?\))';
				//Find all css urls in content
				preg_match_all( '#' . $sRegex . '#i', $styleOrElement, $aCssUrls, PREG_SET_ORDER );

				//Prevent modifying the same url multiple times
				$aCssUrls = array_unique( $aCssUrls, SORT_REGULAR );

				foreach ( $aCssUrls as $aCssUrlMatch )
				{
					$cssUrl       = @$aCssUrlMatch[0] ?: false;
					$urlWithQuery = @$aCssUrlMatch[1] ?: false;
					$url          = @$aCssUrlMatch[2];

					if ( $cssUrl !== false && $url !== false )
					{
						$relRootUrl = $this->fixRelPath( $url );
						$cdnUrl     = Helper::cookieLessDomain( $this->oParams, $relRootUrl, $url );

						//First replace the url in the css url
						$cdnCssUrl = str_replace( $urlWithQuery, $cdnUrl, $cssUrl );
						//Replace the css url in content
						$styleOrElement = str_replace( $cssUrl, $cdnCssUrl, $styleOrElement );
					}
				}

				return $styleOrElement;

			case( 'srcset' ):

				$fullMatch       = @$aMatches[0] ?: false;
				$srcSetAttr      = @$aMatches[2] ?: false;
				$srcSetValue     = @$aMatches[4] ?: false;
				$dataSrcSetAttr  = @$aMatches[5] ?: @$aMatches[8] ?: false;
				$dataSrcSetValue = @$aMatches[7] ?: @$aMatches[10] ?: false;
				$returnMatch     = $fullMatch;

				if ( $srcSetAttr !== false && $srcSetValue !== false )
				{
					$returnMatch = $this->handleSrcSetValues( $srcSetAttr, $srcSetValue, $returnMatch );
				}

				if ( $dataSrcSetAttr !== false && $dataSrcSetValue !== false )
				{
					$returnMatch = $this->handleSrcSetValues( $dataSrcSetAttr, $dataSrcSetValue, $returnMatch );
				}

				return $returnMatch;

			default:

				$fullMatch             = @$aMatches[0] ?: false;
				$hrefSrcAttr           = @$aMatches[3] ?: false;
				$hrefSrcValue          = @$aMatches[5] ?: false;
				$hrefSrcValueWithQuery = @$aMatches[6] ?: false;
				$dataSrcAttr           = @$aMatches[7] ?: @$aMatches[11] ?: false;
				$dataSrcValue          = @$aMatches[9] ?: @$aMatches[13] ?: false;
				$dataSrcValueWithQuery = @$aMatches[10] ?: @$aMatches[14] ?: false;
				$returnMatch           = $fullMatch;

				if ( $hrefSrcAttr !== false && $hrefSrcValue !== false )
				{
					$rootRelSrcValue = $this->fixRelPath( $hrefSrcValue );
					$cdnSrcValue     = Helper::cookieLessDomain( $this->oParams, $rootRelSrcValue, $hrefSrcValue );

					//First replace the url in the src attribute
					$cdnSrcAttr = str_replace( $hrefSrcValueWithQuery, $cdnSrcValue, $hrefSrcAttr );
					//Then replace the original attribute with the attribute containing CDN url
					$returnMatch = str_replace( $hrefSrcAttr, $cdnSrcAttr, $returnMatch );
				}

				if ( $dataSrcAttr !== false && $dataSrcValue !== false )
				{
					$rootRelDataSrcValue = $this->fixRelPath( $dataSrcValue );
					$cdnDataSrcValue     = Helper::cookieLessDomain( $this->oParams, $rootRelDataSrcValue, $dataSrcValue );

					//First replace the url in the data-src attribute
					$cdnDataSrcAttr = str_replace( $dataSrcValueWithQuery, $cdnDataSrcValue, $dataSrcAttr );
					//Then replace the original attribute with the attribute containing CDN url
					$returnMatch = str_replace( $dataSrcAttr, $cdnDataSrcAttr, $returnMatch );
				}

				return $returnMatch;
		}
	}

	protected function handleSrcSetValues( $attribute, $url, $returnMatch )
	{
		$cdnSrcSetAttr = $attribute;
		$regex         = '(?:^|,)\s*+(' . $this->sSearchRegex . '([^,]++))';
		preg_match_all( '#' . $regex . '#i', $url, $aUrls, PREG_SET_ORDER );

		foreach ( $aUrls as $aUrlMatch )
		{
			if ( ! empty( $aUrlMatch[0] ) )
			{
				$url           = $aUrlMatch[2];
				$rootRelUrl    = $this->fixRelPath( $url );
				$cdnUrl        = Helper::cookieLessDomain( $this->oParams, $rootRelUrl, $url );
				$cdnSrcSetAttr = str_replace( $url, $cdnUrl, $cdnSrcSetAttr );
			}
		}

		$returnMatch = str_replace( $attribute, $cdnSrcSetAttr, $returnMatch );

		return $returnMatch;
	}

	protected function fixRelPath( $path )
	{
		$sRegex = '^(?>https?:)?//' . $this->sLocalhost;
		$path   = preg_replace( '#' . $sRegex . '#i', '', trim( $path ) );

		if ( substr( $path, 0, 1 ) != '/' )
		{
			$path = '/' . $this->sDir . '/' . $path;
		}

		return $path;
	}

	public function setDir( $sDir )
	{
		$this->sDir = $sDir;
	}

	public function setLocalhost( $sLocalhost )
	{
		$this->sLocalhost = $sLocalhost;
	}

	public function setContext( $sContext )
	{
		$this->sContext = $sContext;
	}

	public function setSearchRegex( $sSearchRegex )
	{
		$this->sSearchRegex = $sSearchRegex;
	}
}
