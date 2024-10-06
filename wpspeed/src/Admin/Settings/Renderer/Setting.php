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

use WPSpeed\Admin\Settings\Html;

class Setting {
	public static function autoconfiguration() {
		echo Html::_ ( 'rangeSlider', 'rangeSlider', '0' );
	}
	
	public static function pagespeed() {
		echo Html::_ ( 'pageSpeed', 'pageSpeed', '0' );
	}
	
	public static function pagespeedtest_domain_url() {
		echo Html::_ ( 'text', 'pagespeedtest_domain_url', '', '100' );
	}
	
	public static function google_pagespeed_api_key() {
		echo Html::_ ( 'text', 'google_pagespeed_api_key', '', '100' );
	}
	public static function clear_server_cache() {
		echo Html::_ ( 'radio', 'clear_server_cache', '0' );
	}
	public static function debug() {
		echo Html::_ ( 'radio', 'debug', '0' );
	}
	public static function order_plugin() {
		echo Html::_ ( 'radio', 'order_plugin', '1' );
	}
	public static function disable_logged_in_users() {
		echo Html::_ ( 'radio', 'disable_logged_in_users', '0' );
	}
	public static function menuexcludedurl() {
		echo Html::_ ( 'multiselect', 'menuexcludedurl', [ ], 'url', 'file' );
	}
	public static function combine_files_enable() {
		echo Html::_ ( 'radio', 'combine_files_enable', '1' );
	}
	public static function cache_lifetime() {
		$aOptions = [
				'60' => __ ( '1 min', 'wpspeed' ),
				'300' => __ ( '5 min', 'wpspeed' ),
				'600' => __ ( '10 min', 'wpspeed' ),
				'900' => __ ( '15 min', 'wpspeed' ),
				'1800' => __ ( '30 min', 'wpspeed' ),
				'3600' => __ ( '1 hour', 'wpspeed' ),
				'10800' => __ ( '3 hours', 'wpspeed' ),
				'21600' => __ ( '6 hours', 'wpspeed' ),
				'43200' => __ ( '12 hours', 'wpspeed' ),
				'86400' => __ ( '1 day', 'wpspeed' ),
				'172800' => __ ( '2 days', 'wpspeed' ),
				'604800' => __ ( '7 days', 'wpspeed' ),
				'1209600' => __ ( '2 weeks', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'cache_lifetime', '900', $aOptions );
	}
	public static function html_minify_level() {
		$aOptions = [ 
				'0' => __ ( 'Low', 'wpspeed' ),
				'1' => __ ( 'Normal', 'wpspeed' ),
				'2' => __ ( 'High', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'html_minify_level', '1', $aOptions );
	}
	public static function try_catch() {
		echo Html::_ ( 'radio', 'try_catch', '1' );
	}
	public static function html_minify() {
		echo Html::_ ( 'radio', 'html_minify', '1' );
	}
	public static function includeAllExtensions() {
		echo Html::_ ( 'radio', 'includeAllExtensions', '1' );
	}
	public static function phpAndExternal() {
		echo Html::_ ( 'radio', 'phpAndExternal', '1' );
	}
	public static function css() {
		echo Html::_ ( 'radio', 'css', '1' );
	}
	public static function css_minify() {
		echo Html::_ ( 'radio', 'css_minify', '1' );
	}
	public static function replaceImports() {
		echo Html::_ ( 'radio', 'replaceImports', '1' );
	}
	public static function inlineStyle() {
		echo Html::_ ( 'radio', 'inlineStyle', '1' );
	}
	public static function excludeCss() {
		echo Html::_ ( 'multiselect', 'excludeCss', [ ], 'css', 'file' );
	}
	public static function excludeCssComponents() {
		echo Html::_ ( 'multiselect', 'excludeCssComponents', [ ], 'css', 'extension' );
	}
	public static function excludeStyles() {
		echo Html::_ ( 'multiselect', 'excludeStyles', [ ], 'css', 'style' );
	}
	public static function excludeAllStyles() {
		echo Html::_ ( 'radio', 'excludeAllStyles', '0' );
	}
	public static function remove_css() {
		echo Html::_ ( 'multiselect', 'remove_css', [ ], 'css', 'file' );
	}
	public static function font_display_swap() {
		echo Html::_ ( 'radio', 'font_display_swap', '1' );
	}
	public static function defer_combined_styles() {
		echo Html::_ ( 'radio', 'defer_combined_styles', '0' );
	}
	public static function optimizeCssDelivery_enable() {
		echo Html::_ ( 'radio', 'optimizeCssDelivery_enable', '0' );
	}
	public static function optimizeCssDelivery() {
		$aOptions = [
				'400' => '400',
				'600' => '600',
				'800' => '800',
				'1000' => '1000',
				'1200' => '1200',
				'1500' => '1500',
				'2000' => '2000',
				'3000' => '3000',
				'4000' => '4000',
				'5000' => '5000',
				'6000' => '6000',
				'7000' => '7000',
				'8000' => '8000',
				'9000' => '9000',
				'10000' => '10000',
				'15000' => '15000',
				'20000' => '20000'
		];

		echo Html::_ ( 'select', 'optimizeCssDelivery', '800', $aOptions );
	}
	public static function pro_remove_unused_css() {
		echo Html::_ ( 'radio', 'pro_remove_unused_css', '0' );
	}
	public static function optimizeCssDeliveryExcludeFontface() {
		echo Html::_ ( 'radio', 'optimizeCssDeliveryExcludeFontface', '1' );
	}
	public static function pro_dynamic_selectors() {
		echo Html::_ ( 'multiselect', 'pro_dynamic_selectors', [ ], 'selectors', 'style' );
	}
	public static function javascript() {
		echo Html::_ ( 'radio', 'javascript', '1' );
	}
	public static function js_minify() {
		echo Html::_ ( 'radio', 'js_minify', '1' );
	}
	public static function inlineScripts() {
		echo Html::_ ( 'radio', 'inlineScripts', '1' );
	}
	public static function bottom_js() {
		echo Html::_ ( 'radio', 'bottom_js', '1' );
	}
	public static function loadAsynchronous() {
		echo Html::_ ( 'radio', 'loadAsynchronous', '1' );
	}
	public static function excludeJs_peo() {
		echo Html::_ ( 'multiselect', 'excludeJs_peo', [ ], 'js', 'file' );
	}
	public static function excludeJsComponents_peo() {
		echo Html::_ ( 'multiselect', 'excludeJsComponents_peo', [ ], 'js', 'extension' );
	}
	public static function excludeScripts_peo() {
		echo Html::_ ( 'multiselect', 'excludeScripts_peo', [ ], 'js', 'script' );
	}
	public static function excludeAllScripts() {
		echo Html::_ ( 'radio', 'excludeAllScripts', '0' );
	}
	public static function excludeJs() {
		echo Html::_ ( 'multiselect', 'excludeJs', [ ], 'js', 'file' );
	}
	public static function excludeJsComponents() {
		echo Html::_ ( 'multiselect', 'excludeJsComponents', [ ], 'js', 'extension' );
	}
	public static function excludeScripts() {
		echo Html::_ ( 'multiselect', 'excludeScripts', [ ], 'js', 'script' );
	}
	public static function dontmoveJs() {
		echo Html::_ ( 'multiselect', 'dontmoveJs', [ ], 'js', 'file' );
	}
	public static function dontmoveScripts() {
		echo Html::_ ( 'multiselect', 'dontmoveScripts', [ ], 'js', 'script' );
	}
	public static function remove_js() {
		echo Html::_ ( 'multiselect', 'remove_js', [ ], 'js', 'file' );
	}
	public static function cache_enable() {
		echo Html::_ ( 'radio', 'cache_enable', '0' );
	}
	public static function pro_cache_platform() {
		echo Html::_ ( 'radio', 'pro_cache_platform', '0' );
	}
	public static function page_cache_lifetime() {
		$aOptions = [
				'60' => __ ( '1 min', 'wpspeed' ),
				'300' => __ ( '5 min', 'wpspeed' ),
				'600' => __ ( '10 min', 'wpspeed' ),
				'900' => __ ( '15 min', 'wpspeed' ),
				'1800' => __ ( '30 min', 'wpspeed' ),
				'3600' => __ ( '1 hour', 'wpspeed' ),
				'10800' => __ ( '3 hours', 'wpspeed' ),
				'21600' => __ ( '6 hours', 'wpspeed' ),
				'43200' => __ ( '12 hours', 'wpspeed' ),
				'86400' => __ ( '1 day', 'wpspeed' ),
				'172800' => __ ( '2 days', 'wpspeed' ),
				'604800' => __ ( '1 week', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'page_cache_lifetime', '600', $aOptions );
	}
	public static function cache_exclude() {
		echo Html::_ ( 'multiselect', 'cache_exclude', [ ], 'url', 'file' );
	}
	public static function img_attributes_enable() {
		echo Html::_ ( 'radio', 'img_attributes_enable', '0' );
	}
	public static function csg_enable() {
		echo Html::_ ( 'radio', 'csg_enable', '0' );
	}
	public static function csg_direction() {
		$aOptions = [ 
				'vertical' => __ ( 'Vertical direction', 'wpspeed' ),
				'horizontal' => __ ( 'Horizontal direction', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'csg_drection', 'vertical', $aOptions );
	}
	public static function csg_wrap_images() {
		echo Html::_ ( 'radio', 'csg_wrap_images', '0' );
	}
	public static function csg_exclude_images() {
		echo Html::_ ( 'multiselect', 'csg_exclude_images', [ ], 'images', 'file' );
	}
	public static function csg_include_images() {
		echo Html::_ ( 'multiselect', 'csg_include_images', [ ], 'images', 'file' );
	}
	public static function lazyload_enable() {
		echo Html::_ ( 'radio', 'lazyload_enable', '0' );
	}
	public static function lazyload_autosize() {
		echo Html::_ ( 'radio', 'lazyload_autosize', '0' );
	}
	public static function pro_lazyload_effects() {
		echo Html::_ ( 'radio', 'pro_lazyload_effects', '0' );
	}
	public static function pro_lazyload_iframe() {
		echo Html::_ ( 'radio', 'pro_lazyload_iframe', '0' );
	}
	public static function pro_lazyload_bgimages() {
		echo Html::_ ( 'radio', 'pro_lazyload_bgimages', '0' );
	}
	public static function pro_lazyload_audiovideo() {
		echo Html::_ ( 'radio', 'pro_lazyload_audiovideo', '0' );
	}
	public static function disableCoreLazyload() {
		echo Html::_ ( 'radio', 'disableCoreLazyload', '0' );
	}
	public static function excludeLazyLoad() {
		echo Html::_ ( 'multiselect', 'excludeLazyLoad', [ ], 'lazyload', 'file' );
	}
	public static function pro_excludeLazyLoadFolders() {
		echo Html::_ ( 'multiselect', 'pro_excludeLazyLoadFolders', [ ], 'lazyload', 'folder' );
	}
	public static function pro_excludeLazyLoadClass() {
		echo Html::_ ( 'multiselect', 'pro_excludeLazyLoadClass', [ ], 'lazyload', 'class' );
	}
	public static function http2_push_enable() {
		echo Html::_ ( 'radio', 'http2_push_enable', '0' );
	}
	public static function pro_http2_exclude_deferred() {
		echo Html::_ ( 'radio', 'pro_http2_exclude_deferred', '0' );
	}
	public static function pro_http2_push_cdn() {
		echo Html::_ ( 'radio', 'pro_http2_push_cdn', '0' );
	}
	public static function pro_http2_file_types() {
		$aOptions = [ 
				'style' => 'style',
				'script' => 'script',
				'font' => 'font',
				'image' => 'image'
		];

		echo Html::_ ( 'multiselect', 'pro_http2_file_types', $aOptions, 'none', 'value' );
	}
	public static function pro_http2_include() {
		echo Html::_ ( 'multiselect', 'pro_http2_include', [ ], 'http2', 'file' );
	}
	public static function pro_http2_exclude() {
		echo Html::_ ( 'multiselect', 'pro_http2_exclude', [ ], 'http2', 'file' );
	}
	public static function cookielessdomain_enable() {
		echo Html::_ ( 'radio', 'cookielessdomain_enable', '0' );
	}
	public static function pro_cdn_preconnect() {
		echo Html::_ ( 'radio', 'pro_cdn_preconnect', '0' );
	}
	public static function cdn_scheme() {
		$aOptions = [ 
				'0' => __ ( 'Relative', 'wpspeed' ),
				'1' => __ ( 'http', 'wpspeed' ),
				'2' => __ ( 'https', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'cdn_scheme', 0, $aOptions );
	}
	public static function cookielessdomain() {
		echo Html::_ ( 'text', 'cookielessdomain', '' );
	}
	public static function staticfiles() {
		echo Html::_ ( 'multiselect', 'staticfiles', array_keys ( self::staticFilesArray () ), 'none', 'value' );
	}
	private static function staticFilesArray() {
		return [ 
				'css' => 'css',
				'png' => 'png',
				'gif' => 'gif',
				'ico' => 'ico',
				'pdf' => 'pdf',
				'js' => 'js',
				'jpe?g' => 'jp(e)g',
				'bmp' => 'bmp',
				'webp' => 'webp',
				'svg' => 'svg'
		];
	}
	public static function pro_customcdnextensions() {
		echo Html::_ ( 'multiselect', 'pro_customcdnextensions', [ ], 'url', 'file' );
	}
	public static function pro_cookielessdomain_2() {
		echo Html::_ ( 'text', 'pro_cookielessdomain_2', '' );
	}
	public static function pro_staticfiles_2() {
		echo Html::_ ( 'multiselect', 'pro_staticfiles_2', array_keys ( self::staticFilesArray () ), 'none', 'value' );
	}
	public static function pro_cookielessdomain_3() {
		echo Html::_ ( 'text', 'pro_cookielessdomain_3', '' );
	}
	public static function pro_staticfiles_3() {
		echo Html::_ ( 'multiselect', 'pro_staticfiles_3', array_keys ( self::staticFilesArray () ), 'none', 'value' );
	}
	public static function ignore_optimized() {
		echo Html::_ ( 'radio', 'ignore_optimized', '1' );
	}
	public static function pro_next_gen_images() {
		echo Html::_ ( 'radio', 'pro_next_gen_images', '1' );
	}
	public static function pro_web_old_browsers() {
		echo Html::_ ( 'radio', 'pro_web_old_browsers', '0' );
	}
	public static function pro_api_resize_mode() {
		echo Html::_ ( 'radio', 'pro_api_resize_mode', '1' );
	}
	public static function recursive() {
		echo Html::_ ( 'radio', 'recursive', '1' );
	}
	public static function pro_reduce_dom() {
		echo Html::_ ( 'radio', 'pro_reduce_dom', '0' );
	}
	public static function pro_html_sections() {
		$options = [ 
				'section' => 'section',
				'header' => 'header',
				'footer' => 'footer',
				'aside' => 'aside',
				'nav' => 'nav'
		];

		echo Html::_ ( 'multiselect', 'pro_html_sections', $options, 'none', 'value' );
	}
	public static function lightimgs_status() {
		echo Html::_ ( 'radio', 'lightimgs_status', '0' );
	}
	public static function img_processing_minwidth() {
		echo Html::_ ( 'text', 'img_processing_minwidth', '50' );
	}
	public static function img_quality() {
		$aOptions = [ 
				'40' => __ ( '40%', 'wpspeed' ),
				'45' => __ ( '45%', 'wpspeed' ),
				'50' => __ ( '50%', 'wpspeed' ),
				'55' => __ ( '55%', 'wpspeed' ),
				'60' => __ ( '60%', 'wpspeed' ),
				'65' => __ ( '65%', 'wpspeed' ),
				'70' => __ ( '70%', 'wpspeed' ),
				'75' => __ ( '75%', 'wpspeed' ),
				'80' => __ ( '80%', 'wpspeed' ),
				'85' => __ ( '85%', 'wpspeed' ),
				'90' => __ ( '90%', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'img_quality', '70', $aOptions );
	}
	public static function img_resizing_switcher() {
		echo Html::_ ( 'radio', 'img_resizing_switcher', '0' );
	}
	public static function img_resizing() {
		$aOptions = [ 
				'20' => __ ( '20%', 'wpspeed' ),
				'25' => __ ( '25%', 'wpspeed' ),
				'30' => __ ( '30%', 'wpspeed' ),
				'35' => __ ( '35%', 'wpspeed' ),
				'40' => __ ( '40%', 'wpspeed' ),
				'45' => __ ( '45%', 'wpspeed' ),
				'50' => __ ( '50%', 'wpspeed' ),
				'55' => __ ( '55%', 'wpspeed' ),
				'60' => __ ( '60%', 'wpspeed' ),
				'65' => __ ( '65%', 'wpspeed' ),
				'70' => __ ( '70%', 'wpspeed' ),
				'75' => __ ( '75%', 'wpspeed' ),
				'80' => __ ( '80%', 'wpspeed' ),
				'85' => __ ( '85%', 'wpspeed' ),
				'90' => __ ( '90%', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'img_resizing', '60', $aOptions );
	}
	public static function img_resizing_minwidth() {
		echo Html::_ ( 'text', 'img_resizing_minwidth', '300' );
	}
	public static function convert_all_images_to_webp() {
		echo Html::_ ( 'radio', 'convert_all_images_to_webp', '0' );
		
		if(function_exists('imagewebp')) {
			echo '<span data-bs-content="' . __('Your server and PHP version support the conversion to the WebP format', 'wpspeed') . '" class="badge bg-success jspeed-checker hasPopover">' .
				 '<span class="fas fa-check" aria-hidden="true"></span> ' . __('WebP conversion supported', 'wpspeed') . '</span>';
		} else {
			echo '<span data-bs-content="' . __('Your server and PHP version don\'t support the conversion to the WebP format. Contact your hosting provider in order to activate it via the PHP GD library', 'wpspeed') . '" class="badge bg-danger jspeed-checker hasPopover">' .
				 '<span class="fas fa-exclamation-triangle" aria-hidden="true"></span> ' . __('WebP conversion not supported', 'wpspeed') . '</span>';
		}
	}
	public static function convert_all_images_to_avif() {
		echo Html::_ ( 'radio', 'convert_all_images_to_avif', '0' );
		
		if(version_compare(PHP_VERSION, '8.1', '>=') && function_exists('imageavif')) {
			// Convert a dummy image to check if the size is > 0 bytes
			$imageFileSize = 0;
			$mediaFilePath = WPSPEED_DIR . 'media/images/';
			$convertedImage = $mediaFilePath . 'output.avif';
			if(!file_exists($convertedImage)) {
				$originalImage = imagecreatefrompng($mediaFilePath . 'wpspeed-logo.png');
				$result = imageavif($originalImage, $convertedImage);
				if($result) {
					$imageFileSize = filesize($convertedImage);
				}
			} else {
				$imageFileSize = filesize($convertedImage);
			}
			
			if($imageFileSize > 0) {
				echo '<span data-bs-content="' . __('Your server and PHP version support the conversion to the AVIF format', 'wpspeed') . '" class="badge bg-success jspeed-checker hasPopover">' .
					 '<span class="fas fa-check" aria-hidden="true"></span> ' . __('AVIF conversion supported', 'wpspeed') . '</span>';
			} else {
				echo '<span data-bs-content="' . __('Your server and PHP version don\'t support the conversion to the AVIF format. Upgrade at least to PHP 8.1 and contact your hosting provider in order to activate it via the PHP GD library', 'wpspeed') . '" class="badge bg-danger jspeed-checker hasPopover">' .
					 '<span class="fas fa-exclamation-triangle" aria-hidden="true"></span> ' . __('AVIF conversion not supported', 'wpspeed') . '</span>';
			}
		} else {
			echo '<span data-bs-content="' . __('Your server and PHP version don\'t support the conversion to the AVIF format. Upgrade at least to PHP 8.1 and contact your hosting provider in order to activate it via the PHP GD library', 'wpspeed') . '" class="badge bg-danger jspeed-checker hasPopover">' .
				 '<span class="fas fa-exclamation-triangle" aria-hidden="true"></span> ' . __('AVIF conversion not supported', 'wpspeed') . '</span>';
		}
	}
	public static function exclude_light_images_safari() {
		echo Html::_ ( 'radio', 'exclude_light_images_safari', '1' );
	}
	public static function optimize_css_background_images() {
		echo Html::_ ( 'radio', 'optimize_css_background_images', '0' );
	}
	public static function optimize_html_background_images() {
		echo Html::_ ( 'radio', 'optimize_html_background_images', '0' );
	}
	public static function img_support_gif() {
		echo Html::_ ( 'radio', 'img_support_gif', '0' );
	}
	public static function webservice_processing() {
		echo Html::_ ( 'radio', 'webservice_processing', '0' );
	}
	public static function hash_images_algo() {
		$aOptions = [ 
				'full' => __ ( 'Full', 'wpspeed' ),
				'partial' => __ ( 'Partial', 'wpspeed' ),
				'none' => __ ( 'None', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'hash_images_algo', 'full', $aOptions );
	}
	public static function img_exts_excluded() {
		$aOptions = [
				'' => __ ( 'No exclusions', 'wpspeed' ),
				'jpeg' => __ ( 'jpeg', 'wpspeed' ),
				'jpg' => __ ( 'jpg', 'wpspeed' ),
				'png' => __ ( 'png', 'wpspeed' ),
				'gif' => __ ( 'gif', 'wpspeed' ),
				'bmp' => __ ( 'bmp', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'img_exts_excluded', '', $aOptions, 'wpspeed-native-multiselect', true );
	}
	public static function img_files_excluded() {
		echo Html::_ ( 'multiselect', 'img_files_excluded', [ ], 'none', 'file' );
	}
	public static function img_class_excluded() {
		echo Html::_ ( 'multiselect', 'img_class_excluded', [ ], 'none', 'file' );
	}
	public static function img_menu_excluded() {
		echo Html::_ ( 'multiselect', 'img_menu_excluded', [ ], 'none', 'file' );
	}
	public static function extractcss_menu_excluded() {
		echo Html::_ ( 'multiselect', 'extractcss_menu_excluded', [ ], 'none', 'file' );
	}
	public static function img_processing_datasrc() {
		echo Html::_ ( 'radio', 'img_processing_datasrc', '0' );
	}
	public static function img_processing_entity_decode() {
		echo Html::_ ( 'radio', 'img_processing_entity_decode', '1' );
	}
	public static function img_processing_utf8_entity_decode() {
		echo Html::_ ( 'radio', 'img_processing_utf8_entity_decode', '0' );
	}
	public static function purify_string() {
		echo Html::_ ( 'textarea', 'purify_string', '' );
	}
	public static function purify_string_replacement() {
		echo Html::_ ( 'textarea', 'purify_string_replacement', '' );
	}
	public static function img_processing_srcset() {
		echo Html::_ ( 'radio', 'img_processing_srcset', '0' );
	}
	public static function img_processing_srcset_starting_quality() {
		$aOptions = [ 
				'40' => __ ( '40%', 'wpspeed' ),
				'45' => __ ( '45%', 'wpspeed' ),
				'50' => __ ( '50%', 'wpspeed' ),
				'55' => __ ( '55%', 'wpspeed' ),
				'60' => __ ( '60%', 'wpspeed' ),
				'65' => __ ( '65%', 'wpspeed' ),
				'70' => __ ( '70%', 'wpspeed' ),
				'75' => __ ( '75%', 'wpspeed' ),
				'80' => __ ( '80%', 'wpspeed' ),
				'85' => __ ( '85%', 'wpspeed' ),
				'90' => __ ( '90%', 'wpspeed' ),
				'95' => __ ( '95%', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'img_processing_srcset_starting_quality', '90', $aOptions );
	}
	public static function img_processing_srcset_quality_decrease_step() {
		$aOptions = [ 
				'5' => __ ( '5%', 'wpspeed' ),
				'10' => __ ( '10%', 'wpspeed' ),
				'15' => __ ( '15%', 'wpspeed' ),
				'20' => __ ( '20%', 'wpspeed' ),
				'25' => __ ( '25%', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'img_processing_srcset_quality_decrease_step', '15', $aOptions );
	}
	public static function img_processing_srcset_starting_resize() {
		$aOptions = [ 
				'40' => __ ( '40%', 'wpspeed' ),
				'45' => __ ( '45%', 'wpspeed' ),
				'50' => __ ( '50%', 'wpspeed' ),
				'55' => __ ( '55%', 'wpspeed' ),
				'60' => __ ( '60%', 'wpspeed' ),
				'65' => __ ( '65%', 'wpspeed' ),
				'70' => __ ( '70%', 'wpspeed' ),
				'75' => __ ( '75%', 'wpspeed' ),
				'80' => __ ( '80%', 'wpspeed' ),
				'85' => __ ( '85%', 'wpspeed' ),
				'90' => __ ( '90%', 'wpspeed' ),
				'95' => __ ( '95%', 'wpspeed' ),
				'100' => __ ( '100%', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'img_processing_srcset_starting_resize', '100', $aOptions );
	}
	public static function img_processing_srcset_resize_decrease_step() {
		$aOptions = [ 
				'5' => __ ( '5%', 'wpspeed' ),
				'10' => __ ( '10%', 'wpspeed' ),
				'15' => __ ( '15%', 'wpspeed' ),
				'20' => __ ( '20%', 'wpspeed' ),
				'25' => __ ( '25%', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'img_processing_srcset_resize_decrease_step', '20', $aOptions );
	}
	public static function img_processing_srcset_original_image() {
		$aOptions = [ 
				'-1' => __ ( 'Original image', 'wpspeed' ),
				'0' => __ ( '4x', 'wpspeed' ),
				'1' => __ ( '3x', 'wpspeed' ),
				'2' => __ ( '2x', 'wpspeed' ),
				'3' => __ ( '1x', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'img_processing_srcset_original_image', '0', $aOptions );
	}
	public static function adaptive_contents_enable() {
		echo Html::_ ( 'radio', 'adaptive_contents_enable', '0' );
	}
	public static function adaptive_contents_css_selector() {
		echo Html::_ ( 'text', 'adaptive_contents_css_selector', 'script[src],iframe,video', '100' );
	}
	public static function adaptive_contents_bots_list() {
		$aOptions = [ 
				'lighthouse' => __ ( 'Lighthouse', 'wpspeed' ),
				'googlebot' => __ ( 'Googlebot', 'wpspeed' ),
				'googlebot-mobile' => __ ( 'Googlebot-Mobile', 'wpspeed' ),
				'googlebot-video' => __ ( 'Googlebot-Video', 'wpspeed' ),
				'gtmetrix' => __ ( 'GTMetrix', 'wpspeed' ),
				'bingbot' => __ ( 'Bingbot', 'wpspeed' ),
				'baiduspider' => __ ( 'Baiduspider', 'wpspeed' ),
				'duckduckbot' => __ ( 'Duckduckbot', 'wpspeed' ),
				'twitterbot' => __ ( 'Twitterbot', 'wpspeed' ),
				'applebot' => __ ( 'Applebot', 'wpspeed' ),
				'semrushbot' => __ ( 'Semrushbot', 'wpspeed' ),
				'ptst' => __ ( 'WebPageTest', 'wpspeed' ),
				'ahrefs' => __ ( 'Ahrefs', 'wpspeed' ),
				'pingdom' => __ ( 'Pingdom', 'wpspeed' ),
				'seranking' => __ ( 'Seranking', 'wpspeed' ),
				'moto g power' => __ ( 'Motogpower', 'wpspeed' )
		];

		echo Html::_ ( 'multiselect', 'adaptive_contents_bots_list', array_keys ( $aOptions ), 'none', 'value' );
	}
	public static function adaptive_contents_remove_js() {
		echo Html::_ ( 'radio', 'adaptive_contents_remove_js', '0' );
	}
	public static function adaptive_contents_remove_js_files() {
		echo Html::_ ( 'multiselect', 'adaptive_contents_remove_js_files', [ ], 'js', 'file' );
	}
	public static function adaptive_contents_remove_css() {
		echo Html::_ ( 'radio', 'adaptive_contents_remove_css', '0' );
	}
	public static function adaptive_contents_remove_css_files() {
		echo Html::_ ( 'multiselect', 'adaptive_contents_remove_css_files', [ ], 'css', 'file' );
	}
	public static function adaptive_contents_remove_all_js() {
		echo Html::_ ( 'radio', 'adaptive_contents_remove_all_js', '0' );
	}
	public static function adaptive_contents_remove_all_css() {
		echo Html::_ ( 'radio', 'adaptive_contents_remove_all_css', '0' );
	}
	public static function adaptive_contents_essential_css_code() {
		echo Html::_ ( 'textarea', 'adaptive_contents_essential_css_code', '' );
	}
	public static function enable_instant_page() {
		echo Html::_ ( 'radio', 'enable_instant_page', '0' );
	}
	public static function adaptive_contents_add_size_attributes() {
		echo Html::_ ( 'radio', 'adaptive_contents_add_size_attributes', '0' );
	}
	public static function adaptive_contents_optimize_images() {
		echo Html::_ ( 'radio', 'adaptive_contents_optimize_images', '0' );
	}
	public static function adaptive_contents_extract_css() {
		echo Html::_ ( 'radio', 'adaptive_contents_extract_css', '0' );
	}
	public static function instant_page_delay() {
		$aOptions = [ 
				'fast' => __ ( 'Fast delay', 'wpspeed' ),
				'slow' => __ ( 'Slow delay', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'instant_page_delay', 'fast', $aOptions );
	}
	public static function add_custom_js_code() {
		echo Html::_ ( 'radio', 'add_custom_js_code', '0' );
	}
	public static function custom_js_code() {
		echo Html::_ ( 'textarea', 'custom_js_code', '' );
	}
	public static function add_custom_css_code() {
		echo Html::_ ( 'radio', 'add_custom_css_code', '0' );
	}
	public static function custom_css_code() {
		echo Html::_ ( 'textarea', 'custom_css_code', '' );
	}
	public static function base64encode() {
		echo Html::_ ( 'radio', 'base64encode', '0' );
	}
	public static function lazyload_html_enable() {
		echo Html::_ ( 'radio', 'lazyload_html_enable', '0' );
	}
	public static function lazyload_html_css_selector() {
		echo Html::_ ( 'text', 'lazyload_html_css_selector', '', '100' );
	}
	public static function lazyload_method() {
		$aOptions = [ 
				'scroll' => __ ( 'Scroll', 'wpspeed' ),
				'delay' => __ ( 'Delay', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'lazyload_method', 'scroll', $aOptions );
	}
	public static function lazyload_html_delay() {
		$aOptions = [ 
				'100' => __ ( '100 ms', 'wpspeed' ),
				'200' => __ ( '200 ms', 'wpspeed' ),
				'300' => __ ( '300 ms', 'wpspeed' ),
				'500' => __ ( '500 ms', 'wpspeed' ),
				'600' => __ ( '600 ms', 'wpspeed' ),
				'700' => __ ( '700 ms', 'wpspeed' ),
				'800' => __ ( '800 ms', 'wpspeed' ),
				'900' => __ ( '900 ms', 'wpspeed' ),
				'1000' => __ ( '1000 ms', 'wpspeed' ),
				'1500' => __ ( '1500 ms', 'wpspeed' ),
				'2000' => __ ( '2000 ms', 'wpspeed' ),
				'2500' => __ ( '2500 ms', 'wpspeed' ),
				'3000' => __ ( '3000 ms', 'wpspeed' ),
				'3500' => __ ( '3500 ms', 'wpspeed' ),
				'4000' => __ ( '4000 ms', 'wpspeed' ),
				'4500' => __ ( '4500 ms', 'wpspeed' ),
				'5000' => __ ( '5000 ms', 'wpspeed' ),
				'5500' => __ ( '5500 ms', 'wpspeed' ),
				'6000' => __ ( '6000 ms', 'wpspeed' )
		];

		echo Html::_ ( 'select', 'lazyload_html_delay', '3000', $aOptions );
	}
	public static function lazyload_html_use_important_override() {
		echo Html::_ ( 'radio', 'lazyload_html_use_important_override', '0' );
	}
}