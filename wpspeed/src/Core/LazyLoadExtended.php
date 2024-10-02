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

use WPSpeed\Core\Css\Parser as CssParser;
use WPSpeed\Core\Html\ElementObject;
use WPSpeed\Core\Html\Parser;
use WPSpeed\Core\Html\Processor;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

class LazyLoadExtended
{
	public static function lazyLoadAudioVideo( $aMatches, $sReturn )
	{
		$sPosterAttribute = @$aMatches[5] ?: false;

		return str_replace( $sPosterAttribute, 'data-' . $sPosterAttribute, $sReturn );
	}

	public static function negateAudioVideoPreload( $aMatches, $sReturn )
	{
		$sElementName       = @$aMatches[1] ?: false;
		$sPreloadAttribute  = @$aMatches[8] ?: false;
		$sPreloadDelimiter  = @$aMatches[9] ?: false;
		$sAutoLoadAttribute = @$aMatches[11] ?: false;

		if ( $sPreloadAttribute !== false )
		{
			$sNewPreloadAttribute = 'preload=' . $sPreloadDelimiter . 'none' . $sPreloadDelimiter;
			$sReturn              = str_replace( $sPreloadAttribute, $sNewPreloadAttribute, $sReturn );
		}
		else
		{
			$sReturn = str_replace( '<' . $sElementName, '<' . $sElementName . ' preload="none"', $sReturn );
		}

		if ( $sAutoLoadAttribute !== false )
		{
			$sReturn = str_replace( $sAutoLoadAttribute, '', $sReturn );
		}

		return $sReturn;
	}

	public static function lazyLoadBgImages( $aMatches, $sReturn )
	{
		$sStyleAttribute = @$aMatches[5] ?: false;
		$sStyleDelimiter = @$aMatches[6] ?: false;
		$sBgDeclaration  = @$aMatches[7] ?: false;
		$sCssUrl         = @$aMatches[8] ?: false;
		$sCssUrlValue    = @$aMatches[9] ?: false;

		$sNewStyleAttribute = str_replace( $sCssUrl, '', $sStyleAttribute );

		if ( strpos( $sBgDeclaration, 'background-image' ) !== false )
		{
			$sNewStyleAttribute = str_replace( $sBgDeclaration, 'background', $sNewStyleAttribute );
		}

		$sNewStyleAttribute = 'data-bg=' . $sStyleDelimiter . $sCssUrlValue . $sStyleDelimiter . ' ' . $sNewStyleAttribute;

		return str_replace( $sStyleAttribute, $sNewStyleAttribute, $sReturn );
	}

	public static function setupLazyLoadExtended( Processor $oProcessor, Parser $oParser, $bDeferred )
	{
		if ( $bDeferred && $oProcessor->oParams->get( 'pro_lazyload_iframe', '0' ) )
		{
			$oIframeElement = new ElementObject();
			$oIframeElement->setNamesArray( array( 'iframe' ) );
			$oIframeElement->setCaptureAttributesArray( array( 'class', 'src' ) );
			$oParser->addElementObject( $oIframeElement );
			unset( $oIframeElement );
		}

		if ( ! $bDeferred || $oProcessor->oParams->get( 'pro_lazyload_bgimages', '0' )
			|| $oProcessor->oParams->get( 'pro_next_gen_images', '1' )
		)
		{
			$oBgElement = new ElementObject();
			$oBgElement->setNamesArray( array( '[^\s/"\'=<>]++' ) );
			$oBgElement->bSelfClosing = true;
			$oBgElement->setCaptureAttributesArray( array( 'class', 'style' ) );
			//language=RegExp
			$sValueCriteriaRegex = '(?=(?>[^b>]*+b?)*?[^b>]*+(background(?:-image)?))'
				. '(?=(?>[^u>]*+u?)*?[^u>]*+(' . CssParser::CSS_URL_CP( true ) . '))';
			$oBgElement->setValueCriteriaRegex( array( 'style' => $sValueCriteriaRegex ) );
			$oParser->addElementObject( $oBgElement );
			unset( $oBgElement );
		}

		if ( $bDeferred && $oProcessor->oParams->get( 'pro_lazyload_audiovideo', '0' ) )
		{
			$oVAElement = new ElementObject();
			$oVAElement->setNamesArray( array( 'video', 'audio' ) );
			$oVAElement->setCaptureAttributesArray( array( 'class', 'poster', 'preload', 'autoplay' ) );
			$oParser->addElementObject( $oVAElement );
			unset( $oVAElement );
		}
	}

	public static function getLazyLoadClass($aMatches)
	{
		return $aMatches[4];
	}
}