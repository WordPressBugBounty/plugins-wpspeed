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

use WPSpeed\Core\CdnDomains;
use WPSpeed\Core\DynamicJs;
use WPSpeed\Core\Exception;
use WPSpeed\Core\GoogleFonts;
use WPSpeed\Core\Helper;
use WPSpeed\Core\Output;
use WPSpeed\Core\Url;
use WPSpeed\Platform\FileSystem;
use WPSpeed\Platform\Uri;
use WPSpeed\Platform\Cache;
use WPSpeed\Platform\Profiler;
use WPSpeed\Platform\Paths;
use WPSpeed\Platform\Plugin;
use WPSpeed\Platform\Utility;

/**
 *
 *
 */
class LinkBuilder
{
	/** @var Parser $oProcessor */
	public $oProcessor;
	/** @var string cache id * */
	public $oParams;
	/** @var string         Document line end */
	public $sLnEnd;
	public $bGFontPreloaded = false;
	/** @var string         Document tab */
	public $sTab;
	protected $oAsyncManager;
	protected $oFilesManager;
	/** @var array $aJsDynamicUrls Array of Js Urls to load dynamically for Remove Unused Js feature */
	protected $aJsDynamicUrls = [];
	protected $params;
	/** @var string Store the default mobile friendly essential CSS */
	protected $essentialCssMobileFriendly = 'img,iframe{max-width:100%;height:auto}a,li{margin:20px 0;padding:20px 0;font-size:20px;color:#000}ul{list-style:none;padding-left:0;margin-left:0}';
	

	/**
	 * Constructor
	 *
	 * @param   Parser  $oProcessor
	 */
	public function __construct( Processor $oProcessor = null )
	{
		$this->oProcessor    = $oProcessor;
		$this->oParams       = $this->oProcessor->oParams;
		$this->sLnEnd        = $this->oProcessor->sLnEnd;
		$this->sTab          = $this->oProcessor->sTab;
		$this->oFilesManager = FilesManager::getInstance( $this->oParams );
		$this->oAsyncManager = new AsyncManager( $this->oParams, $this->sLnEnd );
		$this->params 		 = Plugin::getPluginParams();
	}

	/**
	 * Add preconnect elements for Google Font files and CDN domains
	 * Used by PRO_ONLY
	 */
	public function addPreConnects()
	{
		if ( ! GoogleFonts::isGFontPreConnected( $this->oProcessor ) && GoogleFonts::$bGoogleFontsOptimized )
		{
			$this->prependChildToHead( GoogleFonts::getPreconnect() );
		}

		$this->prependChildToHead( CdnDomains::preconnect( $this->oParams ) );
	}

	private function prependChildToHead( $sChild )
	{
		$sHeadHtml = preg_replace( '#<head[^>]*+>#i', '<head>' . $this->sLnEnd . $this->sTab . $sChild, $this->oProcessor->getHeadHtml(), 1 );
		$this->oProcessor->setHeadHtml( $sHeadHtml );
	}

	public function optimizeGFonts( $aGFonts )
	{
		$this->appendChildToHead( GoogleFonts::optimizeFiles( $aGFonts ) );
	}

	public function appendOptimizedFontsToHead($fontFile)
	{
		$this->appendChildToHead($fontFile);
	}

	private function appendChildToHead( $sChild, $bCleanReplacement = false )
	{
		if ( $bCleanReplacement )
		{
			$sChild = Helper::cleanReplacement( $sChild );
		}

		$sHeadHtml = $this->oProcessor->getHeadHtml();
		$sHeadHtml = preg_replace( '#' . Parser::HTML_END_HEAD_TAG() . '#i', $sChild . $this->sLnEnd . $this->sTab . '</head>', $sHeadHtml, 1 );

		$this->oProcessor->setHeadHtml( $sHeadHtml );
	}

	public function addCriticalCssToHead( $sCriticalCss )
	{
		$sCriticalStyle = '<style id="wpspeed-critical-css">' . $this->sLnEnd .
			$sCriticalCss . $this->sLnEnd .
			'</style>';

		$this->appendChildToHead( $sCriticalStyle, true );
	}

	public function addExcludedJsToSection( $sSection )
	{
		$aExcludedJs = $this->oFilesManager->aExcludedJs;

		//Add excluded javascript files to the bottom of the HTML section
		$sExcludedJs = implode( $this->sLnEnd, $aExcludedJs['ieo'] ) . implode( $this->sLnEnd, $aExcludedJs['peo'] );
		$sExcludedJs = Helper::cleanReplacement( $sExcludedJs );

		$this->appendChildToHTML( $sExcludedJs, $sSection );
	}

	private function appendChildToHTML( $sChild, $sSection )
	{
		$sSearchArea = preg_replace( '#' . Parser::{'HTML_END_' . strtoupper( $sSection ) . '_Tag'}() . '#i', $this->sTab . $sChild . $this->sLnEnd . '</' . $sSection . '>', $this->oProcessor->getFullHtml(), 1 );
		$this->oProcessor->setFullHtml( $sSearchArea );
	}

	public function addDeferredJs( $aDefers, $sSection )
	{
		//If we're loading javascript dynamically add the deferred javascript files to array of files to load dynamically instead
		if ( $this->oParams->get( 'pro_remove_unused_js_enable', '0' ) )
		{
			$aDefersNoMatches          = array_map( function ( $a ) {
				unset( $a['match'] );
				return $a;
			}, $aDefers );
			DynamicJs::$aJsDynamicUrls = array_merge( DynamicJs::$aJsDynamicUrls, $aDefersNoMatches );
		}
		else
		{
			$sDefers = implode( $this->sLnEnd, array_column( $aDefers, 'match' ) );
			$this->appendChildToHTML( $sDefers, $sSection );
		}
	}

	public function setImgAttributes( $aCachedImgAttributes )
	{
		$sHtml = $this->oProcessor->getBodyHtml();
		$this->oProcessor->setBodyHtml( str_replace( $this->oProcessor->aImgs[0], $aCachedImgAttributes, $sHtml ) );
	}

	/**
	 * Insert url of aggregated file in html
	 *
	 * @param   string  $sId
	 * @param   string  $sType
	 * @param   string  $sSection     Whether section being processed is head|body
	 * @param   int     $iJsLinksKey  Index key of javascript combined file
	 *
	 * @throws Exception
	 */
	public function replaceLinks( $sId, $sType, $sSection = 'head', $iJsLinksKey = 0 )
	{
		WPSPEED_DEBUG ? Profiler::start( 'ReplaceLinks - ' . $sType ) : null;

		$sSearchArea = $this->oProcessor->getFullHtml();

		$sUrl     = $this->buildUrl( $sId, $sType );
		$sNewLink = $this->{'getNew' . ucfirst( $sType ) . 'Link'}( $sUrl );

		//All js files after the last excluded js will be placed at bottom of section
		if ( $sType == 'js' && $iJsLinksKey >= $this->oFilesManager->jsExcludedIndex
			&& ! empty( $this->oFilesManager->aJs[ $this->oFilesManager->iIndex_js ] ) )
		{
			//If Remove Unused js enabled we'll simply add these files to array to be dynamically loaded instead
			if ( $this->oParams->get( 'pro_remove_unused_js_enable', '0' ) )
			{
				DynamicJs::$aJsDynamicUrls[] = [
					'url'      => $sUrl,
					'module'   => false,
					'nomodule' => false
				];

				return;
			}
			//If last combined file is being inserted at the bottom of the page then
			//add the async or defer attribute
			if ( $sSection == 'body' )
			{
				//Add async attribute to last combined js file if option is set
				$sNewLink = str_replace( '></script>', $this->getAsyncAttribute() . '></script>', $sNewLink );
			}

			//Insert script tag at the appropriate section in the HTML
			$sSearchArea = preg_replace( '#' . Parser::{'HTML_END_' . ucfirst( $sSection ) . '_TAG'}() . '#i', $this->sTab . $sNewLink . $this->sLnEnd . '</' . $sSection . '>', $sSearchArea, 1 );

			$deferred = $this->oFilesManager->isFileDeferred( $sNewLink );
			Helper::addHttp2Push( $sUrl, $sType, $deferred );
		}
		else
		{
			Helper::addHttp2Push( $sUrl, $sType );
		}
		//Replace placeholders in HTML with combined files
		$sSearchArea = preg_replace( '#<WPSPEED_' . strtoupper( $sType ) . '([^>]++)>#', $sNewLink, $sSearchArea, 1 );
		$this->oProcessor->setFullHtml( $sSearchArea );

		WPSPEED_DEBUG ? Profiler::stop( 'ReplaceLinks - ' . $sType, true ) : null;
	}

	/**
	 * Returns url of aggregated file
	 *
	 * @param   string  $sId
	 * @param   string  $sType  css or js
	 *
	 * @return string  Url of aggregated file
	 * @throws Exception
	 */
	public function buildUrl( $sId, $sType )
	{
		$sPath = Paths::cachePath();
		$sUrl  = $sPath . '/' . $sType . '/' . $sId . '.' . $sType;

		$this->createStaticFiles( $sId, $sType, $sUrl );


		if ( $this->oParams->get( 'cookielessdomain_enable', '0' ) && ! Url::isRootRelative( $sUrl ) )
		{
			$sUrl = Url::toRootRelative( $sUrl );
		}

		return Helper::cookieLessDomain( $this->oParams, $sUrl, $sUrl );
	}

	/**
	 * Check if gzip is set or enabled
	 *
	 * @return boolean   True if gzip parameter set and server is enabled
	 */
	public function isGZ()
	{
		return ( extension_loaded( 'zlib' ) && ! ini_get( 'zlib.output_compression' )
			&& ( ini_get( 'output_handler' ) != 'ob_gzhandler' ) );
	}

	/**
	 * Create static combined file if not yet exists
	 *
	 *
	 * @param   string  $sId    Cache id of file
	 * @param   string  $sType  Type of file css|js
	 * @param   string  $sUrl   Url of combine file
	 *
	 * @return null
	 * @throws Exception
	 * @throws \Exception
	 */
	protected function createStaticFiles( $sId, $sType, $sUrl )
	{
		static $sTypeIndex;
		if(!is_array($sTypeIndex)) {
			$sTypeIndex = array('css'=>0, 'js'=>0);
		}
		
		WPSPEED_DEBUG ? Profiler::start( 'CreateStaticFiles - ' . $sType ) : null;

		// ADAPTIVE CONTENTS: remove any matched tag for bots
		// Check for user agent exclusion
		if($this->params->get('adaptive_contents_enable', 0) &&
		(($this->params->get('adaptive_contents_remove_all_css', 0) && $sType == 'css') ||
		($this->params->get('adaptive_contents_remove_all_js', 0) && $sType == 'js'))) {
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
					$fileReplacement = '';
					// File path of combined file
					$sCombinedFile = Helper::getFilePath( $sUrl );
					
					if($sType == 'css') {
						$fileReplacement = $this->essentialCssMobileFriendly;
						$adaptiveContentsEssentialCssCode = trim($this->params->get ( 'adaptive_contents_essential_css_code', ''));
						if($adaptiveContentsEssentialCssCode) {
							$fileReplacement .= $adaptiveContentsEssentialCssCode;
						}
						
						// Only append an eventual $adaptiveContentsEssentialCssCode to the first splitted CSS file only
						if($sTypeIndex[$sType] == 0) {
							FileSystem::write ( $sCombinedFile, $fileReplacement );
						} else {
							FileSystem::write ( $sCombinedFile, '' );
						}
					}
					
					if($sType == 'js') {
						FileSystem::write ( $sCombinedFile, '' );
					}
					
					$sTypeIndex[$sType]++;
					return;
				}
			}
		}
						
		//File path of combined file
		$sCombinedFile = Helper::getFilePath( $sUrl );

		if ( ! file_exists( $sCombinedFile ) )
		{
			$aGet = array(
				'f'    => $sId,
				'type' => $sType
			);

			$sContent = Output::getCombinedFile( $aGet, false );

			if ( $sContent === false )
			{
				throw new Exception( 'Error retrieving combined contents' );
			}

			//Create file and any directory
			if ( ! FileSystem::write( $sCombinedFile, $sContent ) )
			{
				Cache::deleteCache();

				throw new Exception( 'Error creating static file' );
			}
		}

		WPSPEED_DEBUG ? Profiler::stop( 'CreateStaticFiles - ' . $sType, true ) : null;
	}

	/**
	 * Adds the async attribute to the aggregated js file link
	 *
	 * @return string
	 */
	protected function getAsyncAttribute()
	{
		if ( $this->oParams->get( 'loadAsynchronous', '1' ) )
		{
			$attr = $this->oFilesManager->bLoadJsAsync ? 'async' : 'defer';

			return Helper::isXhtml( $this->oProcessor->getHtml() ) ? ' ' . $attr . '="' . $attr . '" ' : ' ' . $attr . ' ';
		}
		else
		{
			return '';
		}
	}

	/**
	 * Determine if document is of XHTML doctype
	 *
	 * @return boolean
	 */
	public function isXhtml()
	{
		return (bool)preg_match( '#^\s*+(?:<!DOCTYPE(?=[^>]+XHTML)|<\?xml.*?\?>)#i', trim( $this->oProcessor->getHtml() ) );
	}

	/**
	 *
	 * @param   array  $sUrl
	 *
	 * @throws Exception
	 */
	public function loadCssAsync( $aCssUrls )
	{
		if ( ! $this->oParams->get( 'pro_remove_unused_css', '0' ) )
		{
			$sCssPreloads = implode( Utility::lnEnd(), array_map( function ( $sUrl ) {

				//language=HTML
				return '<link rel="preload" as="style" href="' . $sUrl . '" onload="this.rel=\'stylesheet\'" />';
			}, $aCssUrls ) );

			$this->appendChildToHead( $sCssPreloads );
		}
		else
		{
			$this->oAsyncManager->loadCssAsync( $aCssUrls );
		}
	}

	public function appendCriticalJsToHtml( $sCriticalJsUrl )
	{
		$sCriticalJs = '<script src="' . $sCriticalJsUrl . '"></script>';

		$this->appendChildToHTML( $sCriticalJs, 'body' );
	}

	public function appendAsyncScriptsToHead()
	{
		$sScript = $this->cleanScript( $this->oAsyncManager->printHeaderScript() );
		$this->appendChildToHead( $sScript );
	}

	/**
	 *
	 * @param   string  $sScript
	 *
	 * @return string|string[]
	 */
	protected function cleanScript( $sScript )
	{
		if ( ! Helper::isXhtml( $this->oProcessor->getHtml() ) )
		{
			$sScript = str_replace( array(
				'<script type="text/javascript"><![CDATA[',
				'<script><![CDATA[',
				']]></script>'
			),
				array( '<script type="text/javascript">', '<script>', '</script>' ), $sScript );
		}

		return $sScript;
	}

	/**
	 * @param   string  $sUrl  Url of file
	 *
	 * @return string
	 */
	protected function getNewJsLink( $sUrl )
	{
		return '<script src="' . $sUrl . '"></script>';
	}

	/**
	 * @param   string  $sUrl  Url of file
	 *
	 * @return string
	 */
	protected function getNewCssLink( $sUrl )
	{
		//language=HTML
		if(Plugin::getPluginParams()->get('defer_combined_styles', 0)) {
			return '<style>html{visibility:hidden}</style><link rel="preload" as="style" onload="setTimeout(function(){document.querySelector(\'html\').style.visibility=\'visible\';}, 1);this.onload=null;this.rel=\'stylesheet\'" href="' . $sUrl . '"/>';
		} else {
			return '<link rel="stylesheet" href="' . $sUrl . '" />';
		}
	}
}


