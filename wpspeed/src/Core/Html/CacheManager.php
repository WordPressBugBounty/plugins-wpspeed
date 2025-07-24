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

namespace WPSpeed\Core\Html;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

use WPSpeed\Core\Combiner;
use WPSpeed\Core\Cron;
use WPSpeed\Core\DynamicJs;
use WPSpeed\Core\Exception;
use WPSpeed\Core\Fonts;
use WPSpeed\Core\Helper;
use WPSpeed\Core\Logger;
use WPSpeed\Core\Url;
use WPSpeed\Platform\Cache;
use WPSpeed\Platform\Profiler;
use WPSpeed\Platform\Uri;
use WPSpeed\Platform\Plugin;
use WPSpeed\Platform\Utility;

/**
 * Class CacheManager
 * @package WPSpeed\Core\Html
 *
 *          Handles the retrieval of contents from cache and hands over the repairing of the HTML to LinkBuilder
 */
class CacheManager
{
	public $oParams;

	public $oLinkBuilder;

	protected $aImgs;

	protected $oFilesManager;
	
	protected $params;

	public function __construct( LinkBuilder $oLinkBuilder )
	{
		$this->oLinkBuilder = $oLinkBuilder;
		$this->oParams      = $oLinkBuilder->oProcessor->oParams;
		$this->aImgs        = $oLinkBuilder->oProcessor->aImgs;

		$this->oFilesManager = FilesManager::getInstance( $this->oParams );
		
		$this->params = Plugin::getPluginParams();
	}

	public function handleCombineJsCss()
	{
		if ( ! function_exists( "array_key_last" ) )
		{
			function array_key_last( $array )
			{
				if ( ! is_array( $array ) || empty( $array ) )
				{
					return null;
				}
 
				return array_keys( $array )[ count( $array ) - 1 ];
			}
		}

		//Indexed multidimensional array of files to be combined
		$aCssLinksArray = $this->oFilesManager->aCss;
		$aJsLinksArray  = $this->oFilesManager->aJs;

		// Add CSS to hide elements that are set to be lazyloaded, mask/force the add custom css code
		if($this->params->get('lazyload_html_enable', 0) && $lazyloadHtmlCssSelector = trim($this->params->get('lazyload_html_css_selector', ''))) {
			$lazyloadDelay = $this->params->get('lazyload_html_delay', 3000);
			$lazyloadMethod = $this->params->get ('lazyload_method', 'scroll');
			$importantOverride = $this->params->get('lazyload_html_use_important_override', 0) ? ' !important' : '';
			
			// Concatenate custom code if existing
			$customCssCode = '';
			if ($this->params->get ( 'add_custom_css_code', 0 ) && $concatenateCustomCss = trim($this->params->get ( 'custom_css_code', ''))) {
				$customCssCode = $concatenateCustomCss;
			}
			$this->params->set ( 'add_custom_css_code', 1 );
			if($lazyloadMethod == 'delay') {
				$this->params->set ( 'custom_css_code', '*[data-jspeed-dom-lazyload]{visibility:hidden' . $importantOverride . ';display:none' . $importantOverride . '}' . $customCssCode);
			} else {
				$this->params->set ( 'custom_css_code', '*[data-jspeed-dom-lazyload]{visibility:hidden' . $importantOverride . '}' . $customCssCode);
			}
			
			// Concatenate custom code if existing
			$customJsCode = '';
			if ($this->params->get ( 'add_custom_js_code', 0 ) && $concatenateCustomJs = trim($this->params->get ( 'custom_js_code', ''))) {
				$customJsCode = $concatenateCustomJs;
			}
			$this->params->set ( 'add_custom_js_code', 1 );
			if($lazyloadMethod == 'delay') {
				$this->params->set ( 'custom_js_code', 'setTimeout(function(){[].slice.call(document.querySelectorAll("*[data-jspeed-dom-lazyload]")).map(function(element){element.removeAttribute("data-jspeed-dom-lazyload")})}, ' . $lazyloadDelay . ');' . $customJsCode);
			} else {
				$this->params->set ( 'custom_js_code', 'function jspeedIsVisible(e){var t=e.getBoundingClientRect();return document.documentElement.clientHeight-t.top>0&&t.top+t.height>0}function jspeedShowVisible(){[].slice.call(document.querySelectorAll("*[data-jspeed-dom-lazyload]")).map(function(e){jspeedIsVisible(e)&&e.removeAttribute("data-jspeed-dom-lazyload")})}window.addEventListener("scroll",jspeedShowVisible),window.addEventListener("DOMContentLoaded",function(){jspeedShowVisible()});' . $customJsCode);
			}
		}
		
		// ADAPTIVE CONTENTS: add essential CSS code for bots if remove all CSS is not active
		if ($this->params->get('adaptive_contents_enable', 0) &&
		   ($adaptiveContentsEssentialCssCode = trim($this->params->get ( 'adaptive_contents_essential_css_code', ''))) &&
		   !$this->params->get('adaptive_contents_remove_all_css', 0)){
				if (isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
					$user_agent = $_SERVER ['HTTP_USER_AGENT'];
					$botRegexPattern = array();
					$botsList = $this->params->get ( 'adaptive_contents_bots_list', array (
							'lighthouse',
							'googlebot',
							'googlebot-mobile',
							'googlebot-video',
							'gtmetrix',
							'baiduspider',
							'duckduckbot',
							'twitterbot',
							'applebot',
							'semrushbot',
							'ptst',
							'ahrefs',
							'pingdom',
							'seranking',
							'moto g power',
							'rsiteauditor'
					) );
					if (! empty ( $botsList )) {
						foreach ( $botsList as &$bot ) {
							$bot = preg_quote($bot);
						}
						$botRegexPattern = implode('|', $botsList);
					}
					
					$isBot = preg_match("/{$botRegexPattern}/i", $user_agent) || array_key_exists($_SERVER['REMOTE_ADDR'], Utility::$botsIP);
					if($isBot) {
						$mobileFriendlyCss = '';
						if($this->params->get('adaptive_contents_remove_all_css', 0)) {
							$mobileFriendlyCss = $this->essentialCssMobileFriendly;
						}
						$this->params->set ( 'add_custom_css_code', 1 );
						$this->params->set ( 'custom_css_code', $mobileFriendlyCss . $adaptiveContentsEssentialCssCode);
					}
				}
		}
		
		// Add the custom CSS/JS file array
		if ($this->params->get ( 'add_custom_css_code', 0 )) {
			$customCssCode = trim($this->params->get ( 'custom_css_code', ''));
			$aMatches = array();
			$aMatches['content'] = $customCssCode;
			if($customCssCode) {
				$aCssLinksArray [0] [] = array (
						'match' => '<style>' . $customCssCode . '</style>',
						'content' => $customCssCode,
						'id' => $this->oFilesManager->getFileID ( $aMatches ),
						'media' => null
				);
			}
		}
		
		// Add the custom CSS/JS file array
		if ($this->params->get ( 'add_custom_js_code', 0 )) {
			$customJsCode = trim($this->params->get ( 'custom_js_code', ''));
			$aMatches = array();
			$aMatches['content'] = $customJsCode;
			if($customJsCode) {
				$aJsLinksArray [0] [] = array (
						'match' => '<script>' . $customJsCode . '</script>',
						'content' => $customJsCode,
						'id' => $this->oFilesManager->getFileID ( $aMatches )
				);
			}
		}
		
		if ( ! Helper::isMsieLT10() && $this->oParams->get( 'combine_files_enable', '1' ) && ! $this->oLinkBuilder->oProcessor->bAmpPage )
		{
			$bCombineCss = (bool)$this->oParams->get( 'css', 1 );
			$bCombineJs  = (bool)$this->oParams->get( 'js', 1 );


			if ( $bCombineCss || $bCombineJs )
			{
				$this->runCronTasks();
			}

			if ( $bCombineCss && ! empty( $aCssLinksArray ) )
			{
				$oCssProcessor = new \WPSpeed\Core\Css\Processor( $this->oParams );

				$sPageCss = '';
				$aCssUrls = [];
				$aJsUrls  = [];

				foreach ( $aCssLinksArray as $aCssLinks )
				{
					$sCssCacheId = $this->getCacheId( $aCssLinks, 'css' );
					//Optimize and cache css files
					$aCssCache = $this->getCombinedFiles( $aCssLinks, $sCssCacheId, 'css' );

					if ( ! empty ( $aCssCache['gfonts'] ) )
					{
						$this->oLinkBuilder->optimizeGFonts( $aCssCache['gfonts'] );
					}

					if ( ! empty ( $aCssCache['font-face'] ) )
					{
						Fonts::appendOptimizedFontsToHtml( $this, $aCssCache['font-face'] );
					}

					//If Optimize CSS Delivery feature not enabled then we'll need to insert the link to
					//the combined css file in the HTML
					if ( ! $this->oParams->get( 'optimizeCssDelivery_enable', '0' ) )
					{
						//Http2 push
						$oCssProcessor->preloadHttp2( $aCssCache['contents'], true );
						$this->oLinkBuilder->replaceLinks( $sCssCacheId, 'css' );
					}
					else
					{
						$sPageCss   .= $aCssCache['contents'];
						$aCssUrls[] = $this->oLinkBuilder->buildUrl( $sCssCacheId, 'css' );
					}
				}

				$css_delivery_enabled = $this->oParams->get( 'optimizeCssDelivery_enable', '0' );

				if ( $css_delivery_enabled )
				{
					$this->oLinkBuilder->loadCssAsync( $aCssUrls );

					try
					{
						$sCriticalCss = $this->getCriticalCss( $oCssProcessor, $sPageCss );
						//Http2 push
						$oCssProcessor->preloadHttp2( $sCriticalCss );
						$this->oLinkBuilder->addCriticalCssToHead( $sCriticalCss );
					}
					catch ( Exception $oException )
					{
						Logger::log( 'Optimize CSS Delivery failed: ' . $oException->getMessage(), $this->oParams );
						//@TODO Just add CssUrls to HEAD section of document
					}

					foreach ( $aCssUrls as $sUrl )
					{
						Helper::addHttp2Push( $sUrl, 'style', true );
					}
				}

				$this->oLinkBuilder->addPreConnects();
			}

			if ( $bCombineJs )
			{
				$sSection = $this->oParams->get( 'bottom_js', '1' ) == '1' ? 'body' : 'head';

				$this->oLinkBuilder->addExcludedJsToSection( $sSection );

				if ( ! empty ( $aJsLinksArray ) )
				{

					foreach ( $aJsLinksArray as $aJsLinksKey => $aJsLinks )
					{

						$sJsCacheId = $this->getCacheId( $aJsLinks, 'js' );
						//Optimize and cache javascript files
						$this->getCombinedFiles( $aJsLinks, $sJsCacheId, 'js' );
						//Insert link to combined javascript file in HTML
						$this->oLinkBuilder->replaceLinks( $sJsCacheId, 'js', $sSection, $aJsLinksKey );
					}

					DynamicJs::appendCriticalJsToHtml( $this );
				}

				//We also now append any deferred javascript files below the
				//last combined javascript file
				$aDefers = $this->oFilesManager->aDefers;

				if ( ! empty( $aDefers ) )
				{
					$this->oLinkBuilder->addDeferredJs( $aDefers, $sSection );
				}
			}
		}

		$this->oLinkBuilder->appendAsyncScriptsToHead();
	}

	protected function runCronTasks()
	{
		WPSPEED_DEBUG ? Profiler::start( 'RunCronTasks' ) : null;

		$sId = md5( 'CRON_TASKS' );

		$aArgs = array( $this->oLinkBuilder->oProcessor );

		$oCron     = new Cron( $this->oParams );
		$aFunction = array( $oCron, 'runCronTasks' );

		try
		{
			$this->loadCache( $aFunction, $aArgs, $sId );
		}
		catch ( Exception $e )
		{
		}

		WPSPEED_DEBUG ? Profiler::stop( 'RunCronTasks', true ) : null;

	}

	/**
	 * Create and cache aggregated file if it doesn't exists.
	 *
	 * @param   callable  $aFunction  Name of function used to aggregate files
	 * @param   array     $aArgs      Arguments used by function above
	 * @param   string    $sId        Generated id to identify cached file
	 *
	 * @return  bool|array  The contents of the combined file
	 *
	 * @throws  Exception
	 */
	private function loadCache( $aFunction, $aArgs, $sId )
	{
		//Returns the contents of the combined file or false if failure
		$mCached = Cache::getCallbackCache( $sId, $aFunction, $aArgs );

		if ( $mCached === false )
		{
			throw new Exception( 'Error creating cache file' );
		}

		return $mCached;
	}

	/**
	 * Calculates the id of combined files from array of urls
	 *
	 * @param   array   $aUrlArrays
	 * @param   string  $sType
	 *
	 * @return   string   ID of combined file
	 */
	public function getCacheId( $aUrlArrays, $sType )
	{
		return md5( serialize( array_column( $aUrlArrays, 'id' ) ) . $sType );
	}

	/**
	 * Returns contents of the combined files from cache
	 *
	 * @param   array   $aLinks  Indexed multidimensional array of file urls to combine
	 * @param   string  $sId     Id of generated cache file
	 * @param   string  $sType   css or js
	 *
	 * @return array Contents in array from cache containing combined file(s)
	 * @throws Exception
	 */
	public function getCombinedFiles( $aLinks, $sId, $sType )
	{
		WPSPEED_DEBUG ? Profiler::start( 'GetCombinedFiles - ' . $sType ) : null;

		$aArgs = array( $aLinks, $sType );

		$oCombiner = new Combiner( $this->oParams );
		$aFunction = array( &$oCombiner, 'getContents' );

		$aCachedContents = $this->loadCache( $aFunction, $aArgs, $sId );

		WPSPEED_DEBUG ? Profiler::stop( 'GetCombinedFiles - ' . $sType, true ) : null;

		return $aCachedContents;
	}

	public function getCriticalCss( $oCssProcessor, $sPageCss )
	{
		if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) )
		{
			throw new Exception( 'Document Object Model not supported' );
		}
		else
		{
			WPSPEED_DEBUG ? Profiler::start( 'CacheManager::GetCriticalCss' ) : null;

			$sHtml = $this->oLinkBuilder->oProcessor->cleanHtml();
			//Remove text nodes from HTML elements
			$sHtml = preg_replace_callback( '#(<(?>[^<>]++|(?1))*+>)|((?<=>)(?=[^<>\S]*+[^<>\s])[^<>]++)#',
				function ( $m ) {
					if ( ! empty( $m[1] ) )
					{
						return $m[0];
					}

					if ( ! empty( $m[2] ) )
					{
						return ' ';
					}

				}, $sHtml );

			//Truncate HTML to 400 elements to key cache
			$sHtmlKey = '';
			preg_replace_callback( '#<(?:[a-z0-9]++)(?:[^>]*+)>(?><?[^<]*+(<ul\b[^>]*+>(?>[^<]*+<(?!ul)[^<]*+|(?1))*?</ul>)?)*?(?=<[a-z0-9])#i',
				function ( $aM ) use ( &$sHtmlKey ) {
					$sHtmlKey .= $aM[0];

					return;
				}, $sHtml, 400 );

			$aArgs     = [ $sPageCss, $sHtml ];
			$aFunction = [ $oCssProcessor, 'optimizeCssDelivery' ];
			$iCacheId  = md5( serialize( $this->oParams ) . $sHtmlKey );

			WPSPEED_DEBUG ? Profiler::stop( 'CacheManager::getCriticalCss', true ) : null;

			return $this->loadCache( $aFunction, $aArgs, $iCacheId );
		}
	}

	/**
	 *
	 *
	 */
	public function handleImgAttributes()
	{
		if ( ! empty( $this->aImgs ) )
		{
			WPSPEED_DEBUG ? Profiler::start( 'AddImgAttributes' ) : null;

			$sId = md5( serialize( $this->aImgs ) . $this->oFilesManager->sFileHash );

			try
			{
				$aImgAttributes = $this->loadCache( array(
					$this,
					'getCachedImgAttributes'
				), array( $this->aImgs ), $sId );
			}
			catch ( Exception $e )
			{
				return;
			}

			$this->oLinkBuilder->setImgAttributes( $aImgAttributes );
		}


		WPSPEED_DEBUG ? Profiler::stop( 'AddImgAttributes', true ) : null;
	}

	/**
	 *
	 * @param   array  $aImages
	 *
	 * @return array
	 */
	public function getCachedImgAttributes( $aImages )
	{
		$aImgAttributes = array();
		$total          = count( $aImages[0] );

		for ( $i = 0; $i < $total; $i++ )
		{
			if ( $aImages[2][ $i ] )
			{
				//delimiter
				$delim = $aImages[3][ $i ];
				//Image url
				$url = $aImages[4][ $i ];
			}
			else
			{
				$delim = $aImages[6][ $i ];
				$url   = $aImages[7][ $i ];
			}

			if (
				Url::isInvalid( $url )
				|| ! $this->oFilesManager->isHttpAdapterAvailable( $url )
				|| Url::isSSL( $url ) && ! extension_loaded( 'openssl' )
				|| ! Url::isHttpScheme( $url )
			)
			{
				$aImgAttributes[] = $aImages[0][ $i ];
				continue;
			}

			$sPath = Helper::getFilePath( $url );

			if ( file_exists( $sPath ) )
			{
				$aSize = getimagesize( $sPath );

				if ( $aSize === false || empty( $aSize ) || ( $aSize[0] == '1' && $aSize[1] == '1' ) )
				{
					$aImgAttributes[] = $aImages[0][ $i ];
					continue;
				}

				$u                     = Parser::HTML_ATTRIBUTE_VALUE();
				$bImgAttributesEnabled = $this->oParams->get( 'img_attributes_enable', '0' );
				
				// Setup default excludes for Adaptive Contents
				$isBot = false;
				if($this->oParams->get('adaptive_contents_enable', 0) && isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
					$user_agent = $_SERVER ['HTTP_USER_AGENT'];
					$botRegexPattern = array();
					$botsList = $this->oParams->get ( 'adaptive_contents_bots_list', array (
							'lighthouse',
							'googlebot',
							'googlebot-mobile',
							'googlebot-video',
							'gtmetrix',
							'baiduspider',
							'duckduckbot',
							'twitterbot',
							'applebot',
							'semrushbot',
							'ptst',
							'ahrefs',
							'pingdom',
							'seranking',
							'moto g power',
							'rsiteauditor'
					) );
					if (! empty ( $botsList )) {
						foreach ( $botsList as &$bot ) {
							$bot = preg_quote($bot);
						}
						$botRegexPattern = implode('|', $botsList);
					}
					
					$isBot = preg_match("/{$botRegexPattern}/i", $user_agent) || array_key_exists($_SERVER['REMOTE_ADDR'], Utility::$botsIP);
				}
				if($this->oParams->get('adaptive_contents_add_size_attributes', 0) && !$isBot) {
					$bImgAttributesEnabled = false;
				}
				
				//Checks for any existing width attribute
				if ( preg_match( "#\swidth\s*+=\s*+['\"]?($u)#i", $aImages[0][ $i ], $aMatches ) )
				{
					//Calculate height based on aspect ratio
					$iWidthAttrValue = preg_replace( '#[^0-9]#', '', $aMatches[1] );
					$height          = round( ( $aSize[1] / $aSize[0] ) * $iWidthAttrValue, 2 );
					//If add attributes not enabled put data-height instead
					$heightAttribute = $bImgAttributesEnabled ? 'height=' : 'data-height=';
					$heightAttribute .= $delim . $height . $delim;
					//Add height attribute to the img element and save in array
					$aImgAttributes[] = preg_replace( '#\s*+/?>$#', ' ' . $heightAttribute . ' />', $aImages[0][ $i ] );

				} //Check for any existing height attribute
				elseif ( preg_match( "#\sheight\s*+=\s*=['\"]?($u)#i", $aImages[0][ $i ], $aMatches ) )
				{
					//Calculate width based on aspect ratio
					$iHeightAttrValue = preg_replace( '#[^0-9]#', '', $aMatches[1] );
					$width            = round( ( $aSize[0] / $aSize[1] ) * $iHeightAttrValue, 2 );
					//if add attributes not enabled put data-width instead
					$widthAttribute = $bImgAttributesEnabled ? 'width=' : 'data-width=';
					$widthAttribute .= $delim . $width . $delim;
					//Add width attribute to the img element and save in array
					$aImgAttributes[] = preg_replace( '#\s*+/?>$#', ' ' . $widthAttribute . ' />', $aImages[0][ $i ] );
				}
				else //No existing attributes, just go ahead and add attributes from getimagesize
				{
					//It's best to use the same delimiter for the width/height attributes that the urls used
					$sReplace = ' ' . str_replace( '"', $delim, $aSize[3] );
					//Add the width and height attributes from the getimagesize function
					$sReplace = preg_replace( '#\s*+/?>$#', $sReplace . ' />', $aImages[0][ $i ] );

					if ( ! $bImgAttributesEnabled )
					{
						$sReplace = str_replace( array(
							'width=',
							'height='
						), array( 'data-width=', 'data-height=' ), $sReplace );
					}
					$aImgAttributes[] = $sReplace;
				}

			}
			else
			{
				$aImgAttributes[] = $aImages[0][ $i ];
			}
		}

		return $aImgAttributes;
	}
}
