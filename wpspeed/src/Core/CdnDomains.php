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

use WPSpeed\Platform\Settings;
use WPSpeed\Platform\Utility;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

class CdnDomains
{
	public static function addCdnDomains( Cdn $oCdn, array &$aDomain )
	{
		if ( trim( $oCdn->oParams->get( 'pro_cookielessdomain_2', '' ) ) != '' )
		{
			$domain2       = $oCdn->oParams->get( 'pro_cookielessdomain_2' );
			$sStaticFiles2 = implode( '|', $oCdn->oParams->get( 'pro_staticfiles_2', Cdn::getStaticFiles() ) );

			$aDomain[ $oCdn->scheme . $oCdn->prepareDomain( $domain2 ) ] = $sStaticFiles2;
		}

		if ( trim( $oCdn->oParams->get( 'pro_cookielessdomain_3', '' ) ) != '' )
		{
			$domain3       = $oCdn->oParams->get( 'pro_cookielessdomain_3' );
			$sStaticFiles3 = implode( '|', $oCdn->oParams->get( 'pro_staticfiles_3', Cdn::getStaticFiles() ) );

			$aDomain[ $oCdn->scheme . $oCdn->prepareDomain( $domain3 ) ] = $sStaticFiles3;
		}
	}

	public static function preconnect( Settings $oParams )
	{
		if ( $oParams->get( 'cookielessdomain_enable', '0' ) && $oParams->get( 'pro_cdn_preconnect', '1' ) )
		{
			$oCdn     = Cdn::getInstance( $oParams );
			$aDomains = $oCdn->getCdnDomains();

			$sCdnPreConnect = '';

			foreach ( $aDomains as $sDomain => $sStaticFiles )
			{
				$sCdnPreConnect .= Utility::tab() . '<link rel="preconnect" href="' . $sDomain . '" crossorigin />'
					. Utility::lnEnd();
			}

			return $sCdnPreConnect;
		}
	}
}