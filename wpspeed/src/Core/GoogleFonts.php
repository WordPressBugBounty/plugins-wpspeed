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

use WPSpeed\Core\Html\ElementObject;
use WPSpeed\Core\Html\Parser;
use WPSpeed\Core\Html\Processor;
use WPSpeed\Platform\Utility;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

class GoogleFonts
{
	public static $bGoogleFontsOptimized = false;

	public static $bFontsOptimized = false;

	public static function getPreconnect()
	{
		return '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />';
	}

	/**
	 * Optimizes an array of Google Font files. Files imported in CSS files will be returned as an array when the files are combined
	 *
	 * @param                $aGFonts
	 *
	 * @return string
	 */
	public static function optimizeFiles( $aGFonts )
	{
		$gFonts = '';

		foreach ( $aGFonts as $aGFontArray )
		{
			$sUrl   = $aGFontArray['url'];
			$sMedia = $aGFontArray['media'];

			$gFonts .= Utility::tab() . self::optimizeFile( $sUrl, $sMedia ) . Utility::lnEnd();
		}

		return $gFonts;
	}

	/**
	 * Optimizes a single Google Font file. Used in FileManager when the CombineJSCSS is being processed
	 *
	 * @param $url
	 * @param $media
	 *
	 * @return string
	 */
	public static function optimizeFile( $url, $media )
	{
		if ( strpos( $url, 'display=swap' ) === false )
		{
			$url .= '&display=swap';
		}

		if ( $media == 'none' )
		{
			$media = 'all';
		}

		$mediaAttr = $media != '' ? ' media="' . $media . '" ' : '';

		self::$bGoogleFontsOptimized = true;

		//language=HTML
		return '<link rel="preload" as="style" href="' . $url . '" ' . $mediaAttr . ' onload="this.rel=\'stylesheet\'" />';
	}

	public static function isGFontPreConnected( Processor $oProcessor )
	{
		if ( $oProcessor->oParams->get( 'pro_optimize_gfonts_enabled', '0' ) )
		{
			try
			{
				$oGFParser = new Parser();
				$oGFParser->addExclude( Parser::HTML_COMMENT() );

				$oGFElement = new ElementObject();
				$oGFElement->setNamesArray( array( 'link' ) );
				$oGFElement->addPosAttrCriteriaRegex( 'rel==[\'"]?preconnect[\'"> ]' );
				$oGFElement->addPosAttrCriteriaRegex( 'href==[\'"]?https?://fonts.gstatic.com[\'"> ]' );
				$oGFElement->bSelfClosing = true;
				$oGFParser->addElementObject( $oGFElement );

				$aMatches = $oGFParser->findMatches( $oProcessor->getHeadHtml() );

				if ( ! empty( $aMatches[0] ) )
				{
					return true;
				}
			}
			catch ( Exception $oException )
			{
				Logger::log( 'Failed searching for Gfont preconnect: ' . $oException->getMessage(), $oProcessor->oParams );
			}

			return false;
		}
	}
}