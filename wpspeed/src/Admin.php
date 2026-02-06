<?php
namespace WPSpeed;

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

use WPSFramework\Application\Application;
use WPSFramework\Container\Container;
use WPSFramework\Uri\Uri;
use WPSpeed\Core\Admin\Ajax\Ajax;
use WPSpeed\Admin\Settings\Html;
use WPSpeed\Admin\Settings\TabSettings;
use WPSpeed\Platform\Plugin;
use WPSpeed\Core\FileRetriever;

abstract class Admin
{

	public static function addAdminMenu()
	{
		$hook_suffix = add_options_page( __( 'WPSpeed Settings', 'wpspeed' ), 'WPSpeed Settings', 'manage_options', 'wpspeed', [
			__CLASS__,
			'loadAdminPage'
		] );

		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'loadResourceFiles' ] );
		add_action( 'admin_head-' . $hook_suffix, [ __CLASS__, 'addScriptsToHead' ] );
		add_action( 'load-' . $hook_suffix, [ __CLASS__, 'initializeSettings' ] );

		add_action( 'admin_bar_menu', [ __CLASS__, 'addMenuToAdminBar' ], 100 );
		add_action( 'admin_init', [ __CLASS__, 'checkMessages' ] );
		
		add_filter( 'plugin_action_links_' . plugin_basename(WPSPEED_FILE_PATH), [ static::class, 'add_action_link' ], 10, 2 );
	}

	public static function loadAdminPage()
	{
		try
		{
			$oContainer   = new Container();
			$oApplication = Application::getInstance( 'WPSpeedApplication', $oContainer );
			$oApplication->initialise();
			$oApplication->route();
			$oApplication->dispatch();
			$oApplication->render();
		}
		catch ( \Exception $e )
		{
			$class = get_class( $e );
			echo <<<HTML
<h1>Error initializing the WPSpeed plugin</h1>
<div class="alert alert-danger">  {$class} &mdash;  {$e->getMessage()}  </div>
<pre class="well"> {$e->getTraceAsString()} </pre>
HTML;
		}
	}

	public static function registerOptions()
	{
		register_setting( 'wpspeedOptionsPage', 'wpspeed_settings', [ 'type' => 'array' ] );
	}

	/**
	 * Adds links to Premium Support and FAQ under the plugin in the plugin overview page.
	 *
	 * @param array  $links Array of links for the plugins, adapted when the current plugin is found.
	 * @param string $file  The filename for the current plugin, which the filter loops through.
	 *
	 * @return array
	 */
	public static function add_action_link( $links, $file ) {
		// Add link to docs.
		$faq_link = '<a href="https://storejextensions.org/faq/wpspeed-faq-learn-how-to-make-wordpress-running-fast-for-compliance-with-the-google-pagespeed.html" target="_blank">' . __( 'FAQ', 'wpspeed' ) . '</a>';
		array_push( $links, $faq_link );
		
		$docs_link = '<a href="https://storejextensions.org/wpspeed-documentation.html" target="_blank">' . __( 'PDF docs', 'wpspeed' ) . '</a>';
		array_push( $links, $docs_link );
		
		// Add link to premium landing page.
		$premium_link = '<a style="font-weight: bold;" href="https://storejextensions.org/extensions/wpspeed.html" target="_blank">' . __( 'Upgrade to PRO', 'wpspeed' ) . '</a>';
		array_unshift( $links, $premium_link );
		
		return $links;
	}
	
	public static function loadResourceFiles( $hook )
	{
		if ( 'settings_page_wpspeed' != $hook )
		{
			return;
		}
		
		// Remove any other Select2 assets
		add_filter( 'style_loader_tag', function( $html, $handle, $href, $media ) {
			$is_select2 = stripos($handle, 'select2') !== false || stripos($href, 'select2') !== false;
			$is_wpspeed = strpos($handle, 'wpspeed-') === 0;
			if ( $is_select2 && ! $is_wpspeed ) {
				return '';
			}
			return $html;
		}, 1000, 4 );
		add_filter( 'script_loader_tag', function( $html, $handle, $src ) {
			$is_select2 = stripos($handle, 'select2') !== false || stripos($src, 'select2') !== false;
			$is_wpspeed = strpos($handle, 'wpspeed-') === 0;
			
			if ( $is_select2 && ! $is_wpspeed ) {
				return '';
			}
			return $html;
		}, 1000, 3 );

		wp_enqueue_style( 'wpspeed-bootstrap-css' );
		wp_enqueue_style( 'wpspeed-verticaltabs-css' );
		wp_enqueue_style( 'wpspeed-admin-css' );
		wp_enqueue_style( 'wpspeed-fonts-css' );
		wp_enqueue_style( 'wpspeed-select2-css' );
		wp_enqueue_style( 'wpspeed-wordpress-css' );

		wp_enqueue_script( 'wpspeed-adminutilities-js' );
		wp_enqueue_script( 'wpspeed-bootstrap-js' );
		wp_enqueue_script( 'wpspeed-adminutility-js' );
		wp_enqueue_script( 'wpspeed-multiselect-js' );

		wp_enqueue_script( 'wpspeed-select2-js' );
		wp_enqueue_script( 'wpspeed-collapsible-js' );
	}

	public static function initializeSettings()
	{
		//Css files
		wp_register_style( 'wpspeed-bootstrap-css', WPSPEED_URL . 'media/bootstrap/css/bootstrap.css', [], WPSPEED_VERSION );

		wp_register_style( 'wpspeed-fonts-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css' );
		wp_register_style( 'wpspeed-select2-css', WPSPEED_URL . 'media/select2/select2.min.css', [], WPSPEED_VERSION );
		wp_register_style( 'wpspeed-wordpress-css', WPSPEED_URL . 'media/css/admin.css', [], WPSPEED_VERSION );

		//Javascript files
		wp_register_script( 'wpspeed-bootstrap-js', WPSPEED_URL . 'media/bootstrap/js/bootstrap.bundle.min.js', [ 'jquery' ], WPSPEED_VERSION, true );
		wp_register_script( 'wpspeed-adminutilities-js', WPSPEED_URL . 'media/js/adminutilities.js', [ 'jquery' ], WPSPEED_VERSION, true );
		wp_register_script( 'wpspeed-adminutility-js', WPSPEED_URL . 'media/core/js/admin-utility.js', [ 'jquery' ], WPSPEED_VERSION, true );
		wp_register_script( 'wpspeed-multiselect-js', WPSPEED_URL . 'media/core/js/multiselect.js', [
			'jquery',
			'wpspeed-adminutility-js',
			'wpspeed-adminutilities-js'
		], WPSPEED_VERSION, true );
		wp_register_script( 'wpspeed-select2-js', WPSPEED_URL . 'media/select2/select2.min.js', [ 'jquery' ], WPSPEED_VERSION, true );

		$aSettingsArray = TabSettings::getSettingsArray();

		foreach ( $aSettingsArray as $section => $aSettings )
		{
			add_settings_section( 'wpspeed_' . $section . '_section', '', [
				'\\WPSpeed\\Admin\\Settings\\Renderer\\Section',
				$section
			], 'wpspeedOptionsPage' );

			/**
			 * $aArgs = [
			 *        0 => title,
			 *        1 => description,
			 *        2 => new
			 * ]
			 */
			foreach ( $aSettings as $setting => $aArgs )
			{
				list( $title, $description, $new ) = array_pad( $aArgs, 3, 0 );

				$id    = 'wpspeed_' . $setting;
				$title = Html::description( $title, $description, $new );
				$args  = [];

				$aClasses = self::getSettingsClassMap();

				if ( isset( $aClasses[$setting] ) )
				{
					$args['class'] = $aClasses[$setting];
				}

				add_settings_field( $id, $title, [
					'\\WPSpeed\\Admin\\Settings\\Renderer\\Setting',
					$setting
				], 'wpspeedOptionsPage', 'wpspeed_' . $section . '_section', $args );
			}
		}
	}

	public static function addScriptsToHead()
	{
		
		echo <<<HTML
		<script type="text/javascript">
		(function($){
			$(document).ready(function () {
					$(".select2-ctrl").select2({
					    tags: true,
					    tokenSeparators: [',']
					})

					$('.hasPopover').popover({
						container: 'body',
						placement: 'bottom',
						trigger: 'hover focus',
						html: true
					})
				});
			})(jQuery);
		 </script>
                
HTML;
	}
	
	private static function getSettingsClassMap()
	{
		return [
			'pro_http2_file_types' => 'wpspeed-wp-checkboxes-grid-wrapper columns-4',
			'staticfiles'          => 'wpspeed-wp-checkboxes-grid-wrapper columns-5',
			'pro_staticfiles_2'    => 'wpspeed-wp-checkboxes-grid-wrapper columns-5',
			'pro_staticfiles_3'    => 'wpspeed-wp-checkboxes-grid-wrapper columns-5',
			'pro_html_sections'    => 'wpspeed-wp-checkboxes-grid-wrapper columns-5 width-400'
		];
	}

	public static function addMenuToAdminBar( $admin_bar )
	{
		if ( ! current_user_can( 'manage_options' ) )
		{
			return;
		}

		$aArgs = [
			'id'     => 'wpspeed-settings',
			'title'  => __( '<span class="ab-icon dashicons-admin-settings"></span>WPSpeed', 'wpspeed' ),
			'href'   => add_query_arg( [
				'page' => 'wpspeed',
			], admin_url( 'options-general.php' ) )
		];

		$admin_bar->add_node( $aArgs );
		
		$nonce = wp_create_nonce('cleancache');
		$aArgs = [
			'id'     => 'wpspeed-clear-cache',
			'title'  => __( '<span class="ab-icon dashicons-trash"></span>WPSpeed cache', 'wpspeed' ),
			'href'   => add_query_arg( [
					'page' => 'wpspeed&task=cleancache&_wpnonce=' . $nonce,
			], admin_url( 'options-general.php' ) )
		];
		
		$admin_bar->add_node( $aArgs );
	}

	public static function getCurrentAdminUri()
	{
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );

		if ( ! $uri )
		{
			return '';
		}

		return $uri;
	}

	public static function checkMessages()
	{
		if ( get_transient( 'wpspeed_notices' ) )
		{
			add_action( 'admin_notices', [ __CLASS__, 'publishAdminNotices' ] );
		}
	}

	public static function publishAdminNotices()
	{
		$oContainer = new Container();

		try
		{
			/** @var \WPSpeed\Admin\Application $oApplication */
			$oApplication = Application::getInstance( 'WPSpeedApplication', $oContainer );
			$oApplication->publishMessages( $oApplication->getMessageQueue() );
		}
		catch ( WPSFramework\Exception\App $e )
		{
		}
	}

	public static function loadActionLinks( $links, $file )
	{
		static $this_plugin;

		if ( ! $this_plugin )
		{
			$this_plugin = plugin_basename( WPSPEED_FILE_PATH );
		}

		if ( $file == $this_plugin )
		{
			$settings_link = '<a href="' . admin_url( 'options-general.php?page=wpspeed' ) . '">' . __( 'Settings' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	public static function doAjaxMultiSelect()
	{
		echo Ajax::getInstance( 'MultiSelect' )->run();
		die();
	}
	
	public static function executePageSpeedTest()
	{
		$params = Plugin::getPluginParams ();
		$container = new Container();
		$application = Application::getInstance( 'WPSpeedApplication', $container );
		
		// Ensure that at least a language is available for the backend and locale sent to Google otherwise the API fails
		$langTag = get_bloginfo ( 'language' );
		if($langTag) {
			$explodedLangTag = explode ( '-', $langTag );
			$locale = array_shift ( $explodedLangTag );
		} else {
			$locale = 'en';
		}
		
		$response = new \stdClass();
		
		// Build the purified domain to scrape using the host only
		$linkUrl = trim($params->get('pagespeedtest_domain_url', get_site_url()));
		if(!$linkUrl) {
			$linkUrl = get_site_url();
		}
		$hostDomain = rawurlencode ( $linkUrl );
		$customApiKey = trim($params->get ( 'google_pagespeed_api_key', ''));
		$apiKey = $customApiKey ? $customApiKey : 'AIzaSyDBN6utYmIBNQ2IVlLcY7-42S9GuKHBIfQ';
		
		$urlDesktop = "https://content.googleapis.com/pagespeedonline/v5/runPagespeed?url=$hostDomain&key=$apiKey&strategy=desktop&category=performance&locale=" . $locale;
		$urlMobile = "https://content.googleapis.com/pagespeedonline/v5/runPagespeed?url=$hostDomain&key=$apiKey&strategy=mobile&category=performance&locale=" . $locale;
		
		try {
			// Fetch remote data to scrape
			$httpResponseDesktop = wp_remote_get( $urlDesktop, array('timeout'=>120));
			$httpResponseMobile = wp_remote_get ( $urlMobile, array('timeout'=>120));
			
			// Check if HTTP status code is 200 OK
			if (!$httpResponseDesktop instanceof \WP_Error) {
				$decodedApiResponse = json_decode($httpResponseDesktop['body'], true);
				if(is_array($decodedApiResponse)) {
					// Calculate the score category, range, colors for labels and sliders
					$response->pagespeedDesktop = isset($decodedApiResponse['lighthouseResult']['categories']['performance']) ? (int)($decodedApiResponse['lighthouseResult']['categories']['performance']['score'] * 100) : -1;
					
					// Throw exception on failure results
					if($response->pagespeedDesktop == -1) {
						throw new \Exception();
					}
					
					$response->fcpDesktop = number_format($decodedApiResponse['lighthouseResult']['audits']['first-contentful-paint']['numericValue'] / 1000, 1);
					$response->siDesktop = number_format($decodedApiResponse['lighthouseResult']['audits']['speed-index']['numericValue'] / 1000, 1);
					$response->lcpDesktop = number_format($decodedApiResponse['lighthouseResult']['audits']['largest-contentful-paint']['numericValue'] / 1000, 1);
					
					$response->interactiveDesktop = number_format($decodedApiResponse['lighthouseResult']['audits']['interactive']['numericValue'] / 1000, 1);
					$response->tbtDesktop = intval($decodedApiResponse['lighthouseResult']['audits']['total-blocking-time']['numericValue']);
					$response->clsDesktop = number_format($decodedApiResponse['lighthouseResult']['audits']['cumulative-layout-shift']['numericValue'], 3);
					
					$response->screenShotDesktop = $decodedApiResponse['lighthouseResult']['audits']['final-screenshot']['details']['data'];
				}
			}
			// Check if HTTP status code is 200 OK
			if (!$httpResponseMobile instanceof \WP_Error) {
				$decodedApiResponse = json_decode($httpResponseMobile['body'], true);
				if(is_array($decodedApiResponse)) {
					// Calculate the score category, range, colors for labels and sliders
					$response->pagespeedMobile = isset($decodedApiResponse['lighthouseResult']['categories']['performance']) ? (int)($decodedApiResponse['lighthouseResult']['categories']['performance']['score'] * 100) : -1;
					
					// Throw exception on failure results
					if($response->pagespeedMobile == -1) {
						throw new \Exception();
					}
					
					$response->fcpMobile = number_format($decodedApiResponse['lighthouseResult']['audits']['first-contentful-paint']['numericValue'] / 1000, 1);
					$response->siMobile = number_format($decodedApiResponse['lighthouseResult']['audits']['speed-index']['numericValue'] / 1000, 1);
					$response->lcpMobile = number_format($decodedApiResponse['lighthouseResult']['audits']['largest-contentful-paint']['numericValue'] / 1000, 1);
					
					$response->interactiveMobile = number_format($decodedApiResponse['lighthouseResult']['audits']['interactive']['numericValue'] / 1000, 1);
					$response->tbtMobile = intval($decodedApiResponse['lighthouseResult']['audits']['total-blocking-time']['numericValue']);
					$response->clsMobile = number_format($decodedApiResponse['lighthouseResult']['audits']['cumulative-layout-shift']['numericValue'], 3);
					
					$response->screenShotMobile = $decodedApiResponse['lighthouseResult']['audits']['final-screenshot']['details']['data'];
				}
			}
			
			$response->analyzedUrl = $linkUrl;
		} catch ( \Exception $e ) {
			// Go on with the next API without blocking exception
			$response->success = false;
		}
		
		$response->success = true;
		
		echo json_encode($response);
		die();
	}
}