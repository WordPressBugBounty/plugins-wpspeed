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

defined ( '_WPSPEED_EXEC' ) or die ( 'Restricted access' );

use WPSpeed\Platform\Utility;
use WPSpeed\Platform\Plugin;

class Browser {
	// Based on response from https://fonts.googleapis.com/css?family=Racing+Sans+One
	protected static $instances = array ();
	protected $fontHash = '';
	protected $oClient;
	public static function getInstance($userAgent = '') {
		if ($userAgent == '' && isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
			$userAgent = trim ( $_SERVER ['HTTP_USER_AGENT'] );
		}
		
		$params = Plugin::getPluginParams ();
		if(!is_admin() && $params->get('adaptive_contents_enable', 0)) {
			// Fallback for HTTP_X_FORWARDED_FOR
			if($_SERVER['REMOTE_ADDR'] == '127.0.0.1' && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER['REMOTE_ADDR']) {
				// Check if there are multiple HTTP forwarded IP addresses
				if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
					$multipleIPs = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
					$_SERVER['REMOTE_ADDR'] = $multipleIPs[0];
				} else {
					$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
			}
			
			if(array_key_exists($_SERVER['REMOTE_ADDR'], Utility::$botsIP)) {
				$userAgent .= ' GTMetrix';
			}
		}
		
		$signature = md5 ( $userAgent );
		
		if (! isset ( self::$instances [$signature] )) {
			self::$instances [$signature] = new Browser ( $userAgent );
		}
		
		return self::$instances [$signature];
	}
	protected function calculateFontHash($userAgent) {
		$this->fontHash .= $this->oClient->os . '/';

		$sVersion = $this->oClient->browserVersion;

		switch ($this->oClient->browser) {
			case 'Chrome' :

				if (version_compare ( $sVersion, '40', '>=' ) || $sVersion == 'Unknown') {
					$this->fontHash .= 'woff2/unicode';
				} elseif (version_compare ( $sVersion, '22', '>=' )) {
					$this->fontHash .= 'woff';
				}

				break;

			case 'Firefox' :

				if (version_compare ( $sVersion, '44', '>=' ) || $sVersion == 'Unknown') {
					$this->fontHash .= 'woff2/unicode';
				} elseif (version_compare ( $sVersion, '39', '>=' )) {
					$this->fontHash .= 'woff2';
				} elseif (version_compare ( $sVersion, '11', '>=' )) {
					$this->fontHash .= 'woff';
				}

				break;

			case 'Edge' :

				if (version_compare ( $sVersion, '17', '>=' ) || $sVersion == 'Unknown') {
					$this->fontHash .= 'woff2/unicode';
				} elseif (version_compare ( $sVersion, '15', '>=' )) {
					$this->fontHash .= 'woff2';
				}

				break;

			case 'Internet Explorer' :

				if (version_compare ( $sVersion, '9', '>=' ) || $sVersion == 'Unknown') {
					$this->fontHash .= 'woff';
				} elseif (version_compare ( $sVersion, '7', '>=' )) {
					$this->fontHash .= 'eot';
				}

				break;

			case 'Opera' :

				if (version_compare ( $sVersion, '20', '>=' ) || $sVersion == 'Unknown') {
					$this->fontHash .= 'woff2/unicode';
				} elseif (version_compare ( $sVersion, '11.1', '>=' )) {
					$this->fontHash .= 'woff';
				} elseif (version_compare ( $sVersion, '10.6', '>=' )) {
					$this->fontHash .= 'ttf';
				}

				break;

			case 'Safari' :

				if (version_compare ( $sVersion, '10.1', '>=' ) || $sVersion == 'Unknown') {
					$this->fontHash .= 'woff2/unicode';
				} elseif (version_compare ( $sVersion, '5.1', '>=' )) {
					$this->fontHash .= 'woff';
				} elseif (version_compare ( $sVersion, '4', '>=' )) {
					$this->fontHash .= 'ttf';
				}

				break;

			default :
				break;
		}
		
		if (preg_match ( '#GTMetrix#i', $userAgent)) {
			$this->fontHash = 'gtc000mx/woff';
		} // Lighthouse
		elseif (preg_match ( '#Lighthouse#i', $userAgent)) {
			$this->fontHash = 'lgh000se/woff';
		} // Googlebot
		elseif (preg_match ( '#Googlebot#i', $userAgent)) {
			$this->fontHash = 'goo001bo/woff';
		} // Bingbot
		elseif (preg_match ( '#Bingbot#i', $userAgent)) {
			$this->fontHash = 'bng001bo/woff';
		} // Baiduspider
		elseif (preg_match ( '#Baiduspider#i', $userAgent)) {
			$this->fontHash = 'bdug001sp/woff';
		} // Duckduckbot
		elseif (preg_match ( '#Duckduckbot#i', $userAgent)) {
			$this->fontHash = 'dckg001bo/woff';
		} // Twitterbot
		elseif (preg_match ( '#Twitterbot#i', $userAgent)) {
			$this->fontHash = 'twtg001bo/woff';
		} // Applebot
		elseif (preg_match ( '#Applebot#i', $userAgent)) {
			$this->fontHash = 'appg001bo/woff';
		} // Semrushbot
		elseif (preg_match ( '#Semrushbot#i', $userAgent)) {
			$this->fontHash = 'semg001bo/woff';
		}
	}
	public function getBrowser() {
		return $this->oClient->browser;
	}
	public function getFontHash() {
		return $this->fontHash;
	}
	public function getVersion() {
		return $this->oClient->browserVersion;
	}
	public function __construct($userAgent) {
		$this->oClient = Utility::userAgent ( $userAgent );
		$this->calculateFontHash ($userAgent);
	}
}
