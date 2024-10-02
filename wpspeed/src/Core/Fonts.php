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

use WPSpeed\Core\Html\CacheManager;

class Fonts
{
	public static function appendOptimizedFontsToHtml( CacheManager $oCacheManager, $aFontFaceArray )
	{
		$aFonts = self::prepareFonts( $aFontFaceArray );

		$fontsId = $oCacheManager->getCacheId( $aFonts, 'css' );
		$oCacheManager->getCombinedFiles( $aFonts, $fontsId, 'css' );

		$fontsUrl = self::optimizeFile( $oCacheManager->oLinkBuilder->buildUrl( $fontsId, 'css' ) );
		$oCacheManager->oLinkBuilder->appendOptimizedFontsToHead( $fontsUrl );
	}

	private static function prepareFonts( $aFontFaceArray )
	{
		$aFonts = [];

		foreach ( $aFontFaceArray as $aFontFace )
		{
			$fontFaceCss = $aFontFace['content'];

			$aFonts[] = [
				'content'            => $fontFaceCss,
				'id'                 => md5( $fontFaceCss ),
				'match'              => $fontFaceCss,
				'media'              => $aFontFace['media'],
				'combining-fontface' => true
			];
		}

		return $aFonts;
	}

	private static function optimizeFile( $url )
	{
		return <<<HTML
<link rel="preload" as="style" href="{$url}" onload="this.rel='stylesheet'" >
HTML;
	}
}