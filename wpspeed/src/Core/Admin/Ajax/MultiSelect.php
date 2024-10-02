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

namespace WPSpeed\Core\Admin\Ajax;

use WPSpeed\Core\Admin\Json;
use WPSpeed\Core\Admin\MultiSelectItems;
use WPSpeed\Platform\Html;
use WPSpeed\Platform\Plugin;
use WPSpeed\Platform\Utility;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

class MultiSelect extends Ajax
{
	public function run()
	{
		$aData = Utility::get( 'data', array(), 'array' );

		$params = Plugin::getPluginParams();
		$oAdmin = new MultiSelectItems( $params );
		$oHtml  = new Html( $params );

		try
		{
			$sHtml = $oHtml->getHomePageHtml();
			$oAdmin->getAdminLinks( $sHtml );
		}
		catch ( \Exception $e )
		{
			$error = $e->getMessage();
		}

		$response = array();

		foreach ( $aData as $sData )
		{
			$options = $oAdmin->prepareFieldOptions( $sData['type'], $sData['param'], $sData['group'], false );

			$response[$sData['id']] = new Json( $options );
		}

		return new Json( $response );
	}
}