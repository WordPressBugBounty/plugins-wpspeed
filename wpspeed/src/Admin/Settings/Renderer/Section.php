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
namespace WPSpeed\Admin\Settings\Renderer;

use WPSpeed\Admin\Settings\TabContent;

class Section {
	private static function new() {
		return <<<HTML
		<span class="badge bg-danger">New!</span>
HTML;
	}
	/**
	 * AUTO CONFIGURATION SECTION
	 */
	public static function autoconfigurationSection() {
		echo TabContent::addTab ( 'autoconfiguration-tab', true );
		
		$title = __ ( 'Auto configuration', 'wpspeed' );
		$description = __ ( 'Automatically configure the plugin and test your Google PageSpeed score', 'wpspeed');
		
		echo TabContent::addSection ( $title, $description, 'autoconfiguration' );
	}
	
	/**
	 * GENERAL TAB
	 */
	public static function generalSection() {
		echo TabContent::addTab ( 'general-tab' );

		$title = __ ( 'General', 'wpspeed' );
		$description = __ ( 'Manage the plugin stats', 'wpspeed');

		echo TabContent::addSection ( $title, $description, 'general' );
	}
	
	/**
	 * ASSETS INCLUSIONS
	 */
	public static function javascriptAutomaticSettingsSection() {
		echo TabContent::addTab ( 'assets-inclusions' );

		$title = __ ( 'Include assets', 'wpspeed' );
		$description = __ ( 'Choose assets to include in the optimization process.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'includeassets' );
	}
	/**
	 * ASSETS EXCLUSIONS
	 */
	public static function excludeCssSection() {
		echo TabContent::addTab ( 'assets-exclusions' );

		$title = __ ( 'Exclude CSS', 'wpspeed' );
		$description = __ ( 'Exclude CSS files to preserve the original ones and solve conflicts. It\'s possible to select a file name from the list of options or specify additional ones and hit \'Enter\'.' );

		echo TabContent::addSection ( $title, $description, 'excludecss' );
	}
	/**
	 * CSS
	 */
	public static function removeCssSection() {
		echo TabContent::addTab ( 'remove-tab' );

		$title = __ ( 'Remove CSS Files', 'wpspeed' );
		$description = __ ( 'You can remove and prevent CSS files from loading on the page if they\'re optional or not being used at all to speed up page load and rendering. Pay attention that removing required files could break your pages.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'removecssfiles' );
	}
	/**
	 * JAVASCRIPT
	 */
	public static function dontMoveSection() {
		$title = __ ( 'Keep Original Position', 'wpspeed' );
		$description = __ ( 'Keep the original position for Javascript files and scripts.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'keeporiginalposition' );
	}
	/**
	 * PAGE CACHE
	 */
	public static function pageCacheSection() {
		echo TabContent::addTab ( 'page-cache-tab' );

		$title = __ ( 'Page Cache', 'wpspeed' );
		$description = __ ( 'The HTML source code of pages can be fully cached to significantly speed up the page load. Keep the page cache disabled or clear it while configuring the plugin or making changes to the site.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'pagecache' );
	}
	/**
	 * COMBINE IMAGES
	 */
	public static function spriteGeneratorSection() {
		echo TabContent::addTab ( 'media-tab' );

		$title = __ ( 'Combine settings', 'wpspeed' );
		$description = __ ( 'Images loaded through CSS background styles can be combined into one single image.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'mediacombinesettings' );
	}
	/**
	 * LAZY LOAD
	 */
	public static function lazyLoadSection() {
		echo TabContent::addTab ( 'lazy-load-tab' );

		$title = __ ( 'Lazy-Load Images And Iframes', 'wpspeed' );
		$description = __ ( 'The lazy-load reduces the loading time of the page by displaying elements when the user scrolls down the page.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'lazyload' );
	}
	/**
	 * HTTP/2
	 */
	public static function http2PushSection() {
		echo TabContent::addTab ( 'http2-tab' );

		$title = __ ( 'HTTP/2 Settings', 'wpspeed' );
		$description = __ ( 'HTTP/2 Server Push allows an HTTP/2-compliant server to send resources to a HTTP/2-compliant client before the client requests them.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'http2' );
	}
	/**
	 * CDN
	 */
	public static function cdnSection() {
		echo TabContent::addTab ( 'cdn-tab' );

		$title = __ ( 'CDN Settings', 'wpspeed' );
		$description = __ ( 'Enter CDN domains to have the plugin load all static files from these external domains.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'cdntab' );
	}
	/**
	 * OPTIMIZE IMAGES
	 */
	public static function addImageAttributesSection() {
		echo TabContent::addTab ( 'optimize-image-tab' );

		$title = __ ( 'Optimize Heavy Images', 'wpspeed' );
		$description = __ ( 'The images optimization allows to reduce the size of large images that can be converted, rescaled and resized on the fly.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'optimizeimages' );
	}
	/**
	 * ADAPTIVE CONTENTS
	 */
	public static function addAdaptiveContentsSection() {
		echo TabContent::addTab ( 'adaptive-contents-tab' );
		
		$title = __ ( 'Adaptive Contents Settings', 'wpspeed' );
		$description = __ ( 'Adapt your assets and contents for bots and PageSpeed tools.', 'wpspeed' );
		
		echo TabContent::addSection ( $title, $description, 'adaptive' );
	}
	/**
	 * ADVANCED
	 */
	public static function optimizeCssDeliverySection() {
		echo TabContent::addTab ( 'miscellaneous-tab' );

		$title = __ ( 'Extract Basic CSS Styles', 'wpspeed' );
		$description = __ ( 'Extract basic CSS styles required to format the page above the fold and put this in a &lt;style&gt; element inside the &lt;head&gt; section of the HTML to prevent \'render-blocking\'.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'advancedtab' );
	}
	public static function reduceDomSection() {
		$title = __ ( 'Reduce DOM Tree', 'wpspeed' );
		$description = __ ( 'HTML5 DOM elements exceeding the limit of 600 below the fold will be removed and loaded asynchronously using Javascript after that the page has been fully rendered.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'reducedom' );
	}
	public static function minifyAssets() {
		$title = __ ( 'Minify Assets', 'wpspeed' );
		$description = __ ( 'CSS, Javascript and HTML code can be minified and optimized.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'minifyassets' );
	}
	public static function excludeMenuItemsSection() {
		$title = __ ( 'Exclude Urls', 'wpspeed' );
		$description = __ ( 'Disable the plugin optimization for specific urls.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'excludeurls' );
	}
	public static function combineCssJsSection() {
		$title = __ ( 'Combine Assets', 'wpspeed' );
		$description = __ ( 'Manage the combining of CSS and Javascript files.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'combineassets' );
	}
	public static function combineCssJsAutoSection() {
		$title = __ ( 'Optimization Settings', 'wpspeed' );
		$description = __ ( 'Manage optimizations settings such as cache, position of scripts and fonts loading.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'optimizationsettings' );
	}
	public static function excludePeoSection() {
		$title = __ ( 'Exclude JS With Order', 'wpspeed' );
		$description = __ ( 'If you experience conflicts, choose Javascript files to exclude from the combine functionality preserving their execution order as they appear on the page.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'excludejsorder' );
	}
	public static function excludeIeoSection() {
		$title = __ ( 'Exclude JS Without Order', 'wpspeed' );
		$description = __ ( 'If you experience conflicts, choose Javascript files to exclude from the combine functionality without preserving their execution order as they appear on the page.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'excludejswithoutorder' );
	}
	public static function removeJsSection() {
		$title = __ ( 'Remove Javascript Files', 'wpspeed' );
		$description = __ ( 'You can remove and prevent Javascript files from loading on the page if they\'re optional or not being used at all to speed up page load and rendering. Pay attention that removing required files could break your pages.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'removejsfiles' );
	}
	
	public static function addCustomJsSection() {
		$title = __ ( 'Add custom Javascript', 'wpspeed' );
		$description = __ ( 'You can add custom Javascript code that will be included within the compiled file.', 'wpspeed' );
		
		echo TabContent::addSection ( $title, $description, 'addcustomjs' );
	}
	
	public static function addCustomCssSection() {
		$title = __ ( 'Add custom CSS', 'wpspeed' );
		$description = __ ( 'You can add custom CSS code that will be included within the compiled file.', 'wpspeed' );
		
		echo TabContent::addSection ( $title, $description, 'addcustomcss' );
	}
	
	public static function autoApiSection() {
		$title = __ ( 'Optimize Images By Pages' );
		$description = __ ( 'The plugin will scan the pages of your website for you to find the images to optimize. (Currently only the Main Menu). You don\'t need to select them beforehand.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description );
	}
	public static function manualApiSection() {
		$title = __ ( 'Optimize Images By Folders' );
		$description = __ ( 'Use the file tree to select the subfolders and files you want to optimize. Files will be optimized in subfolders recursively. If you want to rescale your images while optimizing, enter the new width and height in the respective columns beside each image on the right hand side.', 'wpspeed' );

		echo TabContent::addSection ( $title, $description );
	}
	public static function advancedSettings() {
		$title = __ ( 'Advanced Settings', 'wpspeed' );
		$description = __ ( 'Manage advanced settings for the plugin', 'wpspeed' );

		echo TabContent::addSection ( $title, $description, 'advancedsettings' );
	}
	public static function addImageSrcsetSection() {
		$title = __ ( 'Srcset management', 'wpspeed' );
		$description = __ ( 'This settings make it possible to automatically generate a srcset starting from the original image. When this feature is enabled, it will replace the native Wordpress \'srcset\'.', 'wpspeed' );
		
		echo TabContent::addSection ( $title, $description, 'srcsetmanage' );
	}
	public static function instantPageSection() {
		$title = __ ( 'Instant Page', 'wpspeed' );
		$description = __ ( 'Enable the Instant Page preloading before a page is opened.', 'wpspeed' );
		
		echo TabContent::addSection ( $title, $description, 'instantpage' );
	}
	public static function lazyLoadHtmlSection() {
		$title = __ ( 'Lazy-Load HTML tags', 'wpspeed' );
		$description = __ ( 'Enable the lazy-load for user-defined HTML tags and parts of a page. They will be loaded when the user scrolls down the page or after a delay.', 'wpspeed' );
		
		echo TabContent::addSection ( $title, $description, 'lazyloadhtml' );
	}
}