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

namespace WPSpeed\Core\Css\Sprite\Handlers;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );


interface HandlerInterface
{

	public function getSupportedFormats();

	public function createSprite($iSpriteWidth, $iSpriteHeight, $sBgColour, $sOutputFormat);

	public function createBlankImage($aFileInfo);

	public function resizeImage($oSprite, $oCurrentImage, $aFileInfo);

	public function copyImageToSprite($oSprite, $oCurrentImage, $aFileInfo, $bResize);

	public function destroy($oImage);

	public function createImage($aFileInfo);

	public function writeImage($oImage, $sExtension, $sFilename);
}

