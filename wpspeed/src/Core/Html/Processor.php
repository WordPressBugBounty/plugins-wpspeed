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

defined ( '_WPSPEED_EXEC' ) or die ( 'Restricted access' );

use WPSpeed\Core\Exception;
use WPSpeed\Core\FileRetriever;
use WPSpeed\Core\Helper;
use WPSpeed\Core\Html\Callbacks\Cdn;
use WPSpeed\Core\Html\Callbacks\CombineJsCss;
use WPSpeed\Core\Html\Callbacks\LazyLoad;
use WPSpeed\Core\Css\Parser as CssParser;
use WPSpeed\Core\Logger;
use WPSpeed\Core\Url;
use WPSpeed\Core\Cdn as CdnCore;
use WPSpeed\Platform\Profiler;
use WPSpeed\Platform\Settings;
use WPSpeed\Platform\Uri;
use WPSpeed\Platform\Utility;

/**
 * Class Processor
 *
 * @package WPSpeed\Core\Html
 *         
 *          This class interacts with the Parser passing over HTML elements, criteria and callbacks to parse for in the HTML
 *          and maintains the processed HTML
 */
class Processor {
	/** @var Settings       Plugin parameters */
	public $oParams;

	/** @var bool           Indicates if the page is an Amp page */
	public $bAmpPage = false;

	/** @var string         Line end used by document */
	public $sLnEnd;

	/** @var string         Tab used by document */
	public $sTab;

	/** @var array $aImgs Array of IMG elements requiring width/height attribute */
	public $aImgs = [ ];

	/** @var string         File hash */
	public $sFileHash;

	/** @var string         Used to determine the end of useful string after parsing */
	protected $sRegexMarker = 'WPSPEEDREGEXMARKER';

	/** @var string         HTML being processed */
	protected $sHtml;

	/**
	 * Processor constructor.
	 *
	 * @param string $sHtml
	 *        	HTML document of page
	 * @param Settings $oParams
	 *        	Plugin parameters
	 * @param FileRetriever|null $oFileRetriever
	 *        	FileRetriever object
	 */
	public function __construct(string $sHtml, Settings $oParams) {
		$this->sHtml = $sHtml;
		$this->oParams = $oParams;

		$this->bAmpPage = ( bool ) preg_match ( '#<html [^>]*?(?:&\#26A1;|amp)[ >]#', $sHtml );

		$this->sLnEnd = Utility::lnEnd ();
		$this->sTab = Utility::tab ();
	}

	/**
	 * Returns the HTML being processed
	 */
	public function getHtml() {
		return $this->sHtml;
	}
	public function processCombineJsCss() {
		global $wp;
		$oUri = Uri::getInstance ();
		$this->sFileHash = serialize ( $this->oParams->getOptions () ) . WPSPEED_VERSION . $oUri->toString ( array (
				'scheme',
				'host'
		) );

		if ($this->isCombineFilesSet () || $this->oParams->get ( 'pro_http2_push_enable', '0' )) {

			// Exclude by menu item the CSS extraction
			if($this->oParams->get ( 'optimizeCssDelivery_enable', '0' )) {
				$currentPageUrl = home_url ( $wp->request );
				$menuExcluded = $this->oParams->get ( 'extractcss_menu_excluded', array () );
				foreach ( $menuExcluded as $menuChunk ) {
					if (stripos ( $currentPageUrl, $menuChunk )) {
						$this->oParams->set( 'optimizeCssDelivery_enable', '0' );
					}
				}
			}
			
			// Setup default excludes for Adaptive Contents
			$isBot = false;
			if($this->oParams->get('adaptive_contents_enable', 0) && $this->oParams->get('adaptive_contents_extract_css', 0) && isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
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
						'moto g power'
				) );
				if (! empty ( $botsList )) {
					foreach ( $botsList as &$bot ) {
						$bot = preg_quote($bot);
					}
					$botRegexPattern = implode('|', $botsList);
				}
				
				$isBot = preg_match("/{$botRegexPattern}/i", $user_agent) || array_key_exists($_SERVER['REMOTE_ADDR'], Utility::$botsIP);
				
				if(!$isBot) {
					$this->oParams->set( 'optimizeCssDelivery_enable', '0' );
				}
			}
 
			try {
				$oParser = new Parser ();
				$oParser->addExclude ( Parser::HTML_COMMENT () );
				$oParser->addExclude ( Parser::HTML_ELEMENT ( 'noscript' ) );
				$oParser->addExclude ( Parser::HTML_ELEMENT ( 'template' ) );
				$this->setUpJsCssCriteria ( $oParser );
				$oCombineJsCssCallback = new CombineJsCss ( $this );
				$oCombineJsCssCallback->setSection ( 'head' );
				$sProcessedHeadHtml = $oParser->processMatchesWithCallback ( $this->getHeadHtml (), $oCombineJsCssCallback );
				$this->setHeadHtml ( $sProcessedHeadHtml );

				if ($this->oParams->get ( 'bottom_js', '1' )) {
					$oCombineJsCssCallback->setSection ( 'body' );
					$sProcessedBodyHtml = $oParser->processMatchesWithCallback ( $this->getBodyHtml (), $oCombineJsCssCallback );
					$this->setBodyHtml ( $sProcessedBodyHtml );
				}
			} catch ( Exception $oException ) {
				Logger::log ( 'CombineJsCss failed ' . $oException->getMessage (), $this->oParams );
			}

			\WPSpeed\Core\GoogleFonts::isGFontPreConnected ( $this );
		}
	}
	public function isCombineFilesSet() {
		return ! Helper::isMsieLT10 () && $this->oParams->get ( 'combine_files_enable', '1' ) && ! $this->bAmpPage;
	}
	protected function setUpJsCssCriteria(Parser $oParser) {
		$oJsFilesElement = new ElementObject ();
		$oJsFilesElement->setNamesArray ( array (
				'script'
		) );
		// language=RegExp
		$oJsFilesElement->addNegAttrCriteriaRegex ( 'type==(?!(?>[\'"]?)(?:(?:text|application)/javascript|module)[\'"> ])' );
		$oJsFilesElement->setCaptureAttributesArray ( array (
				'src'
		) );
		$oJsFilesElement->setValueCriteriaRegex ( '(?=.)' );
		$oParser->addElementObject ( $oJsFilesElement );

		$oJsContentElement = new ElementObject ();
		$oJsContentElement->setNamesArray ( array (
				'script'
		) );
		// language=RegExp
		$oJsContentElement->addNegAttrCriteriaRegex ( 'src|type==(?!(?>[\'"]?)(?:text|application)/javascript[\'"> ])' );
		$oJsContentElement->bCaptureContent = true;
		$oParser->addElementObject ( $oJsContentElement );

		$oCssFileElement = new ElementObject ();
		$oCssFileElement->bSelfClosing = true;
		$oCssFileElement->setNamesArray ( array (
				'link'
		) );
		// language=RegExp
		$oCssFileElement->addNegAttrCriteriaRegex ( 'itemprop|disabled|type==(?!(?>[\'"]?)text/css[\'"> ])|rel==(?!(?>[\'"]?)stylesheet[\'"> ])' );
		$oCssFileElement->setCaptureAttributesArray ( array (
				'href'
		) );
		$oCssFileElement->setValueCriteriaRegex ( '(?=.)' );
		$oParser->addElementObject ( $oCssFileElement );

		$oStyleElement = new ElementObject ();
		$oStyleElement->setNamesArray ( array (
				'style'
		) );
		// language=RegExp
		$oStyleElement->addNegAttrCriteriaRegex ( 'scope|amp|type==(?!(?>[\'"]?)text/(?:css|stylesheet)[\'"> ])' );
		$oStyleElement->bCaptureContent = true;
		$oParser->addElementObject ( $oStyleElement );
	}
	public function getHeadHtml() {
		preg_match ( '#' . Parser::HTML_HEAD_ELEMENT () . '#i', $this->sHtml, $aMatches );

		return $aMatches [0] . $this->sRegexMarker;
	}
	public function setHeadHtml($sHtml) {
		$sHtml = $this->cleanRegexMarker ( $sHtml );
		$this->sHtml = preg_replace ( '#' . Parser::HTML_HEAD_ELEMENT () . '#i', Helper::cleanReplacement ( $sHtml ), $this->sHtml, 1 );
	}
	protected function cleanRegexMarker($sHtml) {
		return preg_replace ( '#' . preg_quote ( $this->sRegexMarker, '#' ) . '.*+$#', '', $sHtml );
	}
	public function getBodyHtml() {
		preg_match ( '#' . Parser::HTML_BODY_ELEMENT () . '#si', $this->sHtml, $aMatches );

		return $aMatches [0] . $this->sRegexMarker;
	}
	public function setBodyHtml($sHtml) {
		$sHtml = $this->cleanRegexMarker ( $sHtml );
		$this->sHtml = preg_replace ( '#' . Parser::HTML_BODY_ELEMENT () . '#si', Helper::cleanReplacement ( $sHtml ), $this->sHtml, 1 );
	}

	/**
	 *
	 * @return array|mixed
	 */
	public function processImagesForApi() {
		try {
			$oParser = new Parser ();
			$oParser->addExclude ( Parser::HTML_COMMENT () );
			$oParser->addExclude ( Parser::HTML_ELEMENTS ( array (
					'script',
					'noscript',
					'style'
			) ) );

			$oImgElement = new ElementObject ();
			$oImgElement->bSelfClosing = true;
			$oImgElement->setNamesArray ( array (
					'img'
			) );
			$oImgElement->setCaptureAttributesArray ( array (
					'src',
					'srcset'
			) );
			$oParser->addElementObject ( $oImgElement );
			unset ( $oImgElement );

			$oBgElement = new ElementObject ();
			$oBgElement->setNamesArray ( array (
					'[^\s/"\'=<>]++'
			) );
			$oBgElement->bSelfClosing = true;
			$oBgElement->setCaptureAttributesArray ( array (
					'style'
			) );
			// language=RegExp
			$sValueCriteriaRegex = '(?=(?>[^b>]*+b?)*?[^b>]*+(background(?:-image)?))' . '(?=(?>[^u>]*+u?)*?[^u>]*+(' . CssParser::CSS_URL_CP ( true ) . '))';
			$oBgElement->setValueCriteriaRegex ( array (
					'style' => $sValueCriteriaRegex
			) );
			$oParser->addElementObject ( $oBgElement );
			unset ( $oBgElement );

			return $oParser->findMatches ( $this->getBodyHtml (), PREG_SET_ORDER );
		} catch ( Exception $oException ) {
			Logger::log ( 'ProcessApiImages failed ' . $oException->getMessage (), $this->oParams );
		}
	}
	public function processLazyLoad() {
		$bLazyLoad = ( bool ) ($this->oParams->get ( 'lazyload_enable', '0' ) && ! $this->bAmpPage);

		if ($bLazyLoad || $this->oParams->get ( 'pro_http2_push_enable', '0' ) || $this->oParams->get ( 'pro_next_gen_images', '1' )) {
			WPSPEED_DEBUG ? Profiler::start ( 'LazyLoadImages' ) : null;

			if ($bLazyLoad) {
				$css = '        <noscript>
			<style type="text/css">
				img.wpspeed-lazyload, iframe.wpspeed-lazyload{
					display: none;
				}                               
			</style>                                
		</noscript>
	</head>';

				$this->sHtml = preg_replace ( '#' . Parser::HTML_END_HEAD_TAG () . '#i', $css, $this->sHtml, 1 );
				// $aExcludes = array_merge_recursive( $aExcludes, $this->getLazyLoadExcludes() );
			}

			$sHtml = '<WPSPEED_START>' . $this->getBodyHtml ();

			preg_match ( '#(^(?:(?:<[0-9a-z]++[^>]*+>[^<]*+(?><[^0-9a-z][^<]*+)*+){0,81}))(.*+)#six', $sHtml, $aMatches );

			$sAboveFoldHtml = str_replace ( '<WPSPEED_START>', '', $aMatches [1] );
			$sBelowFoldHtml = $aMatches [2];

			try {
				$aHttp2Args = array (
						'lazyload' => false,
						'deferred' => false,
						'parent' => ''
				);

				$oAboveFoldParser = new Parser ();
				// language=RegExp
				$this->setupLazyLoadCriteria ( $oAboveFoldParser, false );
				$oHttp2Callback = new LazyLoad ( $this, $aHttp2Args );
				$sProcessedAboveFoldHtml = $oAboveFoldParser->processMatchesWithCallback ( $sAboveFoldHtml, $oHttp2Callback );

				$oBelowFoldParser = new Parser ();
				$aLazyLoadArgs = array (
						'lazyload' => $bLazyLoad,
						'deferred' => true,
						'parent' => ''
				);

				$this->setupLazyLoadCriteria ( $oBelowFoldParser, true );
				$oLazyLoadCallback = new LazyLoad ( $this, $aLazyLoadArgs );
				$sProcessedBelowFoldHtml = $oBelowFoldParser->processMatchesWithCallback ( $sBelowFoldHtml, $oLazyLoadCallback );

				$this->setBodyHtml ( $sProcessedAboveFoldHtml . $sProcessedBelowFoldHtml );
			} catch ( Exception $oException ) {
				Logger::log ( 'Lazy-load failed: ' . $oException->getMessage (), $this->oParams );
			}

			WPSPEED_DEBUG ? Profiler::stop ( 'LazyLoadImages', true ) : null;
		}
	}
	protected function setupLazyLoadCriteria(Parser $oParser, $bDeferred) {
		$oParser->addExclude ( Parser::HTML_COMMENT () );
		$oParser->addExclude ( Parser::HTML_ELEMENT ( 'script' ) );
		$oParser->addExclude ( Parser::HTML_ELEMENT ( 'noscript' ) );
		$oParser->addExclude ( Parser::HTML_ELEMENT ( 'textarea' ) );
		$oParser->addExclude ( Parser::HTML_ELEMENT ( 'template' ) );

		$oImgElement = new ElementObject ();
		$oImgElement->bSelfClosing = true;
		$oImgElement->setNamesArray ( array (
				'img'
		) );
		// language=RegExp
		$oImgElement->addNegAttrCriteriaRegex ( '(?:data-(?:src|original))' );
		$oImgElement->setCaptureAttributesArray ( array (
				'class',
				'src',
				'srcset',
				'(?:data-)?width',
				'(?:data-)?height'
		) );
		$oParser->addElementObject ( $oImgElement );
		unset ( $oImgElement );

		$oInputElement = new ElementObject ();
		$oInputElement->bSelfClosing = true;
		$oInputElement->setNamesArray ( array (
				'input'
		) );
		// language=RegExp
		$oInputElement->addPosAttrCriteriaRegex ( 'type=(?>[\'"]?)image[\'"> ]' );
		$oInputElement->setCaptureAttributesArray ( array (
				'class',
				'src'
		) );
		$oParser->addElementObject ( $oInputElement );
		unset ( $oInputElement );

		$oPictureElement = new ElementObject ();
		$oPictureElement->setNamesArray ( array (
				'picture'
		) );
		$oPictureElement->setCaptureAttributesArray ( array (
				'class'
		) );
		$oPictureElement->bCaptureContent = true;
		$oParser->addElementObject ( $oPictureElement );
		unset ( $oPictureElement );

		\WPSpeed\Core\LazyLoadExtended::setupLazyLoadExtended ( $this, $oParser, $bDeferred );
	}
	public function processImageAttributes() {
		$bImgAttributesEnabled = $this->oParams->get ( 'img_attributes_enable', '0' );

		// Setup default excludes for Adaptive Contents
		$isBot = false;
		if ($this->oParams->get ( 'adaptive_contents_enable', 0 ) && isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
			$user_agent = $_SERVER ['HTTP_USER_AGENT'];
			$botRegexPattern = array ();
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
					'moto g power'
			) );
			if (! empty ( $botsList )) {
				foreach ( $botsList as &$bot ) {
					$bot = preg_quote ( $bot );
				}
				$botRegexPattern = implode ( '|', $botsList );
			}

			$isBot = preg_match ( "/{$botRegexPattern}/i", $user_agent ) || array_key_exists ( $_SERVER ['REMOTE_ADDR'], Utility::$botsIP );
		}
		if ($this->oParams->get ( 'adaptive_contents_add_size_attributes', 0 ) && ! $isBot) {
			$bImgAttributesEnabled = false;
		}

		if ($bImgAttributesEnabled || ($this->oParams->get ( 'lazyload_enable', '0' ) && $this->oParams->get ( 'lazyload_autosize', '0' ))) {
			WPSPEED_DEBUG ? Profiler::start ( 'ProcessImageAttributes' ) : null;

			$oParser = new Parser ();
			$oParser->addExclude ( Parser::HTML_COMMENT () );

			$oImgElement = new ElementObject ();
			$oImgElement->setNamesArray ( [ 
					'img'
			] );
			$oImgElement->bSelfClosing = true;
			// language=RegExp
			$oImgElement->addPosAttrCriteriaRegex ( 'width' );
			// language=RegExp
			$oImgElement->addPosAttrCriteriaRegex ( 'height' );
			$oImgElement->bNegateCriteria = true;
			$oImgElement->setCaptureAttributesArray ( [ 
					'data-src',
					'src'
			] );
			$oParser->addElementObject ( $oImgElement );

			try {
				$this->aImgs = $oParser->findMatches ( $this->getBodyHtml () );
			} catch ( Exception $oException ) {
				Logger::log ( 'Image Attributes matches failed: ' . $oException->getMessage (), $this->oParams );
			}

			WPSPEED_DEBUG ? Profiler::stop ( 'ProcessImageAttributes', true ) : null;
		}
	}
	public function processCdn() {
		if (! $this->oParams->get ( 'cookielessdomain_enable', '0' ) || (trim ( $this->oParams->get ( 'cookielessdomain', '' ) ) == '' && trim ( $this->oParams->get ( 'pro_cookielessdomain_2', '' ) ) == '' && trim ( $this->oParams->get ( 'pro_cookieless_3', '' ) ) == '')) {
			return false;
		}

		WPSPEED_DEBUG ? Profiler::start ( 'RunCookieLessDomain' ) : null;

		$aStaticFiles = CdnCore::getInstance ( $this->oParams )->getCdnFileTypes ();
		$sf = implode ( '|', $aStaticFiles );
		$oUri = clone Uri::getInstance ();
		$port = $oUri->toString ( array (
				'port'
		) );

		if (empty ( $port )) {
			$port = ':80';
		}

		$host = '(?:www\.)?' . preg_quote ( preg_replace ( '#^www\.#i', '', $oUri->getHost () ), '#' ) . '(?:' . $port . ')?';
		// Find base value in HTML
		$oBaseParser = new Parser ();
		$oBaseElement = new ElementObject ();
		$oBaseElement->setNamesArray ( array (
				'base'
		) );
		$oBaseElement->bSelfClosing = true;
		$oBaseElement->setCaptureAttributesArray ( array (
				'href'
		) );
		$oBaseParser->addElementObject ( $oBaseElement );

		$aMatches = $oBaseParser->findMatches ( $this->getHeadHtml () );
		unset ( $oBaseParser );
		unset ( $oBaseElement );

		$dir = trim ( Uri::base ( true ), '/' );

		// Adjust $dir if necessary based on <base/>
		if (! empty ( $aMatches [0] )) {
			$oBaseUri = Uri::getInstance ( $aMatches [4] [0] );
			// Remove filename from path
			$baseDir = trim ( preg_replace ( '#/?[^/]*$#', '', $oBaseUri->getPath () ), '/ \n\r\t\v\0"' );

			if ($baseDir != '') {
				$dir = $baseDir;
			}
		}

		// This part should match the scheme and host of a local file
		// language=RegExp
		$localhost = '(?:\s*+(?:(?>https?:)?//' . $host . ')?)(?!http|//)';
		// language=RegExp
		$valueMatch = '(?!data:image)' . '(?=' . $localhost . ')' . '(?=((?<=")(?>\.?[^.>"?]*+)*?\.(?>' . $sf . ')(?=["?\#])' . '|(?<=\')(?>\.?[^.>\'?]*+)*?\.(?>' . $sf . ')(?=[\'?\#])' . '|(?<=\()(?>\.?[^.>)?]*+)*?\.(?>' . $sf . ')(?=[)?\#])' . '|(?<=^|[=\s,])(?>\.?[^.>\s?]*+)*?\.(?>' . $sf . ')(?=[\s?\#>]|$)))';

		try {
			// Get regex for <script> without src attribute
			$oElementParser = new Parser ();

			$oElementWithCriteria = new ElementObject ();
			$oElementWithCriteria->setNamesArray ( array (
					'script'
			) );
			$oElementWithCriteria->addNegAttrCriteriaRegex ( 'src' );

			$oElementParser->addElementObject ( $oElementWithCriteria );
			$sScriptWithoutSrc = $oElementParser->getElementWithCriteria ();
			unset ( $oElementParser );
			unset ( $oElementWithCriteria );

			// Process cdn for elements with href or src attributes
			$oSrcHrefParser = new Parser ();
			$oSrcHrefParser->addExclude ( Parser::HTML_COMMENT () );
			$oSrcHrefParser->addExclude ( $sScriptWithoutSrc );

			$this->setUpCdnSrcHrefCriteria ( $oSrcHrefParser, $valueMatch );

			$oCdnCallback = new Cdn ( $this );
			$oCdnCallback->setDir ( $dir );
			$oCdnCallback->setLocalhost ( $host );
			$sCdnHtml = $oSrcHrefParser->processMatchesWithCallback ( $this->getFullHtml (), $oCdnCallback );
			unset ( $oSrcHrefParser );

			$this->setFullHtml ( $sCdnHtml );

			// Process cdn for CSS urls in style attributes or <style/> elements
			// language=RegExp
			$sUrlSearchRegex = '(?=((?>[^()<>]*+[()]?)*?[^()<>]*+(?<=url)\((?>[\'"]?)' . $valueMatch . '))';

			$oUrlParser = new Parser ();
			$oUrlParser->addExclude ( Parser::HTML_COMMENT () );
			$oUrlParser->addExclude ( Parser::HTML_ELEMENTS ( [ 
					'script',
					'link',
					'meta'
			] ) );
			$this->setUpCdnUrlCriteria ( $oUrlParser, $sUrlSearchRegex );
			$oCdnCallback->setContext ( 'cssurl' );
			$oCdnCallback->setSearchRegex ( $valueMatch );
			$sCdnUrlHtml = $oUrlParser->processMatchesWithCallback ( $this->getFullHtml (), $oCdnCallback );
			unset ( $oUrlParser );

			$this->setFullHtml ( $sCdnUrlHtml );

			// Process cdn for elements with srcset attributes
			$oSrcsetParser = new Parser ();
			$oSrcsetParser->addExclude ( Parser::HTML_COMMENT () );
			$oSrcsetParser->addExclude ( Parser::HTML_ELEMENT ( 'script' ) );
			$oSrcsetParser->addExclude ( Parser::HTML_ELEMENT ( 'style' ) );

			$oSrcsetElement = new ElementObject ();
			$oSrcsetElement->bSelfClosing = true;
			$oSrcsetElement->setNamesArray ( [ 
					'img',
					'source'
			] );
			$oSrcsetElement->setCaptureOneOrBothAttributesArray ( [ 
					'srcset',
					'data-srcset'
			] );
			$oSrcsetElement->setValueCriteriaRegex ( '(?=.)' );

			$oSrcsetParser->addElementObject ( $oSrcsetElement );
			$oCdnCallback->setContext ( 'srcset' );
			$sCdnSrcsetHtml = $oSrcsetParser->processMatchesWithCallback ( $this->getBodyHtml (), $oCdnCallback );
			unset ( $oSrcsetParser );
			unset ( $oSrcsetElement );

			$this->setBodyHtml ( $sCdnSrcsetHtml );
		} catch ( Exception $oException ) {
			Logger::log ( 'Cdn failed :' . $oException->getMessage (), $this->oParams );
		}

		WPSPEED_DEBUG ? Profiler::stop ( 'RunCookieLessDomain', true ) : null;
	}
	protected function setUpCdnSrcHrefCriteria(Parser $oParser, $sValueMatch) {
		$oSrcElement = new ElementObject ();
		$oSrcElement->bSelfClosing = true;
		$oSrcElement->setNamesArray ( [ 
				'img',
				'script',
				'source',
				'input'
		] );
		$oSrcElement->setCaptureOneOrBothAttributesArray ( [ 
				'src',
				'data-src'
		] );
		$oSrcElement->setValueCriteriaRegex ( $sValueMatch );
		$oParser->addElementObject ( $oSrcElement );
		unset ( $oSrcElement );

		$oHrefElement = new ElementObject ();
		$oHrefElement->bSelfClosing = true;
		$oHrefElement->setNamesArray ( [ 
				'a',
				'link',
				'image'
		] );
		$oHrefElement->setCaptureAttributesArray ( [ 
				'(?:xlink:)?href'
		] );
		$oHrefElement->setValueCriteriaRegex ( $sValueMatch );
		$oParser->addElementObject ( $oHrefElement );
		unset ( $oHrefElement );

		$oVideoElement = new ElementObject ();
		$oVideoElement->bSelfClosing = true;
		$oVideoElement->setNamesArray ( [ 
				'video'
		] );
		$oVideoElement->setCaptureAttributesArray ( [ 
				'(?:src|poster)'
		] );
		$oVideoElement->setValueCriteriaRegex ( $sValueMatch );
		$oParser->addElementObject ( $oVideoElement );
		unset ( $oVideoElement );

		$oMediaElement = new ElementObject ();
		$oMediaElement->bSelfClosing = true;
		$oMediaElement->setNamesArray ( [ 
				'meta'
		] );
		$oMediaElement->setCaptureAttributesArray ( [ 
				'content'
		] );
		$oMediaElement->setValueCriteriaRegex ( $sValueMatch );
		$oParser->addElementObject ( $oMediaElement );
		unset ( $oMediaElement );
	}
	public function getFullHtml() {
		return $this->sHtml . $this->sRegexMarker;
	}
	public function setFullHtml($sHtml) {
		$this->sHtml = $this->cleanRegexMarker ( $sHtml );
	}
	protected function setUpCdnUrlCriteria(Parser $oParser, $sValueMatch) {
		$oElements = new ElementObject ();
		$oElements->bSelfClosing = true;
		// language=RegExp
		$oElements->setNamesArray ( array (
				'(?!style|script|link|meta)[^\s/"\'=<>]++'
		) );
		$oElements->setCaptureAttributesArray ( array (
				'style'
		) );
		$oElements->setValueCriteriaRegex ( $sValueMatch );
		$oParser->addElementObject ( $oElements );
		unset ( $oElements );

		$oStyleElement = new ElementObject ();
		$oStyleElement->setNamesArray ( array (
				'style'
		) );
		$oStyleElement->bCaptureContent = true;
		$oStyleElement->setValueCriteriaRegex ( $sValueMatch );
		$oParser->addElementObject ( $oStyleElement );
		unset ( $oStyleElement );
	}

	/**
	 *
	 * @return string
	 */
	public function cleanHtml() {
		$aSearch = [ 
				'#' . Parser::HTML_HEAD_ELEMENT () . '#ix',
				'#' . Parser::HTML_COMMENT () . '#ix',
				'#' . Parser::HTML_ELEMENT ( 'script' ) . '#ix',
				'#' . Parser::HTML_ELEMENT ( 'style' ) . '#ix',
				'#' . Parser::HTML_ELEMENT ( 'link', true ) . '#six'
		];

		$aReplace = [ 
				'<head><title></title></head>',
				'',
				'',
				'',
				''
		];

		$html = preg_replace ( $aSearch, $aReplace, $this->sHtml );

		// Remove any hidden element from HtmL
		return preg_replace_callback ( '#(<[^>]*+>)[^<>]*+#ix', function ($aMatches) {
			if (preg_match ( '#type\s*+=\s*+["\']?hidden["\'\s>]|\shidden(?=[\s>=])[^>\'"=]*+[>=]#i', $aMatches [1] )) {
				return '';
			}

			// Add linebreak for readability during debugging
			return $aMatches [1] . "\n";
		}, $html );
	}
}
