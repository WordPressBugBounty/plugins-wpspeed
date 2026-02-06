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

use WPSpeed\Core\Css\Parser as CssParser;
use WPSpeed\Core\Html\ElementObject;
use WPSpeed\Core\Html\Parser;
use WPSpeed\Core\Browser;
use WPSpeed\Core\FileRetriever;
use WPSpeed\Core\Html\Processor;
use WPSpeed\Core\Helper;
use WPSpeed\Platform\Plugin;
use WPSpeed\Platform\Utility;
use WPSFramework\Container\Container;
use WPSFramework\Application\Application;
use WPSFramework\Uri\Uri;
use WPSFramework\Filesystem\File;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

/**
 * Optimize images on the fly using the cache
 *
 * @access public
 * @param string $sOptimizedHtml
 * @return string on success, false on failure
 */
class LightImages {
	private $rootDir;
	private $oContainer;
	private $oApplication;
	private $params;
	private $isEnabled;
	private $excludedExts;
	private $oFileRetriever;
	private $lazyLoadedImage = false;
	private $disableSrcSet = null;
	
	private function processImageNodes(&$node, $srcSet = false, $srcSetIteration = null, $qualitySrcSet = null, $resizeFactorSrcSet = null, $originalSource = null) {
		// Always init to false for each image processing
		$this->lazyLoadedImage = false;
		
		// Srcset settings
		$originalSrc = null;
		$preSrcSetCreation = false;
		$createSrcSet = $this->disableSrcSet ? false : $this->params->get('img_processing_srcset', 0);
		$srcSetQualityStartingQuality = $this->params->get('img_processing_srcset_starting_quality', 90);
		$srcSetQualityDecreaseStep = $this->params->get('img_processing_srcset_quality_decrease_step', 15);
		$srcSetResizeStartingResize = $this->params->get('img_processing_srcset_starting_resize', 100);
		$srcSetResizeDecreaseStep = $this->params->get('img_processing_srcset_resize_decrease_step', 20);
		$srcSetOriginalImage = $this->params->get('img_processing_srcset_original_image', 0);
		$imgProcessingSrcsetDatasrc = (bool)$this->params->get('img_processing_srcset_datasrc', 0);
		
		$quality = $qualitySrcSet ? $qualitySrcSet : $this->params->get('img_quality', 70);
		
		$pngQuality = 10 - (int) ($quality / 10); // Inverted as level of compression
		$pngQuality = $pngQuality >= 10 ? 9 : $pngQuality; // Max level of compression is 9, range is 1 to 9
		$resizeFactorSwitcher = $this->params->get('img_resizing_switcher', false) || $createSrcSet;
		$resizeFactor = $resizeFactorSrcSet ? $resizeFactorSrcSet : $this->params->get('img_resizing', 60);
		$resizeMinWidth = $this->params->get('img_resizing_minwidth', 50);
		$processingMinWidth = $this->params->get('img_processing_minwidth', 50);
		$processingDataSrc = $this->params->get('img_processing_datasrc', 0);
		$processingDataCustom = $this->params->get('img_processing_datacustom', '');
		
		// Remove the default WP srcset
		if($createSrcSet && !$srcSet) {
			$node->removeAttribute("srcset");
			$node->removeAttribute("sizes");
		}
		
		// Override to leave unaltered the original image when a srcset creation is planned for this image
		if($createSrcSet && !$srcSet) {
			$quality = $srcSetQualityStartingQuality;
			$pngQuality = 10 - (int) ($srcSetQualityStartingQuality / 10);
			$resizeFactor = $srcSetResizeStartingResize;
			$preSrcSetCreation = true;
		}
		
		$cache_path = WPSPEED_CACHE_DIR . 'images';
		$cache_path_http = rtrim(Uri::root(true), '/') . "/wp-content/cache/wpspeed/images";
		
		// Get what we want
		if ($node->nodeType == XML_ELEMENT_NODE && $node->hasAttributes()) {
			if ($node->getAttribute('height') && strpos($node->getAttribute('height'), 'px')) {
				$heightExplicitFromNode = (int) $node->getAttribute("height");
			}
			
			if ($node->getAttribute('width') && strpos($node->getAttribute('width'), 'px')) {
				$widthExplicitFromNode = (int) $node->getAttribute("width");
			}
			
			if ($node->getAttribute("src") != "")
				$src = $originalSrc = $node->getAttribute("src");
		}
		
		if (!isset($src)) {
			if ($processingDataSrc && ($node->hasAttribute ( 'data-src' ) || ($processingDataCustom !== '' && $node->hasAttribute ( 'data-' . $processingDataCustom )))) {
				// Go on
				$src = '';
			} else {
				return false;
			}
		}
		
		// Need to remove query string if any
		$src = preg_replace('/\?.*/', '', $src);
		
		// Need to remove fragment if any
		$src = preg_replace('/\#.*/', '', $src);
		
		// Check if there is a processing for a lazy loaded image having a src=data:image and a valid data-src instead
		if(strpos($src, 'data:image/svg+xml;bas' . 'e64') !== false && $node->hasAttribute('data-wpspeed-lazyload')) {
			$src = $originalSrc = preg_replace('/\?.*/', '', $node->getAttribute('data-src'));
			$this->lazyLoadedImage = true;
			$processingDataSrc = true;
			$createSrcSet = $imgProcessingSrcsetDatasrc;
		}
		
		// Check if there is a processing for a lazy loaded image having an invalid or missing src and a valid data-src instead
		if ($processingDataSrc &&
			(!$src || strpos ( $src, 'data:image/svg+xml;base64' ) !== false || strpos ( $src, 'data:image/gif;base64' ) !== false) &&
			($node->hasAttribute ( 'data-src' ) || ($processingDataCustom !== '' && $node->hasAttribute ( 'data-' . $processingDataCustom )))) {
				$src = $originalSrc = $node->getAttribute('data-src');
				
				// Check if there is a custom data attribute fallback
				if (!$src) {
					$src = $originalSrc = $node->getAttribute('data-' . $processingDataCustom);
				}
				
				if (!$src) {
					return false;
				}

				// Need to remove query string if any
				$src = preg_replace('/\?.*/', '', $src);
				
				// Need to remove fragment if any
				$src = preg_replace('/\#.*/', '', $src);

				$this->lazyLoadedImage = true;
				$processingDataSrc = true;
				$createSrcSet = $imgProcessingSrcsetDatasrc;
		}

		// Need to remove encoding
		$imagePath = urldecode($src);
		
		// Check for exclusions
		$imgFilesExcluded = $this->params->get('img_files_excluded', array());
		foreach ($imgFilesExcluded as $excludedImg) {
			if(strpos($imagePath, $excludedImg) !== false) {
				return false;
			}
		}
		
		$imgClassExcluded = $this->params->get('img_class_excluded', array());
		if ($node->hasAttribute('class') && !empty($imgClassExcluded)) {
			foreach ($imgClassExcluded as $excludedImgClass) {
				$imgAttributeClass = $node->getAttribute('class');
				if(strpos($imgAttributeClass, $excludedImgClass) !== false) {
					return false;
				}
			}
		}
		
		$urlparts = parse_url($imagePath);
		// Dose this URL contain a host name?
		if (!empty($urlparts["host"])) {
			// is it local?
			if (substr($imagePath, 0, strlen(Uri::root())) == Uri::root()) {
				// This is a local url
				// Remove the URL
				$imagePath = substr($imagePath, strlen(Uri::root()));
			}
		}
		
		if (isset($imagePath[0]) && $imagePath[0] == "/") {
			$root = Uri::base(true);
			if (substr($imagePath, 0, strlen($root)) == $root) {
				$imagePath = dirname($_SERVER["SCRIPT_FILENAME"]) . substr($imagePath, strlen($root));
			}
		} else {
			$imagePath = $this->rootDir . $imagePath;
		}
		
		if (realpath($imagePath) === false) {
			return false;
		}
		
		$imagePath = realpath($imagePath);
		$path_parts = pathinfo($src);
		
		// Return false immediately if the image type is not supported
		if(isset($path_parts['extension'])) {
			$fileExtension = strtolower($path_parts['extension']);
			if (!$srcSet && ! in_array ( $fileExtension, array (
					'jpeg',
					'jpg',
					'swf',
					'psd',
					'bmp',
					'tiff',
					'jpc',
					'jp2',
					'jpf',
					'jb2',
					'swc',
					'aiff',
					'wbmp',
					'xbm',
					'gif',
					'png'
			) )) {
				return false;
			}
		} else {
			$fileExtension = null;
		}
		
		switch ($fileExtension) {
			case 'jpeg':
			case 'jpg':
			case 'swf':
			case 'psd':
			case 'bmp':
			case 'tiff':
			case 'jpc':
			case 'jp2':
			case 'jpf':
			case 'jb2':
			case 'swc':
			case 'aiff':
			case 'wbmp':
			case 'xbm':
				$new_ext = 'jpg';
				break;
			case 'gif':
				//!! GD dosent support resizing animated gifs
				$support_gif = (bool) $this->params->get('img_support_gif', 0);
				if ($support_gif) {
					$new_ext = 'png';
				} else {
					return false;
				}
				
				break;
			case 'png':
				$new_ext = 'png';
				break;
			default:
				$new_ext = 'png';
				$pref = $imagePath;
				break;
		}
		
		// Override force mode if all images must be converted to WEBP
		$wpSpeedBrowser = Browser::getInstance()->getBrowser ();
		$excludeJSpeedBrowserSafari = $this->params->get('exclude_light_images_safari', 0) && $wpSpeedBrowser == 'Safari';
		if($this->params->get('convert_all_images_to_webp', 1) && function_exists('imagewebp') && $wpSpeedBrowser != 'IE' && !$excludeJSpeedBrowserSafari) {
			$new_ext = 'webp';
		}

		// Override webp if avif is supported and enabled
		if($this->params->get('convert_all_images_to_avif', 0) && version_compare(PHP_VERSION, '8.1', '>=') && function_exists('imageavif') && $wpSpeedBrowser != 'IE' && $wpSpeedBrowser != 'Edge' && $wpSpeedBrowser != 'Opera Mini' && !$excludeJSpeedBrowserSafari) {
			$new_ext = 'avif';
		}

		// Skip images for excluded extensions
		if (isset($path_parts['extension']) && in_array(strtolower($path_parts['extension']), $this->excludedExts)) {
			return false;
		}
		
		$imagesAlgo = $this->params->get ( 'hash_images_algo', 'full' );
		if ($imagesAlgo == 'full') {
			if ($srcSet) {
				$filename = sha1 ( $originalSource ) . "_" . (4 - $srcSetIteration) . "x." . $new_ext;
			} else {
				$filename = sha1 ( $src ) . "." . $new_ext;
			}
		} elseif ($imagesAlgo == 'partial') {
			if ($srcSet) {
				$srcset_path_parts = pathinfo ( $originalSource );
				$filename = ($srcset_path_parts ['filename']) . '_' . sha1 ( $originalSource ) . "_" . (4 - $srcSetIteration) . "x." . $new_ext;
			} else {
				$filename = ($path_parts ['filename']) . '_' . sha1 ( $src ) . "." . $new_ext;
			}
		} elseif ($imagesAlgo == 'none') {
			if ($srcSet) {
				$srcset_path_parts = pathinfo ( $originalSource );
				$filename = ($srcset_path_parts ['filename']) . "_" . (4 - $srcSetIteration) . "x." . $new_ext;
			} else {
				$filename = ($path_parts ['filename']) . "." . $new_ext;
			}
		}
		
		$full_path_filename = $cache_path . "/" . $filename;
		
		// If cache file exists don't process anymore
		if ((@is_file($full_path_filename) && @is_file($imagePath) && @filemtime($full_path_filename) > @filemtime($imagePath)) || ($srcSet && @is_file($full_path_filename))) {
			// Files that are 0bytes, mean that they sould be ignored.
			if (filesize($full_path_filename) == 0) {
				return true;
			}
			
			$url = $cache_path_http . "/" . $filename;
		} elseif($this->params->get('webservice_processing', 1)) {
			$uriInstance = Uri::getInstance();
			$getDomain = rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/');
			$remoteImgUrl = $getDomain . '/' . ltrim($src, '/');
			
			$optimized_png_arr = json_decode($this->oFileRetriever->getFileContents('http://api.resmush.it/ws.php?img=' . $remoteImgUrl . '&qlty=' . $quality));
			if(isset($optimized_png_arr->dest)) {
				file_put_contents($full_path_filename, $this->oFileRetriever->getFileContents($optimized_png_arr->dest));
				$url = $cache_path_http . "/" . $filename;
			} else {
				return false;
			}
		} else {
			list($image, $image_file_size) = $this->fetchImageData($imagePath);
			if ($image === false) {
				return false;
			}
			
			$widthOriginal = imagesx($image);
			$heightOriginal = imagesy($image);
			
			if (!isset($heightExplicitFromNode) || !isset($widthExplicitFromNode)) {
				$imageWidth = $widthOriginal;
				$imageHeight = $heightOriginal;
			} else {
				$imageWidth = $widthExplicitFromNode;
				$imageHeight = $heightExplicitFromNode;
			}
			
			// Ensure that the image is worth of being processed and optimized, otherwise skip
			if($imageWidth < $processingMinWidth && !$srcSet) {
				return false;
			}
			
			// Override $imageWidth and $imageHeight if factor percentage is enabled and resizement is valid
			if ($resizeFactorSwitcher && ($imageWidth >= $resizeMinWidth || $srcSet || $preSrcSetCreation)) {
				$imageWidth = intval($imageWidth * $resizeFactor / 100);
				$imageHeight = intval($imageHeight * $resizeFactor / 100);
			}
			
			$result = @imagecreatetruecolor($imageWidth, $imageHeight);
			if ($result == false)
				return false;
				
			if ($new_ext == 'png' || $new_ext == 'webp' || $new_ext == 'avif') {
				imagealphablending($result, false);
				$transparent = imagecolorallocatealpha($result, 0, 0, 0, 127);
				imagefill($result, 0, 0, $transparent);
				imagesavealpha($result, true);
				imagealphablending($result, true);
			}
			
			$sample = @imagecopyresampled($result, $image, 0, 0, 0, 0, $imageWidth, $imageHeight, $widthOriginal, $heightOriginal);
			
			if ($sample == false)
				return false;
				
			switch ($new_ext) {
				case 'jpg':
					$save = @imagejpeg($result, $full_path_filename, $quality);
					break;
				case 'png':
					$save = @imagepng($result, $full_path_filename, $pngQuality);
					break;
				case 'webp':
					$save = @imagewebp($result, $full_path_filename, $quality);
					break;
				case 'avif':
					$save = @imageavif($result, $full_path_filename, $quality);
					break;
			}
			
			if ($save == false) {
				return false;
			}
			
			// Make sure we are really creating a smaller image!
			if (filesize($full_path_filename) >= $image_file_size && !$srcSet && !$preSrcSetCreation) {
				// Files that are 0bytes, mean that they sould be ignored.
				unlink($full_path_filename);
				return true;
			}
			
			$url = $cache_path_http . "/" . $filename;
			
			// Make sure we are really creating a smaller image!
			if (filesize($full_path_filename) >= $image_file_size && ($srcSet || $preSrcSetCreation)) {
				// Files that are 0bytes, mean that they sould be ignored.
				unlink($full_path_filename);
				if($originalSource || $preSrcSetCreation) {
					$filename = $originalSource ? $originalSource : $src;
					$full_path_filename = $imagePath;
					$url = $filename;
				}
			}
		}
		
		// Add to HTTP2 preload and remove the original image unless there is a srcset that must keep it
		$originalImageSrcHashToRemove = $originalSource ? md5(ltrim($originalSource, '/')) : md5(ltrim($originalSrc, '/'));
		if($createSrcSet && $srcSetOriginalImage == -1) {
			// Add the original image to the preload list
			Helper::addHttp2Push ( $originalSrc, 'image', false );
			// Kill the original image removal
			$originalImageSrcHashToRemove = null;
		}
		Helper::addHttp2Push ( $url, 'image', false, $originalImageSrcHashToRemove);
		
		// Set the new image location
		if(!$srcSet) {
			if(!$this->lazyLoadedImage) {
				$node->setAttribute("src", $url);
			}
			if ($processingDataSrc && $node->hasAttribute ( 'data-src' )) {
				$node->setAttribute ( "data-src", $url );
			} elseif ($processingDataSrc && $processingDataCustom !== '' && $node->hasAttribute ( 'data-' . $processingDataCustom )) {
				$node->setAttribute ( "data-" . $processingDataCustom, $url );
			}
		} else {
			// Get the current srcset populated till this recursion
			$srcSetAttribute = $node->getAttribute("srcset");
			
			list($image, $image_file_size) = $this->fetchImageData($full_path_filename);
			if ($image === false) {
				return false;
			}
			$widthSrcSetImage = imagesx($image);
			$newSrcSetImage =  $url . ' ' . $widthSrcSetImage . 'w,';
			
			// Concatenate the srcset attribute
			$newSrcSetAttribute = $srcSetAttribute . $newSrcSetImage;
			
			// Set the updated srcset attribute
			$node->setAttribute("srcset", $newSrcSetAttribute);
			if($processingDataSrc && $node->hasAttribute('data-srcset')) {
				$node->setAttribute("data-srcset", $newSrcSetAttribute);
			}
			
			if($srcSetOriginalImage != -1 && $srcSetIteration == $srcSetOriginalImage) {
				$node->setAttribute("src", $url);
				if($processingDataSrc && $node->hasAttribute('data-src')) {
					$node->setAttribute("data-src", $url);
				} elseif ($processingDataSrc && $processingDataCustom !== '' && $node->hasAttribute ( 'data-' . $processingDataCustom )) {
					$node->setAttribute ( "data-" . $processingDataCustom, $url );
				}
			}
		}

		/**
		 * Optional srcset creation using a recursive function
		 * It creates 4 srcset images:
		 * 4x that is the same as the regular img src
		 * 3x based on reduction factors for quality and resize
		 * 2x based on reduction factors for quality and resize
		 * 1x based on reduction factors for quality and resize
		 */
		if ($createSrcSet && !$srcSet) {
			for($i = 0; $i <= 3; $i ++) {
				$srcSetImgQuality = $srcSetQualityStartingQuality - ($srcSetQualityDecreaseStep * $i);
				$srcSetImgQuality = $srcSetImgQuality <= 0 ? 10 : $srcSetImgQuality;
				$srcSetImgResizeFactor = $srcSetResizeStartingResize - ($srcSetResizeDecreaseStep * $i);
				$srcSetImgResizeFactor = $srcSetImgResizeFactor <= 0 ? 10 : $srcSetImgResizeFactor;
				if($srcSetOriginalImage == -1) {
					$node->setAttribute("src", $originalSrc);
				}
				if($srcSetOriginalImage == -1 && $processingDataSrc && $node->hasAttribute('data-src')) {
					$node->setAttribute("data-src", $originalSrc);
				}
				$this->processImageNodes ( $node, true, $i, $srcSetImgQuality, $srcSetImgResizeFactor, $src );
			}
			// Trim the created srcset attribute
			$srcSetAttribute = $node->getAttribute("srcset");
			$newSrcSetAttribute = trim($srcSetAttribute, ',');
			$node->setAttribute("srcset", $newSrcSetAttribute);
			if($node->hasAttribute('data-srcset') || $node->hasAttribute('data-wpspeed-lazyload')) {
				$node->setAttribute("data-srcset", $newSrcSetAttribute);
				list($image, $image_file_size) = $this->fetchImageData($imagePath);
				$widthOriginalSrcSetImage = imagesx($image);
				$heightOriginalSrcSetImage = imagesy($image);
				$sSvgSrcset          = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $widthOriginalSrcSetImage . '" height="' . $heightOriginalSrcSetImage . '"></svg>';
				$sNewSrcsetAttribute = 'data:image/svg+xml;base64,' . base64_encode( $sSvgSrcset );
				$node->setAttribute('srcset', $sNewSrcsetAttribute);				
			}
		}
		
		return true;
	}
	
	private function fetchImageData($file) {
		if(!file_exists($file)) {
			return array(false, 0);
		}
		
		$data = @file_get_contents($file);
		
		// could not open image?
		if ($data === false) {
			return array(false, strlen($data));
		}
		
		$img = @imagecreatefromstring($data);
		
		return array($img, strlen($data));
	}
	
	public function optimize($sOptimizedHtml) {
		global $wp;
	
		// Avoid unuseful operations if not enabled
		if (!$this->isEnabled) {
			return false;
		}

		// Exclude by menu item
		$currentPageUrl = home_url($wp->request);
		$menuExcluded = $this->params->get ( 'img_menu_excluded', array () );
		foreach ($menuExcluded as $menuChunk) {
			if(stripos ( $currentPageUrl, $menuChunk )) {
				return false;
			}
		}
		
		// Ensure valid cache folder exists
		if (!File::folderExists(WPSPEED_CACHE_DIR . 'images')) {
			File::folderCreate(WPSPEED_CACHE_DIR . 'images');
		}

		$purifyString = trim($this->params->get('purify_string',''));

		// Param per switch DOMDocument <-> SimpleHtmlDom
		$useSimpleHtmlDom = (bool) $this->params->get('use_simplehtmldom', 0);
		
		if ($useSimpleHtmlDom) {
			// SimpleHtmlDom method
			$dom = new SimpleHtmlDom();
			
			$dom->load($sOptimizedHtml);
			
			foreach ($dom->find('img') as $element) {
				$this->processImageNodes($element);
			}
			
			if ($this->params->get('img_processing_simplehtmldom_entity_decode', 0)) {
				foreach ($dom->nodes as $n) {
					if ($n->nodetype === WPSPEED_HDOM_TYPE_TEXT) {
						$p = $n->parent;
						if ($p && in_array(strtolower($p->tag), ['script','style'], true)) {
							continue;
						}
						
						$txt = $n->_[WPSPEED_HDOM_INFO_TEXT] ?? '';
						if ($txt !== '') {
							$txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
							
							$n->_[WPSPEED_HDOM_INFO_TEXT] = $txt;
						}
					}
				}
			}
			
			$output = $dom->save();
			$dom->clear();
			unset($dom);
			
			return $output;
		} else {
			// DOMDocument method
			$doc = new \DOMDocument();
			libxml_use_internal_errors(true);
			
			if($this->params->get('img_processing_utf8_entity_decode', 0)) {
				$sOptimizedHtml = mb_encode_numericentity($sOptimizedHtml, [0x80, 0xFFFF, 0, 0xFFFF], 'UTF-8');
			}
			
			// Fix for alpine.js @attributes
			$sOptimizedHtml = preg_replace('/@click/i', 'x-on:click', $sOptimizedHtml);
			$sOptimizedHtml = preg_replace('/@change/i', 'x-on:change', $sOptimizedHtml);
			$sOptimizedHtml = preg_replace('/@submit/i', 'x-on:submit', $sOptimizedHtml);
			
			if($purifyString) {
				$purifyStringReplacement = trim($this->params->get('purify_string_replacement',''));
				$purifiedStringReplacedHtml = preg_replace('/' . addcslashes($purifyString, '/') . '/i', $purifyStringReplacement, $sOptimizedHtml);
				
				if($this->params->get('img_processing_utf8_entity_decode', 0)) {
					$doc->loadHTML($purifiedStringReplacedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
				} else {
					$doc->loadHTML($purifiedStringReplacedHtml);
				}
			} else {
				if($this->params->get('img_processing_utf8_entity_decode', 0)) {
					$doc->loadHTML($sOptimizedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
				} else {
					$doc->loadHTML($sOptimizedHtml);
				}
			}
			
			if($this->params->get('img_processing_entity_decode', 1)) {
				$doc->encoding = 'utf-8';
			}
			
			libxml_clear_errors();

			$nodes = $doc->getElementsByTagName('img');

			foreach ($nodes as $node) {
				$this->processImageNodes($node);
			}
			
			if($this->params->get('img_processing_entity_decode', 1)) {
				if($this->params->get('img_processing_utf8_entity_decode', 0)) {
					return mb_decode_numericentity($doc->saveHTML(), [0x0, 0x2FFFF, 0, 0xFFFF], 'UTF-8');
				} else {
					return html_entity_decode($doc->saveHTML(), ENT_QUOTES, 'UTF-8');
				}
			} else {
				return $doc->saveHTML();
			}
		}
	}

	public function optimizeSingleImage(&$DOMelement) {
		$this->disableSrcSet = true;
		
		// Ensure valid cache folder exists
		if (!File::folderExists(WPSPEED_CACHE_DIR . 'images')) {
			File::folderCreate(WPSPEED_CACHE_DIR . 'images');
		}
		
		$this->processImageNodes($DOMelement);
	}

	public function __construct($params) {
		$this->params = $params;
		
		$this->isEnabled = $this->params->get('lightimgs_status', 1);
		
		// Setup default excludes for Adaptive Contents
		$isBot = false;
		if($this->params->get('adaptive_contents_enable', 1) && $this->params->get('adaptive_contents_optimize_images', 0) && isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
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
			
			if(!$isBot) {
				$this->isEnabled = false;
			}
		}
		
		$this->excludedExts = $this->params->get('img_exts_excluded', []);
		$this->oFileRetriever = FileRetriever::getInstance ();
		
		include dirname( __FILE__, 3 ) . '/root.php';
		$this->rootDir = $WPSPEED_ROOT;
		$this->oContainer   = new Container();
		$this->oApplication = Application::getInstance( 'WPSpeedApplication', $this->oContainer );
	}
}