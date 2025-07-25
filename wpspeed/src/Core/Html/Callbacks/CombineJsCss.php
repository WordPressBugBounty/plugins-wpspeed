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

use WPSpeed\Core\Html\FilesManager;
use WPSpeed\Core\Helper;
use WPSpeed\Core\Html\Processor;
use WPSpeed\Core\Url;
use WPSpeed\Platform\Utility;
use WPSpeed\Platform\Cache;
use WPSpeed\Platform\Profiler;
use WPSpeed\Platform\Excludes;

class CombineJsCss extends CallbackBase
{
	/** @var array          Array of excludes parameters */
	protected $aExcludes;

	protected $sSection = 'head';

	/**
	 * CombineJsCss constructor.
	 *
	 * @param   Processor  $oProcessor
	 */
	public function __construct( Processor $oProcessor )
	{
		parent::__construct( $oProcessor );

		$this->setupExcludes();
	}

	/**
	 * Retrieves all exclusion parameters for the Combine Files feature
	 *
	 * @return array
	 */
	protected function setupExcludes()
	{
		WPSPEED_DEBUG ? Profiler::start( 'SetUpExcludes' ) : null;

		$this->aExcludes = array();
		$aExcludes       = array();
		$oParams         = $this->oParams;

		//These parameters will be excluded while preserving execution order
		$aExJsComp  = $this->getExComp( $oParams->get( 'excludeJsComponents_peo', '' ) );
		$aExCssComp = $this->getExComp( $oParams->get( 'excludeCssComponents', '' ) );

		$aExcludeJs_peo     = Helper::getArray( $oParams->get( 'excludeJs_peo', '' ) );
		$aExcludeCss_peo    = Helper::getArray( $oParams->get( 'excludeCss', '' ) );
		$aExcludeScript_peo = Helper::getArray( $oParams->get( 'excludeScripts_peo' ) );
		$aExcludeStyle_peo  = Helper::getArray( $oParams->get( 'excludeStyles' ) );

		$aExcludeScript_peo = array_map( function ( $sScript ) {
			return stripslashes( $sScript );
		}, $aExcludeScript_peo );

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
		
		$defaultExcludesAlways = array(
				'.com/maps/api/js',
				'.com/jsapi',
				'.com/uds',
				'typekit.net',
				'cdn.ampproject.org',
				'googleadservices.com/pagead/conversion'
		);
		$defaultExcludesBottom = array( '.com/recaptcha/api' );
		if($isBot) {
			$defaultExcludesAlways = array();
			$defaultExcludesBottom = array();
		}

		$this->aExcludes['excludes_peo']['js']         = array_merge( $aExcludeJs_peo, $aExJsComp, $defaultExcludesAlways, Excludes::head( 'js' ) );
		$this->aExcludes['excludes_peo']['css']        = array_merge( $aExcludeCss_peo, $aExCssComp, Excludes::head( 'css' ) );
		$this->aExcludes['excludes_peo']['js_script']  = $aExcludeScript_peo;
		$this->aExcludes['excludes_peo']['css_script'] = $aExcludeStyle_peo;

		$this->aExcludes['critical_js']['js']     = Helper::getArray( $oParams->get( 'pro_criticalJs', '' ) );
		$this->aExcludes['critical_js']['script'] = Helper::getArray( $oParams->get( 'pro_criticalScripts', '' ) );

		//These parameters will be excluded without preserving execution order
		$aExJsComp_ieo      = $this->getExComp( $oParams->get( 'excludeJsComponents', '' ) );
		$aExcludeJs_ieo     = Helper::getArray( $oParams->get( 'excludeJs', '' ) );
		$aExcludeScript_ieo = Helper::getArray( $oParams->get( 'excludeScripts' ) );

		$this->aExcludes['excludes_ieo']['js']        = array_merge( $aExcludeJs_ieo, $aExJsComp_ieo );
		$this->aExcludes['excludes_ieo']['js_script'] = $aExcludeScript_ieo;

		$this->aExcludes['dontmove']['js']      = Helper::getArray( $oParams->get( 'dontmoveJs', '' ) );
		$this->aExcludes['dontmove']['scripts'] = Helper::getArray( $oParams->get( 'dontmoveScripts', '' ) );

		$this->aExcludes['remove']['js']  = Helper::getArray( $oParams->get( 'remove_js', '' ) );
		$this->aExcludes['remove']['css'] = Helper::getArray( $oParams->get( 'remove_css', '' ) );

		$aExcludes['head']                = $this->aExcludes;

		if ( $this->oParams->get( 'bottom_js', '1' ) == 1 )
		{
			$this->aExcludes['excludes_peo']['js_script'] = array_merge(
				$this->aExcludes['excludes_peo']['js_script'],
				array( '.write(', 'var google_conversion' ),
				Excludes::body( 'js', 'script' )
			);
			$this->aExcludes['excludes_peo']['js']        = array_merge(
				$this->aExcludes['excludes_peo']['js'],
				$defaultExcludesBottom,
				Excludes::body( 'js' )
			);
			$this->aExcludes['dontmove']['scripts']       = array_merge(
				$this->aExcludes['dontmove']['scripts'],
				array( '.write(' )
			);

			$aExcludes['body'] = $this->aExcludes;

		}

		WPSPEED_DEBUG ? Profiler::stop( 'SetUpExcludes', true ) : null;


		$this->aExcludes = $aExcludes;
	}

	/**
	 * Generates regex for excluding components set in plugin params
	 *
	 * @param $sExComParam
	 *
	 * @return array
	 */
	protected function getExComp( $sExComParam )
	{
		$aComponents = Helper::getArray( $sExComParam );
		$aExComp     = array();

		if ( ! empty( $aComponents ) )
		{
			$aExComp = array_map( function ( $sValue ) {
				return $sValue . '/';
			}, $aComponents );
		}

		return $aExComp;
	}

	/**
	 * Callback function used to remove urls of css and js files in head tags
	 *
	 * @param   array  $aMatches  Array of all matches
	 *
	 * @return string               Returns the url if excluded, empty string otherwise
	 */
	public function processMatches( $aMatches )
	{
		if ( empty( $aMatches[0] ) )
		{
			return $aMatches[0];
		}

		$sUrl         = $aMatches['url'] = trim( isset( $aMatches[4] ) ? $aMatches[4] : '' );
		$sDeclaration = $aMatches['content'] = ! isset( $aMatches[4] ) ? $aMatches[2] : '';

		if ( preg_match( '#^<!--#', $aMatches[0] )
			|| ( Url::isInvalid( $sUrl ) && trim( $sDeclaration ) == '' ) )
		{
			return $aMatches[0];
		}

		$sType = strcasecmp( $aMatches[1], 'script' ) == 0 ? 'js' : 'css';

		$oFilesManager = FilesManager::getInstance( $this->oParams );

		if ( $sType == 'js' && ( ! $this->oParams->get( 'javascript', '1' ) || ! $this->oProcessor->isCombineFilesSet() ) )
		{
			$deferred = $oFilesManager->isFileDeferred( $aMatches[0] );

			Helper::addHttp2Push( $sUrl, 'script', $deferred );

			return $aMatches[0];
		}

		if ( $sType == 'css' && ( ! $this->oParams->get( 'css', '1' ) || ! $this->oProcessor->isCombineFilesSet() ) )
		{
			Helper::addHttp2Push( $sUrl, 'style' );

			return $aMatches[0];
		}

		$oFilesManager->setExcludes( $this->aExcludes[ $this->sSection ] );

		return $oFilesManager->processFiles( $sType, $aMatches );
	}

	public function setSection( $sSection )
	{
		$this->sSection = $sSection;
	}
}
