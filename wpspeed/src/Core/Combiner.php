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

use WPSpeed\RegexTokenizer\Debug\Debug;
use WPSpeed\Core\Admin\Icons;
use WPSpeed\Core\Css\Processor as CssProcessor;
use WPSpeed\Core\Html\Processor as HtmlProcessor;
use WPSpeed\Core\Css\Sprite\SpriteGenerator;
use WPSpeed\Platform\Cache;
use WPSpeed\Platform\Profiler;
use WPSpeed\Platform\Settings;
use WPSpeed\Platform\Utility;
use WPSpeed\Minify\Js;
use WPSpeed\Minify\Css;
use WPSpeed\Core\LightImages;
use WPSFramework\Uri\Uri;
use Psr\Log\LoggerAwareInterface;

/**
 *
 *
 */
class Combiner implements LoggerAwareInterface
{
	use Debug;

	public static $bLogErrors = false;
	public $params = null;
	public $sLnEnd = '';
	public $sTab = '';
	public $bBackend = false;

	/**
	 * Constructor
	 *
	 * @param   Settings  $params
	 * @param   bool      $bBackend
	 */
	public function __construct( Settings $params, $bBackend = false )
	{
		$this->params   = $params;
		$this->bBackend = $bBackend;

		$this->sLnEnd = Utility::lnEnd();
		$this->sTab   = Utility::tab();
	}

	/**
	 *
	 * @return bool|mixed
	 */
	public function getLogParam()
	{
		if ( self::$bLogErrors == '' )
		{
			self::$bLogErrors = $this->params->get( 'log', 0 );
		}

		return self::$bLogErrors;
	}

	/**
	 * Get aggregated and possibly minified content from js and css files
	 *
	 * @param   array   $aUrlArray  Indexed multidimensional array of urls of css or js files for aggregation
	 * @param   string  $sType      css or js
	 *
	 * @return array   Aggregated (and possibly minified) contents of files
	 * @throws Exception
	 */
	public function getContents( $aUrlArray, $sType )
	{
		WPSPEED_DEBUG ? Profiler::start( 'GetContents - ' . $sType, true ) : null;

		$aResult   = $this->combineFiles( $aUrlArray, $sType );
		$sContents = $this->prepareContents( $aResult['content'] );

		if ( $sType == 'css' )
		{

			if ( $this->params->get( 'csg_enable', 0 ) )
			{
				try
				{
					$oSpriteGenerator = new SpriteGenerator( $this->params );
					$aSpriteCss       = $oSpriteGenerator->getSprite( $sContents );

					if ( ! empty( $aSpriteCss ) && ! empty( $aSpriteCss['needles'] ) && ! empty( $aSpriteCss['replacements'] ) )
					{
						$sContents = str_replace( $aSpriteCss['needles'], $aSpriteCss['replacements'], $sContents );
					}
				}
				catch ( Exception $ex )
				{
					Logger::log( $ex->getMessage(), $this->params );
				}
			}

			$sContents = $aResult['import'] . $sContents;

			if ( function_exists( 'mb_convert_encoding' ) )
			{
				$sContents = '@charset "utf-8";' . $sContents;
			}

		}

		//Save contents in array to store in cache
		$aContents = array(
			'filemtime' => Utility::unixCurrentDate(),
			'etag'      => md5( $sContents ),
			'contents'  => $sContents,
			'gfonts'    => $aResult['gfonts'],
			'images'    => array_unique( $aResult['images'] ),
			'font-face'  => $aResult['font-face']
		);

		WPSPEED_DEBUG ? Profiler::stop( 'GetContents - ' . $sType ) : null;

		return $aContents;
	}

	/**
	 * Aggregate contents of CSS and JS files
	 *
	 * @param   array   $aUrlArray  Array of links of files to combine
	 * @param   string  $sType      css|js
	 *
	 * @return array               Aggregated contents
	 * @throws Exception
	 */
	public function combineFiles( $aUrlArray, $sType )
	{
		$aData = [
			'content'   => '',
			'import'    => '',
			'font-face' => [],
			'gfonts'    => [],
			'images'    => []
		];

		// ADAPTIVE CONTENTS: remove any matched tag for bots
		// Check for user agent exclusion
		$isBot = false;
		if($this->params->get('adaptive_contents_enable', 0)) {
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
			}
		}
		
		//Iterate through each file/script to optimize and combine
		foreach ( $aUrlArray as $aUrl )
		{
			//Truncate url to less than 40 characters
			$sUrl = Helper::prepareFileUrl( $aUrl, $sType );
			
			// Prevent bots response to be cached
			if($isBot) {
				$aUrl ['id'] = '';
			}

			WPSPEED_DEBUG ? Profiler::start( 'CombineFile - ' . $sUrl ) : null;

			//If a cache id is present then cache this individual file to avoid
			//optimizing it again if it's present on another page
			if ( isset( $aUrl['id'] ) && $aUrl['id'] != '' )
			{
				$function = array( $this, 'cacheContent' );
				$args     = array( $aUrl, $sType, true );

				//Optimize and cache file/script returning the optimized content
				$aResult = Cache::getCallbackCache( $aUrl['id'], $function, $args );

				//Append to combined contents
				$aData['content'] .= $this->addCommentedUrl( $sType, $aUrl ) . $aResult['content'] .
					$this->sLnEnd . 'DELIMITER';
			}
			else
			{
				//If we're not caching just get the optimized content
				$aResult = $this->cacheContent( $aUrl, $sType, false );
				$aResult['content'] = $this->minifyContent( $aResult['content'], $sType, $aUrl );
				$aResult['content'] = $this->prepareContents( $aResult['content'] );
				$aData['content'] .= $this->addCommentedUrl( $sType, $aUrl ) . $aResult['content'] . '|"LINE_END"|';
			}

			if ( $sType == 'css' )
			{
				$aData['import'] .= $aResult['import'];
				$aData['gfonts'] = array_merge( $aData['gfonts'], $aResult['gfonts'] );
				$aData['images'] = array_merge( $aData['images'], $aResult['images'] );

				if ( ! empty( $aResult['font-face'] ) )
				{
					$aData['font-face'][] = $aResult['font-face'];
				}
			}

			WPSPEED_DEBUG ? Profiler::stop( 'CombineFile - ' . $sUrl, true ) : null;
		}

		return $aData;
	}

	/**
	 *
	 * @param   string  $sType
	 * @param   string  $sUrl
	 *
	 * @return string
	 */
	protected function addCommentedUrl( $sType, $sUrl )
	{
		$sComment = '';

		if ( $this->params->get( 'debug', '1' ) )
		{
			if ( is_array( $sUrl ) )
			{
				$sUrl = isset( $sUrl['url'] ) ? $sUrl['url'] : ( ( $sType == 'js' ? 'script' : 'style' ) . ' declaration' );
			}

			$sComment = '|"COMMENT_START ' . $sUrl . ' COMMENT_END"|';
		}

		return $sComment;
	}

	/**
	 * Optimize and cache contents of individual file/script returning optimized content
	 *
	 * @param   array    $aUrl
	 * @param   string   $sType
	 * @param   boolean  $bPrepare
	 *
	 * @return string|string[]|null
	 * @throws Exception
	 */
	public function cacheContent( $aUrl, $sType, $bPrepare )
	{
		//Initialize content string
		$sContent = '';
		$aData    = array();

		//If it's a file fetch the contents of the file
		if ( isset( $aUrl['url'] ) )
		{
			//Convert local urls to file path
			$sPath          = Helper::getFilePath( $aUrl['url'] );
			$oFileRetriever = FileRetriever::getInstance();
			$sContent       .= $oFileRetriever->getFileContents( $sPath );
		}
		else
		{
			//If it's a declaration just use it
			$sContent .= $aUrl['content'];
		}

		if ( $sType == 'css' )
		{
			$oCssProcessor = new CssProcessor( $this->params );
			$oCssProcessor->setUrlArray( $aUrl );
			$oCssProcessor->setCss( $sContent );
			$oCssProcessor->formatCss();
			$oCssProcessor->processUrls( false, false, $this->bBackend );
			$oCssProcessor->processMediaQueries();
			$oCssProcessor->processAtRules();

			$sContent = $oCssProcessor->getCss();

			$aData['import']    = $oCssProcessor->getImports();
			$aData['gfonts']    = $oCssProcessor->getGFonts();
			$aData['images']    = $oCssProcessor->getImages();
			$aData['font-face'] = $oCssProcessor->getFontFace();
			
			// Process even background images of CSS files
			if(!is_admin () && $this->params->get('lightimgs_status', 0) && $this->params->get('optimize_css_background_images', 0)) {
				$lightImageOptimizer = new LightImages($this->params);
				$dom = new \DOMDocument('1.0', 'utf-8');
				$processGIF = $this->params->get('img_support_gif', 0);
				$imgsRegex = $processGIF ? '/\.jpg|\.jpeg|\.png|\.gif|\.bmp/i' : '/\.jpg|\.jpeg|\.png|\.bmp/i';
				$uriInstance = Uri::getInstance();
				$absoluteUri = rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/') . '/';
				$sContent = preg_replace_callback(
					'/(url)(\(.*\))/imU',
					function ($matches) use ($lightImageOptimizer, $dom, $imgsRegex, $absoluteUri) {
						// Apply only to jpg, jpeg, png, gif, bmp
						if(!preg_match($imgsRegex, $matches[2])) {
							return $matches[0];
						}
						
						$innerContents = trim($matches[2], '()');
						$innerContents = trim($innerContents, '\'"');
						$innerContents = trim($innerContents, '/\\');
						$innerContents = str_replace('../', '', $innerContents);
						$innerContents = '/' . $innerContents;
						
						// Call here the LightImages optimizer for this image, then replace the path with the cached image
						$element = $dom->createElement('img', '');
						$element->setAttribute('src', $innerContents);
						$lightImageOptimizer->optimizeSingleImage($element);
						$newSrc = $element->getAttribute('src');
						$newAbsoluteUri = $absoluteUri . ltrim($newSrc, '/');
						
						// Check if the image has been processed, otherwise leave it unaltered
						if(stripos($newAbsoluteUri, 'cache/wpspeed') === false) {
							return $matches[0];
						}
						
						return "url('" . $newAbsoluteUri . "')";
					},
					$sContent
				);
			}
		}

		if ( $sType == 'js' && trim( $sContent ) != '' )
		{
			if ( $this->params->get( 'try_catch', '1' ) )
			{
				$sContent = $this->addErrorHandler( $sContent, $aUrl );
			}
			else
			{
				$sContent = $this->addSemiColon( $sContent );
			}
		}

		// ADAPTIVE CONTENTS: remove any matched tag for bots
		// Check for user agent exclusion
		if($this->params->get('adaptive_contents_enable', 0)) {
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
					// Exclude whole file contents here
					$removeAJS = $this->params->get('adaptive_contents_remove_js', 0);
					$removeAJSFiles = $this->params->get('adaptive_contents_remove_js_files', array());
					if(isset ( $aUrl ['url'] ) && $removeAJS && $sType == 'js') {
						if (! empty ( $removeAJSFiles )) {
							foreach ( $removeAJSFiles as $jsAFileToRemove ) {
								if (stripos ( $jsAFileToRemove, $aUrl ['url'] ) !== false || stripos ( $aUrl ['url'], $jsAFileToRemove ) !== false) {
									$sContent = '';
								}
							}
						}
					}
					$removeACSS = $this->params->get('adaptive_contents_remove_css', 0);
					$removeACSSFiles = $this->params->get('adaptive_contents_remove_css_files', array());
					if(isset ( $aUrl ['url'] ) && $removeACSS && $sType == 'css') {
						if (! empty ( $removeACSSFiles )) {
							foreach ( $removeACSSFiles as $cssAFileToRemove ) {
								if (stripos ( $cssAFileToRemove, $aUrl ['url'] ) !== false || stripos ( $aUrl ['url'], $cssAFileToRemove ) !== false) {
									$sContent = '';
								}
							}
						}
					}
				}
			}
		}
		
		if ( $bPrepare )
		{
			$sContent = $this->minifyContent( $sContent, $sType, $aUrl );
			$sContent = $this->prepareContents( $sContent );
		}

		$aData['content'] = $sContent;

		return $aData;
	}

	/**
	 * Add semi-colon to end of js files if non exists;
	 *
	 * @param   string  $sContent
	 * @param   array   $aUrl
	 *
	 * @return string
	 */
	public function addErrorHandler( $sContent, $aUrl )
	{
		$sContent = 'try {' . $this->sLnEnd . $sContent . $this->sLnEnd . '} catch (e) {' . $this->sLnEnd;
		$sContent .= 'console.error(\'Error in ';
		$sContent .= isset( $aUrl['url'] ) ? 'file:' . $aUrl['url'] : 'script declaration';
		$sContent .= '; Error:\' + e.message);' . $this->sLnEnd . '};';

		return $sContent;
	}

	/**
	 * Add semi-colon to end of js files if non exists;
	 *
	 * @param   string  $sContent
	 *
	 * @return string
	 */
	public function addSemiColon( $sContent )
	{
		$sContent = rtrim( $sContent );

		if ( substr( $sContent, -1 ) != ';' && ! preg_match( '#\|"COMMENT_START File[^"]+not found COMMENT_END"\|#', $sContent ) )
		{
			$sContent = $sContent . ';';
		}

		return $sContent;
	}

	/**
	 * Minify contents of fil
	 *
	 * @param   string  $sContent
	 * @param   string  $sType
	 * @param   array   $aUrl
	 *
	 * @return string $sMinifiedContent Minified content or original content if failed
	 */
	protected function minifyContent( $sContent, $sType, $aUrl )
	{
		if ( $this->params->get( $sType . '_minify', 0 ) )
		{
			$sUrl = Helper::prepareFileUrl( $aUrl, $sType );

			$sMinifiedContent = trim( $sType == 'css' ? Css::optimize( $sContent ) : Js::optimize( $sContent ) );

			/* @TODO inject Exception class into minifier libraries */
			if ( preg_last_error() !== 0 )
			{
				Logger::log( sprintf( 'Error occurred trying to minify: %s', $sUrl ), $this->params );
				$sMinifiedContent = $sContent;
			}

			$this->_debug( $sUrl, '', 'minifyContent' );

			return $sMinifiedContent;
		}

		return $sContent;
	}

	/**
	 * Remove placeholders from aggregated file for caching
	 *
	 * @param   string  $sContents  Aggregated file contents
	 * @param   bool    $test
	 *
	 * @return string
	 */
	public function prepareContents( $sContents, $test = false )
	{
		$sContents = str_replace(
			array(
				'|"COMMENT_START',
				'|"COMMENT_IMPORT_START',
				'COMMENT_END"|',
				'DELIMITER',
				'|"LINE_END"|'
			),
			array(
				$this->sLnEnd . '/***! ',
				$this->sLnEnd . $this->sLnEnd . '/***! @import url',
				' !***/' . $this->sLnEnd . $this->sLnEnd,
				( $test ) ? 'DELIMITER' : '',
				$this->sLnEnd
			), trim( $sContents ) );

		return $sContents;
	}
}
