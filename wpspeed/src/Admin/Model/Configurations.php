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
namespace WPSpeed\Admin\Model;

use WPSFramework\Mvc\Model;
use FilesystemIterator;

class Configurations extends Model {
	public function getCacheSize($cache_path, &$size, &$no_files) {
		if (file_exists ( $cache_path )) {
			$fi = new FilesystemIterator ( $cache_path, FilesystemIterator::SKIP_DOTS );

			foreach ( $fi as $file ) {
				$size += $file->getSize ();
			}

			$no_files += iterator_count ( $fi );
		}
		
		// Add images path if availble
		$imagesCachePath = $cache_path . 'images/';
		if (file_exists ( $imagesCachePath )) {
			$fim = new FilesystemIterator ( $imagesCachePath, FilesystemIterator::SKIP_DOTS );
			
			foreach ( $fim as $image ) {
				$size += $image->getSize ();
			}
			
			$no_files += iterator_count ( $fim );
		}
	}
}