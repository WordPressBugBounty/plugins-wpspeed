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
namespace WPSpeed\Admin\View\Configurations;

use WPSFramework\Mvc\View;

class Html extends View {
	protected $size = 0;
	protected $no_files = 0;
	protected function onBeforeMain() {
		wp_register_script ( 'wpspeed-tabstate-js', WPSPEED_URL . 'media/js/tabs-state.js', [ 
				'jquery',
				'wpspeed-bootstrap-js'
		], WPSPEED_VERSION, true );

		wp_enqueue_script ( 'wpspeed-tabstate-js' );

		$this->getCacheSize ();

		return true;
	}
	private function getCacheSize() {
		/** @var Main $oModel */
		$oModel = $this->getModel ();

		$oModel->getCacheSize ( WPSPEED_CACHE_DIR, $this->size, $this->no_files );

		$decimals = 2;
		$sz = 'BKMGTP';
		$factor = ( int ) floor ( (strlen ( $this->size ) - 1) / 3 );

		$this->size = sprintf ( "%.{$decimals}f", $this->size / pow ( 1024, $factor ) ) . $sz [$factor];
		$this->no_files = number_format ( $this->no_files );
	}
}