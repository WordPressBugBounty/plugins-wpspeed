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
use WPSpeed\Core\Html\FilesManager;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

class DynamicJs
{
	/** @var array $sCriticalJs Array of javascript files/scripts excluded from the Remove Unused Js feature */
	public static $aCriticalJs = [];
	/** @var array $aJsDynamicUrls Array of Js Urls to load dynamically for Remove Unused Js feature */
	public static $aJsDynamicUrls = [];

	/**
	 * @throws Html\ExcludeException
	 */
	public static function handleCriticalUrls( FilesManager $oFilesManager, $sUrl )
	{
		if ( $oFilesManager->oParams->get( 'pro_remove_unused_js_enable', '0' ) &&
			Helper::findExcludes( @$oFilesManager->aExcludes['critical_js']['js'], $sUrl ) )
		{
			self::$aCriticalJs[] = [
				'match' => $oFilesManager->aMatch[0],
				'url'   => $sUrl,
				'id'    => $oFilesManager->getFileID( $oFilesManager->aMatch )
			];

			$oFilesManager->excludeJsIEO();
		}

	}

	/**
	 * @throws Html\ExcludeException
	 */
	public static function handleCriticalScripts( FilesManager $oFilesManager, $sContent )
	{
		if ( $oFilesManager->oParams->get( 'pro_remove_unused_js_enable', '0' ) &&
			Helper::findExcludes( @$oFilesManager->aExcludes['critical_js']['script'], $sContent ) )
		{
			self::$aCriticalJs[] = [
				'match'   => $oFilesManager->aMatch[0],
				'content' => $sContent,
				'id'      => $oFilesManager->getFileID( $oFilesManager->aMatch )
			];

			$oFilesManager->excludeJsIEO();
		}
	}

	/**
	 * @throws Exception
	 */
	public static function appendCriticalJsToHtml( CacheManager $oCacheManager )
	{
		if ( $oCacheManager->oParams->get( 'pro_remove_unused_js_enable', '0' ) )
		{
			if ( ! empty ( self::$aCriticalJs ) )
			{
				$sCriticalJsId = $oCacheManager->getCacheId( self::$aCriticalJs, 'js' );
				$oCacheManager->getCombinedFiles( self::$aCriticalJs, $sCriticalJsId, 'js' );
				$oCacheManager->oLinkBuilder->appendCriticalJsToHtml( $oCacheManager->oLinkBuilder->buildUrl( $sCriticalJsId, 'js' ) );
			}
		}
	}
}