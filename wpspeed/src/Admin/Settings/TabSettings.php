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
namespace WPSpeed\Admin\Settings;

class TabSettings {
	public static function getSettingsArray() {
		$aTabs = get_class_methods ( __CLASS__ );
		$aSettingsArray = [ ];

		foreach ( $aTabs as $tab ) {
			if ($tab == str_replace ( __CLASS__ . '::', '', __METHOD__ )) {
				continue;
			}

			$aSettingsArray = array_merge ( $aSettingsArray, self::$tab () );
		}

		return $aSettingsArray;
	}
	public static function generalTab() {
		return [ 
				/**
				 * Miscellaneous
				 */
				'generalSection' => [
						'registration_email' => [
								'',
								'',
								true
						]
				],
				/**
				 * Combine CSS JS
				 */
				'combineCssJsSection' => [ 
						'combine_files_enable' => [ 
								__ ( 'Enable combining of CSS and Javascript files', 'wpspeed' ),
								__ ( 'If enabled, multiple CSS or Javascript files will be combined into a single file or grouped in as few files as possible. This feature allows to reduce the number of HTTP requests, the total page size and loading time.')
						],
						'css' => [ 
								__ ( 'Combine CSS files', 'wpspeed' ),
								__ ( 'If enabled, multiple CSS files will be combined into a single file. The single file will replace all the original multiple files.', 'wpspeed' )
						],
						'javascript' => [ 
								__ ( 'Combine Javascript files', 'wpspeed' ),
								__ ( 'If enabled, multiple Javascript files will be combined into a single file. In the case that it is necessary to exclude certain files while preserving the execution order, the files will be grouped in as few files as possible. Combined files will replace all the original multiple files, unless they have been excluded from the combine functionality.', 'wpspeed' )
						]
				],
				/**
				 * Minify Assets
				 */
				'minifyAssets' => [ 
						'css_minify' => [ 
								__ ( 'Minify CSS', 'wpspeed' ),
								__ ( 'Whitespaces and comments will be removed from the combined CSS file, this helps to reduce the file size, save bandwidth and improve the loading time. NOTICE: this applies only to resulting combined CSS files and not to original files, thus if the \'combine\' feature is not enabled this setting won\'t have any effect.', 'wpspeed' )
						],
						'js_minify' => [ 
								__ ( 'Minify javascript', 'wpspeed' ),
								__ ( 'Whitespaces and comments will be removed from the combined JS file, this helps to reduce the file size, save bandwidth and improve the loading time. NOTICE: this applies only to resulting combined JS files and not to original files, thus if the \'combine\' feature is not enabled this setting won\'t have any effect.', 'wpspeed' )
						],
						'html_minify' => [ 
								__ ( 'Minify HTML', 'wpspeed' ),
								__ ( 'Whitespaces and comments will be removed from the HTML source code, this helps to save bandwidth and improve the loading time.', 'wpspeed' )
						],
						'html_minify_level' => [ 
								__ ( 'HTML Minification level', 'wpspeed' ),
								__ ( 'Choose the level of minification for HTML. Using the \'Low\' level, multiple whitespaces outside of elements will be reduced to a single one. Using the \'Normal\' level comments and all not needed whitespaces or carriage returns will be removed. Using the \'High\' level even quotes or double quotes from around attributes will be removed. CAUTION: using the \'High\' level could cause issues with third-party plugins; if you experience this kind of problems switch back to \'Normal\'.', 'wpspeed' )
						]
				],

				/**
				 * Combine Css Js Auto Settings
				 */
				'combineCssJsAutoSection' => [ 
						'cache_lifetime' => [ 
								__ ( 'Cache lifetime', 'wpspeed' ),
								__ ( 'You can set the cache lifetime in minutes for the combined files. If you\'re using a third-party Page Cache plugin, be sure to set this value higher than the lifetime set in the Page Cache plugin. If you experience issues with a huge amount of cache being generated, then decrease this value to reduce the cache size.', 'wpspeed' )
						],
						'bottom_js' => [ 
								__ ( 'Combined Javascript at closing body tag', 'wpspeed' ),
								__ ( 'If enabled, the combined Javascript file will be positioned at the bottom of the page just before the closing body tag. In the case that multiple combined Javascript files have been generated, only the last one will be placed at the bottom of the page. Additionally thanks to this option, even files positioned within the \'body\' section will be included in the combined file.', 'wpspeed' )
						],
						'loadAsynchronous' => [ 
								__ ( 'Load Javascript files asynchronously', 'wpspeed' ),
								__ ( 'If enabled, Javascript will be loaded asyncronously to avoid render blocking resources. If there is only one combined file placed at the bottom of the page without any excluded script it will be loaded using the \'async\' attribute. If instead there are excluded scripts and the order of execution must be preserved it will be loaded using the \'defer\' attribute.' )
						],
						'font_display_swap' => [ 
								__ ( 'Visible text during fonts load', 'wpspeed' ),
								__ ( 'If enabled, this function will fix the PageSpeed audit: \'Ensure text remains visible during webfont load\'. The easiest way to avoid showing invisible text while custom fonts load is to temporarily show a system font. When this option is enabled a special CSS rule \'font-display: swap\' will be included in each @font-face style, so that the flash of invisible text won\'t happen in most modern browsers. The font-display API specifies how a font is displayed, \'swap\' tells the browser that text using the font should be displayed immediately using a system font. Once the custom font is ready, it replaces the system font.' )
						],
						'defer_combined_styles' => [
								__ ( 'Defer combined styles', 'wpspeed' ),
								__ ( 'If enabled, the combined CSS file resulting from the merge of original ones will be loaded in a special \'preload\' mode to eliminate render-blocking resources and improve the First Contentful Paint (FCP).' )
						]
				]
		];
	}
	public static function removeTab() {
		return [ 
				/**
				 * Remove CSS
				 */
				'removeCssSection' => [ 
						'remove_css' => [ 
								__ ( 'Remove unused CSS files', 'wpspeed' ),
								__ ( 'Select or add the CSS files that you want to remove. Be sure that they are optional or not used on the site.', 'wpspeed' )
						]
				],
				/**
				 * Remove JS file
				 */
				'removeJsSection' => [ 
						'remove_js' => [ 
								__ ( 'Remove unused javascript files', 'wpspeed' ),
								__ ( 'Select or add the Javascript files that you want to remove. Be sure that they are optional or not used on the site.', 'wpspeed' )
						]
				],
				/**
				 * Add custom CSS code
				 */
				'addCustomJsSection' => [
						'add_custom_js_code' => [
								__ ( 'Add custom JS code', 'wpspeed' ),
								__ ( 'If enabled, it\'s possible to include custom JS code within the compiled JS file.', 'wpspeed' )
						],
						'custom_js_code' => [
								__ ( 'Custom JS code', 'wpspeed' ),
								__ ( 'Enter the custom JS code to be included within the compiled JS file.', 'wpspeed' )
						]
				],
				/**
				 * Add custom JS code
				 */
				'addCustomCssSection' => [
						'add_custom_css_code' => [
								__ ( 'Add custom CSS code', 'wpspeed' ),
								__ ( 'If enabled, it\'s possible to include custom CSS code within the compiled CSS file.', 'wpspeed' )
						],
						'custom_css_code' => [
								__ ( 'Custom CSS code', 'wpspeed' ),
								__ ( 'Enter the custom CSS code to be included within the compiled CSS file.', 'wpspeed' )
						]
				]
		];
	}

	/**
	 * Settings on Page Cache
	 *
	 * @return array
	 */
	public static function pageCacheTab() {
		return [ 
				/**
				 * Page Cache
				 */
				'pageCacheSection' => [
						'cache_enable' => [
								__ ( 'Enable PHP page caching', 'wpspeed' ),
								__ ( 'When the PHP page caching is enabled, pages are served up as static HTML versions of a page in order to avoid potentially time-consuming execution of your server and database. NOTE: this feature is enabled only for logged out users.', 'wpspeed' )
						],
						'page_cache_lifetime' => [
								__ ( 'Page cache lifetime', 'wpspeed' ),
								__ ( 'The lifetime of the page cache. This value must be lower than the cache lifetime of combined files under the optimization settings. This option may not apply to the .htaccess page caching if all pages of the website are cache and the PHP execution is completely skipped.', 'wpspeed' )
						],
						'pro_cache_platform' => [
								__ ( 'Platform specific cache', 'wpspeed' ),
								__ ( 'Page caching can be different for mobile and desktop devices. Enable this option if you need to serve different contents through 2 separate cache. This option does not apply to .htaccess page caching.', 'wpspeed' )
						],
						'cache_exclude' => [
								__ ( 'Exclude page cache by URL', 'wpspeed' ),
								__ ( 'Enter a substring of each url that you want to exclude from the page cache. It\'s not needed to enter the complete url for the matching, but only a part of it. Add a string and hit \'Enter\'.', 'wpspeed' )
						],
						'delete_all_cache' => [
								__ ( 'Delete all cache folders', 'wpspeed' ),
								__ ( 'If enabled, WPSpeed will delete every cache folder inside wp-content/cache/, including those created by other plugins. This may solve conflicts but can also cause issues with third-party caches. Disable to limit deletions only to the WPSpeed cache.', 'wpspeed' )
						]
				],
				'instantPageSection' => [
						'enable_instant_page' => [
								__ ( 'Enable Instant Page preloading', 'wpspeed' ),
								__ ( 'Instant Page uses just-in-time preloading. This feature, compatible with both desktop and mobile devices, preloads a page right before a user clicks on it. Pages are preloaded only when there\'s a good chance that a user will visit them, and only the HTML is preloaded, being respectful of your users and servers bandwidth and CPU. Preloading happens just a few milliseconds before than a user opens a page thanks to the special \'Prefetch\' metatag, as a result Instant Page makes you pages feel instant to the human brain even on 3G.', 'wpspeed' )
						],
						'instant_page_delay' => [
								__ ( 'Preload delay', 'wpspeed' ),
								__ ( 'By default, Instant Page preloads 65 ms fast after hovering a link and when a mobile user starts touching their display. For some sites with a lot of huge click targets, it\'s possible to increase the delay on hover to preload 150 ms slow.', 'wpspeed' )
						]
				]
		];
	}

	/**
	 * Settings on Media Tab
	 *
	 * @return array
	 */
	public static function lazyLoadTab() {
		return [
				/**
				 * Lazy-Load Images
				 */
				'lazyLoadSection' => [
						'lazyload_enable' => [
								__ ( 'Enable lazy-load for images and iframes', 'wpspeed' ),
								__ ( 'Enable the lazy-load for images and iframes when the user scrolls down to view them. This feature reduces the loading time of the page.', 'wpspeed' )
						],
						'lazyload_autosize' => [
								__ ( 'Autosize images', 'wpspeed' ),
								__ ( 'Enable this option if you experience an incorrect size of images or an empty space around them.' )
						],
						'pro_lazyload_effects' => [
								__ ( 'Enable effects', 'wpspeed' ),
								__ ( 'If enabled, a fade-in effects will be applied to lazy-loaded images when they are scrolled into view.', 'wpspeed' )
						],
						'pro_lazyload_iframe' => [
								__ ( 'Include iframes', 'wpspeed' ),
								__ ( 'If enabled, also iframes will be lazy-loaded.', 'wpspeed' )
						],
						'pro_lazyload_bgimages' => [
								__ ( 'Include CSS background  images', 'wpspeed' ),
								__ ( 'If enabled, also CSS background images will be lazy-loaded in the same way of normal images.', 'wpspeed' )
						],
						'pro_lazyload_audiovideo' => [
								__ ( 'Include audio/video tags', 'wpspeed' ),
								__ ( 'If enabled, also audio/video tags will be lazy-loaded.' )
						],
						'disableCoreLazyload' => [
								__ ( 'Disable core lazy-load', 'wpspeed' ),
								__ ( 'If enabled, the WordPress core lazy-load will be disabled. This is helpful to improve the LCP by disabling the lazy-load of images displayed above the fold.' )
						],
						'excludeLazyLoad' => [
								__ ( 'Exclude images by URL', 'wpspeed' ),
								__ ( 'Choose URLs of the images that you want to exclude from being lazy-loaded. It\'s possible to select a file name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'pro_excludeLazyLoadFolders' => [
								__ ( 'Exclude images by folder', 'wpspeed' ),
								__ ( 'Choose folders of the images that you want to exclude from being lazy-loaded. It\'s possible to select a folder name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'pro_excludeLazyLoadClass' => [
								__ ( 'Exclude images by CSS class', 'wpspeed' ),
								__ ( 'Choose classes of the images that you want to exclude from being lazy-loaded. A class must be declared in the tag of an <span class="notranslate">\'&lt;img&gt;\'</span> element. It\'s possible to select a class name or attribute from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'pro_excludeLazyLoadApplyFetchPriority' => [
								__( 'Apply fetchpriority to excluded images', 'wpspeed' ),
								__( 'When enabled, images excluded from lazy loading will be loaded eagerly and marked with <span class="notranslate">fetchpriority="high"</span>. This helps prioritize above-the-fold or LCP images for faster rendering.', 'wpspeed' )
						]
				],
				/**
				 * Lazy-Load Images
				 */
				'lazyLoadHtmlSection' => [
					'lazyload_html_enable' => [
							__ ( 'Enable lazy-loading for HTML elements', 'wpspeed' ),
							__ ( 'Choose if enable the lazy-loading for HTML elements based on the CSS selectors parameter. This feature requires "Minify HTML" enabled in order to work.', 'wpspeed' )
					],
					'lazyload_html_css_selector' => [
							__ ( 'CSS selectors for lazy-loaded elements', 'wpspeed' ),
							__ ( 'Enter one or more CSS selectors comma separated to match certain HTML elements that should be lazy-loaded, for example using the selector \'img.myclass,div.myclass\'. This option could be useful for example to lazy-load the LCP element of a certain page.', 'wpspeed' )
					],
					'lazyload_method' => [
							__ ( 'Lazy-load method', 'wpspeed' ),
							__ ( 'Choose the preferred method used to reveal lazy-loaded elements. Hidden elements can be lazy-loaded using scrolling or a delay.', 'wpspeed' )
					],
					'lazyload_html_delay' => [
							__ ( 'Lazy-load delay', 'wpspeed' ),
							__ ( 'When the selected lazy-load method is \'Delay\' then the lazy-loading of selected HTML elements will be delayed of a certain amount of milliseconds, by default it\'s delayed of 3000 ms. You can change this value based on best performance reported in the PageSpeed test.', 'wpspeed' )
					],
					'lazyload_html_use_important_override' => [
							__ ( 'Use CSS override', 'wpspeed' ),
							__ ( 'If enabled, the special \'!important\' CSS override will be used to lazy-load a HTML elements. This may be needed if certain tags can\'t be lazy-loaded because of CSS styles included in the template that require to be overridden.', 'wpspeed' )
					]
				]
		];
	}
	
	/**
	 * Settings on Media Tab
	 *
	 * @return array
	 */
	public static function mediaTab() {
		return [ 
				/**
				 * Sprite Generator
				 */
				'spriteGeneratorSection' => [ 
						'csg_enable' => [ 
								__ ( 'Combine background images', 'wpspeed' ),
								__ ( 'If enabled, images loaded through CSS background styles will be combined into one single image in order to reduce HTTP requests. NOTICE: the option \'Combine CSS Files\' must be enabled to have this feature working".', 'wpspeed')
						],
						'csg_direction' => [ 
								__ ( 'Image orientation', 'wpspeed' ),
								__ ( 'Choose the best orientation used for images positioned inside the combined one.', 'wpspeed' )
						],
						'csg_wrap_images' => [ 
								__ ( 'Auto-format combined image', 'wpspeed' ),
								__ ( 'If the size of the combined image exceeds 2000px, included images will be wrapped in a new row or column.', 'wpspeed' )
						],
						'csg_exclude_images' => [ 
								__ ( 'Exclude images', 'wpspeed' ),
								__ ( 'You can exclude certain images from the combine functionality in the case that they got broken or displayed incorrectly. It\'s possible to select a file name from the list of options or specify additional ones and hit \'Enter\'".', 'wpspeed' )
						],
						'csg_include_images' => [ 
								__ ( 'Include additional images', 'wpspeed' ),
								__ ( 'Images to be combined are crawled and selected automatically. In the case that you want to include additional images that have not been found automatically, you can enter them in this parameter. It\'s possible to select a file name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						]
				]
		];
	}

	/**
	 * Settings on Http/2 tab
	 *
	 * @return array
	 */
	public static function http2PushTab() {
		return [ 
				/**
				 * Http2 Push
				 */
				'http2PushSection' => [ 
						'http2_push_enable' => [ 
								__ ( 'Enable HTTP/2 Server Push', 'wpspeed' ),
								__ ( 'HTTP/2 Server Push allows an HTTP/2-compliant server to send resources to a HTTP/2-compliant client before the client requests them. It is, for the most part, a performance technique that can be helpful in loading resources preemptively. If this option is enabled, WPSpeed will communicate to the server to operate with this technique, keep in mind that this only works on a HTTP/2 compliant server.', 'wpspeed' )
						],
						'pro_http2_exclude_deferred' => [ 
								__ ( 'Exclude asyncronous assets', 'wpspeed' ),
								__ ( 'If enabled, assets that are deferred, loaded asynchronously or lazy-loaded will be excluded.', 'wpspeed' )
						],
						'pro_http2_push_cdn' => [ 
								__ ( 'Include CDN files', 'wpspeed' ),
								__ ( 'Files loaded over your CDN domains will also be included.', 'wpspeed' )
						],
						'pro_http2_file_types' => [ 
								__ ( 'File types', 'wpspeed' ),
								__ ( 'Select the type of files to preload through HTTP/2.', 'wpspeed' )
						],
						'pro_http2_include' => [ 
								__ ( 'Add custom files', 'wpspeed' ),
								__ ( 'It\'s possible to preload custom files adding their full path in this field. supported file types are: .js, .css, .webp, .gif, .png, .jpg, .woff, .woff2', 'wpspeed' )
						],
						'pro_http2_exclude' => [ 
								__ ( 'Exclude files', 'wpspeed' ),
								__ ( 'If you receive warnings in the browser console about preloaded files not used within a few seconds, you can exclude these files here.', 'wpspeed' )
						]
				]
		];
	}

	/**
	 * Settings on CDN tab
	 *
	 * @return array
	 */
	public static function cdnTab() {
		return [ 
				/**
				 * CDN
				 */
				'cdnSection' => [ 
						'cookielessdomain_enable' => [ 
								__ ( 'Enable CDN', 'wpspeed' ),
								__ ( 'If enabled, all static assets such as background images, Javascript, CSS files, etc will be loaded from a CDN domain. This requires that you have correctly setup a CDN for your website, you can configure up to 3 different CDN domains.', 'wpspeed')
						],
						'pro_cdn_preconnect' => [ 
								__ ( 'Preconnect browsers to CDN', 'wpspeed' ),
								__ ( 'If enabled, the browser will perform an early preconnect to your CDN domains to speed up loading of subsequent resources.', 'wpspeed' )
						],
						'cdn_scheme' => [ 
								__ ( 'CDN scheme', 'wpspeed' ),
								__ ( 'Select the scheme for your CDN domain.', 'wpspeed' )
						],
						'cookielessdomain' => [ 
								__ ( 'CDN Domain #1', 'wpspeed' ),
								__ ( 'Enter the CDN domain #1.', 'wpspeed' )
						],
						'staticfiles' => [ 
								__ ( 'CDN files #1', 'wpspeed' ),
								__ ( 'Select file types that you want to be loaded over a CDN with the domain #1. It\'s possible to select a value from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'pro_cookielessdomain_2' => [ 
								__ ( 'CDN Domain #2', 'wpspeed' ),
								__ ( 'Enter the CDN domain #2.', 'wpspeed' )
						],
						'pro_staticfiles_2' => [ 
								__ ( 'CDN files #2', 'wpspeed' ),
								__ ( 'Select file types that you want to be loaded over a CDN with the domain #2. It\'s possible to select a value from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'pro_cookielessdomain_3' => [ 
								__ ( 'CDN Domain #3', 'wpspeed' ),
								__ ( 'Enter the CDN domain #3.', 'wpspeed' )
						],
						'pro_staticfiles_3' => [ 
								__ ( 'CDN files #3', 'wpspeed' ),
								__ ( 'Select file types that you want to be loaded over a CDN with the domain #3. It\'s possible to select a value from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'pro_customcdnextensions' => [ 
								__ ( 'Custom CDN extensions', 'wpspeed' ),
								__ ( 'It\'s possible to add custom extensions for file types to load them through the CDN Domain #1. Add custom options and hit \'Enter\'.', 'wpspeed' )
						]
				]
		];
	}

	/**
	 * Settings on Optimize Image tab
	 *
	 * @return array
	 */
	public static function optimizeImageTab() {
		return [ 
			/**
			 * Add image Attributes
			 */
			'addImageAttributesSection' => [
					'lightimgs_status' => [
							__ ( 'Enable image optimization', 'wpspeed' ),
							__ ( 'If images optimization is enabled, images will be compressed on the fly to reduce the bandwidth usage and speed up your site. NOTICE: the quality of images may be significantly decreased on large screens.', 'wpspeed' )
					],
					'img_processing_minwidth' => [
							__ ( 'Minimum width for image optimization', 'wpspeed' ),
							__ ( 'Choose the minimum width that images must have to be optimized. You should ignore small images that are already lightweight and don\'t need any kind of optimization.', 'wpspeed' )
					],
					'img_quality' => [
							__ ( 'Quality for compressed images', 'wpspeed' ),
							__ ( 'You can choose the image quality for resultimg optimized images. High value will mean a higher quality but also higher bandwidth usage.', 'wpspeed' )
					],
					'img_resizing_switcher' => [
							__ ( 'Enable image resizing', 'wpspeed' ),
							__ ( 'You can choose if images should be resized to a lower resolution to reduce size.', 'wpspeed' )
					],
					'img_resizing' => [
							__ ( 'Resizing percentage', 'wpspeed' ),
							__ ( 'Choose the percentage to which you want to down-size your images. For example if you have an image 1500px wide and apply a resizing percentage of 50%, the image will be resized to 750px thus saving bandwidth.', 'wpspeed' )
					],
					'img_resizing_minwidth' => [
							__ ( 'Minimum width for resizing images', 'wpspeed' ),
							__ ( 'Choose the minimum width that images must have to be resized to a lower resolution. You should avoid to resize small images that are already lightweight and apply down-sizing only to large and heavyweight images.', 'wpspeed' )
					],
					'convert_all_images_to_webp' => [
							__ ( 'Convert all images to WebP', 'wpspeed' ),
							__ ( 'If enabled, all optimized images will be converted to the next generation WebP format. NOTICE: for this function to work the server PHP must include the GD library with the WebP support enabled and the \'imagewebp\' function must be available, if this is missing on your server you can contact the hosting provider.', 'wpspeed' )
					],
					'convert_all_images_to_avif' => [
							__ ( 'Convert all images to AVIF', 'wpspeed' ),
							__ ( 'If enabled, all optimized images will be converted to the next generation AVIF format. NOTICE: for this function to work the server PHP must include the GD library with the AVIF support enabled and the \'imageavif\' function must be available, if this is missing on your server you can contact the hosting provider. Commonly this feature is supported by PHP 8.1 and later.', 'wpspeed' )
					],
					'exclude_light_images_safari' => [
							__ ( 'Exclude images conversion for Safari', 'wpspeed' ),
							__ ( 'If enabled, all optimized images will not be converted to the next generation WebP/AVIF format for Safari browser on iOS devices. Safari supports WebP images since version 16.0 and AVIF images since version 16.4. Keep this option enabled for maximum compatibility with older versions of the Safari browser.', 'wpspeed' )
					],
					'optimize_css_background_images' => [
							__ ( 'Optimize CSS background images', 'wpspeed' ),
							__ ( 'If enabled, all images that are loaded through CSS files will also be optimized. The compiled CSS files will include new links to optimized images. This features requires CSS optimization to be enabled.', 'wpspeed' )
					],
					'optimize_html_background_images' => [
							__ ( 'Optimize HTML background images', 'wpspeed' ),
							__ ( 'If enabled, all images that are loaded through HTML inline styles for background will also be optimized. The minified HTML code will include new links to optimized images. This features requires HTML optimization to be enabled and set to \'High\' level.', 'wpspeed' )
					],
					'img_support_gif' => [
							__ ( 'GIF images optimization', 'wpspeed' ),
							__ ( 'Choose to optmize also GIF images on your site. Note that if this feature is enabled for animated GIF all animation frames will be lost.', 'wpspeed' )
					],
					'webservice_processing' => [
							__ ( 'Lossless optimization', 'wpspeed' ),
							__ ( 'If enabled, all images will be converted using a lossless compression provided by the third-party Resmush WebService https://resmush.it. NOTICE: using this system images won\'t never be resized, the parameter \'Quality for compressed images\' is applied only to JPG images and the conversion to the WebP format won\'t have any effect. Although it\'s possible to generate a srcset using this method it doesn\'t make much sense, indeed all images of the srcset will have the same size and quality.', 'wpspeed' )
					],
					'hash_images_algo' => [
							__ ( 'Hash method of images name', 'wpspeed' ),
							__ ( 'Choose the preferred method for the hash generation of the image name. The \'full\' method totally replaces the name of an image with a full hash. The \'partial\' method concatenates a hash to the original name of an image. The \'none\' method leaves the original name of an image unaltered, but pay attention that not using a hash could cause names collision if you have different images with the same name.', 'wpspeed' )
					],
					'preserve_cached_images' => [
							__ ( 'Preserve cached images' ),
							__ ( 'If enabled, images stored in the cache will not be deleted when clearing or regenerating the cache and will persist indefinitely. The first time a page is visited, optimized images must be created and this may slightly increase the loading time. By preserving cached images forever, once they are generated the following visits will be much faster since the optimized images are already available' )
					],
					'img_exts_excluded' => [
							__ ( 'Exclude image extensions', 'wpspeed' ),
							__ ( 'Choose by file extension if some image types should be excluded from the resizing process.', 'wpspeed' )
					],
					'img_files_excluded' => [
							__ ( 'Exclude images by name/path', 'wpspeed' ),
							__ ( 'If you need to exclude certain images from the optimization process you can enter a substring of the name or path to keep the original one. For example if you want to exclude an image at the following path: \'images/socials/mycustomimage.png\' it\'s enough to enter \'socials\' to exclude the entire folder or \'custom\' to exclude an image named \'mycustomname.png\'.', 'wpspeed' )
					],
					'img_class_excluded' => [
							__ ( 'Exclude images by class', 'wpspeed' ),
							__ ( 'Choose classes of the images that you want to exclude from being optimized. A class must be declared in the attribute of an \'&lt;img&gt;\' element. It\'s possible to select a class name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
					],
					'img_menu_excluded' => [
							__ ( 'Exclude images optimization by page URL', 'wpspeed' ),
							__ ( 'Enter a substring of each url that you want to exclude from the images optimization. It\'s not needed to enter the complete url for the matching, but only a part of it. Add a string and hit \'Enter\'.', 'wpspeed' )
					],
					'img_processing_datasrc' => [
							__ ( 'Process \'data-src\' attribute', 'wpspeed' ),
							__ ( 'If enabled, when the special \'data-src\' attribute of an image is present, it will be set to point to the optimized image. This can be useful for example for galleries that use this special attribute in order to load the optimized image instead that the original one".', 'wpspeed' )
					],
					'img_processing_datacustom' => [
							__ ( 'Process custom "data-" attributes', 'wpspeed' ),
							__ ( 'If enabled, custom "data-" attributes containing image URLs will be processed to point to optimized images. This is useful for elements that store image sources in custom attributes instead of the standard "src" or "data-src"', 'wpspeed' )
					],
					'img_attributes_enable' => [
							__ ( 'Add size attributes', 'wpspeed' ),
							__ ( 'You can choose to add width and height attributes to images for better optimization.', 'wpspeed')
					],
					'use_simplehtmldom' => [
							__ ( 'Alternative HTML parsing mode', 'wpspeed' ),
							__ ( 'Enable this option only if you notice layout issues, wrong UTF-8 characters encoding or conflicts with the default processing. It provides an alternative way to analyze and optimize your page output, which may improve compatibility with certain templates or extensions', 'wpspeed' )
					],
					'img_processing_entity_decode' => [
							__ ( 'Decode HTML entities', 'wpspeed' ),
							__ ( 'If enabled, UTF-8 characters will be decoded and preserved without encoding entities.', 'wpspeed' )
					],
					'img_processing_utf8_entity_decode' => [
							__ ( 'Decode UTF-8 HTML entities', 'wpspeed' ),
							__ ( 'If enabled, UTF-8 characters will be decoded into HTML entities. Enable this option if you experience characters on the page that are wrongly encoded.', 'wpspeed' )
					],
					'purify_string' => [
							__ ( 'Purify string', 'wpspeed' ),
							__ ( 'If you experience weird errors such as javascript code inside the page content caused by a certain script word or tag, you can specify that string in this parameter in order to remove it and avoid the conflict. This field supports regular expressions and automatically adds the escape slash to the \'/\' special character.', 'wpspeed' )
					],
					'purify_string_replacement' => [
							__ ( 'Replacement string', 'wpspeed' ),
							__ ( 'You can specify an alternative string used to replace the purified string. By default it will be completely removed and replaced with an empty string.', 'wpspeed' )
					],
					'img_processing_simplehtmldom_entity_decode' => [
							__ ( 'Decode text entities', 'wpspeed' ),
							__ ( 'Convert encoded characters into normal characters in the visible text of your pages. Attributes such as links will remain safe. Enable only if you prefer cleaner source code or need it for validation', 'wpspeed' )
					]
			],
			'addImageSrcsetSection' => [
					'img_processing_srcset' => [
							__ ( 'Create srcset', 'wpspeed' ),
							__ ( 'If enabled, an additional HTML5 \'srcset\' attribute will be added for each image that need to be optimized and WPSpeed will automatically create up to 4 differently-sized images as variations to the base image. The browser will pick up the most correct image based on the device screen size and resolution. IMPORTANT: if you activate this feature, images may be enlarged to full width because the srcset doesn\'t render images based on its own width but based on the total width of the container element; as a result you may need to rework your CSS and apply a \'max-width\' to the various images displayed via a srcset.', 'wpspeed' )
					],
					'img_processing_srcset_starting_quality' => [
							__ ( 'Initial quality value', 'wpspeed' ),
							__ ( 'Choose the value of the initial quality used for the image with the highest resolution of the srcset and for the regular fallback img tag. Clear the plugin cache after that you have changed this setting.', 'wpspeed' )
					],
					'img_processing_srcset_quality_decrease_step' => [
							__ ( 'Quality decrease', 'wpspeed' ),
							__ ( 'Choose the amount of quality decrease that each srcset image should have. Starting from the highest quality image there will be 3 more images with a lower quality reduced by this value. Clear the plugin cache after that you have changed this setting.', 'wpspeed' )
					],
					'img_processing_srcset_starting_resize' => [
							__ ( 'Initial resizing value', 'wpspeed' ),
							__ ( 'Choose the value of the initial resizing used for the image with the largest size of the srcset and for the regular fallback img tag. Clear the plugin cache after that you have changed this setting.', 'wpspeed' )
					],
					'img_processing_srcset_resize_decrease_step' => [
							__ ( 'Resizing decrease', 'wpspeed' ),
							__ ( 'Choose the amount of resizing decrease that each srcset image should have. Starting from the biggest image there will be 3 more smaller images reduced by this value. Clear the plugin cache after that you have changed this setting.', 'wpspeed' )
					],
					'img_processing_srcset_original_image' => [
							__ ( 'Standard image \'src\'', 'wpspeed' ),
							__ ( 'You can choose which image to use for the standard \'src\' attribute used as a fallback for browsers that don\'t support srcset. Choosing a lower resolution image for the \'src\' attribute could be helpful to reach a higher score during the PageSpeed test.', 'wpspeed' )
					],
					'img_processing_srcset_datasrc' => [
							__ ( 'Create \'srcset\' for \'data-src\' images', 'wpspeed' ),
							__ ( 'If enabled, JSpeed will generate a \'srcset\' attribute for images using \'data-src\' or custom \'data-\' attributes, ensuring optimized responsive images for different screen sizes and improving loading performance.', 'wpspeed' )
					]
			]
		];
	}

	/**
	 * Settings on Miscellanous tab
	 *
	 * @return array
	 */
	public static function miscellaneous() {
		return [ 
				/**
				 * Optimize CSS Delivery
				 */
				'optimizeCssDeliverySection' => [ 
						'optimizeCssDelivery_enable' => [ 
								__ ( 'Enable extraction', 'wpspeed' ),
								__ ( 'Enable the extraction of basic CSS styles and load them above the fold to avoid render blocking resources.')
						],
						'optimizeCssDelivery' => [ 
								__ ( 'Number of elements', 'wpspeed' ),
								__ ( 'Select the number of HTML elements from the top of the page that you want to analyze to find the basic CSS to be extracted from.', 'wpspeed')
						],
						'pro_remove_unused_css' => [ 
								__ ( 'Remove unused CSS', 'wpspeed' ),
								__ ( 'If enabled, the plugin will \'lazy-load\' the unused CSS to prevent unnecessary processing before than the page is loaded.', 'wpspeed' )
						],
						'optimizeCssDeliveryExcludeFontface' => [
								__ ( 'Exclude @font-face', 'wpspeed' ),
								__ ( 'If enabled, the extracted \'Above-the-fold\' CSS code will not include any external font, futher improving performance.', 'wpspeed' )
						],
						'pro_dynamic_selectors' => [ 
								__ ( 'CSS selectors', 'wpspeed' ),
								__ ( 'It could be necessary to extract the basic CSS for some custom elements. Add any substring from the CSS declaration here to have them included in the extracted CSS code and hit \'Enter\'.', 'wpspeed' )
						],
						'extractcss_menu_excluded' => [
								__ ( 'Exclude extraction by page URL', 'wpspeed' ),
								__ ( 'Enter a substring of each url that you want to exclude from the CSS extraction. It\'s not needed to enter the complete url for the matching, but only a part of it. Add a string and hit \'Enter\'.', 'wpspeed' )
						],
				],
				/**
				 * Reduce Dom Section
				 */
				'reduceDomSection' => [ 
						'pro_reduce_dom' => [ 
								__ ( 'Enable DOM reduction', 'wpspeed' ),
								__ ( 'Enable the reduction of DOM tree. If you experience conflicts or malfuctioning keep this feature disabled.', 'wpspeed')
						],
						'pro_html_sections' => [ 
								__ ( 'HTML tags to load asynchronously', 'wpspeed' ),
								__ ( 'Select which HTML elements you want to load asynchronously.' )
						]
				],
				'advancedSettings' => [
						'clear_server_cache' => [
								__ ( 'Clear server cache (Varnish)', 'wpspeed' ),
								__ ( 'If enabled, when the plugin clears its own cache it will also try to invalidate a server side caching system, such as Varnish, by making a PURGE request. This could be needed to keep synced the cache cleaning between the static files generated by the plugin and the server side cache.', 'wpspeed' )
						],
						'base64encode' => [
								__ ( 'Cache encryption', 'wpspeed' ),
								__ ( 'If enabled, all cached files will be encrypted by PHP and it won\'t be possible to read contents. This option could slow down performance and increase the cache size.', 'wpspeed' )
						],
						'debug' => [ 
								__ ( 'Debug Plugin', 'wpspeed' ),
								__ ( 'This option will enable the debug mode that adds the url of the original files inside the combined and optimized file generated by WPSpeed and enable the log system at the path "root/wp-content/plugins/wpspeed/logs". This is useful to identify and resolve conflicts.', 'wpspeed' )
						],
						'order_plugin' => [ 
								__ ( 'Order plugins', 'wpspeed' ),
								__ ( 'If enabled, the plugin automatically ensures the correct execution order with other popular caching plugins.' )
						],
						'try_catch' => [
								__ ( 'Handle Javascript errors', 'wpspeed' ),
								__ ( 'If you get Javascript errors in the browser console, you can try to enable this option to execute each combined Javascript file within a try-catch block.', 'wpspeed' )
						]
				]
		];
	}

	/**
	 * Settings on assetsInclusions tab
	 *
	 * @return array
	 */
	public static function assetsInclusions() {
		return [ 
				/**
				 * Javascript Automatic Settings
				 */
				'javascriptAutomaticSettingsSection' => [ 
						'includeAllExtensions' => [ 
								__ ( 'Include all assets', 'wpspeed' ),
								__ ( 'If enabled, all files managed by third-party plugins and external domains will be included in the combined file.', 'wpspeed' )
						],
						'replaceImports' => [ 
								__ ( 'Include imported CSS files', 'wpspeed' ),
								__ ( 'If you have CSS files that use @import statements, when this option is enabled the contents of each referenced resource will be fetched and directly included in the containing file to speed up delivering of all styles.', 'wpspeed' )
						],
						'phpAndExternal' => [ 
								__ ( 'Include PHP and external files', 'wpspeed' ),
								__ ( 'If enabled, dynamic Javascript and CSS files having a \'.php\' file extension and files pointing to external domains will be included in the combined file. This feature requires the \'PHP CURL\' library or \'allow_url_fopen\' enabled on your server for PHP.' )
						],
						'inlineStyle' => [ 
								__ ( 'Include inline CSS styles' ),
								__ ( 'If enabled, inline CSS styles will be included in the combined CSS file in the same order that they appear in the page.', 'wpspeed' )
						],
						'inlineScripts' => [ 
								__ ( 'Include inline scripts', 'wpspeed' ),
								__ ( 'If enabled, inline Javascript scripts will be included in the combined JS files in the same order that they appear in the page.', 'wpspeed' )
						]
				]
		];
	}

	/**
	 * Settings on assetsExclusions tab
	 *
	 * @return array
	 */
	public static function assetsExclusions() {
		return [ 
				/**
				 * Exclude CSS Settings
				 */
				'excludeCssSection' => [ 
						'excludeCSS' => [ 
								__ ( 'Exclude CSS files', 'wpspeed' ),
								__ ( 'Select the CSS files that you want to exclude from the combine functionality. It\'s possible to select a file name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'excludeCssComponents' => [ 
								__ ( 'Exclude CSS files by plugin', 'wpspeed' ),
								__ ( 'All CSS files that belong to the chosen extensions will be excluded from the combine functionality. It\'s possible to select an extension name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'excludeStyles' => [ 
								__ ( 'Exclude inline "style" declarations', 'wpspeed' ),
								__ ( 'Select an inline \'style\' declaration that you want to exclude from the combine functionality, you can type in a substring of the style tag or content that you want to exclude.', 'wpspeed' )
						],
						'excludeAllStyles' => [ 
								__ ( 'Exclude all inline "style" declarations', 'wpspeed' ),
								__ ( 'Exclude all inline \'style\' declaration from the combine functionality. This could reduce the amount of cache if there are inline styles that change continuously.', 'wpspeed' )
						]
				],
				/**
				 * Exclude Preserving Execution Order
				 */
				'excludePeoSection' => [ 
						'excludeJs_peo' => [ 
								__ ( 'Exclude Javascript files', 'wpspeed' ),
								__ ( 'Select the Javascript files that you want to exclude from the combine functionality preserving the execution order as they appear on the page. It\'s possible to select a file name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'excludeJsComponents_peo' => [ 
								__ ( 'Exclude Javascript files by plugin', 'wpspeed' ),
								__ ( 'All Javascript files that belong to the chosen plugin will be excluded from the combine functionality preserving the execution order as they appear on the page. It\'s possible to select a plugin name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'excludeScripts_peo' => [ 
								__ ( 'Exclude inline "script" declarations', 'wpspeed' ),
								__ ( 'Select an inline \'script\' declarations that you want to exclude from the combine functionality, you can type in a substring of the script tag or content that you want to exclude.', 'wpspeed' )
						],
						'excludeAllScripts' => [ 
								__ ( 'Exclude all inline "script" declarations', 'wpspeed' ),
								__ ( 'Exclude all inline \'script\' declaration from the combine functionality. This could reduce the amount of cache if there are inline styles that change continuously.', 'wpspeed' )
						]
				],
				/**
				 * Exclude Ignoring Excecution Order
				 */
				'excludeIeoSection' => [ 
						'excludeJs' => [ 
								__ ( 'Exclude javascript files', 'wpspeed' ),
								__ ( 'Select the Javascript files that you want to exclude from the combine functionality without preserving the execution order as they appear on the page. It\'s possible to select a file name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'excludeJsComponents' => [ 
								__ ( 'Exclude Javascript files by plugin', 'wpspeed' ),
								__ ( 'All Javascript files that belong to the chosen plugin will be excluded from the combine functionality without preserving the execution order as they appear on the page. It\'s possible to select a plugin name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
						],
						'excludeScripts' => [ 
								__ ( 'Exclude inline "script" declarations', 'wpspeed' ),
								__ ( 'Select an inline \'script\' declarations that you want to exclude from the combine functionality, you can type in a substring of the script tag or content that you want to exclude.', 'wpspeed' )
						]
				],
				/**
				 * Exclude Menu Items
				 */
				'excludeMenuItemsSection' => [ 
						'menuexcludedurl' => [ 
								__ ( 'Exclude urls', 'wpspeed' ),
								__ ( 'Enter a substring of each url that you want to exclude from the plugin optimization. It\'s not needed to enter the complete url for the matching, but only a part of it. Add a string and hit \'Enter\'.', 'wpspeed' )
						],
						'disable_logged_in_users' => [ 
								__ ( 'Exclude logged in users', 'wpspeed' ),
								__ ( 'If enabled, the plugin will be disabled for all users that are logged in.', 'wpspeed' )
						]
				],
				/**
				 * Don't Move Files To Bottom
				 */
				'dontMoveSection' => [ 
						'dontmoveJs' => [ 
								__ ( 'Javascript files', 'wpspeed' ),
								__ ( 'Excluded Javascript files are commonly moved to the bottom of the page. In the case of conflicts, it\'s possible to enter them in this setting to keep the original position in the page.', 'wpspeed' )
						],
						'dontmoveScripts' => [ 
								__ ( 'Inline scripts', 'wpspeed' ),
								__ ( 'Excluded scripts are commonly moved to the bottom of the page. In the case of conflicts, it\'s possible to enter them in this setting to keep the original position in the page.', 'wpspeed' )
						]
				]
		];
	}
	/**
	 * Settings on assetsExclusions tab
	 *
	 * @return array
	 */
	public static function adaptiveContentsTab() {
		return [
			/**
			 * Exclude CSS Settings
			 */
			'addAdaptiveContentsSection' => [
					'adaptive_contents_enable' => [
							__ ( 'Enable Adaptive Contents', 'wpspeed' ),
							__ ( 'If the Adaptive Contents feature is enabled it\'s possible to remove certain parts of HTML or CSS/JS files only for bots and page speed tools. This makes it possible to raise the page speed score without losing website functionality for regular visitors.', 'wpspeed' )
					],
					'adaptive_contents_css_selector' => [
							__ ( 'CSS selectors for elements to remove <span style=\'font-size:11px;font-weight:bold\'>(Requires \'Minify HTML\' enabled)</span>', 'wpspeed' ),
							__ ( 'Enter one or more CSS selectors comma separated to match certain HTML elements that you want to remove from the page when visited by bots and page speed tools, for example using the selector \'div.myclass\'. This is especially useful if you need to remove elements causing a high CLS value. Note that this feature requires the \'Minify HTML\' option to be enabled.', 'wpspeed' )
					],
					'adaptive_contents_bots_list' => [
							__ ( 'Bots user-agent strings', 'wpspeed' ),
							__ ( 'List of user-agent strings used by bots and page speed tools that need to be excluded. Just enter a single part of the user-agent string to select it as a regular expression is used.', 'wpspeed' )
					],
					'adaptive_contents_remove_js' => [
							__ ( 'Remove JS files', 'wpspeed' ),
							__ ( 'It\'s possible to completely remove certain JS files from the page only for bots and page speed tools to improve the following PageSpeed tests: \'Minimize main-thread work\', \'Reduce Javascript execution time\' and \'Avoid enormous network payloads\'.', 'wpspeed' )
					],
					'adaptive_contents_remove_js_files' => [
							__ ( 'Select JS files to remove', 'wpspeed' ),
							__ ( 'Select the Javascript files that you want to completely remove from the page for bots and page speed tools. It\'s possible to select a file name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
					],
					'adaptive_contents_remove_css' => [
							__ ( 'Remove CSS files', 'wpspeed' ),
							__ ( 'It\'s possible to completely remove certain CSS files from the page only for bots and page speed tools to improve the following PageSpeed tests: \'Minimize main-thread work\' and \'Avoid enormous network payloads\'.', 'wpspeed' )
					],
					'adaptive_contents_remove_css_files' => [
							__ ( 'Select CSS files to remove', 'wpspeed' ),
							__ ( 'Select the CSS files that you want to completely remove from the page only for bots and page speed tools. It\'s possible to select a file name from the list of options or specify additional ones and hit \'Enter\'.', 'wpspeed' )
					],
					'adaptive_contents_remove_all_js' => [
							__ ( 'Remove all JS', 'wpspeed' ),
							__ ( 'If enabled, all JS code will be removed for bots and page speed tools.', 'wpspeed' )
					],
					'adaptive_contents_remove_all_css' => [
							__ ( 'Remove all CSS', 'wpspeed' ),
							__ ( 'If enabled, all CSS code will be removed for bots and page speed tools.', 'wpspeed' )
					],
					'adaptive_contents_essential_css_code' => [
							__ ( 'Essential CSS code', 'wpspeed' ),
							__ ( 'It\'s possible to add a piece of essential CSS code that should be present in the lightweight version of a page generated with Adaptive Contents for bots and page speed tools. This is valuable, for example, if you choose to remove all CSS but still need some styles to preserve a basic layout or if you experience warnings in Google Search Console about \'Mobile Usability Issue - Content Wider Than Screen & Clickable Element Too Close\'.', 'wpspeed' )
					],
					'adaptive_contents_essential_js_code' => [
							__ ( 'Essential JS code', 'wpspeed' ),
							__ ( 'It\'s possible to add a piece of essential JavaScript code that should be present in the lightweight version of a page generated with Adaptive Contents for bots and page speed tools. This can be useful, for example, if you remove all JavaScript but still need minimal functionality, such as basic interactivity, lazy loading images, or avoiding errors due to missing scripts. The code must be added without the &lt;script&gt; tag and will always be added inline', 'wpspeed' )
					],
					'adaptive_contents_add_size_attributes' => [
							__ ( 'Add size attributes only for bots', 'wpspeed' ),
							__ ( 'If you have enabled the option \'Add size attributes\' under the \'Images optimization\' tab, it\'s possible to choose to add the size attributes for images only for bots using the \'Adaptive Contents\' feature. This is useful if you experience issues with the size of images being stretched on the website.', 'wpspeed' )
					],
					'adaptive_contents_optimize_images' => [
							__ ( 'Optimize images only for bots', 'wpspeed' ),
							__ ( 'If you have enabled the \'Images optimization\' feature, it\'s possible to choose to optimize images only for bots using the \'Adaptive Contents\' feature. This is useful if you experience issues or page conflicts with the images optimization on the website.', 'wpspeed' )
					],
					'adaptive_contents_extract_css' => [
							__ ( 'Extract \'Above-the-fold\' CSS only for bots', 'wpspeed' ),
							__ ( 'If you have enabled the \'Extract Above-the-fold CSS\' feature, it\'s possible to choose to run this only for bots using the \'Adaptive Contents\' feature. This is useful if you experience issues or page conflicts with CSS on the website.', 'wpspeed' )
					],
					'adaptive_contents_essential_js_code_lazyload_imgs' => [
							__ ( 'Manage lazy-loaded images', 'wpspeed' ),
							__ ( 'If you have images lazy-loaded by third-party extensions, this option ensures that they are always loaded for bots as well.', 'wpspeed' )
					]
				]
			];
		}
		
		public static function autoconfigurationTab() {
			return [
				/**
				 * Auto configuration
				 */
				'autoconfigurationSection' => [
					'autoconfiguration' => [
							__ ( 'Choose optimization level', 'wpspeed' ),
							__ ( 'Select an optimization level for the auto configuration', 'wpspeed' ),
							true
					],
					'pagespeed' => [
							__ ( 'Google PageSpeed score', 'wpspeed' ),
							__ ( 'Test your website to evaluate your Google PageSpeed score', 'wpspeed' ),
							true
					],
					'pagespeedtest_domain_url' => [
							__ ( 'Page URL to test', 'wpspeed' ),
							__ ( 'Enter the URL of the page to test, if the field is left empty then the home page URL will be used', 'wpspeed' ),
							true
					],
					'google_pagespeed_api_key' => [
							__ ( 'Custom Google PageSpeed ApiKey', 'wpspeed' ),
							__ ( 'It\'s possible to enter a custom Google PageSpeed ApiKey, if the field is left empty then the built in ApiKey will be used', 'wpspeed' ),
							true
					]
				]
			];
		}
}